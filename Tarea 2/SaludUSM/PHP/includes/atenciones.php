<?php
// Reglas de atenciones, diagnosticos, recetas e historial clinico.
// No genera salida HTML. Los errores para el usuario se lanzan como DomainException.
require_once __DIR__ . '/citas.php';

const MAX_TEXTO_CLINICO = 2000;
const MAX_TEXTO_RECETA = 200;
const MAX_DIAS_TRATAMIENTO = 365;

function largoClinico(string $texto): int {
    $n = preg_match_all('/./us', $texto, $letras);
    return $n === false ? -1 : $n;
}

function validarAtencion(string $motivo, string $observaciones): array {
    $errores = [];
    $n = largoClinico($motivo);
    if ($n < 1 || $n > MAX_TEXTO_CLINICO) {
        $errores[] = 'El motivo de consulta es obligatorio (hasta ' . MAX_TEXTO_CLINICO . ' caracteres).';
    }
    $n = largoClinico($observaciones);
    if ($n < 1 || $n > MAX_TEXTO_CLINICO) {
        $errores[] = 'Las observaciones clínicas son obligatorias (hasta ' . MAX_TEXTO_CLINICO . ' caracteres).';
    }
    return $errores;
}

// Codigo CIE-10 a nivel de categoria: una letra y dos digitos (ej. J00).
function validarDiagnostico(string $codigo, string $descripcion, bool $descripcionObligatoria): array {
    $errores = [];
    if (!preg_match('/^[A-Z][0-9]{2}$/D', $codigo)) {
        $errores[] = 'El código CIE-10 debe tener una letra y dos dígitos (ejemplo: J00).';
    }
    $n = largoClinico($descripcion);
    if ($n < 0 || $n > 255 || ($descripcionObligatoria && $n < 1)) {
        $errores[] = 'La descripción del diagnóstico es obligatoria para un código nuevo (hasta 255 caracteres).';
    }
    return $errores;
}

function validarReceta(string $medicamento, string $dosis, string $dias): array {
    $errores = [];
    $n = largoClinico($medicamento);
    if ($n < 1 || $n > MAX_TEXTO_RECETA) {
        $errores[] = 'El medicamento es obligatorio (hasta ' . MAX_TEXTO_RECETA . ' caracteres).';
    }
    $n = largoClinico($dosis);
    if ($n < 1 || $n > MAX_TEXTO_RECETA) {
        $errores[] = 'La dosis es obligatoria (hasta ' . MAX_TEXTO_RECETA . ' caracteres).';
    }
    if (!ctype_digit($dias) || (int)$dias < 1 || (int)$dias > MAX_DIAS_TRATAMIENTO) {
        $errores[] = 'Los días de tratamiento deben ser un número entero entre 1 y ' . MAX_DIAS_TRATAMIENTO . '.';
    }
    return $errores;
}

// ---------------------------------------------------------------- Ficha (Vista 2)

// Datos de la cita del medico y su atencion (si existe). null si la cita no es suya.
function cargarFicha(PDO $db, string $rutMedico, int $nroCita): ?array {
    $q = $db->prepare("SELECT c.Nro_Cita, c.Fecha_Cita, TIME_FORMAT(c.Hora_Cita, '%H:%i') AS Hora,
            e.Estado_Cita, cm.Nombre_Centro, esp.Nombre_Especialidad,
            p.RUT_Paciente, p.Nombre_Paciente, p.`Fecha-Nacimiento_Paciente` AS Nacimiento, p.Sexo_Paciente,
            m.RUT_Medico, m.Nombre_Medico, m.`E-mail_Medico` AS Email_Medico,
            a.Nro_Atencion, a.Motivo_Atencion, a.Observaciones_Atencion
        FROM cita c
        JOIN estado e ON e.ID_Estado = c.ID_Estado
        JOIN centro_medico cm ON cm.Codigo_Centro = c.Codigo_Centro
        JOIN especialidad esp ON esp.ID_Especialidad = c.ID_Especialidad
        JOIN medico m ON m.RUT_Medico = c.RUT_Medico
        LEFT JOIN paciente p ON p.RUT_Paciente = c.RUT_Paciente
        LEFT JOIN atencion a ON a.Nro_Cita = c.Nro_Cita
        WHERE c.Nro_Cita = ? AND c.RUT_Medico = ?");
    $q->execute([$nroCita, $rutMedico]);
    $ficha = $q->fetch(PDO::FETCH_ASSOC);
    if (!$ficha) {
        return null;
    }
    $ficha['diagnosticos'] = $ficha['Nro_Atencion'] ? diagnosticosAtencion($db, (int)$ficha['Nro_Atencion']) : [];
    $ficha['recetas'] = $ficha['Nro_Atencion'] ? recetasAtencion($db, (int)$ficha['Nro_Atencion']) : [];
    return $ficha;
}

function diagnosticosAtencion(PDO $db, int $nroAtencion): array {
    $q = $db->prepare('SELECT d.Codigo_Diagnostico, d.Descripcion_Diagnostico
        FROM atencion_diagnostico ad JOIN diagnostico d ON d.Codigo_Diagnostico = ad.Codigo_Diagnostico
        WHERE ad.Nro_Atencion = ? ORDER BY d.Codigo_Diagnostico');
    $q->execute([$nroAtencion]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

function recetasAtencion(PDO $db, int $nroAtencion): array {
    $q = $db->prepare('SELECT ID_Receta, Medicamento_Receta, Dosis_Receta, `Dias-Tratamiento_Receta` AS Dias
        FROM receta WHERE Nro_Atencion = ? ORDER BY ID_Receta');
    $q->execute([$nroAtencion]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

function catalogoDiagnosticos(PDO $db): array {
    return $db->query('SELECT Codigo_Diagnostico, Descripcion_Diagnostico FROM diagnostico
        ORDER BY Codigo_Diagnostico')->fetchAll(PDO::FETCH_ASSOC);
}

// ---------------------------------------------------------------- Atencion

function ejecutarTransaccion(PDO $db, callable $operacion) {
    try {
        $db->beginTransaction();
        $resultado = $operacion();
        $db->commit();
        return $resultado;
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        if ($error instanceof PDOException && (int)($error->errorInfo[1] ?? 0) === 1062) {
            throw new DomainException('Ese dato ya está registrado en la atención.');
        }
        throw errorCita($error);
    }
}

// Atencion del medico, bloqueada para modificarla dentro de la transaccion.
function atencionPropia(PDO $db, string $rutMedico, int $nroAtencion): int {
    $q = $db->prepare('SELECT a.Nro_Atencion FROM atencion a JOIN cita c ON c.Nro_Cita = a.Nro_Cita
        WHERE a.Nro_Atencion = ? AND c.RUT_Medico = ? FOR UPDATE');
    $q->execute([$nroAtencion, $rutMedico]);
    if (!$q->fetchColumn()) {
        throw new DomainException('La atención no existe o no fue registrada en una de tus citas.');
    }
    return $nroAtencion;
}

function crearAtencion(PDO $db, string $rutMedico, int $nroCita, string $motivo, string $observaciones): int {
    return ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroCita, $motivo, $observaciones) {
        $q = $db->prepare('SELECT e.Estado_Cita, a.Nro_Atencion FROM cita c
            JOIN estado e ON e.ID_Estado = c.ID_Estado
            LEFT JOIN atencion a ON a.Nro_Cita = c.Nro_Cita
            WHERE c.Nro_Cita = ? AND c.RUT_Medico = ? FOR UPDATE');
        $q->execute([$nroCita, $rutMedico]);
        $cita = $q->fetch(PDO::FETCH_ASSOC);
        if (!$cita) {
            throw new DomainException('La cita no existe o no pertenece a tu agenda.');
        }
        if ($cita['Nro_Atencion'] !== null) {
            throw new DomainException('Esta cita ya tiene una atención registrada; puedes editarla.');
        }
        if ($cita['Estado_Cita'] !== 'Atendida') {
            throw new DomainException('Solo se puede registrar la atención de una cita en estado Atendida (esta cita está '
                . $cita['Estado_Cita'] . '). Cambia su estado desde la agenda.');
        }
        // El trigger ValidarAtencion vuelve a comprobar el estado en la base de datos.
        $q = $db->prepare('INSERT INTO atencion (Motivo_Atencion, Observaciones_Atencion, Nro_Cita) VALUES (?, ?, ?)');
        $q->execute([$motivo, $observaciones, $nroCita]);
        return (int)$db->lastInsertId();
    });
}

function actualizarAtencion(PDO $db, string $rutMedico, int $nroAtencion, string $motivo, string $observaciones): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion, $motivo, $observaciones) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        $q = $db->prepare('UPDATE atencion SET Motivo_Atencion = ?, Observaciones_Atencion = ? WHERE Nro_Atencion = ?');
        $q->execute([$motivo, $observaciones, $nroAtencion]);
    });
}

// ON DELETE CASCADE elimina sus diagnosticos y recetas.
function eliminarAtencion(PDO $db, string $rutMedico, int $nroAtencion): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        $q = $db->prepare('DELETE FROM atencion WHERE Nro_Atencion = ?');
        $q->execute([$nroAtencion]);
    });
}

// ---------------------------------------------------------------- Diagnosticos

// Usa el codigo del catalogo; si es nuevo, lo agrega con la descripcion indicada.
function asegurarDiagnostico(PDO $db, string $codigo, string $descripcion): void {
    $q = $db->prepare('SELECT 1 FROM diagnostico WHERE Codigo_Diagnostico = ?');
    $q->execute([$codigo]);
    if ($q->fetchColumn()) {
        return;
    }
    if ($descripcion === '') {
        throw new DomainException("El código $codigo no está en el catálogo: ingresa su descripción para agregarlo.");
    }
    $q = $db->prepare('INSERT INTO diagnostico (Codigo_Diagnostico, Descripcion_Diagnostico) VALUES (?, ?)');
    $q->execute([$codigo, $descripcion]);
}

function agregarDiagnostico(PDO $db, string $rutMedico, int $nroAtencion, string $codigo, string $descripcion): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion, $codigo, $descripcion) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        asegurarDiagnostico($db, $codigo, $descripcion);
        $q = $db->prepare('SELECT 1 FROM atencion_diagnostico WHERE Nro_Atencion = ? AND Codigo_Diagnostico = ?');
        $q->execute([$nroAtencion, $codigo]);
        if ($q->fetchColumn()) {
            throw new DomainException("El diagnóstico $codigo ya está en esta atención.");
        }
        $q = $db->prepare('INSERT INTO atencion_diagnostico (Nro_Atencion, Codigo_Diagnostico) VALUES (?, ?)');
        $q->execute([$nroAtencion, $codigo]);
    });
}

// Editar un diagnostico de la atencion = reemplazar su codigo por otro.
function cambiarDiagnostico(PDO $db, string $rutMedico, int $nroAtencion, string $anterior, string $codigo,
                            string $descripcion): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion, $anterior, $codigo, $descripcion) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        if ($anterior === $codigo) {
            throw new DomainException('Elige un código distinto al actual.');
        }
        asegurarDiagnostico($db, $codigo, $descripcion);
        $q = $db->prepare('SELECT 1 FROM atencion_diagnostico WHERE Nro_Atencion = ? AND Codigo_Diagnostico = ?');
        $q->execute([$nroAtencion, $codigo]);
        if ($q->fetchColumn()) {
            throw new DomainException("El diagnóstico $codigo ya está en esta atención.");
        }
        $q = $db->prepare('UPDATE atencion_diagnostico SET Codigo_Diagnostico = ?
            WHERE Nro_Atencion = ? AND Codigo_Diagnostico = ?');
        $q->execute([$codigo, $nroAtencion, $anterior]);
        if ($q->rowCount() !== 1) {
            throw new DomainException("El diagnóstico $anterior ya no está en esta atención.");
        }
    });
}

function quitarDiagnostico(PDO $db, string $rutMedico, int $nroAtencion, string $codigo): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion, $codigo) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        $q = $db->prepare('DELETE FROM atencion_diagnostico WHERE Nro_Atencion = ? AND Codigo_Diagnostico = ?');
        $q->execute([$nroAtencion, $codigo]);
        if ($q->rowCount() !== 1) {
            throw new DomainException("El diagnóstico $codigo ya no está en esta atención.");
        }
    });
}

// ---------------------------------------------------------------- Recetas

function agregarReceta(PDO $db, string $rutMedico, int $nroAtencion, string $medicamento, string $dosis, int $dias): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion, $medicamento, $dosis, $dias) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        $q = $db->prepare('INSERT INTO receta (Medicamento_Receta, Dosis_Receta, `Dias-Tratamiento_Receta`, Nro_Atencion)
            VALUES (?, ?, ?, ?)');
        $q->execute([$medicamento, $dosis, $dias, $nroAtencion]);
    });
}

function editarReceta(PDO $db, string $rutMedico, int $nroAtencion, int $idReceta, string $medicamento,
                      string $dosis, int $dias): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion, $idReceta, $medicamento, $dosis, $dias) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        $q = $db->prepare('SELECT 1 FROM receta WHERE ID_Receta = ? AND Nro_Atencion = ?');
        $q->execute([$idReceta, $nroAtencion]);
        if (!$q->fetchColumn()) {
            throw new DomainException('La línea de receta ya no existe en esta atención.');
        }
        $q = $db->prepare('UPDATE receta SET Medicamento_Receta = ?, Dosis_Receta = ?, `Dias-Tratamiento_Receta` = ?
            WHERE ID_Receta = ?');
        $q->execute([$medicamento, $dosis, $dias, $idReceta]);
    });
}

function eliminarReceta(PDO $db, string $rutMedico, int $nroAtencion, int $idReceta): void {
    ejecutarTransaccion($db, function () use ($db, $rutMedico, $nroAtencion, $idReceta) {
        atencionPropia($db, $rutMedico, $nroAtencion);
        $q = $db->prepare('DELETE FROM receta WHERE ID_Receta = ? AND Nro_Atencion = ?');
        $q->execute([$idReceta, $nroAtencion]);
        if ($q->rowCount() !== 1) {
            throw new DomainException('La línea de receta ya no existe en esta atención.');
        }
    });
}

// ---------------------------------------------------------------- Historial

// Atenciones del paciente (de cualquier medico), de la mas reciente a la mas antigua.
function historialPaciente(PDO $db, string $rutPaciente): array {
    $q = $db->prepare("SELECT a.Nro_Atencion, a.Motivo_Atencion, a.Observaciones_Atencion, c.Nro_Cita,
            c.Fecha_Cita, TIME_FORMAT(c.Hora_Cita, '%H:%i') AS Hora, esp.Nombre_Especialidad,
            m.Nombre_Medico, cm.Nombre_Centro
        FROM atencion a
        JOIN cita c ON c.Nro_Cita = a.Nro_Cita
        JOIN especialidad esp ON esp.ID_Especialidad = c.ID_Especialidad
        JOIN medico m ON m.RUT_Medico = c.RUT_Medico
        JOIN centro_medico cm ON cm.Codigo_Centro = c.Codigo_Centro
        WHERE c.RUT_Paciente = ?
        ORDER BY c.Fecha_Cita DESC, c.Hora_Cita DESC");
    $q->execute([$rutPaciente]);
    $atenciones = $q->fetchAll(PDO::FETCH_ASSOC);
    foreach ($atenciones as &$atencion) {
        $atencion['diagnosticos'] = diagnosticosAtencion($db, (int)$atencion['Nro_Atencion']);
        $atencion['recetas'] = recetasAtencion($db, (int)$atencion['Nro_Atencion']);
    }
    return $atenciones;
}

// "Sus pacientes": pacientes con al menos una cita con el medico.
function pacientesDelMedico(PDO $db, string $rutMedico): array {
    $q = $db->prepare('SELECT p.RUT_Paciente, p.Nombre_Paciente, pr.Prevision_Paciente,
            MAX(c.Fecha_Cita) AS Ultima_Cita,
            (SELECT COUNT(*) FROM atencion a2 JOIN cita c2 ON c2.Nro_Cita = a2.Nro_Cita
             WHERE c2.RUT_Paciente = p.RUT_Paciente) AS Atenciones
        FROM cita c
        JOIN paciente p ON p.RUT_Paciente = c.RUT_Paciente
        JOIN prevision pr ON pr.ID_Prevision = p.ID_Prevision
        WHERE c.RUT_Medico = ?
        GROUP BY p.RUT_Paciente, p.Nombre_Paciente, pr.Prevision_Paciente
        ORDER BY p.Nombre_Paciente');
    $q->execute([$rutMedico]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

function pacienteDelMedico(PDO $db, string $rutMedico, string $rutPaciente): ?array {
    $q = $db->prepare('SELECT p.RUT_Paciente, p.Nombre_Paciente, p.`Fecha-Nacimiento_Paciente` AS Nacimiento,
            p.Sexo_Paciente, pr.Prevision_Paciente
        FROM paciente p JOIN prevision pr ON pr.ID_Prevision = p.ID_Prevision
        WHERE p.RUT_Paciente = ?
          AND EXISTS (SELECT 1 FROM cita c WHERE c.RUT_Paciente = p.RUT_Paciente AND c.RUT_Medico = ?)');
    $q->execute([$rutPaciente, $rutMedico]);
    $paciente = $q->fetch(PDO::FETCH_ASSOC);
    return $paciente ?: null;
}
