<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/citas.php';
$cuenta = exigirRol('Medico');
$rut = (string)$cuenta['id'];
$errores = [];
$aviso = $_SESSION['aviso_agenda'] ?? '';
unset($_SESSION['aviso_agenda']);
$zona = new DateTimeZone(ZONA_HORARIA);
$fuente = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$fecha = is_string($fuente['fecha'] ?? null) ? $fuente['fecha'] : '';
$dia = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, $zona);
if (!$dia || $dia->format('Y-m-d') !== $fecha) {
    $dia = new DateTimeImmutable('today', $zona);
    $fecha = $dia->format('Y-m-d');
}
$citas = [];
try {
    require __DIR__ . '/../config/conexion.php';
    cancelarCitasVencidas($conexion);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nro = is_string($_POST['nro'] ?? null) && ctype_digit($_POST['nro']) ? (int)$_POST['nro'] : 0;
        $estado = is_string($_POST['estado'] ?? null) ? $_POST['estado'] : '';
        if (!csrfValido()) {
            throw new DomainException('El formulario venció. Recarga la página.');
        }
        cambiarEstadoMedico($conexion, $rut, $nro, $estado);
        $_SESSION['aviso_agenda'] = "Cita Nº $nro actualizada a $estado.";
        header('Location: ' . BASE_ACCESO . '/medico/agenda.php?fecha=' . urlencode($fecha));
        exit;
    }
} catch (DomainException $error) {
    $errores[] = $error->getMessage();
} catch (Throwable $error) {
    error_log('SaludUSM agenda: ' . $error->getMessage());
    $errores[] = 'No se pudo completar la operación. Revisa la conexión o el log de PHP.';
}
try {
    if (isset($conexion)) {
        $citas = agendaMedico($conexion, $rut, $fecha);
    }
} catch (Throwable $error) {
    error_log('SaludUSM agenda: ' . $error->getMessage());
    $errores[] = 'No se pudo cargar la agenda.';
}
$dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Agenda | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1>Agenda — <?= escapar($dias[(int)$dia->format('N')] . ' ' . $dia->format('d-m-Y')) ?></h1>
<?php if ($aviso !== ''): ?><p class="exito" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>
<form class="acciones" method="get" action="agenda.php">
    <a href="agenda.php?fecha=<?= escapar($dia->modify('-1 day')->format('Y-m-d')) ?>">← Día anterior</a>
    <label for="fecha">Día</label>
    <input id="fecha" name="fecha" type="date" value="<?= escapar($fecha) ?>" onchange="this.form.submit()">
    <button class="boton" type="submit">Ver</button>
    <a href="agenda.php">Hoy</a>
    <a href="agenda.php?fecha=<?= escapar($dia->modify('+1 day')->format('Y-m-d')) ?>">Día siguiente →</a>
</form>

<?php if (!$citas): ?>
<p>No tienes citas este día.</p>
<?php else: ?>
<div class="tabla-contenedor"><table>
<thead><tr><th scope="col">Hora</th><th scope="col">Nº</th><th scope="col">Paciente</th><th scope="col">Previsión</th>
<th scope="col">Centro</th><th scope="col">Especialidad</th><th scope="col">Estado</th><th scope="col">Cambiar estado</th></tr></thead>
<tbody>
<?php foreach ($citas as $cita): $opciones = estadosPermitidosMedico($cita); ?>
<tr>
    <td><?= escapar($cita['Hora']) ?></td>
    <td><?= escapar($cita['Nro_Cita']) ?></td>
    <td><?= $cita['RUT_Paciente'] === null ? '<em>Paciente eliminado</em>'
        : escapar($cita['Nombre_Paciente']) . '<br><small>' . escapar($cita['RUT_Paciente']) . '</small>' ?></td>
    <td><?= escapar($cita['Prevision_Paciente'] ?? '—') ?></td>
    <td><?= escapar($cita['Nombre_Centro']) ?></td>
    <td><?= escapar($cita['Nombre_Especialidad']) ?></td>
    <td><?= escapar($cita['Estado_Cita']) ?><?= $cita['Nro_Atencion'] !== null ? '<br><small>Atención registrada</small>' : '' ?></td>
    <td>
        <?php if ($opciones): ?>
        <form class="acciones" method="post" action="agenda.php">
            <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
            <input type="hidden" name="fecha" value="<?= escapar($fecha) ?>">
            <input type="hidden" name="nro" value="<?= escapar($cita['Nro_Cita']) ?>">
            <select name="estado" aria-label="Nuevo estado de la cita <?= escapar($cita['Nro_Cita']) ?>">
                <?php foreach ($opciones as $opcion): ?>
                <option value="<?= escapar($opcion) ?>"><?= escapar($opcion) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="boton" type="submit">Guardar</button>
        </form>
        <?php else: ?>—<?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<p>Confirmada: antes de la hora de la cita. Atendida o No Asistió: desde la hora de la cita.
Las citas Reservadas o Confirmadas de días anteriores se cancelan automáticamente.</p>
</main></body></html>
