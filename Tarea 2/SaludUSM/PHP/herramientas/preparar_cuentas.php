<?php
// Solo consola: nunca crea cuentas al abrir una URL.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ejecuta esta herramienta desde la consola de XAMPP.');
}

function preguntar(string $texto, string $predeterminado = ''): string {
    echo $texto . ($predeterminado !== '' ? " [$predeterminado]" : '') . ': ';
    $entrada = fgets(STDIN);
    if ($entrada === false) {
        throw new RuntimeException('Entrada cancelada.');
    }
    $entrada = trim($entrada);
    return $entrada !== '' ? $entrada : $predeterminado;
}

function elegir(array $filas, string $rut, string $nombre, string $tipo): ?array {
    if (!$filas) return null;
    echo "\nRegistros de $tipo disponibles:\n";
    foreach ($filas as $fila) echo $fila[$rut] . ' - ' . $fila[$nombre] . PHP_EOL;
    $seleccion = preguntar("RUT del $tipo que usaremos", count($filas) === 1 ? $filas[0][$rut] : '');
    foreach ($filas as $fila) if ($fila[$rut] === $seleccion) return $fila;
    throw new RuntimeException('El RUT elegido no existe. No se ha cambiado ningun registro.');
}

function columna(PDO $db, string $tabla, string $campo, string $definicion): void {
    $q = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $q->execute([$tabla, $campo]);
    if (!(int)$q->fetchColumn()) $db->exec("ALTER TABLE `$tabla` ADD COLUMN `$campo` $definicion");
}

function correoLibre(PDO $db, string $email, ?string $paciente, ?string $medico): void {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException("Correo invalido: $email");
    $q = $db->prepare('SELECT RUT_Paciente FROM paciente WHERE LOWER(TRIM(Email_Paciente))=LOWER(?)');
    $q->execute([$email]);
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $rut) {
        if ($rut !== $paciente) throw new RuntimeException("Correo ya usado por un paciente: $email");
    }
    $q = $db->prepare('SELECT RUT_Medico FROM medico WHERE LOWER(TRIM(`E-mail_Medico`))=LOWER(?)');
    $q->execute([$email]);
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $rut) {
        if ($rut !== $medico) throw new RuntimeException("Correo ya usado por un medico: $email");
    }
}

$conexion = null;
$archivoCreado = false;
$rutaAdmin = '';
try {
    require __DIR__ . '/../config/conexion.php';
    if (!($conexion instanceof PDO)) throw new RuntimeException('conexion.php debe definir $conexion como PDO.');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $bd = $conexion->query('SELECT DATABASE()')->fetchColumn();
    echo "Base seleccionada por conexion.php: $bd\n";
    echo "No se crearan tablas ni citas. Se agregaran tres columnas y cuentas de prueba.\n";

    $medico = elegir($conexion->query('SELECT RUT_Medico, Nombre_Medico, `E-mail_Medico` FROM medico ORDER BY RUT_Medico')->fetchAll(), 'RUT_Medico', 'Nombre_Medico', 'medico');
    if (!$medico) throw new RuntimeException('Primero registra un medico en phpMyAdmin.');
    $paciente = elegir($conexion->query('SELECT RUT_Paciente, Nombre_Paciente FROM paciente ORDER BY RUT_Paciente')->fetchAll(), 'RUT_Paciente', 'Nombre_Paciente', 'paciente');
    $rutPaciente = $paciente ? $paciente['RUT_Paciente'] : '00000000-2';
    if (!$paciente) echo "No hay pacientes: se creara PacientePrueba con RUT $rutPaciente.\n";
    $correoPaciente = strtolower(preguntar('Correo para la cuenta del paciente', 'paciente.prueba@example.test'));
    $correoMedico = strtolower(preguntar('Correo del medico (se guardara en su registro)', trim($medico['E-mail_Medico'])));
    $correoAdmin = strtolower(preguntar('Correo del administrador', 'admin.prueba@example.test'));
    $nombreAdmin = preguntar('Nombre del administrador', 'AdministradorPrueba');
    if (count(array_unique([$correoPaciente, $correoMedico, $correoAdmin])) !== 3) throw new RuntimeException('Los tres correos deben ser diferentes.');

    $directorio = preguntar('Carpeta privada FUERA de htdocs', 'C:\\xampp\\saludusm_config');
    $normalizado = str_replace('\\', '/', $directorio);
    if (!preg_match('~^(?:[A-Za-z]:/|/)~', $normalizado) || preg_match('~(?:^|/)htdocs(?:/|$)~i', $normalizado)) {
        throw new RuntimeException('Usa una ruta absoluta fuera de htdocs.');
    }
    $rutaAdmin = rtrim($directorio, '/\\') . DIRECTORY_SEPARATOR . 'administrador.json';
    if (file_exists($rutaAdmin)) throw new RuntimeException('Ya existe administrador.json. No se sobrescribe: esta herramienta es para la primera preparacion.');
    echo "\nContrasenas de prueba:\nPaciente: PacientePrueba!2026\nMedico: MedicoPrueba!2026\nAdministrador: AdminPrueba!2026\n";
    echo "Se conservaran las asignaciones existentes; se completaran hasta tener dos centros y dos especialidades.\n";
    if (preguntar('Escribe CREAR para aplicar estos cambios') !== 'CREAR') exit("Cancelado sin cambios.\n");

    $motores = $conexion->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('paciente','medico','prevision','especialidad','centro_medico','medico_especialidad','centro_medico_medico')")->fetchAll();
    foreach ($motores as $tabla) {
        if (strtoupper($tabla['ENGINE']) !== 'INNODB') throw new RuntimeException('Se requiere InnoDB para garantizar rollback: ' . $tabla['TABLE_NAME']);
    }

    // ALTER TABLE produce commit implicito en MariaDB. Estas columnas pueden
    // permanecer vacias si falla la carga posterior; no se eliminan datos.
    columna($conexion, 'paciente', 'Email_Paciente', 'VARCHAR(255) DEFAULT NULL');
    columna($conexion, 'paciente', 'Contrasena_Hash', 'VARCHAR(255) DEFAULT NULL');
    columna($conexion, 'medico', 'Contrasena_Hash', 'VARCHAR(255) DEFAULT NULL');
    $q = $conexion->prepare('SELECT Contrasena_Hash FROM medico WHERE RUT_Medico=?');
    $q->execute([$medico['RUT_Medico']]);
    if ($q->fetchColumn()) throw new RuntimeException('El medico ya tiene credenciales. No se cambia su contrasena.');
    if ($paciente) {
        $q = $conexion->prepare('SELECT Email_Paciente, Contrasena_Hash FROM paciente WHERE RUT_Paciente=?');
        $q->execute([$rutPaciente]); $existente = $q->fetch();
        if ($existente['Contrasena_Hash'] || $existente['Email_Paciente']) throw new RuntimeException('El paciente ya tiene datos de cuenta. No se sobrescriben.');
    }
    $duplicados = $conexion->query('SELECT Email_Paciente FROM paciente WHERE Email_Paciente IS NOT NULL GROUP BY Email_Paciente HAVING COUNT(*)>1')->fetchAll();
    if ($duplicados) throw new RuntimeException('Hay correos de paciente duplicados: corregir antes de continuar.');
    $q = $conexion->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='paciente' AND INDEX_NAME='uq_paciente_email'");
    if (!(int)$q->fetchColumn()) $conexion->exec('ALTER TABLE paciente ADD UNIQUE KEY uq_paciente_email (Email_Paciente)');

    correoLibre($conexion, $correoPaciente, $rutPaciente, null);
    correoLibre($conexion, $correoMedico, null, $medico['RUT_Medico']);
    correoLibre($conexion, $correoAdmin, null, null);
    $hashPaciente = password_hash('PacientePrueba!2026', PASSWORD_DEFAULT);
    $hashMedico = password_hash('MedicoPrueba!2026', PASSWORD_DEFAULT);
    $hashAdmin = password_hash('AdminPrueba!2026', PASSWORD_DEFAULT);
    $conexion->beginTransaction();
    if (!$paciente) {
        $prev = $conexion->query('SELECT ID_Prevision FROM prevision ORDER BY ID_Prevision LIMIT 1')->fetchColumn();
        if ($prev === false) {
            $conexion->exec("INSERT INTO prevision (Prevision_Paciente) VALUES ('Fonasa')");
            $prev = $conexion->lastInsertId();
        }
        $q = $conexion->prepare('INSERT INTO paciente (RUT_Paciente,Nombre_Paciente,Telefono_Paciente,`Fecha-Nacimiento_Paciente`,Sexo_Paciente,ID_Prevision) VALUES (?,?,?,?,?,?)');
        $q->execute([$rutPaciente, 'PacientePrueba', '900000000', '2000-01-01', 'Otro', $prev]);
    }
    $q = $conexion->prepare('UPDATE paciente SET Email_Paciente=?,Contrasena_Hash=? WHERE RUT_Paciente=?');
    $q->execute([$correoPaciente, $hashPaciente, $rutPaciente]);
    $q = $conexion->prepare('UPDATE medico SET `E-mail_Medico`=?,Contrasena_Hash=? WHERE RUT_Medico=?');
    $q->execute([$correoMedico, $hashMedico, $medico['RUT_Medico']]);

    $rutMedico = $medico['RUT_Medico'];
    $q = $conexion->prepare('SELECT ID_Especialidad FROM medico_especialidad WHERE RUT_Medico=?');
    $q->execute([$rutMedico]); $asignadas = $q->fetchAll(PDO::FETCH_COLUMN);
    if (count($asignadas)>3) throw new RuntimeException('El medico ya tiene mas de tres especialidades. Debes revisar ese dato antes de continuar.');
    foreach (['Medicina General','Cardiología'] as $nombre) {
        if (count($asignadas)>=2) break;
        $q=$conexion->prepare('SELECT ID_Especialidad FROM especialidad WHERE Nombre_Especialidad=?');
        $q->execute([$nombre]); $id=$q->fetchColumn();
        if ($id===false) {
            $q=$conexion->prepare('INSERT INTO especialidad (Nombre_Especialidad) VALUES (?)'); $q->execute([$nombre]);
            $id=$conexion->lastInsertId();
        }
        if (!in_array($id,$asignadas)) {
            $q=$conexion->prepare('INSERT INTO medico_especialidad (RUT_Medico,ID_Especialidad) VALUES (?,?)');
            $q->execute([$rutMedico,$id]); $asignadas[]=$id;
        }
    }
    $q=$conexion->prepare('SELECT Codigo_Centro FROM centro_medico_medico WHERE RUT_Medico=?');
    $q->execute([$rutMedico]); $centros=$q->fetchAll(PDO::FETCH_COLUMN);
    foreach ([['CentroPrueba1','San Joaquín','RM'],['CentroPrueba2','Vitacura','RM']] as $datos) {
        if (count($centros)>=2) break;
        $q=$conexion->prepare('SELECT Codigo_Centro FROM centro_medico WHERE Nombre_Centro=? AND Comuna_Centro=? AND Region_Centro=? ORDER BY Codigo_Centro LIMIT 1');
        $q->execute($datos); $id=$q->fetchColumn();
        if ($id===false) {
            $q=$conexion->prepare('INSERT INTO centro_medico (Nombre_Centro,Comuna_Centro,Region_Centro) VALUES (?,?,?)');
            $q->execute($datos); $id=$conexion->lastInsertId();
        }
        if (!in_array($id,$centros)) {
            $q=$conexion->prepare('INSERT INTO centro_medico_medico (Codigo_Centro,RUT_Medico) VALUES (?,?)');
            $q->execute([$id,$rutMedico]); $centros[]=$id;
        }
    }
    if (count($asignadas)<2 || count($centros)<2) throw new RuntimeException('No se consiguieron las asignaciones requeridas.');

    $q=$conexion->prepare('SELECT Contrasena_Hash FROM paciente WHERE RUT_Paciente=?');
    $q->execute([$rutPaciente]);
    if (!password_verify('PacientePrueba!2026',$q->fetchColumn())) throw new RuntimeException('Fallo al verificar la cuenta paciente.');
    $q=$conexion->prepare('SELECT Contrasena_Hash FROM medico WHERE RUT_Medico=?');
    $q->execute([$rutMedico]);
    if (!password_verify('MedicoPrueba!2026',$q->fetchColumn())) throw new RuntimeException('Fallo al verificar la cuenta medico.');

    if (!is_dir($directorio) && !mkdir($directorio,0700,true)) throw new RuntimeException('No se pudo crear la carpeta privada.');
    $admin=['nombre'=>$nombreAdmin,'email'=>$correoAdmin,'contrasena_hash'=>$hashAdmin,'rol'=>'Administrador'];
    $json=json_encode($admin,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    $f=fopen($rutaAdmin,'x');
    if ($f===false) throw new RuntimeException('No se pudo crear el archivo privado.');
    $archivoCreado=true;
    $bytes=fwrite($f,$json); fclose($f);
    if ($bytes!==strlen($json)) throw new RuntimeException('No se pudo guardar el archivo completo.');
    $guardado=json_decode(file_get_contents($rutaAdmin),true,512,JSON_THROW_ON_ERROR);
    if (!password_verify('AdminPrueba!2026',$guardado['contrasena_hash'])) throw new RuntimeException('Fallo al verificar la cuenta administrador.');
    $conexion->commit();
    $archivoCreado=false;

    $readme="# Cuentas de prueba\n\nBase: `$bd`\n\n"
        ."| Rol | Correo | Contraseña de prueba | RUT vinculado |\n|---|---|---|---|\n"
        ."| Paciente | $correoPaciente | PacientePrueba!2026 | $rutPaciente |\n"
        ."| Médico | $correoMedico | MedicoPrueba!2026 | $rutMedico |\n"
        ."| Administrador | $correoAdmin | AdminPrueba!2026 | No aplica |\n\n"
        ."El médico tiene ".count($centros)." centros y ".count($asignadas)." especialidades.\n\n"
        ."## Supuestos\n- Una cuenta administradora de prueba en archivo privado JSON: `$rutaAdmin`.\n"
        ."- Paciente: Email_Paciente y Contrasena_Hash en paciente. Médico: E-mail_Medico y Contrasena_Hash en medico.\n"
        ."- Los registros sin hash no son cuentas habilitadas.\n"
        ."- Contraseñas almacenadas con password_hash(PASSWORD_DEFAULT); se validan con password_verify.\n"
        ."- Política propuesta: 12 a 64 caracteres ASCII, con mayúscula, minúscula, número y símbolo. Pendiente implementar en formularios.\n"
        ."- El instalador comprueba que los correos no se repitan entre las tres fuentes.\n"
        ."- La unicidad global entre tablas/archivo deberá mantenerse en registro y edición de perfiles; los UNIQUE por tabla no bastan.\n"
        ."- No se han creado citas, atenciones, diagnósticos ni recetas.\n\n"
        ."## Comprobación\nHashes de las tres cuentas verificados desde almacenamiento. Pendiente implementar y comprobar el login, las sesiones, los permisos y la edición de perfiles. La comprobación de las cuentas estará completa cuando se pruebe el acceso real.\n\n"
        ."## Instalación en otro equipo\nRecrear estas cuentas mediante preparar_cuentas.php sobre una base sin credenciales y configurar la ruta privada. No publicar administrador.json en htdocs ni en el repositorio. El instalador no sobrescribe cuentas existentes.\n";
    $rutaReadme=dirname(__DIR__,2).DIRECTORY_SEPARATOR.'README_cuentas_prueba.md';
    if (file_exists($rutaReadme)) {
        echo "\nNo se sobrescribio el README existente. Copia este bloque:\n$readme\n";
    } elseif (file_put_contents($rutaReadme,$readme)===false) {
        echo "\nCuentas creadas, pero no se pudo guardar el README. Copia este bloque:\n$readme\n";
    } else echo "\nCredenciales documentadas en: $rutaReadme\n";
    echo "\nPREPARACION CORRECTA: tres hashes verificados.\nPaciente: $correoPaciente\nMedico: $correoMedico\nAdministrador: $correoAdmin\nArchivo privado: $rutaAdmin\n";
    echo "Falta implementar y probar el inicio de sesion.\n";
} catch (Throwable $e) {
    if ($conexion instanceof PDO && $conexion->inTransaction()) $conexion->rollBack();
    if ($archivoCreado && is_file($rutaAdmin)) unlink($rutaAdmin);
    fwrite(STDERR,"\nNo se completo la preparacion: ".$e->getMessage()."\nLas columnas agregadas pueden permanecer vacias. No vuelvas a ejecutar sin revisar el mensaje.\n");
    exit(1);
}
