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

INSERT IGNORE INTO Medico (RUT_Medico, Nombre, Email, Contrasena) VALUES 
	('12345678-9', 'Dr. Juan Pérez', 'juan.perez@saludusm.cl', 'Medico123*'),
	('98765432-1', 'Dra. María González', 'maria.gonzalez@saludusm.cl', 'Medico123*');

INSERT IGNORE INTO Medico_Especialidad (RUT_Medico, ID_Especialidad, Numero_Especialidad) VALUES 
	('12345678-9', (SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Medicina General'), 1),
	('12345678-9', (SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Pediatría'), 2),
	('98765432-1', (SELECT ID_Especialidad FROM Especialidad WHERE Nombre = 'Dermatología'), 1);

INSERT IGNORE INTO Centro_Medico_Del_Medico (Codigo_Interno, RUT_Medico) VALUES 
	((SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM Valparaíso'), '12345678-9'),
	((SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM Viña del Mar'), '12345678-9'),
	((SELECT Codigo_Interno FROM Centro_Medico WHERE Nombre = 'SaludUSM San Joaquín'), '98765432-1');

INSERT IGNORE INTO Paciente (RUT_Paciente, Nombre, Fecha_Nacimiento, Sexo, ID_Prevision, Telefono, Comuna, Contrasena) VALUES 
	('20123456-7', 'Carlos Silva', '1995-04-12', 'M', (SELECT ID_Prevision FROM Prevision WHERE Tipo = 'Fonasa'), '56911223344', 'Valparaíso', 'Paciente123*'),
	('15678901-K', 'Ana Rojas', '1982-11-05', 'F', (SELECT ID_Prevision FROM Prevision WHERE Tipo = 'Isapre'), '56955667788', 'San Miguel', 'Paciente123*');