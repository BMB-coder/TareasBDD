<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id_usuario'];
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    $contrasena = $_POST['contrasena'];

    try {
        if ($rol == 'Paciente') {
            $telefono = $_POST['telefono'];
            $comuna = $_POST['comuna'];
            $stmt = $conexion->prepare("UPDATE Paciente SET Telefono = ?, Comuna = ?, Contrasena = ? WHERE RUT_Paciente = ?");
            $stmt->execute([$telefono, $comuna, $contrasena, $id_usuario]);
            
        } elseif ($rol == 'Medico') {
            $email = $_POST['email'];
            $stmt = $conexion->prepare("UPDATE Medico SET Email = ?, Contrasena = ? WHERE RUT_Medico = ?");
            $stmt->execute([$email, $contrasena, $id_usuario]);
            
        } elseif ($rol == 'Administrador') {
            $stmt = $conexion->prepare("UPDATE Administrador SET Contrasena = ? WHERE ID_Admin = ?");
            $stmt->execute([$contrasena, $id_usuario]);
        }
        $mensaje = '<div class="alert alert-success">Perfil actualizado correctamente.</div>';
    } catch(PDOException $e) {
        $mensaje = '<div class="alert alert-danger">Error al actualizar: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'eliminar' && $rol == 'Paciente') {
    try {
        $stmt = $conexion->prepare("DELETE FROM Paciente WHERE RUT_Paciente = ?");
        $stmt->execute([$id_usuario]);
        session_destroy();
        header("Location: index.php?mensaje=cuenta_eliminada");
        exit();
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) { 
            $mensaje = '<div class="alert alert-danger fw-bold">No puedes eliminar tu cuenta porque tienes un historial médico asociado. Por normativas de integridad de datos, tu registro debe mantenerse.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al eliminar: ' . $e->getMessage() . '</div>';
        }
    }
}

if ($rol == 'Paciente') {
    $stmt = $conexion->prepare("SELECT Nombre, Telefono, Comuna, Contrasena FROM Paciente WHERE RUT_Paciente = ?");
} elseif ($rol == 'Medico') {
    $stmt = $conexion->prepare("SELECT Nombre, Email, Contrasena FROM Medico WHERE RUT_Medico = ?");
} elseif ($rol == 'Administrador') {
    $stmt = $conexion->prepare("SELECT Username AS Nombre, Contrasena FROM Administrador WHERE ID_Admin = ?");
}
$stmt->execute([$id_usuario]);
$datos_usuario = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - SaludUSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-primary mb-0">Mi Perfil</h2>
                        <span class="badge bg-secondary">Rol: <?php echo $rol; ?></span>
                    </div>
                    <div>
                        <a href="<?php echo ($rol == 'Paciente') ? 'mis_citas.php' : (($rol == 'Medico') ? 'agenda_medica.php' : 'panel_admin.php'); ?>" class="btn btn-outline-secondary btn-sm me-2">Volver al Panel</a>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                    </div>
                </div>

                <?php echo $mensaje; ?>

                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Actualizar Mis Datos</h5>
                        
                        <form action="perfil.php" method="POST">
                            <input type="hidden" name="accion" value="actualizar">
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre Registrado</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['Nombre']); ?>" disabled>
                            </div>
                            
                            <?php if ($rol == 'Paciente'): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['Telefono']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Comuna</label>
                                    <input type="text" class="form-control" name="comuna" value="<?php echo htmlspecialchars($datos_usuario['Comuna']); ?>" required>
                                </div>
                            <?php endif; ?>

                            <?php if ($rol == 'Medico'): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email de Contacto</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($datos_usuario['Email']); ?>" required>
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Contraseña</label>
                                <input type="text" class="form-control" name="contrasena" value="<?php echo htmlspecialchars($datos_usuario['Contrasena']); ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                        </form>
                    </div>
                </div>

                <?php if ($rol == 'Paciente'): ?>
                <div class="card border-danger shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title text-danger mb-3">Zona de Peligro</h5>
                        <p class="text-muted small">Al eliminar tu cuenta perderás el acceso al sistema. Esto solo es posible si no tienes citas médicas previas (Integridad Referencial).</p>
                        
                        <form action="perfil.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar tu cuenta permanentemente?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <button type="submit" class="btn btn-outline-danger w-100">Eliminar mi Cuenta</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>