<?php
require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/includes/registro_paciente.php';
if (!empty($_SESSION['cuenta'])) {
    header('Location: ' . destinoRol($_SESSION['cuenta']['rol']));
    exit;
}
$datos = array_fill_keys(['rut', 'nombre', 'email', 'telefono', 'nacimiento', 'sexo', 'prevision'], '');
$errores = [];
$previsiones = [];
$disponible = false;
$exito = !empty($_SESSION['registro_exitoso']);
unset($_SESSION['registro_exitoso']);
try {
    require __DIR__ . '/config/conexion.php';
    $previsiones = $conexion->query('SELECT ID_Prevision, Prevision_Paciente FROM prevision ORDER BY Prevision_Paciente')->fetchAll(PDO::FETCH_ASSOC);
    $disponible = count($previsiones) > 0;
    if (!$disponible) {
        $errores[] = 'No hay previsiones disponibles. Se debe completar el catálogo antes de registrar pacientes.';
    }
} catch (Throwable $error) {
    error_log('SaludUSM registro: ' . $error->getMessage());
    $errores[] = 'No se pudo cargar el registro. Revisa que MySQL esté iniciado y la conexión configurada.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exito = false;
    foreach ($datos as $campo => $valor) {
        $datos[$campo] = isset($_POST[$campo]) && is_string($_POST[$campo]) ? trim($_POST[$campo]) : '';
    }
    $datos['rut'] = strtoupper($datos['rut']);
    $datos['email'] = strtolower($datos['email']);
    $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
    $confirmacion = is_string($_POST['confirmacion'] ?? null) ? $_POST['confirmacion'] : '';
    if (!csrfValido()) {
        $errores[] = 'El formulario venció. Recarga la página y vuelve a intentarlo.';
    }
    $errores = array_merge($errores, validarRegistroPaciente($datos, $clave, $confirmacion));
    if (!$errores && $disponible) {
        try {
            crearPacienteRegistrado($conexion, $datos, $clave);
            $_SESSION['registro_exitoso'] = true;
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ' . BASE_ACCESO . '/registro.php');
            exit;
        } catch (DomainException $error) {
            $errores[] = $error->getMessage();
        } catch (Throwable $error) {
            error_log('SaludUSM registro: ' . $error->getMessage());
            $errores[] = 'No fue posible completar el registro. Revisa la configuración o el log de PHP.';
        }
    }
}
$hoy = (new DateTimeImmutable('today', new DateTimeZone('America/Santiago')))->format('Y-m-d');
?>
<!doctype html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registro de pacientes | SaludUSM</title>
<link rel="stylesheet" href="../CSS/estilos.css">
</head><body><main>
<h1>Registro de pacientes</h1>
<p><a href="login.php">Iniciar sesión</a> · <a href="index.php">Inicio</a></p>
<?php if ($exito): ?>
    <p class="exito" role="status">Cuenta creada correctamente. Ya puedes iniciar sesión con tu correo y contraseña.</p>
    <a class="boton" href="login.php">Ir al inicio de sesión</a>
<?php else: ?>
    <?php if ($errores): ?><div class="error" role="alert"><ul>
        <?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?>
    </ul></div><?php endif; ?>
    <p>Todos los campos son obligatorios, excepto el teléfono.</p>
    <?php if ($disponible): ?>
    <form method="post" action="registro.php">
        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
        <p><label for="rut">RUT (sin puntos, por ejemplo 12345678-5)</label><br>
        <input id="rut" name="rut" required maxlength="10" pattern="[0-9]{8}-[0-9Kk]" value="<?= escapar($datos['rut']) ?>"></p>
        <p><label for="nombre">Nombre completo</label><br>
        <input id="nombre" name="nombre" required maxlength="100" autocomplete="name" value="<?= escapar($datos['nombre']) ?>"></p>
        <p><label for="email">Correo electrónico</label><br>
        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="<?= escapar($datos['email']) ?>"></p>
        <p><label for="telefono">Teléfono (opcional, ejemplo 912345678)</label><br>
        <input id="telefono" name="telefono" type="tel" maxlength="11" autocomplete="tel" value="<?= escapar($datos['telefono']) ?>"></p>
        <p><label for="nacimiento">Fecha de nacimiento</label><br>
        <input id="nacimiento" name="nacimiento" type="date" required min="1000-01-01" max="<?= escapar($hoy) ?>" value="<?= escapar($datos['nacimiento']) ?>"></p>
        <p><label for="sexo">Sexo</label><br><select id="sexo" name="sexo" required>
        <option value="">Selecciona</option>
        <?php foreach (['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'] as $valor => $etiqueta): ?>
        <option value="<?= escapar($valor) ?>" <?= $datos['sexo'] === $valor ? 'selected' : '' ?>><?= escapar($etiqueta) ?></option>
        <?php endforeach; ?></select></p>
        <p><label for="prevision">Previsión</label><br><select id="prevision" name="prevision" required>
        <option value="">Selecciona</option>
        <?php foreach ($previsiones as $prevision): ?>
        <option value="<?= escapar($prevision['ID_Prevision']) ?>" <?= $datos['prevision'] === (string)$prevision['ID_Prevision'] ? 'selected' : '' ?>><?= escapar($prevision['Prevision_Paciente']) ?></option>
        <?php endforeach; ?></select></p>
        <p id="reglas">Contraseña: al menos 8 caracteres, incluyendo mayúscula A-Z, minúscula a-z y un número.</p>
        <p><label for="clave">Contraseña</label><br><input id="clave" name="clave" type="password" required minlength="8" autocomplete="new-password" aria-describedby="reglas"></p>
        <p><label for="confirmacion">Repite la contraseña</label><br><input id="confirmacion" name="confirmacion" type="password" required autocomplete="new-password"></p>
        <button class="boton" type="submit">Crear cuenta de paciente</button>
    </form>
    <?php endif; ?>
<?php endif; ?>
</main></body></html>
