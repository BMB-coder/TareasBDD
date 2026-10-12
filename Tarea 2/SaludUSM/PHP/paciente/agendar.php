<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/citas.php';
$cuenta = exigirRol('Paciente');
$rut = (string)$cuenta['id'];
$errores = [];

function entero(array $origen, string $campo): int {
    return is_string($origen[$campo] ?? null) && ctype_digit($origen[$campo]) ? (int)$origen[$campo] : 0;
}
function texto(array $origen, string $campo): string {
    return is_string($origen[$campo] ?? null) ? trim($origen[$campo]) : '';
}

$fuente = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$especialidad = entero($fuente, 'especialidad');
$centro = entero($fuente, 'centro');
$medico = strtoupper(texto($fuente, 'medico'));
$fecha = texto($fuente, 'fecha');
$reprogramar = entero($fuente, 'reprogramar');
$cita = null;
$soloMensaje = false;
$especialidades = $centros = $medicos = $bloques = [];
$medicoValido = false;
$hoy = ahora()->format('Y-m-d');
$limite = ahora()->modify('+' . DIAS_RESERVA . ' days')->format('Y-m-d');

try {
    require __DIR__ . '/../config/conexion.php';
    cancelarCitasVencidas($conexion);
    if ($reprogramar) {
        // Al reprogramar solo cambian fecha y hora: medico, centro y especialidad son los de la cita.
        $cita = cargarCitaPaciente($conexion, $rut, $reprogramar);
        if (!$cita) {
            throw new DomainException('La cita no existe o no te pertenece.');
        }
        citaModificable($conexion, $rut, $reprogramar);
        $especialidad = (int)$cita['ID_Especialidad'];
        $centro = (int)$cita['Codigo_Centro'];
        $medico = $cita['RUT_Medico'];
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $hora = texto($_POST, 'hora');
        if (!csrfValido()) {
            throw new DomainException('El formulario venció. Recarga la página.');
        }
        if ($reprogramar) {
            reprogramarCita($conexion, $rut, $reprogramar, $fecha, $hora);
            $_SESSION['aviso_citas'] = "Cita Nº $reprogramar reprogramada para el "
                . date('d-m-Y', strtotime($fecha)) . " a las $hora. Queda en estado Reservada.";
        } else {
            $nro = reservarCita($conexion, $rut, $especialidad, $centro, $medico, $fecha, $hora);
            $_SESSION['aviso_citas'] = "Cita Nº $nro reservada para el " . date('d-m-Y', strtotime($fecha)) . " a las $hora.";
        }
        header('Location: ' . BASE_ACCESO . '/paciente/mis_citas.php');
        exit;
    }
} catch (DomainException $error) {
    $errores[] = $error->getMessage();
    // Si la cita no se puede reprogramar, solo se muestra el motivo.
    $soloMensaje = $reprogramar && $_SERVER['REQUEST_METHOD'] !== 'POST';
} catch (Throwable $error) {
    error_log('SaludUSM agendar: ' . $error->getMessage());
    $errores[] = 'No se pudo completar la operación. Revisa la conexión o el log de PHP.';
}

try {
    if (isset($conexion)) {
        $especialidades = especialidadesConMedicos($conexion);
        if ($especialidad) {
            $centros = centrosParaEspecialidad($conexion, $especialidad);
        }
        if ($especialidad && $centro) {
            $medicos = medicosPara($conexion, $especialidad, $centro);
        }
        $medicoValido = $medico !== '' && in_array($medico, array_column($medicos, 'RUT_Medico'), true);
        if ($medicoValido && $fecha !== '') {
            if (!fechaReservable($fecha)) {
                // Tras un POST rechazado el motivo ya esta en $errores.
                if (!$errores) {
                    $errores[] = 'Elige un día hábil (lunes a viernes) entre hoy y los próximos ' . DIAS_RESERVA . ' días.';
                }
            } else {
                $bloques = bloquesLibres($conexion, $medico, $rut, $fecha, $reprogramar ?: null);
            }
        }
    }
} catch (Throwable $error) {
    error_log('SaludUSM agendar: ' . $error->getMessage());
    $errores[] = 'No se pudieron cargar las opciones de reserva.';
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $reprogramar ? 'Reprogramar cita' : 'Agendar hora' ?> | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1><?= $reprogramar ? 'Reprogramar cita Nº ' . escapar($reprogramar) : 'Agendar hora' ?></h1>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>
<p>Horario de atención: lunes a viernes, de 09:00 a 13:00 y de 14:00 a 18:00, en bloques de <?= DURACION_BLOQUE ?> minutos.
Puedes reservar hasta <?= DIAS_RESERVA ?> días hacia adelante.</p>

<?php if (!$soloMensaje): ?>
<form class="formulario" method="get" action="agendar.php">
<?php if ($reprogramar && $cita): ?>
    <input type="hidden" name="reprogramar" value="<?= escapar($reprogramar) ?>">
    <p>Cita actual: <strong><?= escapar(date('d-m-Y', strtotime($cita['Fecha_Cita'])) . ' ' . substr($cita['Hora_Cita'], 0, 5)) ?></strong>
    — <?= escapar($cita['Nombre_Especialidad']) ?> con <?= escapar($cita['Nombre_Medico']) ?> en <?= escapar($cita['Nombre_Centro']) ?>.</p>
    <p>Solo se cambian la fecha y la hora. Para otro médico, centro o especialidad, cancela esta cita y reserva una nueva.</p>
<?php else: ?>
    <p><label for="especialidad">1. Especialidad</label><br>
    <select id="especialidad" name="especialidad" onchange="this.form.centro.value=''; this.form.submit()">
        <option value="">Selecciona</option>
        <?php foreach ($especialidades as $e): ?>
        <option value="<?= escapar($e['ID_Especialidad']) ?>" <?= $especialidad === (int)$e['ID_Especialidad'] ? 'selected' : '' ?>><?= escapar($e['Nombre_Especialidad']) ?></option>
        <?php endforeach; ?>
    </select></p>
    <?php if ($especialidad): ?>
    <p><label for="centro">2. Centro médico</label><br>
    <select id="centro" name="centro" onchange="this.form.medico.value=''; this.form.submit()">
        <option value="">Selecciona</option>
        <?php foreach ($centros as $c): ?>
        <option value="<?= escapar($c['Codigo_Centro']) ?>" <?= $centro === (int)$c['Codigo_Centro'] ? 'selected' : '' ?>><?= escapar($c['Nombre_Centro'] . ' (' . $c['Comuna_Centro'] . ')') ?></option>
        <?php endforeach; ?>
    </select></p>
    <?php else: ?><input type="hidden" name="centro" value=""><?php endif; ?>
    <?php if ($especialidad && $centro): ?>
    <p><label for="medico">3. Médico</label><br>
    <select id="medico" name="medico" onchange="this.form.submit()">
        <option value="">Selecciona</option>
        <?php foreach ($medicos as $m): ?>
        <option value="<?= escapar($m['RUT_Medico']) ?>" <?= $medico === $m['RUT_Medico'] ? 'selected' : '' ?>><?= escapar($m['Nombre_Medico']) ?></option>
        <?php endforeach; ?>
    </select></p>
    <?php else: ?><input type="hidden" name="medico" value=""><?php endif; ?>
<?php endif; ?>
<?php if ($medicoValido): ?>
    <p><label for="fecha"><?= $reprogramar ? 'Nueva fecha' : '4. Fecha' ?></label><br>
    <input id="fecha" name="fecha" type="date" min="<?= escapar($hoy) ?>" max="<?= escapar($limite) ?>" value="<?= escapar($fecha) ?>" onchange="this.form.submit()"></p>
<?php endif; ?>
    <noscript><button class="boton" type="submit">Continuar</button></noscript>
    <button class="boton" type="submit">Ver disponibilidad</button>
</form>

<?php if ($medicoValido && $fecha !== '' && fechaReservable($fecha)): ?>
<h2>Bloques disponibles el <?= escapar(date('d-m-Y', strtotime($fecha))) ?></h2>
<?php if (!$bloques): ?>
    <p>No quedan bloques disponibles ese día. Prueba con otra fecha.</p>
<?php else: ?>
<form method="post" action="agendar.php" class="bloques">
    <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
    <input type="hidden" name="especialidad" value="<?= escapar($especialidad) ?>">
    <input type="hidden" name="centro" value="<?= escapar($centro) ?>">
    <input type="hidden" name="medico" value="<?= escapar($medico) ?>">
    <input type="hidden" name="fecha" value="<?= escapar($fecha) ?>">
    <?php if ($reprogramar): ?><input type="hidden" name="reprogramar" value="<?= escapar($reprogramar) ?>"><?php endif; ?>
    <?php foreach ($bloques as $bloque): ?>
    <button class="boton" type="submit" name="hora" value="<?= escapar($bloque) ?>"><?= escapar($bloque) ?></button>
    <?php endforeach; ?>
</form>
<p>Selecciona un bloque para <?= $reprogramar ? 'mover tu cita' : 'reservar' ?>.</p>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<p><a href="mis_citas.php">Volver a mis citas</a></p>
</main></body></html>
