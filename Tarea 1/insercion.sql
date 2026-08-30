-- POBLAMIENTO DE DATOS

INSERT INTO Prevision (Nombre) VALUES ('Fonasa'), ('Isapre'), ('Particular');

INSERT INTO Centro_Medico (Nombre, Comuna, Region) VALUES 
('SaludUSM Valparaíso', 'Valparaíso', 5),
('SaludUSM Viña del Mar', 'Viña del Mar', 5),
('SaludUSM San Joaquín', 'San Joaquín', 13),
('SaludUSM Vitacura', 'Vitacura', 13);

INSERT INTO Especialidad (Tipo_Especialidad) VALUES 
('Cardiología'), ('Pediatría'), ('Traumatología'), ('Medicina General'), 
('Dermatología'), ('Neurología'), ('Psiquiatría'), ('Gastroenterología');

INSERT INTO Estado_Cita (Nombre) VALUES 
('Reservada'), ('Confirmada'), ('Atendida'), ('No Asistió'), ('Cancelada');

INSERT INTO Diagnostico (Codigo, Descripcion) VALUES 
('J00', 'Resfriado común (Rinofaringitis aguda)'),
('I10', 'Hipertensión esencial (primaria)'),
('E11', 'Diabetes mellitus tipo 2'),
('M54', 'Dorsalgia (Dolor de espalda)'),
('J03', 'Amigdalitis aguda'),
('A09', 'Gastroenteritis de presunto origen infeccioso');

INSERT INTO Medico (RUT_Medico, Nombre, Email) VALUES 
('12345678-9', 'Dr. Juan Pérez', 'juan.perez@saludusm.cl'),
('19876543-2', 'Dra. María González', 'maria.gonzalez@saludusm.cl');

INSERT INTO Medico_Especialidad (RUT_Medico, Tipo_Especialidad) VALUES 
('12345678-9', 'Medicina General'),
('12345678-9', 'Pediatría');

INSERT INTO Centro_Medico_Medico (Codigo_Interno, RUT_Medico) VALUES 
(1, '12345678-9'), -- Atiende en Valparaíso
(2, '12345678-9'); -- Tambien atiende en Viña del Mar

INSERT INTO Paciente (RUT_Paciente, Nombre, Fecha_Nacimiento, Sexo, ID_Prevision, Telefono, Comuna) VALUES 
('20123456-7', 'Carlos Silva', '1995-04-12', 'M', 1, '+56911223344', 'Valparaíso'),
('15678901-K', 'Ana Rojas', '1982-11-05', 'F', 2, '+56955667788', 'San Miguel');

INSERT INTO Cita (Fecha_Hora, RUT_Paciente, RUT_Medico, Codigo_Interno, Tipo_Especialidad, ID_Estado) VALUES 
('2026-08-15 10:00:00', '20123456-7', '12345678-9', 1, 'Medicina General', 3), -- Atendida
('2026-08-16 11:30:00', '15678901-K', '19876543-2', 3, 'Dermatología', 4); -- No Asistio

INSERT INTO Atencion (ID_Cita, Motivo, Observaciones) VALUES 
(1, 'Dolor de garganta y fiebre', 'Paciente presenta amígdalas inflamadas, se receta antibiótico');

INSERT INTO Atencion_Diagnostico (ID_Atencion, Codigo) VALUES 
(1, 'J03'); -- Amigdalitis

INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento) VALUES 
(1, 'Amoxicilina', '500 mg cada 8 horas', 7),
(1, 'Paracetamol', '1g cada 12 horas en caso de fiebre', 3);

-- =================================================================
-- POBLAMIENTO MASIVO AUTOMATIZADO (PostgreSQL PL/pgSQL)
-- =================================================================

DO $$
DECLARE
    i INT;
    v_id_cita INT;
    v_id_atencion INT;
    v_estado INT;
BEGIN
    -- 1. Insertar 15 Médicos y asociarlos a Centros y Especialidades
    FOR i IN 1..15 LOOP
        INSERT INTO Medico (RUT_Medico, Nombre, Email)
        VALUES (LPAD(i::text, 8, '0') || '-K', 'Dr. Automático ' || i, 'doc' || i || '@saludusm.cl')
        ON CONFLICT DO NOTHING;

        INSERT INTO Centro_Medico_Medico (Codigo_Interno, RUT_Medico)
        VALUES (1 + (i % 4), LPAD(i::text, 8, '0') || '-K') ON CONFLICT DO NOTHING;

        INSERT INTO Medico_Especialidad (RUT_Medico, Tipo_Especialidad)
        VALUES (LPAD(i::text, 8, '0') || '-K', 'Medicina General') ON CONFLICT DO NOTHING;
    END LOOP;

    -- 2. Insertar 40 Pacientes
    FOR i IN 1..40 LOOP
        INSERT INTO Paciente (RUT_Paciente, Nombre, Fecha_Nacimiento, Sexo, ID_Prevision, Comuna)
        VALUES (LPAD((i+100)::text, 8, '0') || '-9', 'Paciente Genérico ' || i, '1990-05-14', 'F', 1 + (i % 3), 'Valparaíso')
        ON CONFLICT DO NOTHING;
    END LOOP;

    -- 3. Insertar 120 Citas
    FOR i IN 1..120 LOOP
        -- Lógica para cumplir la rúbrica: 65 citas Atendidas (Estado 3), el resto variables (1,2,4,5) para asegurar inasistencias
        IF i <= 65 THEN 
            v_estado := 3; 
        ELSE 
            v_estado := 1 + (i % 5); 
        END IF;

        INSERT INTO Cita (Fecha_Hora, RUT_Paciente, RUT_Medico, Codigo_Interno, Tipo_Especialidad, ID_Estado)
        VALUES (
            '2026-09-01 08:00:00'::timestamp + ((i * 2) || ' hours')::interval, -- Suma horas para no repetir fecha/hora
            LPAD(((i % 40)+101)::text, 8, '0') || '-9', -- Rota entre los 40 pacientes
            LPAD(((i % 15)+1)::text, 8, '0') || '-K',   -- Rota entre los 15 médicos
            1 + (((i % 15)+1) % 4),                     -- Asigna el centro correspondiente
            'Medicina General',
            v_estado
        ) RETURNING ID_Cita INTO v_id_cita;

        -- 4. Generar Atenciones, Diagnósticos y Recetas SOLO si el estado es "Atendida"
        IF v_estado = 3 THEN
            INSERT INTO Atencion (ID_Cita, Motivo, Observaciones)
            VALUES (v_id_cita, 'Control médico general', 'Paciente estable')
            RETURNING ID_Atencion INTO v_id_atencion;

            -- Reutiliza el catálogo CIE-10 (J00)
            INSERT INTO Atencion_Diagnostico (ID_Atencion, Codigo)
            VALUES (v_id_atencion, 'J00');

            -- Inserta 1 receta base. Como pide 80 recetas en total, a los primeros 25 les damos una segunda receta.
            INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento)
            VALUES (v_id_atencion, 'Paracetamol', '500mg cada 8 hrs', 5);

            IF i <= 25 THEN
                INSERT INTO Receta (ID_Atencion, Medicamento, Dosis, Dias_Tratamiento)
                VALUES (v_id_atencion, 'Ibuprofeno', '400mg cada 12 hrs', 3);
            END IF;
        END IF;
    END LOOP;
END $$;