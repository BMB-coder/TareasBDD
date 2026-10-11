<?php
$medicos = [];
$mensajeError = '';

try {
    require_once __DIR__ . '/config/conexion.php';

    $consulta = $conexion->query(
        'SELECT Nombre_Medico, `E-mail_Medico` AS Email
         FROM medico
         ORDER BY Nombre_Medico'
    );

    $medicos = $consulta->fetchAll();
} catch (PDOException $error) {
    error_log('SaludUSM - listado: ' . $error->getMessage());

    $mensajeError = 'No fue posible cargar los médicos. '
                  . 'Revisa la conexión y que exista la tabla medico.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Médicos | SaludUSM</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
</head>
<body>
    <?php require __DIR__ . '/includes/menu.php'; ?>

    <main>
        <h1>Médicos</h1>

        <?php if ($mensajeError !== ''): ?>

            <p class="error">
                <?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?>
            </p>

        <?php elseif (empty($medicos)): ?>

            <p>No hay médicos registrados.</p>

        <?php else: ?>

            <p>Total de médicos: <?= count($medicos) ?></p>

            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo electrónico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicos as $medico): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $medico['Nombre_Medico'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $medico['Email'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

        <p><a href="index.php">Volver al inicio</a></p>
    </main>
</body>
</html>