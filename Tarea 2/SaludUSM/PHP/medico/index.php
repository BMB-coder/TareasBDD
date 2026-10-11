<?php
require_once __DIR__ . '/../includes/autenticacion.php';
$cuenta = exigirRol('Medico');
$titulo = 'Panel del médico';
require __DIR__ . '/../includes/panel_acceso.php';
