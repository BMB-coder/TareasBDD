<?php
// JSON del administrador. Ubicacion recomendada: fuera de htdocs, en
// <carpeta de XAMPP>/saludusm_config/administrador.json (C:/xampp/saludusm_config en Windows).
// Si no existe, se usa la copia incluida en SaludUSM/saludusm_config (bloqueada por .htaccess).
function rutaArchivoAdministrador(): string {
    $raiz = !empty($_SERVER['DOCUMENT_ROOT'])
        ? dirname(rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'))
        : 'C:/xampp';
    $externo = $raiz . '/saludusm_config/administrador.json';
    $incluido = dirname(__DIR__, 2) . '/saludusm_config/administrador.json';
    return is_file($externo) || !is_file($incluido) ? $externo : $incluido;
}
define('ARCHIVO_ADMINISTRADOR', rutaArchivoAdministrador());
const BASE_ACCESO = '/SaludUSM/PHP';
