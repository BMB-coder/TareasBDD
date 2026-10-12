-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-10-2026 a las 21:56:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30


CREATE DATABASE IF NOT EXISTS saludusm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
-- Datos de prueba: las fechas de las citas son relativas al dia de importacion
-- (@lunes = lunes de la semana actual), asi siempre existen citas pasadas,
-- del dia y futuras. Se calcula antes de cambiar time_zone para usar la hora local.
-- NO reemplazar este archivo con una exportacion de phpMyAdmin: fijaria las fechas.
SET @hoy = CURDATE();
SET @lunes = DATE_SUB(@hoy, INTERVAL WEEKDAY(@hoy) DAY);
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

-- Consulta 10 de la Tarea 1: citas Reservadas o Confirmadas con fecha anterior
-- a hoy pasan a Cancelada. Se compara solo la fecha: las citas de hoy siguen
-- vigentes para que el medico pueda marcarlas como Atendida o No Asistio.
CREATE PROCEDURE `CancelarCitasVencidas` ()  MODIFIES SQL DATA BEGIN
    DECLARE v_EstadoCancelada INT;

    SET v_EstadoCancelada = (
        SELECT ID_Estado
        FROM estado
        WHERE Estado_Cita = 'Cancelada'
    );

    UPDATE cita AS c
    INNER JOIN estado AS e ON e.ID_Estado = c.ID_Estado
    SET c.ID_Estado = v_EstadoCancelada
    WHERE e.Estado_Cita IN ('Reservada', 'Confirmada')
      AND c.Fecha_Cita < CURDATE();
END$$

-- Sin sobreagendamiento: el medico y el paciente no pueden tener otra cita
-- vigente (no Cancelada) en el mismo bloque. Lo usan los triggers de cita.
CREATE PROCEDURE `ValidarBloqueCita` (IN `p_Nro_Cita` INT, IN `p_RUT_Medico` VARCHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_RUT_Paciente` VARCHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_Fecha` DATE, IN `p_Hora` TIME)  READS SQL DATA BEGIN
    IF EXISTS (
        SELECT 1
        FROM cita AS c
        INNER JOIN estado AS e ON e.ID_Estado = c.ID_Estado
        WHERE c.RUT_Medico = p_RUT_Medico
          AND c.Fecha_Cita = p_Fecha
          AND c.Hora_Cita = p_Hora
          AND c.Nro_Cita <> IFNULL(p_Nro_Cita, 0)
          AND e.Estado_Cita <> 'Cancelada'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El médico ya tiene una cita en ese bloque horario';
    END IF;

    IF p_RUT_Paciente IS NOT NULL AND EXISTS (
        SELECT 1
        FROM cita AS c
        INNER JOIN estado AS e ON e.ID_Estado = c.ID_Estado
        WHERE c.RUT_Paciente = p_RUT_Paciente
          AND c.Fecha_Cita = p_Fecha
          AND c.Hora_Cita = p_Hora
          AND c.Nro_Cita <> IFNULL(p_Nro_Cita, 0)
          AND e.Estado_Cita <> 'Cancelada'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El paciente ya tiene una cita a esa fecha y hora';
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

--
-- Volcado de datos para la tabla `atencion`
--

INSERT INTO `atencion` (`Nro_Atencion`, `Motivo_Atencion`, `Observaciones_Atencion`, `Nro_Cita`) VALUES
(1, 'Fiebre y congestión nasal', 'Rinofaringitis; presión arterial elevada en control.', 1),
(2, 'Dolor de garganta y fiebre', 'Amígdalas inflamadas con exudado.', 2),
(3, 'Irritación cutánea en antebrazos', 'Dermatitis de contacto leve.', 3),
(4, 'Control de presión arterial', 'Presión 150/95 mmHg. Se ajusta tratamiento.', 5),
(5, 'Tos y dificultad respiratoria', 'Sibilancias leves, cuadro viral asociado.', 6),
(6, 'Cefalea recurrente', 'Migraña sin aura, examen neurológico normal.', 8),
(7, 'Dolor epigástrico y acidez', 'Dolor a la palpación en epigastrio.', 10),
(8, 'Insomnio y ansiedad', 'Síntomas ansiosos de 3 meses de evolución.', 12),
(9, 'Dolor lumbar tras caída', 'Esguince de tobillo y lumbago mecánico.', 13),
(10, 'Control cardiovascular', 'Hipertensión y glicemia elevada.', 15),
(11, 'Resfrío y dolor de espalda', 'Cuadro viral leve y contractura dorsal.', 16),
(12, 'Control de diabetes', 'Hemoglobina glicosilada 7,8%.', 18),
(13, 'Dolor lumbar persistente', 'Lumbago sin irradiación.', 19),
(14, 'Malestar general', 'Resfrío común; presión arterial controlada.', 20),
(15, 'Ansiedad y dolor cervical', 'Mejoría parcial con tratamiento.', 22),
(16, 'Diarrea y control de glicemia', 'Gastroenteritis aguda en paciente diabético.', 24),
(17, 'Control de resfrío', 'Evolución favorable; sin indicación de fármacos.', 26);

--
-- Disparadores `atencion`
-- Solo puede existir una atencion si la cita esta en estado Atendida.
--
DELIMITER $$
CREATE TRIGGER `ValidarAtencion` BEFORE INSERT ON `atencion` FOR EACH ROW BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM cita AS c
        INNER JOIN estado AS e ON e.ID_Estado = c.ID_Estado
        WHERE c.Nro_Cita = NEW.Nro_Cita
          AND e.Estado_Cita = 'Atendida'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Solo se puede registrar una atención de una cita en estado Atendida';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atencion_diagnostico`
--

CREATE TABLE `atencion_diagnostico` (
  `Nro_Atencion` int(11) NOT NULL,
  `Codigo_Diagnostico` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atencion_diagnostico`
--

INSERT INTO `atencion_diagnostico` (`Nro_Atencion`, `Codigo_Diagnostico`) VALUES
(1, 'J00'),
(1, 'I10'),
(2, 'J03'),
(2, 'J00'),
(3, 'L30'),
(4, 'I10'),
(5, 'J00'),
(5, 'J45'),
(6, 'G43'),
(7, 'K29'),
(7, 'K21'),
(8, 'F41'),
(9, 'M54'),
(9, 'S93'),
(10, 'I10'),
(10, 'E11'),
(11, 'J00'),
(11, 'M54'),
(12, 'E11'),
(12, 'J00'),
(13, 'M54'),
(14, 'J00'),
(14, 'I10'),
(15, 'F41'),
(15, 'M54'),
(16, 'E11'),
(16, 'A09'),
(17, 'J00'),
(17, 'I10');

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
(1, 'SaludUSM San Joaquín', 'San Joaquín', 'RM'),
(2, 'SaludUSM Vitacura', 'Vitacura', 'RM'),
(3, 'SaludUSM Valparaíso', 'Valparaíso', 'VA'),
(4, 'SaludUSM Viña del Mar', 'Viña del Mar', 'VA'),
(5, 'SaludUSM Concepción', 'Concepción', 'BI');

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
(2, '00000000-1'),
(3, '12345678-9'),
(4, '12345678-9'),
(1, '98765432-1'),
(2, '13579246-8'),
(5, '13579246-8'),
(3, '17246813-5'),
(5, '17246813-5'),
(4, '16482753-1');

--
-- Disparadores `centro_medico_medico`
-- Impiden quitar la asignacion si el medico tiene citas futuras
-- (Reservada o Confirmada desde hoy) que dependen de ella.
--
DELIMITER $$
CREATE TRIGGER `ProtegerCentroMedico` BEFORE DELETE ON `centro_medico_medico` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1
        FROM cita AS c
        INNER JOIN estado AS e ON e.ID_Estado = c.ID_Estado
        WHERE c.RUT_Medico = OLD.RUT_Medico
          AND c.Codigo_Centro = OLD.Codigo_Centro
          AND e.Estado_Cita IN ('Reservada', 'Confirmada')
          AND c.Fecha_Cita >= CURDATE()
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede quitar el centro: el médico tiene citas futuras en él';
    END IF;
END
$$
DELIMITER ;

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
(1, DATE_ADD(@lunes, INTERVAL -42 DAY), '09:00:00', 1, '00000000-0', '00000000-1', 4, 2),
(2, DATE_ADD(@lunes, INTERVAL -41 DAY), '10:00:00', 3, '20123456-7', '12345678-9', 4, 2),
(3, DATE_ADD(@lunes, INTERVAL -40 DAY), '11:00:00', 1, '15678901-K', '98765432-1', 5, 2),
(4, DATE_ADD(@lunes, INTERVAL -39 DAY), '09:00:00', 1, '00000001-0', '00000000-1', 4, 3),
(5, DATE_ADD(@lunes, INTERVAL -35 DAY), '09:30:00', 2, '00000000-0', '00000000-1', 1, 2),
(6, DATE_ADD(@lunes, INTERVAL -34 DAY), '15:00:00', 4, '23456789-6', '12345678-9', 2, 2),
(7, DATE_ADD(@lunes, INTERVAL -33 DAY), '11:00:00', 1, '18234567-0', '98765432-1', 5, 3),
(8, DATE_ADD(@lunes, INTERVAL -32 DAY), '10:30:00', 5, '10987654-3', '13579246-8', 6, 2),
(9, DATE_ADD(@lunes, INTERVAL -31 DAY), '10:00:00', 2, '20123456-7', '00000000-1', 1, 4),
(10, DATE_ADD(@lunes, INTERVAL -28 DAY), '12:00:00', 3, '11223344-5', '17246813-5', 8, 2),
(11, DATE_ADD(@lunes, INTERVAL -27 DAY), '10:00:00', 1, '21098765-4', '00000000-1', 1, 3),
(12, DATE_ADD(@lunes, INTERVAL -26 DAY), '09:00:00', 2, '18234567-0', '13579246-8', 7, 2),
(13, DATE_ADD(@lunes, INTERVAL -24 DAY), '16:00:00', 4, '20123456-7', '16482753-1', 3, 2),
(14, DATE_ADD(@lunes, INTERVAL -21 DAY), '15:00:00', 4, '18234567-0', '16482753-1', 3, 4),
(15, DATE_ADD(@lunes, INTERVAL -20 DAY), '09:00:00', 1, '10987654-3', '00000000-1', 1, 2),
(16, DATE_ADD(@lunes, INTERVAL -19 DAY), '10:00:00', 3, '21098765-4', '12345678-9', 4, 2),
(17, DATE_ADD(@lunes, INTERVAL -18 DAY), '12:00:00', 3, '23456789-6', '12345678-9', 2, 3),
(18, DATE_ADD(@lunes, INTERVAL -17 DAY), '11:30:00', 5, '00000001-0', '17246813-5', 4, 2),
(19, DATE_ADD(@lunes, INTERVAL -14 DAY), '14:00:00', 4, '11223344-5', '16482753-1', 3, 2),
(20, DATE_ADD(@lunes, INTERVAL -13 DAY), '09:30:00', 1, '20123456-7', '00000000-1', 4, 2),
(21, DATE_ADD(@lunes, INTERVAL -12 DAY), '16:00:00', 3, '21098765-4', '17246813-5', 8, 1),
(22, DATE_ADD(@lunes, INTERVAL -11 DAY), '10:00:00', 2, '15678901-K', '13579246-8', 7, 2),
(23, DATE_ADD(@lunes, INTERVAL -10 DAY), '09:00:00', 5, '11223344-5', '13579246-8', 6, 3),
(24, DATE_ADD(@lunes, INTERVAL -7 DAY), '09:00:00', 3, '10987654-3', '17246813-5', 8, 2),
(25, DATE_ADD(@lunes, INTERVAL -6 DAY), '11:00:00', 1, '15678901-K', '98765432-1', 5, 3),
(26, DATE_ADD(@lunes, INTERVAL -5 DAY), '15:30:00', 1, '00000000-0', '00000000-1', 4, 2),
(27, DATE_ADD(@lunes, INTERVAL -4 DAY), '09:30:00', 3, '00000000-0', '12345678-9', 4, 4),
(28, DATE_ADD(@lunes, INTERVAL -3 DAY), '10:00:00', 2, '00000001-0', '00000000-1', 1, 0),
(29, DATE_ADD(@lunes, INTERVAL 7 DAY), '09:00:00', 1, '00000000-0', '00000000-1', 4, 0),
(30, DATE_ADD(@lunes, INTERVAL 8 DAY), '10:00:00', 2, '00000000-0', '00000000-1', 1, 1),
(31, DATE_ADD(@lunes, INTERVAL 9 DAY), '11:00:00', 1, '15678901-K', '98765432-1', 5, 0),
(32, DATE_ADD(@lunes, INTERVAL 10 DAY), '09:30:00', 3, '20123456-7', '12345678-9', 4, 1),
(33, DATE_ADD(@lunes, INTERVAL 14 DAY), '10:00:00', 5, '18234567-0', '13579246-8', 7, 0),
(34, DATE_ADD(@lunes, INTERVAL 15 DAY), '15:00:00', 4, '11223344-5', '16482753-1', 3, 0),
(35, DATE_ADD(@lunes, INTERVAL 16 DAY), '09:00:00', 2, '00000001-0', '00000000-1', 1, 0),
(36, DATE_ADD(@lunes, INTERVAL 18 DAY), '12:00:00', 5, '10987654-3', '17246813-5', 8, 1),
(37, DATE_ADD(@lunes, INTERVAL 22 DAY), '10:30:00', 3, '21098765-4', '12345678-9', 2, 0),
(38, DATE_ADD(@lunes, INTERVAL 24 DAY), '11:00:00', 1, '00000000-0', '98765432-1', 5, 0),
-- Citas Atendidas sin atencion registrada: casos para registrar la atencion (04.3)
(39, DATE_ADD(@lunes, INTERVAL -4 DAY), '11:00:00', 1, '10987654-3', '00000000-1', 4, 2),
(40, DATE_ADD(@lunes, INTERVAL -3 DAY), '14:00:00', 4, '11223344-5', '16482753-1', 3, 2);

-- Citas del dia actual para la agenda del medico (solo si hoy es dia habil)
INSERT INTO `cita` (`Nro_Cita`, `Fecha_Cita`, `Hora_Cita`, `Codigo_Centro`, `RUT_Paciente`, `RUT_Medico`, `ID_Especialidad`, `ID_Estado`)
SELECT 41, @hoy, '09:00:00', 1, '00000000-0', '00000000-1', 4, 1 FROM DUAL WHERE WEEKDAY(@hoy) < 5
UNION ALL SELECT 42, @hoy, '10:30:00', 2, '10987654-3', '00000000-1', 1, 0 FROM DUAL WHERE WEEKDAY(@hoy) < 5
UNION ALL SELECT 43, @hoy, '12:00:00', 1, '20123456-7', '00000000-1', 4, 1 FROM DUAL WHERE WEEKDAY(@hoy) < 5
UNION ALL SELECT 44, @hoy, '15:00:00', 3, '23456789-6', '12345678-9', 2, 0 FROM DUAL WHERE WEEKDAY(@hoy) < 5;

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

--
-- Disparadores `cita`: validan el bloque al reservar y al reprogramar
--
DELIMITER $$
CREATE TRIGGER `ValidarAgendaInsert` BEFORE INSERT ON `cita` FOR EACH ROW BEGIN
    CALL ValidarBloqueCita(NULL, NEW.RUT_Medico, NEW.RUT_Paciente, NEW.Fecha_Cita, NEW.Hora_Cita);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `ValidarAgendaUpdate` BEFORE UPDATE ON `cita` FOR EACH ROW BEGIN
    -- Solo al mover la cita: los cambios de estado no se validan aqui.
    IF NEW.Fecha_Cita <> OLD.Fecha_Cita OR NEW.Hora_Cita <> OLD.Hora_Cita
       OR NEW.RUT_Medico <> OLD.RUT_Medico THEN
        IF TIMESTAMP(NEW.Fecha_Cita, NEW.Hora_Cita) < NOW() THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede mover una cita a una fecha y hora pasada';
        END IF;
        CALL ValidarBloqueCita(OLD.Nro_Cita, NEW.RUT_Medico, NEW.RUT_Paciente, NEW.Fecha_Cita, NEW.Hora_Cita);
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

--
-- Volcado de datos para la tabla `diagnostico`
--

INSERT INTO `diagnostico` (`Codigo_Diagnostico`, `Descripcion_Diagnostico`) VALUES
('A09', 'Gastroenteritis de presunto origen infeccioso'),
('E11', 'Diabetes mellitus tipo 2'),
('F41', 'Otros trastornos de ansiedad'),
('G43', 'Migraña'),
('I10', 'Hipertensión esencial (primaria)'),
('J00', 'Rinofaringitis aguda (resfriado común)'),
('J03', 'Amigdalitis aguda'),
('J45', 'Asma'),
('K21', 'Enfermedad del reflujo gastroesofágico'),
('K29', 'Gastritis y duodenitis'),
('L30', 'Otras dermatitis'),
('M54', 'Dorsalgia'),
('S93', 'Luxación y esguince de tobillo y pie'),
('Z00', 'Examen general sin queja o diagnóstico informado');

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
(5, 'Dermatología'),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('00000000-1', 'Dr. Andrés Vidal', 'medicogenerico0@gmail.com', '$2y$10$xUPtWBW1jjtMgY0wcePACOEpSABZPg30K54sFGBwG9IqTmuJOCDLy'),
('12345678-9', 'Dr. Juan Pérez', 'juan.perez@saludusm.cl', NULL),
('98765432-1', 'Dra. María González', 'maria.gonzalez@saludusm.cl', NULL),
('13579246-8', 'Dr. Pedro Soto', 'pedro.soto@saludusm.cl', NULL),
('17246813-5', 'Dra. Camila Fuentes', 'camila.fuentes@saludusm.cl', NULL),
('16482753-1', 'Dr. Felipe Muñoz', 'felipe.munoz@saludusm.cl', NULL);

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
('00000000-1', 4),
('12345678-9', 2),
('12345678-9', 3),
('12345678-9', 4),
('98765432-1', 5),
('13579246-8', 6),
('13579246-8', 7),
('17246813-5', 4),
('17246813-5', 8),
('16482753-1', 3);

--
-- Disparadores `medico_especialidad`
-- Impiden quitar la asignacion si el medico tiene citas futuras
-- (Reservada o Confirmada desde hoy) que dependen de ella.
--
DELIMITER $$
CREATE TRIGGER `ProtegerEspecialidadMedico` BEFORE DELETE ON `medico_especialidad` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1
        FROM cita AS c
        INNER JOIN estado AS e ON e.ID_Estado = c.ID_Estado
        WHERE c.RUT_Medico = OLD.RUT_Medico
          AND c.ID_Especialidad = OLD.ID_Especialidad
          AND e.Estado_Cita IN ('Reservada', 'Confirmada')
          AND c.Fecha_Cita >= CURDATE()
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede quitar la especialidad: el médico tiene citas futuras con ella';
    END IF;
END
$$
DELIMITER ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `paciente`
--

INSERT INTO `paciente` (`RUT_Paciente`, `Nombre_Paciente`, `Telefono_Paciente`, `Fecha-Nacimiento_Paciente`, `Sexo_Paciente`, `ID_Prevision`, `Email_Paciente`, `Contrasena_Hash`) VALUES
('00000000-0', 'Martina Díaz', '912345670', '1995-01-01', 'F', 2, 'paciente.prueba@example.test', '$2y$10$kLjbFHC7SZ7JLBksG67x/..wwk0TVER5LhJxycjRbm1Ex/niirt8a'),
('00000001-0', 'Tomás Rivas', '912345671', '2000-01-02', 'M', 1, 'paciente2@gmail.com', '$2y$10$xfb1lfdIPImxhViFV0I1EOP7icBe4dDc7Wsfq0wudo6Qs0LtnJxYu'),
('20123456-7', 'Carlos Silva', '911223344', '1995-04-12', 'M', 1, NULL, NULL),
('15678901-K', 'Ana Rojas', '955667788', '1982-11-05', 'F', 2, NULL, NULL),
('23456789-6', 'Valentina Torres', '922334455', '2015-06-20', 'F', 1, NULL, NULL),
('10987654-3', 'Jorge Herrera', '933445566', '1958-03-02', 'M', 3, NULL, NULL),
('18234567-0', 'Francisca Reyes', '944556677', '1990-09-14', 'F', 2, NULL, NULL),
('11223344-5', 'Luis Castro', '966778899', '1972-01-30', 'M', 1, NULL, NULL),
('21098765-4', 'Sofía Morales', '977889900', '2001-12-08', 'Otro', 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prevision`
--

CREATE TABLE `prevision` (
  `ID_Prevision` int(11) NOT NULL,
  `Prevision_Paciente` varchar(10) NOT NULL COMMENT 'Fonasa | Isapre | Particular'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Volcado de datos para la tabla `receta`
--

INSERT INTO `receta` (`ID_Receta`, `Medicamento_Receta`, `Dosis_Receta`, `Dias-Tratamiento_Receta`, `Nro_Atencion`) VALUES
(1, 'Paracetamol 500 mg', '1 comprimido cada 8 horas', 5, 1),
(2, 'Losartán 50 mg', '1 comprimido cada 24 horas', 30, 1),
(3, 'Amoxicilina 500 mg', '1 cápsula cada 8 horas', 7, 2),
(4, 'Ibuprofeno 400 mg', '1 comprimido cada 8 horas', 5, 2),
(5, 'Hidrocortisona crema 1%', 'Aplicar 2 veces al día', 10, 3),
(6, 'Losartán 50 mg', '1 comprimido cada 24 horas', 30, 4),
(7, 'Paracetamol jarabe 120 mg/5 ml', '5 ml cada 8 horas', 5, 5),
(8, 'Salbutamol inhalador', '2 inhalaciones cada 6 horas', 7, 5),
(9, 'Sumatriptán 50 mg', '1 comprimido al inicio de la crisis', 10, 6),
(10, 'Omeprazol 20 mg', '1 cápsula cada 24 horas en ayunas', 30, 7),
(11, 'Sertralina 50 mg', '1 comprimido cada 24 horas', 60, 8),
(12, 'Ibuprofeno 600 mg', '1 comprimido cada 8 horas', 7, 9),
(13, 'Metformina 850 mg', '1 comprimido cada 12 horas', 90, 10),
(14, 'Losartán 50 mg', '1 comprimido cada 24 horas', 30, 10),
(15, 'Paracetamol 500 mg', '1 comprimido cada 8 horas', 5, 11),
(16, 'Metformina 850 mg', '1 comprimido cada 12 horas', 90, 12),
(17, 'Diclofenaco 50 mg', '1 comprimido cada 12 horas', 7, 13),
(18, 'Paracetamol 500 mg', '1 comprimido cada 8 horas', 4, 14),
(19, 'Sertralina 50 mg', '1 comprimido cada 24 horas', 60, 15),
(20, 'Sales de rehidratación oral', '1 sobre después de cada deposición', 3, 16);

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
  ADD KEY `idx_Medico_Fecha_Cita` (`RUT_Medico`,`Fecha_Cita`,`Hora_Cita`),
  ADD UNIQUE KEY `uq_Cita_Estado_Cita` (`Nro_Cita`,`ID_Estado`),
  ADD KEY `idx_Paciente_Fecha_Cita` (`RUT_Paciente`,`Fecha_Cita`,`Hora_Cita`),
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
  MODIFY `Nro_Atencion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `centro_medico`
--
ALTER TABLE `centro_medico`
  MODIFY `Codigo_Centro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `cita`
--
ALTER TABLE `cita`
  MODIFY `Nro_Cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  MODIFY `ID_Especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `prevision`
--
ALTER TABLE `prevision`
  MODIFY `ID_Prevision` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `receta`
--
ALTER TABLE `receta`
  MODIFY `ID_Receta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
