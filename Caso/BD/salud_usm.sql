-- MySQL dump 10.13  Distrib 8.4.13, for Linux (x86_64)
--
-- Host: localhost    Database: salud_usm
-- ------------------------------------------------------
-- Server version	8.4.13

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `salud_usm`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `salud_usm` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `salud_usm`;

--
-- Table structure for table `atencion`
--

DROP TABLE IF EXISTS `atencion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atencion` (
  `Nro_Atencion` int NOT NULL AUTO_INCREMENT,
  `Motivo_Atencion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Observaciones_Atencion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nro_Cita` int NOT NULL,
  PRIMARY KEY (`Nro_Atencion`),
  UNIQUE KEY `uq_Cita` (`Nro_Cita`) USING BTREE,
  CONSTRAINT `cita_Atencion` FOREIGN KEY (`Nro_Cita`) REFERENCES `cita` (`Nro_Cita`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atencion`
--

LOCK TABLES `atencion` WRITE;
/*!40000 ALTER TABLE `atencion` DISABLE KEYS */;
INSERT INTO `atencion` VALUES (1,'Fiebre y congestión nasal','Rinofaringitis; presión arterial elevada en control.',1),(2,'Dolor de garganta y fiebre','Amígdalas inflamadas con exudado.',2),(3,'Irritación cutánea en antebrazos','Dermatitis de contacto leve.',3),(4,'Control de presión arterial','Presión 150/95 mmHg. Se ajusta tratamiento.',5),(5,'Tos y dificultad respiratoria','Sibilancias leves, cuadro viral asociado.',6),(6,'Cefalea recurrente','Migraña sin aura, examen neurológico normal.',8),(7,'Dolor epigástrico y acidez','Dolor a la palpación en epigastrio.',10),(8,'Insomnio y ansiedad','Síntomas ansiosos de 3 meses de evolución.',12),(9,'Dolor lumbar tras caída','Esguince de tobillo y lumbago mecánico.',13),(10,'Control cardiovascular','Hipertensión y glicemia elevada.',15),(11,'Resfrío y dolor de espalda','Cuadro viral leve y contractura dorsal.',16),(12,'Control de diabetes','Hemoglobina glicosilada 7,8%.',18),(13,'Dolor lumbar persistente','Lumbago sin irradiación.',19),(14,'Malestar general','Resfrío común; presión arterial controlada.',20),(15,'Ansiedad y dolor cervical','Mejoría parcial con tratamiento.',22),(16,'Diarrea y control de glicemia','Gastroenteritis aguda en paciente diabético.',24),(17,'Control de resfrío','Evolución favorable; sin indicación de fármacos.',26),(18,'Motivo de prueba grupo 2','Observación de prueba grupo 4',39),(19,'Motivo de prueba grupo 2','Observación de prueba grupo 4',40),(20,'Motivo de prueba grupo 2','Observación de prueba grupo 4',165),(21,'Motivo de prueba grupo 3','Observación de prueba grupo 5',166),(22,'Motivo de prueba grupo 3','Observación de prueba grupo 5',167),(23,'Motivo de prueba grupo 3','Observación de prueba grupo 5',168),(24,'Motivo de prueba grupo 3','Observación de prueba grupo 5',169),(25,'Motivo de prueba grupo 3','Observación de prueba grupo 5',170),(26,'Motivo de prueba grupo 3','Observación de prueba grupo 6',171),(27,'Motivo de prueba grupo 3','Observación de prueba grupo 6',172),(28,'Motivo de prueba grupo 3','Observación de prueba grupo 6',173),(29,'Motivo de prueba grupo 3','Observación de prueba grupo 6',174),(30,'Motivo de prueba grupo 3','Observación de prueba grupo 6',175),(31,'Motivo de prueba grupo 4','Observación de prueba grupo 7',176),(32,'Motivo de prueba grupo 4','Observación de prueba grupo 7',177),(33,'Motivo de prueba grupo 4','Observación de prueba grupo 7',178),(34,'Motivo de prueba grupo 4','Observación de prueba grupo 7',179),(35,'Motivo de prueba grupo 4','Observación de prueba grupo 7',180),(36,'Motivo de prueba grupo 4','Observación de prueba grupo 8',181),(37,'Motivo de prueba grupo 4','Observación de prueba grupo 8',182),(38,'Motivo de prueba grupo 4','Observación de prueba grupo 8',183),(39,'Motivo de prueba grupo 4','Observación de prueba grupo 8',184),(40,'Motivo de prueba grupo 4','Observación de prueba grupo 8',185),(41,'Motivo de prueba grupo 5','Observación de prueba grupo 9',186),(42,'Motivo de prueba grupo 5','Observación de prueba grupo 9',187),(43,'Motivo de prueba grupo 5','Observación de prueba grupo 9',188),(44,'Motivo de prueba grupo 5','Observación de prueba grupo 9',189),(45,'Motivo de prueba grupo 5','Observación de prueba grupo 9',190),(46,'Motivo de prueba grupo 5','Observación de prueba grupo 10',191),(47,'Motivo de prueba grupo 5','Observación de prueba grupo 10',192),(48,'Motivo de prueba grupo 5','Observación de prueba grupo 10',193),(49,'Motivo de prueba grupo 5','Observación de prueba grupo 10',194),(50,'Motivo de prueba grupo 5','Observación de prueba grupo 10',195),(51,'Motivo de prueba grupo 6','Observación de prueba grupo 11',196),(52,'Motivo de prueba grupo 6','Observación de prueba grupo 11',197),(53,'Motivo de prueba grupo 6','Observación de prueba grupo 11',198),(54,'Motivo de prueba grupo 6','Observación de prueba grupo 11',199),(55,'Motivo de prueba grupo 6','Observación de prueba grupo 11',200),(56,'Motivo de prueba grupo 6','Observación de prueba grupo 12',201),(57,'Motivo de prueba grupo 6','Observación de prueba grupo 12',202),(58,'Motivo de prueba grupo 6','Observación de prueba grupo 12',203),(59,'Motivo de prueba grupo 6','Observación de prueba grupo 12',204),(60,'Motivo de prueba grupo 6','Observación de prueba grupo 12',205);
/*!40000 ALTER TABLE `atencion` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ValidarAtencion` BEFORE INSERT ON `atencion` FOR EACH ROW BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `atencion_diagnostico`
--

DROP TABLE IF EXISTS `atencion_diagnostico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atencion_diagnostico` (
  `Nro_Atencion` int NOT NULL,
  `Codigo_Diagnostico` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Nro_Atencion`,`Codigo_Diagnostico`),
  KEY `atencion_dianostico_diagnostico` (`Codigo_Diagnostico`),
  CONSTRAINT `atencion_diagnostico_atencion` FOREIGN KEY (`Nro_Atencion`) REFERENCES `atencion` (`Nro_Atencion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `atencion_dianostico_diagnostico` FOREIGN KEY (`Codigo_Diagnostico`) REFERENCES `diagnostico` (`Codigo_Diagnostico`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atencion_diagnostico`
--

LOCK TABLES `atencion_diagnostico` WRITE;
/*!40000 ALTER TABLE `atencion_diagnostico` DISABLE KEYS */;
INSERT INTO `atencion_diagnostico` VALUES (16,'A09'),(18,'A09'),(32,'A09'),(46,'A09'),(60,'A09'),(10,'E11'),(12,'E11'),(16,'E11'),(19,'E11'),(33,'E11'),(47,'E11'),(8,'F41'),(15,'F41'),(20,'F41'),(34,'F41'),(48,'F41'),(6,'G43'),(21,'G43'),(35,'G43'),(49,'G43'),(1,'I10'),(4,'I10'),(10,'I10'),(14,'I10'),(17,'I10'),(22,'I10'),(36,'I10'),(50,'I10'),(1,'J00'),(2,'J00'),(5,'J00'),(11,'J00'),(12,'J00'),(14,'J00'),(17,'J00'),(23,'J00'),(37,'J00'),(51,'J00'),(2,'J03'),(24,'J03'),(38,'J03'),(52,'J03'),(5,'J45'),(25,'J45'),(39,'J45'),(53,'J45'),(7,'K21'),(26,'K21'),(40,'K21'),(54,'K21'),(7,'K29'),(27,'K29'),(41,'K29'),(55,'K29'),(3,'L30'),(28,'L30'),(42,'L30'),(56,'L30'),(9,'M54'),(11,'M54'),(13,'M54'),(15,'M54'),(29,'M54'),(43,'M54'),(57,'M54'),(9,'S93'),(30,'S93'),(44,'S93'),(58,'S93'),(31,'Z00'),(45,'Z00'),(59,'Z00');
/*!40000 ALTER TABLE `atencion_diagnostico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `centro_medico`
--

DROP TABLE IF EXISTS `centro_medico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `centro_medico` (
  `Codigo_Centro` int NOT NULL AUTO_INCREMENT,
  `Nombre_Centro` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Comuna_Centro` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Region_Centro` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RM, VA, CO, AT...',
  PRIMARY KEY (`Codigo_Centro`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `centro_medico`
--

LOCK TABLES `centro_medico` WRITE;
/*!40000 ALTER TABLE `centro_medico` DISABLE KEYS */;
INSERT INTO `centro_medico` VALUES (1,'SaludUSM San Joaquín','San Joaquín','RM'),(2,'SaludUSM Vitacura','Vitacura','RM'),(3,'SaludUSM Valparaíso','Valparaíso','VA'),(4,'SaludUSM Viña del Mar','Viña del Mar','VA'),(5,'SaludUSM Concepción','Concepción','BI');
/*!40000 ALTER TABLE `centro_medico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `centro_medico_medico`
--

DROP TABLE IF EXISTS `centro_medico_medico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `centro_medico_medico` (
  `Codigo_Centro` int NOT NULL,
  `RUT_Medico` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Codigo_Centro`,`RUT_Medico`),
  KEY `fk_Medico` (`RUT_Medico`),
  CONSTRAINT `fk_Centro` FOREIGN KEY (`Codigo_Centro`) REFERENCES `centro_medico` (`Codigo_Centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Medico` FOREIGN KEY (`RUT_Medico`) REFERENCES `medico` (`RUT_Medico`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `centro_medico_medico`
--

LOCK TABLES `centro_medico_medico` WRITE;
/*!40000 ALTER TABLE `centro_medico_medico` DISABLE KEYS */;
INSERT INTO `centro_medico_medico` VALUES (1,'00000000-1'),(2,'00000000-1'),(3,'00000000-1'),(4,'00000000-1'),(5,'00000000-1'),(1,'10000000-8'),(2,'10000000-8'),(3,'10000000-8'),(4,'10000000-8'),(5,'10000000-8'),(1,'10000001-6'),(2,'10000001-6'),(3,'10000001-6'),(4,'10000001-6'),(5,'10000001-6'),(1,'10000002-4'),(2,'10000002-4'),(3,'10000002-4'),(4,'10000002-4'),(5,'10000002-4'),(1,'10000003-2'),(2,'10000003-2'),(3,'10000003-2'),(4,'10000003-2'),(5,'10000003-2'),(1,'10000004-0'),(2,'10000004-0'),(3,'10000004-0'),(4,'10000004-0'),(5,'10000004-0'),(1,'10000005-9'),(2,'10000005-9'),(3,'10000005-9'),(4,'10000005-9'),(5,'10000005-9'),(1,'10000006-7'),(2,'10000006-7'),(3,'10000006-7'),(4,'10000006-7'),(5,'10000006-7'),(1,'10000007-5'),(2,'10000007-5'),(3,'10000007-5'),(4,'10000007-5'),(5,'10000007-5'),(1,'10000008-3'),(2,'10000008-3'),(3,'10000008-3'),(4,'10000008-3'),(5,'10000008-3'),(1,'10000009-K'),(2,'10000009-K'),(3,'10000009-K'),(4,'10000009-K'),(5,'10000009-K'),(1,'10000010-5'),(2,'10000010-5'),(3,'10000010-5'),(4,'10000010-5'),(5,'10000010-5'),(1,'10000011-3'),(2,'10000011-3'),(3,'10000011-3'),(4,'10000011-3'),(5,'10000011-3'),(1,'10000012-1'),(2,'10000012-1'),(3,'10000012-1'),(4,'10000012-1'),(5,'10000012-1'),(1,'10000013-K'),(2,'10000013-K'),(3,'10000013-K'),(4,'10000013-K'),(5,'10000013-K'),(1,'10000014-8'),(2,'10000014-8'),(3,'10000014-8'),(4,'10000014-8'),(5,'10000014-8'),(1,'10000015-6'),(2,'10000015-6'),(3,'10000015-6'),(4,'10000015-6'),(5,'10000015-6'),(1,'12345678-9'),(2,'12345678-9'),(3,'12345678-9'),(4,'12345678-9'),(5,'12345678-9'),(1,'13579246-8'),(2,'13579246-8'),(3,'13579246-8'),(4,'13579246-8'),(5,'13579246-8'),(1,'16482753-1'),(2,'16482753-1'),(3,'16482753-1'),(4,'16482753-1'),(5,'16482753-1'),(1,'17246813-5'),(2,'17246813-5'),(3,'17246813-5'),(4,'17246813-5'),(5,'17246813-5'),(1,'98765432-1'),(2,'98765432-1'),(3,'98765432-1'),(4,'98765432-1'),(5,'98765432-1');
/*!40000 ALTER TABLE `centro_medico_medico` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ProtegerCentroMedico` BEFORE DELETE ON `centro_medico_medico` FOR EACH ROW BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cita`
--

DROP TABLE IF EXISTS `cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cita` (
  `Nro_Cita` int NOT NULL AUTO_INCREMENT,
  `Fecha_Cita` date NOT NULL,
  `Hora_Cita` time NOT NULL,
  `Codigo_Centro` int NOT NULL,
  `RUT_Paciente` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'XXXXXXXX-X',
  `RUT_Medico` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'XXXXXXXX-X',
  `ID_Especialidad` int NOT NULL,
  `ID_Estado` int NOT NULL,
  PRIMARY KEY (`Nro_Cita`),
  UNIQUE KEY `uq_Cita_Estado_Cita` (`Nro_Cita`,`ID_Estado`),
  KEY `idx_Medico_Fecha_Cita` (`RUT_Medico`,`Fecha_Cita`,`Hora_Cita`),
  KEY `idx_Paciente_Fecha_Cita` (`RUT_Paciente`,`Fecha_Cita`,`Hora_Cita`),
  KEY `fkEstado_Cita` (`ID_Estado`),
  KEY `fkCentroMedico_Cita` (`Codigo_Centro`,`RUT_Medico`),
  KEY `fkMedicoEspecialidad_Cita` (`RUT_Medico`,`ID_Especialidad`),
  KEY `fkPaciente_Cita` (`RUT_Paciente`) USING BTREE,
  CONSTRAINT `fkCentroMedico_Cita` FOREIGN KEY (`Codigo_Centro`, `RUT_Medico`) REFERENCES `centro_medico_medico` (`Codigo_Centro`, `RUT_Medico`) ON UPDATE CASCADE,
  CONSTRAINT `fkEstado_Cita` FOREIGN KEY (`ID_Estado`) REFERENCES `estado` (`ID_Estado`) ON UPDATE CASCADE,
  CONSTRAINT `fkMedicoEspecialidad_Cita` FOREIGN KEY (`RUT_Medico`, `ID_Especialidad`) REFERENCES `medico_especialidad` (`RUT_Medico`, `ID_Especialidad`) ON UPDATE CASCADE,
  CONSTRAINT `fkPaciente_Cita` FOREIGN KEY (`RUT_Paciente`) REFERENCES `paciente` (`RUT_Paciente`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cita`
--

LOCK TABLES `cita` WRITE;
/*!40000 ALTER TABLE `cita` DISABLE KEYS */;
INSERT INTO `cita` VALUES (1,'2026-09-21','09:00:00',1,'00000000-0','00000000-1',4,2),(2,'2026-09-22','10:00:00',3,'20123456-7','12345678-9',4,2),(3,'2026-09-23','11:00:00',1,'15678901-K','98765432-1',5,2),(4,'2026-09-24','09:00:00',1,'00000001-0','00000000-1',4,3),(5,'2026-09-28','09:30:00',2,'00000000-0','00000000-1',1,2),(6,'2026-09-29','15:00:00',4,'23456789-6','12345678-9',2,2),(7,'2026-09-30','11:00:00',1,'18234567-0','98765432-1',5,3),(8,'2026-10-01','10:30:00',5,'10987654-3','13579246-8',6,2),(9,'2026-10-02','10:00:00',2,'20123456-7','00000000-1',1,4),(10,'2026-10-05','12:00:00',3,'11223344-5','17246813-5',8,2),(11,'2026-10-06','10:00:00',1,'21098765-4','00000000-1',1,3),(12,'2026-10-07','09:00:00',2,'18234567-0','13579246-8',7,2),(13,'2026-10-09','16:00:00',4,'20123456-7','16482753-1',3,2),(14,'2026-10-12','15:00:00',4,'18234567-0','16482753-1',3,4),(15,'2026-10-13','09:00:00',1,'10987654-3','00000000-1',1,2),(16,'2026-10-14','10:00:00',3,'21098765-4','12345678-9',4,2),(17,'2026-10-15','12:00:00',3,'23456789-6','12345678-9',2,3),(18,'2026-10-16','11:30:00',5,'00000001-0','17246813-5',4,2),(19,'2026-10-19','14:00:00',4,'11223344-5','16482753-1',3,2),(20,'2026-10-20','09:30:00',1,'20123456-7','00000000-1',4,2),(21,'2026-10-21','16:00:00',3,'21098765-4','17246813-5',8,1),(22,'2026-10-22','10:00:00',2,'15678901-K','13579246-8',7,2),(23,'2026-10-23','09:00:00',5,'11223344-5','13579246-8',6,3),(24,'2026-10-26','09:00:00',3,'10987654-3','17246813-5',8,2),(25,'2026-10-27','11:00:00',1,'15678901-K','98765432-1',5,3),(26,'2026-10-28','15:30:00',1,'00000000-0','00000000-1',4,2),(27,'2026-10-29','09:30:00',3,'00000000-0','12345678-9',4,4),(28,'2026-10-30','10:00:00',2,'00000001-0','00000000-1',1,0),(29,'2026-11-09','09:00:00',1,'00000000-0','00000000-1',4,0),(30,'2026-11-10','10:00:00',2,'00000000-0','00000000-1',1,1),(31,'2026-11-11','11:00:00',1,'15678901-K','98765432-1',5,0),(32,'2026-11-12','09:30:00',3,'20123456-7','12345678-9',4,1),(33,'2026-11-16','10:00:00',5,'18234567-0','13579246-8',7,0),(34,'2026-11-17','15:00:00',4,'11223344-5','16482753-1',3,0),(35,'2026-11-18','09:00:00',2,'00000001-0','00000000-1',1,0),(36,'2026-11-20','12:00:00',5,'10987654-3','17246813-5',8,1),(37,'2026-11-24','10:30:00',3,'21098765-4','12345678-9',2,0),(38,'2026-11-26','11:00:00',1,'00000000-0','98765432-1',5,0),(39,'2026-10-29','11:00:00',1,'10987654-3','00000000-1',4,2),(40,'2026-10-30','14:00:00',4,'11223344-5','16482753-1',3,2),(41,'2026-11-02','09:00:00',1,'00000000-0','00000000-1',4,3),(42,'2026-11-02','10:30:00',2,'10987654-3','00000000-1',1,0),(43,'2026-11-02','12:00:00',1,'20123456-7','00000000-1',4,3),(44,'2026-11-02','15:00:00',3,'23456789-6','12345678-9',2,0),(45,'2026-11-03','11:30:00',1,'00000001-9','00000000-1',1,3),(46,'2026-11-03','12:00:00',2,'00000001-9','10000000-8',1,1),(47,'2026-11-03','12:30:00',3,'00000001-9','10000001-6',4,0),(48,'2026-11-04','11:30:00',4,'00000002-7','10000002-4',1,1),(49,'2026-11-04','12:00:00',5,'00000002-7','10000003-2',2,0),(50,'2026-11-04','12:30:00',1,'00000002-7','10000004-0',5,3),(51,'2026-11-05','11:30:00',2,'00000003-5','10000005-9',1,0),(52,'2026-11-05','12:00:00',3,'00000003-5','10000006-7',3,1),(53,'2026-11-05','12:30:00',4,'00000003-5','10000007-5',6,0),(54,'2026-11-06','09:30:00',5,'00000004-3','10000008-3',1,1),(55,'2026-11-06','10:00:00',1,'00000004-3','10000009-K',4,0),(56,'2026-11-06','10:30:00',2,'00000004-3','10000010-5',1,1),(57,'2026-11-07','12:00:00',3,'00000005-1','10000011-3',2,0),(58,'2026-11-07','12:30:00',4,'00000005-1','10000012-1',5,1),(59,'2026-11-07','13:00:00',5,'00000005-1','10000013-K',1,0),(60,'2026-11-08','12:00:00',1,'00000006-K','10000014-8',3,1),(61,'2026-11-08','12:30:00',2,'00000006-K','10000015-6',6,0),(62,'2026-11-08','13:00:00',3,'00000006-K','12345678-9',2,1),(63,'2026-11-09','14:00:00',4,'00000007-8','13579246-8',6,0),(64,'2026-11-09','14:30:00',5,'00000007-8','16482753-1',3,1),(65,'2026-11-09','15:00:00',1,'00000007-8','17246813-5',4,0),(66,'2026-11-10','15:00:00',2,'00000008-6','98765432-1',5,1),(67,'2026-11-10','15:30:00',3,'00000008-6','00000000-1',4,0),(68,'2026-11-10','16:00:00',4,'00000008-6','10000000-8',2,1),(69,'2026-11-11','09:30:00',5,'00000009-4','10000001-6',5,0),(70,'2026-11-11','10:00:00',1,'00000009-4','10000002-4',7,1),(71,'2026-11-11','10:30:00',2,'00000009-4','10000003-2',3,0),(72,'2026-11-12','09:30:00',3,'00000010-8','10000004-0',6,1),(73,'2026-11-12','10:00:00',4,'00000010-8','10000005-9',2,0),(74,'2026-11-12','10:30:00',5,'00000010-8','10000006-7',4,1),(75,'2026-11-13','14:00:00',1,'00000011-6','10000007-5',7,0),(76,'2026-11-13','14:30:00',2,'00000011-6','10000008-3',2,1),(77,'2026-11-13','15:00:00',3,'00000011-6','10000009-K',5,0),(78,'2026-11-14','11:00:00',4,'00000012-4','10000010-5',7,1),(79,'2026-11-14','11:30:00',5,'00000012-4','10000011-3',3,0),(80,'2026-11-14','12:00:00',1,'00000012-4','10000012-1',6,1),(81,'2026-11-15','14:00:00',2,'00000013-2','10000013-K',2,0),(82,'2026-11-15','14:30:00',3,'00000013-2','10000014-8',4,1),(83,'2026-11-15','15:00:00',4,'00000013-2','10000015-6',7,0),(84,'2026-11-16','09:00:00',5,'00000014-0','12345678-9',3,1),(85,'2026-11-16','09:30:00',1,'00000014-0','13579246-8',7,0),(86,'2026-11-16','10:00:00',2,'00000014-0','16482753-1',3,1),(87,'2026-11-17','09:30:00',3,'00000015-9','17246813-5',8,0),(88,'2026-11-17','10:00:00',4,'00000015-9','98765432-1',5,1),(89,'2026-11-17','10:30:00',5,'00000015-9','00000000-1',1,0),(90,'2026-11-18','10:30:00',1,'00000016-7','10000000-8',3,1),(91,'2026-11-18','11:00:00',2,'00000016-7','10000001-6',6,0),(92,'2026-11-18','11:30:00',3,'00000016-7','10000002-4',8,1),(93,'2026-11-19','12:30:00',4,'00000017-5','10000003-2',4,0),(94,'2026-11-19','13:00:00',5,'00000017-5','10000004-0',7,1),(95,'2026-11-19','13:30:00',1,'00000017-5','10000005-9',8,0),(96,'2026-11-20','12:30:00',2,'00000018-3','10000006-7',5,1),(97,'2026-11-20','13:00:00',3,'00000018-3','10000007-5',8,0),(98,'2026-11-20','13:30:00',4,'00000018-3','10000008-3',3,1),(99,'2026-11-21','09:00:00',5,'00000019-1','10000009-K',6,0),(100,'2026-11-21','09:30:00',1,'00000019-1','10000010-5',8,1),(101,'2026-11-21','10:00:00',2,'00000019-1','10000011-3',4,0),(102,'2026-11-22','10:00:00',3,'00000020-5','10000012-1',7,1),(103,'2026-11-22','10:30:00',4,'00000020-5','10000013-K',8,0),(104,'2026-11-22','11:00:00',5,'00000020-5','10000014-8',5,1),(105,'2026-11-23','14:30:00',1,'00000021-3','10000015-6',8,0),(106,'2026-11-23','15:00:00',2,'00000021-3','12345678-9',4,1),(107,'2026-11-23','15:30:00',3,'00000021-3','13579246-8',6,0),(108,'2026-11-24','14:30:00',4,'00000022-1','16482753-1',3,1),(109,'2026-11-24','15:00:00',5,'00000022-1','17246813-5',4,0),(110,'2026-11-24','15:30:00',1,'00000022-1','98765432-1',5,1),(111,'2026-11-25','12:30:00',2,'00000023-K','00000000-1',4,0),(112,'2026-11-25','13:00:00',3,'00000023-K','10000000-8',1,1),(113,'2026-11-25','13:30:00',4,'00000023-K','10000001-6',4,0),(114,'2026-11-26','13:30:00',5,'00000024-8','10000002-4',1,1),(115,'2026-11-26','14:00:00',1,'00000024-8','10000003-2',2,0),(116,'2026-11-26','14:30:00',2,'00000024-8','10000004-0',5,1),(117,'2026-11-27','14:00:00',3,'00000025-6','10000005-9',1,0),(118,'2026-11-27','14:30:00',4,'00000025-6','10000006-7',3,1),(119,'2026-11-27','15:00:00',5,'00000025-6','10000007-5',6,0),(120,'2026-11-28','15:00:00',1,'00000026-4','10000008-3',1,1),(121,'2026-11-28','15:30:00',2,'00000026-4','10000009-K',4,0),(122,'2026-11-28','16:00:00',3,'00000026-4','10000010-5',1,1),(123,'2026-11-29','12:00:00',4,'00000027-2','10000011-3',2,0),(124,'2026-11-29','12:30:00',5,'00000027-2','10000012-1',5,1),(125,'2026-11-29','13:00:00',1,'00000027-2','10000013-K',1,0),(126,'2026-11-30','11:00:00',2,'00000028-0','10000014-8',3,1),(127,'2026-11-30','11:30:00',3,'00000028-0','10000015-6',6,0),(128,'2026-11-30','12:00:00',4,'00000028-0','12345678-9',2,1),(129,'2026-12-01','13:30:00',5,'00000029-9','13579246-8',7,0),(130,'2026-12-01','14:00:00',1,'00000029-9','16482753-1',3,1),(131,'2026-12-01','14:30:00',2,'00000029-9','17246813-5',8,0),(132,'2026-12-02','15:30:00',3,'00000030-2','98765432-1',5,1),(133,'2026-12-02','16:00:00',4,'00000030-2','00000000-1',1,0),(134,'2026-12-02','16:30:00',5,'00000030-2','10000000-8',2,1),(135,'2026-12-03','11:00:00',1,'00000031-0','10000001-6',5,0),(136,'2026-12-03','11:30:00',2,'00000031-0','10000002-4',7,1),(137,'2026-12-03','12:00:00',3,'00000031-0','10000003-2',3,0),(138,'2026-12-04','13:00:00',4,'00000032-9','10000004-0',6,1),(139,'2026-12-04','13:30:00',5,'00000032-9','10000005-9',2,0),(140,'2026-12-04','14:00:00',1,'00000032-9','10000006-7',4,1),(141,'2026-12-05','12:00:00',2,'00000033-7','10000007-5',7,0),(142,'2026-12-05','12:30:00',3,'00000033-7','10000008-3',2,1),(143,'2026-12-05','13:00:00',4,'00000033-7','10000009-K',5,0),(144,'2026-12-06','11:00:00',5,'00000034-5','10000010-5',7,1),(145,'2026-12-06','11:30:00',1,'00000034-5','10000011-3',3,0),(146,'2026-12-06','12:00:00',2,'00000034-5','10000012-1',6,1),(147,'2026-12-07','10:30:00',3,'00000035-3','10000013-K',2,0),(148,'2026-12-07','11:00:00',4,'00000035-3','10000014-8',4,1),(149,'2026-12-07','11:30:00',5,'00000035-3','10000015-6',7,0),(150,'2026-12-08','11:30:00',1,'00000036-1','12345678-9',3,1),(151,'2026-12-08','12:00:00',2,'00000036-1','13579246-8',6,0),(152,'2026-12-08','12:30:00',3,'00000036-1','16482753-1',3,1),(153,'2026-12-09','15:30:00',4,'00000037-K','17246813-5',4,0),(154,'2026-12-09','16:00:00',5,'00000037-K','98765432-1',5,1),(155,'2026-12-09','16:30:00',1,'00000037-K','00000000-1',4,0),(156,'2026-12-10','10:30:00',2,'00000038-8','10000000-8',3,1),(157,'2026-12-10','11:00:00',3,'00000038-8','10000001-6',6,0),(158,'2026-12-10','11:30:00',4,'00000038-8','10000002-4',8,1),(159,'2026-12-11','10:00:00',5,'00000039-6','10000003-2',4,0),(160,'2026-12-11','10:30:00',1,'00000039-6','10000004-0',7,1),(161,'2026-12-11','11:00:00',2,'00000039-6','10000005-9',8,0),(162,'2026-12-12','09:00:00',3,'00000040-K','10000006-7',5,1),(163,'2026-12-12','09:30:00',4,'00000040-K','10000007-5',8,0),(164,'2026-12-12','10:00:00',5,'00000040-K','10000008-3',3,1),(165,'2020-01-01','09:00:00',1,'00000001-9','00000000-1',1,2),(166,'2020-01-02','09:00:00',2,'00000002-7','10000000-8',1,2),(167,'2020-01-03','09:00:00',3,'00000003-5','10000001-6',4,2),(168,'2020-01-04','09:00:00',4,'00000004-3','10000002-4',1,2),(169,'2020-01-05','09:00:00',5,'00000005-1','10000003-2',2,2),(170,'2020-01-06','09:00:00',1,'00000006-K','10000004-0',5,2),(171,'2020-01-07','09:00:00',2,'00000007-8','10000005-9',1,2),(172,'2020-01-08','09:00:00',3,'00000008-6','10000006-7',3,2),(173,'2020-01-09','09:00:00',4,'00000009-4','10000007-5',6,2),(174,'2020-01-10','09:00:00',5,'00000010-8','10000008-3',1,2),(175,'2020-01-11','09:00:00',1,'00000011-6','10000009-K',4,2),(176,'2020-01-12','09:00:00',2,'00000012-4','10000010-5',1,2),(177,'2020-01-13','09:00:00',3,'00000013-2','10000011-3',2,2),(178,'2020-01-14','09:00:00',4,'00000014-0','10000012-1',5,2),(179,'2020-01-15','09:00:00',5,'00000015-9','10000013-K',1,2),(180,'2020-01-16','09:00:00',1,'00000016-7','10000014-8',3,2),(181,'2020-01-17','09:00:00',2,'00000017-5','10000015-6',6,2),(182,'2020-01-18','09:00:00',3,'00000018-3','12345678-9',2,2),(183,'2020-01-19','09:00:00',4,'00000019-1','13579246-8',6,2),(184,'2020-01-20','09:00:00',5,'00000020-5','16482753-1',3,2),(185,'2020-01-21','09:00:00',1,'00000021-3','17246813-5',4,2),(186,'2020-01-22','09:00:00',2,'00000022-1','98765432-1',5,2),(187,'2020-01-23','09:00:00',3,'00000023-K','00000000-1',4,2),(188,'2020-01-24','09:00:00',4,'00000024-8','10000000-8',2,2),(189,'2020-01-25','09:00:00',5,'00000025-6','10000001-6',5,2),(190,'2020-01-26','09:00:00',1,'00000026-4','10000002-4',7,2),(191,'2020-01-27','09:00:00',2,'00000027-2','10000003-2',3,2),(192,'2020-01-28','09:00:00',3,'00000028-0','10000004-0',6,2),(193,'2020-01-29','09:00:00',4,'00000029-9','10000005-9',2,2),(194,'2020-01-30','09:00:00',5,'00000030-2','10000006-7',4,2),(195,'2020-01-31','09:00:00',1,'00000031-0','10000007-5',7,2),(196,'2020-02-01','09:00:00',2,'00000032-9','10000008-3',2,2),(197,'2020-02-02','09:00:00',3,'00000033-7','10000009-K',5,2),(198,'2020-02-03','09:00:00',4,'00000034-5','10000010-5',7,2),(199,'2020-02-04','09:00:00',5,'00000035-3','10000011-3',3,2),(200,'2020-02-05','09:00:00',1,'00000036-1','10000012-1',6,2),(201,'2020-02-06','09:00:00',2,'00000037-K','10000013-K',2,2),(202,'2020-02-07','09:00:00',3,'00000038-8','10000014-8',4,2),(203,'2020-02-08','09:00:00',4,'00000039-6','10000015-6',7,2),(204,'2020-02-09','09:00:00',5,'00000040-K','12345678-9',3,2),(205,'2020-02-10','09:00:00',1,'00000001-9','13579246-8',7,2);
/*!40000 ALTER TABLE `cita` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `FechaCita` BEFORE INSERT ON `cita` FOR EACH ROW BEGIN
    IF TIMESTAMP(NEW.`Fecha_Cita`, NEW.`Hora_Cita`) < NOW() THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede registrar una cita en una fecha y hora pasada';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ValidarAgendaInsert` BEFORE INSERT ON `cita` FOR EACH ROW BEGIN
    CALL ValidarBloqueCita(NULL, NEW.RUT_Medico, NEW.RUT_Paciente, NEW.Fecha_Cita, NEW.Hora_Cita);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ValidarAgendaUpdate` BEFORE UPDATE ON `cita` FOR EACH ROW BEGIN
    -- Solo al mover la cita: los cambios de estado no se validan aqui.
    IF NEW.Fecha_Cita <> OLD.Fecha_Cita OR NEW.Hora_Cita <> OLD.Hora_Cita
       OR NEW.RUT_Medico <> OLD.RUT_Medico THEN
        IF TIMESTAMP(NEW.Fecha_Cita, NEW.Hora_Cita) < NOW() THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede mover una cita a una fecha y hora pasada';
        END IF;
        CALL ValidarBloqueCita(OLD.Nro_Cita, NEW.RUT_Medico, NEW.RUT_Paciente, NEW.Fecha_Cita, NEW.Hora_Cita);
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `diagnostico`
--

DROP TABLE IF EXISTS `diagnostico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diagnostico` (
  `Codigo_Diagnostico` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Codigo CIE-10',
  `Descripcion_Diagnostico` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Codigo_Diagnostico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diagnostico`
--

LOCK TABLES `diagnostico` WRITE;
/*!40000 ALTER TABLE `diagnostico` DISABLE KEYS */;
INSERT INTO `diagnostico` VALUES ('A09','Gastroenteritis de presunto origen infeccioso'),('E11','Diabetes mellitus tipo 2'),('F41','Otros trastornos de ansiedad'),('G43','Migraña'),('I10','Hipertensión esencial (primaria)'),('J00','Rinofaringitis aguda (resfriado común)'),('J03','Amigdalitis aguda'),('J45','Asma'),('K21','Enfermedad del reflujo gastroesofágico'),('K29','Gastritis y duodenitis'),('L30','Otras dermatitis'),('M54','Dorsalgia'),('S93','Luxación y esguince de tobillo y pie'),('Z00','Examen general sin queja o diagnóstico informado');
/*!40000 ALTER TABLE `diagnostico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especialidad`
--

DROP TABLE IF EXISTS `especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especialidad` (
  `ID_Especialidad` int NOT NULL AUTO_INCREMENT,
  `Nombre_Especialidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID_Especialidad`),
  UNIQUE KEY `uq_Especialidad` (`Nombre_Especialidad`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especialidad`
--

LOCK TABLES `especialidad` WRITE;
/*!40000 ALTER TABLE `especialidad` DISABLE KEYS */;
INSERT INTO `especialidad` VALUES (1,'Cardiología'),(5,'Dermatología'),(8,'Gastroenterología'),(4,'Medicina General'),(6,'Neurología'),(2,'Pediatría'),(7,'Psiquiatría'),(3,'Traumatología');
/*!40000 ALTER TABLE `especialidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado`
--

DROP TABLE IF EXISTS `estado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado` (
  `ID_Estado` int NOT NULL,
  `Estado_Cita` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Reservada | Confirmada | Atendida | No Asistió | Cancelada',
  PRIMARY KEY (`ID_Estado`),
  UNIQUE KEY `uq_Estado` (`Estado_Cita`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado`
--

LOCK TABLES `estado` WRITE;
/*!40000 ALTER TABLE `estado` DISABLE KEYS */;
INSERT INTO `estado` VALUES (2,'Atendida'),(4,'Cancelada'),(1,'Confirmada'),(3,'No Asistió'),(0,'Reservada');
/*!40000 ALTER TABLE `estado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medico`
--

DROP TABLE IF EXISTS `medico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico` (
  `RUT_Medico` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'XXXXXXXX-X',
  `Nombre_Medico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `E-mail_Medico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '...@...',
  `Contrasena_Hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RUT_Medico`),
  UNIQUE KEY `uq_email` (`E-mail_Medico`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Realizada con modelo 3FN';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medico`
--

LOCK TABLES `medico` WRITE;
/*!40000 ALTER TABLE `medico` DISABLE KEYS */;
INSERT INTO `medico` VALUES ('00000000-1','Dr. Andrés Vidal','medicogenerico0@gmail.com','$2y$10$xUPtWBW1jjtMgY0wcePACOEpSABZPg30K54sFGBwG9IqTmuJOCDLy'),('10000000-8','Médico Genérico1','medicogenerico1@gmail.com',NULL),('10000001-6','Médico Genérico2','medicogenerico2@gmail.com',NULL),('10000002-4','Médico Genérico3','medicogenerico3@gmail.com',NULL),('10000003-2','Médico Genérico4','medicogenerico4@gmail.com',NULL),('10000004-0','Médico Genérico5','medicogenerico5@gmail.com',NULL),('10000005-9','Médico Genérico6','medicogenerico6@gmail.com',NULL),('10000006-7','Médico Genérico7','medicogenerico7@gmail.com',NULL),('10000007-5','Médico Genérico8','medicogenerico8@gmail.com',NULL),('10000008-3','Médico Genérico9','medicogenerico9@gmail.com',NULL),('10000009-K','Médico Genérico10','medicogenerico10@gmail.com',NULL),('10000010-5','Médico Genérico11','medicogenerico11@gmail.com',NULL),('10000011-3','Médico Genérico12','medicogenerico12@gmail.com',NULL),('10000012-1','Médico Genérico13','medicogenerico13@gmail.com',NULL),('10000013-K','Médico Genérico14','medicogenerico14@gmail.com',NULL),('10000014-8','Médico Genérico15','medicogenerico15@gmail.com',NULL),('10000015-6','Médico Genérico16','medicogenerico16@gmail.com',NULL),('12345678-9','Dr. Juan Pérez','juan.perez@saludusm.cl',NULL),('13579246-8','Dr. Pedro Soto','pedro.soto@saludusm.cl',NULL),('16482753-1','Dr. Felipe Muñoz','felipe.munoz@saludusm.cl',NULL),('17246813-5','Dra. Camila Fuentes','camila.fuentes@saludusm.cl',NULL),('98765432-1','Dra. María González','maria.gonzalez@saludusm.cl',NULL);
/*!40000 ALTER TABLE `medico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medico_especialidad`
--

DROP TABLE IF EXISTS `medico_especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico_especialidad` (
  `RUT_Medico` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'XXXXXXXX-X',
  `ID_Especialidad` int NOT NULL,
  PRIMARY KEY (`RUT_Medico`,`ID_Especialidad`),
  KEY `fk_Especialidad` (`ID_Especialidad`),
  CONSTRAINT `fk_Especialidad` FOREIGN KEY (`ID_Especialidad`) REFERENCES `especialidad` (`ID_Especialidad`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Medico_Especialidad` FOREIGN KEY (`RUT_Medico`) REFERENCES `medico` (`RUT_Medico`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medico_especialidad`
--

LOCK TABLES `medico_especialidad` WRITE;
/*!40000 ALTER TABLE `medico_especialidad` DISABLE KEYS */;
INSERT INTO `medico_especialidad` VALUES ('00000000-1',1),('10000000-8',1),('10000002-4',1),('10000005-9',1),('10000008-3',1),('10000010-5',1),('10000013-K',1),('10000000-8',2),('10000003-2',2),('10000005-9',2),('10000008-3',2),('10000011-3',2),('10000013-K',2),('12345678-9',2),('10000000-8',3),('10000003-2',3),('10000006-7',3),('10000008-3',3),('10000011-3',3),('10000014-8',3),('12345678-9',3),('16482753-1',3),('00000000-1',4),('10000001-6',4),('10000003-2',4),('10000006-7',4),('10000009-K',4),('10000011-3',4),('10000014-8',4),('12345678-9',4),('17246813-5',4),('10000001-6',5),('10000004-0',5),('10000006-7',5),('10000009-K',5),('10000012-1',5),('10000014-8',5),('98765432-1',5),('10000001-6',6),('10000004-0',6),('10000007-5',6),('10000009-K',6),('10000012-1',6),('10000015-6',6),('13579246-8',6),('10000002-4',7),('10000004-0',7),('10000007-5',7),('10000010-5',7),('10000012-1',7),('10000015-6',7),('13579246-8',7),('10000002-4',8),('10000005-9',8),('10000007-5',8),('10000010-5',8),('10000013-K',8),('10000015-6',8),('17246813-5',8);
/*!40000 ALTER TABLE `medico_especialidad` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ProtegerEspecialidadMedico` BEFORE DELETE ON `medico_especialidad` FOR EACH ROW BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `paciente`
--

DROP TABLE IF EXISTS `paciente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paciente` (
  `RUT_Paciente` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'XXXXXXXX-X',
  `Nombre_Paciente` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefono_Paciente` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '+56 ...',
  `Fecha-Nacimiento_Paciente` date NOT NULL COMMENT 'DD-MM-YYYY',
  `Sexo_Paciente` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'M | F | Otro',
  `ID_Prevision` int NOT NULL,
  `Email_Paciente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Contrasena_Hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RUT_Paciente`),
  UNIQUE KEY `uq_paciente_email` (`Email_Paciente`),
  KEY `ID_Prevision` (`ID_Prevision`),
  CONSTRAINT `previcion_Paciente` FOREIGN KEY (`ID_Prevision`) REFERENCES `prevision` (`ID_Prevision`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paciente`
--

LOCK TABLES `paciente` WRITE;
/*!40000 ALTER TABLE `paciente` DISABLE KEYS */;
INSERT INTO `paciente` VALUES ('00000000-0','Martina Díaz','912345670','1995-01-01','F',2,'paciente.prueba@example.test','$2y$10$kLjbFHC7SZ7JLBksG67x/..wwk0TVER5LhJxycjRbm1Ex/niirt8a'),('00000001-0','Tomás Rivas','912345671','2000-01-02','M',1,'paciente2@gmail.com','$2y$10$xfb1lfdIPImxhViFV0I1EOP7icBe4dDc7Wsfq0wudo6Qs0LtnJxYu'),('00000001-9','Paciente Genérico1','900000001','2010-01-01','M',1,'pacientegenerico1@gmail.com',NULL),('00000002-7','Paciente Genérico2','900000002','2000-01-02','F',2,'pacientegenerico2@gmail.com',NULL),('00000003-5','Paciente Genérico3','900000003','2000-01-03','Otro',3,'pacientegenerico3@gmail.com',NULL),('00000004-3','Paciente Genérico4','900000004','2000-01-04','M',1,'pacientegenerico4@gmail.com',NULL),('00000005-1','Paciente Genérico5','900000005','2000-01-05','F',2,'pacientegenerico5@gmail.com',NULL),('00000006-K','Paciente Genérico6','900000006','2000-01-06','Otro',3,'pacientegenerico6@gmail.com',NULL),('00000007-8','Paciente Genérico7','900000007','2000-01-07','M',1,'pacientegenerico7@gmail.com',NULL),('00000008-6','Paciente Genérico8',NULL,'2000-01-08','F',2,'pacientegenerico8@gmail.com',NULL),('00000009-4','Paciente Genérico9','900000009','2000-01-09','Otro',3,'pacientegenerico9@gmail.com',NULL),('00000010-8','Paciente Genérico10','900000010','2000-01-10','M',1,'pacientegenerico10@gmail.com',NULL),('00000011-6','Paciente Genérico11','900000011','2000-01-11','F',2,'pacientegenerico11@gmail.com',NULL),('00000012-4','Paciente Genérico12','900000012','2000-01-12','Otro',3,'pacientegenerico12@gmail.com',NULL),('00000013-2','Paciente Genérico13','900000013','2000-01-13','M',1,'pacientegenerico13@gmail.com',NULL),('00000014-0','Paciente Genérico14','900000014','2000-01-14','F',2,'pacientegenerico14@gmail.com',NULL),('00000015-9','Paciente Genérico15','900000015','2000-01-15','Otro',3,'pacientegenerico15@gmail.com',NULL),('00000016-7','Paciente Genérico16',NULL,'2000-01-16','M',1,'pacientegenerico16@gmail.com',NULL),('00000017-5','Paciente Genérico17','900000017','2000-01-17','F',2,'pacientegenerico17@gmail.com',NULL),('00000018-3','Paciente Genérico18','900000018','2000-01-18','Otro',3,'pacientegenerico18@gmail.com',NULL),('00000019-1','Paciente Genérico19','900000019','2000-01-19','M',1,'pacientegenerico19@gmail.com',NULL),('00000020-5','Paciente Genérico20','900000020','2000-01-20','F',2,'pacientegenerico20@gmail.com',NULL),('00000021-3','Paciente Genérico21','900000021','2000-01-21','Otro',3,'pacientegenerico21@gmail.com',NULL),('00000022-1','Paciente Genérico22','900000022','2000-01-22','M',1,'pacientegenerico22@gmail.com',NULL),('00000023-K','Paciente Genérico23','900000023','2000-01-23','F',2,'pacientegenerico23@gmail.com',NULL),('00000024-8','Paciente Genérico24',NULL,'2000-01-24','Otro',3,'pacientegenerico24@gmail.com',NULL),('00000025-6','Paciente Genérico25','900000025','2000-01-25','M',1,'pacientegenerico25@gmail.com',NULL),('00000026-4','Paciente Genérico26','900000026','2000-01-26','F',2,'pacientegenerico26@gmail.com',NULL),('00000027-2','Paciente Genérico27','900000027','2000-01-27','Otro',3,'pacientegenerico27@gmail.com',NULL),('00000028-0','Paciente Genérico28','900000028','2000-01-28','M',1,'pacientegenerico28@gmail.com',NULL),('00000029-9','Paciente Genérico29','900000029','2000-01-29','F',2,'pacientegenerico29@gmail.com',NULL),('00000030-2','Paciente Genérico30','900000030','2000-01-30','Otro',3,'pacientegenerico30@gmail.com',NULL),('00000031-0','Paciente Genérico31','900000031','2000-01-31','M',1,'pacientegenerico31@gmail.com',NULL),('00000032-9','Paciente Genérico32',NULL,'2000-02-01','F',2,'pacientegenerico32@gmail.com',NULL),('00000033-7','Paciente Genérico33','900000033','2000-02-02','Otro',3,'pacientegenerico33@gmail.com',NULL),('00000034-5','Paciente Genérico34','900000034','2000-02-03','M',1,'pacientegenerico34@gmail.com',NULL),('00000035-3','Paciente Genérico35','900000035','2000-02-04','F',2,'pacientegenerico35@gmail.com',NULL),('00000036-1','Paciente Genérico36','900000036','2000-02-05','Otro',3,'pacientegenerico36@gmail.com',NULL),('00000037-K','Paciente Genérico37','900000037','2000-02-06','M',1,'pacientegenerico37@gmail.com',NULL),('00000038-8','Paciente Genérico38','900000038','2000-02-07','F',2,'pacientegenerico38@gmail.com',NULL),('00000039-6','Paciente Genérico39','900000039','2000-02-08','Otro',3,'pacientegenerico39@gmail.com',NULL),('00000040-K','Paciente Genérico40',NULL,'2000-02-09','M',1,'pacientegenerico40@gmail.com',NULL),('10987654-3','Jorge Herrera','933445566','1958-03-02','M',3,NULL,NULL),('11223344-5','Luis Castro','966778899','1972-01-30','M',1,NULL,NULL),('15678901-K','Ana Rojas','955667788','1982-11-05','F',2,NULL,NULL),('18234567-0','Francisca Reyes','944556677','1990-09-14','F',2,NULL,NULL),('20123456-7','Carlos Silva','911223344','1995-04-12','M',1,NULL,NULL),('21098765-4','Sofía Morales','977889900','2001-12-08','Otro',3,NULL,NULL),('23456789-6','Valentina Torres','922334455','2015-06-20','F',1,NULL,NULL);
/*!40000 ALTER TABLE `paciente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prevision`
--

DROP TABLE IF EXISTS `prevision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prevision` (
  `ID_Prevision` int NOT NULL AUTO_INCREMENT,
  `Prevision_Paciente` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Fonasa | Isapre | Particular',
  PRIMARY KEY (`ID_Prevision`),
  UNIQUE KEY `uq_Prevision` (`Prevision_Paciente`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prevision`
--

LOCK TABLES `prevision` WRITE;
/*!40000 ALTER TABLE `prevision` DISABLE KEYS */;
INSERT INTO `prevision` VALUES (1,'Fonasa'),(2,'Isapre'),(3,'Particular');
/*!40000 ALTER TABLE `prevision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receta`
--

DROP TABLE IF EXISTS `receta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `receta` (
  `ID_Receta` int NOT NULL AUTO_INCREMENT,
  `Medicamento_Receta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Dosis_Receta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Dias-Tratamiento_Receta` int NOT NULL,
  `Nro_Atencion` int NOT NULL,
  PRIMARY KEY (`ID_Receta`),
  KEY `fkNroAtencion_receta` (`Nro_Atencion`) USING BTREE,
  CONSTRAINT `fkAtencion_Receta` FOREIGN KEY (`Nro_Atencion`) REFERENCES `atencion` (`Nro_Atencion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receta`
--

LOCK TABLES `receta` WRITE;
/*!40000 ALTER TABLE `receta` DISABLE KEYS */;
INSERT INTO `receta` VALUES (1,'Paracetamol 500 mg','1 comprimido cada 8 horas',5,1),(2,'Losartán 50 mg','1 comprimido cada 24 horas',30,1),(3,'Amoxicilina 500 mg','1 cápsula cada 8 horas',7,2),(4,'Ibuprofeno 400 mg','1 comprimido cada 8 horas',5,2),(5,'Hidrocortisona crema 1%','Aplicar 2 veces al día',10,3),(6,'Losartán 50 mg','1 comprimido cada 24 horas',30,4),(7,'Paracetamol jarabe 120 mg/5 ml','5 ml cada 8 horas',5,5),(8,'Salbutamol inhalador','2 inhalaciones cada 6 horas',7,5),(9,'Sumatriptán 50 mg','1 comprimido al inicio de la crisis',10,6),(10,'Omeprazol 20 mg','1 cápsula cada 24 horas en ayunas',30,7),(11,'Sertralina 50 mg','1 comprimido cada 24 horas',60,8),(12,'Ibuprofeno 600 mg','1 comprimido cada 8 horas',7,9),(13,'Metformina 850 mg','1 comprimido cada 12 horas',90,10),(14,'Losartán 50 mg','1 comprimido cada 24 horas',30,10),(15,'Paracetamol 500 mg','1 comprimido cada 8 horas',5,11),(16,'Metformina 850 mg','1 comprimido cada 12 horas',90,12),(17,'Diclofenaco 50 mg','1 comprimido cada 12 horas',7,13),(18,'Paracetamol 500 mg','1 comprimido cada 8 horas',4,14),(19,'Sertralina 50 mg','1 comprimido cada 24 horas',60,15),(20,'Sales de rehidratación oral','1 sobre después de cada deposición',3,16),(21,'Medicamento de prueba grupo 3','Pauta ficticia grupo 1',3,1),(22,'Medicamento de prueba grupo 3','Pauta ficticia grupo 1',4,2),(23,'Medicamento de prueba grupo 3','Pauta ficticia grupo 1',5,3),(24,'Medicamento de prueba grupo 3','Pauta ficticia grupo 1',3,4),(25,'Medicamento de prueba grupo 3','Pauta ficticia grupo 1',4,5),(26,'Medicamento de prueba grupo 3','Pauta ficticia grupo 2',5,6),(27,'Medicamento de prueba grupo 3','Pauta ficticia grupo 2',3,7),(28,'Medicamento de prueba grupo 3','Pauta ficticia grupo 2',4,8),(29,'Medicamento de prueba grupo 3','Pauta ficticia grupo 2',5,9),(30,'Medicamento de prueba grupo 3','Pauta ficticia grupo 2',3,10),(31,'Medicamento de prueba grupo 4','Pauta ficticia grupo 3',4,11),(32,'Medicamento de prueba grupo 4','Pauta ficticia grupo 3',5,12),(33,'Medicamento de prueba grupo 4','Pauta ficticia grupo 3',3,13),(34,'Medicamento de prueba grupo 4','Pauta ficticia grupo 3',4,14),(35,'Medicamento de prueba grupo 4','Pauta ficticia grupo 3',5,15),(36,'Medicamento de prueba grupo 4','Pauta ficticia grupo 4',3,16),(37,'Medicamento de prueba grupo 4','Pauta ficticia grupo 4',4,17),(38,'Medicamento de prueba grupo 4','Pauta ficticia grupo 4',5,18),(39,'Medicamento de prueba grupo 4','Pauta ficticia grupo 4',3,19),(40,'Medicamento de prueba grupo 4','Pauta ficticia grupo 4',4,20),(41,'Medicamento de prueba grupo 5','Pauta ficticia grupo 5',5,21),(42,'Medicamento de prueba grupo 5','Pauta ficticia grupo 5',3,22),(43,'Medicamento de prueba grupo 5','Pauta ficticia grupo 5',4,23),(44,'Medicamento de prueba grupo 5','Pauta ficticia grupo 5',5,24),(45,'Medicamento de prueba grupo 5','Pauta ficticia grupo 5',3,25),(46,'Medicamento de prueba grupo 5','Pauta ficticia grupo 6',4,26),(47,'Medicamento de prueba grupo 5','Pauta ficticia grupo 6',5,27),(48,'Medicamento de prueba grupo 5','Pauta ficticia grupo 6',3,28),(49,'Medicamento de prueba grupo 5','Pauta ficticia grupo 6',4,29),(50,'Medicamento de prueba grupo 5','Pauta ficticia grupo 6',5,30),(51,'Medicamento de prueba grupo 6','Pauta ficticia grupo 7',3,31),(52,'Medicamento de prueba grupo 6','Pauta ficticia grupo 7',4,32),(53,'Medicamento de prueba grupo 6','Pauta ficticia grupo 7',5,33),(54,'Medicamento de prueba grupo 6','Pauta ficticia grupo 7',3,34),(55,'Medicamento de prueba grupo 6','Pauta ficticia grupo 7',4,35),(56,'Medicamento de prueba grupo 6','Pauta ficticia grupo 8',5,36),(57,'Medicamento de prueba grupo 6','Pauta ficticia grupo 8',3,37),(58,'Medicamento de prueba grupo 6','Pauta ficticia grupo 8',4,38),(59,'Medicamento de prueba grupo 6','Pauta ficticia grupo 8',5,39),(60,'Medicamento de prueba grupo 6','Pauta ficticia grupo 8',3,40),(61,'Medicamento de prueba grupo 7','Pauta ficticia grupo 9',4,41),(62,'Medicamento de prueba grupo 7','Pauta ficticia grupo 9',5,42),(63,'Medicamento de prueba grupo 7','Pauta ficticia grupo 9',3,43),(64,'Medicamento de prueba grupo 7','Pauta ficticia grupo 9',4,44),(65,'Medicamento de prueba grupo 7','Pauta ficticia grupo 9',5,45),(66,'Medicamento de prueba grupo 7','Pauta ficticia grupo 10',3,46),(67,'Medicamento de prueba grupo 7','Pauta ficticia grupo 10',4,47),(68,'Medicamento de prueba grupo 7','Pauta ficticia grupo 10',5,48),(69,'Medicamento de prueba grupo 7','Pauta ficticia grupo 10',3,49),(70,'Medicamento de prueba grupo 7','Pauta ficticia grupo 10',4,50),(71,'Medicamento de prueba grupo 8','Pauta ficticia grupo 11',5,51),(72,'Medicamento de prueba grupo 8','Pauta ficticia grupo 11',3,52),(73,'Medicamento de prueba grupo 8','Pauta ficticia grupo 11',4,53),(74,'Medicamento de prueba grupo 8','Pauta ficticia grupo 11',5,54),(75,'Medicamento de prueba grupo 8','Pauta ficticia grupo 11',3,55),(76,'Medicamento de prueba grupo 8','Pauta ficticia grupo 12',4,56),(77,'Medicamento de prueba grupo 8','Pauta ficticia grupo 12',5,57),(78,'Medicamento de prueba grupo 8','Pauta ficticia grupo 12',3,58),(79,'Medicamento de prueba grupo 8','Pauta ficticia grupo 12',4,59),(80,'Medicamento de prueba grupo 8','Pauta ficticia grupo 12',5,60);
/*!40000 ALTER TABLE `receta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'salud_usm'
--

--
-- Dumping routines for database 'salud_usm'
--
/*!50003 DROP FUNCTION IF EXISTS `EdadPaciente` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `EdadPaciente`(`p_FechaNacimiento` DATE) RETURNS int
    NO SQL
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, p_FechaNacimiento, CURDATE());
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CancelarCitasVencidas` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CancelarCitasVencidas`()
    MODIFIES SQL DATA
BEGIN
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `NULLCita` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `NULLCita`(IN `p_Nro_Cita` INT)
    MODIFIES SQL DATA
BEGIN
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ValidarBloqueCita` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ValidarBloqueCita`(IN `p_Nro_Cita` INT, IN `p_RUT_Medico` VARCHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_RUT_Paciente` VARCHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_Fecha` DATE, IN `p_Hora` TIME)
    READS SQL DATA
BEGIN
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-05  1:46:01
