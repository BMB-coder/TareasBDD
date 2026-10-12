<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/atenciones.php';

$cuenta = exigirRol('Medico');
$rutMedico = (string)$cuenta['id'];
$errores = [];
$aviso = '';
$misPacientes = [];

try {
    require __DIR__ . '/../config/conexion.php';
    $misPacientes = pacientesDelMedico($conexion, $rutMedico);
} catch (Throwable $error) {
    error_log('SaludUSM pacientes: ' . $error->getMessage());
    $errores[] = 'No se pudo cargar la lista de pacientes. Revisa la conexión.';
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mis Pacientes | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1>Mis Pacientes</h1>
<?php if ($aviso !== ''): ?><p class="exito" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>

<?php if (!$misPacientes): ?>
<p>Aún no tienes pacientes asignados o atendidos en tu agenda.</p>
<?php else: ?>
<div class="tabla-contenedor"><table>
<thead><tr><th scope="col">RUT</th><th scope="col">Paciente</th><th scope="col">Previsión</th><th scope="col">Última Cita</th><th scope="col">Acciones</th></tr></thead>
<tbody>
<?php foreach ($misPacientes as $p): ?>
<tr>
    <td><?= escapar($p['RUT_Paciente']) ?></td>
    <td><?= escapar($p['Nombre_Paciente']) ?></td>
    <td><?= escapar($p['Prevision_Paciente'] ?? '—') ?></td>
    <td><?= escapar($p['Ultima_Cita']) ?></td>
    <td class="acciones">
        <a href="historial.php?rut=<?= urlencode($p['RUT_Paciente']) ?>">Ver Historial</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<p>Aquí solo aparecen los pacientes que tienen o han tenido alguna cita contigo.</p>
</main></body></html>