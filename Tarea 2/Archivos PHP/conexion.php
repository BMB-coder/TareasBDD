<?php
// conexion.php
$host = 'localhost';
$dbname = 'usm'; // Nombre Base de Datos
$username = 'root';   // Usuario de MySQL
$password = '';       // Contraseña de MySQL

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar PDO para que lance excepciones en caso de error
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>