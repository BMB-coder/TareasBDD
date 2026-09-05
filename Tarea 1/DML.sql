-- DML
BEGIN;

INSERT INTO Prevision (Tipo) VALUES 
	('Fonasa'), 
	('Isapre'), 
	('Particular');

INSERT INTO Especialidad (Nombre) VALUES 
	('Cardiología'), 
	('Pediatría'), 
	('Traumatología'), 
	('Medicina General'), 
	('Dermatología'), 
	('Neurología'), 
	('Psiquiatría'), 
	('Gastroenterología');

INSERT INTO Estado_Cita (ID_Estado, Estado ) VALUES 
	(1, 'Reservada'),
	(2, 'Confirmada'),
	(3, 'Atendida'),
	(4, 'No Asistió'), 
	(5, 'Cancelada');

INSERT INTO Diagnostico (Codigo, Descripcion) VALUES 
	('J00', 'Resfriado común (Rinofaringitis aguda)'),
	('I10', 'Hipertensión esencial (primaria)'),
	('E11', 'Diabetes mellitus tipo 2'),
	('M54', 'Dorsalgia (Dolor de espalda)'),
	('J03', 'Amigdalitis aguda'),
	('A09', 'Gastroenteritis de presunto origen infeccioso'),
	('Z00', 'Examen general sin queja o diagnóstico informado'),
    ('L30', 'Dermatitis no especificada');

INSERT INTO Centro_Medico (Nombre, Comuna, Region) VALUES 
	('SaludUSM Valparaíso', 'Valparaíso', 'VA'),
	('SaludUSM Viña del Mar', 'Viña del Mar', 'VA'),
	('SaludUSM San Joaquín', 'San Joaquín', 'RM'),
	('SaludUSM Vitacura', 'Vitacura', 'RM');

INSERT INTO Medico (RUT_Medico, Nombre, Email) VALUES 
	('12345678-9', 'Dr. Juan Pérez', 'juan.perez@saludusm.cl'),
	('98765432-1', 'Dra. María González', 'maria.gonzalez@saludusm.cl');

INSERT INTO Medico_Especialidad (RUT_Medico, ID_Especialidad, Numero_Especialidad) VALUES 
	(
		'12345678-9', 
		(SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Medicina General'),
		1
	),
	(
		'12345678-9', 
		(SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Pediatría'),
		2
	),
	(
		'98765432-1', 
		(SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Dermatología'),
		1
	);

INSERT INTO Centro_Medico_Del_Medico (Codigo_Interno, RUT_Medico) VALUES 
	(
		(SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM Valparaíso'),
		'12345678-9'
	),
	(
		(SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM Viña del Mar'),
		'12345678-9'
	),
	(
		(SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM San Joaquín'),
		'98765432-1'
	);

INSERT INTO Paciente (RUT_Paciente, Nombre, Fecha_Nacimiento, Sexo, ID_Prevision, Telefono, Comuna) VALUES 
	(
		'20123456-7', 
		'Carlos Silva', 
		'1995-04-12', 
		'M', 
		(SELECT ID_Prevision FROM Prevision WHERE Tipo = 'Fonasa'), 
		'56911223344', 
		'Valparaíso'
	),
	(
		'15678901-K', 
		'Ana Rojas', 
		'1982-11-05', 
		'F', 
		(SELECT ID_Prevision FROM Prevision WHERE Tipo = 'Isapre'),  
		'56955667788', 
		'San Miguel'
	);

INSERT INTO Cita (Fecha_Hora, RUT_Paciente, RUT_Medico, Codigo_Interno, ID_Especialidad, ID_Estado) VALUES 
	(
		'2026-08-15 10:00:00', 
		'20123456-7', 
		'12345678-9', 
		(SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM Valparaíso'), 
		(SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Medicina General'), 
		3
	),
	(
		'2026-08-16 11:30:00', 
		'15678901-K', 
		'98765432-1', 
		(SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM San Joaquín'), 
		(SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Dermatología'),
		4
	),
	(
		'2026-08-17 10:00:00',
	    '20123456-7',
	    '12345678-9',
	    (SELECT Codigo_Interno
	     FROM Centro_Medico
	     WHERE Nombre = 'SaludUSM Viña del Mar'),
	    (SELECT ID_Especialidad
	     FROM Especialidad
	     WHERE Nombre = 'Pediatría'),
	    3
	),
	(
		'2026-08-18 10:00:00',
	    '20123456-7',
	    '98765432-1',
	    (SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM San Joaquín'),
	    (SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Dermatología'),
	    3
	),
	(
		'2026-08-20 09:00:00',
	    '20123456-7',
	    '12345678-9',
	    (SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM Valparaíso'),
	    (SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Medicina General'),
    	1
	),
	(
		'2026-08-21 09:00:00',
	    '15678901-K',
	    '98765432-1',
	    (SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM San Joaquín'),
	    (SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Dermatología'),
	    2
	);

INSERT INTO Atencion (ID_Cita, Motivo, Observaciones) VALUES 
	(
		(SELECT ID_Cita FROM Cita WHERE RUT_Paciente = '20123456-7' AND Fecha_Hora = '2026-08-15 10:00:00'), 
		'Dolor de garganta y fiebre', 
		'Paciente presenta amígdalas inflamadas, se receta antibiótico'
	),
	(
		(SELECT ID_Cita FROM Cita WHERE RUT_Paciente = '20123456-7' AND Fecha_Hora = '2026-08-17 10:00:00'),
	    'Control pediátrico',
	    'Paciente estable'
	),
	(
		(SELECT ID_Cita FROM Cita WHERE RUT_Paciente = '20123456-7' AND Fecha_Hora = '2026-08-18 10:00:00'),
	    'Consulta dermatológica',
	    'Paciente presenta irritación cutánea'
	);

INSERT INTO Atencion_Diagnostico (ID_Atencion, Codigo) VALUES
	(
		(SELECT a.ID_Atencion FROM Atencion a JOIN Cita c ON c.ID_Cita = a.ID_Cita WHERE c.RUT_Paciente = '20123456-7' AND c.Fecha_Hora = '2026-08-15 10:00:00'), -- a = Atencion; c = Cita
		'J03'
	),
	(
		(SELECT a.ID_Atencion FROM Atencion a JOIN Cita c ON c.ID_Cita = a.ID_Cita WHERE c.RUT_Paciente = '20123456-7' AND c.Fecha_Hora = '2026-08-17 10:00:00'),
    	'Z00'
	),
	(
		(SELECT a.ID_Atencion FROM Atencion a JOIN Cita c ON c.ID_Cita = a.ID_Cita WHERE c.RUT_Paciente = '20123456-7' AND c.Fecha_Hora = '2026-08-18 10:00:00'),
    	'L30'
	);

INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento) VALUES 
	(
		(SELECT a.ID_Atencion FROM Atencion a JOIN Cita c ON c.ID_Cita = a.ID_Cita WHERE c.RUT_Paciente = '20123456-7' AND c.Fecha_Hora = '2026-08-15 10:00:00'), -- a = Atencion; c = Cita
		'Amoxicilina', 
		'500 mg cada 8 horas', 
		7
	),
	(	
		(SELECT a.ID_Atencion FROM Atencion a JOIN Cita c ON c.ID_Cita = a.ID_Cita WHERE c.RUT_Paciente = '20123456-7' AND c.Fecha_Hora = '2026-08-15 10:00:00'), -- a = Atencion; c = Cita 
		'Paracetamol', 
		'1g cada 12 horas en caso de fiebre', 
		3
	);

-- =================================================================
-- POBLAMIENTO MASIVO AUTOMATIZADO (PostgreSQL PL/pgSQL)
-- =================================================================

DO $$
DECLARE
    i INT;
    v_id_cita INT;
    v_id_atencion INT;
    v_estado INT;
	v_id_med_general INT;
	v_rut_medico VARCHAR(10);
	v_codigo_centro INT;
BEGIN

	SELECT ID_Especialidad INTO v_id_med_general FROM Especialidad WHERE Nombre = 'Medicina General'; -- v_id_med_general = ID_Especialidad

	-- 1. Insertar 15 Médicos y asociarlos a Centros y Especialidades
    FOR i IN 1..15 LOOP
        v_rut_medico := LPAD(i::text, 8, '0') || '-K';
		v_codigo_centro := 1 + (i % 4);

		INSERT INTO Medico (RUT_Medico, Nombre, Email) VALUES
			(
				v_rut_medico,
				'Dr. Automático ' || i,
				'doc' || i || '@saludusm.cl'
			);

		INSERT INTO Centro_Medico_Del_Medico (Codigo_Interno, RUT_Medico) VALUES
			(v_codigo_centro, v_rut_medico);

		INSERT INTO Medico_Especialidad (RUT_Medico, ID_Especialidad, Numero_Especialidad) VALUES
			(v_rut_medico, v_id_med_general, 1);
	END LOOP;

    -- 2. Insertar 40 Pacientes
    FOR i IN 1..40 LOOP
        INSERT INTO Paciente (RUT_Paciente, Nombre, Fecha_Nacimiento, Sexo, ID_Prevision, Comuna) VALUES
			(
				LPAD((i + 100)::text, 8, '0') || '-9',
				'Paciente Genérico ' || i,
				CASE (i % 5)
					WHEN 0 THEN '1960-05-14'::date
					WHEN 1 THEN '1970-05-14'::date
					WHEN 2 THEN '1980-05-14'::date
					WHEN 3 THEN '1990-05-14'::date
					ELSE '2000-05-14'::date
				END,
				'F',
				1 + (i % 3),
				'Valparaíso'
			);
    END LOOP;

    -- 3. Insertar 120 Citas
    FOR i IN 1..120 LOOP
	
		IF i <= 65 THEN
	   		v_estado := 3;
		ELSE
			CASE (i % 4)
				WHEN 0 THEN v_estado := 1;
				WHEN 1 THEN v_estado := 2;
				WHEN 2 THEN v_estado := 4;
				ELSE v_estado := 5;
			END CASE;
		END IF;

		IF i <= 20 THEN
			v_rut_medico := '00000001-K';
			v_codigo_centro := 2;
		ELSE
			v_rut_medico := LPAD(((i % 15) + 1)::text, 8, '0') || '-K'; -- Rotación entre médico automáticos
			v_codigo_centro := 1 + (((i % 15) + 1) % 4);
		END IF;
		
		INSERT INTO Cita (Fecha_Hora, RUT_Paciente, RUT_Medico, Codigo_Interno, ID_Especialidad, ID_Estado) VALUES
			(
				'2026-09-01 08:00:00'::timestamp + ((i * 2) || ' hours')::interval,
				LPAD(((i % 40) + 101)::text, 8 , '0') || '-9',
				v_rut_medico,
				v_codigo_centro,
				v_id_med_general,
				v_estado
			)
		
		RETURNING ID_Cita INTO v_id_cita; -- v_id_cita = ID_Cita

		IF v_estado = 3 THEN
			INSERT INTO Atencion (ID_Cita, Motivo, Observaciones) VALUES
				(
					v_id_cita,
					'Control médico general',
					'Paciente estable'
				)
			RETURNING ID_Atencion INTO v_id_atencion; -- v_id_atencion = ID_Atencion

			INSERT INTO Atencion_Diagnostico (ID_Atencion, Codigo) VALUES
				(v_id_atencion, 'J00');
			
			INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento) VALUES
				(v_id_atencion, 'Paracetamol', '500mg cada 8 hrs', 5);

			IF i <= 13 THEN
				INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento) VALUES
					(v_id_atencion, 'Ibuprofeno', '400mg cada 12hrs', 3);
			END IF;

			IF i = 14 THEN
				INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento) VALUES
					(v_id_atencion, 'Loratadina', '10mg cada 24hrs', 5);
			END IF;

			IF i = 15 THEN
				INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento) VALUES
					(v_id_atencion, 'Omeprazol', '20mg cada 24hrs', 7);
			END IF;
		END IF;
	END LOOP;
END $$;

COMMIT;