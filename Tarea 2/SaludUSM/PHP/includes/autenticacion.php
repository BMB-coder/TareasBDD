<?php
require_once __DIR__ . '/../config/acceso.php';
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('SALUDUSM_SESION');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => BASE_ACCESO . '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
header('Cache-Control: no-store');
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function escapar($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function destinoRol(string $rol): string {
    $rutas = ['Paciente' => '/paciente/index.php', 'Medico' => '/medico/index.php',
              'Administrador' => '/admin/index.php'];
    return BASE_ACCESO . ($rutas[$rol] ?? '/login.php');
}

function csrfValido(): bool {
    return isset($_POST['csrf']) && is_string($_POST['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function exigirRol(string $rol): array {
    if (empty($_SESSION['cuenta'])) {
        header('Location: ' . BASE_ACCESO . '/login.php');
        exit;
    }
    if ($_SESSION['cuenta']['rol'] !== $rol) {
        http_response_code(403);
        echo '<!doctype html><html lang="es"><meta charset="UTF-8"><title>Acceso denegado</title>';
        echo '<h1>Acceso denegado</h1><p>Tu cuenta no tiene permiso para abrir esta página.</p>';
        echo '<a href="' . escapar(destinoRol($_SESSION['cuenta']['rol'])) . '">Volver a mi panel</a></html>';
        exit;
    }
    return $_SESSION['cuenta'];
}

function buscarCuenta(PDO $db, string $email): ?array {
    // Se consultan las tres fuentes. Correos ambiguos no pueden iniciar sesion.
    $cuentas = [];
    $consultas = [
        'Paciente' => 'SELECT RUT_Paciente AS id, Nombre_Paciente AS nombre,
            Email_Paciente AS email, Contrasena_Hash AS hash
            FROM paciente WHERE LOWER(TRIM(Email_Paciente)) = ?',
        'Medico' => 'SELECT RUT_Medico AS id, Nombre_Medico AS nombre,
            `E-mail_Medico` AS email, Contrasena_Hash AS hash
            FROM medico WHERE LOWER(TRIM(`E-mail_Medico`)) = ?'
    ];
    foreach ($consultas as $rol => $sql) {
        $q = $db->prepare($sql);
        $q->execute([$email]);
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $fila['rol'] = $rol;
            $cuentas[] = $fila;
        }
    }
    require_once __DIR__ . '/archivo_admin.php';
    $admin = leerArchivoAdmin();
    if (strtolower(trim($admin['email'])) === $email) {
        $cuentas[] = ['id' => 'administrador', 'nombre' => $admin['nombre'],
            'email' => $admin['email'], 'hash' => $admin['contrasena_hash'],
            'rol' => 'Administrador'];
    }
    return count($cuentas) === 1 ? $cuentas[0] : null;
}

function destruirSesionSalud(): void {
    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600, 'path' => $cookie['path'],
        'secure' => $cookie['secure'], 'httponly' => true, 'samesite' => 'Lax'
    ]);
    session_destroy();
}

// Rechazar tambien sesiones de paciente abiertas antes de la baja en otro navegador.
if (($_SESSION['cuenta']['rol'] ?? '') === 'Paciente') {
    try {
        // Ambito aislado para no alterar la conexion que abra cada pagina.
        $activa = (function (string $rut): bool {
            require __DIR__ . '/../config/conexion.php';
            $q = $conexion->prepare('SELECT Contrasena_Hash FROM paciente WHERE RUT_Paciente = ?');
            $q->execute([$rut]);
            $hash = $q->fetchColumn();
            return is_string($hash) && $hash !== ''
                && hash_equals(hash('sha256', $hash), (string)($_SESSION['firma_cuenta'] ?? ''));
        })((string)$_SESSION['cuenta']['id']);
    } catch (Throwable $error) {
        error_log('SaludUSM sesion: ' . $error->getMessage());
        http_response_code(503);
        exit('No se pudo verificar la cuenta. Revisa MySQL e intentalo nuevamente.');
    }
    if (!$activa) {
        destruirSesionSalud();
        header('Location: ' . BASE_ACCESO . '/login.php');
        exit;
    }
}
