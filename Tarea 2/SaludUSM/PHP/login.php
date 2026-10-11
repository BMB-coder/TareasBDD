<?php
require_once __DIR__ . '/includes/autenticacion.php';
if (!empty($_SESSION['cuenta'])) {
    header('Location: ' . destinoRol($_SESSION['cuenta']['rol']));
    exit;
}
$mensaje = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
    $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
    if (!csrfValido()) {
        $mensaje = 'El formulario venció. Recarga la página e inténtalo nuevamente.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $clave === '' || strlen($clave) > 4096) {
        $mensaje = 'Correo o contraseña incorrectos.';
    } else {
        try {
            require __DIR__ . '/config/conexion.php';
            $cuenta = buscarCuenta($conexion, $email);
            // Hash de relleno para verificar tambien cuando el correo no existe.
            $relleno = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
            $hash = $cuenta['hash'] ?? '';
            $valida = password_verify($clave, $hash !== '' ? $hash : $relleno);
            if ($cuenta !== null && $hash !== '' && $valida) {
                session_regenerate_id(true);
                $_SESSION['firma_cuenta'] = hash('sha256', $hash);
                unset($cuenta['hash']);
                $_SESSION['cuenta'] = $cuenta;
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                header('Location: ' . destinoRol($cuenta['rol']));
                exit;
            }
            $mensaje = 'Correo o contraseña incorrectos.';
        } catch (Throwable $error) {
            error_log('SaludUSM login: ' . $error->getMessage());
            $mensaje = 'No fue posible verificar el acceso. Revisa MySQL y la configuración del administrador.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | SaludUSM</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
</head>
<body><main>
    <h1>Iniciar sesión</h1>
    <?php if ($mensaje !== ''): ?><p class="error" role="alert"><?= escapar($mensaje) ?></p><?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
        <p><label for="email">Correo electrónico</label><br>
        <input id="email" name="email" type="email" autocomplete="username" required
               maxlength="255" value="<?= escapar($email) ?>"></p>
        <p><label for="clave">Contraseña</label><br>
        <input id="clave" name="clave" type="password" autocomplete="current-password" required></p>
        <button class="boton" type="submit">Entrar</button>
    </form>
    <p>¿No tienes cuenta? <a href="registro.php">Registrarme como paciente</a></p>
    <p><a href="index.php">Volver al inicio</a></p>
</main></body></html>
