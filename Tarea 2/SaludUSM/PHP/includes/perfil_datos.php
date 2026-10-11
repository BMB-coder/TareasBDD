<?php
require_once __DIR__ . '/archivo_admin.php';

function perfilAdmin(array $admin): array {
    return ['id' => 'administrador', 'nombre' => $admin['nombre'], 'email' => $admin['email'],
        'hash' => $admin['contrasena_hash'], 'rol' => 'Administrador'];
}

function cargarPerfil(PDO $db, array $cuenta): array {
    if ($cuenta['rol'] === 'Administrador') {
        return perfilAdmin(leerArchivoAdmin());
    }
    if ($cuenta['rol'] === 'Paciente') {
        $sql = 'SELECT RUT_Paciente AS id, Nombre_Paciente AS nombre, Email_Paciente AS email,
            Contrasena_Hash AS hash, Telefono_Paciente AS telefono,
            `Fecha-Nacimiento_Paciente` AS nacimiento, Sexo_Paciente AS sexo, ID_Prevision AS prevision
            FROM paciente WHERE RUT_Paciente = ?';
    } elseif ($cuenta['rol'] === 'Medico') {
        $sql = 'SELECT RUT_Medico AS id, Nombre_Medico AS nombre,
            `E-mail_Medico` AS email, Contrasena_Hash AS hash FROM medico WHERE RUT_Medico = ?';
    } else {
        throw new RuntimeException('Rol de sesion invalido.');
    }
    $q = $db->prepare($sql);
    $q->execute([$cuenta['id']]);
    $perfil = $q->fetch(PDO::FETCH_ASSOC);
    if (!$perfil) {
        throw new RuntimeException('La cuenta ya no existe. Cierra sesion.');
    }
    $perfil['rol'] = $cuenta['rol'];
    return $perfil;
}

function versionPerfil(array $perfil): string {
    return hash_hmac('sha256', json_encode($perfil, JSON_THROW_ON_ERROR), $_SESSION['csrf']);
}

function erroresPerfil(array $datos, string $rol, string $clave, string $confirmar): array {
    $errores = [];
    $n = preg_match_all('/./us', $datos['nombre'], $letras);
    if ($datos['nombre'] === '' || $n === false || $n > 100) {
        $errores[] = 'El nombre es obligatorio y admite hasta 100 caracteres.';
    }
    $limiteCorreo = $rol === 'Medico' ? 100 : 255;
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || strlen($datos['email']) > $limiteCorreo) {
        $errores[] = 'Ingresa un correo válido de hasta ' . $limiteCorreo . ' caracteres.';
    }
    if ($rol === 'Paciente') {
        if ($datos['telefono'] !== '' && !preg_match('/^\+?[0-9]{8,10}$/D', $datos['telefono'])) {
            $errores[] = 'El teléfono admite 8 a 10 dígitos y un + opcional, sin espacios.';
        }
        $zona = new DateTimeZone('America/Santiago');
        $f = DateTimeImmutable::createFromFormat('!Y-m-d', $datos['nacimiento'], $zona);
        if (!$f || $f->format('Y-m-d') !== $datos['nacimiento'] || $f->format('Y') < '1000'
            || $f > new DateTimeImmutable('today', $zona)) {
            $errores[] = 'La fecha de nacimiento debe ser válida y no futura.';
        }
        if (!in_array($datos['sexo'], ['M', 'F', 'Otro'], true)) {
            $errores[] = 'Selecciona un sexo válido.';
        }
        if (!ctype_digit($datos['prevision']) || (int)$datos['prevision'] < 1) {
            $errores[] = 'Selecciona una previsión válida.';
        }
    }
    if ($clave !== '' || $confirmar !== '') {
        $n = preg_match_all('/./us', $clave, $letras);
        if ($n === false || $n < 8 || strlen($clave) > 72 || strpos($clave, "\0") !== false
            || !preg_match('/[A-Z]/', $clave) || !preg_match('/[a-z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres, mayúscula A-Z, minúscula a-z y número; máximo 72 bytes.';
        }
        if ($clave !== $confirmar) {
            $errores[] = 'La nueva contraseña y su confirmación no coinciden.';
        }
    }
    return $errores;
}

function comprobarCorreoPerfil(PDO $db, string $email, array $cuenta, array $admin): void {
    $consultas = [
        'Paciente' => 'SELECT RUT_Paciente FROM paciente WHERE LOWER(TRIM(Email_Paciente)) = ?',
        'Medico' => 'SELECT RUT_Medico FROM medico WHERE LOWER(TRIM(`E-mail_Medico`)) = ?'
    ];
    foreach ($consultas as $rol => $sql) {
        $q = $db->prepare($sql);
        $q->execute([$email]);
        foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $id) {
            if ($cuenta['rol'] !== $rol || (string)$cuenta['id'] !== (string)$id) {
                throw new DomainException('Ese correo ya está en uso por otra cuenta.');
            }
        }
    }
    if ($cuenta['rol'] !== 'Administrador' && strtolower(trim($admin['email'])) === $email) {
        throw new DomainException('Ese correo ya está en uso por otra cuenta.');
    }
}

function guardarPerfil(PDO $db, array $cuenta, array $datos, string $actual, string $nueva, string $version): array {
    $nombreBD = (string)$db->query('SELECT DATABASE()')->fetchColumn();
    // Mismo bloqueo que el registro de pacientes para evitar correos concurrentes.
    $bloqueo = 'saludusm_cuentas_' . substr(hash('sha256', $nombreBD), 0, 40);
    $q = $db->prepare('SELECT GET_LOCK(?, 5)');
    $q->execute([$bloqueo]);
    if ((int)$q->fetchColumn() !== 1) {
        throw new DomainException('Hay otra actualización en curso. Inténtalo nuevamente.');
    }
    $f = null;
    try {
        $esAdmin = $cuenta['rol'] === 'Administrador';
        $f = @fopen(ARCHIVO_ADMINISTRADOR, $esAdmin ? 'r+b' : 'rb');
        if (!$f) {
            throw new RuntimeException('No se pudo abrir el JSON privado con los permisos necesarios.');
        }
        bloquearArchivoAdmin($f, $esAdmin ? LOCK_EX : LOCK_SH);
        $original = stream_get_contents($f);
        if ($original === false) {
            throw new RuntimeException('No se pudo leer el JSON.');
        }
        $admin = decodificarAdmin($original);
        $perfil = $esAdmin ? perfilAdmin($admin) : cargarPerfil($db, $cuenta);
        if (!hash_equals(versionPerfil($perfil), $version)) {
            throw new DomainException('El perfil cambió desde que abriste el formulario. Recarga la página antes de editarlo.');
        }
        if ($actual === '' || strlen($actual) > 4096 || !password_verify($actual, (string)$perfil['hash'])) {
            throw new DomainException('La contraseña actual es incorrecta. No se guardaron cambios.');
        }
        comprobarCorreoPerfil($db, $datos['email'], $cuenta, $admin);
        $hash = $nueva === '' ? $perfil['hash'] : password_hash($nueva, PASSWORD_DEFAULT);
        if (!is_string($hash) || $hash === '') {
            throw new RuntimeException('No se pudo generar el hash.');
        }
        if ($esAdmin) {
            $admin['nombre'] = $datos['nombre'];
            $admin['email'] = $datos['email'];
            $admin['contrasena_hash'] = $hash;
            $nuevo = json_encode($admin, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            // Respaldo privado previo a cada cambio; nunca se guarda en htdocs.
            $respaldo = ARCHIVO_ADMINISTRADOR . '.bak';
            if (file_put_contents($respaldo, $original, LOCK_EX) !== strlen($original)) {
                throw new RuntimeException('No se pudo respaldar el JSON. No se aplicaron cambios.');
            }
            try {
                escribirCompletoAdmin($f, $nuevo);
            } catch (Throwable $error) {
                try { escribirCompletoAdmin($f, $original); }
                catch (Throwable $restauracion) { error_log('SaludUSM: restaurar administrador.json desde su .bak privado.'); }
                throw $error;
            }
            return perfilAdmin($admin);
        }
        $db->beginTransaction();
        if ($cuenta['rol'] === 'Paciente') {
            $q = $db->prepare('SELECT 1 FROM prevision WHERE ID_Prevision = ?');
            $q->execute([$datos['prevision']]);
            if (!$q->fetchColumn()) {
                throw new DomainException('La previsión seleccionada no existe.');
            }
            $q = $db->prepare('UPDATE paciente SET Nombre_Paciente = ?, Email_Paciente = ?,
                Telefono_Paciente = ?, `Fecha-Nacimiento_Paciente` = ?, Sexo_Paciente = ?,
                ID_Prevision = ?, Contrasena_Hash = ? WHERE RUT_Paciente = ?');
            $q->execute([$datos['nombre'], $datos['email'], $datos['telefono'] !== '' ? $datos['telefono'] : null,
                $datos['nacimiento'], $datos['sexo'], $datos['prevision'], $hash, $cuenta['id']]);
        } elseif ($cuenta['rol'] === 'Medico') {
            $q = $db->prepare('UPDATE medico SET Nombre_Medico = ?, `E-mail_Medico` = ?,
                Contrasena_Hash = ? WHERE RUT_Medico = ?');
            $q->execute([$datos['nombre'], $datos['email'], $hash, $cuenta['id']]);
        } else {
            throw new RuntimeException('Rol invalido.');
        }
        $resultado = cargarPerfil($db, $cuenta);
        $db->commit();
        return $resultado;
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        if ($error instanceof PDOException && (int)($error->errorInfo[1] ?? 0) === 1062) {
            throw new DomainException('El correo ya está registrado. No se guardaron cambios.');
        }
        throw $error;
    } finally {
        if (is_resource($f)) { flock($f, LOCK_UN); fclose($f); }
        $q = $db->prepare('SELECT RELEASE_LOCK(?)');
        $q->execute([$bloqueo]);
    }
}
