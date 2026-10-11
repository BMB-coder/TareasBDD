<?php
// Borrado fisico del paciente. Conserva citas no elegibles y su informacion clinica.
function darBajaPaciente(PDO $db, string $rut, string $clave): int {
    $base = (string)$db->query('SELECT DATABASE()')->fetchColumn();
    $bloqueo = 'saludusm_cuentas_' . substr(hash('sha256', $base), 0, 40);
    $q = $db->prepare('SELECT GET_LOCK(?, 5)');
    $q->execute([$bloqueo]);
    if ((int)$q->fetchColumn() !== 1) {
        throw new DomainException('Hay otra operación en curso. Inténtalo nuevamente.');
    }
    try {
        $motores = $db->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('paciente','cita','atencion')")->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach (['paciente','cita','atencion'] as $tabla) {
            if (strtoupper((string)($motores[$tabla] ?? '')) !== 'INNODB') {
                throw new RuntimeException('Se requiere InnoDB en ' . $tabla);
            }
        }
        // Impedir el borrado si la migracion requerida no esta aplicada.
        $nullable = $db->query("SELECT IS_NULLABLE FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cita' AND COLUMN_NAME='RUT_Paciente'")->fetchColumn();
        $fk = $db->query("SELECT r.DELETE_RULE, k.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_COLUMN_NAME
            FROM information_schema.REFERENTIAL_CONSTRAINTS r
            JOIN information_schema.KEY_COLUMN_USAGE k
              ON k.CONSTRAINT_SCHEMA=r.CONSTRAINT_SCHEMA AND k.TABLE_NAME=r.TABLE_NAME
              AND k.CONSTRAINT_NAME=r.CONSTRAINT_NAME
            WHERE r.CONSTRAINT_SCHEMA=DATABASE() AND r.REFERENCED_TABLE_NAME='paciente'")->fetchAll(PDO::FETCH_ASSOC);
        if ($nullable !== 'YES' || count($fk) !== 1 || $fk[0]['TABLE_NAME'] !== 'cita'
            || $fk[0]['COLUMN_NAME'] !== 'RUT_Paciente' || $fk[0]['REFERENCED_COLUMN_NAME'] !== 'RUT_Paciente'
            || $fk[0]['DELETE_RULE'] !== 'SET NULL') {
            throw new DomainException('La estructura no corresponde a la eliminación con historial conservado. Revisa la FK y las referencias a paciente.');
        }
        $db->beginTransaction();
        $q = $db->prepare('SELECT Contrasena_Hash FROM paciente WHERE RUT_Paciente = ? FOR UPDATE');
        $q->execute([$rut]);
        $hash = $q->fetchColumn();
        if (!is_string($hash) || $hash === ''
            || !hash_equals(hash('sha256', $hash), (string)($_SESSION['firma_cuenta'] ?? ''))) {
            throw new DomainException('La cuenta cambió o ya no existe. Vuelve a iniciar sesión.');
        }
        if ($clave === '' || strlen($clave) > 4096 || !password_verify($clave, $hash)) {
            throw new DomainException('La contraseña actual es incorrecta. No se realizaron cambios.');
        }
        $estados = $db->query("SELECT Estado_Cita, ID_Estado FROM estado
            WHERE Estado_Cita IN ('Reservada','Confirmada')")->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach (['Reservada','Confirmada'] as $estado) {
            if (!isset($estados[$estado])) { throw new RuntimeException('Falta estado ' . $estado); }
        }
        // Bloquear las citas antes de revisar sus atenciones: evita borrarlas
        // mientras otra transaccion intenta crear una atencion mediante su FK.
        $q = $db->prepare('SELECT Nro_Cita, Fecha_Cita, Hora_Cita, ID_Estado
            FROM cita WHERE RUT_Paciente = ? ORDER BY Nro_Cita FOR UPDATE');
        $q->execute([$rut]);
        $citas = $q->fetchAll(PDO::FETCH_ASSOC);
        $ahora = (new DateTimeImmutable('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');
        $atencion = $db->prepare('SELECT Nro_Atencion FROM atencion WHERE Nro_Cita = ? FOR UPDATE');
        $borrar = $db->prepare('DELETE FROM cita WHERE Nro_Cita = ? AND RUT_Paciente = ?');
        $eliminadas = 0;
        foreach ($citas as $cita) {
            $esActiva = in_array((int)$cita['ID_Estado'], [(int)$estados['Reservada'], (int)$estados['Confirmada']], true);
            $fechaHora = $cita['Fecha_Cita'] . ' ' . $cita['Hora_Cita'];
            if (!$esActiva || $fechaHora < $ahora) { continue; }
            $atencion->execute([$cita['Nro_Cita']]);
            $tieneAtencion = $atencion->fetchColumn() !== false;
            $atencion->closeCursor();
            if ($tieneAtencion) { continue; }
            $borrar->execute([$cita['Nro_Cita'], $rut]);
            $eliminadas += $borrar->rowCount();
        }
        $q = $db->prepare('DELETE FROM paciente WHERE RUT_Paciente = ?');
        $q->execute([$rut]);
        if ($q->rowCount() !== 1) { throw new RuntimeException('No se elimino el paciente esperado.'); }
        // ON DELETE SET NULL desvincula las citas conservadas.
        $db->commit();
        return $eliminadas;
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw $error;
    } finally {
        $q = $db->prepare('SELECT RELEASE_LOCK(?)');
        $q->execute([$bloqueo]);
    }
}
