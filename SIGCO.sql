/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.7.24 : Database - sigco
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sigco` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `sigco`;

/*Table structure for table `acciones` */

DROP TABLE IF EXISTS `acciones`;

CREATE TABLE `acciones` (
  `ID_ACCIONES` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_ACCION` varchar(45) NOT NULL,
  PRIMARY KEY (`ID_ACCIONES`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `acciones` */

insert  into `acciones`(`ID_ACCIONES`,`NOMBRE_ACCION`) values (1,'INSERT'),(2,'SELECT'),(3,'UPDATE'),(4,'DELETE');

/*Table structure for table `cita` */

DROP TABLE IF EXISTS `cita`;

CREATE TABLE `cita` (
  `ID_CITA` int(11) NOT NULL AUTO_INCREMENT,
  `PACIENTE_ID_PACIENTE` int(11) NOT NULL,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) NOT NULL,
  `FECHA_HORA` datetime NOT NULL,
  `HORARIO_DISPONIBILIDAD_ID_HORARIO` int(11) NOT NULL,
  `FECHA_CREACION` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ESTADO_CITA_ID` int(11) DEFAULT '1',
  `FECHA_CANCELACION` datetime DEFAULT NULL,
  `MOTIVO_CANCELACION` varchar(255) DEFAULT NULL,
  `OBSERVACIONES_DOCTOR` text,
  `RECOMENDACIONES` text,
  `FECHA_ATENCION` datetime DEFAULT NULL,
  `MOTIVO` varchar(200) NOT NULL,
  PRIMARY KEY (`ID_CITA`),
  KEY `PACIENTE_ID_PACIENTE` (`PACIENTE_ID_PACIENTE`),
  KEY `ODONTOLOGO_ID_ODONTOLOGO` (`ODONTOLOGO_ID_ODONTOLOGO`),
  KEY `HORARIO_DISPONIBILIDAD_ID_HORARIO` (`HORARIO_DISPONIBILIDAD_ID_HORARIO`),
  KEY `fk_cita_estado_cita` (`ESTADO_CITA_ID`),
  CONSTRAINT `fk_cita_estado` FOREIGN KEY (`ESTADO_CITA_ID`) REFERENCES `estado_cita` (`ID_ESTADO`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cita_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cita_paciente` FOREIGN KEY (`PACIENTE_ID_PACIENTE`) REFERENCES `paciente` (`ID_PACIENTE`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1041 DEFAULT CHARSET=utf8;

/*Data for the table `cita` */

insert  into `cita`(`ID_CITA`,`PACIENTE_ID_PACIENTE`,`ODONTOLOGO_ID_ODONTOLOGO`,`FECHA_HORA`,`HORARIO_DISPONIBILIDAD_ID_HORARIO`,`FECHA_CREACION`,`ESTADO_CITA_ID`,`FECHA_CANCELACION`,`MOTIVO_CANCELACION`,`OBSERVACIONES_DOCTOR`,`RECOMENDACIONES`,`FECHA_ATENCION`,`MOTIVO`) values (2,2,2,'2026-03-02 15:20:00',0,'2026-06-19 09:56:53',4,NULL,NULL,NULL,NULL,NULL,''),(14,9,12,'2026-06-17 08:00:00',9,'2026-06-19 09:56:53',2,'2026-06-19 10:37:04','Cambio de planes',NULL,'Prueba de recomendacion desde SQL',NULL,''),(15,9,12,'2026-06-20 10:00:00',1,'2026-06-19 09:56:53',2,NULL,NULL,'Paciente presenta tejidos periodontales sanos. Se realiza valoración completa sin hallazgos de caries activas.','Evitar el consumo de bebidas oscuras las próximas 24 horas y usar seda dental a diario.',NULL,''),(16,9,12,'2026-06-25 08:00:00',201,'2026-06-19 09:56:53',3,'2026-06-23 18:06:50','Cancelada por administrador',NULL,NULL,NULL,''),(17,9,12,'2026-06-25 11:00:00',203,'2026-06-19 09:56:53',2,'2026-06-21 15:47:12','Problemas de horario',NULL,NULL,NULL,''),(26,9,12,'2026-06-19 09:00:00',1,'2026-06-19 09:56:53',2,'2026-06-21 15:47:33','No puedo asistir',NULL,NULL,NULL,''),(27,9,12,'2026-06-19 14:00:00',1,'2026-06-19 09:56:53',2,'2026-06-21 15:56:10','Problemas de horario - Obs: Perdon',NULL,NULL,NULL,''),(28,9,12,'2026-06-15 08:00:00',1,'2026-06-19 09:56:53',2,NULL,NULL,'Encías sanas y estables.','Usar seda dental diariamente y regresar en 6 meses.','2026-06-15 08:30:00',''),(29,9,12,'2026-06-10 10:30:00',1,'2026-06-19 09:56:53',2,NULL,NULL,'Procedimiento terminado exitosamente.','','2026-06-10 10:30:00',''),(30,9,12,'2026-06-12 11:00:00',1,'2026-06-19 09:56:53',2,NULL,NULL,NULL,NULL,NULL,''),(31,9,12,'2026-06-08 16:00:00',1,'2026-06-19 09:56:53',2,NULL,NULL,NULL,NULL,'2026-06-05 09:00:00',''),(32,9,12,'2026-05-20 09:00:00',1,'2026-06-19 09:56:53',2,NULL,NULL,NULL,NULL,NULL,''),(33,9,12,'2026-05-15 15:00:00',1,'2026-06-19 09:56:53',2,NULL,NULL,NULL,NULL,NULL,''),(34,9,12,'2026-06-24 11:00:00',202,'2026-06-21 21:52:56',4,NULL,NULL,NULL,NULL,NULL,''),(35,9,12,'2026-06-25 14:00:00',204,'2026-06-22 08:49:20',3,'2026-06-22 08:50:20','No puedo asistir - Obs: HOLIS',NULL,NULL,NULL,''),(36,9,12,'2026-06-25 11:00:00',203,'2026-06-22 08:53:05',3,'2026-06-22 08:53:10','No puedo asistir',NULL,NULL,NULL,''),(37,9,12,'2026-06-25 14:00:00',204,'2026-06-22 08:55:20',3,'2026-06-22 08:55:32','Problemas de horario - Observación: ksdsd',NULL,NULL,NULL,''),(38,9,12,'2026-06-25 08:00:00',201,'2026-06-22 12:17:45',3,'2026-06-23 16:46:15','Cancelada por administrador',NULL,NULL,NULL,''),(999,9,2,'2026-06-25 10:00:00',999,'2026-06-22 16:46:35',2,NULL,NULL,NULL,NULL,NULL,''),(1000,8,12,'2026-06-23 10:00:00',1000,'2026-06-23 21:47:43',3,'2026-06-23 16:52:11','Cancelada por administrador',NULL,NULL,NULL,''),(1001,9,12,'2026-06-24 12:00:00',1001,'2026-06-23 21:50:47',4,NULL,NULL,NULL,NULL,NULL,''),(1002,2,12,'2026-06-25 08:00:00',1002,'2026-06-23 22:05:48',4,NULL,NULL,NULL,NULL,NULL,''),(1003,9,12,'2026-06-25 08:00:00',1003,'2026-06-23 22:14:53',4,NULL,NULL,NULL,NULL,NULL,''),(1004,9,12,'2026-06-23 18:00:00',1004,'2026-06-23 23:17:09',4,NULL,NULL,NULL,NULL,NULL,''),(1007,12,12,'2026-06-26 17:43:13',1,'2026-06-26 17:43:13',2,NULL,NULL,NULL,NULL,'2026-06-26 17:43:13','Valoración inicial de prueba'),(1008,12,12,'2026-06-26 17:47:02',1,'2026-06-26 17:47:02',2,NULL,NULL,NULL,NULL,'2026-06-26 17:47:02','Consulta de valoración y tratamiento'),(1009,17,8,'2026-07-02 12:30:00',1005,'2026-07-02 12:20:30',3,'2026-07-02 12:22:19','Problemas de horario',NULL,NULL,NULL,'PROTESIS PARCIAL REMOVIBLE'),(1010,17,6,'2026-07-02 12:45:00',1006,'2026-07-02 12:26:38',3,'2026-07-02 12:39:05','Problemas personales',NULL,NULL,NULL,'PROTESIS PARCIAL REMOVIBLE'),(1011,17,6,'2026-07-02 12:45:00',1006,'2026-07-02 13:27:09',4,NULL,NULL,NULL,NULL,NULL,'PROTESIS TOTAL SUPERIOR'),(1012,17,6,'2026-07-04 10:00:00',1007,'2026-07-02 13:28:21',4,NULL,NULL,NULL,NULL,NULL,'LIMPIEZA PROFUNDA (DETARTRAJE) | Reprogramada: El paciente solicitó cambiar el horario de atención médica.'),(1013,18,8,'2026-07-02 17:30:00',1005,'2026-07-02 17:09:06',3,'2026-07-02 17:29:11','No puedo asistir',NULL,NULL,NULL,'Reprogramación solicitada por el usuario.'),(1014,18,12,'2026-07-02 18:10:00',1009,'2026-07-02 18:08:46',4,NULL,NULL,NULL,NULL,NULL,'LIMPIEZA PROFUNDA (DETARTRAJE)'),(1015,18,6,'2026-07-02 18:40:00',1010,'2026-07-02 18:35:00',4,NULL,NULL,NULL,NULL,NULL,'RESINA DE FOTOCURADO (CALZA)'),(1016,18,6,'2026-07-02 18:44:00',1011,'2026-07-02 18:41:16',3,'2026-07-02 18:41:42','No puedo asistir',NULL,NULL,NULL,'LIMPIEZA PROFUNDA (DETARTRAJE)'),(1017,18,6,'2026-07-03 21:40:00',1012,'2026-07-02 21:39:07',3,'2026-07-03 12:30:55','No puedo asistir',NULL,NULL,NULL,'LIMPIEZA PROFUNDA (DETARTRAJE)'),(1018,18,6,'2026-07-03 14:00:00',1014,'2026-07-03 11:27:07',2,NULL,NULL,'hola ivan',NULL,'2026-07-03 11:28:42','CONTROL MENSUAL DE ORTODONCIA'),(1019,9,6,'2026-07-06 02:30:00',1015,'2026-07-03 13:19:03',3,'2026-07-03 13:22:04','No puedo asistir',NULL,NULL,NULL,'Reprogramación solicitada por el usuario.'),(1020,18,6,'2026-07-05 19:40:00',1017,'2026-07-05 19:34:49',2,NULL,NULL,'',NULL,'2026-07-05 19:35:58','VALORACION Y DIAGNOSTICO INICIAL'),(1021,18,6,'2026-07-05 21:14:00',1019,'2026-07-05 20:14:49',2,NULL,NULL,'',NULL,'2026-07-05 20:16:58','RESINA DE FOTOCURADO (CALZA)'),(1022,18,6,'2026-07-05 23:14:00',1019,'2026-07-05 20:29:02',2,NULL,NULL,'',NULL,'2026-07-05 20:33:30','VALORACION Y DIAGNOSTICO INICIAL'),(1023,18,8,'2026-07-05 21:30:00',1020,'2026-07-05 21:24:48',2,NULL,NULL,'',NULL,'2026-07-05 22:45:06','CONTROL MENSUAL DE ORTODONCIA'),(1024,18,6,'2026-07-06 07:30:00',1015,'2026-07-05 23:00:56',2,NULL,NULL,'Maicol | Pieza 10 | ','UwU','2026-07-06 09:38:39','Resina de fotocurado (calza)'),(1025,18,8,'2026-07-06 09:15:00',1021,'2026-07-06 09:04:53',2,NULL,NULL,'Pieza 32 | ',NULL,'2026-07-06 09:07:06','Control mensual de ortodoncia'),(1026,18,6,'2026-07-06 09:30:00',1015,'2026-07-06 09:23:44',2,NULL,NULL,'Pieza 31 | ',NULL,'2026-07-06 09:26:12','Resina de fotocurado (calza)'),(1027,18,8,'2026-07-06 10:15:00',1021,'2026-07-06 10:00:32',2,NULL,NULL,'Prueba 1 | Pieza 7 | ','No comer frio','2026-07-06 10:03:23','Exodoncia simple (extraccion)'),(1028,18,8,'2026-07-06 11:15:00',1021,'2026-07-06 10:13:00',2,NULL,NULL,'Prueba 2 | Piezas: 2, 3, 4, 5 | ','Usar hilo dental','2026-07-06 10:14:23','Protesis parcial removible'),(1029,18,6,'2026-07-06 11:30:00',1015,'2026-07-06 11:11:34',2,NULL,NULL,'prueb 3 | Pieza 5 | Pendiente para despues','no comidas duras','2026-07-06 11:13:14','Limpieza profunda (detartraje)'),(1030,18,8,'2026-07-07 07:00:00',1022,'2026-07-06 16:24:29',3,'2026-07-07 11:38:54','Cancelada por administrador',NULL,NULL,NULL,'Exodoncia simple (extraccion)'),(1031,9,12,'2026-08-15 08:00:00',0,'2026-07-07 12:07:50',4,NULL,NULL,NULL,NULL,NULL,'Revisión General'),(1032,1,6,'2026-08-15 09:30:00',0,'2026-07-07 12:07:50',4,NULL,NULL,NULL,NULL,NULL,'Limpieza Profunda'),(1033,9,12,'2026-08-15 08:00:00',0,'2026-07-07 12:09:43',4,NULL,NULL,NULL,NULL,NULL,'Revisi?n General'),(1034,1,6,'2026-08-15 09:30:00',0,'2026-07-07 12:09:43',4,NULL,NULL,NULL,NULL,NULL,'Limpieza Profunda'),(1035,22,15,'2026-07-16 13:00:00',1027,'2026-07-16 10:41:56',4,NULL,NULL,'blanca miento general','cita periodicas',NULL,'Montaje de brakets metalicos (cuota inicial)'),(1036,22,15,'2026-07-22 11:00:00',1030,'2026-07-22 07:20:25',2,NULL,NULL,'Pieza 1 | ',NULL,'2026-07-22 07:46:29','Valoracion y diagnostico inicial'),(1038,31,18,'2026-11-13 12:00:00',1032,'2026-11-13 09:00:06',3,'2026-11-13 09:05:08','Problemas de horario',NULL,NULL,NULL,'Endodoncia multirradicular (molares) | Reprogramada: El paciente solicitó cambiar el horario de atención médica.'),(1039,31,18,'2026-11-13 10:00:00',1032,'2026-11-13 09:06:30',2,NULL,NULL,'asdsadasdsad','asdsadsadsadsad','2026-11-13 09:15:32','Blanqueamiento dental'),(1040,1,18,'2026-11-19 08:00:00',1034,'2026-11-19 07:06:04',2,NULL,NULL,NULL,NULL,'2026-11-19 07:12:38','Control mensual de ortodoncia');

/*Table structure for table `cita_has_procedimiento` */

DROP TABLE IF EXISTS `cita_has_procedimiento`;

CREATE TABLE `cita_has_procedimiento` (
  `ID_CITA_HAS_PROCEDIMIENTO` int(11) NOT NULL AUTO_INCREMENT,
  `CITA_ID_CITA` int(11) NOT NULL,
  `PROCEDIMIENTO_ID_PROCEDIMIENTO` int(11) NOT NULL,
  PRIMARY KEY (`ID_CITA_HAS_PROCEDIMIENTO`),
  KEY `FK_CITA_HP` (`CITA_ID_CITA`),
  KEY `FK_PROCEDIMIENTO_HP` (`PROCEDIMIENTO_ID_PROCEDIMIENTO`),
  CONSTRAINT `FK_CITA_HP` FOREIGN KEY (`CITA_ID_CITA`) REFERENCES `cita` (`ID_CITA`),
  CONSTRAINT `FK_PROCEDIMIENTO_HP` FOREIGN KEY (`PROCEDIMIENTO_ID_PROCEDIMIENTO`) REFERENCES `procedimientos` (`ID_PROCEDIMIENTO`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;

/*Data for the table `cita_has_procedimiento` */

insert  into `cita_has_procedimiento`(`ID_CITA_HAS_PROCEDIMIENTO`,`CITA_ID_CITA`,`PROCEDIMIENTO_ID_PROCEDIMIENTO`) values (5,14,1),(6,15,1),(7,16,1),(8,17,1),(9,26,1),(10,27,1),(11,28,1),(12,29,1),(13,30,1),(14,31,1),(15,32,1),(16,33,1),(20,33,1),(21,34,2),(22,35,3),(23,36,8),(24,37,10),(25,38,9),(26,1009,7),(27,1010,7),(28,1011,5),(29,1012,2),(30,1013,1),(31,1014,2),(32,1015,3),(33,1016,2),(34,1017,2),(35,1019,1),(36,1020,1),(37,1021,3),(38,1022,1),(39,1023,9),(40,1024,3),(41,1025,9),(42,1026,3),(43,1027,4),(44,1028,7),(45,1029,2),(46,1030,4),(47,1035,8),(48,1036,1),(51,1031,1),(52,1032,2),(53,1033,1),(54,1034,2),(55,1007,1),(56,1008,1),(57,999,1),(58,1000,1),(59,1001,1),(60,1002,1),(61,1003,1),(62,1004,1),(63,2,1),(64,1038,11),(65,1039,102),(66,1040,9);

/*Table structure for table `condicion_medica` */

DROP TABLE IF EXISTS `condicion_medica`;

CREATE TABLE `condicion_medica` (
  `ID_CONDICION_MEDICA` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_CONDICION` varchar(100) NOT NULL,
  `TIPO` enum('ENFERMEDAD','ALERGIA') NOT NULL,
  `DESCRIPCION` varchar(200) DEFAULT NULL,
  `ESTADO` enum('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  PRIMARY KEY (`ID_CONDICION_MEDICA`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `condicion_medica` */

insert  into `condicion_medica`(`ID_CONDICION_MEDICA`,`NOMBRE_CONDICION`,`TIPO`,`DESCRIPCION`,`ESTADO`) values (1,'DIABETES','ENFERMEDAD','TRANSTORNO METABOLICO QUE AFECTA LOS NIVELES DE GLUCOSA EN LA SANGRE','ACTIVA'),(2,'HIPERTENSION','ENFERMEDAD','PRESION ARTERIAL ELEVADA QUE PUEDE GENERAR RIESGOS CARDIOVASCULARES','ACTIVA'),(3,'PENICILINA','ALERGIA','REACCION ALERGICA A ANTIBIOTICOS DERIVADOS DE LA PENICILINA','ACTIVA'),(4,'LATEX','ALERGIA','REACCION ALERGICA AL CONTACTO CON MATERIALES DE LATEX','INACTIVA'),(6,'cancer','ENFERMEDAD','cancer cerca de la area dental','INACTIVA');

/*Table structure for table `configuracion_comisiones` */

DROP TABLE IF EXISTS `configuracion_comisiones`;

CREATE TABLE `configuracion_comisiones` (
  `ID_CONFIG` int(11) NOT NULL AUTO_INCREMENT,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) NOT NULL,
  `PORCENTAJE` decimal(5,2) NOT NULL DEFAULT '40.00',
  PRIMARY KEY (`ID_CONFIG`),
  KEY `ODONTOLOGO_ID_ODONTOLOGO` (`ODONTOLOGO_ID_ODONTOLOGO`),
  CONSTRAINT `fk_comisiones_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `configuracion_comisiones` */

insert  into `configuracion_comisiones`(`ID_CONFIG`,`ODONTOLOGO_ID_ODONTOLOGO`,`PORCENTAJE`) values (1,12,50.00),(2,6,40.00),(3,8,40.00),(4,15,30.00),(5,18,30.00);

/*Table structure for table `consultorio` */

DROP TABLE IF EXISTS `consultorio`;

CREATE TABLE `consultorio` (
  `ID_CONSULTORIO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE` varchar(100) NOT NULL,
  `UBICACION` varchar(200) DEFAULT NULL,
  `DESCRIPCION` text,
  `ESTADO` enum('DISPONIBLE','ASIGNADO','MANTENIMIENTO') DEFAULT 'DISPONIBLE',
  PRIMARY KEY (`ID_CONSULTORIO`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

/*Data for the table `consultorio` */

insert  into `consultorio`(`ID_CONSULTORIO`,`NOMBRE`,`UBICACION`,`DESCRIPCION`,`ESTADO`) values (4,'Consultorio 4','Piso 2','Consultorio de endodoncia','DISPONIBLE'),(5,'Consultorio 100012001','Piso 3','Consultorio de periodoncia','DISPONIBLE'),(6,'Consultorio 6','Piso 3','Consultorio de odontopediatría','ASIGNADO'),(8,'Consultorio 7','Piso 1','','ASIGNADO'),(9,'Consultorio 206','Piso 7','Muestra','ASIGNADO'),(10,'Consultorio 777','Piso 1','Ortodoncia','ASIGNADO'),(11,'Consultorio 178','Piso 1','Consulta general','ASIGNADO'),(12,'Consultio 67','Piso 40','Consultorio prueba','ASIGNADO');

/*Table structure for table `contacto_landing` */

DROP TABLE IF EXISTS `contacto_landing`;

CREATE TABLE `contacto_landing` (
  `ID_CONTACTO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_COMPLETO` varchar(120) NOT NULL,
  `CORREO` varchar(150) NOT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `SERVICIO_INTERES` varchar(100) DEFAULT NULL,
  `MENSAJE` text NOT NULL,
  `FECHA_REGISTRO` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ESTADO` enum('PENDIENTE','CONTACTADO','DESCARTADO') NOT NULL DEFAULT 'PENDIENTE',
  `OBSERVACIONES` varchar(255) DEFAULT NULL,
  `USUARIOS_ID_USUARIOS` int(11) DEFAULT NULL COMMENT 'Administrador que gestion├│ el prospecto',
  `PROCEDIMIENTOS_ID_PROCEDIMIENTO` int(11) DEFAULT NULL COMMENT 'Procedimiento del cat├ílogo por el que consult├│',
  PRIMARY KEY (`ID_CONTACTO`),
  KEY `fk_contacto_usuarios_idx` (`USUARIOS_ID_USUARIOS`),
  KEY `fk_contacto_procedimientos_idx` (`PROCEDIMIENTOS_ID_PROCEDIMIENTO`),
  CONSTRAINT `fk_contacto_procedimientos` FOREIGN KEY (`PROCEDIMIENTOS_ID_PROCEDIMIENTO`) REFERENCES `procedimientos` (`ID_PROCEDIMIENTO`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_contacto_usuarios` FOREIGN KEY (`USUARIOS_ID_USUARIOS`) REFERENCES `usuarios` (`ID_USUARIOS`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/*Data for the table `contacto_landing` */

insert  into `contacto_landing`(`ID_CONTACTO`,`NOMBRE_COMPLETO`,`CORREO`,`TELEFONO`,`SERVICIO_INTERES`,`MENSAJE`,`FECHA_REGISTRO`,`ESTADO`,`OBSERVACIONES`,`USUARIOS_ID_USUARIOS`,`PROCEDIMIENTOS_ID_PROCEDIMIENTO`) values (2,'Test Prospecto María Gómez','maria.gomez.test@gmail.com','3124567890','Ortodoncia','Hola, me gustaría saber los costos del tratamiento de ortodoncia invisible y los horarios disponibles.','2026-10-12 15:00:05','PENDIENTE',NULL,NULL,9),(4,'maicol','jc2825511@gmail.com','3130264958','ortodoncia','deseo tener una sonrisa hermosa','2026-11-13 08:55:10','PENDIENTE',NULL,NULL,9);

/*Table structure for table `egresos` */

DROP TABLE IF EXISTS `egresos`;

CREATE TABLE `egresos` (
  `ID_EGRESO` int(11) NOT NULL AUTO_INCREMENT,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) NOT NULL,
  `MES_LIQUIDADO` varchar(20) DEFAULT NULL,
  `MONTO_TOTAL_PRODUCCION` decimal(15,2) DEFAULT NULL,
  `PORCENTAJE_APLICADO` decimal(5,2) DEFAULT NULL,
  `MONTO_PAGADO` decimal(15,2) DEFAULT NULL,
  `FECHA_REGISTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_EGRESO`),
  KEY `ODONTOLOGO_ID_ODONTOLOGO` (`ODONTOLOGO_ID_ODONTOLOGO`),
  CONSTRAINT `fk_egresos_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

/*Data for the table `egresos` */

insert  into `egresos`(`ID_EGRESO`,`ODONTOLOGO_ID_ODONTOLOGO`,`MES_LIQUIDADO`,`MONTO_TOTAL_PRODUCCION`,`PORCENTAJE_APLICADO`,`MONTO_PAGADO`,`FECHA_REGISTRO`) values (1,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 16:51:58'),(2,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:03:37'),(3,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:07:08'),(4,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:09:39'),(5,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:11:45'),(6,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:12:17'),(7,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:13:45'),(8,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:14:05'),(9,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:14:31'),(10,12,'2026-06',190000.00,50.00,95000.00,'2026-06-28 17:20:35'),(11,12,'2026-06',190000.00,45.00,85500.00,'2026-06-28 17:24:35'),(12,12,'2026-06',190000.00,45.00,85500.00,'2026-06-29 14:20:43'),(13,12,'2026-06',190000.00,35.00,66500.00,'2026-06-30 11:45:13'),(14,6,'2026-07',290000.00,41.00,118900.00,'2026-07-03 12:27:18'),(15,6,'2026-07',510000.00,41.00,209100.00,'2026-07-05 19:42:23'),(16,6,'2026-07',510000.00,41.00,209100.00,'2026-07-05 19:45:15'),(17,6,'2026-07',510000.00,41.00,209100.00,'2026-07-05 19:45:39'),(18,6,'2026-07',510000.00,41.00,209100.00,'2026-07-05 19:46:03'),(19,6,'2026-07',510000.00,41.00,209100.00,'2026-07-05 20:01:13'),(20,8,'2026-07',2620000.00,40.00,1048000.00,'2026-07-07 12:14:38'),(21,15,'2026-07',1390000.00,30.00,417000.00,'2026-07-22 07:51:13'),(22,18,'2026-11',570000.00,30.00,171000.00,'2026-11-13 09:32:06'),(23,6,'2026-07',450000.00,40.00,180000.00,'2026-11-13 09:34:08'),(24,18,'2026-11',90000.00,30.00,27000.00,'2026-11-19 07:37:36');

/*Table structure for table `eps` */

DROP TABLE IF EXISTS `eps`;

CREATE TABLE `eps` (
  `ID_EPS` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_EPS` varchar(45) NOT NULL,
  `DIRECCION` varchar(50) NOT NULL,
  `NUMERO` varchar(45) NOT NULL,
  `ESTADO` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`ID_EPS`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `eps` */

insert  into `eps`(`ID_EPS`,`NOMBRE_EPS`,`DIRECCION`,`NUMERO`,`ESTADO`) values (1,'SURA','CARRERA 57#98-30','3128567456','INACTIVO'),(2,'SANITAS','CALLE 89#38-28','3103546987','ACTIVO'),(3,'NUEVA EPS','AV 32 #23-80','3146568956','ACTIVO'),(4,'Famisanar','','','ACTIVO'),(5,'Salud Total','','','ACTIVO');

/*Table structure for table `especialidad` */

DROP TABLE IF EXISTS `especialidad`;

CREATE TABLE `especialidad` (
  `ID_ESPECIALIDAD` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_ESPECIALIDAD` varchar(45) NOT NULL,
  `ESTADO` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`ID_ESPECIALIDAD`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

/*Data for the table `especialidad` */

insert  into `especialidad`(`ID_ESPECIALIDAD`,`NOMBRE_ESPECIALIDAD`,`ESTADO`) values (1,'Odontologia general/integral','INACTIVO'),(2,'Rehabilitación oral','ACTIVO'),(3,'Cirugía oral y maxilofacial','ACTIVO'),(4,'Odontopediatria','ACTIVO'),(5,'Estética dental','ACTIVO'),(6,'Endodoncia','ACTIVO'),(7,'Ortodoncia','ACTIVO'),(8,'Implantología','ACTIVO'),(9,'Periodoncia','ACTIVO'),(10,'Radiología oral y maxilofacial','ACTIVO'),(11,'Medicina oral','ACTIVO'),(12,'Patología oral y maxilofacial','ACTIVO'),(13,'Ortopedia maxilar','ACTIVO'),(14,'Armonizacion orofacial','ACTIVO'),(15,'Prueba','INACTIVO');

/*Table structure for table `estado_cita` */

DROP TABLE IF EXISTS `estado_cita`;

CREATE TABLE `estado_cita` (
  `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_ESTADO` varchar(50) NOT NULL,
  PRIMARY KEY (`ID_ESTADO`),
  UNIQUE KEY `NOMBRE_ESTADO` (`NOMBRE_ESTADO`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `estado_cita` */

insert  into `estado_cita`(`ID_ESTADO`,`NOMBRE_ESTADO`) values (3,'Cancelada'),(2,'Completada'),(4,'No asistió'),(1,'Pendiente');

/*Table structure for table `factura` */

DROP TABLE IF EXISTS `factura`;

CREATE TABLE `factura` (
  `ID_FACTURA` int(11) NOT NULL AUTO_INCREMENT,
  `HISTORIA_CLINICA_ID_HISTORIA_CLINICA` int(11) NOT NULL,
  `CITA_ID_CITA` int(11) NOT NULL,
  `FECHA_EMISION` datetime NOT NULL,
  `TOTAL` decimal(10,0) NOT NULL,
  `ESTADO` enum('EMITIDA','PAGADA','ANULADA') NOT NULL DEFAULT 'EMITIDA',
  `PACIENTE_ID_PACIENTE` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID_FACTURA`),
  KEY `HISTORIA_CLINICA_ID_HISTORIA_CLINICA` (`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`),
  KEY `CITA_ID_CITA` (`CITA_ID_CITA`),
  KEY `fk_factura_paciente` (`PACIENTE_ID_PACIENTE`),
  CONSTRAINT `fk_factura_paciente` FOREIGN KEY (`PACIENTE_ID_PACIENTE`) REFERENCES `paciente` (`ID_PACIENTE`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1049 DEFAULT CHARSET=utf8;

/*Data for the table `factura` */

insert  into `factura`(`ID_FACTURA`,`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`,`CITA_ID_CITA`,`FECHA_EMISION`,`TOTAL`,`ESTADO`,`PACIENTE_ID_PACIENTE`) values (1,1,0,'2026-02-28 15:36:37',80000,'PAGADA',1),(2,2,0,'2026-03-02 15:37:42',120000,'EMITIDA',2),(88,77,0,'2026-06-18 17:29:12',3000000,'EMITIDA',1),(100,100,0,'2026-06-18 17:50:31',1500000,'EMITIDA',9),(101,101,0,'2026-01-15 09:30:00',240000,'PAGADA',9),(102,102,0,'2026-03-10 14:30:00',180000,'ANULADA',9),(103,103,0,'2026-05-20 10:30:00',1100000,'EMITIDA',9),(999,999,0,'2026-06-22 16:46:35',90000,'EMITIDA',9),(1003,1003,1007,'2026-06-26 17:43:13',50000,'EMITIDA',12),(1004,1004,1008,'2026-06-26 17:47:02',140000,'EMITIDA',12),(1005,1006,1018,'2026-07-03 11:28:42',120000,'EMITIDA',18),(1006,1007,1018,'2026-07-03 11:28:42',90000,'EMITIDA',18),(1007,1008,1018,'2026-07-03 11:28:42',80000,'EMITIDA',18),(1008,1009,1020,'2026-07-05 19:35:58',100000,'EMITIDA',18),(1009,1010,1020,'2026-07-05 19:35:58',120000,'EMITIDA',18),(1010,1011,1021,'2026-07-05 20:16:58',90000,'EMITIDA',18),(1011,1012,1021,'2026-07-05 20:16:58',100000,'EMITIDA',18),(1012,1013,1022,'2026-07-05 20:33:30',50000,'EMITIDA',18),(1013,1014,1023,'2026-07-05 22:45:06',350000,'EMITIDA',18),(1014,1015,1023,'2026-07-05 22:45:06',350000,'EMITIDA',18),(1015,1016,1023,'2026-07-05 22:45:06',350000,'EMITIDA',18),(1016,1017,1023,'2026-07-05 22:45:06',350000,'EMITIDA',18),(1017,1018,1025,'2026-07-06 09:07:06',100000,'EMITIDA',18),(1018,1019,1025,'2026-07-06 09:07:06',100000,'EMITIDA',18),(1019,1020,1025,'2026-07-06 09:07:06',100000,'EMITIDA',18),(1020,1021,1025,'2026-07-06 09:07:06',100000,'EMITIDA',18),(1021,1022,1026,'2026-07-06 09:26:12',90000,'EMITIDA',18),(1022,1023,1026,'2026-07-06 09:26:12',90000,'EMITIDA',18),(1023,1024,1024,'2026-07-06 09:38:39',2000000,'EMITIDA',18),(1024,1025,1027,'2026-07-06 10:03:23',120000,'EMITIDA',18),(1025,1026,1027,'2026-07-06 10:03:23',100000,'EMITIDA',18),(1026,1027,1028,'2026-07-06 10:14:23',600000,'EMITIDA',18),(1027,1028,1029,'2026-07-06 11:13:14',120000,'EMITIDA',18),(1028,1029,1029,'2026-07-06 11:13:14',120000,'EMITIDA',18),(1029,1030,1029,'2026-07-06 11:13:14',120000,'EMITIDA',18),(1030,0,0,'2026-07-06 10:00:00',150000,'PAGADA',9),(1031,0,0,'2026-07-06 11:30:00',200000,'EMITIDA',1),(1032,1031,1036,'2026-07-22 07:46:29',80000,'EMITIDA',22),(1033,1032,1036,'2026-07-22 07:46:29',50000,'EMITIDA',22),(1034,1033,1036,'2026-07-22 07:46:29',120000,'EMITIDA',22),(1035,1034,1036,'2026-07-22 07:46:29',90000,'EMITIDA',22),(1036,1035,1036,'2026-07-22 07:46:29',950000,'EMITIDA',22),(1037,1036,1036,'2026-07-22 07:46:29',100000,'EMITIDA',22),(1038,201,0,'2026-06-25 08:30:00',80000,'EMITIDA',9),(1039,1001,0,'2026-06-26 17:40:39',50000,'EMITIDA',12),(1040,1002,0,'2026-06-26 17:40:53',50000,'EMITIDA',12),(1041,1005,0,'2026-07-02 12:29:20',950000,'EMITIDA',17),(1043,1038,1039,'2026-11-13 09:13:27',120000,'PAGADA',31),(1044,1039,1039,'2026-11-13 09:13:27',120000,'EMITIDA',31),(1045,1040,1039,'2026-11-13 09:13:27',120000,'EMITIDA',31),(1046,1041,1039,'2026-11-13 09:13:27',120000,'EMITIDA',31),(1047,1042,1039,'2026-11-13 09:13:28',90000,'EMITIDA',31),(1048,1043,1040,'2026-11-19 07:12:08',90000,'EMITIDA',1);

/*Table structure for table `fases_plan` */

DROP TABLE IF EXISTS `fases_plan`;

CREATE TABLE `fases_plan` (
  `ID_FASE` int(11) NOT NULL AUTO_INCREMENT,
  `PROCEDIMIENTOS_ID_PROCEDIMIENTO` int(11) NOT NULL,
  `ORDEN` int(11) NOT NULL,
  `DESCRIPCION_FASE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID_FASE`),
  KEY `fk_fase_procedimiento` (`PROCEDIMIENTOS_ID_PROCEDIMIENTO`),
  CONSTRAINT `fk_fases_procedimientos` FOREIGN KEY (`PROCEDIMIENTOS_ID_PROCEDIMIENTO`) REFERENCES `procedimientos` (`ID_PROCEDIMIENTO`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Data for the table `fases_plan` */

insert  into `fases_plan`(`ID_FASE`,`PROCEDIMIENTOS_ID_PROCEDIMIENTO`,`ORDEN`,`DESCRIPCION_FASE`) values (1,1,1,'Ortodoncia Fase 1: Valoración y Diagnóstico Inicial'),(2,2,2,'Ortodoncia Fase 2: Limpieza Profunda Pre-Brackets'),(3,8,3,'Ortodoncia Fase 3: Montaje de Brackets Metálicos'),(4,9,4,'Ortodoncia Fase 4: Primer Control Mensual'),(5,1,1,'Endodoncia Fase 1: Valoración y Toma de Radiografía'),(6,11,2,'Endodoncia Fase 2: Tratamiento de Conductos (Molar)'),(7,3,3,'Endodoncia Fase 3: Resina de Fotocurado (Cierre)');

/*Table structure for table `historia_clinica` */

DROP TABLE IF EXISTS `historia_clinica`;

CREATE TABLE `historia_clinica` (
  `ID_HISTORIA_CLINICA` int(11) NOT NULL AUTO_INCREMENT,
  `PACIENTE_ID_PACIENTE` int(11) NOT NULL,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) NOT NULL,
  `FECHA_REGISTRO` datetime NOT NULL,
  `MOTIVO_CONSULTA` text NOT NULL,
  `DIAGNOSTICO` text NOT NULL,
  `TRATAMIENTO` text NOT NULL,
  `OBSERVACIONES` text,
  `CITA_ID_CITA` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID_HISTORIA_CLINICA`),
  KEY `PACIENTE_ID_PACIENTE` (`PACIENTE_ID_PACIENTE`),
  KEY `ODONTOLOGO_ID_ODONTOLOGO` (`ODONTOLOGO_ID_ODONTOLOGO`),
  KEY `fk_hc_cita` (`CITA_ID_CITA`),
  CONSTRAINT `fk_hc_cita` FOREIGN KEY (`CITA_ID_CITA`) REFERENCES `cita` (`ID_CITA`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_hc_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hc_paciente` FOREIGN KEY (`PACIENTE_ID_PACIENTE`) REFERENCES `paciente` (`ID_PACIENTE`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1044 DEFAULT CHARSET=utf8;

/*Data for the table `historia_clinica` */

insert  into `historia_clinica`(`ID_HISTORIA_CLINICA`,`PACIENTE_ID_PACIENTE`,`ODONTOLOGO_ID_ODONTOLOGO`,`FECHA_REGISTRO`,`MOTIVO_CONSULTA`,`DIAGNOSTICO`,`TRATAMIENTO`,`OBSERVACIONES`,`CITA_ID_CITA`) values (1,1,12,'2026-02-28 15:25:00','VINO POR DOLOR EN UNA MUELA Y SE LE RETIRO','TENIA UNA CARIES QUE LE LLEGÓ A LA PULPA PERO AL SABER EL PRECIO PREFIRIO QUE LA SACARA','PARA EL DOLOR SE LE ENVIO IBUPROFENO Y SE LE RECOMENDO HACER BUCHES DE CALENDULA PARA LA INFLAMACION Y PARA QUE LE CICATRICE RAPIDAMENTE','VINO POR UN DOLOR DE MUELA Y SE LE RETIRO. SE USO ANESTECIA EN EL PROCEDIMIENTO',NULL),(2,2,2,'2026-03-02 15:30:06','LIMPIEZA DENTAL PREVENTIVA','ACUMULACION MODERADA DE PLACA','PROFILAXIS DENTAL','PACIENTE CON BUENA HIGIENE EN GENERAL',NULL),(77,1,12,'2026-06-18 17:29:12','Paciente asiste para control de tratamiento estético.','Falta de alineación en arco superior.','Ortodoncia estética correctiva.','Prueba del disparador automatizado de facturas.',NULL),(100,9,12,'2026-06-18 17:50:31','Control clínico de ortodoncia - Prueba General','Tratamiento activo','Ajuste general','Auditoría financiera exitosa',NULL),(101,9,12,'2026-01-15 09:00:00','Limpieza profunda inicial','Acumulación de sarro','Detartraje','Pago total recibido',NULL),(102,9,12,'2026-03-10 14:00:00','Valoración brackets','Maloclusión','Estudio','Se anula por orden médica',NULL),(103,9,12,'2026-05-20 10:00:00','Endodoncia molar','Infección pulpar','Tratamiento de conductos','Falta abonar el saldo',NULL),(201,9,12,'2026-06-25 08:30:00','Control de ortodoncia','Evolución favorable del arco superior','Ajuste de brackets metálicos','Se realiza cambio de elásticos superiores. El paciente presenta excelente higiene. Se recomienda continuar con el uso riguroso de seda dental y enjuague sin alcohol.',NULL),(999,9,2,'2026-06-22 16:46:35','Control técnico de prueba','Alineación de oclusión','Resina de fotocurado','Filtro financiero activo',NULL),(1001,12,12,'2026-06-26 17:40:39','Consulta de control','Sin caries','Valoración Inicial','Paciente no desea tratamiento adicional',NULL),(1002,12,12,'2026-06-26 17:40:53','Consulta de control','Sin caries','Valoración Inicial','Paciente no desea tratamiento adicional',NULL),(1003,12,12,'2026-06-26 17:43:13','Valoración inicial','Paciente sano','Valoración','Sin novedades',NULL),(1004,12,12,'2026-06-26 17:47:02','Valoración y Resina','Caries dental','Valoración y Tratamiento de Resina','Paciente aprueba ambos procedimientos',NULL),(1005,17,6,'2026-07-02 12:29:20','Intervención en Pieza 4','prueba 1 egr','PROTESIS TOTAL SUPERIOR',NULL,NULL),(1006,18,6,'2026-07-02 18:36:21','Intervención en Pieza 18','','LIMPIEZA PROFUNDA (DETARTRAJE)',NULL,NULL),(1007,18,6,'2026-07-02 18:36:21','Intervención en Pieza 17','','RESINA DE FOTOCURADO (CALZA)',NULL,NULL),(1008,18,6,'2026-07-03 11:28:36','Intervención en Pieza 6','hola ivan','CONTROL MENSUAL DE ORTODONCIA',NULL,NULL),(1009,18,6,'2026-07-06 19:35:54','Intervención en Pieza 5','','EXODONCIA SIMPLE (EXTRACCION)',NULL,NULL),(1010,18,6,'2026-07-06 19:35:54','Intervención en Pieza 3','','LIMPIEZA PROFUNDA (DETARTRAJE)',NULL,NULL),(1011,18,6,'2026-07-06 20:16:53','Intervención en Pieza 4','','RESINA DE FOTOCURADO (CALZA)',NULL,NULL),(1012,18,6,'2026-07-06 20:16:53','Intervención en Pieza 8','','EXODONCIA SIMPLE (EXTRACCION)',NULL,NULL),(1013,18,6,'2026-07-06 20:33:26','Intervención en Pieza 20','','VALORACION Y DIAGNOSTICO INICIAL',NULL,NULL),(1014,18,8,'2026-07-06 22:45:01','Intervención en Pieza 3','','Exodoncia quirurgica (cordales)',NULL,NULL),(1015,18,8,'2026-07-06 22:45:01','Intervención en Pieza 4','','Exodoncia quirurgica (cordales)',NULL,NULL),(1016,18,8,'2026-07-06 22:45:01','Intervención en Pieza 5','','Exodoncia quirurgica (cordales)',NULL,NULL),(1017,18,8,'2026-07-06 22:45:01','Intervención en Pieza 6','','Exodoncia quirurgica (cordales)',NULL,NULL),(1018,18,8,'2026-07-06 09:07:01','Exodoncia simple (extraccion)','Pieza 10 | ','Exodoncia simple (extraccion)',NULL,NULL),(1019,18,8,'2026-07-06 09:07:01','Exodoncia simple (extraccion)','Pieza 11 | ','Exodoncia simple (extraccion)',NULL,NULL),(1020,18,8,'2026-07-06 09:07:01','Exodoncia simple (extraccion)','Pieza 31 | ','Exodoncia simple (extraccion)',NULL,NULL),(1021,18,8,'2026-07-06 09:07:01','Exodoncia simple (extraccion)','Pieza 32 | ','Exodoncia simple (extraccion)',NULL,NULL),(1022,18,6,'2026-07-06 09:25:48','Resina de fotocurado (calza)','Pieza 25 | ','Resina de fotocurado (calza)',NULL,NULL),(1023,18,6,'2026-07-06 09:25:48','Resina de fotocurado (calza)','Pieza 31 | ','Resina de fotocurado (calza)',NULL,NULL),(1024,18,6,'2026-07-06 09:38:36','Implante dental (fase quirurgica)','Maicol | Pieza 10 | ','Implante dental (fase quirurgica)',NULL,NULL),(1025,18,8,'2026-07-06 10:03:17','Limpieza profunda (detartraje)','Prueba 1 | Pieza 10 | ','Limpieza profunda (detartraje)',NULL,NULL),(1026,18,8,'2026-07-06 10:03:17','Exodoncia simple (extraccion)','Prueba 1 | Pieza 7 | ','Exodoncia simple (extraccion)',NULL,NULL),(1027,18,8,'2026-07-06 10:14:19','Protesis parcial removible','Prueba 2 | Piezas: 2, 3, 4, 5 | ','Protesis parcial removible',NULL,1028),(1028,18,6,'2026-07-06 11:13:11','Limpieza profunda (detartraje)','prueb 3 | Pieza 2 | Pendiente para despues','Limpieza profunda (detartraje)',NULL,1029),(1029,18,6,'2026-07-06 11:13:11','Limpieza profunda (detartraje)','prueb 3 | Pieza 4 | Pendiente para despues','Limpieza profunda (detartraje)',NULL,1029),(1030,18,6,'2026-07-06 11:13:11','Limpieza profunda (detartraje)','prueb 3 | Pieza 5 | Pendiente para despues','Limpieza profunda (detartraje)',NULL,1029),(1031,22,15,'2026-07-16 10:50:04','Control mensual de ortodoncia','blanca miento general | Piezas: 4 | bdfasdfgj','Control mensual de ortodoncia',NULL,1035),(1032,22,15,'2026-07-22 07:21:42','Valoracion y diagnostico inicial','Pieza 1 | se detecto una caries','Valoracion y diagnostico inicial',NULL,1036),(1033,22,15,'2026-07-22 07:21:42','Limpieza profunda (detartraje)','Pieza 1 | se detecto una caries','Limpieza profunda (detartraje)',NULL,1036),(1034,22,15,'2026-07-22 07:46:24','Resina de fotocurado (calza)','Pieza 1 | ','Resina de fotocurado (calza)',NULL,1036),(1035,22,15,'2026-07-22 07:46:24','Protesis total superior','Piezas: 1 | ','Protesis total superior',NULL,1036),(1036,22,15,'2026-07-22 07:46:24','Exodoncia simple (extraccion)','Pieza 1 | ','Exodoncia simple (extraccion)',NULL,1036),(1038,31,18,'2026-11-13 09:13:26','Limpieza profunda (detartraje)','asdsadasdsad | Pieza 1 | ','Limpieza profunda (detartraje)',NULL,1039),(1039,31,18,'2026-11-13 09:13:26','Limpieza profunda (detartraje)','asdsadasdsad | Pieza 3 | ','Limpieza profunda (detartraje)',NULL,1039),(1040,31,18,'2026-11-13 09:13:26','Limpieza profunda (detartraje)','asdsadasdsad | Pieza 4 | ','Limpieza profunda (detartraje)',NULL,1039),(1041,31,18,'2026-11-13 09:13:26','Limpieza profunda (detartraje)','asdsadasdsad | Pieza 5 | ','Limpieza profunda (detartraje)',NULL,1039),(1042,31,18,'2026-11-13 09:13:28','Resina de fotocurado (calza)','asdsadasdsad | Pieza 2 | Prueba','Resina de fotocurado (calza)',NULL,1039),(1043,1,18,'2026-11-19 07:12:08','Resina de fotocurado (calza)','Pieza 3 | ','Resina de fotocurado (calza)',NULL,1040);

/*Table structure for table `historia_clinica_has_procedimientos` */

DROP TABLE IF EXISTS `historia_clinica_has_procedimientos`;

CREATE TABLE `historia_clinica_has_procedimientos` (
  `ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS` int(11) NOT NULL AUTO_INCREMENT,
  `HISTORIA_CLINICA_ID_HISTORIA_CLINICA` int(11) NOT NULL,
  `PROCEDIMIENTOS_ID_PROCEDIMIENTO` int(11) NOT NULL,
  `CANTIDAD` int(11) NOT NULL,
  `PRECIO_APLICADO` decimal(10,0) NOT NULL,
  `OBSERVACIONES` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS`),
  KEY `HISTORIA_CLINICA_ID_HISTORIA_CLINICA` (`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`),
  KEY `PROCEDIMIENTOS_ID_PROCEDIMIENTO` (`PROCEDIMIENTOS_ID_PROCEDIMIENTO`),
  CONSTRAINT `fk_hchp_hc` FOREIGN KEY (`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`) REFERENCES `historia_clinica` (`ID_HISTORIA_CLINICA`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hchp_proc` FOREIGN KEY (`PROCEDIMIENTOS_ID_PROCEDIMIENTO`) REFERENCES `procedimientos` (`ID_PROCEDIMIENTO`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

/*Data for the table `historia_clinica_has_procedimientos` */

insert  into `historia_clinica_has_procedimientos`(`ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS`,`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`,`PROCEDIMIENTOS_ID_PROCEDIMIENTO`,`CANTIDAD`,`PRECIO_APLICADO`,`OBSERVACIONES`) values (1,2,2,1,120000,'PROCEDIMIENTO EXITOSO'),(3,1,4,1,120000,'SIN COMPLICACIONES'),(5,77,99,2,1500000,'Inserción de prueba para auditoría de saldos'),(7,100,8,1,1500000,'Carga automática'),(8,101,2,1,120000,'Profilaxis simple'),(9,102,3,1,90000,'Resina de fotocurado'),(10,103,11,1,550000,'Tratamiento complejo molares'),(11,201,9,1,80000,'Control mensual asistido'),(12,999,3,1,90000,'Validación de aislamiento de saldos'),(14,1003,1,1,50000,'Valoración cobrada automáticamente'),(15,1004,1,1,50000,'Valoración cobrada'),(16,1004,3,1,90000,'Resina de fotocurado'),(17,1005,5,1,950000,NULL),(18,1006,2,1,120000,NULL),(19,1007,3,1,90000,NULL),(20,1008,9,1,80000,NULL),(21,1009,4,1,100000,NULL),(22,1010,2,1,120000,NULL),(23,1011,3,1,90000,NULL),(24,1012,4,1,100000,NULL),(25,1013,1,1,50000,NULL),(26,1014,100,1,350000,NULL),(27,1015,100,1,350000,NULL),(28,1016,100,1,350000,NULL),(29,1017,100,1,350000,NULL),(30,1018,4,1,100000,NULL),(31,1019,4,1,100000,NULL),(32,1020,4,1,100000,NULL),(33,1021,4,1,100000,NULL),(34,1022,3,1,90000,NULL),(35,1023,3,1,90000,NULL),(36,1024,104,1,2000000,NULL),(37,1025,2,1,120000,NULL),(38,1026,4,1,100000,NULL),(39,1027,7,1,600000,NULL),(40,1028,2,1,120000,NULL),(41,1029,2,1,120000,NULL),(42,1030,2,1,120000,NULL),(43,1031,9,1,80000,NULL),(44,1032,1,1,50000,NULL),(45,1033,2,1,120000,NULL),(46,1034,3,1,90000,NULL),(47,1035,5,1,950000,NULL),(48,1036,4,1,100000,NULL),(50,1038,2,1,120000,NULL),(51,1039,2,1,120000,NULL),(52,1040,2,1,120000,NULL),(53,1041,2,1,120000,NULL),(54,1042,3,1,90000,NULL),(55,1043,3,1,90000,'');

/*Table structure for table `historial_asignacion_consultorio` */

DROP TABLE IF EXISTS `historial_asignacion_consultorio`;

CREATE TABLE `historial_asignacion_consultorio` (
  `ID_HISTORIAL` int(11) NOT NULL AUTO_INCREMENT,
  `CONSULTORIO_ID_CONSULTORIO` int(11) NOT NULL,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) NOT NULL,
  `FECHA_ASIGNACION` datetime DEFAULT CURRENT_TIMESTAMP,
  `FECHA_DESASIGNACION` datetime DEFAULT NULL,
  `MOTIVO_CAMBIO` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID_HISTORIAL`),
  KEY `fk_historial_consultorio` (`CONSULTORIO_ID_CONSULTORIO`),
  KEY `fk_historial_odontologo` (`ODONTOLOGO_ID_ODONTOLOGO`),
  CONSTRAINT `fk_hac_consultorio` FOREIGN KEY (`CONSULTORIO_ID_CONSULTORIO`) REFERENCES `consultorio` (`ID_CONSULTORIO`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hac_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Data for the table `historial_asignacion_consultorio` */

insert  into `historial_asignacion_consultorio`(`ID_HISTORIAL`,`CONSULTORIO_ID_CONSULTORIO`,`ODONTOLOGO_ID_ODONTOLOGO`,`FECHA_ASIGNACION`,`FECHA_DESASIGNACION`,`MOTIVO_CAMBIO`) values (6,4,2,'2026-06-30 18:41:25','2026-06-30 18:41:46',''),(7,4,6,'2026-06-30 20:37:20','2026-07-05 19:29:37',''),(9,6,10,'2026-06-30 20:46:26','2026-07-05 19:27:14',''),(10,8,8,'2026-07-02 12:15:28',NULL,NULL),(12,6,2,'2026-07-05 19:28:07',NULL,NULL),(13,4,6,'2026-07-05 19:29:48','2026-07-05 19:48:05',''),(14,5,7,'2026-07-05 19:31:25','2026-11-13 08:23:49','Mantenimiento'),(15,4,6,'2026-07-05 19:50:19','2026-11-13 08:14:07','Cambio de turno'),(16,9,12,'2026-07-07 12:57:29',NULL,NULL),(17,10,14,'2026-07-16 10:22:30',NULL,NULL),(18,11,15,'2026-07-16 10:27:32',NULL,NULL);

/*Table structure for table `historial_cita` */

DROP TABLE IF EXISTS `historial_cita`;

CREATE TABLE `historial_cita` (
  `ID_HISTORIAL` int(11) NOT NULL AUTO_INCREMENT,
  `CITA_ID_CITA` int(11) NOT NULL,
  `ESTADO_CITA_ID` int(11) NOT NULL,
  `FECHA_HORA_ANTERIOR` datetime DEFAULT NULL,
  `FECHA_HORA_NUEVA` datetime DEFAULT NULL,
  `ACCION` varchar(100) NOT NULL,
  `MOTIVO` text,
  `CREADO_EL` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_HISTORIAL`),
  KEY `CITA_ID_CITA` (`CITA_ID_CITA`),
  KEY `ESTADO_CITA_ID` (`ESTADO_CITA_ID`),
  CONSTRAINT `fk_historial_cita_cita` FOREIGN KEY (`CITA_ID_CITA`) REFERENCES `cita` (`ID_CITA`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_historial_cita_estado` FOREIGN KEY (`ESTADO_CITA_ID`) REFERENCES `estado_cita` (`ID_ESTADO`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

/*Data for the table `historial_cita` */

insert  into `historial_cita`(`ID_HISTORIAL`,`CITA_ID_CITA`,`ESTADO_CITA_ID`,`FECHA_HORA_ANTERIOR`,`FECHA_HORA_NUEVA`,`ACCION`,`MOTIVO`,`CREADO_EL`) values (1,17,1,'2026-06-21 11:00:00','2026-06-25 09:00:00','REPROGRAMACION','Viaje imprevisto','2026-06-21 15:35:21'),(2,17,1,'2026-06-25 09:00:00','2026-06-25 11:00:00','REPROGRAMACION','Prueba 2','2026-06-21 15:44:12'),(3,16,3,'2026-06-25 08:00:00',NULL,'CANCELACION','Otra razón - Obs: Anime 1','2026-06-21 15:46:41'),(4,17,3,'2026-06-25 11:00:00',NULL,'CANCELACION','Problemas de horario','2026-06-21 15:47:12'),(5,26,3,'2026-06-19 09:00:00',NULL,'CANCELACION','No puedo asistir','2026-06-21 15:47:33'),(6,35,1,'2026-06-25 11:00:00','2026-06-25 14:00:00','REPROGRAMACION','PERSONALES','2026-06-22 08:49:59'),(7,38,1,'2026-06-25 14:00:00','2026-06-25 08:00:00','REPROGRAMACION','ghfghfgh','2026-06-22 12:18:03'),(8,1013,1,'2026-07-02 17:30:00','2026-07-02 17:30:00','REPROGRAMACION','Reprogramación solicitada por el usuario.','2026-07-02 17:21:43'),(9,1013,1,'2026-07-02 17:30:00','2026-07-02 18:10:00','REPROGRAMACION','Reprogramación solicitada por el usuario.','2026-07-02 17:22:13'),(10,1013,1,'2026-07-02 18:10:00','2026-07-02 17:30:00','REPROGRAMACION','Reprogramación solicitada por el usuario.','2026-07-02 17:28:09'),(11,1018,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-03 11:28:42'),(12,1019,1,'2026-07-06 14:30:00','2026-07-06 07:30:00','REPROGRAMACION','Reprogramación solicitada por el usuario.','2026-07-03 13:21:38'),(13,1020,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-05 19:35:58'),(14,1021,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-05 20:16:58'),(15,1022,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-05 20:33:30'),(16,1023,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-05 22:45:06'),(17,1025,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-06 09:07:06'),(18,1026,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-06 09:26:12'),(19,1024,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-06 09:38:39'),(20,1027,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-06 10:03:23'),(21,1028,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-06 10:14:23'),(22,1029,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-06 11:13:14'),(23,1036,2,NULL,NULL,'Completada','Cita completada desde módulo de Historial Clínico y Factura Generada','2026-07-22 07:46:29');

/*Table structure for table `historial_reprogramacion_citas` */

DROP TABLE IF EXISTS `historial_reprogramacion_citas`;

CREATE TABLE `historial_reprogramacion_citas` (
  `ID_HISTORIAL` int(11) NOT NULL AUTO_INCREMENT,
  `CITA_ID_CITA` int(11) NOT NULL,
  `FECHA_HORA_ANTERIOR` datetime DEFAULT NULL,
  `FECHA_HORA_NUEVA` datetime DEFAULT NULL,
  `MOTIVO_CAMBIO` varchar(255) DEFAULT NULL,
  `CREADO_EL` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_HISTORIAL`),
  KEY `fk_hist_reprog_cita` (`CITA_ID_CITA`),
  CONSTRAINT `fk_hist_reprog_cita` FOREIGN KEY (`CITA_ID_CITA`) REFERENCES `cita` (`ID_CITA`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `historial_reprogramacion_citas` */

/*Table structure for table `horario_disponibilidad` */

DROP TABLE IF EXISTS `horario_disponibilidad`;

CREATE TABLE `horario_disponibilidad` (
  `ID_HORARIO` int(11) NOT NULL AUTO_INCREMENT,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) NOT NULL,
  `FECHA` date NOT NULL,
  `HORA_INICIO` time NOT NULL,
  `HORA_FIN` time NOT NULL,
  `ESTADO` enum('Disponible','Ocupado','Inactivo','No utilizado') DEFAULT 'Disponible',
  `JORNADA` enum('Mañana','Tarde','Jornada completa') NOT NULL DEFAULT 'Mañana',
  `DIA` set('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `DESCANSO_INICIO` time DEFAULT NULL,
  `DESCANSO_FIN` time DEFAULT NULL,
  PRIMARY KEY (`ID_HORARIO`),
  KEY `ODONTOLOGO_ID_ODONTOLOGO` (`ODONTOLOGO_ID_ODONTOLOGO`),
  CONSTRAINT `fk_horario_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1035 DEFAULT CHARSET=utf8mb4;

/*Data for the table `horario_disponibilidad` */

insert  into `horario_disponibilidad`(`ID_HORARIO`,`ODONTOLOGO_ID_ODONTOLOGO`,`FECHA`,`HORA_INICIO`,`HORA_FIN`,`ESTADO`,`JORNADA`,`DIA`,`DESCANSO_INICIO`,`DESCANSO_FIN`) values (1,12,'2026-05-23','08:00:00','06:00:00','No utilizado','Mañana','',NULL,NULL),(2,12,'2026-06-11','08:00:00','09:00:00','No utilizado','Mañana','',NULL,NULL),(3,12,'2026-06-11','09:00:00','10:00:00','No utilizado','Mañana','',NULL,NULL),(4,12,'2026-06-11','10:00:00','11:00:00','No utilizado','Mañana','',NULL,NULL),(5,12,'2026-06-12','08:00:00','09:00:00','No utilizado','Mañana','',NULL,NULL),(6,12,'2026-06-12','09:00:00','10:00:00','No utilizado','Mañana','',NULL,NULL),(7,12,'2026-06-13','08:00:00','09:00:00','No utilizado','Mañana','',NULL,NULL),(8,12,'2026-06-14','08:00:00','09:00:00','No utilizado','Mañana','',NULL,NULL),(9,12,'2026-06-17','08:00:00','09:00:00','No utilizado','Mañana','',NULL,NULL),(10,12,'2026-06-17','09:00:00','10:00:00','No utilizado','Mañana','',NULL,NULL),(11,12,'2026-06-17','10:00:00','11:00:00','No utilizado','Mañana','',NULL,NULL),(12,12,'2026-06-18','08:00:00','09:00:00','No utilizado','Mañana','',NULL,NULL),(13,12,'2026-06-18','09:00:00','10:00:00','No utilizado','Mañana','',NULL,NULL),(201,12,'2026-06-25','08:00:00','09:00:00','No utilizado','Mañana','',NULL,NULL),(202,12,'2026-06-25','09:00:00','10:00:00','Ocupado','Mañana','',NULL,NULL),(203,12,'2026-06-25','11:00:00','12:00:00','No utilizado','Mañana','',NULL,NULL),(204,12,'2026-06-25','14:00:00','15:00:00','No utilizado','Mañana','',NULL,NULL),(205,12,'2026-06-26','10:00:00','11:00:00','No utilizado','Mañana','',NULL,NULL),(206,12,'2026-06-26','15:00:00','16:00:00','No utilizado','Mañana','',NULL,NULL),(207,12,'2026-06-29','08:30:00','09:30:00','No utilizado','Mañana','',NULL,NULL),(208,12,'2026-06-29','16:00:00','17:00:00','No utilizado','Mañana','',NULL,NULL),(999,2,'2026-06-25','10:00:00','11:00:00','Ocupado','Mañana','',NULL,NULL),(1000,12,'2026-06-23','10:00:00','11:00:00','No utilizado','Mañana','',NULL,NULL),(1001,12,'2026-06-24','12:00:00','13:00:00','Ocupado','Mañana','',NULL,NULL),(1002,12,'2026-06-25','08:00:00','09:00:00','Ocupado','Mañana','',NULL,NULL),(1003,12,'2026-06-25','08:00:00','09:00:00','Ocupado','Mañana','',NULL,NULL),(1004,12,'2026-06-23','18:00:00','19:00:00','Ocupado','Mañana','',NULL,NULL),(1005,8,'2026-07-02','17:30:00','18:00:00','No utilizado','','',NULL,NULL),(1006,6,'2026-07-02','13:00:00','19:00:00','Ocupado','','',NULL,NULL),(1007,6,'2026-07-02','13:30:00','18:27:00','Ocupado','Mañana','',NULL,NULL),(1008,6,'2026-07-02','17:30:00','18:00:00','No utilizado','','',NULL,NULL),(1009,12,'2026-07-02','18:10:00','19:10:00','Ocupado','Mañana','',NULL,NULL),(1010,6,'2026-07-02','18:40:00','18:50:00','Ocupado','Mañana','',NULL,NULL),(1011,6,'2026-07-02','18:44:00','00:00:00','Inactivo','Mañana','',NULL,NULL),(1012,6,'2026-07-03','21:40:00','22:38:00','No utilizado','Jornada completa','',NULL,NULL),(1013,6,'2026-07-03','11:30:00','18:00:00','No utilizado','','',NULL,NULL),(1014,6,'2026-07-03','14:00:00','15:00:00','Ocupado','Mañana','',NULL,NULL),(1015,6,'2026-07-06','07:30:00','17:30:00','No utilizado','Mañana','',NULL,NULL),(1016,6,'2026-07-06','19:40:00','00:00:00','No utilizado','Jornada completa','',NULL,NULL),(1017,6,'2026-07-05','19:40:00','20:00:00','Ocupado','Mañana','',NULL,NULL),(1018,6,'2026-07-06','20:15:00','20:30:00','No utilizado','Tarde','',NULL,NULL),(1019,6,'2026-07-05','20:14:00','23:48:00','No utilizado','Tarde','',NULL,NULL),(1020,8,'2026-07-05','21:30:00','23:30:00','No utilizado','Tarde','',NULL,NULL),(1021,8,'2026-07-06','09:15:00','19:15:00','No utilizado','','',NULL,NULL),(1022,8,'2026-07-07','07:00:00','17:00:00','No utilizado','Jornada completa','','12:00:00','13:00:00'),(1023,12,'2026-07-20','08:00:00','12:00:00','No utilizado','','',NULL,NULL),(1024,6,'2026-07-20','14:00:00','18:00:00','No utilizado','Tarde','',NULL,NULL),(1025,6,'2026-07-10','07:00:00','11:50:00','No utilizado','Mañana','',NULL,NULL),(1026,14,'2026-07-16','06:00:00','18:00:00','No utilizado','Jornada completa','','12:00:00','14:00:00'),(1027,15,'2026-07-16','12:00:00','14:00:00','No utilizado','Tarde','',NULL,NULL),(1028,15,'2026-07-20','17:35:00','19:35:00','No utilizado','Tarde','',NULL,NULL),(1029,15,'2026-07-20','17:40:00','20:40:00','No utilizado','Tarde','',NULL,NULL),(1030,15,'2026-07-22','06:00:00','12:00:00','No utilizado','Jornada completa','','13:00:00','17:00:00'),(1031,2,'2026-10-22','16:00:00','17:00:00','No utilizado','Tarde','',NULL,NULL),(1032,18,'2026-11-13','10:00:00','00:00:00','Ocupado','Mañana','',NULL,NULL),(1033,6,'2026-11-13','13:00:00','18:00:00','No utilizado','Tarde','',NULL,NULL),(1034,18,'2026-11-19','08:00:00','10:00:00','Ocupado','Mañana','',NULL,NULL);

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_21_000001_create_paciente_has_acudiente_table',1);

/*Table structure for table `notificaciones` */

DROP TABLE IF EXISTS `notificaciones`;

CREATE TABLE `notificaciones` (
  `ID_NOTIFICACIONES` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION` int(11) NOT NULL,
  `USUARIOS_ID_USUARIOS` int(11) NOT NULL,
  `MENSAJE` text NOT NULL,
  `FECHA_ENVIO` datetime NOT NULL,
  `ESTADO` enum('NO_LEIDA','LEIDA') NOT NULL DEFAULT 'NO_LEIDA',
  `FECHA_LECTURA` datetime DEFAULT NULL,
  PRIMARY KEY (`ID_NOTIFICACIONES`),
  KEY `TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION` (`TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION`),
  KEY `USUARIOS_ID_USUARIOS` (`USUARIOS_ID_USUARIOS`),
  CONSTRAINT `fk_notificaciones_tipo` FOREIGN KEY (`TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION`) REFERENCES `tipo_notificacion` (`ID_TIPO_NOTIFICACION`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notificaciones_usuarios` FOREIGN KEY (`USUARIOS_ID_USUARIOS`) REFERENCES `usuarios` (`ID_USUARIOS`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=413 DEFAULT CHARSET=utf8;

/*Data for the table `notificaciones` */

insert  into `notificaciones`(`ID_NOTIFICACIONES`,`TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION`,`USUARIOS_ID_USUARIOS`,`MENSAJE`,`FECHA_ENVIO`,`ESTADO`,`FECHA_LECTURA`) values (1,5,3,'PAGO REALIZADO CON EXITO POR UN VALOR DE 80.000','2026-02-28 15:40:20','LEIDA',NULL),(2,1,4,'PROCEDIMIENTO PROGRAMADO','2026-03-02 15:40:54','LEIDA',NULL),(3,1,2,'Tienes una nueva cita agendada','2026-06-17 13:53:20','LEIDA',NULL),(4,1,1,'Se le ha asignado un nuevo paciente','2026-06-17 13:53:20','LEIDA',NULL),(5,1,2,'Tienes una nueva cita agendada','2026-06-17 13:53:36','LEIDA',NULL),(6,1,1,'Se le ha asignado un nuevo paciente','2026-06-17 13:53:36','NO_LEIDA',NULL),(7,1,2,'Tienes una nueva cita agendada','2026-06-17 13:54:39','LEIDA',NULL),(8,1,1,'Se le ha asignado un nuevo paciente','2026-06-17 13:54:39','NO_LEIDA',NULL),(9,1,2,'Tienes una nueva cita agendada','2026-06-17 16:06:57','LEIDA',NULL),(10,1,1,'Se le ha asignado un nuevo paciente','2026-06-17 16:06:57','NO_LEIDA',NULL),(11,2,13,'El administrador agendó una cita para el 23/06/2026 a las 10:00 AM. Motivo: Limpieza Dental','2026-06-23 16:47:43','LEIDA',NULL),(12,2,2,'El administrador agendó una cita para el 24/06/2026 a las 12:00 PM. Motivo: Ajuste Ortodoncia','2026-06-23 16:50:47','LEIDA',NULL),(13,2,2,'El administrador reprogramó tu cita para el 24/06/2026 a las 11:00 AM. Motivo: Reprogramada desde administrador','2026-06-23 16:52:34','LEIDA',NULL),(14,2,4,'El administrador agendó una cita para el 25/06/2026 a las 08:00 AM. Motivo: VALORACION Y DIAGNOSTICO INICIAL','2026-06-23 17:05:48','LEIDA',NULL),(15,2,2,'El administrador agendó una cita para el 25/06/2026 a las 08:00 AM. Motivo: EXODONCIA SIMPLE (EXTRACCION)','2026-06-23 17:14:53','LEIDA',NULL),(16,2,2,'El administrador agendó una cita para el 23/06/2026 a las 06:00 PM. Motivo: VALORACION Y DIAGNOSTICO INICIAL','2026-06-23 18:17:09','LEIDA',NULL),(17,18,15,'El paciente Fabio Contrao se ha registrado en la plataforma.','2026-07-02 12:13:53','LEIDA',NULL),(18,1,23,'Tu cita médica ha sido agendada para el 02/07/2026 a las 12:30 PM.','2026-07-02 12:20:30','LEIDA',NULL),(19,21,21,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 12:30 PM. Procedimiento: PROTESIS PARCIAL REMOVIBLE','2026-07-02 12:20:30','LEIDA',NULL),(20,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 12:30 PM. Procedimiento: PROTESIS PARCIAL REMOVIBLE','2026-07-02 12:20:30','LEIDA',NULL),(21,7,23,'Tu cita médica del 02/07/2026 a las 12:30 PM ha sido cancelada. Motivo: Problemas de horario','2026-07-02 12:22:19','NO_LEIDA',NULL),(22,9,15,'El paciente ha cancelado su cita del 02/07/2026 a las 12:30 PM. Motivo: Problemas de horario','2026-07-02 12:22:19','LEIDA',NULL),(23,1,23,'Tu cita médica ha sido agendada para el 02/07/2026 a las 12:45 PM.','2026-07-02 12:26:38','NO_LEIDA',NULL),(24,21,14,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 12:45 PM. Procedimiento: PROTESIS PARCIAL REMOVIBLE','2026-07-02 12:26:38','LEIDA',NULL),(25,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 12:45 PM. Procedimiento: PROTESIS PARCIAL REMOVIBLE','2026-07-02 12:26:38','LEIDA',NULL),(28,7,23,'Tu cita médica del 02/07/2026 a las 12:45 PM ha sido cancelada. Motivo: Problemas personales','2026-07-02 12:39:05','NO_LEIDA',NULL),(29,9,15,'El paciente ha cancelado su cita del 02/07/2026 a las 12:45 PM. Motivo: Problemas personales','2026-07-02 12:39:05','LEIDA',NULL),(30,1,23,'Tu cita médica ha sido agendada para el 02/07/2026 a las 12:45 PM.','2026-07-02 13:27:09','NO_LEIDA',NULL),(31,21,14,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 12:45 PM. Procedimiento: PROTESIS TOTAL SUPERIOR','2026-07-02 13:27:09','LEIDA',NULL),(32,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 12:45 PM. Procedimiento: PROTESIS TOTAL SUPERIOR','2026-07-02 13:27:09','LEIDA',NULL),(33,1,23,'Tu cita médica ha sido agendada para el 02/07/2026 a las 01:30 PM.','2026-07-02 13:28:21','NO_LEIDA',NULL),(34,21,14,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 01:30 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 13:28:21','LEIDA',NULL),(35,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 01:30 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 13:28:21','LEIDA',NULL),(36,2,23,'Se reprogramó una cita para el 04/07/2026 am Saturdayam00 10:00 AM. Motivo: El paciente solicitó cambiar el horario de atención médica.','2026-07-02 13:53:31','NO_LEIDA',NULL),(37,18,15,'El paciente New 1 se ha registrado en la plataforma.','2026-07-02 16:55:21','LEIDA','2026-11-13 08:16:56'),(38,1,24,'Tu cita médica ha sido agendada para el 02/07/2026 a las 05:30 PM.','2026-07-02 17:09:06','LEIDA',NULL),(39,21,14,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 05:30 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-02 17:09:06','LEIDA',NULL),(40,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 05:30 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-02 17:09:06','LEIDA','2026-11-13 08:16:56'),(41,13,24,'Tu cita médica ha sido reprogramada para el 02/07/2026 a las 05:30 PM.','2026-07-02 17:21:43','LEIDA',NULL),(42,15,15,'El paciente ha reprogramado su cita para el 02/07/2026 a las 05:30 PM.','2026-07-02 17:21:43','LEIDA','2026-11-13 08:16:56'),(43,13,24,'Tu cita médica ha sido reprogramada para el 02/07/2026 a las 06:10 PM.','2026-07-02 17:22:13','LEIDA',NULL),(44,15,15,'El paciente ha reprogramado su cita para el 02/07/2026 a las 06:10 PM.','2026-07-02 17:22:13','LEIDA','2026-11-13 08:16:56'),(45,13,24,'Tu cita médica ha sido reprogramada para el 02/07/2026 a las 05:30 PM.','2026-07-02 17:28:09','LEIDA',NULL),(46,15,15,'El paciente ha reprogramado su cita para el 02/07/2026 a las 05:30 PM.','2026-07-02 17:28:09','LEIDA','2026-11-13 08:16:56'),(47,7,24,'Tu cita médica del 02/07/2026 a las 05:30 PM ha sido cancelada. Motivo: No puedo asistir','2026-07-02 17:29:11','LEIDA',NULL),(48,9,15,'El paciente ha cancelado su cita del 02/07/2026 a las 05:30 PM. Motivo: No puedo asistir','2026-07-02 17:29:11','LEIDA','2026-11-13 08:16:56'),(49,1,24,'Tu cita médica ha sido agendada para el 02/07/2026 a las 06:10 PM.','2026-07-02 18:08:46','LEIDA',NULL),(50,21,1,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:10 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 18:08:46','NO_LEIDA',NULL),(51,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:10 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 18:08:46','LEIDA','2026-11-13 08:16:56'),(52,1,24,'Tu cita médica ha sido agendada para el 02/07/2026 a las 06:40 PM.','2026-07-02 18:35:00','LEIDA',NULL),(53,21,14,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:40 PM. Procedimiento: RESINA DE FOTOCURADO (CALZA)','2026-07-02 18:35:00','LEIDA',NULL),(54,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:40 PM. Procedimiento: RESINA DE FOTOCURADO (CALZA)','2026-07-02 18:35:00','LEIDA','2026-11-13 08:16:56'),(55,22,28,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:40 PM. Procedimiento: RESINA DE FOTOCURADO (CALZA)','2026-07-02 18:35:00','NO_LEIDA',NULL),(62,1,24,'Tu cita médica ha sido agendada para el 02/07/2026 a las 06:44 PM.','2026-07-02 18:41:16','LEIDA',NULL),(63,21,14,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:44 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 18:41:16','LEIDA',NULL),(64,22,15,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:44 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 18:41:16','LEIDA','2026-11-13 08:16:56'),(65,22,28,'El paciente ha agendado una nueva cita médica para el 02/07/2026 a las 06:44 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 18:41:16','NO_LEIDA',NULL),(66,7,24,'Tu cita médica del 02/07/2026 a las 06:44 PM ha sido cancelada. Motivo: No puedo asistir','2026-07-02 18:41:42','LEIDA',NULL),(67,9,15,'El paciente ha cancelado su cita del 02/07/2026 a las 06:44 PM. Motivo: No puedo asistir','2026-07-02 18:41:42','LEIDA','2026-11-13 08:16:56'),(68,9,28,'El paciente ha cancelado su cita del 02/07/2026 a las 06:44 PM. Motivo: No puedo asistir','2026-07-02 18:41:42','NO_LEIDA',NULL),(69,1,24,'Tu cita médica ha sido agendada para el 03/07/2026 a las 09:40 PM.','2026-07-02 21:39:07','LEIDA',NULL),(70,21,14,'El paciente ha agendado una nueva cita médica para el 03/07/2026 a las 09:40 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 21:39:07','LEIDA',NULL),(72,22,28,'El paciente ha agendado una nueva cita médica para el 03/07/2026 a las 09:40 PM. Procedimiento: LIMPIEZA PROFUNDA (DETARTRAJE)','2026-07-02 21:39:07','NO_LEIDA',NULL),(73,2,24,'El administrador agendó una cita para el 03/07/2026 a las 02:00 PM. Motivo: CONTROL MENSUAL DE ORTODONCIA','2026-07-03 11:27:07','LEIDA',NULL),(74,1,24,'Se le ha agendado una cita médica para el 03/07/2026 a las 02:00 PM.','2026-07-03 11:27:07','LEIDA',NULL),(75,21,14,'El administrador le ha asignado una nueva cita para el 03/07/2026 a las 02:00 PM.','2026-07-03 11:27:07','LEIDA',NULL),(79,16,24,'Tus datos personales fueron actualizados con éxito.','2026-07-03 11:42:26','LEIDA',NULL),(81,18,28,'El paciente New 1 ha actualizado sus datos personales.','2026-07-03 11:42:26','NO_LEIDA',NULL),(83,20,28,'El paciente New 1 hizo un abono de $40.000,00 a la factura #1007.','2026-07-03 12:04:20','NO_LEIDA',NULL),(85,19,24,'Hemos recibido tu abono de $40.000,00 por concepto de CONTROL MENSUAL DE ORTODONCIA. ┬íGracias!','2026-07-03 12:04:20','LEIDA',NULL),(86,7,24,'Tu cita médica del 03/07/2026 a las 09:40 PM ha sido cancelada. Motivo: No puedo asistir','2026-07-03 12:30:55','LEIDA',NULL),(87,8,14,'El paciente ha cancelado su cita del 03/07/2026 a las 09:40 PM. Motivo: No puedo asistir','2026-07-03 12:30:55','LEIDA',NULL),(89,9,28,'El paciente ha cancelado su cita del 03/07/2026 a las 09:40 PM. Motivo: No puedo asistir','2026-07-03 12:30:55','NO_LEIDA',NULL),(90,20,15,'El paciente Esteban Galindo hizo un abono de $7.000,00 a la factura #103.','2026-07-03 13:12:42','LEIDA','2026-11-13 08:16:56'),(91,20,28,'El paciente Esteban Galindo hizo un abono de $7.000,00 a la factura #103.','2026-07-03 13:12:42','NO_LEIDA',NULL),(92,25,1,'El paciente Esteban Galindo ha realizado un abono de $7.000,00 a su cuenta.','2026-07-03 13:12:42','NO_LEIDA',NULL),(93,19,2,'Hemos recibido tu abono de $7.000,00 por concepto de ENDODONCIA MULTIRRADICULAR (MOLARES). ┬íGracias!','2026-07-03 13:12:42','NO_LEIDA',NULL),(94,20,15,'El paciente Esteban Galindo hizo un abono de $69.300,00 a la factura #103.','2026-07-03 13:14:27','LEIDA','2026-11-13 08:16:56'),(95,20,28,'El paciente Esteban Galindo hizo un abono de $69.300,00 a la factura #103.','2026-07-03 13:14:27','NO_LEIDA',NULL),(96,25,1,'El paciente Esteban Galindo ha realizado un abono de $69.300,00 a su cuenta.','2026-07-03 13:14:27','NO_LEIDA',NULL),(97,19,2,'Hemos recibido tu abono de $69.300,00 por concepto de ENDODONCIA MULTIRRADICULAR (MOLARES). ┬íGracias!','2026-07-03 13:14:27','NO_LEIDA',NULL),(98,23,15,'El paciente Esteban Galindo ha cancelado la totalidad de la factura #103 por $623.700,00.','2026-07-03 13:14:58','LEIDA','2026-11-13 08:16:56'),(99,23,28,'El paciente Esteban Galindo ha cancelado la totalidad de la factura #103 por $623.700,00.','2026-07-03 13:14:58','NO_LEIDA',NULL),(100,24,1,'El paciente Esteban Galindo ha pagado su tratamiento. Tu producción ha sido actualizada.','2026-07-03 13:14:58','NO_LEIDA',NULL),(101,5,2,'Hemos recibido tu pago total de $623.700,00 por concepto de ENDODONCIA MULTIRRADICULAR (MOLARES). ┬íGracias!','2026-07-03 13:14:58','LEIDA',NULL),(102,1,2,'Tu cita médica ha sido agendada para el 06/07/2026 a las 02:30 PM.','2026-07-03 13:19:03','NO_LEIDA',NULL),(103,21,14,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 02:30 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-03 13:19:03','NO_LEIDA',NULL),(104,22,15,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 02:30 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-03 13:19:03','LEIDA','2026-11-13 08:16:56'),(105,22,28,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 02:30 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-03 13:19:03','NO_LEIDA',NULL),(106,13,2,'Tu cita médica ha sido reprogramada para el 06/07/2026 a las 07:30 AM.','2026-07-03 13:21:38','NO_LEIDA',NULL),(107,14,14,'El paciente ha reprogramado su cita para el 06/07/2026 a las 07:30 AM.','2026-07-03 13:21:38','NO_LEIDA',NULL),(108,15,15,'El paciente ha reprogramado su cita para el 06/07/2026 a las 07:30 AM.','2026-07-03 13:21:38','LEIDA','2026-11-13 08:16:56'),(109,15,28,'El paciente ha reprogramado su cita para el 06/07/2026 a las 07:30 AM.','2026-07-03 13:21:38','NO_LEIDA',NULL),(110,7,2,'Tu cita médica del 06/07/2026 a las 07:30 AM ha sido cancelada. Motivo: No puedo asistir','2026-07-03 13:22:04','NO_LEIDA',NULL),(111,8,14,'El paciente ha cancelado su cita del 06/07/2026 a las 07:30 AM. Motivo: No puedo asistir','2026-07-03 13:22:04','NO_LEIDA',NULL),(112,9,15,'El paciente ha cancelado su cita del 06/07/2026 a las 07:30 AM. Motivo: No puedo asistir','2026-07-03 13:22:04','LEIDA','2026-11-13 08:16:56'),(113,9,28,'El paciente ha cancelado su cita del 06/07/2026 a las 07:30 AM. Motivo: No puedo asistir','2026-07-03 13:22:04','NO_LEIDA',NULL),(114,18,15,'Tus datos personales fueron actualizados con éxito.','2026-07-03 13:24:09','LEIDA','2026-11-13 08:16:56'),(115,17,14,'Tus datos personales fueron actualizados con éxito.','2026-07-03 13:24:25','NO_LEIDA',NULL),(116,18,15,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-03 13:24:25','LEIDA','2026-11-13 08:16:56'),(117,18,28,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-03 13:24:25','NO_LEIDA',NULL),(118,16,2,'Tus datos personales fueron actualizados con éxito.','2026-07-03 13:26:36','NO_LEIDA',NULL),(119,18,15,'El paciente Esteban Galinda ha actualizado sus datos personales.','2026-07-03 13:26:36','LEIDA','2026-11-13 08:16:56'),(120,18,28,'El paciente Esteban Galinda ha actualizado sus datos personales.','2026-07-03 13:26:36','NO_LEIDA',NULL),(122,16,24,'Tus datos personales fueron actualizados con éxito.','2026-07-05 19:34:26','LEIDA',NULL),(123,18,15,'El paciente New 2 ha actualizado sus datos personales.','2026-07-05 19:34:26','LEIDA','2026-11-13 08:16:56'),(124,18,28,'El paciente New 2 ha actualizado sus datos personales.','2026-07-05 19:34:26','NO_LEIDA',NULL),(125,1,24,'Tu cita médica ha sido agendada para el 05/07/2026 a las 07:40 PM.','2026-07-05 19:34:49','NO_LEIDA',NULL),(126,21,14,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 07:40 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-05 19:34:49','NO_LEIDA',NULL),(127,22,15,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 07:40 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-05 19:34:49','LEIDA','2026-11-13 08:16:56'),(128,22,28,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 07:40 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-05 19:34:49','NO_LEIDA',NULL),(129,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $100.000,00.','2026-07-05 19:35:54','LEIDA','2026-11-13 08:16:56'),(130,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $100.000,00.','2026-07-05 19:35:54','NO_LEIDA',NULL),(131,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-05 19:35:54','LEIDA',NULL),(132,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $120.000,00.','2026-07-05 19:35:54','LEIDA','2026-11-13 08:16:56'),(133,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $120.000,00.','2026-07-05 19:35:54','NO_LEIDA',NULL),(134,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-05 19:35:54','LEIDA',NULL),(135,17,14,'Tus datos personales fueron actualizados con éxito.','2026-07-05 19:36:43','LEIDA',NULL),(136,18,15,'El odontólogo Manuel Turizoo ha actualizado sus datos personales.','2026-07-05 19:36:43','LEIDA','2026-11-13 08:16:56'),(137,18,28,'El odontólogo Manuel Turizoo ha actualizado sus datos personales.','2026-07-05 19:36:43','NO_LEIDA',NULL),(139,20,28,'El paciente New 2 realizo un pago por $10.000,00 a la factura #1009.','2026-07-05 19:39:11','NO_LEIDA',NULL),(140,19,24,'Hemos recibido tu pago de $10.000,00 a la factura #1009. Gracias!','2026-07-05 19:39:11','LEIDA',NULL),(142,20,28,'El paciente New 2 hizo un abono de $20.000,00 a la factura #1009.','2026-07-05 19:40:04','NO_LEIDA',NULL),(143,19,24,'Hemos recibido tu abono de $20.000,00 por concepto de LIMPIEZA PROFUNDA (DETARTRAJE). Gracias!','2026-07-05 19:40:04','LEIDA',NULL),(144,24,14,'El administrador te ha realizado un pago por $209.100,00 correspondiente al mes de 2026-07.','2026-07-05 19:42:23','LEIDA',NULL),(146,20,28,'Has registrado un pago por $209.100,00 al odontólogo(a) Manuel Turizoo correspondiente al mes de 2026-07.','2026-07-05 19:42:23','NO_LEIDA',NULL),(147,24,14,'El administrador te ha realizado un pago por $209.100,00 correspondiente al mes de 2026-07.','2026-07-05 19:45:15','NO_LEIDA',NULL),(149,20,28,'Has registrado un pago por $209.100,00 al odontólogo(a) Manuel Turizoo correspondiente al mes de 2026-07.','2026-07-05 19:45:15','NO_LEIDA',NULL),(150,24,14,'El administrador te ha realizado un pago por $209.100,00 correspondiente al mes de 2026-07.','2026-07-05 19:45:39','NO_LEIDA',NULL),(152,20,28,'Has registrado un pago por $209.100,00 al odontólogo(a) Manuel Turizoo correspondiente al mes de 2026-07.','2026-07-05 19:45:39','NO_LEIDA',NULL),(153,24,14,'El administrador te ha realizado un pago por $209.100,00 correspondiente al mes de 2026-07.','2026-07-05 19:46:03','NO_LEIDA',NULL),(155,20,28,'Has registrado un pago por $209.100,00 al odontólogo(a) Manuel Turizoo correspondiente al mes de 2026-07.','2026-07-05 19:46:03','NO_LEIDA',NULL),(156,24,14,'El administrador te ha realizado un pago por $209.100,00 correspondiente al mes de 2026-07.','2026-07-05 20:01:13','NO_LEIDA',NULL),(158,20,28,'Has registrado un pago por $209.100,00 al odontólogo(a) Manuel Turizoo correspondiente al mes de 2026-07.','2026-07-05 20:01:13','NO_LEIDA',NULL),(159,1,24,'Tu cita médica ha sido agendada para el 05/07/2026 a las 09:14 PM.','2026-07-05 20:14:49','NO_LEIDA',NULL),(160,21,14,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 09:14 PM. Procedimiento: RESINA DE FOTOCURADO (CALZA)','2026-07-05 20:14:49','NO_LEIDA',NULL),(161,22,15,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 09:14 PM. Procedimiento: RESINA DE FOTOCURADO (CALZA)','2026-07-05 20:14:49','LEIDA','2026-11-13 08:16:56'),(162,22,28,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 09:14 PM. Procedimiento: RESINA DE FOTOCURADO (CALZA)','2026-07-05 20:14:49','NO_LEIDA',NULL),(163,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $90.000,00.','2026-07-05 20:16:53','LEIDA','2026-11-13 08:16:56'),(164,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $90.000,00.','2026-07-05 20:16:53','NO_LEIDA',NULL),(165,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-05 20:16:53','NO_LEIDA',NULL),(166,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $100.000,00.','2026-07-05 20:16:53','LEIDA','2026-11-13 08:16:56'),(167,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $100.000,00.','2026-07-05 20:16:53','NO_LEIDA',NULL),(168,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-05 20:16:53','NO_LEIDA',NULL),(169,1,24,'Tu cita médica ha sido agendada para el 05/07/2026 a las 11:14 PM.','2026-07-05 20:29:02','NO_LEIDA',NULL),(170,21,14,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 11:14 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-05 20:29:02','NO_LEIDA',NULL),(171,22,15,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 11:14 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-05 20:29:02','LEIDA','2026-11-13 08:16:56'),(172,22,28,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 11:14 PM. Procedimiento: VALORACION Y DIAGNOSTICO INICIAL','2026-07-05 20:29:02','NO_LEIDA',NULL),(173,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $50.000,00.','2026-07-05 20:33:26','LEIDA','2026-11-13 08:16:56'),(174,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $50.000,00.','2026-07-05 20:33:26','NO_LEIDA',NULL),(175,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-05 20:33:26','LEIDA',NULL),(176,1,24,'Tu cita médica ha sido agendada para el 05/07/2026 a las 09:30 PM.','2026-07-05 21:24:48','NO_LEIDA',NULL),(177,21,21,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 09:30 PM. Procedimiento: CONTROL MENSUAL DE ORTODONCIA','2026-07-05 21:24:48','NO_LEIDA',NULL),(178,22,15,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 09:30 PM. Procedimiento: CONTROL MENSUAL DE ORTODONCIA','2026-07-05 21:24:48','LEIDA','2026-11-13 08:16:56'),(179,22,28,'El paciente ha agendado una nueva cita médica para el 05/07/2026 a las 09:30 PM. Procedimiento: CONTROL MENSUAL DE ORTODONCIA','2026-07-05 21:24:48','NO_LEIDA',NULL),(180,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $1.400.000,00.','2026-07-05 22:45:01','LEIDA','2026-11-13 08:16:56'),(181,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $1.400.000,00.','2026-07-05 22:45:01','NO_LEIDA',NULL),(182,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-05 22:45:01','NO_LEIDA',NULL),(183,1,24,'Tu cita médica ha sido agendada para el 06/07/2026 a las 07:30 AM.','2026-07-05 23:00:56','NO_LEIDA',NULL),(184,21,14,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 07:30 AM. Procedimiento: Resina de fotocurado (calza)','2026-07-05 23:00:56','NO_LEIDA',NULL),(185,22,15,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 07:30 AM. Procedimiento: Resina de fotocurado (calza)','2026-07-05 23:00:56','LEIDA','2026-11-13 08:16:56'),(186,22,28,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 07:30 AM. Procedimiento: Resina de fotocurado (calza)','2026-07-05 23:00:56','NO_LEIDA',NULL),(187,1,24,'Tu cita médica ha sido agendada para el 06/07/2026 a las 09:15 AM.','2026-07-06 09:04:53','LEIDA',NULL),(188,21,21,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 09:15 AM. Procedimiento: Control mensual de ortodoncia','2026-07-06 09:04:53','LEIDA',NULL),(190,22,28,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 09:15 AM. Procedimiento: Control mensual de ortodoncia','2026-07-06 09:04:53','NO_LEIDA',NULL),(191,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $400.000,00.','2026-07-06 09:07:01','LEIDA','2026-11-13 08:16:56'),(192,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $400.000,00.','2026-07-06 09:07:01','NO_LEIDA',NULL),(193,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-06 09:07:01','NO_LEIDA',NULL),(194,1,24,'Tu cita médica ha sido agendada para el 06/07/2026 a las 09:30 AM.','2026-07-06 09:23:44','NO_LEIDA',NULL),(195,21,14,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 09:30 AM. Procedimiento: Resina de fotocurado (calza)','2026-07-06 09:23:44','NO_LEIDA',NULL),(196,22,15,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 09:30 AM. Procedimiento: Resina de fotocurado (calza)','2026-07-06 09:23:44','LEIDA','2026-11-13 08:16:56'),(197,22,28,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 09:30 AM. Procedimiento: Resina de fotocurado (calza)','2026-07-06 09:23:44','NO_LEIDA',NULL),(198,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $180.000,00.','2026-07-06 09:25:48','LEIDA','2026-11-13 08:16:56'),(199,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $180.000,00.','2026-07-06 09:25:48','NO_LEIDA',NULL),(200,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-06 09:25:48','NO_LEIDA',NULL),(201,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $2.000.000,00.','2026-07-06 09:38:36','LEIDA','2026-11-13 08:16:56'),(202,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $2.000.000,00.','2026-07-06 09:38:36','NO_LEIDA',NULL),(203,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-06 09:38:36','NO_LEIDA',NULL),(204,1,24,'Tu cita médica ha sido agendada para el 06/07/2026 a las 10:15 AM.','2026-07-06 10:00:32','NO_LEIDA',NULL),(205,21,21,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 10:15 AM. Procedimiento: Exodoncia simple (extraccion)','2026-07-06 10:00:32','NO_LEIDA',NULL),(206,22,15,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 10:15 AM. Procedimiento: Exodoncia simple (extraccion)','2026-07-06 10:00:32','LEIDA','2026-11-13 08:16:56'),(207,22,28,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 10:15 AM. Procedimiento: Exodoncia simple (extraccion)','2026-07-06 10:00:32','NO_LEIDA',NULL),(208,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $120.000,00.','2026-07-06 10:03:17','LEIDA','2026-11-13 08:16:56'),(209,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $120.000,00.','2026-07-06 10:03:17','NO_LEIDA',NULL),(210,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-06 10:03:17','NO_LEIDA',NULL),(211,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $100.000,00.','2026-07-06 10:03:17','LEIDA','2026-11-13 08:16:56'),(212,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $100.000,00.','2026-07-06 10:03:17','NO_LEIDA',NULL),(213,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-06 10:03:17','NO_LEIDA',NULL),(214,1,24,'Tu cita médica ha sido agendada para el 06/07/2026 a las 11:15 AM.','2026-07-06 10:13:00','NO_LEIDA',NULL),(215,21,21,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 11:15 AM. Procedimiento: Protesis parcial removible','2026-07-06 10:13:00','NO_LEIDA',NULL),(216,22,15,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 11:15 AM. Procedimiento: Protesis parcial removible','2026-07-06 10:13:00','LEIDA','2026-11-13 08:16:56'),(217,22,28,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 11:15 AM. Procedimiento: Protesis parcial removible','2026-07-06 10:13:00','NO_LEIDA',NULL),(218,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $600.000,00.','2026-07-06 10:14:19','LEIDA','2026-11-13 08:16:56'),(219,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $600.000,00.','2026-07-06 10:14:19','NO_LEIDA',NULL),(220,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-06 10:14:19','NO_LEIDA',NULL),(221,1,24,'Tu cita médica ha sido agendada para el 06/07/2026 a las 11:30 AM.','2026-07-06 11:11:34','NO_LEIDA',NULL),(222,21,14,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 11:30 AM. Procedimiento: Limpieza profunda (detartraje)','2026-07-06 11:11:34','NO_LEIDA',NULL),(223,22,15,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 11:30 AM. Procedimiento: Limpieza profunda (detartraje)','2026-07-06 11:11:34','LEIDA','2026-11-13 08:16:56'),(224,22,28,'El paciente ha agendado una nueva cita médica para el 06/07/2026 a las 11:30 AM. Procedimiento: Limpieza profunda (detartraje)','2026-07-06 11:11:34','NO_LEIDA',NULL),(225,29,15,'Se ha creado un nuevo plan de tratamiento para New 2 por $360.000,00.','2026-07-06 11:13:11','LEIDA','2026-11-13 08:16:56'),(226,29,28,'Se ha creado un nuevo plan de tratamiento para New 2 por $360.000,00.','2026-07-06 11:13:11','NO_LEIDA',NULL),(227,30,24,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-06 11:13:11','NO_LEIDA',NULL),(228,18,15,'Tus datos personales fueron actualizados con éxito.','2026-07-06 14:57:59','LEIDA','2026-11-13 08:16:56'),(229,23,15,'El paciente New 2 ha cancelado la totalidad de la factura #1028 por $120.000,00.','2026-07-06 14:58:13','LEIDA','2026-11-13 08:16:56'),(230,23,28,'El paciente New 2 ha cancelado la totalidad de la factura #1028 por $120.000,00.','2026-07-06 14:58:13','NO_LEIDA',NULL),(231,5,24,'Hemos recibido tu pago total de $120.000,00 por concepto de Limpieza profunda (detartraje). Gracias!','2026-07-06 14:58:13','NO_LEIDA',NULL),(232,20,15,'El paciente New 2 hizo un abono de $20.000,00 a la factura #1029.','2026-07-06 14:58:26','LEIDA','2026-11-13 08:16:56'),(233,20,28,'El paciente New 2 hizo un abono de $20.000,00 a la factura #1029.','2026-07-06 14:58:26','NO_LEIDA',NULL),(234,19,24,'Hemos recibido tu abono de $20.000,00 por concepto de Limpieza profunda (detartraje). Gracias!','2026-07-06 14:58:26','NO_LEIDA',NULL),(235,23,15,'El paciente New 2 ha cancelado la totalidad de la factura #1029 por $100.000,00.','2026-07-06 15:00:36','LEIDA','2026-11-13 08:16:56'),(236,23,28,'El paciente New 2 ha cancelado la totalidad de la factura #1029 por $100.000,00.','2026-07-06 15:00:36','NO_LEIDA',NULL),(237,5,24,'Hemos recibido tu pago total de $100.000,00 por concepto de Limpieza profunda (detartraje). Gracias!','2026-07-06 15:00:36','NO_LEIDA',NULL),(238,1,24,'Tu cita médica ha sido agendada para el 07/07/2026 a las 07:00 AM.','2026-07-06 16:24:29','NO_LEIDA',NULL),(239,21,21,'El paciente ha agendado una nueva cita médica para el 07/07/2026 a las 07:00 AM. Procedimiento: Exodoncia simple (extraccion)','2026-07-06 16:24:29','LEIDA',NULL),(241,22,28,'El paciente ha agendado una nueva cita médica para el 07/07/2026 a las 07:00 AM. Procedimiento: Exodoncia simple (extraccion)','2026-07-06 16:24:29','NO_LEIDA',NULL),(242,7,24,'Su cita ha sido cancelada por la administración. Motivo: Cancelada por administrador','2026-07-07 11:38:13','NO_LEIDA',NULL),(243,8,21,'El administrador canceló una cita asignada a usted. Motivo: Cancelada por administrador','2026-07-07 11:38:13','NO_LEIDA',NULL),(244,7,24,'Su cita ha sido cancelada por la administración. Motivo: Cancelada por administrador','2026-07-07 11:38:30','NO_LEIDA',NULL),(245,8,21,'El administrador canceló una cita asignada a usted. Motivo: Cancelada por administrador','2026-07-07 11:38:30','NO_LEIDA',NULL),(246,7,24,'Su cita ha sido cancelada por la administración. Motivo: Cancelada por administrador','2026-07-07 11:38:42','NO_LEIDA',NULL),(247,8,21,'El administrador canceló una cita asignada a usted. Motivo: Cancelada por administrador','2026-07-07 11:38:42','NO_LEIDA',NULL),(248,7,24,'Su cita ha sido cancelada por la administración. Motivo: Cancelada por administrador','2026-07-07 11:38:54','NO_LEIDA',NULL),(249,8,21,'El administrador canceló una cita asignada a usted. Motivo: Cancelada por administrador','2026-07-07 11:38:54','NO_LEIDA',NULL),(250,20,15,'El paciente New 2 hizo un abono de $35.000,00 a la factura #1027.','2026-07-07 12:12:55','LEIDA','2026-11-13 08:16:56'),(251,20,28,'El paciente New 2 hizo un abono de $35.000,00 a la factura #1027.','2026-07-07 12:12:55','NO_LEIDA',NULL),(252,19,24,'Hemos recibido tu abono de $35.000,00 por concepto de Limpieza profunda (detartraje). Gracias!','2026-07-07 12:12:55','LEIDA','2026-10-21 21:29:19'),(253,24,21,'El administrador te ha realizado un pago por $1.048.000,00 correspondiente al mes de 2026-07.','2026-07-07 12:14:38','NO_LEIDA',NULL),(254,20,15,'Has registrado un pago por $1.048.000,00 al odontólogo(a) Maicol Gay correspondiente al mes de 2026-07.','2026-07-07 12:14:38','LEIDA','2026-11-13 08:16:56'),(255,20,28,'Has registrado un pago por $1.048.000,00 al odontólogo(a) Maicol Gay correspondiente al mes de 2026-07.','2026-07-07 12:14:38','NO_LEIDA',NULL),(256,23,15,'El paciente ha cancelado la totalidad de la factura #1030 por $150.000,00.','2026-07-07 12:25:02','LEIDA','2026-11-13 08:16:56'),(257,23,28,'El paciente ha cancelado la totalidad de la factura #1030 por $150.000,00.','2026-07-07 12:25:02','NO_LEIDA',NULL),(258,5,2,'Hemos recibido tu pago total de $150.000,00 por concepto de Carga Masiva. Gracias!','2026-07-07 12:25:02','NO_LEIDA',NULL),(259,20,15,'El paciente hizo un abono de $100.000,00 a la factura #1031.','2026-07-07 12:25:02','LEIDA','2026-11-13 08:16:56'),(260,20,28,'El paciente hizo un abono de $100.000,00 a la factura #1031.','2026-07-07 12:25:02','NO_LEIDA',NULL),(261,19,3,'Hemos recibido tu abono de $100.000,00 por concepto de Carga Masiva. Gracias!','2026-07-07 12:25:02','NO_LEIDA',NULL),(262,18,15,'Tus datos personales fueron actualizados con éxito.','2026-07-07 13:06:48','LEIDA','2026-11-13 08:16:56'),(263,18,15,'Tus datos personales fueron actualizados con éxito.','2026-07-16 07:18:02','LEIDA','2026-11-13 08:16:56'),(264,17,14,'Tus datos personales fueron actualizados con éxito.','2026-07-16 07:19:32','NO_LEIDA',NULL),(265,18,15,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-16 07:19:32','LEIDA','2026-11-13 08:16:56'),(266,18,28,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-16 07:19:32','NO_LEIDA',NULL),(267,17,14,'Tus datos personales fueron actualizados con éxito.','2026-07-16 07:27:14','NO_LEIDA',NULL),(268,18,15,'El odontólogo Manuel Turiza ha actualizado sus datos personales.','2026-07-16 07:27:14','LEIDA','2026-11-13 08:16:56'),(269,18,28,'El odontólogo Manuel Turiza ha actualizado sus datos personales.','2026-07-16 07:27:14','NO_LEIDA',NULL),(270,17,14,'Tus datos personales fueron actualizados con éxito.','2026-07-16 07:27:24','NO_LEIDA',NULL),(271,18,15,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-16 07:27:24','LEIDA','2026-11-13 08:16:56'),(272,18,28,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-16 07:27:24','NO_LEIDA',NULL),(273,18,15,'El paciente Edubin Torres se ha registrado en la plataforma.','2026-07-16 10:05:50','LEIDA','2026-11-13 08:16:56'),(274,18,28,'El paciente Edubin Torres se ha registrado en la plataforma.','2026-07-16 10:05:50','NO_LEIDA',NULL),(275,1,33,'Tu cita médica ha sido agendada para el 16/07/2026 a las 01:00 PM.','2026-07-16 10:41:56','NO_LEIDA',NULL),(276,21,35,'El paciente ha agendado una nueva cita médica para el 16/07/2026 a las 01:00 PM. Procedimiento: Montaje de brakets metalicos (cuota inicial)','2026-07-16 10:41:56','LEIDA',NULL),(277,22,15,'El paciente ha agendado una nueva cita médica para el 16/07/2026 a las 01:00 PM. Procedimiento: Montaje de brakets metalicos (cuota inicial)','2026-07-16 10:41:56','LEIDA','2026-11-13 08:16:56'),(278,22,28,'El paciente ha agendado una nueva cita médica para el 16/07/2026 a las 01:00 PM. Procedimiento: Montaje de brakets metalicos (cuota inicial)','2026-07-16 10:41:56','NO_LEIDA',NULL),(279,29,15,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $80.000,00.','2026-07-16 10:50:04','LEIDA','2026-11-13 08:16:56'),(280,29,28,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $80.000,00.','2026-07-16 10:50:04','NO_LEIDA',NULL),(281,30,33,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-16 10:50:04','NO_LEIDA',NULL),(282,18,15,'El paciente Victor Martinez se ha registrado en la plataforma.','2026-07-20 19:11:20','LEIDA','2026-11-13 08:16:56'),(283,18,28,'El paciente Victor Martinez se ha registrado en la plataforma.','2026-07-20 19:11:20','NO_LEIDA',NULL),(285,18,15,'El paciente Victor Martinez ha actualizado sus datos personales.','2026-07-20 19:13:10','LEIDA','2026-11-13 08:16:56'),(286,18,28,'El paciente Victor Martinez ha actualizado sus datos personales.','2026-07-20 19:13:10','NO_LEIDA',NULL),(287,17,14,'Tus datos personales fueron actualizados con éxito.','2026-07-20 19:31:54','NO_LEIDA',NULL),(288,18,15,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-20 19:31:54','LEIDA','2026-11-13 08:16:56'),(289,18,28,'El odontólogo Manuel Turizo ha actualizado sus datos personales.','2026-07-20 19:31:54','NO_LEIDA',NULL),(290,18,15,'Tus datos personales fueron actualizados con éxito.','2026-07-20 19:32:57','LEIDA','2026-11-13 08:16:56'),(291,1,33,'Tu cita médica ha sido agendada para el 22/07/2026 a las 11:00 AM.','2026-07-22 07:20:26','NO_LEIDA',NULL),(292,21,35,'El paciente ha agendado una nueva cita médica para el 22/07/2026 a las 11:00 AM. Procedimiento: Valoracion y diagnostico inicial','2026-07-22 07:20:26','LEIDA',NULL),(293,22,15,'El paciente ha agendado una nueva cita médica para el 22/07/2026 a las 11:00 AM. Procedimiento: Valoracion y diagnostico inicial','2026-07-22 07:20:26','LEIDA','2026-11-13 08:16:56'),(294,22,28,'El paciente ha agendado una nueva cita médica para el 22/07/2026 a las 11:00 AM. Procedimiento: Valoracion y diagnostico inicial','2026-07-22 07:20:26','NO_LEIDA',NULL),(295,29,15,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $50.000,00.','2026-07-22 07:21:42','LEIDA','2026-11-13 08:16:56'),(296,29,28,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $50.000,00.','2026-07-22 07:21:42','NO_LEIDA',NULL),(297,30,33,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-22 07:21:42','NO_LEIDA',NULL),(298,29,15,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $120.000,00.','2026-07-22 07:21:42','LEIDA','2026-11-13 08:16:56'),(299,29,28,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $120.000,00.','2026-07-22 07:21:42','NO_LEIDA',NULL),(300,30,33,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-22 07:21:42','NO_LEIDA',NULL),(301,17,35,'Tus datos personales fueron actualizados con éxito.','2026-07-22 07:31:08','LEIDA',NULL),(302,18,15,'El odontólogo Lis Pena ha actualizado sus datos personales.','2026-07-22 07:31:08','LEIDA','2026-11-13 08:16:56'),(303,18,28,'El odontólogo Lis Pena ha actualizado sus datos personales.','2026-07-22 07:31:08','NO_LEIDA',NULL),(304,16,33,'Tus datos personales fueron actualizados con éxito.','2026-07-22 07:44:44','LEIDA',NULL),(305,18,15,'El paciente Edubin Torres ha actualizado sus datos personales.','2026-07-22 07:44:44','LEIDA','2026-11-13 08:16:56'),(306,18,28,'El paciente Edubin Torres ha actualizado sus datos personales.','2026-07-22 07:44:44','NO_LEIDA',NULL),(307,29,15,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $90.000,00.','2026-07-22 07:46:24','LEIDA','2026-11-13 08:16:56'),(308,29,28,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $90.000,00.','2026-07-22 07:46:24','NO_LEIDA',NULL),(309,30,33,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-22 07:46:24','NO_LEIDA',NULL),(310,29,15,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $950.000,00.','2026-07-22 07:46:24','LEIDA','2026-11-13 08:16:56'),(311,29,28,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $950.000,00.','2026-07-22 07:46:24','NO_LEIDA',NULL),(312,30,33,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-22 07:46:24','NO_LEIDA',NULL),(313,29,15,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $100.000,00.','2026-07-22 07:46:24','LEIDA','2026-11-13 08:16:56'),(314,29,28,'Se ha creado un nuevo plan de tratamiento para Edubin Torres por $100.000,00.','2026-07-22 07:46:24','NO_LEIDA',NULL),(315,30,33,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-07-22 07:46:24','NO_LEIDA',NULL),(317,20,28,'El paciente Edubin Torres realizo un abono por $40.000,00 a la factura #1032.','2026-07-22 07:47:18','NO_LEIDA',NULL),(318,19,33,'Hemos recibido tu abono de $40.000,00 a la factura #1032. Gracias!','2026-07-22 07:47:18','NO_LEIDA',NULL),(320,23,28,'El paciente Edubin Torres ha cancelado la totalidad de la factura #1032 por $40.000,00.','2026-07-22 07:47:53','NO_LEIDA',NULL),(321,5,33,'Hemos recibido tu pago total de $40.000,00 a la factura #1032. Gracias!','2026-07-22 07:47:53','NO_LEIDA',NULL),(323,20,28,'El paciente Edubin Torres hizo un abono de $25.000,00 a la factura #1037.','2026-07-22 07:49:27','NO_LEIDA',NULL),(324,19,33,'Hemos recibido tu abono de $25.000,00 por concepto de Exodoncia simple (extraccion). Gracias!','2026-07-22 07:49:27','NO_LEIDA',NULL),(325,24,35,'El administrador te ha realizado un pago por $417.000,00 correspondiente al mes de 2026-07.','2026-07-22 07:51:13','LEIDA',NULL),(326,20,15,'Has registrado un pago por $417.000,00 al odontólogo(a) Lis Pena correspondiente al mes de 2026-07.','2026-07-22 07:51:13','LEIDA','2026-11-13 08:16:56'),(327,20,28,'Has registrado un pago por $417.000,00 al odontólogo(a) Lis Pena correspondiente al mes de 2026-07.','2026-07-22 07:51:13','NO_LEIDA',NULL),(328,34,15,'Nuevo mensaje de contacto web de Test Prospecto María Gómez. Tel: 3124567890. Mensaje: Hola, me gustaría saber los costos del tratamiento de ortodo...','2026-10-12 14:59:46','LEIDA','2026-11-13 08:16:56'),(329,34,28,'Nuevo mensaje de contacto web de Test Prospecto María Gómez. Tel: 3124567890. Mensaje: Hola, me gustaría saber los costos del tratamiento de ortodo...','2026-10-12 14:59:46','NO_LEIDA',NULL),(330,34,15,'Nuevo mensaje de contacto web de Test Prospecto María Gómez. Tel: 3124567890. Mensaje: Hola, me gustaría saber los costos del tratamiento de ortodo...','2026-10-12 15:00:05','LEIDA','2026-11-13 08:16:56'),(331,34,28,'Nuevo mensaje de contacto web de Test Prospecto María Gómez. Tel: 3124567890. Mensaje: Hola, me gustaría saber los costos del tratamiento de ortodo...','2026-10-12 15:00:05','NO_LEIDA',NULL),(332,34,15,'Nuevo mensaje de contacto web de Test Prospecto María Gómez. Tel: 3124567890. Mensaje: Hola, me gustaría saber los costos del tratamiento de ortodo...','2026-10-12 15:00:51','LEIDA','2026-11-13 08:16:56'),(333,34,28,'Nuevo mensaje de contacto web de Test Prospecto María Gómez. Tel: 3124567890. Mensaje: Hola, me gustaría saber los costos del tratamiento de ortodo...','2026-10-12 15:00:51','NO_LEIDA',NULL),(334,2,3,'Tu cita programada para el 23/05/2026 08:00 ha sido cancelada.','2026-10-12 17:58:28','',NULL),(335,18,15,'El paciente Lisbeth Geovanna Veliz se ha registrado en la plataforma.','2026-10-23 16:53:38','LEIDA','2026-11-13 08:16:56'),(336,18,28,'El paciente Lisbeth Geovanna Veliz se ha registrado en la plataforma.','2026-10-23 16:53:38','NO_LEIDA',NULL),(338,18,15,'El paciente Lisbeth Geovanna Veliz se ha registrado en la plataforma.','2026-10-23 17:00:18','LEIDA','2026-11-13 08:16:56'),(339,18,28,'El paciente Lisbeth Geovanna Veliz se ha registrado en la plataforma.','2026-10-23 17:00:18','NO_LEIDA',NULL),(341,18,15,'El paciente Lisbeth Geovanna Veliz se ha registrado en la plataforma.','2026-10-23 17:09:30','LEIDA','2026-11-13 08:16:56'),(342,18,28,'El paciente Lisbeth Geovanna Veliz se ha registrado en la plataforma.','2026-10-23 17:09:30','NO_LEIDA',NULL),(344,1,23,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-10-23 18:57:53','NO_LEIDA',NULL),(345,1,15,'Se ha creado un nuevo plan de tratamiento para Fabio Contrao por $120.000,00.','2026-10-23 18:57:53','LEIDA','2026-11-13 08:16:56'),(346,16,3,'Has vinculado exitosamente a tu hijo/a Camila Prueba (Padre). Ya puedes agendar citas a su nombre.','2026-11-13 07:47:56','NO_LEIDA',NULL),(347,18,15,'El acudiente MAICOL MALDONADO registró a su familiar menor Camila Prueba.','2026-11-13 07:47:56','LEIDA','2026-11-13 08:16:56'),(348,18,28,'El acudiente MAICOL MALDONADO registró a su familiar menor Camila Prueba.','2026-11-13 07:47:56','NO_LEIDA',NULL),(349,16,3,'Has actualizado exitosamente los datos de tu familiar menor Camila Prueba.','2026-11-13 07:48:23','NO_LEIDA',NULL),(350,18,15,'El paciente Cesar Hernandez se ha registrado en la plataforma.','2026-11-13 07:51:18','LEIDA','2026-11-13 08:16:56'),(351,18,28,'El paciente Cesar Hernandez se ha registrado en la plataforma.','2026-11-13 07:51:18','NO_LEIDA',NULL),(352,16,48,'¡Bienvenido/a Cesar a Odonto Estética! Ya puedes agendar tus citas desde el portal.','2026-11-13 07:51:18','NO_LEIDA',NULL),(353,16,48,'Tu contraseña ha sido actualizada exitosamente.','2026-11-13 07:53:43','NO_LEIDA',NULL),(354,16,48,'Tus datos personales fueron actualizados con éxito.','2026-11-13 08:01:09','NO_LEIDA',NULL),(355,18,15,'El paciente Cesar Esteban Hernandez Galindo ha actualizado sus datos personales.','2026-11-13 08:01:09','LEIDA','2026-11-13 08:16:56'),(356,18,28,'El paciente Cesar Esteban Hernandez Galindo ha actualizado sus datos personales.','2026-11-13 08:01:09','NO_LEIDA',NULL),(357,16,48,'Has vinculado exitosamente a tu hijo/a Dylan Alexander Mahecha Castellanos (Padre). Ya puedes agendar citas a su nombre.','2026-11-13 08:02:40','NO_LEIDA',NULL),(358,18,15,'El acudiente Cesar Esteban Hernandez Galindo registró a su familiar menor Dylan Alexander Mahecha Castellanos.','2026-11-13 08:02:40','LEIDA','2026-11-13 08:16:56'),(359,18,28,'El acudiente Cesar Esteban Hernandez Galindo registró a su familiar menor Dylan Alexander Mahecha Castellanos.','2026-11-13 08:02:40','NO_LEIDA',NULL),(360,16,48,'Tu contraseña ha sido actualizada exitosamente.','2026-11-13 08:03:12','NO_LEIDA',NULL),(361,16,48,'Has vinculado exitosamente a tu hijo/a Dylan Alexander Mahecha Castellanos (Madre). Ya puedes agendar citas a su nombre.','2026-11-13 08:04:46','LEIDA','2026-11-13 08:18:43'),(362,18,15,'El acudiente Cesar Esteban Hernandez Galindo registró a su familiar menor Dylan Alexander Mahecha Castellanos.','2026-11-13 08:04:46','LEIDA','2026-11-13 08:21:40'),(363,18,28,'El acudiente Cesar Esteban Hernandez Galindo registró a su familiar menor Dylan Alexander Mahecha Castellanos.','2026-11-13 08:04:46','NO_LEIDA',NULL),(364,18,15,'Tus datos personales fueron actualizados con éxito.','2026-11-13 08:34:07','NO_LEIDA',NULL),(365,18,15,'Tus datos personales fueron actualizados con éxito.','2026-11-13 08:52:12','NO_LEIDA',NULL),(366,18,15,'Tus datos personales fueron actualizados con éxito.','2026-11-13 08:52:23','NO_LEIDA',NULL),(367,34,15,'Nuevo mensaje de contacto web de maicol. Tel: 3130264958. Mensaje: deseo tener una sonrisa hermosa','2026-11-13 08:55:10','NO_LEIDA',NULL),(368,34,28,'Nuevo mensaje de contacto web de maicol. Tel: 3130264958. Mensaje: deseo tener una sonrisa hermosa','2026-11-13 08:55:10','NO_LEIDA',NULL),(369,1,48,'Tu cita médica ha sido agendada para el 13/11/2026 a las 10:00 AM.','2026-11-13 09:00:06','LEIDA','2026-11-13 09:00:19'),(370,21,51,'El paciente Cesar Esteban Hernandez Galindo ha agendado una cita médica para el 13/11/2026 a las 10:00 AM. Procedimiento: Endodoncia multirradicular (molares)','2026-11-13 09:00:06','NO_LEIDA',NULL),(371,22,15,'El paciente Cesar Esteban Hernandez Galindo ha agendado una cita médica para el 13/11/2026 a las 10:00 AM. Procedimiento: Endodoncia multirradicular (molares)','2026-11-13 09:00:06','NO_LEIDA',NULL),(372,22,28,'El paciente Cesar Esteban Hernandez Galindo ha agendado una cita médica para el 13/11/2026 a las 10:00 AM. Procedimiento: Endodoncia multirradicular (molares)','2026-11-13 09:00:06','NO_LEIDA',NULL),(373,2,48,'Se reprogramó una cita para el 13/11/2026 pm Fridaypm00 12:00 PM. Motivo: El paciente solicitó cambiar el horario de atención médica.','2026-11-13 09:03:25','LEIDA','2026-11-13 09:03:58'),(374,7,48,'Tu cita programada para el 13/11/2026 12:00 ha sido cancelada. Motivo: Problemas de horario','2026-11-13 09:05:08','LEIDA','2026-11-13 09:05:26'),(375,7,48,'Tu cita médica del 13/11/2026 a las 12:00 PM ha sido cancelada. Motivo: Problemas de horario','2026-11-13 09:05:08','LEIDA','2026-11-13 09:05:29'),(376,8,51,'El paciente ha cancelado su cita del 13/11/2026 a las 12:00 PM. Motivo: Problemas de horario','2026-11-13 09:05:08','NO_LEIDA',NULL),(377,9,15,'El paciente ha cancelado su cita del 13/11/2026 a las 12:00 PM. Motivo: Problemas de horario','2026-11-13 09:05:08','NO_LEIDA',NULL),(378,9,28,'El paciente ha cancelado su cita del 13/11/2026 a las 12:00 PM. Motivo: Problemas de horario','2026-11-13 09:05:08','NO_LEIDA',NULL),(379,1,48,'Tu cita médica ha sido agendada para el 13/11/2026 a las 10:00 AM.','2026-11-13 09:06:30','NO_LEIDA',NULL),(380,21,51,'El paciente Cesar Esteban Hernandez Galindo ha agendado una cita médica para el 13/11/2026 a las 10:00 AM. Procedimiento: Blanqueamiento dental','2026-11-13 09:06:30','LEIDA','2026-11-19 07:16:23'),(381,22,15,'El paciente Cesar Esteban Hernandez Galindo ha agendado una cita médica para el 13/11/2026 a las 10:00 AM. Procedimiento: Blanqueamiento dental','2026-11-13 09:06:30','NO_LEIDA',NULL),(382,22,28,'El paciente Cesar Esteban Hernandez Galindo ha agendado una cita médica para el 13/11/2026 a las 10:00 AM. Procedimiento: Blanqueamiento dental','2026-11-13 09:06:30','NO_LEIDA',NULL),(383,1,48,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-11-13 09:13:27','NO_LEIDA',NULL),(384,1,15,'Se ha creado un nuevo plan de tratamiento para Cesar Esteban Hernandez Galindo por $480.000,00.','2026-11-13 09:13:27','NO_LEIDA',NULL),(385,1,48,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-11-13 09:13:28','NO_LEIDA',NULL),(386,1,15,'Se ha creado un nuevo plan de tratamiento para Cesar Esteban Hernandez Galindo por $90.000,00.','2026-11-13 09:13:28','NO_LEIDA',NULL),(387,23,15,'El paciente Cesar Esteban Hernandez Galindo canceló la totalidad de la factura FAC-1043 por $120.000 (Efectivo).','2026-11-13 09:28:54','LEIDA','2026-11-13 09:29:12'),(388,23,28,'El paciente Cesar Esteban Hernandez Galindo canceló la totalidad de la factura FAC-1043 por $120.000 (Efectivo).','2026-11-13 09:28:54','NO_LEIDA',NULL),(389,5,48,'Hemos recibido tu pago total de $120.000 para la factura FAC-1043. ¡Gracias!','2026-11-13 09:28:54','NO_LEIDA',NULL),(390,24,51,'El administrador te ha realizado un pago por $171.000,00 correspondiente al mes de 2026-11.','2026-11-13 09:32:06','LEIDA',NULL),(391,20,15,'Has registrado un pago por $171.000,00 al odontólogo(a) Maria Mar correspondiente al mes de 2026-11.','2026-11-13 09:32:06','NO_LEIDA',NULL),(392,20,28,'Has registrado un pago por $171.000,00 al odontólogo(a) Maria Mar correspondiente al mes de 2026-11.','2026-11-13 09:32:06','NO_LEIDA',NULL),(393,24,14,'El administrador te ha realizado un pago por $180.000,00 correspondiente al mes de 2026-07.','2026-11-13 09:34:08','NO_LEIDA',NULL),(394,20,15,'Has registrado un pago por $180.000,00 al odontólogo(a) Manuel Turizo correspondiente al mes de 2026-07.','2026-11-13 09:34:08','LEIDA','2026-11-13 09:35:30'),(395,20,28,'Has registrado un pago por $180.000,00 al odontólogo(a) Manuel Turizo correspondiente al mes de 2026-07.','2026-11-13 09:34:08','NO_LEIDA',NULL),(396,18,15,'El paciente DYLAN CASTELLANOS se ha registrado en la plataforma.','2026-11-18 22:05:27','NO_LEIDA',NULL),(397,18,28,'El paciente DYLAN CASTELLANOS se ha registrado en la plataforma.','2026-11-18 22:05:27','NO_LEIDA',NULL),(398,16,52,'¡Bienvenido/a DYLAN a Odonto Estética! Ya puedes agendar tus citas desde el portal.','2026-11-18 22:05:27','NO_LEIDA',NULL),(399,1,3,'Tu cita médica ha sido agendada para el 19/11/2026 a las 08:00 AM.','2026-11-19 07:06:06','NO_LEIDA',NULL),(400,21,51,'El paciente MAICOL MALDONADO ha agendado una cita médica para el 19/11/2026 a las 08:00 AM. Procedimiento: Control mensual de ortodoncia','2026-11-19 07:06:06','LEIDA','2026-11-19 07:09:20'),(401,22,15,'El paciente MAICOL MALDONADO ha agendado una cita médica para el 19/11/2026 a las 08:00 AM. Procedimiento: Control mensual de ortodoncia','2026-11-19 07:06:06','NO_LEIDA',NULL),(402,22,28,'El paciente MAICOL MALDONADO ha agendado una cita médica para el 19/11/2026 a las 08:00 AM. Procedimiento: Control mensual de ortodoncia','2026-11-19 07:06:06','NO_LEIDA',NULL),(403,1,3,'Tu nuevo plan de tratamiento ya está disponible en tu perfil.','2026-11-19 07:12:08','LEIDA','2026-11-19 07:14:04'),(404,1,15,'Se ha creado un nuevo plan de tratamiento para MAICOL MALDONADO por $90.000,00.','2026-11-19 07:12:08','LEIDA','2026-11-19 07:35:06'),(405,20,15,'El paciente MAICOL MALDONADO realizó un abono de $20.000 a la factura FAC-1031 (Efectivo).','2026-11-19 07:16:17','NO_LEIDA',NULL),(406,20,28,'El paciente MAICOL MALDONADO realizó un abono de $20.000 a la factura FAC-1031 (Efectivo).','2026-11-19 07:16:17','NO_LEIDA',NULL),(407,19,3,'Hemos recibido tu abono de $20.000 para la factura FAC-1031. ¡Gracias!','2026-11-19 07:16:17','NO_LEIDA',NULL),(408,20,15,'El paciente MAICOL MALDONADO realizó un abono de $18.000 a la factura FAC-1031 (Efectivo).','2026-11-19 07:18:03','LEIDA','2026-11-19 07:18:37'),(409,20,28,'El paciente MAICOL MALDONADO realizó un abono de $18.000 a la factura FAC-1031 (Efectivo).','2026-11-19 07:18:03','NO_LEIDA',NULL),(410,24,51,'El administrador te ha realizado un pago por $27.000,00 correspondiente al mes de 2026-11.','2026-11-19 07:37:36','NO_LEIDA',NULL),(411,20,15,'Has registrado un pago por $27.000,00 al odontólogo(a) Maria Mar correspondiente al mes de 2026-11.','2026-11-19 07:37:36','NO_LEIDA',NULL),(412,20,28,'Has registrado un pago por $27.000,00 al odontólogo(a) Maria Mar correspondiente al mes de 2026-11.','2026-11-19 07:37:36','NO_LEIDA',NULL);

/*Table structure for table `odontograma_tratamientos` */

DROP TABLE IF EXISTS `odontograma_tratamientos`;

CREATE TABLE `odontograma_tratamientos` (
  `ID_ODONTOGRAMA_REGISTRO` int(11) NOT NULL AUTO_INCREMENT,
  `PACIENTE_ID_PACIENTE` int(11) NOT NULL,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) DEFAULT NULL,
  `PIEZA_DENTAL` int(11) NOT NULL,
  `PROCEDIMIENTO_ID_PROCEDIMIENTO` int(11) NOT NULL,
  `FECHA_REGISTRO` date NOT NULL,
  `NOTAS` text,
  `ESTADO` enum('PENDIENTE','HECHO','CANCELADO') NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`ID_ODONTOGRAMA_REGISTRO`),
  KEY `FK_ODONTOGRAMA_PACIENTE` (`PACIENTE_ID_PACIENTE`),
  KEY `FK_ODONTOGRAMA_ODONTOLOGO` (`ODONTOLOGO_ID_ODONTOLOGO`),
  KEY `FK_ODONTOGRAMA_PROCEDIMIENTO` (`PROCEDIMIENTO_ID_PROCEDIMIENTO`),
  CONSTRAINT `fk_odonto_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON UPDATE CASCADE,
  CONSTRAINT `fk_odonto_paciente` FOREIGN KEY (`PACIENTE_ID_PACIENTE`) REFERENCES `paciente` (`ID_PACIENTE`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_odonto_procedimiento` FOREIGN KEY (`PROCEDIMIENTO_ID_PROCEDIMIENTO`) REFERENCES `procedimientos` (`ID_PROCEDIMIENTO`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

/*Data for the table `odontograma_tratamientos` */

insert  into `odontograma_tratamientos`(`ID_ODONTOGRAMA_REGISTRO`,`PACIENTE_ID_PACIENTE`,`ODONTOLOGO_ID_ODONTOLOGO`,`PIEZA_DENTAL`,`PROCEDIMIENTO_ID_PROCEDIMIENTO`,`FECHA_REGISTRO`,`NOTAS`,`ESTADO`) values (1,17,6,4,5,'2026-07-02','prueba 1 egr','HECHO'),(2,18,6,18,2,'2026-07-02','','HECHO'),(3,18,6,17,3,'2026-07-02','','HECHO'),(4,18,6,6,9,'2026-07-03','hola ivan','HECHO'),(5,18,6,5,4,'2026-07-05','','HECHO'),(6,18,6,3,2,'2026-07-05','','HECHO'),(7,18,6,4,3,'2026-07-05','','HECHO'),(8,18,6,8,4,'2026-07-05','','HECHO'),(9,18,6,20,1,'2026-07-05','','HECHO'),(10,18,8,3,100,'2026-07-06','','HECHO'),(11,18,8,4,100,'2026-07-06','','HECHO'),(12,18,8,5,100,'2026-07-06','','HECHO'),(13,18,8,6,100,'2026-07-06','','HECHO'),(14,18,8,10,4,'2026-07-06','','HECHO'),(15,18,8,11,4,'2026-07-06','','HECHO'),(16,18,8,31,4,'2026-07-06','','HECHO'),(17,18,8,32,4,'2026-07-06','','HECHO'),(18,18,6,25,3,'2026-07-06','','HECHO'),(19,18,6,31,3,'2026-07-06','','HECHO'),(20,18,6,10,104,'2026-07-06','','HECHO'),(21,18,8,10,2,'2026-07-06','','HECHO'),(22,18,8,7,4,'2026-07-06','','HECHO'),(23,18,8,2,7,'2026-07-06','','HECHO'),(24,18,8,3,7,'2026-07-06','','HECHO'),(25,18,8,4,7,'2026-07-06','','HECHO'),(26,18,8,5,7,'2026-07-06','','HECHO'),(27,18,6,2,2,'2026-07-06','Pendiente para despues','HECHO'),(28,18,6,4,2,'2026-07-06','Pendiente para despues','HECHO'),(29,18,6,5,2,'2026-07-06','Pendiente para despues','HECHO'),(30,22,15,4,9,'2026-07-16','bdfasdfgj','HECHO'),(31,22,15,1,1,'2026-07-22','se detecto una caries','HECHO'),(32,22,15,1,2,'2026-07-22','se detecto una caries','HECHO'),(33,22,15,1,3,'2026-07-22','','HECHO'),(34,22,15,1,5,'2026-07-22','','HECHO'),(35,22,15,1,4,'2026-07-22','','HECHO'),(37,31,18,1,2,'2026-11-13','','HECHO'),(38,31,18,3,2,'2026-11-13','','HECHO'),(39,31,18,4,2,'2026-11-13','','HECHO'),(40,31,18,5,2,'2026-11-13','','HECHO'),(41,31,18,2,3,'2026-11-13','Prueba','HECHO'),(42,1,18,3,3,'2026-11-19','','HECHO');

/*Table structure for table `odontologo` */

DROP TABLE IF EXISTS `odontologo`;

CREATE TABLE `odontologo` (
  `ID_ODONTOLOGO` int(11) NOT NULL AUTO_INCREMENT,
  `USUARIOS_ID_USUARIOS` int(11) NOT NULL,
  `ESPECIALIDAD_ID_ESPECIALIDAD` int(11) NOT NULL,
  `CONSULTORIO_ID_CONSULTORIO` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID_ODONTOLOGO`),
  KEY `ESPECIALIDAD_ID_ESPECIALIDAD` (`ESPECIALIDAD_ID_ESPECIALIDAD`),
  KEY `USUARIOS_ID_USUARIOS` (`USUARIOS_ID_USUARIOS`),
  KEY `fk_odontologo_consultorio` (`CONSULTORIO_ID_CONSULTORIO`),
  CONSTRAINT `fk_odontologo_consultorio` FOREIGN KEY (`CONSULTORIO_ID_CONSULTORIO`) REFERENCES `consultorio` (`ID_CONSULTORIO`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_odontologo_especialidad` FOREIGN KEY (`ESPECIALIDAD_ID_ESPECIALIDAD`) REFERENCES `especialidad` (`ID_ESPECIALIDAD`) ON UPDATE CASCADE,
  CONSTRAINT `fk_odontologo_usuarios` FOREIGN KEY (`USUARIOS_ID_USUARIOS`) REFERENCES `usuarios` (`ID_USUARIOS`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Data for the table `odontologo` */

insert  into `odontologo`(`ID_ODONTOLOGO`,`USUARIOS_ID_USUARIOS`,`ESPECIALIDAD_ID_ESPECIALIDAD`,`CONSULTORIO_ID_CONSULTORIO`) values (2,1,2,6),(6,14,6,NULL),(7,20,9,NULL),(8,21,12,8),(10,22,8,NULL),(12,30,1,9),(13,31,2,NULL),(14,34,7,10),(15,35,5,11),(18,51,14,12);

/*Table structure for table `odontologo_has_especialidad` */

DROP TABLE IF EXISTS `odontologo_has_especialidad`;

CREATE TABLE `odontologo_has_especialidad` (
  `ID_ODONTOLOGO_HAS_ESPECIALIDAD` int(11) NOT NULL AUTO_INCREMENT,
  `ODONTOLOGO_ID_ODONTOLOGO` int(11) NOT NULL,
  `ESPECIALIDAD_ID_ESPECIALIDAD` int(11) NOT NULL,
  PRIMARY KEY (`ID_ODONTOLOGO_HAS_ESPECIALIDAD`),
  UNIQUE KEY `uk_odo_esp` (`ODONTOLOGO_ID_ODONTOLOGO`,`ESPECIALIDAD_ID_ESPECIALIDAD`),
  KEY `fk_ohe_odontologo_idx` (`ODONTOLOGO_ID_ODONTOLOGO`),
  KEY `fk_ohe_especialidad_idx` (`ESPECIALIDAD_ID_ESPECIALIDAD`),
  CONSTRAINT `fk_ohe_especialidad` FOREIGN KEY (`ESPECIALIDAD_ID_ESPECIALIDAD`) REFERENCES `especialidad` (`ID_ESPECIALIDAD`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ohe_odontologo` FOREIGN KEY (`ODONTOLOGO_ID_ODONTOLOGO`) REFERENCES `odontologo` (`ID_ODONTOLOGO`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

/*Data for the table `odontologo_has_especialidad` */

insert  into `odontologo_has_especialidad`(`ID_ODONTOLOGO_HAS_ESPECIALIDAD`,`ODONTOLOGO_ID_ODONTOLOGO`,`ESPECIALIDAD_ID_ESPECIALIDAD`) values (13,2,1),(1,2,2),(2,6,6),(3,7,9),(4,8,12),(5,10,8),(6,12,1),(7,13,2),(8,14,7),(9,15,1),(10,15,5),(11,15,6),(12,15,7),(22,18,1),(30,18,2),(17,18,3),(23,18,4),(19,18,5),(18,18,6),(24,18,7),(20,18,8),(27,18,9),(29,18,10),(21,18,11),(26,18,12),(25,18,13),(16,18,14),(28,18,15);

/*Table structure for table `paciente` */

DROP TABLE IF EXISTS `paciente`;

CREATE TABLE `paciente` (
  `ID_PACIENTE` int(11) NOT NULL AUTO_INCREMENT,
  `USUARIOS_ID_USUARIOS` int(11) NOT NULL,
  `EPS_ID_EPS` int(11) NOT NULL,
  `NOMBRE_CONTACTO_EMERGENCIA` varchar(45) DEFAULT NULL,
  `NUMERO_CONTACTO_EMERGENCIA` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`ID_PACIENTE`),
  KEY `USUARIOS_ID_USUARIOS` (`USUARIOS_ID_USUARIOS`),
  KEY `EPS_ID_EPS` (`EPS_ID_EPS`),
  CONSTRAINT `fk_paciente_eps` FOREIGN KEY (`EPS_ID_EPS`) REFERENCES `eps` (`ID_EPS`) ON UPDATE CASCADE,
  CONSTRAINT `fk_paciente_usuarios` FOREIGN KEY (`USUARIOS_ID_USUARIOS`) REFERENCES `usuarios` (`ID_USUARIOS`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

/*Data for the table `paciente` */

insert  into `paciente`(`ID_PACIENTE`,`USUARIOS_ID_USUARIOS`,`EPS_ID_EPS`,`NOMBRE_CONTACTO_EMERGENCIA`,`NUMERO_CONTACTO_EMERGENCIA`) values (1,3,1,'MARIA HOYOS','3148309234'),(2,4,2,'JULIANA BELTRAN','3184928491'),(8,13,2,'Carlos Martinez','3849402121'),(9,2,3,'',''),(10,14,3,'',''),(12,18,1,'',''),(17,23,1,'',''),(18,24,1,'',''),(19,27,1,NULL,NULL),(20,29,1,NULL,NULL),(21,32,1,NULL,NULL),(22,33,5,'',''),(30,47,5,'',''),(31,48,4,'',''),(32,49,3,'',''),(33,50,4,'',''),(34,52,2,'','');

/*Table structure for table `paciente_has_acudiente` */

DROP TABLE IF EXISTS `paciente_has_acudiente`;

CREATE TABLE `paciente_has_acudiente` (
  `ID_PACIENTE_HAS_ACUDIENTE` int(11) NOT NULL AUTO_INCREMENT,
  `PACIENTE_ID_PACIENTE` int(11) NOT NULL,
  `USUARIOS_ID_ACUDIENTE` int(11) NOT NULL,
  `PARENTESCO` enum('Madre','Padre','Abuelo/a','Tío/a','Tutor Legal','Otro') NOT NULL DEFAULT 'Madre',
  `CONTACTO_PRINCIPAL` enum('Si','No') NOT NULL DEFAULT 'Si',
  `AUTORIZA_TRATAMIENTOS` enum('Si','No') NOT NULL DEFAULT 'Si',
  `FECHA_VINCULACION` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_PACIENTE_HAS_ACUDIENTE`),
  UNIQUE KEY `uk_paciente_acudiente` (`PACIENTE_ID_PACIENTE`,`USUARIOS_ID_ACUDIENTE`),
  KEY `idx_pha_paciente` (`PACIENTE_ID_PACIENTE`),
  KEY `idx_pha_acudiente` (`USUARIOS_ID_ACUDIENTE`),
  CONSTRAINT `fk_pha_acudiente` FOREIGN KEY (`USUARIOS_ID_ACUDIENTE`) REFERENCES `usuarios` (`ID_USUARIOS`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pha_paciente` FOREIGN KEY (`PACIENTE_ID_PACIENTE`) REFERENCES `paciente` (`ID_PACIENTE`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `paciente_has_acudiente` */

insert  into `paciente_has_acudiente`(`ID_PACIENTE_HAS_ACUDIENTE`,`PACIENTE_ID_PACIENTE`,`USUARIOS_ID_ACUDIENTE`,`PARENTESCO`,`CONTACTO_PRINCIPAL`,`AUTORIZA_TRATAMIENTOS`,`FECHA_VINCULACION`) values (1,30,3,'Padre','Si','Si','2026-11-13 07:47:56');

/*Table structure for table `paciente_has_condicion_medica` */

DROP TABLE IF EXISTS `paciente_has_condicion_medica`;

CREATE TABLE `paciente_has_condicion_medica` (
  `ID_PACIENTE_HAS_CONDICION_MEDICA` int(11) NOT NULL AUTO_INCREMENT,
  `PACIENTE_ID_PACIENTE` int(11) NOT NULL,
  `CONDICION_MEDICA_ID_CONDICION_MEDICA` int(11) NOT NULL,
  `OBSERVACION` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ID_PACIENTE_HAS_CONDICION_MEDICA`),
  KEY `CONDICION_MEDICA_ID_CONDICION_MEDICA` (`CONDICION_MEDICA_ID_CONDICION_MEDICA`),
  KEY `PACIENTE_ID_PACIENTE` (`PACIENTE_ID_PACIENTE`),
  CONSTRAINT `fk_phcm_condicion` FOREIGN KEY (`CONDICION_MEDICA_ID_CONDICION_MEDICA`) REFERENCES `condicion_medica` (`ID_CONDICION_MEDICA`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_phcm_paciente` FOREIGN KEY (`PACIENTE_ID_PACIENTE`) REFERENCES `paciente` (`ID_PACIENTE`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Data for the table `paciente_has_condicion_medica` */

insert  into `paciente_has_condicion_medica`(`ID_PACIENTE_HAS_CONDICION_MEDICA`,`PACIENTE_ID_PACIENTE`,`CONDICION_MEDICA_ID_CONDICION_MEDICA`,`OBSERVACION`) values (1,1,1,'RECOMENDACION UNAS PASTAS PARA CUAGULAR LA SANGRE'),(2,2,3,NULL),(9,8,2,NULL),(10,8,3,NULL),(15,9,1,NULL),(16,9,4,NULL),(17,22,1,NULL),(18,22,4,NULL),(19,31,1,NULL),(20,31,3,NULL);

/*Table structure for table `paciente_has_fases_plan` */

DROP TABLE IF EXISTS `paciente_has_fases_plan`;

CREATE TABLE `paciente_has_fases_plan` (
  `ID_PACIENTE_HAS_FASES_PLAN` int(11) NOT NULL AUTO_INCREMENT,
  `PACIENTE_ID_PACIENTE` int(11) NOT NULL,
  `FASES_PLAN_ID_FASE` int(11) NOT NULL,
  `ESTADO` enum('PENDIENTE','EN_CURSO','COMPLETADO') NOT NULL DEFAULT 'PENDIENTE',
  `FECHA_ACTUALIZACION` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_PACIENTE_HAS_FASES_PLAN`),
  UNIQUE KEY `uk_paciente_fase` (`PACIENTE_ID_PACIENTE`,`FASES_PLAN_ID_FASE`),
  KEY `fk_phfp_paciente_idx` (`PACIENTE_ID_PACIENTE`),
  KEY `fk_phfp_fases_idx` (`FASES_PLAN_ID_FASE`),
  CONSTRAINT `fk_phfp_fases` FOREIGN KEY (`FASES_PLAN_ID_FASE`) REFERENCES `fases_plan` (`ID_FASE`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_phfp_paciente` FOREIGN KEY (`PACIENTE_ID_PACIENTE`) REFERENCES `paciente` (`ID_PACIENTE`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Data for the table `paciente_has_fases_plan` */

insert  into `paciente_has_fases_plan`(`ID_PACIENTE_HAS_FASES_PLAN`,`PACIENTE_ID_PACIENTE`,`FASES_PLAN_ID_FASE`,`ESTADO`,`FECHA_ACTUALIZACION`) values (1,1,1,'COMPLETADO','2026-06-23 00:14:17'),(2,1,2,'COMPLETADO','2026-06-23 00:14:17'),(3,1,3,'EN_CURSO','2026-06-23 00:14:17'),(4,1,4,'PENDIENTE','2026-06-23 00:14:17'),(5,2,5,'COMPLETADO','2026-06-23 00:14:17'),(6,2,6,'PENDIENTE','2026-06-23 00:14:17'),(7,2,7,'PENDIENTE','2026-06-23 00:14:17');

/*Table structure for table `pago` */

DROP TABLE IF EXISTS `pago`;

CREATE TABLE `pago` (
  `ID_PAGO` int(11) NOT NULL AUTO_INCREMENT,
  `FACTURA_ID_FACTURA` int(11) NOT NULL,
  `MONTO` decimal(10,0) NOT NULL,
  `FECHA_PAGO` datetime NOT NULL,
  `METODO_PAGO` enum('EFECTIVO','TRANSFERENCIA','TARJETA') NOT NULL,
  `ESTADO` enum('PENDIENTE','PAGADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`ID_PAGO`),
  KEY `FACTURA_ID_FACTURA` (`FACTURA_ID_FACTURA`),
  CONSTRAINT `fk_pago_factura` FOREIGN KEY (`FACTURA_ID_FACTURA`) REFERENCES `factura` (`ID_FACTURA`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

/*Data for the table `pago` */

insert  into `pago`(`ID_PAGO`,`FACTURA_ID_FACTURA`,`MONTO`,`FECHA_PAGO`,`METODO_PAGO`,`ESTADO`) values (1,1,80000,'2026-02-28 15:38:36','EFECTIVO','PAGADO'),(2,2,120000,'0000-00-00 00:00:00','EFECTIVO','PENDIENTE'),(88,88,1200000,'2026-06-18 17:29:12','TRANSFERENCIA','PAGADO'),(100,100,500000,'2026-06-18 17:50:31','TRANSFERENCIA','PAGADO'),(101,101,120000,'2026-01-15 09:45:00','EFECTIVO','PAGADO'),(103,103,200000,'2026-05-20 11:00:00','TARJETA','PAGADO'),(104,1004,12000,'0000-00-00 00:00:00','EFECTIVO','PAGADO'),(105,1004,30000,'0000-00-00 00:00:00','EFECTIVO','PAGADO'),(106,1004,140000,'0000-00-00 00:00:00','EFECTIVO','PAGADO'),(107,1004,140000,'0000-00-00 00:00:00','EFECTIVO','PAGADO'),(108,1004,140000,'0000-00-00 00:00:00','EFECTIVO','PAGADO'),(109,1003,20000,'2026-06-28 19:18:57','EFECTIVO','PAGADO'),(110,999,50000,'2026-06-28 19:19:34','TRANSFERENCIA','PAGADO'),(111,102,180000,'2026-06-28 19:23:10','EFECTIVO','PAGADO'),(112,103,100000,'2026-06-28 19:29:39','EFECTIVO','PAGADO'),(113,103,100000,'2026-06-28 19:30:45','EFECTIVO','PAGADO'),(114,1003,200,'2026-06-28 19:33:48','EFECTIVO','PAGADO'),(115,1003,29800,'2026-06-28 19:34:27','EFECTIVO','PAGADO'),(116,999,1000,'2026-06-29 14:20:12','TRANSFERENCIA','PAGADO'),(117,999,20000,'2026-06-29 15:48:22','TARJETA','PAGADO'),(118,1007,40000,'2026-07-03 12:02:36','TRANSFERENCIA','PAGADO'),(119,1007,40000,'2026-07-03 12:04:20','TRANSFERENCIA','PAGADO'),(120,1006,45000,'2026-07-03 12:13:15','EFECTIVO','PAGADO'),(121,1006,45000,'2026-07-03 12:13:29','TARJETA','PAGADO'),(122,1005,60000,'2026-07-03 12:13:50','TARJETA','PAGADO'),(123,1005,20000,'2026-07-03 12:15:12','TARJETA','PAGADO'),(124,1005,10000,'2026-07-03 12:24:10','EFECTIVO','PAGADO'),(125,1005,10000,'2026-07-03 12:24:32','TRANSFERENCIA','PAGADO'),(126,1005,5000,'2026-07-03 12:24:51','TRANSFERENCIA','PAGADO'),(127,1005,15000,'2026-07-03 12:25:05','TARJETA','PAGADO'),(128,103,7000,'2026-07-03 13:12:42','TARJETA','PAGADO'),(129,103,69300,'2026-07-03 13:14:27','TRANSFERENCIA','PAGADO'),(130,103,623700,'2026-07-03 13:14:58','TARJETA','PAGADO'),(131,1009,10000,'2026-07-05 19:39:11','TARJETA','PAGADO'),(132,1009,20000,'2026-07-05 19:40:04','TARJETA','PAGADO'),(133,1028,120000,'2026-07-06 14:58:13','EFECTIVO','PAGADO'),(134,1029,20000,'2026-07-06 14:58:26','EFECTIVO','PAGADO'),(135,1029,100000,'2026-07-06 15:00:36','EFECTIVO','PAGADO'),(136,1027,35000,'2026-07-07 12:12:55','TRANSFERENCIA','PAGADO'),(137,1030,150000,'2026-07-06 10:00:00','EFECTIVO','PAGADO'),(138,1031,100000,'2026-07-06 11:30:00','TRANSFERENCIA','PENDIENTE'),(139,1032,40000,'2026-07-22 07:47:17','EFECTIVO','PAGADO'),(140,1032,40000,'2026-07-22 07:47:53','TARJETA','PAGADO'),(141,1037,25000,'2026-07-22 07:49:27','TRANSFERENCIA','PAGADO'),(142,1043,120000,'2026-11-13 09:28:54','EFECTIVO','PAGADO'),(143,1031,20000,'2026-11-19 07:16:17','EFECTIVO','PAGADO'),(144,1031,18000,'2026-11-19 07:18:03','EFECTIVO','PAGADO');

/*Table structure for table `pago_mercadopago` */

DROP TABLE IF EXISTS `pago_mercadopago`;

CREATE TABLE `pago_mercadopago` (
  `ID_PAGO_MERCADOPAGO` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FACTURA_ID_FACTURA` int(11) NOT NULL,
  `EXTERNAL_REFERENCE` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PREFERENCE_ID` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PAYMENT_ID` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MONTO` decimal(12,2) NOT NULL,
  `ESTADO` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `STATUS_DETAIL` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PAYLOAD` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `PAGO_ID_PAGO` int(11) DEFAULT NULL,
  `FECHA_CREACION` datetime NOT NULL,
  `FECHA_ACTUALIZACION` datetime DEFAULT NULL,
  PRIMARY KEY (`ID_PAGO_MERCADOPAGO`),
  UNIQUE KEY `pago_mercadopago_external_reference_unique` (`EXTERNAL_REFERENCE`),
  KEY `pago_mercadopago_factura_id_factura_index` (`FACTURA_ID_FACTURA`),
  KEY `pago_mercadopago_pago_id_pago_foreign` (`PAGO_ID_PAGO`),
  CONSTRAINT `pago_mercadopago_factura_id_factura_foreign` FOREIGN KEY (`FACTURA_ID_FACTURA`) REFERENCES `factura` (`ID_FACTURA`) ON DELETE CASCADE,
  CONSTRAINT `pago_mercadopago_pago_id_pago_foreign` FOREIGN KEY (`PAGO_ID_PAGO`) REFERENCES `pago` (`ID_PAGO`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `pago_mercadopago` */

insert  into `pago_mercadopago`(`ID_PAGO_MERCADOPAGO`,`FACTURA_ID_FACTURA`,`EXTERNAL_REFERENCE`,`PREFERENCE_ID`,`PAYMENT_ID`,`MONTO`,`ESTADO`,`STATUS_DETAIL`,`PAYLOAD`,`PAGO_ID_PAGO`,`FECHA_CREACION`,`FECHA_ACTUALIZACION`) values (1,88,'FAC-88-XAVE5SXUUL3R4BJH2RFB',NULL,NULL,100000.00,'ERROR','SSL certificate OpenSSL verify result: unable to get local issuer certificate (20)',NULL,NULL,'2026-11-13 07:47:22','2026-11-13 07:47:26'),(2,1044,'FAC-1044-0POCH6LPQCGTR9UTJAIK',NULL,NULL,12000.00,'ERROR','SSL certificate OpenSSL verify result: unable to get local issuer certificate (20)',NULL,NULL,'2026-11-13 09:48:11','2026-11-13 09:48:16'),(3,1044,'FAC-1044-HYAJPXJB3WLZYPAV3AKH',NULL,NULL,12000.00,'ERROR','SSL certificate OpenSSL verify result: unable to get local issuer certificate (20)',NULL,NULL,'2026-11-13 09:52:54','2026-11-13 09:52:59'),(4,1044,'FAC-1044-AG4OLUKDISIDMSNBXNCR','3837128187-ea01d636-7545-4d70-ac6f-f322b3858451',NULL,12000.00,'PENDIENTE',NULL,NULL,NULL,'2026-11-13 09:58:09',NULL),(5,1044,'FAC-1044-N3TYWICHDWE5YG1UNXBW','3837128187-4394a7bc-b4ae-484b-beaf-e89a652ea7d5',NULL,12000.00,'PENDIENTE',NULL,NULL,NULL,'2026-11-13 09:59:59',NULL),(6,1044,'FAC-1044-YFRFXMHQBDM7JTFTU62J','3837128187-19455f00-157c-43af-9adc-d8145aacbbb9',NULL,120000.00,'PENDIENTE',NULL,NULL,NULL,'2026-11-13 10:04:46',NULL);

/*Table structure for table `procedimientos` */

DROP TABLE IF EXISTS `procedimientos`;

CREATE TABLE `procedimientos` (
  `ID_PROCEDIMIENTO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_PROCEDIMIENTO` varchar(100) NOT NULL,
  `DESCRIPCION` varchar(200) NOT NULL,
  `COSTO` decimal(10,0) NOT NULL,
  `TIEMPO_ESTIMADO` int(11) NOT NULL DEFAULT '30',
  `ESTADO` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `TIPO_COBRO` int(11) DEFAULT '1',
  `ESPECIALIDAD_ID_ESPECIALIDAD` int(11) DEFAULT '1',
  PRIMARY KEY (`ID_PROCEDIMIENTO`),
  KEY `fk_proc_especialidad` (`ESPECIALIDAD_ID_ESPECIALIDAD`),
  CONSTRAINT `fk_proc_especialidad` FOREIGN KEY (`ESPECIALIDAD_ID_ESPECIALIDAD`) REFERENCES `especialidad` (`ID_ESPECIALIDAD`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8;

/*Data for the table `procedimientos` */

insert  into `procedimientos`(`ID_PROCEDIMIENTO`,`NOMBRE_PROCEDIMIENTO`,`DESCRIPCION`,`COSTO`,`TIEMPO_ESTIMADO`,`ESTADO`,`TIPO_COBRO`,`ESPECIALIDAD_ID_ESPECIALIDAD`) values (1,'Valoracion y diagnostico inicial','examen clinico completo y plan de tratamiento',50000,20,'INACTIVO',1,1),(2,'Limpieza profunda (detartraje)','remocion de calculos y profilasis simple',120000,45,'ACTIVO',1,1),(3,'Resina de fotocurado (calza)','restauracion estetica para cavidades por caries',90000,40,'ACTIVO',1,1),(4,'Exodoncia simple (extraccion)','retiro de pieza dental no compleja',100000,30,'ACTIVO',1,1),(5,'Protesis total superior','reemplazo total de dentadura superior en acrilico',950000,30,'ACTIVO',2,2),(6,'Protesis total inferior','reemplazo total de dentadura inferior en acrilico',950000,30,'ACTIVO',2,2),(7,'Protesis parcial removible','estructura para reemplazar solo algunas piezas faltantes',600000,30,'ACTIVO',2,2),(8,'Montaje de brakets metalicos (cuota inicial)','instalacion de aparatologia para alineamiento dental',1500000,60,'ACTIVO',2,7),(9,'Control mensual de ortodoncia','ajuste de arcos y ligaduras mensuales',80000,30,'ACTIVO',2,7),(10,'Endodoncia monorradicular (1 conducto)','tratamiento de nervio en dientes frontales',350000,30,'ACTIVO',1,6),(11,'Endodoncia multirradicular (molares)','tratamiento de nervio en muelas (mas complejo)',550000,90,'ACTIVO',1,6),(99,'Diseño de sonrisa premium test','procedimiento estético de prueba',1500000,30,'ACTIVO',2,5),(100,'Exodoncia quirurgica (cordales)','extraccion compleja de terceros molares',350000,60,'ACTIVO',1,3),(101,'Endodoncia birradicular','tratamiento de nervio en premolares (2 conductos)',450000,60,'ACTIVO',1,6),(102,'Blanqueamiento dental','blanqueamiento estetico en consultorio',400000,60,'ACTIVO',2,5),(103,'Corona en zirconio','funda estetica libre de metal',900000,60,'ACTIVO',1,2),(104,'Implante dental (fase quirurgica)','colocacion de tornillo de titanio en hueso',2000000,90,'ACTIVO',1,8),(107,'Retenedor transparente essix','retenedor post-ortodoncia por arcada',200000,30,'ACTIVO',2,7),(110,'CARILLAS','reemplazo de carillas',200000,180,'INACTIVO',1,1);

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `ID_ROLES` int(11) NOT NULL AUTO_INCREMENT,
  `ROL_NOMBRE` varchar(45) NOT NULL,
  `DESCRIPCION` varchar(60) NOT NULL,
  PRIMARY KEY (`ID_ROLES`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `roles` */

insert  into `roles`(`ID_ROLES`,`ROL_NOMBRE`,`DESCRIPCION`) values (1,'ADMINISTRADOR','Gestiona sus citas y consulta su información'),(2,'ODONTOLOGO','Atiende pacientes y gestiona historias clínicas y citas.'),(3,'PACIENTE','Gestiona sus citas y consulta su información clínica.'),(4,'ADMINISTRADOR_JEFE','encargado de la gestion de todo el personal');

/*Table structure for table `roles_has_acciones` */

DROP TABLE IF EXISTS `roles_has_acciones`;

CREATE TABLE `roles_has_acciones` (
  `ID_ROLES_HAS_ACCIONES` int(11) NOT NULL AUTO_INCREMENT,
  `ROLES_ID_ROLES` int(11) NOT NULL,
  `ACCIONES_ID_ACCIONES` int(11) NOT NULL,
  PRIMARY KEY (`ID_ROLES_HAS_ACCIONES`),
  KEY `ROLES_ID_ROLES` (`ROLES_ID_ROLES`),
  KEY `ACCIONES_ID_ACCIONES` (`ACCIONES_ID_ACCIONES`),
  CONSTRAINT `fk_rha_acciones` FOREIGN KEY (`ACCIONES_ID_ACCIONES`) REFERENCES `acciones` (`ID_ACCIONES`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rha_roles` FOREIGN KEY (`ROLES_ID_ROLES`) REFERENCES `roles` (`ID_ROLES`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `roles_has_acciones` */

insert  into `roles_has_acciones`(`ID_ROLES_HAS_ACCIONES`,`ROLES_ID_ROLES`,`ACCIONES_ID_ACCIONES`) values (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,2,1),(6,2,2),(7,2,3),(8,3,1),(9,3,2),(10,3,3);

/*Table structure for table `tipo_notificacion` */

DROP TABLE IF EXISTS `tipo_notificacion`;

CREATE TABLE `tipo_notificacion` (
  `ID_TIPO_NOTIFICACION` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE` varchar(100) NOT NULL,
  `DESCRIPCION` varchar(200) NOT NULL,
  `ESTADO` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `ROL_DESTINO` int(11) DEFAULT '3',
  PRIMARY KEY (`ID_TIPO_NOTIFICACION`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

/*Data for the table `tipo_notificacion` */

insert  into `tipo_notificacion`(`ID_TIPO_NOTIFICACION`,`NOMBRE`,`DESCRIPCION`,`ESTADO`,`ROL_DESTINO`) values (1,'AGENDAMIENTO DE CITA','CONFIRMACION INMEDIANTA COON FECHA, HORA, DIRECCION Y ODONTOLOGO ASIGNADO','ACTIVO',3),(2,'RECORDATORIO 24H','AVISO AUTOMATICO PARA EL PACIENTE DE 24 HORAS ANTES DE LA CITA','ACTIVO',3),(3,'ALERTA DE AGENDA DIARIA','RESUMEN MATUTINO PARA EL ODONTOLOGO CON TODAS LAS CITAS DEL DIA','ACTIVO',3),(5,'CONFIRMACION DEL PAGO','REGISTRO ADMINISTRATIVO QUE VALIDA EL INGRESO DEL DINERO(ABONO O TOTAL) A UNA FACTURA. SIRVE COMO RESPALDO LEGAL PARA LOS USUARIOS','ACTIVO',3),(6,'RECORDATORIO DEL PAGO','NOTIFICACION AUTOMÁTICA ENVIADA AL PACIENTE DETALLANDO EL SALDO PENDIENTE DE SU TRATAMIENTO ACTUAL Y LA FECHA LIMITE DE PAGO PARA EVITAR RETRASOS EN SUS PROCEDIMIENTOS/TRATAMIENTOS','ACTIVO',3),(7,'CITA CANCELADA','AVISO AL PACIENTE DE QUE SU CITA HA SIDO CANCELADA','ACTIVO',3),(8,'CITA CANCELADA','AVISO AL ODONTOLOGO DE QUE UNA CITA DE SU AGENDA FUE CANCELADA','ACTIVO',2),(9,'CITA CANCELADA','AVISO AL ADMINISTRADOR DE QUE UNA CITA FUE CANCELADA','ACTIVO',1),(10,'CITA CONFIRMADA','AVISO AL PACIENTE DE QUE SU CITA HA SIDO CONFIRMADA','ACTIVO',3),(11,'CITA CONFIRMADA','AVISO AL ODONTOLOGO DE QUE UNA CITA DE SU AGENDA FUE CONFIRMADA','ACTIVO',2),(12,'CITA CONFIRMADA','AVISO AL ADMINISTRADOR DE QUE UNA CITA FUE CONFIRMADA','ACTIVO',1),(13,'CITA REPROGRAMADA','AVISO AL PACIENTE DE QUE SU CITA FUE REPROGRAMADA CON NUEVA FECHA Y HORA','ACTIVO',3),(14,'CITA REPROGRAMADA','AVISO AL ODONTOLOGO DE QUE UNA CITA DE SU AGENDA FUE REPROGRAMADA','ACTIVO',2),(15,'CITA REPROGRAMADA','AVISO AL ADMINISTRADOR DE QUE UNA CITA FUE REPROGRAMADA','ACTIVO',1),(16,'ACTUALIZACION DE DATOS','AVISO AL PACIENTE DE QUE SUS DATOS PERSONALES FUERON ACTUALIZADOS','ACTIVO',3),(17,'ACTUALIZACION DE DATOS','AVISO AL ODONTOLOGO DE QUE SUS DATOS PERSONALES FUERON ACTUALIZADOS','ACTIVO',2),(18,'ACTUALIZACION DE DATOS','AVISO AL ADMINISTRADOR DE QUE SUS DATOS PERSONALES FUERON ACTUALIZADOS','ACTIVO',1),(19,'ABONO','REGISTRO DE UN ABONO (PAGO PARCIAL) REALIZADO A UNA FACTURA DEL PACIENTE','ACTIVO',3),(20,'ABONO','AVISO AL ADMINISTRADOR DE QUE SE REGISTRÓ UN ABONO (PAGO PARCIAL) A UNA FACTURA','ACTIVO',1),(21,'AGENDAMIENTO DE CITA','AVISO AL ODONTOLOGO DE QUE SE LE AGENDÓ UNA NUEVA CITA','ACTIVO',2),(22,'AGENDAMIENTO DE CITA','AVISO AL ADMINISTRADOR DE QUE SE REGISTRÓ UNA NUEVA CITA','ACTIVO',1),(23,'PAGO RECIBIDO','AVISO AL ADMINISTRADOR DE UN PAGO TOTAL RECIBIDO','ACTIVO',1),(24,'PAGO REGISTRADO','AVISO AL ODONTOLOGO DE QUE SU PACIENTE HA PAGADO','ACTIVO',2),(25,'ABONO REGISTRADO','AVISO AL ODONTOLOGO DE UN ABONO PARCIAL','ACTIVO',2),(26,'NUEVA DEUDA REGISTRADA','AVISO AL ADMIN DE UNA NUEVA DEUDA GENERADA','ACTIVO',1),(27,'NUEVO SALDO PENDIENTE','AVISO AL PACIENTE DE UN SALDO PENDIENTE','ACTIVO',3),(28,'DEUDA REGISTRADA','AVISO AL ODONTOLOGO DE UN SALDO PENDIENTE DE SU PACIENTE','ACTIVO',2),(29,'NUEVO PRESUPUESTO','AVISO AL ADMIN DE UN NUEVO PLAN DE TRATAMIENTO CREADO','ACTIVO',1),(30,'PRESUPUESTO DISPONIBLE','AVISO AL PACIENTE DE UN NUEVO PRESUPUESTO','ACTIVO',3),(31,'CIERRE DE CAJA DIARIO','RESUMEN FINANCIERO DEL DIA','ACTIVO',1),(32,'LIQUIDACION PAGADA','AVISO AL ODONTOLOGO DE QUE SE LE PAGO SU PRODUCCION DEL MES','ACTIVO',2),(33,'LIQUIDACION REGISTRADA','AVISO AL ADMINISTRADOR DE QUE REGISTRO EL PAGO DE COMISION A UN ODONTOLOGO','ACTIVO',1),(34,'MENSAJE DE CONTACTO WEB','AVISO AL ADMINISTRADOR DE UN NUEVO MENSAJE EN LA LANDING PAGE','ACTIVO',1),(35,'BIENVENIDA','AVISO DE BIENVENIDA AL PACIENTE CUANDO SE REGISTRA EN LA PLATAFORMA','ACTIVO',3),(36,'NUEVO PACIENTE REGISTRADO','AVISO AL ADMINISTRADOR CUANDO UN NUEVO PACIENTE SE REGISTRA EN LA PLATAFORMA','ACTIVO',1);

/*Table structure for table `usuarios` */

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `ID_USUARIOS` int(11) NOT NULL AUTO_INCREMENT,
  `ROLES_ID_ROLES` int(11) NOT NULL DEFAULT '3',
  `NOMBRES` varchar(45) NOT NULL,
  `APELLIDOS` varchar(45) NOT NULL,
  `TIPO_DOCUMENTO` enum('R.C','T.I','C.C','C.E','PPT') NOT NULL,
  `NUMERO_DOCUMENTO` bigint(20) NOT NULL,
  `TELEFONO` varchar(45) DEFAULT NULL,
  `CORREO` varchar(200) DEFAULT NULL,
  `GENERO` enum('MASCULINO','FEMENINO','OTRO') NOT NULL,
  `FECHA_NACIMIENTO` date DEFAULT NULL,
  `DIRECCION` varchar(60) DEFAULT NULL,
  `RH` enum('A+','B+','AB+','O+','A-','B-','AB-','O-') DEFAULT NULL,
  `CONTRASEÑA` varchar(250) DEFAULT NULL,
  `TOKEN_RECUPERACION` varchar(100) DEFAULT NULL,
  `TOKEN_EXPIRACION` datetime DEFAULT NULL,
  `ESTADO` enum('Activo','Inactivo') DEFAULT 'Activo',
  `NOTIFICACIONES_ACTIVAS` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID_USUARIOS`),
  KEY `ROLES_ID_ROLES` (`ROLES_ID_ROLES`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`ROLES_ID_ROLES`) REFERENCES `roles` (`ID_ROLES`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8;

/*Data for the table `usuarios` */

insert  into `usuarios`(`ID_USUARIOS`,`ROLES_ID_ROLES`,`NOMBRES`,`APELLIDOS`,`TIPO_DOCUMENTO`,`NUMERO_DOCUMENTO`,`TELEFONO`,`CORREO`,`GENERO`,`FECHA_NACIMIENTO`,`DIRECCION`,`RH`,`CONTRASEÑA`,`TOKEN_RECUPERACION`,`TOKEN_EXPIRACION`,`ESTADO`,`NOTIFICACIONES_ACTIVAS`) values (1,2,'MARTHA','ROMERO','C.C',51966489,'3114820481','martha@gmail.com','FEMENINO','1988-09-24','CARRERA 57#83-39','A-','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(2,3,'Esteban','Galinda','C.C',22222222,'3148598840','estebangalindoromero570@gmail.com','MASCULINO','2004-11-17','CALLE 48#02-01','O+','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(3,3,'MAICOL','MALDONADO','C.C',1089495823,'3104925809','maiquito401@gmail.com','MASCULINO','2007-04-18','CARRERA 39#94-49','AB-','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(4,3,'IVAN','MAHECHA','T.I',1038948291,'3507582930','ivan@gmail.com','MASCULINO','2008-04-09','CALLE 82#84-58','AB+','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(13,3,'Juan','Perez Mendoza','C.C',111111111,'31111111111','juanperezz@gmail.com','FEMENINO','2005-06-16','carrera 123','AB+','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(14,2,'Manuel','Turizo','C.C',34343434,'3182323677','egrnetflix11@gmail.com','MASCULINO','1981-02-26','Av 57#84-92','','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(15,1,'Admin hola','Sistema Rico','C.C',1000000001,'3493827472','admin@gmail.com','MASCULINO','1990-01-01','Calle 23#17-12','','$2y$12$K9k5MzFCZd5a9tlNFN4B6eRfpM2YuUWnj1sWZ4NL1hXaFmAioTYgO','272045','2026-10-23 17:26:44','Activo',1),(18,3,'Natalia','Romero','C.C',13734812,'3981282112','nata@gmail.com','FEMENINO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(20,2,'Ana','Maria','C.C',982868472,'3129481232','ana@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(21,2,'Maicol','Hoyos','C.C',982869695,'3274892831','maicol@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(22,2,'Dylan','Castañeda','C.C',982870066,'3173849219','dylang@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(23,3,'Fabio','Contrao','C.C',12637283,'3284721901','fabio@gmail.com','MASCULINO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(24,3,'New','2','C.C',12121212,'1212121212','new@gmail.com','MASCULINO','2004-11-17','','A+','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(25,2,'Cristiano','Ronaldo','C.C',983034036,'3127274891','cr7@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(26,2,'David','Ospina','C.C',983034568,'3126487312','david@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(27,3,'Nuno','Mendez','C.C',983034677,'3126487212','nuno@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(28,1,'Neymar','Jr','C.C',983034991,'3274849221','neymar@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$uxr8AHtdrlQWXLgUv/RATe7lW4C7tdEumWeG9frJLuOMipoiLuvtK',NULL,NULL,'Activo',1),(29,3,'Liliana','Maryori Pinzon','C.C',52112048,'3212741809','lilianahpinzon@gmail.com','FEMENINO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(30,2,'Mariana','Wilches Roman','C.C',52112001,'3113250811','marianaroman@gmail.com','FEMENINO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(31,2,'German','Gomez','C.C',19118015,'3184343829','germangomez@gmail.com','MASCULINO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(32,3,'Mohamed','Salah','C.C',983446717,'3147838992','salah@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(33,3,'Edubin','Torres','C.C',7777777,'3022158563','edutorpe@gmail.com','MASCULINO','1971-07-04','Carrera 00','O+','$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(34,2,'Oscar','Torres','C.C',984215350,'3173842912','oscar@gmail.com','OTRO',NULL,NULL,NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(35,2,'Lis','Pena','C.C',984215652,'3740294412','liz@gmail.com','OTRO','1998-10-13','Carrera 99 #130-90',NULL,'$2y$10$vSnI03MtTA8/9UuNdjnvtukLwoOxlNgqcTT9lB56/ddt3cVYp6OGW',NULL,NULL,'Activo',1),(47,3,'Camila','Prueba','T.I',1306459871,NULL,NULL,'FEMENINO','2016-07-14',NULL,'O+',NULL,NULL,NULL,'Activo',1),(48,3,'Cesar Esteban','Hernandez Galindo','C.C',1360215498,'3130264958','dylanram.112000@gmail.com','MASCULINO','2009-03-12','carrera 81 m #71 a norte','A+','$2y$12$J1tQkNYzZccDm6KpwFVc7usuplG6GFQZ9B8cZ29ycMMQnF/37Troe','917936','2026-11-13 08:11:34','Activo',1),(49,3,'Dylan Alexander','Mahecha Castellanos','T.I',1362541201,NULL,NULL,'MASCULINO','2011-02-04',NULL,'O+',NULL,NULL,NULL,'Activo',1),(50,3,'Dylan Alexander','Mahecha Castellanos','T.I',1360125012,NULL,NULL,'MASCULINO','2011-02-04',NULL,'O+',NULL,NULL,NULL,'Activo',1),(51,2,'Maria','Mar','C.C',994575720,'3130260541','santiato2236@gmail.com','OTRO',NULL,NULL,NULL,'$2y$12$Tc/mKRcOOIfSh7Rt1nIyJePHrunNW00uDAcpa8wrIWrsXKUpuRN66',NULL,NULL,'Activo',1),(52,3,'DYLAN','CASTELLANOS','C.C',1019993386,'3118713084','jc2825511@gmail.com','MASCULINO',NULL,NULL,NULL,'$2y$12$zFpZMY37kGr.YnPPBW/.AeyzayK5WvgvIkcdZuSPqUBnERPVupvnW',NULL,NULL,'Activo',1);

/* Trigger structure for table `cita` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tg_cita_ocupar_horario` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `tg_cita_ocupar_horario` AFTER INSERT ON `cita` FOR EACH ROW BEGIN
    
    IF NEW.HORARIO_DISPONIBILIDAD_ID_HORARIO IS NOT NULL AND (NEW.ESTADO_CITA_ID = 1 OR NEW.ESTADO_CITA_ID IS NULL) THEN
        UPDATE `horario_disponibilidad`
        SET `ESTADO` = 'Ocupado'
        WHERE `ID_HORARIO` = NEW.HORARIO_DISPONIBILIDAD_ID_HORARIO;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `cita` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tg_actualizar_agenda_por_estado` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `tg_actualizar_agenda_por_estado` AFTER UPDATE ON `cita` FOR EACH ROW BEGIN
    IF NEW.ESTADO_CITA_ID IN (3, 4) AND OLD.ESTADO_CITA_ID = 1 AND OLD.HORARIO_DISPONIBILIDAD_ID_HORARIO IS NOT NULL THEN
        UPDATE horario_disponibilidad 
        SET ESTADO = 'Disponible' 
        WHERE ID_HORARIO = OLD.HORARIO_DISPONIBILIDAD_ID_HORARIO
          AND ESTADO = 'Ocupado';
    ELSEIF NEW.ESTADO_CITA_ID = 1 AND NOT (NEW.HORARIO_DISPONIBILIDAD_ID_HORARIO <=> OLD.HORARIO_DISPONIBILIDAD_ID_HORARIO) THEN
        IF OLD.HORARIO_DISPONIBILIDAD_ID_HORARIO IS NOT NULL THEN
            UPDATE horario_disponibilidad 
            SET ESTADO = 'Disponible' 
            WHERE ID_HORARIO = OLD.HORARIO_DISPONIBILIDAD_ID_HORARIO
              AND ESTADO = 'Ocupado';
        END IF;
        IF NEW.HORARIO_DISPONIBILIDAD_ID_HORARIO IS NOT NULL THEN
            UPDATE horario_disponibilidad 
            SET ESTADO = 'Ocupado' 
            WHERE ID_HORARIO = NEW.HORARIO_DISPONIBILIDAD_ID_HORARIO;
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `cita` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tg_notificar_cancelacion_cita` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `tg_notificar_cancelacion_cita` AFTER UPDATE ON `cita` FOR EACH ROW BEGIN
    DECLARE id_usr_paciente INT;
    DECLARE id_usr_acudiente INT;
    DECLARE id_usr_destino INT;
    DECLARE v_notif_activas TINYINT DEFAULT 1;
    IF NEW.ESTADO_CITA_ID = 3 AND OLD.ESTADO_CITA_ID <> 3 THEN
        SELECT p.USUARIOS_ID_USUARIOS INTO id_usr_paciente
        FROM paciente p
        WHERE p.ID_PACIENTE = NEW.PACIENTE_ID_PACIENTE
        LIMIT 1;
        SELECT pha.USUARIOS_ID_ACUDIENTE INTO id_usr_acudiente
        FROM paciente_has_acudiente pha
        WHERE pha.PACIENTE_ID_PACIENTE = NEW.PACIENTE_ID_PACIENTE
        ORDER BY (pha.CONTACTO_PRINCIPAL = 'Si') DESC, pha.ID_PACIENTE_HAS_ACUDIENTE ASC
        LIMIT 1;
        SET id_usr_destino = COALESCE(id_usr_acudiente, id_usr_paciente);
        IF id_usr_destino IS NOT NULL THEN
            SELECT COALESCE(NOTIFICACIONES_ACTIVAS, 1) INTO v_notif_activas
            FROM usuarios
            WHERE ID_USUARIOS = id_usr_destino
            LIMIT 1;
            IF v_notif_activas = 1 THEN
                INSERT INTO notificaciones (
                    TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION,
                    USUARIOS_ID_USUARIOS,
                    MENSAJE,
                    FECHA_ENVIO,
                    ESTADO
                ) VALUES (
                    7,
                    id_usr_destino,
                    CONCAT(
                        'Tu cita programada para el ',
                        DATE_FORMAT(NEW.FECHA_HORA, '%d/%m/%Y %H:%i'),
                        ' ha sido cancelada.',
                        IF(NEW.MOTIVO_CANCELACION IS NOT NULL AND NEW.MOTIVO_CANCELACION <> '', CONCAT(' Motivo: ', NEW.MOTIVO_CANCELACION), '')
                    ),
                    NOW(),
                    'NO_LEIDA'
                );
            END IF;
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `historia_clinica_has_procedimientos` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `costos_procedimientos` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `costos_procedimientos` BEFORE INSERT ON `historia_clinica_has_procedimientos` FOR EACH ROW BEGIN
    DECLARE precio_catalogo DECIMAL(10,2);
    
    IF NEW.PRECIO_APLICADO IS NULL OR NEW.PRECIO_APLICADO = 0 THEN
        SELECT COSTO INTO precio_catalogo
        FROM procedimientos
        WHERE ID_PROCEDIMIENTO = NEW.PROCEDIMIENTOS_ID_PROCEDIMIENTO;
        
        SET NEW.PRECIO_APLICADO = COALESCE(precio_catalogo, 0.00);
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `historia_clinica_has_procedimientos` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `factura_automatica` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `factura_automatica` AFTER INSERT ON `historia_clinica_has_procedimientos` FOR EACH ROW BEGIN
    UPDATE factura
    SET TOTAL = TOTAL + (NEW.CANTIDAD * NEW.PRECIO_APLICADO)
    WHERE HISTORIA_CLINICA_ID_HISTORIA_CLINICA = NEW.HISTORIA_CLINICA_ID_HISTORIA_CLINICA;
END */$$


DELIMITER ;

/* Trigger structure for table `pago` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tg_factura_actualizar_estado_pago` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `tg_factura_actualizar_estado_pago` AFTER INSERT ON `pago` FOR EACH ROW BEGIN
    DECLARE total_pagado DECIMAL(10,2);
    DECLARE total_factura DECIMAL(10,2);
    DECLARE estado_actual VARCHAR(20);
    SELECT COALESCE(SUM(MONTO), 0) INTO total_pagado
    FROM pago
    WHERE FACTURA_ID_FACTURA = NEW.FACTURA_ID_FACTURA AND ESTADO = 'PAGADO';
    SELECT TOTAL, ESTADO INTO total_factura, estado_actual
    FROM factura
    WHERE ID_FACTURA = NEW.FACTURA_ID_FACTURA;
    IF estado_actual <> 'ANULADA' THEN
        IF total_pagado >= total_factura THEN
            UPDATE factura SET ESTADO = 'PAGADA' WHERE ID_FACTURA = NEW.FACTURA_ID_FACTURA;
        ELSE
            UPDATE factura SET ESTADO = 'EMITIDA' WHERE ID_FACTURA = NEW.FACTURA_ID_FACTURA;
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `pago` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tg_factura_actualizar_estado_pago_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `tg_factura_actualizar_estado_pago_update` AFTER UPDATE ON `pago` FOR EACH ROW BEGIN
    DECLARE total_pagado DECIMAL(10,2);
    DECLARE total_factura DECIMAL(10,2);
    DECLARE estado_actual VARCHAR(20);
    SELECT COALESCE(SUM(MONTO), 0) INTO total_pagado
    FROM pago
    WHERE FACTURA_ID_FACTURA = NEW.FACTURA_ID_FACTURA AND ESTADO = 'PAGADO';
    SELECT TOTAL, ESTADO INTO total_factura, estado_actual
    FROM factura
    WHERE ID_FACTURA = NEW.FACTURA_ID_FACTURA;
    IF estado_actual <> 'ANULADA' THEN
        IF total_pagado >= total_factura THEN
            UPDATE factura SET ESTADO = 'PAGADA' WHERE ID_FACTURA = NEW.FACTURA_ID_FACTURA;
        ELSE
            UPDATE factura SET ESTADO = 'EMITIDA' WHERE ID_FACTURA = NEW.FACTURA_ID_FACTURA;
        END IF;
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `usuarios` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tg_sanitizar_usuario_before_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `tg_sanitizar_usuario_before_insert` BEFORE INSERT ON `usuarios` FOR EACH ROW BEGIN
    
    IF NEW.ROLES_ID_ROLES IS NULL OR NEW.ROLES_ID_ROLES = 0 THEN
        SET NEW.ROLES_ID_ROLES = 3;
    END IF;
    
    IF NEW.CORREO IS NOT NULL THEN
        SET NEW.CORREO = LOWER(TRIM(NEW.CORREO));
    END IF;
    
    IF NEW.NOMBRES IS NOT NULL THEN
        SET NEW.NOMBRES = TRIM(NEW.NOMBRES);
    END IF;
    IF NEW.APELLIDOS IS NOT NULL THEN
        SET NEW.APELLIDOS = TRIM(NEW.APELLIDOS);
    END IF;
    
    IF NEW.CORREO IS NOT NULL AND NEW.CORREO != '' AND NEW.CORREO NOT LIKE '%@%.%' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El correo electrónico no tiene un formato válido.';
    END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `usuarios` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tg_sanitizar_usuario_before_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `tg_sanitizar_usuario_before_update` BEFORE UPDATE ON `usuarios` FOR EACH ROW BEGIN
    IF NEW.CORREO IS NOT NULL THEN
        SET NEW.CORREO = LOWER(TRIM(NEW.CORREO));
    END IF;
    IF NEW.NOMBRES IS NOT NULL THEN
        SET NEW.NOMBRES = TRIM(NEW.NOMBRES);
    END IF;
    IF NEW.APELLIDOS IS NOT NULL THEN
        SET NEW.APELLIDOS = TRIM(NEW.APELLIDOS);
    END IF;
    IF NEW.CORREO IS NOT NULL AND NEW.CORREO != '' AND NEW.CORREO NOT LIKE '%@%.%' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El correo electrónico no tiene un formato válido.';
    END IF;
END */$$


DELIMITER ;

/* Procedure structure for procedure `sp_actualizar_citas_vencidas` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_actualizar_citas_vencidas` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_citas_vencidas`()
BEGIN
    UPDATE `horario_disponibilidad` h
    INNER JOIN `cita` c ON h.`ID_HORARIO` = c.`HORARIO_DISPONIBILIDAD_ID_HORARIO`
    SET h.`ESTADO` = 'Disponible'
    WHERE c.`ESTADO_CITA_ID` = 1
      AND c.`FECHA_HORA` < DATE_SUB(NOW(), INTERVAL 6 HOUR)
      AND h.`ESTADO` = 'Ocupado';
    UPDATE `cita`
    SET `ESTADO_CITA_ID` = 4
    WHERE `ESTADO_CITA_ID` = 1
      AND `FECHA_HORA` < DATE_SUB(NOW(), INTERVAL 6 HOUR);
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_agendar_cita` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_agendar_cita` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_agendar_cita`(IN `p_paciente_id` INT, IN `p_odontologo_id` INT, IN `p_horario_id` INT, IN `p_procedimiento_id` INT, IN `p_motivo` VARCHAR(200), OUT `p_nueva_cita_id` INT)
BEGIN
    DECLARE v_estado_horario VARCHAR(20);
    DECLARE v_fecha_horario DATE;
    DECLARE v_hora_inicio TIME;
    DECLARE v_fecha_hora DATETIME;
    SELECT ESTADO, FECHA, HORA_INICIO INTO v_estado_horario, v_fecha_horario, v_hora_inicio
    FROM horario_disponibilidad
    WHERE ID_HORARIO = p_horario_id;
    IF v_estado_horario IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El horario de disponibilidad especificado no existe.';
    ELSEIF v_estado_horario <> 'Disponible' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El horario seleccionado ya no se encuentra disponible.';
    END IF;
    SET v_fecha_hora = CONCAT(v_fecha_horario, ' ', v_hora_inicio);
    INSERT INTO cita (
        PACIENTE_ID_PACIENTE,
        ODONTOLOGO_ID_ODONTOLOGO,
        FECHA_HORA,
        HORARIO_DISPONIBILIDAD_ID_HORARIO,
        ESTADO_CITA_ID,
        MOTIVO,
        FECHA_CREACION
    ) VALUES (
        p_paciente_id,
        p_odontologo_id,
        v_fecha_hora,
        p_horario_id,
        1,
        LEFT(COALESCE(p_motivo, 'Consulta odontológica'), 200),
        NOW()
    );
    SET p_nueva_cita_id = LAST_INSERT_ID();
    IF p_procedimiento_id IS NOT NULL AND p_procedimiento_id > 0 THEN
        INSERT INTO cita_has_procedimiento (
            CITA_ID_CITA,
            PROCEDIMIENTO_ID_PROCEDIMIENTO
        ) VALUES (
            p_nueva_cita_id,
            p_procedimiento_id
        );
    END IF;
    UPDATE horario_disponibilidad SET ESTADO = 'Ocupado' WHERE ID_HORARIO = p_horario_id;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_cancelar_cita` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_cancelar_cita` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cancelar_cita`(IN `p_cita_id` INT, IN `p_motivo_cancelacion` VARCHAR(255))
BEGIN
    DECLARE v_existe INT DEFAULT 0;
    DECLARE v_horario_id INT DEFAULT NULL;
    SELECT COUNT(*) INTO v_existe
    FROM cita
    WHERE ID_CITA = p_cita_id;
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La cita especificada no existe.';
    END IF;
    SELECT HORARIO_DISPONIBILIDAD_ID_HORARIO INTO v_horario_id
    FROM cita
    WHERE ID_CITA = p_cita_id
    LIMIT 1;
    UPDATE cita 
    SET ESTADO_CITA_ID = 3,
        FECHA_CANCELACION = NOW(),
        MOTIVO_CANCELACION = p_motivo_cancelacion
    WHERE ID_CITA = p_cita_id;
    IF v_horario_id IS NOT NULL THEN
        UPDATE horario_disponibilidad 
        SET ESTADO = 'Disponible' 
        WHERE ID_HORARIO = v_horario_id;
    END IF;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_registrar_atencion_hc` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_registrar_atencion_hc` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_atencion_hc`(IN `p_historia_clinica_id` INT, IN `p_procedimiento_id` INT, IN `p_cantidad` INT, IN `p_precio_aplicado` DECIMAL(10,0), IN `p_observaciones` VARCHAR(200), OUT `p_nuevo_registro_id` INT)
BEGIN
    DECLARE v_costo_base DECIMAL(10,0);
    DECLARE v_precio_final DECIMAL(10,0);
    IF (SELECT COUNT(*) FROM historia_clinica WHERE ID_HISTORIA_CLINICA = p_historia_clinica_id) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La historia clínica especificada no existe.';
    END IF;
    SELECT COSTO INTO v_costo_base FROM procedimientos WHERE ID_PROCEDIMIENTO = p_procedimiento_id;
    IF v_costo_base IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El procedimiento especificado no existe.';
    END IF;
    IF p_precio_aplicado IS NOT NULL AND p_precio_aplicado > 0 THEN
        SET v_precio_final = p_precio_aplicado;
    ELSE
        SET v_precio_final = v_costo_base;
    END IF;
    IF p_cantidad IS NULL OR p_cantidad <= 0 THEN
        SET p_cantidad = 1;
    END IF;
    INSERT INTO historia_clinica_has_procedimientos (
        HISTORIA_CLINICA_ID_HISTORIA_CLINICA,
        PROCEDIMIENTOS_ID_PROCEDIMIENTO,
        CANTIDAD,
        PRECIO_APLICADO,
        OBSERVACIONES
    ) VALUES (
        p_historia_clinica_id,
        p_procedimiento_id,
        p_cantidad,
        v_precio_final,
        LEFT(COALESCE(p_observaciones, ''), 200)
    );
    SET p_nuevo_registro_id = LAST_INSERT_ID();
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_registrar_menor_con_acudiente` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_registrar_menor_con_acudiente` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_menor_con_acudiente`(IN `p_nombres` VARCHAR(45), IN `p_apellidos` VARCHAR(45), IN `p_tipo_documento` ENUM('R.C','T.I','C.C','C.E','PPT'), IN `p_numero_documento` BIGINT, IN `p_genero` ENUM('MASCULINO','FEMENINO','OTRO'), IN `p_fecha_nacimiento` DATE, IN `p_rh` ENUM('A+','B+','AB+','O+','A-','B-','AB-','O-'), IN `p_eps_id` INT, IN `p_usuario_acudiente_id` INT, IN `p_parentesco` ENUM('Madre','Padre','Abuelo/a','Tío/a','Tutor Legal','Otro'), IN `p_contacto_principal` ENUM('Si','No'), IN `p_autoriza_tratamientos` ENUM('Si','No'), OUT `p_nuevo_usuario_id` INT, OUT `p_nuevo_paciente_id` INT)
BEGIN
    DECLARE v_existe_doc INT;
    DECLARE v_existe_acudiente INT;
    DECLARE v_eps_final INT;
    SELECT COUNT(*) INTO v_existe_acudiente FROM usuarios WHERE ID_USUARIOS = p_usuario_acudiente_id;
    IF v_existe_acudiente = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario acudiente especificado no existe en el sistema.';
    END IF;
    SELECT COUNT(*) INTO v_existe_doc FROM usuarios WHERE NUMERO_DOCUMENTO = p_numero_documento;
    IF v_existe_doc > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El número de documento del menor ya se encuentra registrado.';
    END IF;
    SET v_eps_final = COALESCE(
        NULLIF(p_eps_id, 0),
        (SELECT ID_EPS FROM eps WHERE ESTADO = 'ACTIVO' ORDER BY ID_EPS ASC LIMIT 1),
        1
    );
    INSERT INTO usuarios (
        ROLES_ID_ROLES,
        NOMBRES,
        APELLIDOS,
        TIPO_DOCUMENTO,
        NUMERO_DOCUMENTO,
        TELEFONO,
        CORREO,
        GENERO,
        FECHA_NACIMIENTO,
        RH,
        `CONTRASEÑA`,
        ESTADO
    ) VALUES (
        3,
        TRIM(p_nombres),
        TRIM(p_apellidos),
        p_tipo_documento,
        p_numero_documento,
        NULL,
        NULL,
        p_genero,
        p_fecha_nacimiento,
        p_rh,
        NULL,
        'Activo'
    );
    SET p_nuevo_usuario_id = LAST_INSERT_ID();
    INSERT INTO paciente (
        USUARIOS_ID_USUARIOS,
        EPS_ID_EPS,
        NOMBRE_CONTACTO_EMERGENCIA,
        NUMERO_CONTACTO_EMERGENCIA
    ) VALUES (
        p_nuevo_usuario_id,
        v_eps_final,
        '',
        ''
    );
    SET p_nuevo_paciente_id = LAST_INSERT_ID();
    INSERT INTO paciente_has_acudiente (
        PACIENTE_ID_PACIENTE,
        USUARIOS_ID_ACUDIENTE,
        PARENTESCO,
        CONTACTO_PRINCIPAL,
        AUTORIZA_TRATAMIENTOS
    ) VALUES (
        p_nuevo_paciente_id,
        p_usuario_acudiente_id,
        COALESCE(p_parentesco, 'Madre'),
        COALESCE(p_contacto_principal, 'Si'),
        COALESCE(p_autoriza_tratamientos, 'Si')
    );
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_registrar_notificacion_web` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_registrar_notificacion_web` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_notificacion_web`(IN `p_tipo_id` INT, IN `p_usuario_id` INT, IN `p_mensaje` TEXT, OUT `p_insertado` TINYINT)
BEGIN
    DECLARE v_activas TINYINT DEFAULT 1;
    SET p_insertado = 0;
    IF p_usuario_id IS NOT NULL AND p_usuario_id > 0 THEN
        SELECT COALESCE(NOTIFICACIONES_ACTIVAS, 1)
        INTO v_activas
        FROM usuarios
        WHERE ID_USUARIOS = p_usuario_id
        LIMIT 1;
    END IF;
    IF v_activas = 1 THEN
        INSERT INTO notificaciones (
            TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION,
            USUARIOS_ID_USUARIOS,
            MENSAJE,
            FECHA_ENVIO,
            ESTADO
        ) VALUES (
            p_tipo_id,
            p_usuario_id,
            p_mensaje,
            NOW(),
            'NO_LEIDA'
        );
        SET p_insertado = 1;
    END IF;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_registrar_paciente_completo` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_registrar_paciente_completo` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_paciente_completo`(IN `p_nombres` VARCHAR(45), IN `p_apellidos` VARCHAR(45), IN `p_tipo_documento` ENUM('R.C','T.I','C.C','C.E','PPT'), IN `p_numero_documento` BIGINT, IN `p_telefono` VARCHAR(45), IN `p_correo` VARCHAR(200), IN `p_genero` ENUM('MASCULINO','FEMENINO','OTRO'), IN `p_contrasena_hash` VARCHAR(250), IN `p_eps_id` INT, IN `p_contacto_emergencia` VARCHAR(45), IN `p_tel_emergencia` VARCHAR(45), OUT `p_nuevo_usuario_id` INT, OUT `p_nuevo_paciente_id` INT)
BEGIN
    DECLARE v_existe_correo INT;
    DECLARE v_existe_doc INT;
    DECLARE v_eps_final INT;
    SELECT COUNT(*) INTO v_existe_correo FROM usuarios WHERE CORREO = LOWER(TRIM(p_correo));
    IF v_existe_correo > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El correo electrónico ya se encuentra registrado.';
    END IF;
    SELECT COUNT(*) INTO v_existe_doc FROM usuarios WHERE NUMERO_DOCUMENTO = p_numero_documento;
    IF v_existe_doc > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El número de documento ya se encuentra registrado.';
    END IF;
    SET v_eps_final = COALESCE(
        NULLIF(p_eps_id, 0),
        (SELECT ID_EPS FROM eps WHERE ESTADO = 'ACTIVO' ORDER BY ID_EPS ASC LIMIT 1),
        1
    );
    INSERT INTO usuarios (
        ROLES_ID_ROLES,
        NOMBRES,
        APELLIDOS,
        TIPO_DOCUMENTO,
        NUMERO_DOCUMENTO,
        TELEFONO,
        CORREO,
        GENERO,
        `CONTRASEÑA`,
        ESTADO
    ) VALUES (
        3,
        TRIM(p_nombres),
        TRIM(p_apellidos),
        p_tipo_documento,
        p_numero_documento,
        NULLIF(TRIM(p_telefono), ''),
        LOWER(TRIM(p_correo)),
        p_genero,
        p_contrasena_hash,
        'Activo'
    );
    SET p_nuevo_usuario_id = LAST_INSERT_ID();
    INSERT INTO paciente (
        USUARIOS_ID_USUARIOS,
        EPS_ID_EPS,
        NOMBRE_CONTACTO_EMERGENCIA,
        NUMERO_CONTACTO_EMERGENCIA
    ) VALUES (
        p_nuevo_usuario_id,
        v_eps_final,
        COALESCE(p_contacto_emergencia, ''),
        COALESCE(p_tel_emergencia, '')
    );
    SET p_nuevo_paciente_id = LAST_INSERT_ID();
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_registrar_pago_factura` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_registrar_pago_factura` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_pago_factura`(IN `p_factura_id` INT, IN `p_monto` DECIMAL(10,0), IN `p_metodo` ENUM('EFECTIVO','TRANSFERENCIA','TARJETA'), OUT `p_nuevo_pago_id` INT)
BEGIN
    DECLARE v_total_factura DECIMAL(10,0);
    DECLARE v_estado_factura VARCHAR(20);
    DECLARE v_total_pagado DECIMAL(10,0) DEFAULT 0;
    SELECT TOTAL, ESTADO INTO v_total_factura, v_estado_factura FROM factura WHERE ID_FACTURA = p_factura_id;
    IF v_total_factura IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La factura especificada no existe.';
    END IF;
    IF v_estado_factura = 'ANULADA' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se pueden registrar pagos sobre una factura anulada.';
    END IF;
    IF p_monto <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El monto del abono debe ser mayor a cero.';
    END IF;
    SELECT COALESCE(SUM(MONTO), 0) INTO v_total_pagado
    FROM pago
    WHERE FACTURA_ID_FACTURA = p_factura_id AND ESTADO = 'PAGADO';
    IF (v_total_pagado + p_monto) > v_total_factura THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El monto supera el saldo pendiente de la factura.';
    END IF;
    INSERT INTO pago (
        FACTURA_ID_FACTURA,
        MONTO,
        FECHA_PAGO,
        METODO_PAGO,
        ESTADO
    ) VALUES (
        p_factura_id,
        p_monto,
        NOW(),
        p_metodo,
        'PAGADO'
    );
    SET p_nuevo_pago_id = LAST_INSERT_ID();
    SET v_total_pagado = v_total_pagado + p_monto;
    IF v_total_pagado >= v_total_factura THEN
        UPDATE factura SET ESTADO = 'PAGADA' WHERE ID_FACTURA = p_factura_id;
    ELSE
        UPDATE factura SET ESTADO = 'EMITIDA' WHERE ID_FACTURA = p_factura_id;
    END IF;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_reprogramar_cita` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_reprogramar_cita` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_reprogramar_cita`(IN `p_cita_id` INT, IN `p_nuevo_horario_id` INT, IN `p_motivo` VARCHAR(255))
BEGIN
    DECLARE v_existe_cita INT DEFAULT 0;
    DECLARE v_horario_anterior_id INT DEFAULT NULL;
    DECLARE v_fecha_anterior DATETIME DEFAULT NULL;
    DECLARE v_nuevo_odontologo_id INT DEFAULT NULL;
    DECLARE v_estado_nuevo VARCHAR(20);
    DECLARE v_fecha_horario DATE;
    DECLARE v_hora_inicio TIME;
    DECLARE v_nueva_fecha_hora DATETIME;
    DECLARE v_motivo_limpio VARCHAR(255);
    SELECT COUNT(*) INTO v_existe_cita FROM cita WHERE ID_CITA = p_cita_id;
    IF v_existe_cita = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La cita a reprogramar no existe.';
    END IF;
    SELECT HORARIO_DISPONIBILIDAD_ID_HORARIO, FECHA_HORA
    INTO v_horario_anterior_id, v_fecha_anterior
    FROM cita WHERE ID_CITA = p_cita_id LIMIT 1;
    SELECT ODONTOLOGO_ID_ODONTOLOGO, ESTADO, FECHA, HORA_INICIO
    INTO v_nuevo_odontologo_id, v_estado_nuevo, v_fecha_horario, v_hora_inicio
    FROM horario_disponibilidad WHERE ID_HORARIO = p_nuevo_horario_id;
    IF v_estado_nuevo IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El nuevo horario especificado no existe.';
    ELSEIF v_estado_nuevo <> 'Disponible' AND NOT (v_horario_anterior_id <=> p_nuevo_horario_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El nuevo horario seleccionado ya no está disponible.';
    END IF;
    SET v_nueva_fecha_hora = CONCAT(v_fecha_horario, ' ', v_hora_inicio);
    SET v_motivo_limpio = COALESCE(NULLIF(TRIM(p_motivo), ''), 'Reprogramación de cita');
    IF v_horario_anterior_id IS NOT NULL AND v_horario_anterior_id <> p_nuevo_horario_id THEN
        UPDATE horario_disponibilidad SET ESTADO = 'Disponible' WHERE ID_HORARIO = v_horario_anterior_id;
    END IF;
    UPDATE horario_disponibilidad SET ESTADO = 'Ocupado' WHERE ID_HORARIO = p_nuevo_horario_id;
    UPDATE cita 
    SET HORARIO_DISPONIBILIDAD_ID_HORARIO = p_nuevo_horario_id,
        ODONTOLOGO_ID_ODONTOLOGO = v_nuevo_odontologo_id,
        FECHA_HORA = v_nueva_fecha_hora,
        ESTADO_CITA_ID = 1,
        MOTIVO = LEFT(CONCAT(COALESCE(MOTIVO, ''), ' | Reprogramada: ', v_motivo_limpio), 200)
    WHERE ID_CITA = p_cita_id;
    INSERT INTO historial_reprogramacion_citas (
        CITA_ID_CITA,
        FECHA_HORA_ANTERIOR,
        FECHA_HORA_NUEVA,
        MOTIVO_CAMBIO,
        CREADO_EL
    ) VALUES (
        p_cita_id,
        v_fecha_anterior,
        v_nueva_fecha_hora,
        v_motivo_limpio,
        NOW()
    );
END */$$
DELIMITER ;

/*Table structure for table `odontologo_especialidad` */

DROP TABLE IF EXISTS `odontologo_especialidad`;

/*!50001 DROP VIEW IF EXISTS `odontologo_especialidad` */;
/*!50001 DROP TABLE IF EXISTS `odontologo_especialidad` */;

/*!50001 CREATE TABLE  `odontologo_especialidad`(
 `ID_ODONTOLOGO` int(11) ,
 `ID_ESPECIALIDAD` int(11) 
)*/;

/*Table structure for table `vista_antecedentes_medicos` */

DROP TABLE IF EXISTS `vista_antecedentes_medicos`;

/*!50001 DROP VIEW IF EXISTS `vista_antecedentes_medicos` */;
/*!50001 DROP TABLE IF EXISTS `vista_antecedentes_medicos` */;

/*!50001 CREATE TABLE  `vista_antecedentes_medicos`(
 `ID_PACIENTE` int(11) ,
 `ID_USUARIOS` int(11) ,
 `TIPO_DOCUMENTO` enum('R.C','T.I','C.C','C.E','PPT') ,
 `NUMERO_DOCUMENTO` bigint(20) ,
 `NOMBRES` varchar(45) ,
 `APELLIDOS` varchar(45) ,
 `PRIMER_NOMBRE` varchar(45) ,
 `PRIMER_APELLIDO` varchar(45) ,
 `NOMBRE_COMPLETO` varchar(91) ,
 `ID_CONDICION_MEDICA` int(11) ,
 `NOMBRE_CONDICION` varchar(100) ,
 `TIPO` enum('ENFERMEDAD','ALERGIA') ,
 `DESCRIPCION` varchar(200) ,
 `OBSERVACION` varchar(200) ,
 `ID_EPS` int(11) ,
 `NOMBRE_EPS` varchar(45) 
)*/;

/*Table structure for table `vista_citas` */

DROP TABLE IF EXISTS `vista_citas`;

/*!50001 DROP VIEW IF EXISTS `vista_citas` */;
/*!50001 DROP TABLE IF EXISTS `vista_citas` */;

/*!50001 CREATE TABLE  `vista_citas`(
 `ID_CITA` int(11) ,
 `PACIENTE_ID_PACIENTE` int(11) ,
 `ODONTOLOGO_ID_ODONTOLOGO` int(11) ,
 `HORARIO_DISPONIBILIDAD_ID_HORARIO` int(11) ,
 `FECHA_HORA` datetime ,
 `FECHA` date ,
 `HORA` time ,
 `MOTIVO` varchar(200) ,
 `ESTADO_CITA_ID` int(11) ,
 `ESTADO_CITA` varchar(50) ,
 `FECHA_CREACION` timestamp ,
 `FECHA_CANCELACION` datetime ,
 `MOTIVO_CANCELACION` varchar(255) ,
 `OBSERVACIONES_DOCTOR` text ,
 `RECOMENDACIONES` text ,
 `FECHA_ATENCION` datetime ,
 `ID_USUARIO_PACIENTE` int(11) ,
 `DOC_PACIENTE` bigint(20) ,
 `TIPO_DOC_PACIENTE` enum('R.C','T.I','C.C','C.E','PPT') ,
 `NOMBRE_PACIENTE` varchar(91) ,
 `TELEFONO_PACIENTE` varchar(45) ,
 `CORREO_PACIENTE` varchar(200) ,
 `ID_USUARIO_ODONTOLOGO` int(11) ,
 `NOMBRE_ODONTOLOGO` varchar(91) ,
 `TELEFONO_ODONTOLOGO` varchar(45) ,
 `CORREO_ODONTOLOGO` varchar(200) ,
 `ID_ESPECIALIDAD` int(11) ,
 `NOMBRE_ESPECIALIDAD` varchar(45) ,
 `ID_CONSULTORIO` int(11) ,
 `NOMBRE_CONSULTORIO` varchar(100) ,
 `UBICACION_CONSULTORIO` varchar(200) ,
 `ID_PROCEDIMIENTO` int(11) ,
 `NOMBRE_PROCEDIMIENTO` varchar(100) ,
 `FECHA_HORARIO` date ,
 `HORA_INICIO_HORARIO` time ,
 `HORA_FIN_HORARIO` time ,
 `JORNADA_HORARIO` enum('Mañana','Tarde','Jornada completa') 
)*/;

/*Table structure for table `vista_comisiones_odontologo` */

DROP TABLE IF EXISTS `vista_comisiones_odontologo`;

/*!50001 DROP VIEW IF EXISTS `vista_comisiones_odontologo` */;
/*!50001 DROP TABLE IF EXISTS `vista_comisiones_odontologo` */;

/*!50001 CREATE TABLE  `vista_comisiones_odontologo`(
 `ID_HISTORIA_CLINICA` int(11) ,
 `FECHA_REGISTRO` datetime ,
 `FECHA_FORMATEADA` varchar(10) ,
 `ID_ODONTOLOGO` int(11) ,
 `NOMBRE_ODONTOLOGO` varchar(91) ,
 `ID_PACIENTE` int(11) ,
 `NOMBRE_PACIENTE` varchar(91) ,
 `DOC_PACIENTE` bigint(20) ,
 `ID_PROCEDIMIENTO` int(11) ,
 `NOMBRE_PROCEDIMIENTO` varchar(100) ,
 `ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS` int(11) ,
 `CANTIDAD` int(11) ,
 `PRECIO_APLICADO` decimal(10,0) ,
 `TOTAL_PROCEDIMIENTO` decimal(20,0) ,
 `PORCENTAJE_COMISION` decimal(5,2) ,
 `GANANCIA_COMISION` decimal(29,6) 
)*/;

/*Table structure for table `vista_consultorios` */

DROP TABLE IF EXISTS `vista_consultorios`;

/*!50001 DROP VIEW IF EXISTS `vista_consultorios` */;
/*!50001 DROP TABLE IF EXISTS `vista_consultorios` */;

/*!50001 CREATE TABLE  `vista_consultorios`(
 `ID_CONSULTORIO` int(11) ,
 `NOMBRE` varchar(100) ,
 `UBICACION` varchar(200) ,
 `DESCRIPCION` text ,
 `ESTADO` enum('DISPONIBLE','ASIGNADO','MANTENIMIENTO') ,
 `ID_ODONTOLOGO` int(11) ,
 `doctor_nombre` varchar(91) ,
 `ID_ESPECIALIDAD` int(11) ,
 `especialidad` varchar(45) 
)*/;

/*Table structure for table `vista_contactos_landing` */

DROP TABLE IF EXISTS `vista_contactos_landing`;

/*!50001 DROP VIEW IF EXISTS `vista_contactos_landing` */;
/*!50001 DROP TABLE IF EXISTS `vista_contactos_landing` */;

/*!50001 CREATE TABLE  `vista_contactos_landing`(
 `ID_CONTACTO` int(11) ,
 `NOMBRE_COMPLETO` varchar(120) ,
 `CORREO` varchar(150) ,
 `TELEFONO` varchar(20) ,
 `SERVICIO_INTERES` varchar(100) ,
 `MENSAJE` text ,
 `FECHA_REGISTRO` datetime ,
 `ESTADO` enum('PENDIENTE','CONTACTADO','DESCARTADO') ,
 `OBSERVACIONES` varchar(255) ,
 `ID_ADMIN_GESTION` int(11) ,
 `NOMBRE_ADMIN_GESTION` varchar(91) ,
 `ID_PROCEDIMIENTO` int(11) ,
 `NOMBRE_PROCEDIMIENTO` varchar(100) 
)*/;

/*Table structure for table `vista_facturacion` */

DROP TABLE IF EXISTS `vista_facturacion`;

/*!50001 DROP VIEW IF EXISTS `vista_facturacion` */;
/*!50001 DROP TABLE IF EXISTS `vista_facturacion` */;

/*!50001 CREATE TABLE  `vista_facturacion`(
 `ID_FACTURA` int(11) ,
 `FECHA_EMISION` datetime ,
 `TOTAL` decimal(10,0) ,
 `ESTADO_FACTURA` enum('EMITIDA','PAGADA','ANULADA') ,
 `HISTORIA_CLINICA_ID_HISTORIA_CLINICA` int(11) ,
 `CITA_ID_CITA` int(11) ,
 `PACIENTE_ID_PACIENTE` bigint(11) ,
 `ID_USUARIO_PACIENTE` int(11) ,
 `DOC_PACIENTE` bigint(20) ,
 `NOMBRE_PACIENTE` varchar(91) ,
 `CORREO_PACIENTE` varchar(200) ,
 `TELEFONO_PACIENTE` varchar(45) ,
 `ID_ODONTOLOGO` int(11) ,
 `NOMBRE_ODONTOLOGO` varchar(91) ,
 `PROCEDIMIENTO` mediumtext ,
 `TOTAL_PAGADO` decimal(32,0) ,
 `SALDO_PENDIENTE` decimal(33,0) ,
 `CANTIDAD_PAGOS` bigint(21) ,
 `FECHA_ULTIMO_PAGO` varchar(6) ,
 `ULTIMO_METODO_PAGO` varchar(341) 
)*/;

/*Table structure for table `vista_facturas_admin` */

DROP TABLE IF EXISTS `vista_facturas_admin`;

/*!50001 DROP VIEW IF EXISTS `vista_facturas_admin` */;
/*!50001 DROP TABLE IF EXISTS `vista_facturas_admin` */;

/*!50001 CREATE TABLE  `vista_facturas_admin`(
 `id` int(11) ,
 `fecha_emision` datetime ,
 `total` decimal(10,0) ,
 `estado` varchar(7) ,
 `paciente` varchar(91) ,
 `documento` varchar(20) ,
 `correo` varchar(200) ,
 `telefono` varchar(45) ,
 `tratamiento` mediumtext ,
 `pagado` decimal(32,0) ,
 `PACIENTE_ID_PACIENTE` bigint(11) 
)*/;

/*Table structure for table `vista_historia_clinica` */

DROP TABLE IF EXISTS `vista_historia_clinica`;

/*!50001 DROP VIEW IF EXISTS `vista_historia_clinica` */;
/*!50001 DROP TABLE IF EXISTS `vista_historia_clinica` */;

/*!50001 CREATE TABLE  `vista_historia_clinica`(
 `ID_HISTORIA_CLINICA` int(11) ,
 `FECHA_REGISTRO` datetime ,
 `MOTIVO_CONSULTA` text ,
 `DIAGNOSTICO` text ,
 `TRATAMIENTO` text ,
 `OBSERVACIONES` text ,
 `CITA_ID_CITA` int(11) ,
 `ID_PACIENTE` int(11) ,
 `DOC_PACIENTE` bigint(20) ,
 `NOMBRE_PACIENTE` varchar(91) ,
 `FECHA_NACIMIENTO` date ,
 `GENERO` enum('MASCULINO','FEMENINO','OTRO') ,
 `RH` enum('A+','B+','AB+','O+','A-','B-','AB-','O-') ,
 `TELEFONO_PACIENTE` varchar(45) ,
 `NOMBRE_EPS` varchar(45) ,
 `ID_ODONTOLOGO` int(11) ,
 `NOMBRE_ODONTOLOGO` varchar(91) ,
 `NOMBRE_ESPECIALIDAD` varchar(45) 
)*/;

/*Table structure for table `vista_historial_consultorio` */

DROP TABLE IF EXISTS `vista_historial_consultorio`;

/*!50001 DROP VIEW IF EXISTS `vista_historial_consultorio` */;
/*!50001 DROP TABLE IF EXISTS `vista_historial_consultorio` */;

/*!50001 CREATE TABLE  `vista_historial_consultorio`(
 `ID_HISTORIAL` int(11) ,
 `CONSULTORIO_ID_CONSULTORIO` int(11) ,
 `consultorio_nombre` varchar(100) ,
 `ODONTOLOGO_ID_ODONTOLOGO` int(11) ,
 `doctor_nombre` varchar(91) ,
 `FECHA_ASIGNACION` datetime ,
 `FECHA_DESASIGNACION` datetime ,
 `MOTIVO_CAMBIO` varchar(255) 
)*/;

/*Table structure for table `vista_horarios_con_citas` */

DROP TABLE IF EXISTS `vista_horarios_con_citas`;

/*!50001 DROP VIEW IF EXISTS `vista_horarios_con_citas` */;
/*!50001 DROP TABLE IF EXISTS `vista_horarios_con_citas` */;

/*!50001 CREATE TABLE  `vista_horarios_con_citas`(
 `ID_HORARIO` int(11) ,
 `ODONTOLOGO_ID_ODONTOLOGO` int(11) ,
 `HORA_INICIO` time ,
 `HORA_FIN` time ,
 `JORNADA` varchar(16) ,
 `FECHA` date ,
 `ESTADO` varchar(12) ,
 `NOMBRES` varchar(45) ,
 `APELLIDOS` varchar(45) ,
 `CONSULTORIO` varchar(100) ,
 `NOMBRE_PACIENTE` varchar(45) ,
 `APELLIDO_PACIENTE` varchar(45) ,
 `ID_PROCEDIMIENTO` int(11) ,
 `NOMBRE_PROCEDIMIENTO` varchar(100) ,
 `DESCANSO_INICIO` time ,
 `DESCANSO_FIN` time 
)*/;

/*Table structure for table `vista_horarios_disponibilidad` */

DROP TABLE IF EXISTS `vista_horarios_disponibilidad`;

/*!50001 DROP VIEW IF EXISTS `vista_horarios_disponibilidad` */;
/*!50001 DROP TABLE IF EXISTS `vista_horarios_disponibilidad` */;

/*!50001 CREATE TABLE  `vista_horarios_disponibilidad`(
 `ID_HORARIO` int(11) ,
 `ODONTOLOGO_ID_ODONTOLOGO` int(11) ,
 `FECHA` date ,
 `HORA_INICIO` time ,
 `HORA_FIN` time ,
 `ESTADO_HORARIO` enum('Disponible','Ocupado','Inactivo','No utilizado') ,
 `JORNADA` enum('Mañana','Tarde','Jornada completa') ,
 `DIA` set('lunes','martes','miercoles','jueves','viernes','sabado','domingo') ,
 `DESCANSO_INICIO` time ,
 `DESCANSO_FIN` time ,
 `ID_USUARIO_ODONTOLOGO` int(11) ,
 `NOMBRE_ODONTOLOGO` varchar(91) ,
 `ID_ESPECIALIDAD` int(11) ,
 `NOMBRE_ESPECIALIDAD` varchar(45) ,
 `ID_CONSULTORIO` int(11) ,
 `NOMBRE_CONSULTORIO` varchar(100) ,
 `UBICACION_CONSULTORIO` varchar(200) 
)*/;

/*Table structure for table `vista_ingresos_procedimiento` */

DROP TABLE IF EXISTS `vista_ingresos_procedimiento`;

/*!50001 DROP VIEW IF EXISTS `vista_ingresos_procedimiento` */;
/*!50001 DROP TABLE IF EXISTS `vista_ingresos_procedimiento` */;

/*!50001 CREATE TABLE  `vista_ingresos_procedimiento`(
 `ID_PAGO` int(11) ,
 `FECHA_PAGO` datetime ,
 `MONTO_PAGO` decimal(10,0) ,
 `ESTADO_PAGO` enum('PENDIENTE','PAGADO','ANULADO') ,
 `ID_FACTURA` int(11) ,
 `TOTAL_FACTURA` decimal(10,0) ,
 `ID_PROCEDIMIENTO` int(11) ,
 `NOMBRE_PROCEDIMIENTO` varchar(100) ,
 `PRECIO_APLICADO` decimal(10,0) ,
 `CANTIDAD` int(11) ,
 `INGRESO_PROPORCIONAL` decimal(34,4) 
)*/;

/*Table structure for table `vista_notificaciones` */

DROP TABLE IF EXISTS `vista_notificaciones`;

/*!50001 DROP VIEW IF EXISTS `vista_notificaciones` */;
/*!50001 DROP TABLE IF EXISTS `vista_notificaciones` */;

/*!50001 CREATE TABLE  `vista_notificaciones`(
 `ID_NOTIFICACIONES` int(11) ,
 `FECHA_ENVIO` datetime ,
 `MENSAJE` text ,
 `ESTADO` enum('NO_LEIDA','LEIDA') ,
 `FECHA_LECTURA` datetime ,
 `ID_TIPO_NOTIFICACION` int(11) ,
 `TIPO_NOTIFICACION` varchar(100) ,
 `TIPO_NOMBRE` varchar(100) ,
 `ROL_DESTINO` int(11) ,
 `USUARIOS_ID_USUARIOS` int(11) ,
 `ID_USUARIOS` int(11) ,
 `NUMERO_DOCUMENTO` bigint(20) ,
 `NOMBRE_USUARIO` varchar(91) ,
 `CORREO` varchar(200) ,
 `TELEFONO` varchar(45) ,
 `ID_ROLES` int(11) ,
 `ROL_NOMBRE` varchar(45) 
)*/;

/*Table structure for table `vista_odontograma` */

DROP TABLE IF EXISTS `vista_odontograma`;

/*!50001 DROP VIEW IF EXISTS `vista_odontograma` */;
/*!50001 DROP TABLE IF EXISTS `vista_odontograma` */;

/*!50001 CREATE TABLE  `vista_odontograma`(
 `ID_ODONTOGRAMA_REGISTRO` int(11) ,
 `PACIENTE_ID_PACIENTE` int(11) ,
 `ODONTOLOGO_ID_ODONTOLOGO` int(11) ,
 `PROCEDIMIENTO_ID_PROCEDIMIENTO` int(11) ,
 `PIEZA_DENTAL` int(11) ,
 `FECHA_REGISTRO` date ,
 `FECHA_FORMATEADA` varchar(40) ,
 `FECHA` date ,
 `ESTADO_DIENTE` enum('PENDIENTE','HECHO','CANCELADO') ,
 `NOTAS` text ,
 `NOMBRE_PROCEDIMIENTO` varchar(100) ,
 `COSTO_PROCEDIMIENTO` decimal(10,0) ,
 `NOMBRE_PACIENTE` varchar(91) ,
 `DOC_PACIENTE` bigint(20) ,
 `NOMBRE_DOCTOR` varchar(91) 
)*/;

/*Table structure for table `vista_odontologos` */

DROP TABLE IF EXISTS `vista_odontologos`;

/*!50001 DROP VIEW IF EXISTS `vista_odontologos` */;
/*!50001 DROP TABLE IF EXISTS `vista_odontologos` */;

/*!50001 CREATE TABLE  `vista_odontologos`(
 `ID_ODONTOLOGO` int(11) ,
 `ID_USUARIOS` int(11) ,
 `NUMERO_DOCUMENTO` bigint(20) ,
 `TIPO_DOCUMENTO` enum('R.C','T.I','C.C','C.E','PPT') ,
 `NOMBRES` varchar(45) ,
 `APELLIDOS` varchar(45) ,
 `PRIMER_NOMBRE` varchar(45) ,
 `PRIMER_APELLIDO` varchar(45) ,
 `NOMBRE_COMPLETO` varchar(91) ,
 `TELEFONO` varchar(45) ,
 `CORREO` varchar(200) ,
 `DIRECCION` varchar(60) ,
 `GENERO` enum('MASCULINO','FEMENINO','OTRO') ,
 `FECHA_NACIMIENTO` date ,
 `ESTADO_USUARIO` enum('Activo','Inactivo') ,
 `NOTIFICACIONES_ACTIVAS` tinyint(1) ,
 `ID_ESPECIALIDAD` int(11) ,
 `NOMBRE_ESPECIALIDAD` varchar(45) ,
 `especialidad_id` int(11) ,
 `especialidad_nombre` varchar(45) ,
 `ID_CONSULTORIO` int(11) ,
 `CONSULTORIO` int(11) ,
 `NOMBRE_CONSULTORIO` varchar(100) ,
 `UBICACION_CONSULTORIO` varchar(200) ,
 `ID_ROLES` int(11) ,
 `ROL_NOMBRE` varchar(45) 
)*/;

/*Table structure for table `vista_pacientes` */

DROP TABLE IF EXISTS `vista_pacientes`;

/*!50001 DROP VIEW IF EXISTS `vista_pacientes` */;
/*!50001 DROP TABLE IF EXISTS `vista_pacientes` */;

/*!50001 CREATE TABLE  `vista_pacientes`(
 `ID_PACIENTE` int(11) ,
 `ID_USUARIOS` int(11) ,
 `NUMERO_DOCUMENTO` bigint(20) ,
 `TIPO_DOCUMENTO` enum('R.C','T.I','C.C','C.E','PPT') ,
 `NOMBRES` varchar(45) ,
 `APELLIDOS` varchar(45) ,
 `PRIMER_NOMBRE` varchar(45) ,
 `PRIMER_APELLIDO` varchar(45) ,
 `NOMBRE_COMPLETO` varchar(91) ,
 `TELEFONO` varchar(45) ,
 `CORREO` varchar(200) ,
 `DIRECCION` varchar(60) ,
 `GENERO` enum('MASCULINO','FEMENINO','OTRO') ,
 `FECHA_NACIMIENTO` date ,
 `EDAD` bigint(21) ,
 `ES_MENOR` int(1) ,
 `RH` enum('A+','B+','AB+','O+','A-','B-','AB-','O-') ,
 `ESTADO_USUARIO` enum('Activo','Inactivo') ,
 `NOTIFICACIONES_ACTIVAS` tinyint(1) ,
 `NOMBRE_CONTACTO_EMERGENCIA` varchar(45) ,
 `NUMERO_CONTACTO_EMERGENCIA` varchar(45) ,
 `ID_EPS` int(11) ,
 `NOMBRE_EPS` varchar(45) ,
 `ID_ROLES` int(11) ,
 `ROL_NOMBRE` varchar(45) 
)*/;

/*Table structure for table `vista_procedimientos_hc` */

DROP TABLE IF EXISTS `vista_procedimientos_hc`;

/*!50001 DROP VIEW IF EXISTS `vista_procedimientos_hc` */;
/*!50001 DROP TABLE IF EXISTS `vista_procedimientos_hc` */;

/*!50001 CREATE TABLE  `vista_procedimientos_hc`(
 `ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS` int(11) ,
 `HISTORIA_CLINICA_ID_HISTORIA_CLINICA` int(11) ,
 `PROCEDIMIENTOS_ID_PROCEDIMIENTO` int(11) ,
 `NOMBRE_PROCEDIMIENTO` varchar(100) ,
 `DESCRIPCION_PROCEDIMIENTO` varchar(200) ,
 `COSTO_PROCEDIMIENTO` decimal(10,0) ,
 `TIEMPO_ESTIMADO_PROCEDIMIENTO` int(11) ,
 `CANTIDAD` int(11) ,
 `PRECIO_APLICADO` decimal(10,0) ,
 `SUBTOTAL` decimal(20,0) ,
 `OBSERVACIONES` varchar(200) ,
 `FECHA_REGISTRO` datetime ,
 `MOTIVO_CONSULTA` text ,
 `DIAGNOSTICO` text ,
 `TRATAMIENTO` text ,
 `ID_PACIENTE` int(11) ,
 `NOMBRE_PACIENTE` varchar(91) ,
 `DOC_PACIENTE` bigint(20) ,
 `TELEFONO_PACIENTE` varchar(45) ,
 `FECHA_NACIMIENTO` date ,
 `ODONTOLOGO_ID_ODONTOLOGO` int(11) ,
 `NOMBRE_ODONTOLOGO` varchar(91) 
)*/;

/*Table structure for table `vista_usuarios` */

DROP TABLE IF EXISTS `vista_usuarios`;

/*!50001 DROP VIEW IF EXISTS `vista_usuarios` */;
/*!50001 DROP TABLE IF EXISTS `vista_usuarios` */;

/*!50001 CREATE TABLE  `vista_usuarios`(
 `ID_USUARIOS` int(11) ,
 `NUMERO_DOCUMENTO` bigint(20) ,
 `TIPO_DOCUMENTO` enum('R.C','T.I','C.C','C.E','PPT') ,
 `NOMBRES` varchar(45) ,
 `APELLIDOS` varchar(45) ,
 `PRIMER_NOMBRE` varchar(45) ,
 `PRIMER_APELLIDO` varchar(45) ,
 `NOMBRE_COMPLETO` varchar(91) ,
 `CORREO` varchar(200) ,
 `TELEFONO` varchar(45) ,
 `DIRECCION` varchar(60) ,
 `GENERO` enum('MASCULINO','FEMENINO','OTRO') ,
 `FECHA_NACIMIENTO` date ,
 `RH` enum('A+','B+','AB+','O+','A-','B-','AB-','O-') ,
 `ESTADO` enum('Activo','Inactivo') ,
 `ROLES_ID_ROLES` int(11) ,
 `ROL_NOMBRE` varchar(45) ,
 `ID_ODONTOLOGO` int(11) ,
 `ID_PACIENTE` int(11) ,
 `ID_ESPECIALIDAD` int(11) ,
 `NOMBRE_ESPECIALIDAD` varchar(45) ,
 `ID_CONSULTORIO` int(11) ,
 `NOMBRE_CONSULTORIO` varchar(100) 
)*/;

/*View structure for view odontologo_especialidad */

/*!50001 DROP TABLE IF EXISTS `odontologo_especialidad` */;
/*!50001 DROP VIEW IF EXISTS `odontologo_especialidad` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `odontologo_especialidad` AS select `odontologo_has_especialidad`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ID_ODONTOLOGO`,`odontologo_has_especialidad`.`ESPECIALIDAD_ID_ESPECIALIDAD` AS `ID_ESPECIALIDAD` from `odontologo_has_especialidad` */;

/*View structure for view vista_antecedentes_medicos */

/*!50001 DROP TABLE IF EXISTS `vista_antecedentes_medicos` */;
/*!50001 DROP VIEW IF EXISTS `vista_antecedentes_medicos` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_antecedentes_medicos` AS select `p`.`ID_PACIENTE` AS `ID_PACIENTE`,`u`.`ID_USUARIOS` AS `ID_USUARIOS`,`u`.`TIPO_DOCUMENTO` AS `TIPO_DOCUMENTO`,`u`.`NUMERO_DOCUMENTO` AS `NUMERO_DOCUMENTO`,`u`.`NOMBRES` AS `NOMBRES`,`u`.`APELLIDOS` AS `APELLIDOS`,`u`.`NOMBRES` AS `PRIMER_NOMBRE`,`u`.`APELLIDOS` AS `PRIMER_APELLIDO`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `NOMBRE_COMPLETO`,`cm`.`ID_CONDICION_MEDICA` AS `ID_CONDICION_MEDICA`,`cm`.`NOMBRE_CONDICION` AS `NOMBRE_CONDICION`,`cm`.`TIPO` AS `TIPO`,`cm`.`DESCRIPCION` AS `DESCRIPCION`,`phcd`.`OBSERVACION` AS `OBSERVACION`,`eps`.`ID_EPS` AS `ID_EPS`,`eps`.`NOMBRE_EPS` AS `NOMBRE_EPS` from ((((`paciente` `p` join `usuarios` `u` on((`p`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `eps` on((`p`.`EPS_ID_EPS` = `eps`.`ID_EPS`))) join `paciente_has_condicion_medica` `phcd` on((`p`.`ID_PACIENTE` = `phcd`.`PACIENTE_ID_PACIENTE`))) join `condicion_medica` `cm` on((`phcd`.`CONDICION_MEDICA_ID_CONDICION_MEDICA` = `cm`.`ID_CONDICION_MEDICA`))) */;

/*View structure for view vista_citas */

/*!50001 DROP TABLE IF EXISTS `vista_citas` */;
/*!50001 DROP VIEW IF EXISTS `vista_citas` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_citas` AS select `c`.`ID_CITA` AS `ID_CITA`,`c`.`PACIENTE_ID_PACIENTE` AS `PACIENTE_ID_PACIENTE`,`c`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ODONTOLOGO_ID_ODONTOLOGO`,`c`.`HORARIO_DISPONIBILIDAD_ID_HORARIO` AS `HORARIO_DISPONIBILIDAD_ID_HORARIO`,`c`.`FECHA_HORA` AS `FECHA_HORA`,cast(`c`.`FECHA_HORA` as date) AS `FECHA`,cast(`c`.`FECHA_HORA` as time) AS `HORA`,`c`.`MOTIVO` AS `MOTIVO`,`c`.`ESTADO_CITA_ID` AS `ESTADO_CITA_ID`,`ec`.`NOMBRE_ESTADO` AS `ESTADO_CITA`,`c`.`FECHA_CREACION` AS `FECHA_CREACION`,`c`.`FECHA_CANCELACION` AS `FECHA_CANCELACION`,`c`.`MOTIVO_CANCELACION` AS `MOTIVO_CANCELACION`,`c`.`OBSERVACIONES_DOCTOR` AS `OBSERVACIONES_DOCTOR`,`c`.`RECOMENDACIONES` AS `RECOMENDACIONES`,`c`.`FECHA_ATENCION` AS `FECHA_ATENCION`,`up`.`ID_USUARIOS` AS `ID_USUARIO_PACIENTE`,`up`.`NUMERO_DOCUMENTO` AS `DOC_PACIENTE`,`up`.`TIPO_DOCUMENTO` AS `TIPO_DOC_PACIENTE`,concat(`up`.`NOMBRES`,' ',`up`.`APELLIDOS`) AS `NOMBRE_PACIENTE`,`up`.`TELEFONO` AS `TELEFONO_PACIENTE`,`up`.`CORREO` AS `CORREO_PACIENTE`,`uo`.`ID_USUARIOS` AS `ID_USUARIO_ODONTOLOGO`,concat(`uo`.`NOMBRES`,' ',`uo`.`APELLIDOS`) AS `NOMBRE_ODONTOLOGO`,`uo`.`TELEFONO` AS `TELEFONO_ODONTOLOGO`,`uo`.`CORREO` AS `CORREO_ODONTOLOGO`,`esp`.`ID_ESPECIALIDAD` AS `ID_ESPECIALIDAD`,`esp`.`NOMBRE_ESPECIALIDAD` AS `NOMBRE_ESPECIALIDAD`,`co`.`ID_CONSULTORIO` AS `ID_CONSULTORIO`,`co`.`NOMBRE` AS `NOMBRE_CONSULTORIO`,`co`.`UBICACION` AS `UBICACION_CONSULTORIO`,`pr`.`ID_PROCEDIMIENTO` AS `ID_PROCEDIMIENTO`,`pr`.`NOMBRE_PROCEDIMIENTO` AS `NOMBRE_PROCEDIMIENTO`,`hd`.`FECHA` AS `FECHA_HORARIO`,`hd`.`HORA_INICIO` AS `HORA_INICIO_HORARIO`,`hd`.`HORA_FIN` AS `HORA_FIN_HORARIO`,`hd`.`JORNADA` AS `JORNADA_HORARIO` from ((((((((((`cita` `c` join `estado_cita` `ec` on((`c`.`ESTADO_CITA_ID` = `ec`.`ID_ESTADO`))) join `paciente` `p` on((`c`.`PACIENTE_ID_PACIENTE` = `p`.`ID_PACIENTE`))) join `usuarios` `up` on((`p`.`USUARIOS_ID_USUARIOS` = `up`.`ID_USUARIOS`))) join `odontologo` `o` on((`c`.`ODONTOLOGO_ID_ODONTOLOGO` = `o`.`ID_ODONTOLOGO`))) join `usuarios` `uo` on((`o`.`USUARIOS_ID_USUARIOS` = `uo`.`ID_USUARIOS`))) left join `especialidad` `esp` on((`o`.`ESPECIALIDAD_ID_ESPECIALIDAD` = `esp`.`ID_ESPECIALIDAD`))) left join `consultorio` `co` on((`o`.`CONSULTORIO_ID_CONSULTORIO` = `co`.`ID_CONSULTORIO`))) left join `horario_disponibilidad` `hd` on((`c`.`HORARIO_DISPONIBILIDAD_ID_HORARIO` = `hd`.`ID_HORARIO`))) left join `cita_has_procedimiento` `chp` on((`c`.`ID_CITA` = `chp`.`CITA_ID_CITA`))) left join `procedimientos` `pr` on((`chp`.`PROCEDIMIENTO_ID_PROCEDIMIENTO` = `pr`.`ID_PROCEDIMIENTO`))) */;

/*View structure for view vista_comisiones_odontologo */

/*!50001 DROP TABLE IF EXISTS `vista_comisiones_odontologo` */;
/*!50001 DROP VIEW IF EXISTS `vista_comisiones_odontologo` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_comisiones_odontologo` AS select `hc`.`ID_HISTORIA_CLINICA` AS `ID_HISTORIA_CLINICA`,`hc`.`FECHA_REGISTRO` AS `FECHA_REGISTRO`,date_format(`hc`.`FECHA_REGISTRO`,'%d/%m/%Y') AS `FECHA_FORMATEADA`,`od`.`ID_ODONTOLOGO` AS `ID_ODONTOLOGO`,concat(`uo`.`NOMBRES`,' ',`uo`.`APELLIDOS`) AS `NOMBRE_ODONTOLOGO`,`pa`.`ID_PACIENTE` AS `ID_PACIENTE`,concat(`u_pac`.`NOMBRES`,' ',`u_pac`.`APELLIDOS`) AS `NOMBRE_PACIENTE`,`u_pac`.`NUMERO_DOCUMENTO` AS `DOC_PACIENTE`,`pr`.`ID_PROCEDIMIENTO` AS `ID_PROCEDIMIENTO`,`pr`.`NOMBRE_PROCEDIMIENTO` AS `NOMBRE_PROCEDIMIENTO`,`hcp`.`ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS` AS `ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS`,`hcp`.`CANTIDAD` AS `CANTIDAD`,`hcp`.`PRECIO_APLICADO` AS `PRECIO_APLICADO`,(`hcp`.`CANTIDAD` * `hcp`.`PRECIO_APLICADO`) AS `TOTAL_PROCEDIMIENTO`,coalesce(`cc`.`PORCENTAJE`,40.00) AS `PORCENTAJE_COMISION`,((`hcp`.`CANTIDAD` * `hcp`.`PRECIO_APLICADO`) * (coalesce(`cc`.`PORCENTAJE`,40.00) / 100)) AS `GANANCIA_COMISION` from (((((((`historia_clinica_has_procedimientos` `hcp` join `historia_clinica` `hc` on((`hcp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` = `hc`.`ID_HISTORIA_CLINICA`))) join `odontologo` `od` on((`hc`.`ODONTOLOGO_ID_ODONTOLOGO` = `od`.`ID_ODONTOLOGO`))) join `usuarios` `uo` on((`od`.`USUARIOS_ID_USUARIOS` = `uo`.`ID_USUARIOS`))) join `paciente` `pa` on((`hc`.`PACIENTE_ID_PACIENTE` = `pa`.`ID_PACIENTE`))) join `usuarios` `u_pac` on((`pa`.`USUARIOS_ID_USUARIOS` = `u_pac`.`ID_USUARIOS`))) join `procedimientos` `pr` on((`pr`.`ID_PROCEDIMIENTO` = `hcp`.`PROCEDIMIENTOS_ID_PROCEDIMIENTO`))) left join `configuracion_comisiones` `cc` on((`cc`.`ODONTOLOGO_ID_ODONTOLOGO` = `od`.`ID_ODONTOLOGO`))) */;

/*View structure for view vista_consultorios */

/*!50001 DROP TABLE IF EXISTS `vista_consultorios` */;
/*!50001 DROP VIEW IF EXISTS `vista_consultorios` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_consultorios` AS select `c`.`ID_CONSULTORIO` AS `ID_CONSULTORIO`,`c`.`NOMBRE` AS `NOMBRE`,`c`.`UBICACION` AS `UBICACION`,`c`.`DESCRIPCION` AS `DESCRIPCION`,`c`.`ESTADO` AS `ESTADO`,`o`.`ID_ODONTOLOGO` AS `ID_ODONTOLOGO`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `doctor_nombre`,`e`.`ID_ESPECIALIDAD` AS `ID_ESPECIALIDAD`,`e`.`NOMBRE_ESPECIALIDAD` AS `especialidad` from (((`consultorio` `c` left join `odontologo` `o` on((`o`.`CONSULTORIO_ID_CONSULTORIO` = `c`.`ID_CONSULTORIO`))) left join `usuarios` `u` on((`o`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `especialidad` `e` on((`o`.`ESPECIALIDAD_ID_ESPECIALIDAD` = `e`.`ID_ESPECIALIDAD`))) */;

/*View structure for view vista_contactos_landing */

/*!50001 DROP TABLE IF EXISTS `vista_contactos_landing` */;
/*!50001 DROP VIEW IF EXISTS `vista_contactos_landing` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_contactos_landing` AS select `c`.`ID_CONTACTO` AS `ID_CONTACTO`,`c`.`NOMBRE_COMPLETO` AS `NOMBRE_COMPLETO`,`c`.`CORREO` AS `CORREO`,`c`.`TELEFONO` AS `TELEFONO`,`c`.`SERVICIO_INTERES` AS `SERVICIO_INTERES`,`c`.`MENSAJE` AS `MENSAJE`,`c`.`FECHA_REGISTRO` AS `FECHA_REGISTRO`,`c`.`ESTADO` AS `ESTADO`,`c`.`OBSERVACIONES` AS `OBSERVACIONES`,`c`.`USUARIOS_ID_USUARIOS` AS `ID_ADMIN_GESTION`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `NOMBRE_ADMIN_GESTION`,`p`.`ID_PROCEDIMIENTO` AS `ID_PROCEDIMIENTO`,`p`.`NOMBRE_PROCEDIMIENTO` AS `NOMBRE_PROCEDIMIENTO` from ((`contacto_landing` `c` left join `usuarios` `u` on((`c`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `procedimientos` `p` on((`c`.`PROCEDIMIENTOS_ID_PROCEDIMIENTO` = `p`.`ID_PROCEDIMIENTO`))) */;

/*View structure for view vista_facturacion */

/*!50001 DROP TABLE IF EXISTS `vista_facturacion` */;
/*!50001 DROP VIEW IF EXISTS `vista_facturacion` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY INVOKER VIEW `vista_facturacion` AS select `f`.`ID_FACTURA` AS `ID_FACTURA`,`f`.`FECHA_EMISION` AS `FECHA_EMISION`,`f`.`TOTAL` AS `TOTAL`,`f`.`ESTADO` AS `ESTADO_FACTURA`,`f`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` AS `HISTORIA_CLINICA_ID_HISTORIA_CLINICA`,`f`.`CITA_ID_CITA` AS `CITA_ID_CITA`,coalesce(`f`.`PACIENTE_ID_PACIENTE`,`hc`.`PACIENTE_ID_PACIENTE`) AS `PACIENTE_ID_PACIENTE`,`up`.`ID_USUARIOS` AS `ID_USUARIO_PACIENTE`,`up`.`NUMERO_DOCUMENTO` AS `DOC_PACIENTE`,concat(`up`.`NOMBRES`,' ',`up`.`APELLIDOS`) AS `NOMBRE_PACIENTE`,`up`.`CORREO` AS `CORREO_PACIENTE`,`up`.`TELEFONO` AS `TELEFONO_PACIENTE`,`od`.`ID_ODONTOLOGO` AS `ID_ODONTOLOGO`,concat(`ud`.`NOMBRES`,' ',`ud`.`APELLIDOS`) AS `NOMBRE_ODONTOLOGO`,coalesce(`ph`.`nombres_procedimientos`,'Tratamiento Odontológico') AS `PROCEDIMIENTO`,coalesce(`pt`.`total_pagado`,0) AS `TOTAL_PAGADO`,(`f`.`TOTAL` - coalesce(`pt`.`total_pagado`,0)) AS `SALDO_PENDIENTE`,coalesce(`pt`.`cantidad_pagos`,0) AS `CANTIDAD_PAGOS`,`pt`.`fecha_ultimo_pago` AS `FECHA_ULTIMO_PAGO`,`pt`.`ultimo_metodo_pago` AS `ULTIMO_METODO_PAGO` from (((((((`sigco`.`factura` `f` left join `sigco`.`historia_clinica` `hc` on((`f`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` = `hc`.`ID_HISTORIA_CLINICA`))) join `sigco`.`paciente` `p` on((coalesce(`f`.`PACIENTE_ID_PACIENTE`,`hc`.`PACIENTE_ID_PACIENTE`) = `p`.`ID_PACIENTE`))) join `sigco`.`usuarios` `up` on((`p`.`USUARIOS_ID_USUARIOS` = `up`.`ID_USUARIOS`))) left join `sigco`.`odontologo` `od` on((`hc`.`ODONTOLOGO_ID_ODONTOLOGO` = `od`.`ID_ODONTOLOGO`))) left join `sigco`.`usuarios` `ud` on((`od`.`USUARIOS_ID_USUARIOS` = `ud`.`ID_USUARIOS`))) left join (select `hchp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` AS `hc_id`,group_concat(`pr`.`NOMBRE_PROCEDIMIENTO` separator ', ') AS `nombres_procedimientos` from (`sigco`.`historia_clinica_has_procedimientos` `hchp` join `sigco`.`procedimientos` `pr` on((`hchp`.`PROCEDIMIENTOS_ID_PROCEDIMIENTO` = `pr`.`ID_PROCEDIMIENTO`))) group by `hchp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`) `ph` on((`hc`.`ID_HISTORIA_CLINICA` = `ph`.`hc_id`))) left join (select `pg`.`FACTURA_ID_FACTURA` AS `factura_id`,sum(`pg`.`MONTO`) AS `total_pagado`,count(0) AS `cantidad_pagos`,nullif(max(`pg`.`FECHA_PAGO`),'0000-00-00 00:00:00') AS `fecha_ultimo_pago`,substring_index(group_concat(`pg`.`METODO_PAGO` order by `pg`.`FECHA_PAGO` DESC,`pg`.`ID_PAGO` DESC separator ','),',',1) AS `ultimo_metodo_pago` from `sigco`.`pago` `pg` where (`pg`.`ESTADO` = 'PAGADO') group by `pg`.`FACTURA_ID_FACTURA`) `pt` on((`f`.`ID_FACTURA` = `pt`.`factura_id`))) */;

/*View structure for view vista_facturas_admin */

/*!50001 DROP TABLE IF EXISTS `vista_facturas_admin` */;
/*!50001 DROP VIEW IF EXISTS `vista_facturas_admin` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_facturas_admin` AS select `f`.`ID_FACTURA` AS `id`,`f`.`FECHA_EMISION` AS `fecha_emision`,`f`.`TOTAL` AS `total`,(case when (`f`.`ESTADO` = 'ANULADA') then 'Anulada' when (ifnull(`p_totales`.`total_pagado`,0) >= `f`.`TOTAL`) then 'Pagada' when (ifnull(`p_totales`.`total_pagado`,0) > 0) then 'Abonada' else 'Emitida' end) AS `estado`,ifnull(concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`),'Paciente Temporal') AS `paciente`,ifnull(`u`.`NUMERO_DOCUMENTO`,'N/A') AS `documento`,ifnull(`u`.`CORREO`,'N/A') AS `correo`,ifnull(`u`.`TELEFONO`,'N/A') AS `telefono`,ifnull(`proc_hc`.`nombres_procedimientos`,'Tratamiento General') AS `tratamiento`,ifnull(`p_totales`.`total_pagado`,0) AS `pagado`,coalesce(`f`.`PACIENTE_ID_PACIENTE`,`hc`.`PACIENTE_ID_PACIENTE`) AS `PACIENTE_ID_PACIENTE` from (((((`sigco`.`factura` `f` left join (select `sigco`.`pago`.`FACTURA_ID_FACTURA` AS `FACTURA_ID_FACTURA`,sum(`sigco`.`pago`.`MONTO`) AS `total_pagado` from `sigco`.`pago` where (`sigco`.`pago`.`ESTADO` = 'PAGADO') group by `sigco`.`pago`.`FACTURA_ID_FACTURA`) `p_totales` on((`f`.`ID_FACTURA` = `p_totales`.`FACTURA_ID_FACTURA`))) left join `sigco`.`historia_clinica` `hc` on((`f`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` = `hc`.`ID_HISTORIA_CLINICA`))) left join `sigco`.`paciente` `p` on((coalesce(`f`.`PACIENTE_ID_PACIENTE`,`hc`.`PACIENTE_ID_PACIENTE`) = `p`.`ID_PACIENTE`))) left join `sigco`.`usuarios` `u` on((`p`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join (select `hchp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` AS `HISTORIA_CLINICA_ID_HISTORIA_CLINICA`,group_concat(distinct `pr`.`NOMBRE_PROCEDIMIENTO` separator ', ') AS `nombres_procedimientos` from (`sigco`.`historia_clinica_has_procedimientos` `hchp` join `sigco`.`procedimientos` `pr` on((`hchp`.`PROCEDIMIENTOS_ID_PROCEDIMIENTO` = `pr`.`ID_PROCEDIMIENTO`))) group by `hchp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`) `proc_hc` on((`hc`.`ID_HISTORIA_CLINICA` = `proc_hc`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`))) */;

/*View structure for view vista_historia_clinica */

/*!50001 DROP TABLE IF EXISTS `vista_historia_clinica` */;
/*!50001 DROP VIEW IF EXISTS `vista_historia_clinica` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_historia_clinica` AS select `hc`.`ID_HISTORIA_CLINICA` AS `ID_HISTORIA_CLINICA`,`hc`.`FECHA_REGISTRO` AS `FECHA_REGISTRO`,`hc`.`MOTIVO_CONSULTA` AS `MOTIVO_CONSULTA`,`hc`.`DIAGNOSTICO` AS `DIAGNOSTICO`,`hc`.`TRATAMIENTO` AS `TRATAMIENTO`,`hc`.`OBSERVACIONES` AS `OBSERVACIONES`,`hc`.`CITA_ID_CITA` AS `CITA_ID_CITA`,`p`.`ID_PACIENTE` AS `ID_PACIENTE`,`up`.`NUMERO_DOCUMENTO` AS `DOC_PACIENTE`,concat(`up`.`NOMBRES`,' ',`up`.`APELLIDOS`) AS `NOMBRE_PACIENTE`,`up`.`FECHA_NACIMIENTO` AS `FECHA_NACIMIENTO`,`up`.`GENERO` AS `GENERO`,`up`.`RH` AS `RH`,`up`.`TELEFONO` AS `TELEFONO_PACIENTE`,`eps`.`NOMBRE_EPS` AS `NOMBRE_EPS`,`od`.`ID_ODONTOLOGO` AS `ID_ODONTOLOGO`,concat(`ud`.`NOMBRES`,' ',`ud`.`APELLIDOS`) AS `NOMBRE_ODONTOLOGO`,`esp`.`NOMBRE_ESPECIALIDAD` AS `NOMBRE_ESPECIALIDAD` from ((((((`historia_clinica` `hc` join `paciente` `p` on((`hc`.`PACIENTE_ID_PACIENTE` = `p`.`ID_PACIENTE`))) join `usuarios` `up` on((`p`.`USUARIOS_ID_USUARIOS` = `up`.`ID_USUARIOS`))) join `odontologo` `od` on((`hc`.`ODONTOLOGO_ID_ODONTOLOGO` = `od`.`ID_ODONTOLOGO`))) join `usuarios` `ud` on((`od`.`USUARIOS_ID_USUARIOS` = `ud`.`ID_USUARIOS`))) left join `eps` on((`p`.`EPS_ID_EPS` = `eps`.`ID_EPS`))) left join `especialidad` `esp` on((`od`.`ESPECIALIDAD_ID_ESPECIALIDAD` = `esp`.`ID_ESPECIALIDAD`))) */;

/*View structure for view vista_historial_consultorio */

/*!50001 DROP TABLE IF EXISTS `vista_historial_consultorio` */;
/*!50001 DROP VIEW IF EXISTS `vista_historial_consultorio` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_historial_consultorio` AS select `h`.`ID_HISTORIAL` AS `ID_HISTORIAL`,`h`.`CONSULTORIO_ID_CONSULTORIO` AS `CONSULTORIO_ID_CONSULTORIO`,`c`.`NOMBRE` AS `consultorio_nombre`,`h`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ODONTOLOGO_ID_ODONTOLOGO`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `doctor_nombre`,`h`.`FECHA_ASIGNACION` AS `FECHA_ASIGNACION`,`h`.`FECHA_DESASIGNACION` AS `FECHA_DESASIGNACION`,`h`.`MOTIVO_CAMBIO` AS `MOTIVO_CAMBIO` from (((`historial_asignacion_consultorio` `h` join `consultorio` `c` on((`h`.`CONSULTORIO_ID_CONSULTORIO` = `c`.`ID_CONSULTORIO`))) join `odontologo` `o` on((`h`.`ODONTOLOGO_ID_ODONTOLOGO` = `o`.`ID_ODONTOLOGO`))) join `usuarios` `u` on((`o`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) */;

/*View structure for view vista_horarios_con_citas */

/*!50001 DROP TABLE IF EXISTS `vista_horarios_con_citas` */;
/*!50001 DROP VIEW IF EXISTS `vista_horarios_con_citas` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_horarios_con_citas` AS select `h`.`ID_HORARIO` AS `ID_HORARIO`,`h`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ODONTOLOGO_ID_ODONTOLOGO`,cast(`c`.`FECHA_HORA` as time) AS `HORA_INICIO`,addtime(cast(`c`.`FECHA_HORA` as time),'01:00:00') AS `HORA_FIN`,`h`.`JORNADA` AS `JORNADA`,`h`.`FECHA` AS `FECHA`,'Ocupado' AS `ESTADO`,`u`.`NOMBRES` AS `NOMBRES`,`u`.`APELLIDOS` AS `APELLIDOS`,ifnull(`con`.`NOMBRE`,'Sin asignar') AS `CONSULTORIO`,`p`.`NOMBRES` AS `NOMBRE_PACIENTE`,`p`.`APELLIDOS` AS `APELLIDO_PACIENTE`,`pr`.`ID_PROCEDIMIENTO` AS `ID_PROCEDIMIENTO`,`pr`.`NOMBRE_PROCEDIMIENTO` AS `NOMBRE_PROCEDIMIENTO`,`h`.`DESCANSO_INICIO` AS `DESCANSO_INICIO`,`h`.`DESCANSO_FIN` AS `DESCANSO_FIN` from ((((((((`horario_disponibilidad` `h` join `odontologo` `o` on((`h`.`ODONTOLOGO_ID_ODONTOLOGO` = `o`.`ID_ODONTOLOGO`))) join `usuarios` `u` on((`o`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `consultorio` `con` on((`o`.`CONSULTORIO_ID_CONSULTORIO` = `con`.`ID_CONSULTORIO`))) join `cita` `c` on(((`h`.`ID_HORARIO` = `c`.`HORARIO_DISPONIBILIDAD_ID_HORARIO`) and (`c`.`ESTADO_CITA_ID` = 1)))) left join `paciente` `pa` on((`c`.`PACIENTE_ID_PACIENTE` = `pa`.`ID_PACIENTE`))) left join `usuarios` `p` on((`pa`.`USUARIOS_ID_USUARIOS` = `p`.`ID_USUARIOS`))) left join `cita_has_procedimiento` `chp` on((`c`.`ID_CITA` = `chp`.`CITA_ID_CITA`))) left join `procedimientos` `pr` on((`chp`.`PROCEDIMIENTO_ID_PROCEDIMIENTO` = `pr`.`ID_PROCEDIMIENTO`))) where (`h`.`ESTADO` in ('Disponible','Ocupado','No utilizado')) union all select `h`.`ID_HORARIO` AS `ID_HORARIO`,`h`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ODONTOLOGO_ID_ODONTOLOGO`,`h`.`HORA_INICIO` AS `HORA_INICIO`,`h`.`HORA_FIN` AS `HORA_FIN`,`h`.`JORNADA` AS `JORNADA`,`h`.`FECHA` AS `FECHA`,(case when (((`h`.`FECHA` < curdate()) or ((`h`.`FECHA` = curdate()) and (`h`.`HORA_FIN` > '00:00:00') and (`h`.`HORA_FIN` < curtime()))) and (`h`.`ESTADO` = 'Disponible')) then 'No utilizado' else `h`.`ESTADO` end) AS `ESTADO`,`u`.`NOMBRES` AS `NOMBRES`,`u`.`APELLIDOS` AS `APELLIDOS`,ifnull(`con`.`NOMBRE`,'Sin asignar') AS `CONSULTORIO`,NULL AS `NOMBRE_PACIENTE`,NULL AS `APELLIDO_PACIENTE`,NULL AS `ID_PROCEDIMIENTO`,NULL AS `NOMBRE_PROCEDIMIENTO`,`h`.`DESCANSO_INICIO` AS `DESCANSO_INICIO`,`h`.`DESCANSO_FIN` AS `DESCANSO_FIN` from (((`horario_disponibilidad` `h` join `odontologo` `o` on((`h`.`ODONTOLOGO_ID_ODONTOLOGO` = `o`.`ID_ODONTOLOGO`))) join `usuarios` `u` on((`o`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `consultorio` `con` on((`o`.`CONSULTORIO_ID_CONSULTORIO` = `con`.`ID_CONSULTORIO`))) where (`h`.`ESTADO` in ('Disponible','No utilizado')) */;

/*View structure for view vista_horarios_disponibilidad */

/*!50001 DROP TABLE IF EXISTS `vista_horarios_disponibilidad` */;
/*!50001 DROP VIEW IF EXISTS `vista_horarios_disponibilidad` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_horarios_disponibilidad` AS select `hd`.`ID_HORARIO` AS `ID_HORARIO`,`hd`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ODONTOLOGO_ID_ODONTOLOGO`,`hd`.`FECHA` AS `FECHA`,`hd`.`HORA_INICIO` AS `HORA_INICIO`,`hd`.`HORA_FIN` AS `HORA_FIN`,`hd`.`ESTADO` AS `ESTADO_HORARIO`,`hd`.`JORNADA` AS `JORNADA`,`hd`.`DIA` AS `DIA`,`hd`.`DESCANSO_INICIO` AS `DESCANSO_INICIO`,`hd`.`DESCANSO_FIN` AS `DESCANSO_FIN`,`uo`.`ID_USUARIOS` AS `ID_USUARIO_ODONTOLOGO`,concat(`uo`.`NOMBRES`,' ',`uo`.`APELLIDOS`) AS `NOMBRE_ODONTOLOGO`,`esp`.`ID_ESPECIALIDAD` AS `ID_ESPECIALIDAD`,`esp`.`NOMBRE_ESPECIALIDAD` AS `NOMBRE_ESPECIALIDAD`,`co`.`ID_CONSULTORIO` AS `ID_CONSULTORIO`,`co`.`NOMBRE` AS `NOMBRE_CONSULTORIO`,`co`.`UBICACION` AS `UBICACION_CONSULTORIO` from ((((`horario_disponibilidad` `hd` join `odontologo` `o` on((`hd`.`ODONTOLOGO_ID_ODONTOLOGO` = `o`.`ID_ODONTOLOGO`))) join `usuarios` `uo` on((`o`.`USUARIOS_ID_USUARIOS` = `uo`.`ID_USUARIOS`))) left join `especialidad` `esp` on((`o`.`ESPECIALIDAD_ID_ESPECIALIDAD` = `esp`.`ID_ESPECIALIDAD`))) left join `consultorio` `co` on((`o`.`CONSULTORIO_ID_CONSULTORIO` = `co`.`ID_CONSULTORIO`))) */;

/*View structure for view vista_ingresos_procedimiento */

/*!50001 DROP TABLE IF EXISTS `vista_ingresos_procedimiento` */;
/*!50001 DROP VIEW IF EXISTS `vista_ingresos_procedimiento` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_ingresos_procedimiento` AS select `pg`.`ID_PAGO` AS `ID_PAGO`,`pg`.`FECHA_PAGO` AS `FECHA_PAGO`,`pg`.`MONTO` AS `MONTO_PAGO`,`pg`.`ESTADO` AS `ESTADO_PAGO`,`f`.`ID_FACTURA` AS `ID_FACTURA`,`f`.`TOTAL` AS `TOTAL_FACTURA`,`pr`.`ID_PROCEDIMIENTO` AS `ID_PROCEDIMIENTO`,`pr`.`NOMBRE_PROCEDIMIENTO` AS `NOMBRE_PROCEDIMIENTO`,`hchp`.`PRECIO_APLICADO` AS `PRECIO_APLICADO`,`hchp`.`CANTIDAD` AS `CANTIDAD`,((`hchp`.`PRECIO_APLICADO` * `hchp`.`CANTIDAD`) * (`pg`.`MONTO` / `f`.`TOTAL`)) AS `INGRESO_PROPORCIONAL` from ((((`pago` `pg` join `factura` `f` on((`pg`.`FACTURA_ID_FACTURA` = `f`.`ID_FACTURA`))) join `historia_clinica` `hc` on((`f`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` = `hc`.`ID_HISTORIA_CLINICA`))) join `historia_clinica_has_procedimientos` `hchp` on((`hc`.`ID_HISTORIA_CLINICA` = `hchp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA`))) join `procedimientos` `pr` on((`hchp`.`PROCEDIMIENTOS_ID_PROCEDIMIENTO` = `pr`.`ID_PROCEDIMIENTO`))) where (`f`.`TOTAL` > 0) */;

/*View structure for view vista_notificaciones */

/*!50001 DROP TABLE IF EXISTS `vista_notificaciones` */;
/*!50001 DROP VIEW IF EXISTS `vista_notificaciones` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_notificaciones` AS select `n`.`ID_NOTIFICACIONES` AS `ID_NOTIFICACIONES`,`n`.`FECHA_ENVIO` AS `FECHA_ENVIO`,`n`.`MENSAJE` AS `MENSAJE`,`n`.`ESTADO` AS `ESTADO`,`n`.`FECHA_LECTURA` AS `FECHA_LECTURA`,`tn`.`ID_TIPO_NOTIFICACION` AS `ID_TIPO_NOTIFICACION`,`tn`.`NOMBRE` AS `TIPO_NOTIFICACION`,`tn`.`NOMBRE` AS `TIPO_NOMBRE`,`tn`.`ROL_DESTINO` AS `ROL_DESTINO`,`n`.`USUARIOS_ID_USUARIOS` AS `USUARIOS_ID_USUARIOS`,`u`.`ID_USUARIOS` AS `ID_USUARIOS`,`u`.`NUMERO_DOCUMENTO` AS `NUMERO_DOCUMENTO`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `NOMBRE_USUARIO`,`u`.`CORREO` AS `CORREO`,`u`.`TELEFONO` AS `TELEFONO`,`r`.`ID_ROLES` AS `ID_ROLES`,`r`.`ROL_NOMBRE` AS `ROL_NOMBRE` from (((`notificaciones` `n` left join `tipo_notificacion` `tn` on((`n`.`TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION` = `tn`.`ID_TIPO_NOTIFICACION`))) left join `usuarios` `u` on((`n`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `roles` `r` on((`u`.`ROLES_ID_ROLES` = `r`.`ID_ROLES`))) */;

/*View structure for view vista_odontograma */

/*!50001 DROP TABLE IF EXISTS `vista_odontograma` */;
/*!50001 DROP VIEW IF EXISTS `vista_odontograma` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_odontograma` AS select `ot`.`ID_ODONTOGRAMA_REGISTRO` AS `ID_ODONTOGRAMA_REGISTRO`,`ot`.`PACIENTE_ID_PACIENTE` AS `PACIENTE_ID_PACIENTE`,`ot`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ODONTOLOGO_ID_ODONTOLOGO`,`ot`.`PROCEDIMIENTO_ID_PROCEDIMIENTO` AS `PROCEDIMIENTO_ID_PROCEDIMIENTO`,`ot`.`PIEZA_DENTAL` AS `PIEZA_DENTAL`,`ot`.`FECHA_REGISTRO` AS `FECHA_REGISTRO`,date_format(`ot`.`FECHA_REGISTRO`,'%d %b %Y') AS `FECHA_FORMATEADA`,cast(`ot`.`FECHA_REGISTRO` as date) AS `FECHA`,`ot`.`ESTADO` AS `ESTADO_DIENTE`,`ot`.`NOTAS` AS `NOTAS`,`pr`.`NOMBRE_PROCEDIMIENTO` AS `NOMBRE_PROCEDIMIENTO`,`pr`.`COSTO` AS `COSTO_PROCEDIMIENTO`,concat(`up`.`NOMBRES`,' ',`up`.`APELLIDOS`) AS `NOMBRE_PACIENTE`,`up`.`NUMERO_DOCUMENTO` AS `DOC_PACIENTE`,coalesce(concat(`uo`.`NOMBRES`,' ',`uo`.`APELLIDOS`),'Sin asignar') AS `NOMBRE_DOCTOR` from (((((`odontograma_tratamientos` `ot` join `procedimientos` `pr` on((`ot`.`PROCEDIMIENTO_ID_PROCEDIMIENTO` = `pr`.`ID_PROCEDIMIENTO`))) join `paciente` `p` on((`ot`.`PACIENTE_ID_PACIENTE` = `p`.`ID_PACIENTE`))) join `usuarios` `up` on((`p`.`USUARIOS_ID_USUARIOS` = `up`.`ID_USUARIOS`))) left join `odontologo` `od` on((`ot`.`ODONTOLOGO_ID_ODONTOLOGO` = `od`.`ID_ODONTOLOGO`))) left join `usuarios` `uo` on((`od`.`USUARIOS_ID_USUARIOS` = `uo`.`ID_USUARIOS`))) */;

/*View structure for view vista_odontologos */

/*!50001 DROP TABLE IF EXISTS `vista_odontologos` */;
/*!50001 DROP VIEW IF EXISTS `vista_odontologos` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_odontologos` AS select `o`.`ID_ODONTOLOGO` AS `ID_ODONTOLOGO`,`u`.`ID_USUARIOS` AS `ID_USUARIOS`,`u`.`NUMERO_DOCUMENTO` AS `NUMERO_DOCUMENTO`,`u`.`TIPO_DOCUMENTO` AS `TIPO_DOCUMENTO`,`u`.`NOMBRES` AS `NOMBRES`,`u`.`APELLIDOS` AS `APELLIDOS`,`u`.`NOMBRES` AS `PRIMER_NOMBRE`,`u`.`APELLIDOS` AS `PRIMER_APELLIDO`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `NOMBRE_COMPLETO`,`u`.`TELEFONO` AS `TELEFONO`,`u`.`CORREO` AS `CORREO`,`u`.`DIRECCION` AS `DIRECCION`,`u`.`GENERO` AS `GENERO`,`u`.`FECHA_NACIMIENTO` AS `FECHA_NACIMIENTO`,`u`.`ESTADO` AS `ESTADO_USUARIO`,`u`.`NOTIFICACIONES_ACTIVAS` AS `NOTIFICACIONES_ACTIVAS`,`e`.`ID_ESPECIALIDAD` AS `ID_ESPECIALIDAD`,`e`.`NOMBRE_ESPECIALIDAD` AS `NOMBRE_ESPECIALIDAD`,`e`.`ID_ESPECIALIDAD` AS `especialidad_id`,`e`.`NOMBRE_ESPECIALIDAD` AS `especialidad_nombre`,`c`.`ID_CONSULTORIO` AS `ID_CONSULTORIO`,`c`.`ID_CONSULTORIO` AS `CONSULTORIO`,`c`.`NOMBRE` AS `NOMBRE_CONSULTORIO`,`c`.`UBICACION` AS `UBICACION_CONSULTORIO`,`r`.`ID_ROLES` AS `ID_ROLES`,`r`.`ROL_NOMBRE` AS `ROL_NOMBRE` from ((((`odontologo` `o` join `usuarios` `u` on((`u`.`ID_USUARIOS` = `o`.`USUARIOS_ID_USUARIOS`))) join `roles` `r` on((`r`.`ID_ROLES` = `u`.`ROLES_ID_ROLES`))) left join `especialidad` `e` on((`o`.`ESPECIALIDAD_ID_ESPECIALIDAD` = `e`.`ID_ESPECIALIDAD`))) left join `consultorio` `c` on((`o`.`CONSULTORIO_ID_CONSULTORIO` = `c`.`ID_CONSULTORIO`))) */;

/*View structure for view vista_pacientes */

/*!50001 DROP TABLE IF EXISTS `vista_pacientes` */;
/*!50001 DROP VIEW IF EXISTS `vista_pacientes` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_pacientes` AS select `p`.`ID_PACIENTE` AS `ID_PACIENTE`,`u`.`ID_USUARIOS` AS `ID_USUARIOS`,`u`.`NUMERO_DOCUMENTO` AS `NUMERO_DOCUMENTO`,`u`.`TIPO_DOCUMENTO` AS `TIPO_DOCUMENTO`,`u`.`NOMBRES` AS `NOMBRES`,`u`.`APELLIDOS` AS `APELLIDOS`,`u`.`NOMBRES` AS `PRIMER_NOMBRE`,`u`.`APELLIDOS` AS `PRIMER_APELLIDO`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `NOMBRE_COMPLETO`,`u`.`TELEFONO` AS `TELEFONO`,`u`.`CORREO` AS `CORREO`,`u`.`DIRECCION` AS `DIRECCION`,`u`.`GENERO` AS `GENERO`,`u`.`FECHA_NACIMIENTO` AS `FECHA_NACIMIENTO`,timestampdiff(YEAR,`u`.`FECHA_NACIMIENTO`,curdate()) AS `EDAD`,if((((`u`.`FECHA_NACIMIENTO` is not null) and (timestampdiff(YEAR,`u`.`FECHA_NACIMIENTO`,curdate()) < 18)) or (`u`.`TIPO_DOCUMENTO` in ('R.C','T.I')) or exists(select 1 from `paciente_has_acudiente` `pha` where (`pha`.`PACIENTE_ID_PACIENTE` = `p`.`ID_PACIENTE`))),1,0) AS `ES_MENOR`,`u`.`RH` AS `RH`,`u`.`ESTADO` AS `ESTADO_USUARIO`,`u`.`NOTIFICACIONES_ACTIVAS` AS `NOTIFICACIONES_ACTIVAS`,`p`.`NOMBRE_CONTACTO_EMERGENCIA` AS `NOMBRE_CONTACTO_EMERGENCIA`,`p`.`NUMERO_CONTACTO_EMERGENCIA` AS `NUMERO_CONTACTO_EMERGENCIA`,`eps`.`ID_EPS` AS `ID_EPS`,`eps`.`NOMBRE_EPS` AS `NOMBRE_EPS`,`r`.`ID_ROLES` AS `ID_ROLES`,`r`.`ROL_NOMBRE` AS `ROL_NOMBRE` from (((`paciente` `p` join `usuarios` `u` on((`u`.`ID_USUARIOS` = `p`.`USUARIOS_ID_USUARIOS`))) join `roles` `r` on((`r`.`ID_ROLES` = `u`.`ROLES_ID_ROLES`))) left join `eps` on((`p`.`EPS_ID_EPS` = `eps`.`ID_EPS`))) */;

/*View structure for view vista_procedimientos_hc */

/*!50001 DROP TABLE IF EXISTS `vista_procedimientos_hc` */;
/*!50001 DROP VIEW IF EXISTS `vista_procedimientos_hc` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_procedimientos_hc` AS select `hcp`.`ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS` AS `ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS`,`hcp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` AS `HISTORIA_CLINICA_ID_HISTORIA_CLINICA`,`hcp`.`PROCEDIMIENTOS_ID_PROCEDIMIENTO` AS `PROCEDIMIENTOS_ID_PROCEDIMIENTO`,`pr`.`NOMBRE_PROCEDIMIENTO` AS `NOMBRE_PROCEDIMIENTO`,`pr`.`DESCRIPCION` AS `DESCRIPCION_PROCEDIMIENTO`,`pr`.`COSTO` AS `COSTO_PROCEDIMIENTO`,`pr`.`TIEMPO_ESTIMADO` AS `TIEMPO_ESTIMADO_PROCEDIMIENTO`,`hcp`.`CANTIDAD` AS `CANTIDAD`,`hcp`.`PRECIO_APLICADO` AS `PRECIO_APLICADO`,(`hcp`.`CANTIDAD` * `hcp`.`PRECIO_APLICADO`) AS `SUBTOTAL`,`hcp`.`OBSERVACIONES` AS `OBSERVACIONES`,`hc`.`FECHA_REGISTRO` AS `FECHA_REGISTRO`,`hc`.`MOTIVO_CONSULTA` AS `MOTIVO_CONSULTA`,`hc`.`DIAGNOSTICO` AS `DIAGNOSTICO`,`hc`.`TRATAMIENTO` AS `TRATAMIENTO`,`p`.`ID_PACIENTE` AS `ID_PACIENTE`,concat(`up`.`NOMBRES`,' ',`up`.`APELLIDOS`) AS `NOMBRE_PACIENTE`,`up`.`NUMERO_DOCUMENTO` AS `DOC_PACIENTE`,`up`.`TELEFONO` AS `TELEFONO_PACIENTE`,`up`.`FECHA_NACIMIENTO` AS `FECHA_NACIMIENTO`,`hc`.`ODONTOLOGO_ID_ODONTOLOGO` AS `ODONTOLOGO_ID_ODONTOLOGO`,concat(`uo`.`NOMBRES`,' ',`uo`.`APELLIDOS`) AS `NOMBRE_ODONTOLOGO` from ((((((`historia_clinica_has_procedimientos` `hcp` join `procedimientos` `pr` on((`hcp`.`PROCEDIMIENTOS_ID_PROCEDIMIENTO` = `pr`.`ID_PROCEDIMIENTO`))) join `historia_clinica` `hc` on((`hcp`.`HISTORIA_CLINICA_ID_HISTORIA_CLINICA` = `hc`.`ID_HISTORIA_CLINICA`))) join `paciente` `p` on((`hc`.`PACIENTE_ID_PACIENTE` = `p`.`ID_PACIENTE`))) join `usuarios` `up` on((`p`.`USUARIOS_ID_USUARIOS` = `up`.`ID_USUARIOS`))) left join `odontologo` `od` on((`hc`.`ODONTOLOGO_ID_ODONTOLOGO` = `od`.`ID_ODONTOLOGO`))) left join `usuarios` `uo` on((`od`.`USUARIOS_ID_USUARIOS` = `uo`.`ID_USUARIOS`))) */;

/*View structure for view vista_usuarios */

/*!50001 DROP TABLE IF EXISTS `vista_usuarios` */;
/*!50001 DROP VIEW IF EXISTS `vista_usuarios` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_usuarios` AS select `u`.`ID_USUARIOS` AS `ID_USUARIOS`,`u`.`NUMERO_DOCUMENTO` AS `NUMERO_DOCUMENTO`,`u`.`TIPO_DOCUMENTO` AS `TIPO_DOCUMENTO`,`u`.`NOMBRES` AS `NOMBRES`,`u`.`APELLIDOS` AS `APELLIDOS`,`u`.`NOMBRES` AS `PRIMER_NOMBRE`,`u`.`APELLIDOS` AS `PRIMER_APELLIDO`,concat(`u`.`NOMBRES`,' ',`u`.`APELLIDOS`) AS `NOMBRE_COMPLETO`,`u`.`CORREO` AS `CORREO`,`u`.`TELEFONO` AS `TELEFONO`,`u`.`DIRECCION` AS `DIRECCION`,`u`.`GENERO` AS `GENERO`,`u`.`FECHA_NACIMIENTO` AS `FECHA_NACIMIENTO`,`u`.`RH` AS `RH`,`u`.`ESTADO` AS `ESTADO`,`u`.`ROLES_ID_ROLES` AS `ROLES_ID_ROLES`,`r`.`ROL_NOMBRE` AS `ROL_NOMBRE`,`o`.`ID_ODONTOLOGO` AS `ID_ODONTOLOGO`,`p`.`ID_PACIENTE` AS `ID_PACIENTE`,`e`.`ID_ESPECIALIDAD` AS `ID_ESPECIALIDAD`,`e`.`NOMBRE_ESPECIALIDAD` AS `NOMBRE_ESPECIALIDAD`,`c`.`ID_CONSULTORIO` AS `ID_CONSULTORIO`,`c`.`NOMBRE` AS `NOMBRE_CONSULTORIO` from (((((`usuarios` `u` join `roles` `r` on((`u`.`ROLES_ID_ROLES` = `r`.`ID_ROLES`))) left join `odontologo` `o` on((`o`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `paciente` `p` on((`p`.`USUARIOS_ID_USUARIOS` = `u`.`ID_USUARIOS`))) left join `especialidad` `e` on((`o`.`ESPECIALIDAD_ID_ESPECIALIDAD` = `e`.`ID_ESPECIALIDAD`))) left join `consultorio` `c` on((`o`.`CONSULTORIO_ID_CONSULTORIO` = `c`.`ID_CONSULTORIO`))) */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
