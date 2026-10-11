-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-10-2026 a las 21:56:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `saludusm`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `NULLCita` (IN `p_Nro_Cita` INT)  MODIFIES SQL DATA BEGIN
    DECLARE v_EstadoCancelada INT;

    SET v_EstadoCancelada = (
        SELECT ID_Estado
        FROM estado
        WHERE Estado_Cita = 'Cancelada'
    );

    IF v_EstadoCancelada IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Falta el estado Cancelada en la tabla estado';
    END IF;

    UPDATE cita AS c
    INNER JOIN estado AS e ON e.ID_Estado = c.ID_Estado
    SET c.ID_Estado = v_EstadoCancelada
    WHERE c.Nro_Cita = p_Nro_Cita
      AND e.Estado_Cita IN ('Reservada', 'Confirmada');

    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La cita no existe o su estado no permite cancelarla';
    END IF;
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `EdadPaciente` (`p_FechaNacimiento` DATE) RETURNS INT(11) NO SQL BEGIN
    RETURN TIMESTAMPDIFF(YEAR, p_FechaNacimiento, CURDATE());
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atencion`
--

CREATE TABLE `atencion` (
  `Nro_Atencion` int(11) NOT NULL,
  `Motivo_Atencion` text NOT NULL,
  `Observaciones_Atencion` text NOT NULL,
  `Nro_Cita` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atencion_diagnostico`
--

CREATE TABLE `atencion_diagnostico` (
  `Nro_Atencion` int(11) NOT NULL,
  `Codigo_Diagnostico` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_medico`
--

CREATE TABLE `centro_medico` (
  `Codigo_Centro` int(11) NOT NULL,
  `Nombre_Centro` varchar(100) NOT NULL,
  `Comuna_Centro` varchar(100) NOT NULL,
  `Region_Centro` varchar(2) NOT NULL COMMENT 'RM, VA, CO, AT...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `centro_medico`
--

INSERT INTO `centro_medico` (`Codigo_Centro`, `Nombre_Centro`, `Comuna_Centro`, `Region_Centro`) VALUES
(1, 'CentroGenerico0', 'San Joaquín', 'RM'),
(2, 'CentroPrueba1', 'San Joaquín', 'RM');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_medico_medico`
--

CREATE TABLE `centro_medico_medico` (
  `Codigo_Centro` int(11) NOT NULL,
  `RUT_Medico` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `centro_medico_medico`
--

INSERT INTO `centro_medico_medico` (`Codigo_Centro`, `RUT_Medico`) VALUES
(1, '00000000-1'),
(2, '00000000-1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita`
--

CREATE TABLE `cita` (
  `Nro_Cita` int(11) NOT NULL,
  `Fecha_Cita` date NOT NULL,
  `Hora_Cita` time NOT NULL,
  `Codigo_Centro` int(11) NOT NULL,
  `RUT_Paciente` varchar(10) DEFAULT NULL COMMENT 'XXXXXXXX-X',
  `RUT_Medico` varchar(10) NOT NULL COMMENT 'XXXXXXXX-X',
  `ID_Especialidad` int(11) NOT NULL,
  `ID_Estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cita`
--

INSERT INTO `cita` (`Nro_Cita`, `Fecha_Cita`, `Hora_Cita`, `Codigo_Centro`, `RUT_Paciente`, `RUT_Medico`, `ID_Especialidad`, `ID_Estado`) VALUES
(1, '2026-01-06', '10:40:00', 1, '00000000-0', '00000000-1', 1, 4),
(5, '2026-10-11', '17:11:00', 1, '00000000-0', '00000000-1', 1, 0);

--
-- Disparadores `cita`
--
DELIMITER $$
CREATE TRIGGER `FechaCita` BEFORE INSERT ON `cita` FOR EACH ROW BEGIN
    IF TIMESTAMP(NEW.Fecha_Cita, NEW.Hora_Cita) < NOW() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'No se puede registrar una cita en una fecha y hora pasada';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `comprobante_reserva_hora`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `comprobante_reserva_hora` (
`Nro_Cita` int(11)
,`Estado_Cita` varchar(10)
,`RUT_Paciente` varchar(10)
,`Nombre_Paciente` varchar(100)
,`Prevision_Paciente` varchar(10)
,`Telefono_Paciente` varchar(11)
,`Fecha_Cita` varchar(10)
,`Hora_Cita` time
,`Nombre_Especialidad` varchar(255)
,`Nombre_Medico` varchar(100)
,`Codigo_Centro` int(11)
,`Nombre_Centro` varchar(100)
,`Comuna_Centro` varchar(100)
,`Region_Centro` varchar(2)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diagnostico`
--

CREATE TABLE `diagnostico` (
  `Codigo_Diagnostico` varchar(3) NOT NULL COMMENT 'Codigo CIE-10',
  `Descripcion_Diagnostico` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidad`
--

CREATE TABLE `especialidad` (
  `ID_Especialidad` int(11) NOT NULL,
  `Nombre_Especialidad` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `especialidad`
--

INSERT INTO `especialidad` (`ID_Especialidad`, `Nombre_Especialidad`) VALUES
(5, ''),
(1, 'Cardiología'),
(8, 'Gastroenterología'),
(4, 'Medicina General'),
(6, 'Neurología'),
(2, 'Pediatría'),
(7, 'Psiquiatría'),
(3, 'Traumatología');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `ID_Estado` int(11) NOT NULL,
  `Estado_Cita` varchar(10) NOT NULL COMMENT 'Reservada | Confirmada | Atendida | No Asistió | Cancelada'
) ;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`ID_Estado`, `Estado_Cita`) VALUES
(2, 'Atendida'),
(4, 'Cancelada'),
(1, 'Confirmada'),
(3, 'No Asistió'),
(0, 'Reservada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medico`
--

CREATE TABLE `medico` (
  `RUT_Medico` varchar(10) NOT NULL COMMENT 'XXXXXXXX-X',
  `Nombre_Medico` varchar(100) NOT NULL,
  `E-mail_Medico` varchar(100) NOT NULL COMMENT '...@...',
  `Contrasena_Hash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Realizada con modelo 3FN';

--
-- Volcado de datos para la tabla `medico`
--

INSERT INTO `medico` (`RUT_Medico`, `Nombre_Medico`, `E-mail_Medico`, `Contrasena_Hash`) VALUES
('00000000-1', 'MedicoGenerico0', 'medicogenerico0@gmail.com', '$2y$10$r6gF0V9xdE3YlnH7J1NIreva5mOZP.DDW0sfPXcNs.Rje9tZeOERu');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medico_especialidad`
--

CREATE TABLE `medico_especialidad` (
  `RUT_Medico` varchar(10) NOT NULL COMMENT 'XXXXXXXX-X',
  `ID_Especialidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `medico_especialidad`
--

INSERT INTO `medico_especialidad` (`RUT_Medico`, `ID_Especialidad`) VALUES
('00000000-1', 1),
('00000000-1', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente`
--

CREATE TABLE `paciente` (
  `RUT_Paciente` varchar(10) NOT NULL COMMENT 'XXXXXXXX-X',
  `Nombre_Paciente` varchar(100) NOT NULL,
  `Telefono_Paciente` varchar(11) DEFAULT NULL COMMENT '+56 ...',
  `Fecha-Nacimiento_Paciente` date NOT NULL COMMENT 'DD-MM-YYYY',
  `Sexo_Paciente` varchar(4) NOT NULL COMMENT 'M | F | Otro',
  `ID_Prevision` int(11) NOT NULL,
  `Email_Paciente` varchar(255) DEFAULT NULL,
  `Contrasena_Hash` varchar(255) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `paciente`
--

INSERT INTO `paciente` (`RUT_Paciente`, `Nombre_Paciente`, `Telefono_Paciente`, `Fecha-Nacimiento_Paciente`, `Sexo_Paciente`, `ID_Prevision`, `Email_Paciente`, `Contrasena_Hash`) VALUES
('00000000-0', 'PacienteGenerico0', '90000000001', '1995-01-01', 'M', 2, 'paciente.prueba@example.test', '$2y$10$kLjbFHC7SZ7JLBksG67x/..wwk0TVER5LhJxycjRbm1Ex/niirt8a'),
('00000001-0', 'Paciente2', '900000002', '2000-01-02', 'M', 1, 'paciente2@gmail.com', '$2y$10$xfb1lfdIPImxhViFV0I1EOP7icBe4dDc7Wsfq0wudo6Qs0LtnJxYu');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prevision`
--

CREATE TABLE `prevision` (
  `ID_Prevision` int(11) NOT NULL,
  `Prevision_Paciente` varchar(10) NOT NULL COMMENT 'Fonasa | Isapre | Particular'
) ;

--
-- Volcado de datos para la tabla `prevision`
--

INSERT INTO `prevision` (`ID_Prevision`, `Prevision_Paciente`) VALUES
(1, 'Fonasa'),
(2, 'Isapre'),
(3, 'Particular');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `receta`
--

CREATE TABLE `receta` (
  `ID_Receta` int(11) NOT NULL,
  `Medicamento_Receta` text NOT NULL,
  `Dosis_Receta` text NOT NULL,
  `Dias-Tratamiento_Receta` int(11) NOT NULL,
  `Nro_Atencion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura para la vista `comprobante_reserva_hora`
--
DROP TABLE IF EXISTS `comprobante_reserva_hora`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `comprobante_reserva_hora`  AS SELECT `c`.`Nro_Cita` AS `Nro_Cita`, `e`.`Estado_Cita` AS `Estado_Cita`, `p`.`RUT_Paciente` AS `RUT_Paciente`, `p`.`Nombre_Paciente` AS `Nombre_Paciente`, `pr`.`Prevision_Paciente` AS `Prevision_Paciente`, `p`.`Telefono_Paciente` AS `Telefono_Paciente`, date_format(`c`.`Fecha_Cita`,'%d-%m-%Y') AS `Fecha_Cita`, `c`.`Hora_Cita` AS `Hora_Cita`, `esp`.`Nombre_Especialidad` AS `Nombre_Especialidad`, `m`.`Nombre_Medico` AS `Nombre_Medico`, `cm`.`Codigo_Centro` AS `Codigo_Centro`, `cm`.`Nombre_Centro` AS `Nombre_Centro`, `cm`.`Comuna_Centro` AS `Comuna_Centro`, `cm`.`Region_Centro` AS `Region_Centro` FROM ((((((`cita` `c` join `estado` `e` on(`c`.`ID_Estado` = `e`.`ID_Estado`)) join `paciente` `p` on(`c`.`RUT_Paciente` = `p`.`RUT_Paciente`)) join `prevision` `pr` on(`p`.`ID_Prevision` = `pr`.`ID_Prevision`)) join `especialidad` `esp` on(`c`.`ID_Especialidad` = `esp`.`ID_Especialidad`)) join `medico` `m` on(`c`.`RUT_Medico` = `m`.`RUT_Medico`)) join `centro_medico` `cm` on(`c`.`Codigo_Centro` = `cm`.`Codigo_Centro`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `atencion`
--
ALTER TABLE `atencion`
  ADD PRIMARY KEY (`Nro_Atencion`),
  ADD UNIQUE KEY `uq_Cita` (`Nro_Cita`) USING BTREE;

--
-- Indices de la tabla `atencion_diagnostico`
--
ALTER TABLE `atencion_diagnostico`
  ADD PRIMARY KEY (`Nro_Atencion`,`Codigo_Diagnostico`),
  ADD KEY `atencion_dianostico_diagnostico` (`Codigo_Diagnostico`);

--
-- Indices de la tabla `centro_medico`
--
ALTER TABLE `centro_medico`
  ADD PRIMARY KEY (`Codigo_Centro`);

--
-- Indices de la tabla `centro_medico_medico`
--
ALTER TABLE `centro_medico_medico`
  ADD PRIMARY KEY (`Codigo_Centro`,`RUT_Medico`),
  ADD KEY `fk_Medico` (`RUT_Medico`);

--
-- Indices de la tabla `cita`
--
ALTER TABLE `cita`
  ADD PRIMARY KEY (`Nro_Cita`),
  ADD UNIQUE KEY `uq_Medico_Fecha_Cita` (`RUT_Medico`,`Fecha_Cita`,`Hora_Cita`) USING BTREE,
  ADD UNIQUE KEY `uq_Cita_Estado_Cita` (`Nro_Cita`,`ID_Estado`),
  ADD UNIQUE KEY `uq_Paciente_Fecha_Cita` (`RUT_Paciente`,`Fecha_Cita`,`Hora_Cita`) USING BTREE,
  ADD KEY `fkEstado_Cita` (`ID_Estado`),
  ADD KEY `fkCentroMedico_Cita` (`Codigo_Centro`,`RUT_Medico`),
  ADD KEY `fkMedicoEspecialidad_Cita` (`RUT_Medico`,`ID_Especialidad`),
  ADD KEY `fkPaciente_Cita` (`RUT_Paciente`) USING BTREE;

--
-- Indices de la tabla `diagnostico`
--
ALTER TABLE `diagnostico`
  ADD PRIMARY KEY (`Codigo_Diagnostico`);

--
-- Indices de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  ADD PRIMARY KEY (`ID_Especialidad`),
  ADD UNIQUE KEY `uq_Especialidad` (`Nombre_Especialidad`) USING BTREE;

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`ID_Estado`),
  ADD UNIQUE KEY `uq_Estado` (`Estado_Cita`) USING BTREE;

--
-- Indices de la tabla `medico`
--
ALTER TABLE `medico`
  ADD PRIMARY KEY (`RUT_Medico`),
  ADD UNIQUE KEY `uq_email` (`E-mail_Medico`) USING BTREE;

--
-- Indices de la tabla `medico_especialidad`
--
ALTER TABLE `medico_especialidad`
  ADD PRIMARY KEY (`RUT_Medico`,`ID_Especialidad`),
  ADD KEY `fk_Especialidad` (`ID_Especialidad`);

--
-- Indices de la tabla `paciente`
--
ALTER TABLE `paciente`
  ADD PRIMARY KEY (`RUT_Paciente`),
  ADD UNIQUE KEY `uq_paciente_email` (`Email_Paciente`),
  ADD KEY `ID_Prevision` (`ID_Prevision`);

--
-- Indices de la tabla `prevision`
--
ALTER TABLE `prevision`
  ADD PRIMARY KEY (`ID_Prevision`),
  ADD UNIQUE KEY `uq_Prevision` (`Prevision_Paciente`) USING BTREE;

--
-- Indices de la tabla `receta`
--
ALTER TABLE `receta`
  ADD PRIMARY KEY (`ID_Receta`),
  ADD KEY `fkNroAtencion_receta` (`Nro_Atencion`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `atencion`
--
ALTER TABLE `atencion`
  MODIFY `Nro_Atencion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `centro_medico`
--
ALTER TABLE `centro_medico`
  MODIFY `Codigo_Centro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cita`
--
ALTER TABLE `cita`
  MODIFY `Nro_Cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  MODIFY `ID_Especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `prevision`
--
ALTER TABLE `prevision`
  MODIFY `ID_Prevision` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `receta`
--
ALTER TABLE `receta`
  MODIFY `ID_Receta` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `atencion`
--
ALTER TABLE `atencion`
  ADD CONSTRAINT `cita_Atencion` FOREIGN KEY (`Nro_Cita`) REFERENCES `cita` (`Nro_Cita`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `atencion_diagnostico`
--
ALTER TABLE `atencion_diagnostico`
  ADD CONSTRAINT `atencion_diagnostico_atencion` FOREIGN KEY (`Nro_Atencion`) REFERENCES `atencion` (`Nro_Atencion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `atencion_dianostico_diagnostico` FOREIGN KEY (`Codigo_Diagnostico`) REFERENCES `diagnostico` (`Codigo_Diagnostico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `centro_medico_medico`
--
ALTER TABLE `centro_medico_medico`
  ADD CONSTRAINT `fk_Centro` FOREIGN KEY (`Codigo_Centro`) REFERENCES `centro_medico` (`Codigo_Centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_Medico` FOREIGN KEY (`RUT_Medico`) REFERENCES `medico` (`RUT_Medico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cita`
--
ALTER TABLE `cita`
  ADD CONSTRAINT `fkCentroMedico_Cita` FOREIGN KEY (`Codigo_Centro`,`RUT_Medico`) REFERENCES `centro_medico_medico` (`Codigo_Centro`, `RUT_Medico`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fkEstado_Cita` FOREIGN KEY (`ID_Estado`) REFERENCES `estado` (`ID_Estado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fkMedicoEspecialidad_Cita` FOREIGN KEY (`RUT_Medico`,`ID_Especialidad`) REFERENCES `medico_especialidad` (`RUT_Medico`, `ID_Especialidad`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fkPaciente_Cita` FOREIGN KEY (`RUT_Paciente`) REFERENCES `paciente` (`RUT_Paciente`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `medico_especialidad`
--
ALTER TABLE `medico_especialidad`
  ADD CONSTRAINT `fk_Especialidad` FOREIGN KEY (`ID_Especialidad`) REFERENCES `especialidad` (`ID_Especialidad`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_Medico_Especialidad` FOREIGN KEY (`RUT_Medico`) REFERENCES `medico` (`RUT_Medico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `paciente`
--
ALTER TABLE `paciente`
  ADD CONSTRAINT `previcion_Paciente` FOREIGN KEY (`ID_Prevision`) REFERENCES `prevision` (`ID_Prevision`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `receta`
--
ALTER TABLE `receta`
  ADD CONSTRAINT `fkAtencion_Receta` FOREIGN KEY (`Nro_Atencion`) REFERENCES `atencion` (`Nro_Atencion`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
