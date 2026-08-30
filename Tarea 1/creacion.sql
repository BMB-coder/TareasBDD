-- Script creación de Base de Datos
-- PostgreSQL

-- Crear la base de datos
CREATE DATABASE salud_usm;
\c salud_usm;


-- CREACIÓN DE CATÁLOGOS
CREATE TABLE Prevision (
    ID_Prevision SERIAL PRIMARY KEY,
    Nombre VARCHAR(500) UNIQUE NOT NULL
);

CREATE TABLE Especialidad (
    Tipo_Especialidad VARCHAR(100) PRIMARY KEY
);

CREATE TABLE Estado_Cita (
    ID_Estado SERIAL PRIMARY KEY,
    Nombre VARCHAR(20) UNIQUE NOT NULL
);

CREATE TABLE Diagnostico (
    Codigo VARCHAR(10) PRIMARY KEY,
    Descripcion TEXT NOT NULL
);

-- CREACIÓN DE ENTIDADES PRINCIPALES
CREATE TABLE Centro_Medico (
    Codigo_interno SERIAL PRIMARY KEY,
    Nombre VARCHAR(150) NOT NULL,
    Comuna VARCHAR(150) NOT NULL,
    Region INT NOT NULL
);

CREATE TABLE Paciente (
    RUT_Paciente VARCHAR(12) PRIMARY KEY,
    Nombre VARCHAR(150) NOT NULL,
    Fecha_Nacimiento DATE NOT NULL,
    Sexo VARCHAR(20) NOT NULL,
    ID_Prevision INT NOT NULL,
    Telefono VARCHAR(15) DEFAULT NULL,
    Comuna VARCHAR(100) NOT NULL,
    FOREIGN KEY (ID_Prevision) REFERENCES Prevision(ID_Prevision) ON UPDATE CASCADE
);

CREATE TABLE Medico (
    RUT_Medico VARCHAR(12) PRIMARY KEY,
    Nombre VARCHAR(150) NOT NULL,
    Email VARCHAR(150) UNIQUE NOT NULL
);

-- CREACIÓN DE TABLAS INTERMEDIAS (Asociaciones M:N)
CREATE TABLE Centro_Medico_Medico (
    Codigo_Interno INT NOT NULL,
    RUT_Medico VARCHAR(12) NOT NULL,
    PRIMARY KEY (Codigo_Interno, RUT_Medico),
    FOREIGN KEY (Codigo_Interno) REFERENCES Centro_Medico(Codigo_Interno) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (RUT_Medico) REFERENCES Medico(RUT_Medico) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Medico_Especialidad (
    RUT_Medico VARCHAR(12) NOT NULL,
    Tipo_Especialidad VARCHAR(100) NOT NULL,
    PRIMARY KEY (RUT_Medico, Tipo_Especialidad),
    FOREIGN KEY (RUT_Medico) REFERENCES Medico(RUT_Medico) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Tipo_Especialidad) REFERENCES Especialidad(Tipo_Especialidad) ON DELETE CASCADE ON UPDATE CASCADE
);

-- CREACIÓN DE CITAS, ATENCIONES Y RECETAS
CREATE TABLE Cita (
    ID_Cita SERIAL PRIMARY KEY,
    Fecha_Hora TIMESTAMP NOT NULL,
    RUT_Paciente VARCHAR(12) NOT NULL,
    RUT_Medico VARCHAR(12) NOT NULL,
    Codigo_Interno INT NOT NULL,
    Tipo_Especialidad VARCHAR(100) NOT NULL,
    ID_Estado INT NOT NULL,
    FOREIGN KEY (RUT_Paciente) REFERENCES Paciente(RUT_Paciente) ON UPDATE CASCADE,
    FOREIGN KEY (RUT_Medico) REFERENCES Medico(RUT_Medico) ON UPDATE CASCADE,
    FOREIGN KEY (Codigo_Interno) REFERENCES Centro_Medico(Codigo_Interno) ON UPDATE CASCADE,
    FOREIGN KEY (Tipo_Especialidad) REFERENCES Especialidad(Tipo_Especialidad) ON UPDATE CASCADE,
    FOREIGN KEY (ID_Estado) REFERENCES Estado_Cita(ID_Estado) ON UPDATE CASCADE,
    UNIQUE (RUT_Paciente, Fecha_Hora),
    UNIQUE (RUT_Medico, Codigo_Interno, Fecha_Hora)
);

CREATE TABLE Atencion (
    ID_Atencion SERIAL PRIMARY KEY,
    ID_Cita INT UNIQUE NOT NULL,
    Motivo TEXT NOT NULL,
    Observaciones TEXT,
    FOREIGN KEY (ID_Cita) REFERENCES Cita(ID_Cita) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Atencion_Diagnostico (
    ID_Atencion INT NOT NULL,
    Codigo VARCHAR(10) NOT NULL,
    PRIMARY KEY (ID_Atencion, Codigo),
    FOREIGN KEY (ID_Atencion) REFERENCES Atencion(ID_Atencion) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Codigo) REFERENCES Diagnostico(Codigo) ON UPDATE CASCADE
);

CREATE TABLE Receta (
    ID_Receta SERIAL PRIMARY KEY,
    ID_Atencion INT NOT NULL,
    Medicamento VARCHAR(150) NOT NULL,
    Dosis VARCHAR(255) NOT NULL,
    Dias_Tratamiento INT NOT NULL,
    FOREIGN KEY (ID_Atencion) REFERENCES Atencion(ID_Atencion) ON DELETE CASCADE ON UPDATE CASCADE
);