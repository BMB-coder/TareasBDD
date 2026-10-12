<?php
// Reglas de registro. No genera salida HTML ni modifica el esquema.
function validarRegistroPaciente(array $datos, string $clave, string $confirmacion): array {
    $errores = [];
    if (!preg_match('/^[0-9]{8}-[0-9K]$/D', $datos['rut'])) {
        $errores[] = 'El RUT debe tener formato XXXXXXXX-X, sin puntos (ejemplo: 12345678-5).';
    }
    $largo = preg_match_all('/./us', $datos['nombre'], $coincidencias);
    if ($datos['nombre'] === '' || $largo === false || $largo > 100) {
        $errores[] = 'Ingresa un nombre de entre 1 y 100 caracteres.';
    }
    if (strlen($datos['email']) > 255 || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresa un correo electrónico válido de hasta 255 caracteres.';
    }
    if ($datos['telefono'] !== '' && !preg_match('/^\+?[0-9]{8,10}$/D', $datos['telefono'])) {
        $errores[] = 'El teléfono debe tener entre 8 y 10 dígitos, con un + opcional al inicio, sin espacios.';
    }
    $zona = new DateTimeZone('America/Santiago');
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $datos['nacimiento'], $zona);
    if (!$fecha || $fecha->format('Y-m-d') !== $datos['nacimiento']
        || $fecha->format('Y') < '1000' || $fecha > new DateTimeImmutable('today', $zona)) {
        $errores[] = 'Ingresa una fecha de nacimiento válida que no sea futura.';
    }
    if (!in_array($datos['sexo'], ['M', 'F', 'Otro'], true)) {
        $errores[] = 'Selecciona una opción válida para sexo.';
    }
    if (!ctype_digit($datos['prevision']) || (int)$datos['prevision'] < 1) {
        $errores[] = 'Selecciona una previsión.';
    }
    // Limite conservador compatible con bcrypt: no aceptar truncamiento silencioso.
    $largoClave = preg_match_all('/./us', $clave, $caracteres);
    if ($largoClave === false || $largoClave < 8 || strlen($clave) > 72 || strpos($clave, "\0") !== false
        || !preg_match('/[A-Z]/', $clave) || !preg_match('/[a-z]/', $clave)
        || !preg_match('/[0-9]/', $clave)) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres y como máximo 72 bytes, con mayúscula A-Z, minúscula a-z y número.';
    }
    if ($clave !== $confirmacion) {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    return $errores;
}

function crearPacienteRegistrado(PDO $db, array $datos, string $clave): void {
    // Los futuros cambios de correo/altas de medicos deben usar el mismo bloqueo.
    $base = (string)$db->query('SELECT DATABASE()')->fetchColumn();
    $bloqueo = 'saludusm_cuentas_' . substr(hash('sha256', $base), 0, 40);
    $q = $db->prepare('SELECT GET_LOCK(?, 5)');
    $q->execute([$bloqueo]);
    if ((int)$q->fetchColumn() !== 1) {
        throw new DomainException('Hay otro registro en curso. Inténtalo nuevamente.');
    }
    $archivo = null;
    try {
        // Lectura protegida del JSON. Toda futura escritura debe usar LOCK_EX.
        // Sin JSON no hay administrador con quien compartir el correo.
        $admin = null;
        if (is_file(ARCHIVO_ADMINISTRADOR)) {
            $archivo = @fopen(ARCHIVO_ADMINISTRADOR, 'rb');
            if ($archivo === false || !flock($archivo, LOCK_SH)) {
                throw new RuntimeException('No se puede leer el archivo privado del administrador.');
            }
            $admin = json_decode(stream_get_contents($archivo), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($admin) || !isset($admin['email']) || !is_string($admin['email'])
                || !filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Correo del administrador ausente o invalido.');
            }
        }
        $db->beginTransaction();
        $q = $db->prepare('SELECT 1 FROM paciente WHERE RUT_Paciente = ? LIMIT 1');
        $q->execute([$datos['rut']]);
        if ($q->fetchColumn()) {
            throw new DomainException('Ese RUT ya está registrado como paciente. No se modificó la cuenta existente.');
        }
        foreach (['SELECT 1 FROM paciente WHERE LOWER(TRIM(Email_Paciente)) = ? LIMIT 1',
                  'SELECT 1 FROM medico WHERE LOWER(TRIM(`E-mail_Medico`)) = ? LIMIT 1'] as $sql) {
            $q = $db->prepare($sql);
            $q->execute([$datos['email']]);
            if ($q->fetchColumn()) {
                throw new DomainException('Ese correo ya está en uso. Utiliza otro correo.');
            }
        }
        if ($admin !== null && strtolower(trim($admin['email'])) === $datos['email']) {
            throw new DomainException('Ese correo ya está en uso. Utiliza otro correo.');
        }
        $q = $db->prepare('SELECT 1 FROM prevision WHERE ID_Prevision = ?');
        $q->execute([$datos['prevision']]);
        if (!$q->fetchColumn()) {
            throw new DomainException('La previsión seleccionada no existe. Recarga el formulario.');
        }
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new RuntimeException('No se pudo generar el hash.');
        }
        $q = $db->prepare('INSERT INTO paciente
            (RUT_Paciente, Nombre_Paciente, Telefono_Paciente, `Fecha-Nacimiento_Paciente`,
             Sexo_Paciente, ID_Prevision, Email_Paciente, Contrasena_Hash)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $q->execute([$datos['rut'], $datos['nombre'], $datos['telefono'] !== '' ? $datos['telefono'] : null,
            $datos['nacimiento'], $datos['sexo'], $datos['prevision'], $datos['email'], $hash]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        if ($error instanceof PDOException && (int)($error->errorInfo[1] ?? 0) === 1062) {
            throw new DomainException('El RUT o correo ya está registrado. No se creó una cuenta duplicada.');
        }
        throw $error;
    } finally {
        if (is_resource($archivo)) {
            flock($archivo, LOCK_UN);
            fclose($archivo);
        }
        $q = $db->prepare('SELECT RELEASE_LOCK(?)');
        $q->execute([$bloqueo]);
    }
}
