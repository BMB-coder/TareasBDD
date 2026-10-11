<?php
//login.php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario']; // Puede ser el RUT o el Username del Admin
    $contrasena = $_POST['contrasena'];

    // 1. Buscar si es Administrador
    $stmt = $conexion->prepare("SELECT * FROM Administrador WHERE Username = ? AND Contrasena = ?");
    $stmt->execute([$usuario, $contrasena]);
    if ($admin = $stmt->fetch()) {
        $_SESSION['rol'] = 'Administrador';
        $_SESSION['id_usuario'] = $admin['ID_Admin'];
        header("Location: panel_admin.php");
        exit();
    }

    // 2. Buscar si es Médico
    $stmt = $conexion->prepare("SELECT * FROM Medico WHERE RUT_Medico = ? AND Contrasena = ?");
    $stmt->execute([$usuario, $contrasena]);
    if ($medico = $stmt->fetch()) {
        $_SESSION['rol'] = 'Medico';
        $_SESSION['id_usuario'] = $medico['RUT_Medico'];
        $_SESSION['nombre'] = $medico['Nombre'];
        header("Location: agenda_medica.php");
        exit();
    }

    // 3. Buscar si es Paciente
    $stmt = $conexion->prepare("SELECT * FROM Paciente WHERE RUT_Paciente = ? AND Contrasena = ?");
    $stmt->execute([$usuario, $contrasena]);
    if ($paciente = $stmt->fetch()) {
        $_SESSION['rol'] = 'Paciente';
        $_SESSION['id_usuario'] = $paciente['RUT_Paciente'];
        $_SESSION['nombre'] = $paciente['Nombre'];
        header("Location: mis_citas.php");
        exit();
    }

    // Si llega aquí, las credenciales son incorrectas
    $_SESSION['error_login'] = "Usuario o contraseña incorrectos.";
    header("Location: index.php");
    exit();
}
?>