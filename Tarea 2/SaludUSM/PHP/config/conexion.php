<?php

$servidor = 'localhost';
$puerto = '3306';
$baseDatos = 'saludusm';
$usuario = 'root';
$contrasena = '';

$dsn = "mysql:host=$servidor;port=$puerto;dbname=$baseDatos;charset=utf8mb4";

$conexion = new PDO($dsn, $usuario, $contrasena, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);