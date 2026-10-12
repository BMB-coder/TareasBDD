<?php
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/gestion_admin.php';
$cuenta = exigirRol('Administrador');
$errores = [];
$aviso = $_SESSION['aviso_admin'] ?? '';
unset($_SESSION['aviso_admin']);
$medicos = $especialidadesCatalogo = $centrosCatalogo = [];
$editando = null;
$datos = ['rut' => '', 'nombre' => '', 'email' => ''];
$especialidades = $centros = [];

function cargarListados(PDO $db): array {
    return [listarMedicos($db),
        $db->query('SELECT ID_Especialidad, Nombre_Especialidad FROM especialidad ORDER BY Nombre_Especialidad')->fetchAll(PDO::FETCH_ASSOC),
        $db->query('SELECT Codigo_Centro, Nombre_Centro, Comuna_Centro FROM centro_medico ORDER BY Nombre_Centro')->fetchAll(PDO::FETCH_ASSOC)];
}

try {
    require __DIR__ . '/../config/conexion.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = is_string($_POST['accion'] ?? null) ? $_POST['accion'] : '';
        foreach ($datos as $campo => $valor) {
            $datos[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
        }
        $datos['rut'] = strtoupper($datos['rut']);
        $datos['email'] = strtolower($datos['email']);
        $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
        $confirmacion = is_string($_POST['confirmacion'] ?? null) ? $_POST['confirmacion'] : '';
        $especialidades = idsFormulario($_POST['especialidades'] ?? []);
        $centros = idsFormulario($_POST['centros'] ?? []);
        if (!csrfValido()) {
            $errores[] = 'El formulario venció. Recarga la página.';
        } elseif ($accion === 'eliminar') {
            eliminarMedico($conexion, $datos['rut']);
            $_SESSION['aviso_admin'] = 'Médico eliminado.';
        } elseif ($accion === 'crear' || $accion === 'editar') {
            $nuevo = $accion === 'crear';
            if (!$nuevo) {
                $editando = cargarMedico($conexion, $datos['rut']);
                if (!$editando) {
                    throw new DomainException('El médico ya no existe.');
                }
            }
            $errores = validarMedico($datos, $nuevo, $clave, $confirmacion, $especialidades, $centros);
            if (!$errores) {
                guardarMedico($conexion, $nuevo ? null : $datos['rut'], $datos, $clave, $especialidades, $centros);
                $_SESSION['aviso_admin'] = $nuevo ? 'Médico creado. Ya puede iniciar sesión con su correo y contraseña.'
                                                  : 'Médico actualizado.';
            }
        } else {
            $errores[] = 'Acción no válida.';
        }
        if (!$errores) {
            header('Location: ' . BASE_ACCESO . '/admin/medicos.php');
            exit;
        }
    } elseif (is_string($_GET['editar'] ?? null)) {
        $editando = cargarMedico($conexion, $_GET['editar']);
        if (!$editando) {
            $errores[] = 'El médico solicitado no existe.';
        } else {
            $datos = ['rut' => $editando['RUT_Medico'], 'nombre' => $editando['Nombre_Medico'],
                      'email' => $editando['Email']];
            $especialidades = $editando['especialidades'];
            $centros = $editando['centros'];
        }
    }
} catch (DomainException $error) {
    $errores[] = $error->getMessage();
} catch (Throwable $error) {
    error_log('SaludUSM medicos admin: ' . $error->getMessage());
    $errores[] = 'No se pudo completar la operación. Revisa la conexión o el log de PHP.';
}
try {
    if (isset($conexion)) {
        [$medicos, $especialidadesCatalogo, $centrosCatalogo] = cargarListados($conexion);
    }
} catch (Throwable $error) {
    error_log('SaludUSM medicos admin: ' . $error->getMessage());
    $errores[] = 'No se pudieron cargar los listados.';
}
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Médicos | SaludUSM</title>
<link rel="stylesheet" href="../../CSS/estilos.css">
</head><body>
<?php require __DIR__ . '/../includes/nav_cuenta.php'; ?>
<main>
<h1>Médicos</h1>
<?php if ($aviso !== ''): ?><p class="exito" role="status"><?= escapar($aviso) ?></p><?php endif; ?>
<?php if ($errores): ?><div class="error" role="alert"><ul>
<?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
</ul></div><?php endif; ?>

<h2><?= $editando ? 'Editar médico' : 'Nuevo médico' ?></h2>
<form class="formulario" method="post" action="medicos.php">
    <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
    <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
    <?php if ($editando): ?>
    <input type="hidden" name="rut" value="<?= escapar($datos['rut']) ?>">
    <p>RUT: <strong><?= escapar($datos['rut']) ?></strong> (no editable)</p>
    <?php else: ?>
    <p><label for="rut">RUT (sin puntos, por ejemplo 12345678-5)</label><br>
    <input id="rut" name="rut" required maxlength="10" pattern="[0-9]{8}-[0-9Kk]" value="<?= escapar($datos['rut']) ?>"></p>
    <?php endif; ?>
    <p><label for="nombre">Nombre completo</label><br>
    <input id="nombre" name="nombre" required maxlength="100" value="<?= escapar($datos['nombre']) ?>"></p>
    <p><label for="email">Correo (también es su usuario)</label><br>
    <input id="email" name="email" type="email" required maxlength="100" value="<?= escapar($datos['email']) ?>"></p>
    <fieldset><legend>Especialidades (entre 1 y <?= MAX_ESPECIALIDADES ?>)</legend>
    <div class="opciones">
    <?php foreach ($especialidadesCatalogo as $esp): ?>
        <label><input type="checkbox" name="especialidades[]" value="<?= escapar($esp['ID_Especialidad']) ?>"
            <?= in_array((int)$esp['ID_Especialidad'], $especialidades, true) ? 'checked' : '' ?>>
            <?= escapar($esp['Nombre_Especialidad']) ?></label>
    <?php endforeach; ?>
    </div></fieldset>
    <fieldset><legend>Centros donde atiende (al menos 1)</legend>
    <div class="opciones">
    <?php foreach ($centrosCatalogo as $cen): ?>
        <label><input type="checkbox" name="centros[]" value="<?= escapar($cen['Codigo_Centro']) ?>"
            <?= in_array((int)$cen['Codigo_Centro'], $centros, true) ? 'checked' : '' ?>>
            <?= escapar($cen['Nombre_Centro'] . ' (' . $cen['Comuna_Centro'] . ')') ?></label>
    <?php endforeach; ?>
    </div></fieldset>
    <fieldset><legend><?= $editando ? 'Nueva contraseña (opcional)' : 'Contraseña inicial' ?></legend>
    <p>Al menos 8 caracteres, con mayúscula A-Z, minúscula a-z y número.<?= $editando ? ' Deja ambos campos vacíos para conservarla.' : '' ?></p>
    <p><label for="clave">Contraseña</label><br>
    <input id="clave" name="clave" type="password" autocomplete="new-password" <?= $editando ? '' : 'required minlength="8"' ?>></p>
    <p><label for="confirmacion">Repite la contraseña</label><br>
    <input id="confirmacion" name="confirmacion" type="password" autocomplete="new-password" <?= $editando ? '' : 'required' ?>></p>
    </fieldset>
    <button class="boton" type="submit"><?= $editando ? 'Guardar cambios' : 'Crear médico' ?></button>
    <?php if ($editando): ?><a href="medicos.php">Cancelar</a><?php endif; ?>
</form>
<p>No se puede quitar una especialidad o un centro si el médico tiene citas futuras (Reservadas o Confirmadas) asociadas,
ni si tiene citas pasadas con esa asignación, porque forman parte del historial.</p>

<h2>Médicos registrados</h2>
<div class="tabla-contenedor"><table>
<thead><tr><th scope="col">RUT</th><th scope="col">Nombre</th><th scope="col">Correo</th><th scope="col">Especialidades</th>
<th scope="col">Centros</th><th scope="col">Citas futuras</th><th scope="col">Acciones</th></tr></thead>
<tbody>
<?php foreach ($medicos as $medico): ?>
<tr>
    <td class="sin-salto"><?= escapar($medico['RUT_Medico']) ?></td>
    <td><?= escapar($medico['Nombre_Medico']) ?></td>
    <td><?= escapar($medico['Email']) ?></td>
    <td><?= escapar($medico['Especialidades'] ?? '') ?></td>
    <td><?= escapar($medico['Centros'] ?? '') ?></td>
    <td><?= (int)$medico['Citas_Futuras'] ?></td>
    <td class="acciones">
        <a href="medicos.php?editar=<?= escapar(urlencode($medico['RUT_Medico'])) ?>">Editar</a>
        <form method="post" action="medicos.php" onsubmit="return confirm('¿Eliminar este médico?');">
            <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="rut" value="<?= escapar($medico['RUT_Medico']) ?>">
            <button class="peligro" type="submit">Eliminar</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<p>Un médico con citas registradas no se puede eliminar, para conservar el historial clínico.</p>
</main></body></html>
