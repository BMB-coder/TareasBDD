CREATE TABLE Centro_Medico (
	Codigo_interno SERIAL primary key NOT NULL UNIQUE,
	Nombre VARCHAR(255) NOT NULL,
	Comuna VARCHAR(255) NOT NULL,
	Region INT NOT NULL
);

CREATE TABLE Paciente (
	RUT_paciente VARCHAR(10) primary key NOT NULL UNIQUE,
	Nombre VARCHAR(10) NOT NULL,
	Fecha_de_nacimiento DATE NOT NULL,
	Sexo VARCHAR(4) NOT NULL,
	Tipo_de_prevision VARCHAR(10) references Prevision_de_Salud(Tipo_de_prevision) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL CHECK(Tipo_de_prevision = "Fonasa" OR Tipo_de_prevision = "Isapre" OR Tipo_de_prevision = "Particular"),
	Telefono VARCHAR(11),
	Comuna VARCHAR(255) NOT NULL
);

CREATE TABLE Medico (
	RUT_medico VARCHAR(10) primary key NOT NULL UNIQUE,
	Nombre VARCHAR(255) NOT NULL,
	Email VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE Especialidad (
	Tipo_de_especialidad VARCHAR(255) primary key NOT NULL UNIQUE
);

CREATE TABLE Centro_y_Medico (
	Codigo_interno SERIAL primary key NOT NULL,
	RUT_medico VARCHAR(10) primary key NOT NULL
);

CREATE TABLE Especialidad_de_Medico (
	RUT_medico VARCHAR(10) primary key NOT NULL,
	Tipo_de_especialidad VARCHAR(255) primary key NOT NULL
);

CREATE TABLE Cita (
	ID_cita SERIAL primary key NOT NULL UNIQUE,
	Fecha_y_hora TIMESTAMP NOT NULL,
	Paciente VARCHAR(255) NOT NULL,
	Medico VARCHAR(255) NOT NULL,
	Centro VARCHAR(255) NOT NULL,
	Especialidad VARCHAR(255),
	Estado_de_cita VARCHAR(10) references Estado(Estado_de_cita) ON UPDATE CASCADE NOT NULL CHECK(Estado_de_cita = "Reservada" OR Estado_de_cita = "Confirmada" OR Estado_de_cita = "Atendida" OR Estado_de_cita = "No Asistió" OR Estado_de_cita = "Cancelada"),
	Codigo_interno INT references Centro_Medico(Codigo_interno) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL,
	RUT_paciente VARCHAR(10) references Paciente(RUT_paciente) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL,
	RUT_medico VARCHAR(10) references Medico(RUT_medico) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL,
	ID_atencion INT references Atencion(ID_atencion) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL UNIQUE
);

CREATE TABLE Atencion (
	ID_atencion SERIAL primary key NOT NULL UNIQUE,
	Motivo VARCHAR(255) NOT NULL,
	Observaciones VARCHAR(255) NOT NULL,
	ID_cita INT references Cita(ID_cita) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL UNIQUE
);

CREATE TABLE Diagnostico (
	Codigo VARCHAR(3) primary key NOT NULL UNIQUE,
	Descripcion VARCHAR(255) NOT NULL,
	ID_atencion INT references Atencion(ID_atencion) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL
);

CREATE TABLE Receta (
	ID_receta SERIAL primary key NOT NULL UNIQUE,
	Medicamento VARCHAR(255) NOT NULL,
	Dosis VARCHAR(255) NOT NULL,
	Dias_de_tratamiento INT NOT NULL,
	ID_atencion INT references Atencion(ID_atencion) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL
);

CREATE TABLE Prevision_de_Salud (
	Tipo_de_prevision VARCHAR(10) primary key NOT NULL UNIQUE CHECK(Tipo_de_prevision = "Fonasa" OR Tipo_de_prevision = "Isapre" OR Tipo_de_prevision = "Particular")
);

CREATE TABLE Estado (
	Estado_de_cita VARCHAR(10) primary key NOT NULL UNIQUE CHECK(Estado_de_cita = "Reservada" OR Estado_de_cita = "Confirmada" OR Estado_de_cita ="Atendida" OR Estado_de_cita = "No Asistió" OR Estado_de_cita = "Cancelada")
);