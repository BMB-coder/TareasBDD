<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/gestion_admin.php';
$cuenta = exigirRol('Administrador');
$errores = [];
$aviso = $_SESSION['aviso_admin'] ?? '';
unset($_SESSION['aviso_admin']);
$centros = [];
$editando = null;
$datos = ['nombre' => '', 'comuna' => '', 'region' => ''];
try {
    require __DIR__ . '/../config/conexion.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = is_string($_POST['accion'] ?? null) ? $_POST['accion'] : '';
        $codigo = is_string($_POST['codigo'] ?? null) && ctype_digit($_POST['codigo']) ? (int)$_POST['codigo'] : 0;
        foreach ($datos as $campo => $valor) {
            $datos[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
        }
        if (!csrfValido()) {
            $errores[] = 'El formulario venció. Recarga la página.';
        } elseif ($accion === 'eliminar') {
            eliminarCentro($conexion, $codigo);
            $_SESSION['aviso_admin'] = 'Centro eliminado.';
        } elseif ($accion === 'crear' || $accion === 'editar') {
            $errores = validarCentro($datos);
            if (!$errores && $accion === 'crear') {
                $nuevo = crearCentro($conexion, $datos);
                $_SESSION['aviso_admin'] = "Centro creado con el código $nuevo.";
            } elseif (!$errores) {
                actualizarCentro($conexion, $codigo, $datos);
                $_SESSION['aviso_admin'] = 'Centro actualizado.';
            }
            if ($errores && $accion === 'editar') {
                $editando = cargarCentro($conexion, $codigo);
            }
        } else {
            $errores[] = 'Acción no válida.';
        }
        if (!$errores) {
            header('Location: ' . BASE_ACCESO . '/admin/centros.php');
            exit;
        }
    } elseif (is_string($_GET['editar'] ?? null) && ctype_digit($_GET['editar'])) {
        $editando = cargarCentro($conexion, (int)$_GET['editar']);
        if (!$editando) {
            $errores[] = 'El centro solicitado no existe.';
        } else {
            $datos = ['nombre' => $editando['Nombre_Centro'], 'comuna' => $editando['Comuna_Centro'],
                      'region' => $editando['Region_Centro']];
        }
    }
    $centros = listarCentros($conexion);
} catch (DomainException $error) {
    $errores[] = $error->getMessage();
    $centros = isset($conexion) ? listarCentros($conexion) : [];
} catch (Throwable $error) {
    error_log('SaludUSM centros: ' . $error->getMessage());
    $errores[] = 'No se pudo completar la operación. Revisa la conexión o el log de PHP.';
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Centros médicos | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1>Centros médicos</h1>
<?php if ($aviso !== ''): ?><p class="exito" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>

<h2><?= $editando ? 'Editar centro ' . escapar($editando['Codigo_Centro']) : 'Nuevo centro' ?></h2>
<form class="formulario" method="post" action="centros.php">
    <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
    <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
    <?php if ($editando): ?>
    <input type="hidden" name="codigo" value="<?= escapar($editando['Codigo_Centro']) ?>">
    <p>Código: <strong><?= escapar($editando['Codigo_Centro']) ?></strong> (asignado por el sistema, no editable)</p>
    <?php else: ?>
    <p>El código se asigna automáticamente al crear el centro.</p>
    <?php endif; ?>
    <p><label for="nombre">Nombre</label><br>
    <input id="nombre" name="nombre" required maxlength="100" value="<?= escapar($datos['nombre']) ?>"></p>
    <p><label for="comuna">Comuna</label><br>
    <input id="comuna" name="comuna" required maxlength="100" value="<?= escapar($datos['comuna']) ?>"></p>
    <p><label for="region">Región</label><br><select id="region" name="region" required>
        <option value="">Selecciona</option>
        <?php foreach (REGIONES as $codigoRegion => $nombreRegion): ?>
        <option value="<?= escapar($codigoRegion) ?>" <?= $datos['region'] === $codigoRegion ? 'selected' : '' ?>><?= escapar($codigoRegion . ' — ' . $nombreRegion) ?></option>
        <?php endforeach; ?>
    </select></p>
    <button class="boton" type="submit"><?= $editando ? 'Guardar cambios' : 'Crear centro' ?></button>
    <?php if ($editando): ?><a href="centros.php">Cancelar</a><?php endif; ?>
</form>

<h2>Centros registrados</h2>
<div class="tabla-contenedor"><table>
<thead><tr><th scope="col">Código</th><th scope="col">Nombre</th><th scope="col">Comuna</th>
<th scope="col">Región</th><th scope="col">Médicos</th><th scope="col">Citas</th><th scope="col">Acciones</th></tr></thead>
<tbody>
<?php foreach ($centros as $centro): ?>
<tr>
    <td><?= escapar($centro['Codigo_Centro']) ?></td>
    <td><?= escapar($centro['Nombre_Centro']) ?></td>
    <td><?= escapar($centro['Comuna_Centro']) ?></td>
    <td><?= escapar($centro['Region_Centro'] . ' — ' . (REGIONES[$centro['Region_Centro']] ?? '')) ?></td>
    <td><?= (int)$centro['Medicos'] ?></td>
    <td><?= (int)$centro['Citas'] ?></td>
    <td class="acciones">
        <a href="centros.php?editar=<?= escapar($centro['Codigo_Centro']) ?>">Editar</a>
        <form method="post" action="centros.php" onsubmit="return confirm('¿Eliminar este centro?');">
            <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="codigo" value="<?= escapar($centro['Codigo_Centro']) ?>">
            <button class="peligro" type="submit">Eliminar</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<p>Un centro con citas registradas no se puede eliminar, para conservar el historial clínico.</p>
</main></body></html>
