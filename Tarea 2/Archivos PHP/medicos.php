<?php
session_start();
require 'conexion.php';

// Validar que solo entre el Administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Administrador') {
    header("Location: index.php");
    exit();
}

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    try {
        if ($accion == 'crear') {
            $rut = $_POST['rut'];
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $contrasena = $_POST['contrasena'];
            $id_centro = $_POST['centro'];
            $id_especialidad = $_POST['especialidad'];

            $conexion->beginTransaction();

            $stmt1 = $conexion->prepare("INSERT INTO Medico (RUT_Medico, Nombre, Email, Contrasena) VALUES (?, ?, ?, ?)");
            $stmt1->execute([$rut, $nombre, $email, $contrasena]);

            $stmt2 = $conexion->prepare("INSERT INTO Centro_Medico_Del_Medico (Codigo_Interno, RUT_Medico) VALUES (?, ?)");
            $stmt2->execute([$id_centro, $rut]);

            $stmt3 = $conexion->prepare("INSERT INTO Medico_Especialidad (RUT_Medico, ID_Especialidad, Numero_Especialidad) VALUES (?, ?, 1)");
            $stmt3->execute([$rut, $id_especialidad]);

            $conexion->commit();
            $mensaje = '<div class="alert alert-success">Médico y sus asignaciones creados con éxito.</div>';

            } elseif ($accion == 'editar') {
                $rut = $_POST['rut_medico']; // El RUT no se edita, se usa como llave
                    $nombre = $_POST['nombre'];
                    $email = $_POST['email'];
                    $id_centro = $_POST['centro'];
                    $id_especialidad = $_POST['especialidad'];

                // 1. VALIDACIÓN PREVIA (Subtarea 06.3): Proteger integridad ante citas futuras
                $stmt_check = $conexion->prepare("
                    SELECT COUNT(*) FROM Cita 
                    WHERE RUT_Medico = ? 
                    AND Fecha_Hora > NOW() 
                    AND ID_Estado IN (1, 2) -- 1: Reservada, 2: Confirmada
                ");
                $stmt_check->execute([$rut]);
                $citas_futuras = $stmt_check->fetchColumn();

                // 2. Ejecutar la Transacción de Actualización
                $conexion->beginTransaction();

                // La tabla 'Medico' (Nombre, Email) siempre se puede actualizar
                $stmt1 = $conexion->prepare("UPDATE Medico SET Nombre = ?, Email = ? WHERE RUT_Medico = ?");
                $stmt1->execute([$nombre, $email, $rut]);

                // Si el médico TIENE citas futuras, bloqueamos el cambio de Centro y Especialidad.
                if ($citas_futuras > 0) {
                    $conexion->commit();
                    $mensaje = '<div class="alert alert-warning fw-bold">Actualización Parcial: El nombre y el correo fueron actualizados. Sin embargo, por normativas de integridad, no se modificó el Centro Médico ni la Especialidad porque el doctor mantiene citas futuras programadas.</div>';
                } else {
                    // Si NO TIENE citas futuras, se actualizan sus asignaciones libremente.
                    $stmt2 = $conexion->prepare("UPDATE Centro_Medico_Del_Medico SET Codigo_Interno = ? WHERE RUT_Medico = ?");
                    $stmt2->execute([$id_centro, $rut]);

                    $stmt3 = $conexion->prepare("UPDATE Medico_Especialidad SET ID_Especialidad = ? WHERE RUT_Medico = ? AND Numero_Especialidad = 1");
                    $stmt3->execute([$id_especialidad, $rut]);

                    $conexion->commit();
                    $mensaje = '<div class="alert alert-success">Datos descriptivos y asignaciones médicas actualizados correctamente.</div>';
                }
            } elseif ($accion == 'eliminar') {
            $rut_eliminar = $_POST['rut_medico'];
            $stmt = $conexion->prepare("DELETE FROM Medico WHERE RUT_Medico = ?");
            $stmt->execute([$rut_eliminar]);
            $mensaje = '<div class="alert alert-success">Médico eliminado del sistema.</div>';
        }
    } catch(PDOException $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        
        if ($e->getCode() == 23000) {
            $mensaje = '<div class="alert alert-danger fw-bold">Operación denegada: Este médico tiene citas programadas o historial clínico. Por integridad de datos, no puede ser eliminado.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error en la base de datos: ' . $e->getMessage() . '</div>';
        }
    }
}

// Obtener datos para los Selectores del formulario y las Modales
$centros = $conexion->query("SELECT Codigo_Interno, Nombre FROM Centro_Medico")->fetchAll();
$especialidades = $conexion->query("SELECT ID_Especialidad, Nombre FROM Especialidad")->fetchAll();

// Obtener lista de médicos incluyendo los IDs para preseleccionar en la Edición
$query_medicos = "
    SELECT m.RUT_Medico, m.Nombre, m.Email, 
           e.Nombre AS Especialidad_Nombre, e.ID_Especialidad, 
           c.Nombre AS Centro_Nombre, c.Codigo_Interno 
    FROM Medico m
    LEFT JOIN Medico_Especialidad me ON m.RUT_Medico = me.RUT_Medico AND me.Numero_Especialidad = 1
    LEFT JOIN Especialidad e ON me.ID_Especialidad = e.ID_Especialidad
    LEFT JOIN Centro_Medico_Del_Medico cm ON m.RUT_Medico = cm.RUT_Medico
    LEFT JOIN Centro_Medico c ON cm.Codigo_Interno = c.Codigo_Interno
    ORDER BY m.Nombre ASC
";
$lista_medicos = $conexion->query($query_medicos)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Médicos - SaludUSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SaludUSM - Administración</a>
            <div>
                <a href="panel_admin.php" class="btn btn-sm btn-outline-light me-3">Volver al Dashboard</a>
                <a href="logout.php" class="btn btn-sm btn-danger">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4 text-primary">Gestión de Médicos y Asignaciones</h2>
        
        <?php echo $mensaje; ?>

        <!-- Formulario para Agregar Medico -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="card-title text-success mb-3">Registrar Nuevo Médico</h5>
                <form action="medicos.php" method="POST">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">RUT (Ej: 12345678-9)</label>
                            <input type="text" name="rut" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small mb-1">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">Especialidad Principal</label>
                            <select name="especialidad" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?php echo $esp['ID_Especialidad']; ?>"><?php echo htmlspecialchars($esp['Nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">Centro Médico Base</label>
                            <select name="centro" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($centros as $cen): ?>
                                    <option value="<?php echo $cen['Codigo_Interno']; ?>"><?php echo htmlspecialchars($cen['Nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-1">Contraseña</label>
                            <input type="text" name="contrasena" class="form-control" value="Medico123*" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">Registrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Listado -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body">
                <h5 class="card-title mb-3">Plantilla Médica Activa</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>RUT</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Especialidad Base</th>
                                <th>Centro Asignado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista_medicos as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['RUT_Medico']); ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($m['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($m['Email']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($m['Especialidad_Nombre'] ?? 'Sin Asignar'); ?></span></td>
                                <td><?php echo htmlspecialchars($m['Centro_Nombre'] ?? 'Sin Asignar'); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <!-- Boton Editar -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditar_<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $m['RUT_Medico']); ?>">
                                            Editar
                                        </button>

                                        <!-- Boton Eliminar -->
                                        <form action="medicos.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar al Dr/Dra <?php echo addslashes($m['Nombre']); ?>?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="rut_medico" value="<?php echo $m['RUT_Medico']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEditar_<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $m['RUT_Medico']); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Editar Asignaciones del Médico</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="medicos.php" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="accion" value="editar">
                                                <input type="hidden" name="rut_medico" value="<?php echo $m['RUT_Medico']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">RUT (No editable)</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($m['RUT_Medico']); ?>" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nombre Completo</label>
                                                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($m['Nombre']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Correo Electrónico</label>
                                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($m['Email']); ?>" required>
                                                </div>
                                                
                                                <hr>
                                                <p class="text-muted small mb-2">Modificar Asignaciones Base</p>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Especialidad Principal</label>
                                                    <select name="especialidad" class="form-select" required>
                                                        <?php foreach ($especialidades as $esp): ?>
                                                            <option value="<?php echo $esp['ID_Especialidad']; ?>" <?php echo ($esp['ID_Especialidad'] == $m['ID_Especialidad']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($esp['Nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Centro Médico Base</label>
                                                    <select name="centro" class="form-select" required>
                                                        <?php foreach ($centros as $cen): ?>
                                                            <option value="<?php echo $cen['Codigo_Interno']; ?>" <?php echo ($cen['Codigo_Interno'] == $m['Codigo_Interno']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($cen['Nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>