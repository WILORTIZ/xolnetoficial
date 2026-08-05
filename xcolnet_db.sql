-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: xcolnet_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `pqrs`
--

DROP TABLE IF EXISTS `pqrs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pqrs` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(256) NOT NULL,
  `Email` varchar(256) NOT NULL,
  `Telefono` varchar(50) DEFAULT NULL,
  `Tipo` varchar(100) NOT NULL,
  `Mensaje` text NOT NULL,
  `Estado` varchar(50) DEFAULT 'Pendiente',
  `FechaCreacion` datetime NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pqrs`
--

LOCK TABLES `pqrs` WRITE;
/*!40000 ALTER TABLE `pqrs` DISABLE KEYS */;
INSERT INTO `pqrs` VALUES (1,'Andres Prueba','andres.prueba@ejemplo.com','3001234567','Petición','Mensaje de prueba','En trámite','2026-06-29 16:43:25');
/*!40000 ALTER TABLE `pqrs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proyectos`
--

DROP TABLE IF EXISTS `proyectos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proyectos` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `NombreCliente` varchar(256) NOT NULL,
  `Email` varchar(256) NOT NULL,
  `Telefono` varchar(50) NOT NULL,
  `NombreEmpresa` varchar(256) DEFAULT NULL,
  `TipoProyecto` varchar(256) NOT NULL,
  `PresupuestoEstimado` varchar(256) NOT NULL,
  `Descripcion` text NOT NULL,
  `Estado` varchar(50) DEFAULT 'Pendiente',
  `FechaCreacion` datetime NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proyectos`
--

LOCK TABLES `proyectos` WRITE;
/*!40000 ALTER TABLE `proyectos` DISABLE KEYS */;
/*!40000 ALTER TABLE `proyectos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(256) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Nombre` (`Nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador'),(2,'Cliente');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `Id` varchar(450) NOT NULL,
  `Username` varchar(256) NOT NULL,
  `Email` varchar(256) NOT NULL,
  `PasswordHash` text NOT NULL,
  `RolId` int(11) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Username` (`Username`),
  KEY `RolId` (`RolId`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`RolId`) REFERENCES `roles` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES ('admin-guid-local-000000000000000001','admin','admin@xcolnet.com','$2y$10$ilqtxQKihRy/kPE992lRfOT2vyLJjRTfuEBur0nv9LP.Ee9jHBCk.',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `estilo_css` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES 
(1,'EMPRESA_A',NULL,'font-bold tracking-widest',1,1,NOW()),
(2,'TECH_CORP',NULL,'font-extrabold italic',2,1,NOW()),
(3,'GLOBAL_NET',NULL,'font-bold uppercase',3,1,NOW()),
(4,'SYSTEMS',NULL,'font-medium',4,1,NOW()),
(5,'DATA_HUB',NULL,'font-light tracking-tighter',5,1,NOW()),
(6,'INFRA_STRUC',NULL,'font-bold',6,1,NOW());
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-03 21:17:00
