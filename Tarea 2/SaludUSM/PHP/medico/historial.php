<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/atenciones.php';

$cuenta = exigirRol('Medico');
$rutMedico = (string)$cuenta['id'];
$rutPaciente = $_GET['rut'] ?? '';
$errores = [];
$aviso = '';
$paciente = null;
$historial = [];

try {
    require __DIR__ . '/../config/conexion.php';
    $paciente = pacienteDelMedico($conexion, $rutMedico, $rutPaciente);
    if (!$paciente) {
        $errores[] = "Acceso denegado: No tienes permisos para ver el historial de este paciente o el RUT no existe.";
    } else {
        $historial = historialPaciente($conexion, $rutPaciente);
    }
} catch (Throwable $error) {
    error_log('SaludUSM historial medico: ' . $error->getMessage());
    $errores[] = 'Error al cargar el historial clínico.';
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Historial Clínico | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<?php if ($paciente): ?>
    <h1>Historial Clínico: <?= escapar($paciente['Nombre_Paciente']) ?></h1>
    <p><strong>RUT:</strong> <?= escapar($paciente['RUT_Paciente']) ?> | <strong>Sexo:</strong> <?= escapar($paciente['Sexo_Paciente']) ?> | <strong>Previsión:</strong> <?= escapar($paciente['Prevision_Paciente']) ?></p>
<?php else: ?>
    <h1>Historial Clínico</h1>
<?php endif; ?>

<?php if ($aviso !== ''): ?><p class="exito" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>

<p><a class="boton" href="pacientes.php">← Volver a Mis Pacientes</a></p>

<?php if ($paciente && !$historial): ?>
    <p>El paciente no tiene atenciones registradas aún.</p>
<?php elseif ($paciente && $historial): ?>
    <?php foreach ($historial as $atencion): ?>
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <h2 style="margin-top: 0;">Atención: <?= escapar($atencion['Fecha_Cita']) ?> (<?= escapar($atencion['Hora']) ?>)</h2>
            <p><strong>Médico:</strong> Dr(a). <?= escapar($atencion['Nombre_Medico']) ?> (<?= escapar($atencion['Nombre_Especialidad']) ?>)</p>
            <p><strong>Centro:</strong> <?= escapar($atencion['Nombre_Centro']) ?></p>
            
            <p><strong>Motivo:</strong><br><?= nl2br(escapar($atencion['Motivo_Atencion'])) ?></p>
            <p><strong>Observaciones:</strong><br><?= nl2br(escapar($atencion['Observaciones_Atencion'])) ?></p>
            
            <?php if (!empty($atencion['diagnosticos'])): ?>
                <h3>Diagnósticos CIE-10</h3>
                <ul>
                    <?php foreach ($atencion['diagnosticos'] as $diag): ?>
                        <li>[<?= escapar($diag['Codigo_Diagnostico']) ?>] <?= escapar($diag['Descripcion_Diagnostico']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($atencion['recetas'])): ?>
                <h3>Receta</h3>
                <ul>
                    <?php foreach ($atencion['recetas'] as $receta): ?>
                        <li><?= escapar($receta['Medicamento_Receta']) ?> - <?= escapar($receta['Dosis_Receta']) ?> (<?= escapar($receta['Dias']) ?> días)</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</main></body></html>