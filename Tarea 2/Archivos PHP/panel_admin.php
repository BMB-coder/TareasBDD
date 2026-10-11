<?php
session_start();

// Bloque de proteccion
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Administrador') {
    header("Location: index.php");
    exit();
}

// Conexion a base de datos
require 'conexion.php';

// Usamos la Función SQL para traer la estadística
$stmt = $conexion->prepare("SELECT Nombre, CalcularPorcentajeInasistencia(Codigo_Interno) AS Porcentaje FROM Centro_Medico");
$stmt->execute();
$centros = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador - SaludUSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Barra de Navegacion -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SaludUSM - Panel Admin</a>
            <span class="navbar-text text-white">
                Bienvenido, Administrador
                <!-- Boton para cerrar sesion -->
                <a href="logout.php" class="btn btn-sm btn-outline-danger ms-3">Cerrar Sesión</a>
            </span>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Dashboard de Gestión</h2>
        
        <!-- Accesos Rápidos del Administrador -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="centros_medicos.php" class="btn btn-primary shadow-sm me-2">Gestionar Centros Médicos</a>
                <a href="medicos.php" class="btn btn-primary shadow-sm">Gestionar Médicos y Asignaciones</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 text-primary">Inasistencia por Centro Médico</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Centro Médico</th>
                                    <th>% Inasistencia (Calculado por Función SQL)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($centros as $centro): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($centro['Nombre']); ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark fs-6">
                                            <?php echo htmlspecialchars($centro['Porcentaje']); ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>