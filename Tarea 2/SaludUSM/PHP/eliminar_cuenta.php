<?php
require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/includes/baja_paciente.php';
$cuenta = exigirRol('Paciente');
$errores = [];
$bajaCompletada = false;
$eliminadas = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
    $confirmacion = is_string($_POST['confirmacion'] ?? null) ? $_POST['confirmacion'] : '';
    if (!csrfValido()) { $errores[] = 'El formulario venció. Recarga la página.'; }
    if ($confirmacion !== 'ELIMINAR') { $errores[] = 'Escribe ELIMINAR para confirmar la baja de tu cuenta.'; }
    if (!$errores) {
        try {
            require __DIR__ . '/config/conexion.php';
            // Identidad tomada exclusivamente de la sesion, nunca del formulario.
            $eliminadas = darBajaPaciente($conexion, (string)$cuenta['id'], $clave);
            destruirSesionSalud();
            $bajaCompletada = true;
        } catch (DomainException $error) {
            $errores[] = $error->getMessage();
        } catch (Throwable $error) {
            error_log('SaludUSM baja: ' . $error->getMessage());
            $errores[] = 'No fue posible completar la baja. Revisa el log de PHP y la configuración de la base.';
        }
    }
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Eliminar mi cuenta | SaludUSM</title><link rel="stylesheet" href="../CSS/estilos.css">
</head><body><main>
<?php if ($bajaCompletada): ?>
<h1>Cuenta eliminada</h1>
<p class="exito" role="status">Se eliminó tu registro de paciente y se cerró tu sesión.</p>
<p>Citas futuras eliminadas: <?= (int)$eliminadas ?>.</p>
<p>Las citas conservadas y sus atenciones permanecen guardadas sin vínculo con tu antiguo RUT.</p>
<p><a href="login.php">Ir al login</a> · <a href="index.php">Inicio</a></p>
<?php else: ?>
<h1>Eliminar mi cuenta</h1>
<p><a href="perfil.php">Volver a mi perfil</a></p>
<p>Cuenta: <?= escapar($cuenta['nombre']) ?> — RUT <?= escapar($cuenta['id']) ?></p>
<ul>
<li>Se eliminará completamente tu fila de paciente, incluidos los datos personales y las credenciales que contiene.</li>
<li>Se borrarán tus citas futuras Reservadas o Confirmadas que no tengan una atención registrada.</li>
<li>Se conservarán las demás citas, atenciones, diagnósticos y recetas, desvinculadas de tu RUT. El texto clínico no se anonimiza automáticamente.</li>
<li>Podrás registrarte de nuevo con el mismo RUT, pero no recuperarás el vínculo con el historial anterior.</li>
</ul>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>
<form method="post" action="eliminar_cuenta.php">
<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
<p><label for="clave">Contraseña actual</label><br><input id="clave" name="clave" type="password" required autocomplete="current-password"></p>
<p><label for="confirmacion">Escribe ELIMINAR para confirmar</label><br><input id="confirmacion" name="confirmacion" required autocomplete="off"></p>
<button type="submit">Eliminar mi cuenta</button>
</form>
<?php endif; ?>
</main></body></html>
