<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/citas.php';
$cuenta = exigirRol('Paciente');
$rut = (string)$cuenta['id'];
$errores = [];
$aviso = $_SESSION['aviso_citas'] ?? '';
unset($_SESSION['aviso_citas']);
$citas = ['proximas' => [], 'historicas' => []];
try {
    require __DIR__ . '/../config/conexion.php';
    cancelarCitasVencidas($conexion);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nro = is_string($_POST['nro'] ?? null) && ctype_digit($_POST['nro']) ? (int)$_POST['nro'] : 0;
        if (!csrfValido()) {
            throw new DomainException('El formulario venció. Recarga la página.');
        }
        cancelarCitaPaciente($conexion, $rut, $nro);
        $_SESSION['aviso_citas'] = "Cita Nº $nro cancelada.";
        header('Location: ' . BASE_ACCESO . '/paciente/mis_citas.php');
        exit;
    }
} catch (DomainException $error) {
    $errores[] = $error->getMessage();
} catch (Throwable $error) {
    error_log('SaludUSM mis citas: ' . $error->getMessage());
    $errores[] = 'No se pudo completar la operación. Revisa la conexión o el log de PHP.';
}
try {
    if (isset($conexion)) {
        $citas = citasPaciente($conexion, $rut);
    }
} catch (Throwable $error) {
    error_log('SaludUSM mis citas: ' . $error->getMessage());
    $errores[] = 'No se pudieron cargar tus citas.';
}
$secciones = ['proximas' => 'Próximas citas', 'historicas' => 'Historial de citas'];
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mis citas | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1>Mis citas</h1>
<?php if ($aviso !== ''): ?><p class="exito" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>
<p><a class="boton" href="agendar.php">Agendar una hora</a></p>

<?php foreach ($secciones as $clave => $tituloSeccion): ?>
<h2><?= escapar($tituloSeccion) ?></h2>
<?php if (!$citas[$clave]): ?>
<p><?= $clave === 'proximas' ? 'No tienes citas próximas.' : 'Aún no tienes citas anteriores.' ?></p>
<?php else: ?>
<div class="tabla-contenedor"><table>
<thead><tr><th scope="col">Nº</th><th scope="col">Fecha</th><th scope="col">Hora</th><th scope="col">Médico</th>
<th scope="col">Especialidad</th><th scope="col">Centro</th><th scope="col">Estado</th>
<?php if ($clave === 'proximas'): ?><th scope="col">Acciones</th><?php endif; ?></tr></thead>
<tbody>
<?php foreach ($citas[$clave] as $cita): ?>
<tr>
    <td><?= escapar($cita['Nro_Cita']) ?></td>
    <td class="sin-salto"><?= escapar($cita['Fecha_Cita']) ?></td>
    <td><?= escapar(substr($cita['Hora_Cita'], 0, 5)) ?></td>
    <td><?= escapar($cita['Nombre_Medico']) ?></td>
    <td><?= escapar($cita['Nombre_Especialidad']) ?></td>
    <td><?= escapar($cita['Nombre_Centro'] . ' (' . $cita['Comuna_Centro'] . ', ' . $cita['Region_Centro'] . ')') ?></td>
    <td><?= escapar($cita['Estado_Cita']) ?></td>
    <?php if ($clave === 'proximas'): ?>
    <td class="acciones">
        <?php if ($cita['Modificable']): ?>
        <a href="agendar.php?reprogramar=<?= escapar($cita['Nro_Cita']) ?>">Reprogramar</a>
        <form method="post" action="mis_citas.php" onsubmit="return confirm('¿Cancelar la cita Nº <?= escapar($cita['Nro_Cita']) ?>?');">
            <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
            <input type="hidden" name="nro" value="<?= escapar($cita['Nro_Cita']) ?>">
            <button class="peligro" type="submit">Cancelar</button>
        </form>
        <?php else: ?>
        En curso
        <?php endif; ?>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php endforeach; ?>
<p>Solo se pueden reprogramar o cancelar citas Reservadas o Confirmadas cuya fecha y hora no han pasado.
Las citas Reservadas o Confirmadas de días anteriores se cancelan automáticamente.</p>
</main></body></html>
