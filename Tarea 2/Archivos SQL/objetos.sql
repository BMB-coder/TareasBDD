-- Procedimiento Almacenado usado para cumplir con la actualizacion de citas vencidas y poblamiento de BD

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

DELIMITER //

CREATE PROCEDURE PoblarBD()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_id_cita INT;
    DECLARE v_id_atencion INT;
    DECLARE v_estado INT;
    DECLARE v_id_med_general INT;
    DECLARE v_rut_medico VARCHAR(10);
    DECLARE v_codigo_centro INT;
    DECLARE v_id_prevision INT;

    -- Obtener la especialidad base dinámicamente
    SELECT ID_Especialidad INTO v_id_med_general FROM Especialidad WHERE Nombre = 'Medicina General' LIMIT 1;

    -- 1. Insertar 15 Médicos
    SET i = 1;
    WHILE i <= 15 DO
        SET v_rut_medico = CONCAT(LPAD(i, 8, '0'), '-K');
        SET v_codigo_centro = 1 + (i % 4);
        
        INSERT INTO Medico (RUT_Medico, Nombre, Email, Contrasena) VALUES 
            (v_rut_medico, CONCAT('Dr. Automático ', i), CONCAT('doc', i, '@saludusm.cl'), 'Medico123*');
        INSERT INTO Centro_Medico_Del_Medico (Codigo_Interno, RUT_Medico) VALUES (v_codigo_centro, v_rut_medico);
        INSERT INTO Medico_Especialidad (RUT_Medico, ID_Especialidad, Numero_Especialidad) VALUES (v_rut_medico, v_id_med_general, 1);
        
        SET i = i + 1;
    END WHILE;

    -- 2. Insertar 40 Pacientes (con búsqueda dinámica de ID de previsión)
    SET i = 1;
    WHILE i <= 40 DO
        IF (i % 3) = 0 THEN
            SELECT ID_Prevision INTO v_id_prevision FROM Prevision WHERE Tipo = 'Fonasa' LIMIT 1;
        ELSEIF (i % 3) = 1 THEN
            SELECT ID_Prevision INTO v_id_prevision FROM Prevision WHERE Tipo = 'Isapre' LIMIT 1;
        ELSE
            SELECT ID_Prevision INTO v_id_prevision FROM Prevision WHERE Tipo = 'Particular' LIMIT 1;
        END IF;

        -- Valor por defecto por si falla el SELECT
        IF v_id_prevision IS NULL THEN
            SET v_id_prevision = 1;
        END IF;

        INSERT INTO Paciente (RUT_Paciente, Nombre, Fecha_Nacimiento, Sexo, ID_Prevision, Comuna, Contrasena) VALUES
            (CONCAT(LPAD((i + 100), 8, '0'), '-9'), CONCAT('Paciente Genérico ', i), '1990-05-14', 'F', v_id_prevision, 'Valparaíso', 'Paciente123*');
        SET i = i + 1;
    END WHILE;

    -- 3. Insertar 120 Citas
    SET i = 1;
    WHILE i <= 120 DO
        -- DISTRIBUCIÓN DE ESTADOS CORREGIDA
        IF i <= 65 THEN 
            SET v_estado = 3; 
        ELSE 
            CASE (i % 4)
                WHEN 0 THEN SET v_estado = 1; -- Reservada
                WHEN 1 THEN SET v_estado = 2; -- Confirmada
                WHEN 2 THEN SET v_estado = 4; -- No Asistió
                ELSE SET v_estado = 5;        -- Cancelada
            END CASE;
        END IF;
        
        -- Definir el RUT del médico
        IF i <= 20 THEN 
            SET v_rut_medico = '00000001-K'; 
        ELSE 
            SET v_rut_medico = CONCAT(LPAD(((i % 15) + 1), 8, '0'), '-K'); 
        END IF;

        -- Solución unificada: Buscar dinámicamente un centro donde ESTE médico sí atienda
        SELECT Codigo_Interno INTO v_codigo_centro 
        FROM Centro_Medico_Del_Medico 
        WHERE RUT_Medico = v_rut_medico 
        LIMIT 1;

        INSERT INTO Cita (Fecha_Hora, RUT_Paciente, RUT_Medico, Codigo_Interno, ID_Especialidad, ID_Estado) VALUES
            (DATE_ADD('2026-09-01 08:00:00', INTERVAL (i * 2) HOUR), CONCAT(LPAD(((i % 40) + 101), 8, '0'), '-9'), v_rut_medico, v_codigo_centro, v_id_med_general, v_estado);

        SET v_id_cita = LAST_INSERT_ID();

        IF v_estado = 3 AND v_id_cita > 0 THEN
            INSERT INTO Atencion (ID_Cita, Motivo, Observaciones, ID_Estado) VALUES (v_id_cita, 'Control médico', 'Estable', 3);
            SET v_id_atencion = LAST_INSERT_ID();
            
            INSERT INTO Atencion_Diagnostico (ID_Atencion, Codigo) VALUES (v_id_atencion, 'J00');
            INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento) VALUES (v_id_atencion, 'Paracetamol', '500mg', 5);
        END IF;
        
        SET i = i + 1;
    END WHILE;
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