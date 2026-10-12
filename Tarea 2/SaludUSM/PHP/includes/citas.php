<?php
// Reglas de citas: bloques horarios, reservas, reprogramaciones, cancelaciones
// y cambios de estado del medico. No genera salida HTML.
// Los errores para el usuario se lanzan como DomainException.

// Horario de atencion: lunes a viernes, 09:00-13:00 y 14:00-18:00, bloques de 30 minutos.
const DURACION_BLOQUE = 30;
const TRAMOS_ATENCION = [['09:00', '13:00'], ['14:00', '18:00']];
const DIAS_RESERVA = 90;
const ZONA_HORARIA = 'America/Santiago';

function ahora(): DateTimeImmutable {
    return new DateTimeImmutable('now', new DateTimeZone(ZONA_HORARIA));
}

function bloquesDelDia(): array {
    $bloques = [];
    foreach (TRAMOS_ATENCION as [$inicio, $fin]) {
        for ($t = strtotime("1970-01-01 $inicio UTC"); $t < strtotime("1970-01-01 $fin UTC"); $t += DURACION_BLOQUE * 60) {
            $bloques[] = gmdate('H:i', $t);
        }
    }
    return $bloques;
}

// Fecha Y-m-d valida, de lunes a viernes, entre hoy y DIAS_RESERVA dias mas. null si no.
function fechaReservable(string $fecha): ?DateTimeImmutable {
    $zona = new DateTimeZone(ZONA_HORARIA);
    $f = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, $zona);
    $hoy = new DateTimeImmutable('today', $zona);
    if (!$f || $f->format('Y-m-d') !== $fecha || $f < $hoy || $f > $hoy->modify('+' . DIAS_RESERVA . ' days')
        || (int)$f->format('N') > 5) {
        return null;
    }
    return $f;
}

// Cancela las citas vencidas (procedimiento almacenado CancelarCitasVencidas).
// Se ejecuta al abrir Mis citas, Agendar hora y la Agenda del medico.
function cancelarCitasVencidas(PDO $db): void {
    $q = $db->query('CALL CancelarCitasVencidas()');
    $q->closeCursor();
}

function idEstado(PDO $db, string $nombre): int {
    $q = $db->prepare('SELECT ID_Estado FROM estado WHERE Estado_Cita = ?');
    $q->execute([$nombre]);
    $id = $q->fetchColumn();
    if ($id === false) {
        throw new RuntimeException("Falta el estado $nombre en la tabla estado.");
    }
    return (int)$id;
}

// Serializa reservas y reprogramaciones: los triggers validan el bloque, pero sin
// clave unica dos reservas simultaneas podrian pasar la validacion a la vez.
function bloquearAgenda(PDO $db): string {
    $base = (string)$db->query('SELECT DATABASE()')->fetchColumn();
    $bloqueo = 'saludusm_agenda_' . substr(hash('sha256', $base), 0, 40);
    $q = $db->prepare('SELECT GET_LOCK(?, 5)');
    $q->execute([$bloqueo]);
    if ((int)$q->fetchColumn() !== 1) {
        throw new DomainException('Hay otra reserva en curso. Inténtalo nuevamente.');
    }
    return $bloqueo;
}

function liberarAgenda(PDO $db, string $bloqueo): void {
    $q = $db->prepare('SELECT RELEASE_LOCK(?)');
    $q->execute([$bloqueo]);
}

// Mensaje claro para los rechazos de los triggers (SQLSTATE 45000).
function errorCita(Throwable $error): Throwable {
    if ($error instanceof PDOException && ($error->errorInfo[0] ?? '') === '45000') {
        return new DomainException(($error->errorInfo[2] ?? 'Cita rechazada') . '.');
    }
    return $error;
}

// ---------------------------------------------------------------- Opciones de reserva

function especialidadesConMedicos(PDO $db): array {
    return $db->query('SELECT DISTINCT e.ID_Especialidad, e.Nombre_Especialidad
        FROM especialidad e
        JOIN medico_especialidad me ON me.ID_Especialidad = e.ID_Especialidad
        JOIN centro_medico_medico cmm ON cmm.RUT_Medico = me.RUT_Medico
        ORDER BY e.Nombre_Especialidad')->fetchAll(PDO::FETCH_ASSOC);
}

function centrosParaEspecialidad(PDO $db, int $especialidad): array {
    $q = $db->prepare('SELECT DISTINCT cm.Codigo_Centro, cm.Nombre_Centro, cm.Comuna_Centro
        FROM centro_medico cm
        JOIN centro_medico_medico cmm ON cmm.Codigo_Centro = cm.Codigo_Centro
        JOIN medico_especialidad me ON me.RUT_Medico = cmm.RUT_Medico
        WHERE me.ID_Especialidad = ?
        ORDER BY cm.Nombre_Centro');
    $q->execute([$especialidad]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

function medicosPara(PDO $db, int $especialidad, int $centro): array {
    $q = $db->prepare('SELECT m.RUT_Medico, m.Nombre_Medico
        FROM medico m
        JOIN medico_especialidad me ON me.RUT_Medico = m.RUT_Medico AND me.ID_Especialidad = ?
        JOIN centro_medico_medico cmm ON cmm.RUT_Medico = m.RUT_Medico AND cmm.Codigo_Centro = ?
        ORDER BY m.Nombre_Medico');
    $q->execute([$especialidad, $centro]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

// Bloques del dia sin cita vigente del medico ni del paciente, y que aun no pasan.
// $excluir: cita que se esta reprogramando (su bloque actual no cuenta como ocupado).
function bloquesLibres(PDO $db, string $rutMedico, string $rutPaciente, string $fecha, ?int $excluir = null): array {
    if (!fechaReservable($fecha)) {
        return [];
    }
    $q = $db->prepare("SELECT DISTINCT TIME_FORMAT(c.Hora_Cita, '%H:%i')
        FROM cita c JOIN estado e ON e.ID_Estado = c.ID_Estado
        WHERE c.Fecha_Cita = ? AND e.Estado_Cita <> 'Cancelada' AND c.Nro_Cita <> ?
          AND (c.RUT_Medico = ? OR c.RUT_Paciente = ?)");
    $q->execute([$fecha, $excluir ?? 0, $rutMedico, $rutPaciente]);
    $ocupados = $q->fetchAll(PDO::FETCH_COLUMN);
    $minimo = ahora()->format('Y-m-d H:i');
    return array_values(array_filter(bloquesDelDia(), function (string $hora) use ($ocupados, $fecha, $minimo) {
        return !in_array($hora, $ocupados, true) && "$fecha $hora" > $minimo;
    }));
}

// Reglas comunes de fecha y hora para reservar o reprogramar.
function validarBloque(string $fecha, string $hora): void {
    if (!fechaReservable($fecha)) {
        throw new DomainException('La fecha debe ser un día hábil (lunes a viernes) entre hoy y los próximos '
            . DIAS_RESERVA . ' días.');
    }
    if (!in_array($hora, bloquesDelDia(), true)) {
        throw new DomainException('La hora no corresponde a un bloque de atención.');
    }
    if ("$fecha $hora" <= ahora()->format('Y-m-d H:i')) {
        throw new DomainException('Ese bloque ya pasó. Elige un horario futuro.');
    }
}

// El medico debe tener la especialidad y atender en el centro (regla de la Tarea 1).
function validarMedicoCentroEspecialidad(PDO $db, string $rutMedico, int $especialidad, int $centro): void {
    $q = $db->prepare('SELECT 1 FROM medico_especialidad WHERE RUT_Medico = ? AND ID_Especialidad = ?');
    $q->execute([$rutMedico, $especialidad]);
    if (!$q->fetchColumn()) {
        throw new DomainException('El médico seleccionado no tiene la especialidad solicitada.');
    }
    $q = $db->prepare('SELECT 1 FROM centro_medico_medico WHERE RUT_Medico = ? AND Codigo_Centro = ?');
    $q->execute([$rutMedico, $centro]);
    if (!$q->fetchColumn()) {
        throw new DomainException('El médico seleccionado no atiende en el centro elegido.');
    }
}

// ---------------------------------------------------------------- Paciente

function reservarCita(PDO $db, string $rutPaciente, int $especialidad, int $centro, string $rutMedico,
                      string $fecha, string $hora): int {
    validarBloque($fecha, $hora);
    $bloqueo = bloquearAgenda($db);
    try {
        $db->beginTransaction();
        validarMedicoCentroEspecialidad($db, $rutMedico, $especialidad, $centro);
        // ValidarAgendaInsert rechaza el bloque si el medico o el paciente ya tienen cita.
        $q = $db->prepare('INSERT INTO cita (Fecha_Cita, Hora_Cita, Codigo_Centro, RUT_Paciente, RUT_Medico,
            ID_Especialidad, ID_Estado) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $q->execute([$fecha, "$hora:00", $centro, $rutPaciente, $rutMedico, $especialidad, idEstado($db, 'Reservada')]);
        $nro = (int)$db->lastInsertId();
        $db->commit();
        return $nro;
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw errorCita($error);
    } finally {
        liberarAgenda($db, $bloqueo);
    }
}

// Cita del paciente que aun puede reprogramarse o cancelarse; bloquea la fila.
function citaModificable(PDO $db, string $rutPaciente, int $nro): array {
    $q = $db->prepare("SELECT c.*, e.Estado_Cita FROM cita c JOIN estado e ON e.ID_Estado = c.ID_Estado
        WHERE c.Nro_Cita = ? AND c.RUT_Paciente = ? FOR UPDATE");
    $q->execute([$nro, $rutPaciente]);
    $cita = $q->fetch(PDO::FETCH_ASSOC);
    if (!$cita) {
        throw new DomainException('La cita no existe o no te pertenece.');
    }
    if (!in_array($cita['Estado_Cita'], ['Reservada', 'Confirmada'], true)) {
        throw new DomainException('Solo se pueden modificar citas en estado Reservada o Confirmada (esta cita está '
            . $cita['Estado_Cita'] . ').');
    }
    if ($cita['Fecha_Cita'] . ' ' . substr($cita['Hora_Cita'], 0, 5) <= ahora()->format('Y-m-d H:i')) {
        throw new DomainException('La fecha y hora de la cita ya pasaron; no se puede modificar.');
    }
    return $cita;
}

function cargarCitaPaciente(PDO $db, string $rutPaciente, int $nro): ?array {
    $q = $db->prepare('SELECT c.Nro_Cita, c.Fecha_Cita, c.Hora_Cita, c.Codigo_Centro, c.ID_Especialidad,
            c.RUT_Medico, m.Nombre_Medico, esp.Nombre_Especialidad, cm.Nombre_Centro, e.Estado_Cita
        FROM cita c
        JOIN medico m ON m.RUT_Medico = c.RUT_Medico
        JOIN especialidad esp ON esp.ID_Especialidad = c.ID_Especialidad
        JOIN centro_medico cm ON cm.Codigo_Centro = c.Codigo_Centro
        JOIN estado e ON e.ID_Estado = c.ID_Estado
        WHERE c.Nro_Cita = ? AND c.RUT_Paciente = ?');
    $q->execute([$nro, $rutPaciente]);
    $cita = $q->fetch(PDO::FETCH_ASSOC);
    return $cita ?: null;
}

// Cambia fecha y hora (mismo medico, centro y especialidad). Vuelve a estado Reservada.
function reprogramarCita(PDO $db, string $rutPaciente, int $nro, string $fecha, string $hora): void {
    validarBloque($fecha, $hora);
    $bloqueo = bloquearAgenda($db);
    try {
        $db->beginTransaction();
        $cita = citaModificable($db, $rutPaciente, $nro);
        if ($cita['Fecha_Cita'] === $fecha && substr($cita['Hora_Cita'], 0, 5) === $hora) {
            throw new DomainException('Elige una fecha u hora distinta a la actual.');
        }
        // ValidarAgendaUpdate rechaza bloques pasados u ocupados.
        $q = $db->prepare('UPDATE cita SET Fecha_Cita = ?, Hora_Cita = ?, ID_Estado = ? WHERE Nro_Cita = ?');
        $q->execute([$fecha, "$hora:00", idEstado($db, 'Reservada'), $nro]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw errorCita($error);
    } finally {
        liberarAgenda($db, $bloqueo);
    }
}

function cancelarCitaPaciente(PDO $db, string $rutPaciente, int $nro): void {
    try {
        $db->beginTransaction();
        citaModificable($db, $rutPaciente, $nro);
        // Procedimiento almacenado NULLCita: pasa la cita a Cancelada.
        $q = $db->prepare('CALL NULLCita(?)');
        $q->execute([$nro]);
        $q->closeCursor();
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw errorCita($error);
    }
}

// Consultas sobre vistas con preparacion emulada: MariaDB (XAMPP) responde 1615
// "Prepared statement needs to be re-prepared" al prepararlas en el servidor.
// PDO sigue escapando los parametros, por lo que no hay riesgo de inyeccion.
function consultarVista(PDO $db, string $sql, array $parametros): array {
    $anterior = $db->getAttribute(PDO::ATTR_EMULATE_PREPARES);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    try {
        $q = $db->prepare($sql);
        $q->execute($parametros);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    } finally {
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, $anterior);
    }
}

// Mis citas, desde la vista comprobante_reserva_hora (Vista 1 de la tarea).
// Proximas: Reservadas o Confirmadas desde hoy. Historicas: todas las demas.
function citasPaciente(PDO $db, string $rutPaciente): array {
    $filas = consultarVista($db, "SELECT v.*, STR_TO_DATE(v.Fecha_Cita, '%d-%m-%Y') AS Fecha_ISO
        FROM comprobante_reserva_hora v
        WHERE v.RUT_Paciente = ?
        ORDER BY Fecha_ISO, v.Hora_Cita", [$rutPaciente]);
    $hoy = ahora()->format('Y-m-d');
    $citas = ['proximas' => [], 'historicas' => []];
    foreach ($filas as $cita) {
        $vigente = in_array($cita['Estado_Cita'], ['Reservada', 'Confirmada'], true) && $cita['Fecha_ISO'] >= $hoy;
        $cita['Modificable'] = $vigente
            && $cita['Fecha_ISO'] . ' ' . substr($cita['Hora_Cita'], 0, 5) > ahora()->format('Y-m-d H:i');
        $citas[$vigente ? 'proximas' : 'historicas'][] = $cita;
    }
    $citas['historicas'] = array_reverse($citas['historicas']);
    return $citas;
}

// ---------------------------------------------------------------- Medico

function agendaMedico(PDO $db, string $rutMedico, string $fecha): array {
    $q = $db->prepare("SELECT c.Nro_Cita, TIME_FORMAT(c.Hora_Cita, '%H:%i') AS Hora, c.Fecha_Cita,
            p.Nombre_Paciente, p.RUT_Paciente, pr.Prevision_Paciente, cm.Nombre_Centro,
            esp.Nombre_Especialidad, e.Estado_Cita, a.Nro_Atencion
        FROM cita c
        JOIN estado e ON e.ID_Estado = c.ID_Estado
        JOIN centro_medico cm ON cm.Codigo_Centro = c.Codigo_Centro
        JOIN especialidad esp ON esp.ID_Especialidad = c.ID_Especialidad
        LEFT JOIN paciente p ON p.RUT_Paciente = c.RUT_Paciente
        LEFT JOIN prevision pr ON pr.ID_Prevision = p.ID_Prevision
        LEFT JOIN atencion a ON a.Nro_Cita = c.Nro_Cita
        WHERE c.RUT_Medico = ? AND c.Fecha_Cita = ?
        ORDER BY c.Hora_Cita");
    $q->execute([$rutMedico, $fecha]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

// Estados a los que el medico puede llevar la cita, segun su estado y si ya ocurrio.
function estadosPermitidosMedico(array $cita): array {
    $ocurrio = $cita['Fecha_Cita'] . ' ' . $cita['Hora'] <= ahora()->format('Y-m-d H:i');
    switch ($cita['Estado_Cita']) {
        case 'Reservada':
            return $ocurrio ? ['Atendida', 'No Asistió'] : ['Confirmada'];
        case 'Confirmada':
            return $ocurrio ? ['Atendida', 'No Asistió'] : [];
        case 'Atendida':
            // Con atencion registrada el estado queda fijo.
            return $cita['Nro_Atencion'] === null ? ['No Asistió'] : [];
        case 'No Asistió':
            return ['Atendida'];
        default:
            return [];
    }
}

function cambiarEstadoMedico(PDO $db, string $rutMedico, int $nro, string $nuevo): void {
    try {
        $db->beginTransaction();
        $q = $db->prepare("SELECT c.Nro_Cita, c.Fecha_Cita, TIME_FORMAT(c.Hora_Cita, '%H:%i') AS Hora,
                e.Estado_Cita, a.Nro_Atencion
            FROM cita c JOIN estado e ON e.ID_Estado = c.ID_Estado
            LEFT JOIN atencion a ON a.Nro_Cita = c.Nro_Cita
            WHERE c.Nro_Cita = ? AND c.RUT_Medico = ? FOR UPDATE");
        $q->execute([$nro, $rutMedico]);
        $cita = $q->fetch(PDO::FETCH_ASSOC);
        if (!$cita) {
            throw new DomainException('La cita no existe o no pertenece a tu agenda.');
        }
        if (!in_array($nuevo, estadosPermitidosMedico($cita), true)) {
            throw new DomainException("No se puede cambiar la cita de {$cita['Estado_Cita']} a $nuevo. "
                . 'Confirmada solo antes de la hora de la cita; Atendida o No Asistió solo desde su hora; '
                . 'una cita Cancelada o con atención registrada no cambia de estado.');
        }
        $q = $db->prepare('UPDATE cita SET ID_Estado = ? WHERE Nro_Cita = ?');
        $q->execute([idEstado($db, $nuevo), $nro]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw errorCita($error);
    }
}
