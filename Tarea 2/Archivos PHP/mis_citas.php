<?php
session_start();

// 1. Guardia de seguridad: Solo entran Pacientes
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Paciente') {
    header("Location: index.php");
    exit();
}

require 'conexion.php';
$rut_paciente = $_SESSION['id_usuario'];
$nombre_paciente = $_SESSION['nombre'];

// Usamos el WHERE para filtrar solo las citas de ESTE paciente y ORDER BY para ordenarlas por fecha
try {
    $stmt = $conexion->prepare("SELECT * FROM Vista_Detalle_Citas WHERE RUT_Paciente = ? ORDER BY Fecha_Hora DESC");
    $stmt->execute([$rut_paciente]);
    $citas = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error al cargar las citas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Citas - SaludUSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SaludUSM - Portal Paciente</a>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3">
                    Hola, <?php echo htmlspecialchars($nombre_paciente); ?>
                </span>
                <a href="perfil.php" class="btn btn-sm btn-light me-2">Mi Perfil</a>
                <a href="logout.php" class="btn btn-sm btn-outline-light">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">Historial de Citas Médicas</h5>
                        <button class="btn btn-success btn-sm">Agendar Nueva Cita</button>
                    </div>
                    <div class="card-body">
                        
                        <?php if (count($citas) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Médico</th>
                                            <th>Especialidad</th>
                                            <th>Centro Médico</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citas as $cita): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo date('d-m-Y', strtotime($cita['Fecha_Hora'])); ?></strong><br>
                                                    <span class="text-muted small"><?php echo date('H:i', strtotime($cita['Fecha_Hora'])); ?> hrs</span>
                                                </td>
                                                <td><?php echo htmlspecialchars($cita['Medico']); ?></td>
                                                <td><?php echo htmlspecialchars($cita['Especialidad']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($cita['Centro_Medico']); ?><br>
                                                    <span class="text-muted small">Región: <?php echo htmlspecialchars($cita['Region']); ?></span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    // Asignar colores de Bootstrap según el estado
                                                    $badge_class = 'bg-secondary';
                                                    switch ($cita['ID_Estado']) {
                                                        case 1: $badge_class = 'bg-primary'; break; // Reservada
                                                        case 2: $badge_class = 'bg-info text-dark'; break; // Confirmada
                                                        case 3: $badge_class = 'bg-success'; break; // Atendida
                                                        case 4: $badge_class = 'bg-warning text-dark'; break; // No Asistió
                                                        case 5: $badge_class = 'bg-danger'; break; // Cancelada
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo htmlspecialchars($cita['Estado']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center my-4">
                                No tienes citas médicas registradas en el sistema.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>