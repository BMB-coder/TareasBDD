<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SaludUSM</title>
    <!-- Incluimos Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">SaludUSM</h2>
                            <p class="text-muted">Ingresa tus credenciales</p>
                        </div>

                        <!-- Mostrar mensaje de error si las credenciales fallan -->
                        <?php 
                        session_start();
                        if(isset($_SESSION['error_login'])){
                            echo '<div class="alert alert-danger py-2 text-center" role="alert">' . $_SESSION['error_login'] . '</div>';
                            unset($_SESSION['error_login']); // Limpiamos el error después de mostrarlo
                        }
                        ?>

                        <!-- El formulario que envía los datos a login.php -->
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="usuario" class="form-label fw-semibold">RUT o Nombre de Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ej: 12345678-9 o admin_salud" required autofocus>
                            </div>
                            
                            <div class="mb-4">
                                <label for="contrasena" class="form-label fw-semibold">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="********" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Iniciar Sesión</button>
                            </div>

                            <div class="text-center mt-3">
                                <a href="registro.php" class="text-decoration-none">¿No tienes cuenta? Regístrate aquí</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>