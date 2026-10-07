-- Procedimiento Almacenado usado para cumplir con la actualizacion de citas vencidas

DELIMITER //

CREATE PROCEDURE ActualizarCitasVencidas()
BEGIN
    -- Cambia a estado 5 (Cancelada) las citas en estado 1 (Reservada) o 2 (Confirmada)
    -- que tengan una fecha anterior al momento en que se ejecuta la funcion
    UPDATE Cita
    SET ID_Estado = 5
    WHERE ID_Estado IN (1, 2)
      AND Fecha_Hora < NOW();
END //

DELIMITER ;

-- Trigger utilizado para cumplir con el requerimiento de evitar la eliminacion de especialidades con citas futuras

DELIMITER //

CREATE TRIGGER trg_proteger_especialidad_medico
BEFORE DELETE ON Medico_Especialidad
FOR EACH ROW
BEGIN
    DECLARE v_citas_futuras INT;

    -- Contar si el medico tiene citas futuras pendientes con la especialidad que se intenta borrar
    SELECT COUNT(*) INTO v_citas_futuras
    FROM Cita
    WHERE RUT_Medico = OLD.RUT_Medico
      AND ID_Especialidad = OLD.ID_Especialidad
      AND Fecha_Hora > NOW()
      AND ID_Estado IN (1, 2); -- 1: Reservada, 2: Confirmada

    -- Si el conteo es mayor a 0, lanzamos un error cualquiera y abortamos el DELETE
    IF v_citas_futuras > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Operación denegada: No se puede quitar la especialidad porque el médico tiene citas futuras vigentes asociadas a ella.';
    END IF;
END //

DELIMITER ;

-- Vistas, centraliza las relaciones de 7 tablas distintas, facilitando las consultas de lectura desde PHP

CREATE VIEW Vista_Detalle_Citas AS
SELECT 
    c.ID_Cita,
    c.Fecha_Hora,
    p.RUT_Paciente,
    p.Nombre AS Paciente,
    pr.Tipo AS Prevision,
    m.RUT_Medico,
    m.Nombre AS Medico,
    e.Nombre AS Especialidad,
    cm.Codigo_Interno AS Codigo_Centro,
    cm.Nombre AS Centro_Medico,
    cm.Region,
    ec.ID_Estado,
    ec.Estado
FROM Cita c
JOIN Paciente p ON c.RUT_Paciente = p.RUT_Paciente
JOIN Prevision pr ON p.ID_Prevision = pr.ID_Prevision
JOIN Medico m ON c.RUT_Medico = m.RUT_Medico
JOIN Especialidad e ON c.ID_Especialidad = e.ID_Especialidad
JOIN Centro_Medico cm ON c.Codigo_Interno = cm.Codigo_Interno
JOIN Estado_Cita ec ON c.ID_Estado = ec.ID_Estado;

-- Función SQL

DELIMITER //

CREATE FUNCTION CalcularPorcentajeInasistencia(p_Codigo_Interno INT) 
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE v_total INT;
    DECLARE v_inasistencias INT;
    DECLARE v_porcentaje DECIMAL(5,2);

    -- Contar el total de citas del centro
    SELECT COUNT(*) INTO v_total 
    FROM Cita 
    WHERE Codigo_Interno = p_Codigo_Interno;

    -- Si no hay citas, el porcentaje es 0 para evitar división por cero
    IF v_total = 0 THEN
        RETURN 0.00;
    END IF;

    -- Contar las inasistencias (Estado 4 = No Asistió) del centro
    SELECT COUNT(*) INTO v_inasistencias 
    FROM Cita 
    WHERE Codigo_Interno = p_Codigo_Interno AND ID_Estado = 4;

    -- Calcular el porcentaje
    SET v_porcentaje = (v_inasistencias / v_total) * 100;

    RETURN v_porcentaje;
END //

DELIMITER ;


-- Uso en PHP
SELECT 
    Nombre AS Centro_Medico, 
    CalcularPorcentajeInasistencia(Codigo_Interno) AS Porcentaje_Inasistencia 
FROM Centro_Medico;