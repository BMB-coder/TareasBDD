<?php
// Menu para cuentas con sesion iniciada: los enlaces dependen del rol.
$enlacesRol = [
    'Paciente' => [['/paciente/index.php', 'Mi panel'], ['/paciente/mis_citas.php', 'Mis citas'],
                   ['/paciente/agendar.php', 'Agendar hora']],
    'Medico' => [['/medico/index.php', 'Mi panel'], ['/medico/agenda.php', 'Agenda']],
    'Administrador' => [['/admin/index.php', 'Panel'], ['/admin/centros.php', 'Centros médicos'],
                        ['/admin/medicos.php', 'Médicos']]
];
?>
<nav aria-label="Navegación de la cuenta">
    <?php foreach ($enlacesRol[$_SESSION['cuenta']['rol'] ?? ''] ?? [] as [$ruta, $texto]): ?>
    <a href="<?= escapar(BASE_ACCESO . $ruta) ?>"><?= escapar($texto) ?></a>
    <?php endforeach; ?>
    <a href="<?= escapar(BASE_ACCESO) ?>/perfil.php">Mi perfil</a>
    <form class="nav-salir" method="post" action="<?= escapar(BASE_ACCESO) ?>/logout.php">
        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
        <button type="submit">Cerrar sesión</button>
    </form>
</nav>
