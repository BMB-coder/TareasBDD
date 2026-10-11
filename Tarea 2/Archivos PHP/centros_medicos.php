<?php
session_start();
require 'conexion.php';

// Validar que solo entre el Administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Administrador') {
    header("Location: index.php");
    exit();
}

$mensaje = '';

// Procesar las acciones CRUD (Create, Update, Delete)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    try {
        if ($accion == 'crear') {
            $nombre = $_POST['nombre'];
            $comuna = $_POST['comuna'];
            $region = $_POST['region'];
            
            $stmt = $conexion->prepare("INSERT INTO Centro_Medico (Nombre, Comuna, Region) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $comuna, $region]);
            $mensaje = '<div class="alert alert-success">Centro médico agregado con éxito.</div>';

        } elseif ($accion == 'editar') {
            $id = $_POST['codigo_interno'];
            $nombre = $_POST['nombre'];
            $comuna = $_POST['comuna'];
            $region = $_POST['region'];
            
            $stmt = $conexion->prepare("UPDATE Centro_Medico SET Nombre = ?, Comuna = ?, Region = ? WHERE Codigo_Interno = ?");
            $stmt->execute([$nombre, $comuna, $region, $id]);
            $mensaje = '<div class="alert alert-success">Centro médico actualizado correctamente.</div>';

        } elseif ($accion == 'eliminar') {
            $id = $_POST['codigo_interno'];
            
            $stmt = $conexion->prepare("DELETE FROM Centro_Medico WHERE Codigo_Interno = ?");
            $stmt->execute([$id]);
            $mensaje = '<div class="alert alert-success">Centro médico eliminado correctamente.</div>';
        }
    } catch(PDOException $e) {
        // Protección de Integridad Referencial
        if ($e->getCode() == 23000) {
            $mensaje = '<div class="alert alert-danger fw-bold">Operación denegada: No se puede eliminar este Centro Médico porque actualmente tiene citas o médicos asociados en la base de datos.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Leer todos los centros actuales (Read)
$stmt = $conexion->query("SELECT * FROM Centro_Medico ORDER BY Codigo_Interno ASC");
$centros = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Centros - SaludUSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SaludUSM - Administración</a>
            <div>
                <a href="panel_admin.php" class="btn btn-sm btn-outline-light me-3">Volver al Dashboard</a>
                <a href="logout.php" class="btn btn-sm btn-danger">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4 text-primary">Gestión de Centros Médicos</h2>
        
        <?php echo $mensaje; ?>

        <!-- Formulario para Agregar Centro Médico -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="card-title text-success mb-3">Registrar Nuevo Centro</h5>
                <form action="centros_medicos.php" method="POST" class="row g-3 align-items-end">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Nombre del Centro</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: SaludUSM Centro" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">Comuna</label>
                        <input type="text" name="comuna" class="form-control" placeholder="Ej: Santiago" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small mb-1">Región</label>
                        <input type="text" name="region" class="form-control" placeholder="Ej: RM" maxlength="2" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success w-100">Guardar Centro</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Listado y Borrado/Edición -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3">Centros Activos</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID Interno</th>
                                <th>Nombre</th>
                                <th>Comuna</th>
                                <th>Región</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($centros as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['Codigo_Interno']); ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($c['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($c['Comuna']); ?></td>
                                <td><?php echo htmlspecialchars($c['Region']); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <!-- Botón que abre la Modal de Edición -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $c['Codigo_Interno']; ?>">
                                            Editar
                                        </button>

                                        <!-- Botón Eliminar -->
                                        <form action="centros_medicos.php" method="POST" onsubmit="return confirm('¿Seguro que deseas intentar eliminar el centro <?php echo addslashes($c['Nombre']); ?>?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="codigo_interno" value="<?php echo $c['Codigo_Interno']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de Edición exclusiva para este registro -->
                            <div class="modal fade" id="modalEditar<?php echo $c['Codigo_Interno']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Editar Centro Médico</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="centros_medicos.php" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="accion" value="editar">
                                                <input type="hidden" name="codigo_interno" value="<?php echo $c['Codigo_Interno']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Nombre del Centro</label>
                                                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($c['Nombre']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Comuna</label>
                                                    <input type="text" name="comuna" class="form-control" value="<?php echo htmlspecialchars($c['Comuna']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Región (Ej: RM, VA)</label>
                                                    <input type="text" name="region" class="form-control" value="<?php echo htmlspecialchars($c['Region']); ?>" maxlength="2" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>