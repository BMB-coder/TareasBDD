<?php
require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/includes/perfil_datos.php';
if (empty($_SESSION['cuenta'])) {
    header('Location: ' . BASE_ACCESO . '/login.php');
    exit;
}
$cuenta = $_SESSION['cuenta'];
if (!in_array($cuenta['rol'], ['Paciente', 'Medico', 'Administrador'], true)) {
    http_response_code(403);
    exit('Acceso denegado.');
}
$errores = [];
$perfil = null;
$previsiones = [];
$datos = [];
$version = '';
$exito = !empty($_SESSION['perfil_guardado']);
unset($_SESSION['perfil_guardado']);
try {
    require __DIR__ . '/config/conexion.php';
    $perfil = cargarPerfil($conexion, $cuenta);
    $version = versionPerfil($perfil);
    foreach (['nombre', 'email', 'telefono', 'nacimiento', 'sexo', 'prevision'] as $campo) {
        $datos[$campo] = (string)($perfil[$campo] ?? '');
    }
    if ($cuenta['rol'] === 'Paciente') {
        $previsiones = $conexion->query('SELECT ID_Prevision, Prevision_Paciente FROM prevision ORDER BY Prevision_Paciente')->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $exito = false;
        foreach ($datos as $campo => $valor) {
            $datos[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
        }
        $datos['email'] = strtolower($datos['email']);
        $actual = is_string($_POST['actual'] ?? null) ? $_POST['actual'] : '';
        $nueva = is_string($_POST['nueva'] ?? null) ? $_POST['nueva'] : '';
        $confirmar = is_string($_POST['confirmar'] ?? null) ? $_POST['confirmar'] : '';
        $version = is_string($_POST['version'] ?? null) ? $_POST['version'] : '';
        if (!csrfValido()) { $errores[] = 'El formulario venció. Recarga la página.'; }
        $errores = array_merge($errores, erroresPerfil($datos, $cuenta['rol'], $nueva, $confirmar));
        if (!$errores) {
            $guardado = guardarPerfil($conexion, $cuenta, $datos, $actual, $nueva, $version);
            $_SESSION['cuenta'] = ['id' => $guardado['id'], 'nombre' => $guardado['nombre'],
                'email' => $guardado['email'], 'rol' => $guardado['rol']];
            $_SESSION['firma_cuenta'] = hash('sha256', $guardado['hash']);
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['perfil_guardado'] = true;
            header('Location: ' . BASE_ACCESO . '/perfil.php');
            exit;
        }
    }
} catch (DomainException $error) {
    $errores[] = $error->getMessage();
} catch (Throwable $error) {
    error_log('SaludUSM perfil: ' . $error->getMessage());
    $errores[] = 'No se pudo completar la operación. Revisa la conexión, los permisos del archivo administrador o el log de PHP.';
}
$hoy = (new DateTimeImmutable('today', new DateTimeZone('America/Santiago')))->format('Y-m-d');
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mi perfil | SaludUSM</title><link rel="stylesheet" href="../CSS/estilos.css">
</head><body><main>
<h1>Mi perfil</h1>
<p><a href="<?= escapar(destinoRol($cuenta['rol'])) ?>">Volver a mi panel</a></p>
<?php if ($exito): ?><p class="exito" role="status">Datos actualizados correctamente.</p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>
<?php if ($perfil): ?>
<p>Rol: <strong><?= escapar($cuenta['rol']) ?></strong></p>
<?php if ($cuenta['rol'] !== 'Administrador'): ?><p>RUT: <strong><?= escapar($perfil['id']) ?></strong> (no editable)</p><?php endif; ?>
<form method="post" action="perfil.php">
<input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
<input type="hidden" name="version" value="<?= escapar($version) ?>">
<p><label for="nombre">Nombre completo</label><br>
<input id="nombre" name="nombre" required maxlength="100" autocomplete="name" value="<?= escapar($datos['nombre']) ?>"></p>
<p><label for="email">Correo de contacto y acceso</label><br>
<input id="email" name="email" type="email" required maxlength="<?= $cuenta['rol'] === 'Medico' ? 100 : 255 ?>" autocomplete="username" value="<?= escapar($datos['email']) ?>"></p>
<?php if ($cuenta['rol'] === 'Paciente'): ?>
<p><label for="telefono">Teléfono (opcional)</label><br>
<input id="telefono" name="telefono" type="tel" maxlength="11" value="<?= escapar($datos['telefono']) ?>"></p>
<p><label for="nacimiento">Fecha de nacimiento</label><br>
<input id="nacimiento" name="nacimiento" type="date" required min="1000-01-01" max="<?= escapar($hoy) ?>" value="<?= escapar($datos['nacimiento']) ?>"></p>
<p><label for="sexo">Sexo</label><br><select id="sexo" name="sexo" required>
<?php foreach (['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'] as $valor => $etiqueta): ?>
<option value="<?= escapar($valor) ?>" <?= $datos['sexo'] === $valor ? 'selected' : '' ?>><?= escapar($etiqueta) ?></option>
<?php endforeach; ?></select></p>
<p><label for="prevision">Previsión</label><br><select id="prevision" name="prevision" required>
<?php foreach ($previsiones as $prev): ?>
<option value="<?= escapar($prev['ID_Prevision']) ?>" <?= $datos['prevision'] === (string)$prev['ID_Prevision'] ? 'selected' : '' ?>><?= escapar($prev['Prevision_Paciente']) ?></option>
<?php endforeach; ?></select></p>
<?php endif; ?>
<fieldset><legend>Cambiar contraseña (opcional)</legend>
<p>Deja ambos campos vacíos para conservarla. La nueva debe tener al menos 8 caracteres, una mayúscula A-Z, una minúscula a-z y un número. Máximo 72 bytes; los caracteres simples ocupan un byte.</p>
<p><label for="nueva">Nueva contraseña</label><br><input id="nueva" name="nueva" type="password" autocomplete="new-password"></p>
<p><label for="confirmar">Repite la nueva contraseña</label><br><input id="confirmar" name="confirmar" type="password" autocomplete="new-password"></p>
</fieldset>
<p><label for="actual">Contraseña actual para confirmar los cambios</label><br>
<input id="actual" name="actual" type="password" required autocomplete="current-password"></p>
<button class="boton" type="submit">Guardar cambios</button>
</form>
<?php endif; ?>
<p><a href="perfil.php">Recargar los datos guardados</a></p>
<?php if ($cuenta['rol'] === 'Paciente'): ?>
<p><a href="eliminar_cuenta.php">Eliminar mi cuenta</a></p>
<?php endif; ?>
<form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
<button type="submit">Cerrar sesión</button></form>
</main></body></html>
