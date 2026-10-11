<?php
require_once __DIR__ . '/../includes/autenticacion.php';
$cuenta = exigirRol('Paciente');
$titulo = 'Panel del paciente';
require __DIR__ . '/../includes/panel_acceso.php';
