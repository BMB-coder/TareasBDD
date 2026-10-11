<?php
$conexionCorrecta = false;

try {
    require_once __DIR__ . '/config/conexion.php';

    $conexion->query('SELECT 1');
    $conexionCorrecta = true;
} catch (PDOException $error) {
    error_log('SaludUSM - conexión: ' . $error->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio | SaludUSM</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
</head>
<body>
    <?php require __DIR__ . '/includes/menu.php'; ?>

    <main>
        <h1>SaludUSM</h1>
        <p>Sistema de gestión de citas médicas.</p>

        <?php if ($conexionCorrecta): ?>
            <p class="exito">
                Conexión a la base de datos realizada correctamente.
            </p>
            <a class="boton" href="medicos.php">Ver médicos</a>
        <?php else: ?>
            <p class="error">
                No se pudo conectar a la base de datos.
                Revisa que MySQL esté iniciado y la configuración
                de conexion.php.
            </p>
        <?php endif; ?>
    </main>
</body>
</html>