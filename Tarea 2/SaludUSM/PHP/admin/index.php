<?php
require_once __DIR__ . '/../includes/autenticacion.php';
$cuenta = exigirRol('Administrador');
$titulo = 'Panel del administrador';
require __DIR__ . '/../includes/panel_acceso.php';
