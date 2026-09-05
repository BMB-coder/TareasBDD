-- DDL

-- CREACIÓN DE CATÁLOGOS (DOMINIOS)
CREATE TABLE Prevision (
    ID_Prevision SERIAL PRIMARY KEY,
    Tipo VARCHAR(10) NOT NULL UNIQUE,
	CHECK(Tipo IN ('Fonasa', 'Isapre', 'Particular'))
);

CREATE TABLE Especialidad (
	ID_Especialidad SERIAL PRIMARY KEY,
    Nombre VARCHAR(255) NOT NULL UNIQUE,
	CHECK(Nombre IN ('Cardiología', 'Pediatría', 'Traumatología', 'Medicina General', 'Dermatología', 'Neurología', 'Psiquiatría', 'Gastroenterología'))
);

CREATE TABLE Estado_Cita (
    ID_Estado INT PRIMARY KEY,
    Estado VARCHAR(10) UNIQUE NOT NULL,
	CHECK(Estado IN ('Reservada', 'Confirmada', 'Atendida', 'No Asistió', 'Cancelada'))
);

-- CREACIÓN DE ENTIDADES PRINCIPALES
CREATE TABLE Centro_Medico (
    Codigo_Interno SERIAL PRIMARY KEY,
    Nombre VARCHAR(255) NOT NULL,
    Comuna VARCHAR(255) NOT NULL,
    Region VARCHAR(2) NOT NULL
);

CREATE TABLE Paciente (
    RUT_Paciente VARCHAR(10) PRIMARY KEY,
    Nombre VARCHAR(255) NOT NULL,
    Fecha_Nacimiento DATE NOT NULL,
    Sexo VARCHAR(4) NOT NULL,
	Telefono VARCHAR(11) DEFAULT NULL,
	Comuna VARCHAR(255) NOT NULL,
    ID_Prevision INT NOT NULL,
    FOREIGN KEY (ID_Prevision) REFERENCES Prevision(ID_Prevision) ON UPDATE CASCADE,
	CHECK (Sexo IN ('M', 'F', 'Otro'))
);

CREATE TABLE Medico (
    RUT_Medico VARCHAR(10) PRIMARY KEY,
    Nombre VARCHAR(255) NOT NULL,
    Email VARCHAR(255) UNIQUE NOT NULL
);

-- CREACIÓN DE TABLAS INTERMEDIAS (Centro_Medico_Del_Medico y Medico_Especialidad)
CREATE TABLE Centro_Medico_Del_Medico (
    Codigo_Interno INT NOT NULL,
    RUT_Medico VARCHAR(10) NOT NULL,
    PRIMARY KEY (Codigo_Interno, RUT_Medico),
    FOREIGN KEY (Codigo_Interno) REFERENCES Centro_Medico(Codigo_Interno) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (RUT_Medico) REFERENCES Medico(RUT_Medico) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Medico_Especialidad (
    RUT_Medico VARCHAR(10) NOT NULL,
    ID_Especialidad INT NOT NULL,
	Numero_Especialidad INT NOT NULL,
    PRIMARY KEY (RUT_Medico, ID_Especialidad),
    FOREIGN KEY (RUT_Medico) REFERENCES Medico(RUT_Medico) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ID_Especialidad) REFERENCES Especialidad(ID_Especialidad) ON DELETE CASCADE ON UPDATE CASCADE,
	UNIQUE(RUT_Medico, Numero_Especialidad),
	CHECK (Numero_Especialidad BETWEEN 1 AND 3)
);

-- CREACIÓN DE CITAS, ATENCIONES Y RECETAS
CREATE TABLE Cita (
    ID_Cita SERIAL PRIMARY KEY,
    Fecha_Hora TIMESTAMP NOT NULL,
    RUT_Paciente VARCHAR(10) NOT NULL,
    RUT_Medico VARCHAR(10) NOT NULL,
    Codigo_Interno INT NOT NULL,
    ID_Especialidad INT NOT NULL,
    ID_Estado INT NOT NULL,
    FOREIGN KEY (RUT_Paciente) REFERENCES Paciente(RUT_Paciente) ON UPDATE CASCADE,
	FOREIGN KEY (ID_Estado) REFERENCES Estado_Cita(ID_Estado) ON UPDATE CASCADE,
    FOREIGN KEY (RUT_Medico, ID_Especialidad) REFERENCES Medico_Especialidad(RUT_Medico, ID_Especialidad) ON UPDATE CASCADE,
    FOREIGN KEY (Codigo_Interno, RUT_Medico) REFERENCES Centro_Medico_Del_Medico(Codigo_Interno, RUT_Medico) ON UPDATE CASCADE,
    UNIQUE (RUT_Paciente, Fecha_Hora),
    UNIQUE (RUT_Medico, Fecha_Hora),
	UNIQUE (ID_Cita, ID_Estado)
);

CREATE TABLE Atencion (
    ID_Atencion SERIAL PRIMARY KEY,
    Motivo TEXT NOT NULL,
    Observaciones TEXT NOT NULL,
	ID_Cita INT NOT NULL UNIQUE,
	ID_Estado INT NOT NULL DEFAULT 3,
	CHECK(ID_Estado = 3),
    FOREIGN KEY (ID_Cita, ID_Estado) REFERENCES Cita(ID_Cita, ID_Estado) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Diagnostico (
    Codigo VARCHAR(3) PRIMARY KEY,
    Descripcion TEXT NOT NULL
);

CREATE TABLE Receta (
    ID_Receta SERIAL PRIMARY KEY,
    Medicamento VARCHAR(255) NOT NULL,
    Dosis TEXT NOT NULL,
    Dias_Tratamiento INT NOT NULL,
	ID_Atencion INT NOT NULL,
    FOREIGN KEY (ID_Atencion) REFERENCES Atencion(ID_Atencion) ON DELETE CASCADE ON UPDATE CASCADE
);

-- CREACIÓN DE TABLA INTERMEDIA (Atencion_Diagnostico)
CREATE TABLE Atencion_Diagnostico (
	ID_Atencion INT NOT NULL,
	Codigo VARCHAR(3) NOT NULL,
	PRIMARY KEY (ID_Atencion, Codigo),
	FOREIGN KEY (ID_Atencion) REFERENCES Atencion(ID_Atencion) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Codigo) REFERENCES Diagnostico(Codigo) ON DELETE CASCADE ON UPDATE CASCADE
);