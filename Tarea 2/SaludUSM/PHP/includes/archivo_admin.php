<?php
// Espera acotada: nunca mantener una peticion esperando indefinidamente.
function bloquearArchivoAdmin($archivo, int $tipo): void {
    $limite = microtime(true) + 3;
    do {
        if (flock($archivo, $tipo | LOCK_NB)) {
            return;
        }
        usleep(50000);
    } while (microtime(true) < $limite);
    throw new RuntimeException('El archivo del administrador esta ocupado. Intentalo nuevamente.');
}

function decodificarAdmin(string $contenido): array {
    $admin = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($admin)) {
        throw new RuntimeException('Configuracion del administrador invalida.');
    }
    foreach (['nombre', 'email', 'contrasena_hash', 'rol'] as $campo) {
        if (!isset($admin[$campo]) || !is_string($admin[$campo]) || $admin[$campo] === '') {
            throw new RuntimeException('Configuracion del administrador incompleta.');
        }
    }
    if ($admin['rol'] !== 'Administrador') {
        throw new RuntimeException('Rol administrador invalido.');
    }
    return $admin;
}

function leerArchivoAdmin(): array {
    $f = @fopen(ARCHIVO_ADMINISTRADOR, 'rb');
    if (!$f) {
        throw new RuntimeException('No se puede abrir el archivo privado del administrador.');
    }
    try {
        bloquearArchivoAdmin($f, LOCK_SH);
        $texto = stream_get_contents($f);
        if ($texto === false) {
            throw new RuntimeException('No se puede leer la configuracion.');
        }
        return decodificarAdmin($texto);
    } finally {
        flock($f, LOCK_UN);
        fclose($f);
    }
}

// null si el JSON no existe; un JSON presente pero invalido sigue siendo un error.
function leerArchivoAdminOpcional(): ?array {
    return is_file(ARCHIVO_ADMINISTRADOR) ? leerArchivoAdmin() : null;
}

function escribirCompletoAdmin($f, string $texto): void {
    if (!rewind($f)) {
        throw new RuntimeException('No se puede posicionar el archivo.');
    }
    $posicion = 0;
    $largo = strlen($texto);
    while ($posicion < $largo) {
        $n = fwrite($f, substr($texto, $posicion));
        if ($n === false || $n === 0) {
            throw new RuntimeException('No se pudo completar la escritura.');
        }
        $posicion += $n;
    }
    if (!ftruncate($f, $largo) || !fflush($f)) {
        throw new RuntimeException('No se pudo finalizar la escritura.');
    }
}
