<?php
session_start();
require 'conexion.php';

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'];
    $fecha_nac = $_POST['fecha_nac'];
    $sexo = $_POST['sexo'];
    $telefono = $_POST['telefono'];
    $comuna = $_POST['comuna'];
    $prevision = $_POST['prevision'];
    $contrasena = $_POST['contrasena'];

    //Revisar si el RUT ya existe en la base de datos
    $stmt_check = $conexion->prepare("SELECT RUT_Paciente FROM Paciente WHERE RUT_Paciente = ?");
    $stmt_check->execute([$rut]);
    
    if ($stmt_check->rowCount() > 0) {
        $mensaje = '<div class="alert alert-danger">Error: Este RUT ya está registrado en el sistema.</div>';
    } else {
        try {
            $sql = "INSERT INTO Paciente (RUT_Paciente, Nombre, Fecha_Nacimiento, Sexo, Telefono, Comuna, ID_Prevision, Contrasena) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$rut, $nombre, $fecha_nac, $sexo, $telefono, $comuna, $prevision, $contrasena]);
            
            $mensaje = '<div class="alert alert-success">¡Registro exitoso! Ya puedes iniciar sesión.</div>';
        } catch(PDOException $e) {
            $mensaje = '<div class="alert alert-danger">Error en el servidor: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Paciente - SaludUSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center py-5">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary">Nuevo Paciente</h3>
                            <p class="text-muted">Crea tu cuenta en SaludUSM</p>
                        </div>

                        <?php echo $mensaje; ?>

                        <form action="registro.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">RUT (Ej: 12345678-9)</label>
                                <input type="text" class="form-control" name="rut" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre Completo</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Fecha Nacimiento</label>
                                    <input type="date" class="form-control" name="fecha_nac" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Sexo</label>
                                    <select class="form-select" name="sexo" required>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Comuna</label>
                                    <input type="text" class="form-control" name="comuna" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Previsión</label>
                                <select class="form-select" name="prevision" required>
                                    <option value="1">Fonasa</option>
                                    <option value="2">Isapre</option>
                                    <option value="3">Particular</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Contraseña</label>
                                <input type="password" class="form-control" name="contrasena" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">Registrarme</button>
                                <a href="index.php" class="btn btn-outline-secondary">Volver al Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>