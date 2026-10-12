<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/citas.php';

$cuenta = obtenerCuentaAutenticada();
$rol = $cuenta['rol']; // 'Administrador', 'Medico' o 'Paciente'
$rutUsuario = (string)$cuenta['id'];

if (!in_array($rol, ['Administrador', 'Medico', 'Paciente'])) {
    header('Location: /login.php');
    exit;
}

$errores = [];
$resultados = [];
$centros = [];
$estados = [];

$filtros = [
    'fecha' => $_GET['fecha'] ?? '',
    'centro' => $_GET['centro'] ?? '',
    'estado' => $_GET['estado'] ?? '',
    'busqueda_medico' => $_GET['busqueda_medico'] ?? ''
];

try {
    require __DIR__ . '/../config/conexion.php';
    
    $centros = $conexion->query("SELECT Codigo_Centro, Nombre_Centro FROM centro_medico ORDER BY Nombre_Centro")->fetchAll(PDO::FETCH_ASSOC);
    $estados = $conexion->query("SELECT Estado_Cita FROM estado ORDER BY ID_Estado")->fetchAll(PDO::FETCH_ASSOC);
    $resultados = buscarCitas($conexion, $filtros, $rol, $rutUsuario);

} catch (Throwable $error) {
    error_log('SaludUSM buscador: ' . $error->getMessage());
    $errores[] = 'Ocurrió un error al realizar la búsqueda. Revisa la conexión.';
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Buscador Avanzado de Citas | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1>Buscador y Filtros de Citas (Panel <?= $rol ?>)</h1>

<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>

<form method="get" action="buscador.php" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd;">
    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label for="fecha" style="font-weight: bold; font-size: 0.9em;">Fecha exacta:</label><br>
            <input type="date" id="fecha" name="fecha" value="<?= escapar($filtros['fecha']) ?>" style="padding: 5px;">
        </div>
        <div>
            <label for="centro" style="font-weight: bold; font-size: 0.9em;">Centro Médico:</label><br>
            <select id="centro" name="centro" style="padding: 5px;">
                <option value="">Todos los centros</option>
                <?php foreach ($centros as $c): ?>
                    <option value="<?= escapar($c['Codigo_Centro']) ?>" <?= $filtros['centro'] == $c['Codigo_Centro'] ? 'selected' : '' ?>>
                        <?= escapar($c['Nombre_Centro']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="estado" style="font-weight: bold; font-size: 0.9em;">Estado de Cita:</label><br>
            <select id="estado" name="estado" style="padding: 5px;">
                <option value="">Todos los estados</option>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= escapar($e['Estado_Cita']) ?>" <?= $filtros['estado'] === $e['Estado_Cita'] ? 'selected' : '' ?>>
                        <?= escapar($e['Estado_Cita']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if ($rol !== 'Medico'): ?>
        <div>
            <label for="busqueda_medico" style="font-weight: bold; font-size: 0.9em;">Médico o Especialidad:</label><br>
            <input type="text" id="busqueda_medico" name="busqueda_medico" placeholder="Ej: Cardiología o Dr. Pérez" value="<?= escapar($filtros['busqueda_medico']) ?>" style="padding: 5px; width: 220px;">
        </div>
        <?php endif; ?>

        <div>
            <button type="submit" class="boton">Buscar</button>
            <a href="buscador.php" class="boton" style="background: #6c757d;">Limpiar Filtros</a>
        </div>
    </div>
</form>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2>Resultados de la búsqueda</h2>
    <p style="background: #e9ecef; padding: 5px 15px; border-radius: 15px; font-weight: bold;">
        Total de citas encontradas: <?= count($resultados) ?>
    </p>
</div>

<?php if (!$resultados): ?>
    <p>No se encontraron citas que coincidan con los filtros aplicados. Intenta borrar algunos criterios.</p>
<?php else: ?>
    <div class="tabla-contenedor"><table>
    <thead><tr>
        <th scope="col">Fecha</th>
        <th scope="col">Hora</th>
        <?php if ($rol !== 'Paciente'): ?><th scope="col">Paciente</th><?php endif; ?>
        <?php if ($rol !== 'Medico'): ?><th scope="col">Médico</th><?php endif; ?>
        <th scope="col">Especialidad</th>
        <th scope="col">Centro</th>
        <th scope="col">Estado</th>
    </tr></thead>
    <tbody>
    <?php foreach ($resultados as $cita): ?>
    <tr>
        <td class="sin-salto"><?= escapar($cita['Fecha_Cita']) ?></td>
        <td><?= escapar($cita['Hora']) ?></td>
        
        <?php if ($rol !== 'Paciente'): ?>
        <td>
            <?= $cita['RUT_Paciente'] === null ? '<em>Paciente eliminado</em>' : escapar($cita['Nombre_Paciente']) ?>
        </td>
        <?php endif; ?>
        
        <?php if ($rol !== 'Medico'): ?>
        <td style="font-weight: 500;"><?= escapar($cita['Nombre_Medico']) ?></td>
        <?php endif; ?>
        
        <td><?= escapar($cita['Nombre_Especialidad']) ?></td>
        <td><?= escapar($cita['Nombre_Centro']) ?></td>
        <td><strong><?= escapar($cita['Estado_Cita']) ?></strong></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table></div>
<?php endif; ?>
</main></body></html>
