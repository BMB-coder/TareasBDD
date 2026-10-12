<?php
// Reglas del administrador: CRUD de centros medicos y de medicos con sus asignaciones.
// No genera salida HTML. Los errores para el usuario se lanzan como DomainException.
require_once __DIR__ . '/archivo_admin.php';

const REGIONES = [
    'AP' => 'Arica y Parinacota', 'TA' => 'Tarapacá', 'AN' => 'Antofagasta', 'AT' => 'Atacama',
    'CO' => 'Coquimbo', 'VA' => 'Valparaíso', 'RM' => 'Metropolitana', 'LI' => "O'Higgins",
    'ML' => 'Maule', 'NB' => 'Ñuble', 'BI' => 'Biobío', 'AR' => 'La Araucanía',
    'LR' => 'Los Ríos', 'LL' => 'Los Lagos', 'AI' => 'Aysén', 'MA' => 'Magallanes'
];
const MAX_ESPECIALIDADES = 3;

function largoTexto(string $texto): int {
    $n = preg_match_all('/./us', $texto, $letras);
    return $n === false ? -1 : $n;
}

// Lista de IDs enviada por checkboxes: solo enteros positivos, sin repetir.
function idsFormulario($valor): array {
    if (!is_array($valor)) {
        return [];
    }
    $ids = [];
    foreach ($valor as $id) {
        if (is_string($id) && ctype_digit($id) && (int)$id > 0) {
            $ids[(int)$id] = (int)$id;
        }
    }
    return array_values($ids);
}

// ---------------------------------------------------------------- Centros

function validarCentro(array $datos): array {
    $errores = [];
    $n = largoTexto($datos['nombre']);
    if ($n < 1 || $n > 100) {
        $errores[] = 'El nombre del centro es obligatorio y admite hasta 100 caracteres.';
    }
    $n = largoTexto($datos['comuna']);
    if ($n < 1 || $n > 100) {
        $errores[] = 'La comuna es obligatoria y admite hasta 100 caracteres.';
    }
    if (!isset(REGIONES[$datos['region']])) {
        $errores[] = 'Selecciona una región válida.';
    }
    return $errores;
}

function listarCentros(PDO $db): array {
    return $db->query('SELECT cm.Codigo_Centro, cm.Nombre_Centro, cm.Comuna_Centro, cm.Region_Centro,
            (SELECT COUNT(*) FROM centro_medico_medico x WHERE x.Codigo_Centro = cm.Codigo_Centro) AS Medicos,
            (SELECT COUNT(*) FROM cita c WHERE c.Codigo_Centro = cm.Codigo_Centro) AS Citas
        FROM centro_medico cm ORDER BY cm.Codigo_Centro')->fetchAll(PDO::FETCH_ASSOC);
}

function cargarCentro(PDO $db, int $codigo): ?array {
    $q = $db->prepare('SELECT Codigo_Centro, Nombre_Centro, Comuna_Centro, Region_Centro
        FROM centro_medico WHERE Codigo_Centro = ?');
    $q->execute([$codigo]);
    $centro = $q->fetch(PDO::FETCH_ASSOC);
    return $centro ?: null;
}

function crearCentro(PDO $db, array $datos): int {
    $q = $db->prepare('INSERT INTO centro_medico (Nombre_Centro, Comuna_Centro, Region_Centro) VALUES (?, ?, ?)');
    $q->execute([$datos['nombre'], $datos['comuna'], $datos['region']]);
    return (int)$db->lastInsertId();
}

function actualizarCentro(PDO $db, int $codigo, array $datos): void {
    if (!cargarCentro($db, $codigo)) {
        throw new DomainException('El centro ya no existe.');
    }
    $q = $db->prepare('UPDATE centro_medico SET Nombre_Centro = ?, Comuna_Centro = ?, Region_Centro = ?
        WHERE Codigo_Centro = ?');
    $q->execute([$datos['nombre'], $datos['comuna'], $datos['region'], $codigo]);
}

function eliminarCentro(PDO $db, int $codigo): void {
    $db->beginTransaction();
    try {
        $q = $db->prepare('SELECT Nombre_Centro FROM centro_medico WHERE Codigo_Centro = ? FOR UPDATE');
        $q->execute([$codigo]);
        $nombre = $q->fetchColumn();
        if ($nombre === false) {
            throw new DomainException('El centro ya no existe.');
        }
        // Las citas (incluidas las historicas) conservan el centro: no se borra.
        $q = $db->prepare('SELECT COUNT(*) FROM cita WHERE Codigo_Centro = ?');
        $q->execute([$codigo]);
        $citas = (int)$q->fetchColumn();
        if ($citas > 0) {
            throw new DomainException("No se puede eliminar $nombre: tiene $citas citas registradas (incluido el historial). Puedes editar sus datos.");
        }
        // Cada medico debe seguir atendiendo en al menos un centro.
        $q = $db->prepare('SELECT m.Nombre_Medico FROM centro_medico_medico cmm
            JOIN medico m ON m.RUT_Medico = cmm.RUT_Medico
            WHERE cmm.Codigo_Centro = ?
              AND (SELECT COUNT(*) FROM centro_medico_medico x WHERE x.RUT_Medico = cmm.RUT_Medico) = 1');
        $q->execute([$codigo]);
        $unicos = $q->fetchAll(PDO::FETCH_COLUMN);
        if ($unicos) {
            throw new DomainException("No se puede eliminar $nombre: es el único centro de " . implode(', ', $unicos)
                . '. Asígnales otro centro primero.');
        }
        // ON DELETE CASCADE quita las asignaciones de medicos a este centro.
        $q = $db->prepare('DELETE FROM centro_medico WHERE Codigo_Centro = ?');
        $q->execute([$codigo]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw $error;
    }
}

// ---------------------------------------------------------------- Medicos

function validarMedico(array $datos, bool $nuevo, string $clave, string $confirmacion,
                       array $especialidades, array $centros): array {
    $errores = [];
    if ($nuevo && !preg_match('/^[0-9]{8}-[0-9K]$/D', $datos['rut'])) {
        $errores[] = 'El RUT debe tener formato XXXXXXXX-X, sin puntos (ejemplo: 12345678-5).';
    }
    $n = largoTexto($datos['nombre']);
    if ($n < 1 || $n > 100) {
        $errores[] = 'El nombre es obligatorio y admite hasta 100 caracteres.';
    }
    if (strlen($datos['email']) > 100 || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresa un correo válido de hasta 100 caracteres.';
    }
    // Misma politica que el registro de pacientes. Al editar, vacia = se conserva.
    if ($nuevo || $clave !== '' || $confirmacion !== '') {
        $n = largoTexto($clave);
        if ($n < 8 || strlen($clave) > 72 || strpos($clave, "\0") !== false
            || !preg_match('/[A-Z]/', $clave) || !preg_match('/[a-z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres y como máximo 72 bytes, con mayúscula A-Z, minúscula a-z y número.';
        }
        if ($clave !== $confirmacion) {
            $errores[] = 'Las contraseñas no coinciden.';
        }
    }
    if (count($especialidades) < 1 || count($especialidades) > MAX_ESPECIALIDADES) {
        $errores[] = 'Selecciona entre 1 y ' . MAX_ESPECIALIDADES . ' especialidades.';
    }
    if (count($centros) < 1) {
        $errores[] = 'Selecciona al menos un centro donde atiende.';
    }
    return $errores;
}

function listarMedicos(PDO $db): array {
    return $db->query("SELECT m.RUT_Medico, m.Nombre_Medico, m.`E-mail_Medico` AS Email,
            (SELECT GROUP_CONCAT(e.Nombre_Especialidad ORDER BY e.Nombre_Especialidad SEPARATOR ', ')
             FROM medico_especialidad me JOIN especialidad e ON e.ID_Especialidad = me.ID_Especialidad
             WHERE me.RUT_Medico = m.RUT_Medico) AS Especialidades,
            (SELECT GROUP_CONCAT(cm.Nombre_Centro ORDER BY cm.Nombre_Centro SEPARATOR ', ')
             FROM centro_medico_medico cmm JOIN centro_medico cm ON cm.Codigo_Centro = cmm.Codigo_Centro
             WHERE cmm.RUT_Medico = m.RUT_Medico) AS Centros,
            (SELECT COUNT(*) FROM cita c JOIN estado s ON s.ID_Estado = c.ID_Estado
             WHERE c.RUT_Medico = m.RUT_Medico AND s.Estado_Cita IN ('Reservada', 'Confirmada')
               AND c.Fecha_Cita >= CURDATE()) AS Citas_Futuras
        FROM medico m ORDER BY m.Nombre_Medico")->fetchAll(PDO::FETCH_ASSOC);
}

function cargarMedico(PDO $db, string $rut): ?array {
    $q = $db->prepare('SELECT RUT_Medico, Nombre_Medico, `E-mail_Medico` AS Email FROM medico WHERE RUT_Medico = ?');
    $q->execute([$rut]);
    $medico = $q->fetch(PDO::FETCH_ASSOC);
    if (!$medico) {
        return null;
    }
    $q = $db->prepare('SELECT ID_Especialidad FROM medico_especialidad WHERE RUT_Medico = ?');
    $q->execute([$rut]);
    $medico['especialidades'] = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    $q = $db->prepare('SELECT Codigo_Centro FROM centro_medico_medico WHERE RUT_Medico = ?');
    $q->execute([$rut]);
    $medico['centros'] = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    return $medico;
}

// Mismo bloqueo que el registro y la edicion de perfiles: correos unicos entre roles.
function bloquearCuentas(PDO $db): string {
    $base = (string)$db->query('SELECT DATABASE()')->fetchColumn();
    $bloqueo = 'saludusm_cuentas_' . substr(hash('sha256', $base), 0, 40);
    $q = $db->prepare('SELECT GET_LOCK(?, 5)');
    $q->execute([$bloqueo]);
    if ((int)$q->fetchColumn() !== 1) {
        throw new DomainException('Hay otra operación de cuentas en curso. Inténtalo nuevamente.');
    }
    return $bloqueo;
}

function comprobarCorreoMedico(PDO $db, string $email, ?string $rutPropio): void {
    $q = $db->prepare('SELECT 1 FROM paciente WHERE LOWER(TRIM(Email_Paciente)) = ? LIMIT 1');
    $q->execute([$email]);
    $enUso = (bool)$q->fetchColumn();
    $q = $db->prepare('SELECT RUT_Medico FROM medico WHERE LOWER(TRIM(`E-mail_Medico`)) = ?');
    $q->execute([$email]);
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $rut) {
        $enUso = $enUso || $rut !== $rutPropio;
    }
    $admin = leerArchivoAdminOpcional();
    if ($enUso || ($admin !== null && strtolower(trim($admin['email'])) === $email)) {
        throw new DomainException('Ese correo ya está en uso por otra cuenta.');
    }
}

function comprobarCatalogo(PDO $db, string $tabla, string $campo, array $ids, string $mensaje): void {
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $q = $db->prepare("SELECT COUNT(*) FROM `$tabla` WHERE `$campo` IN ($marcas)");
    $q->execute($ids);
    if ((int)$q->fetchColumn() !== count($ids)) {
        throw new DomainException($mensaje);
    }
}

// Traduce los rechazos de la BD al quitar una asignacion.
// 45000: triggers ProtegerEspecialidadMedico / ProtegerCentroMedico (citas futuras).
// 1451: la FK de cita impide borrar asignaciones usadas por citas pasadas (historial).
function errorAsignacion(PDOException $error, string $asignacion): Throwable {
    if (($error->errorInfo[0] ?? '') === '45000') {
        return new DomainException(($error->errorInfo[2] ?? 'Cambio rechazado') . " ($asignacion).");
    }
    if ((int)($error->errorInfo[1] ?? 0) === 1451) {
        return new DomainException("No se puede quitar $asignacion: el médico tiene citas pasadas con esa asignación y se conservan en el historial.");
    }
    return $error;
}

function sincronizarAsignaciones(PDO $db, string $rut, string $tabla, string $campo, array $nuevos,
                                 string $catalogo, string $nombreCampo, string $etiqueta): void {
    $q = $db->prepare("SELECT `$campo` FROM `$tabla` WHERE RUT_Medico = ?");
    $q->execute([$rut]);
    $actuales = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    $borrar = $db->prepare("DELETE FROM `$tabla` WHERE RUT_Medico = ? AND `$campo` = ?");
    $nombre = $db->prepare("SELECT `$nombreCampo` FROM `$catalogo` WHERE `$campo` = ?");
    foreach (array_diff($actuales, $nuevos) as $id) {
        try {
            $borrar->execute([$rut, $id]);
        } catch (PDOException $error) {
            $nombre->execute([$id]);
            throw errorAsignacion($error, $etiqueta . ' ' . $nombre->fetchColumn());
        }
    }
    $agregar = $db->prepare("INSERT INTO `$tabla` (RUT_Medico, `$campo`) VALUES (?, ?)");
    foreach (array_diff($nuevos, $actuales) as $id) {
        $agregar->execute([$rut, $id]);
    }
}

// Crea ($rutExistente null) o actualiza un medico y sus asignaciones en una transaccion.
function guardarMedico(PDO $db, ?string $rutExistente, array $datos, string $clave,
                       array $especialidades, array $centros): string {
    $bloqueo = bloquearCuentas($db);
    try {
        $db->beginTransaction();
        $rut = $rutExistente ?? $datos['rut'];
        comprobarCatalogo($db, 'especialidad', 'ID_Especialidad', $especialidades,
            'Alguna especialidad seleccionada no existe. Recarga el formulario.');
        comprobarCatalogo($db, 'centro_medico', 'Codigo_Centro', $centros,
            'Algún centro seleccionado no existe. Recarga el formulario.');
        comprobarCorreoMedico($db, $datos['email'], $rutExistente);
        if ($rutExistente === null) {
            $q = $db->prepare('SELECT 1 FROM medico WHERE RUT_Medico = ?');
            $q->execute([$rut]);
            if ($q->fetchColumn()) {
                throw new DomainException('Ya existe un médico con ese RUT.');
            }
            $q = $db->prepare('INSERT INTO medico (RUT_Medico, Nombre_Medico, `E-mail_Medico`, Contrasena_Hash)
                VALUES (?, ?, ?, ?)');
            $q->execute([$rut, $datos['nombre'], $datos['email'], password_hash($clave, PASSWORD_DEFAULT)]);
        } else {
            $q = $db->prepare('SELECT 1 FROM medico WHERE RUT_Medico = ? FOR UPDATE');
            $q->execute([$rut]);
            if (!$q->fetchColumn()) {
                throw new DomainException('El médico ya no existe.');
            }
            if ($clave === '') {
                $q = $db->prepare('UPDATE medico SET Nombre_Medico = ?, `E-mail_Medico` = ? WHERE RUT_Medico = ?');
                $q->execute([$datos['nombre'], $datos['email'], $rut]);
            } else {
                $q = $db->prepare('UPDATE medico SET Nombre_Medico = ?, `E-mail_Medico` = ?, Contrasena_Hash = ?
                    WHERE RUT_Medico = ?');
                $q->execute([$datos['nombre'], $datos['email'], password_hash($clave, PASSWORD_DEFAULT), $rut]);
            }
        }
        sincronizarAsignaciones($db, $rut, 'medico_especialidad', 'ID_Especialidad', $especialidades,
            'especialidad', 'Nombre_Especialidad', 'la especialidad');
        sincronizarAsignaciones($db, $rut, 'centro_medico_medico', 'Codigo_Centro', $centros,
            'centro_medico', 'Nombre_Centro', 'el centro');
        $db->commit();
        return $rut;
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        if ($error instanceof PDOException && (int)($error->errorInfo[1] ?? 0) === 1062) {
            throw new DomainException('El RUT o el correo ya están registrados.');
        }
        throw $error;
    } finally {
        $q = $db->prepare('SELECT RELEASE_LOCK(?)');
        $q->execute([$bloqueo]);
    }
}

function eliminarMedico(PDO $db, string $rut): void {
    $db->beginTransaction();
    try {
        $q = $db->prepare('SELECT Nombre_Medico FROM medico WHERE RUT_Medico = ? FOR UPDATE');
        $q->execute([$rut]);
        $nombre = $q->fetchColumn();
        if ($nombre === false) {
            throw new DomainException('El médico ya no existe.');
        }
        // Las citas (incluidas las historicas) y sus atenciones necesitan al medico.
        $q = $db->prepare('SELECT COUNT(*) FROM cita WHERE RUT_Medico = ?');
        $q->execute([$rut]);
        $citas = (int)$q->fetchColumn();
        if ($citas > 0) {
            throw new DomainException("No se puede eliminar a $nombre: tiene $citas citas registradas (incluido el historial).");
        }
        // ON DELETE CASCADE quita sus especialidades y centros.
        $q = $db->prepare('DELETE FROM medico WHERE RUT_Medico = ?');
        $q->execute([$rut]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw $error;
    }
}
