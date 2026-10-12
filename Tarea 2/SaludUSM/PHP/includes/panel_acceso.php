<?php
// Solo se muestra a traves de un panel que ya comprobo el rol.
if (!isset($cuenta, $titulo)) {
    http_response_code(403);
    exit('Acceso denegado.');
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= escapar($titulo) ?> | SaludUSM</title>
<link rel="stylesheet" href="<?= escapar(BASE_ACCESO) ?>/../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/nav_cuenta.php'; ?>
<main>
<h1><?= escapar($titulo) ?></h1>
<p class="exito">Inicio de sesión correcto.</p>
<p>Nombre: <?= escapar($cuenta['nombre']) ?></p>
<p>Correo: <?= escapar($cuenta['email']) ?></p>
<p>Rol: <strong><?= escapar($cuenta['rol']) ?></strong></p>
<?php if ($cuenta['rol'] === 'Administrador'): ?>
<p><a class="boton" href="<?= escapar(BASE_ACCESO) ?>/admin/centros.php">Gestionar centros médicos</a>
<a class="boton" href="<?= escapar(BASE_ACCESO) ?>/admin/medicos.php">Gestionar médicos</a></p>
<?php endif; ?>
<p><a class="boton" href="<?= escapar(BASE_ACCESO) ?>/perfil.php">Ver y editar mi perfil</a></p>
<p><a href="<?= escapar(BASE_ACCESO) ?>/index.php">Inicio público</a></p>
</main></body></html>
