<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/atenciones.php';

$cuenta = exigirRol('Paciente');
$miRut = (string)$cuenta['id'];
$errores = [];
$aviso = '';
$historial = [];

try {
    require __DIR__ . '/../config/conexion.php';
    $historial = historialPaciente($conexion, $miRut);
} catch (Throwable $error) {
    error_log('SaludUSM mi historial: ' . $error->getMessage());
    $errores[] = 'Error al cargar tu historial clínico.';
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mi Historial Clínico | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1>Mi Historial Clínico</h1>
<?php if ($aviso !== ''): ?><p class="exito" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>

<?php if (!$historial): ?>
    <p>Aún no tienes atenciones médicas registradas en tu historial.</p>
<?php else: ?>
    <?php foreach ($historial as $atencion): ?>
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <h2 style="margin-top: 0;">Consulta del <?= escapar($atencion['Fecha_Cita']) ?> a las <?= escapar($atencion['Hora']) ?></h2>
            <p><strong>Médico Tratante:</strong> Dr(a). <?= escapar($atencion['Nombre_Medico']) ?> - <?= escapar($atencion['Nombre_Especialidad']) ?></p>
            <p><strong>Lugar:</strong> <?= escapar($atencion['Nombre_Centro']) ?></p>
            
            <p><strong>Motivo de la consulta:</strong><br><?= nl2br(escapar($atencion['Motivo_Atencion'])) ?></p>
            <p><strong>Indicaciones del Médico:</strong><br><?= nl2br(escapar($atencion['Observaciones_Atencion'])) ?></p>
            
            <?php if (!empty($atencion['diagnosticos'])): ?>
                <h3>Mis Diagnósticos</h3>
                <ul>
                    <?php foreach ($atencion['diagnosticos'] as $diag): ?>
                        <li><?= escapar($diag['Descripcion_Diagnostico']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($atencion['recetas'])): ?>
                <h3>Mis Medicamentos Recetados</h3>
                <ul>
                    <?php foreach ($atencion['recetas'] as $receta): ?>
                        <li><strong><?= escapar($receta['Medicamento_Receta']) ?>:</strong> <?= escapar($receta['Dosis_Receta']) ?> por <?= escapar($receta['Dias']) ?> días.</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</main></body></html>