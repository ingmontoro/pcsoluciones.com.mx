-- MySQL dump 10.13  Distrib 5.7.9, for Win32 (AMD64)
--
-- Host: 127.0.0.1    Database: pcsoluciones
-- ------------------------------------------------------
-- Server version	5.6.15-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `articulo`
--

DROP TABLE IF EXISTS `articulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articulo` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(15) CHARACTER SET latin1 NOT NULL,
  `nombre` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
  `descripcion` text CHARACTER SET latin1,
  `cantidad` int(11) DEFAULT '999',
  `precio` float NOT NULL,
  `categoria` int(11) DEFAULT '7',
  `corta` varchar(256) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`clave`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=315 DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categoria` (
  `clave` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `cat` varchar(5) NOT NULL,
  PRIMARY KEY (`clave`),
  UNIQUE KEY `clave` (`clave`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) CHARACTER SET latin1 NOT NULL,
  `nombre_fiscal` varchar(199) CHARACTER SET latin1 DEFAULT NULL,
  `apellido1` varchar(30) CHARACTER SET latin1 NOT NULL,
  `apellido2` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
  `rfc` varchar(13) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1813 DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cliente_domicilio`
--

DROP TABLE IF EXISTS `cliente_domicilio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente_domicilio` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `idCliente` int(11) NOT NULL,
  `claveDomicilio` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=1813 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cliente_telefono`
--

DROP TABLE IF EXISTS `cliente_telefono`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente_telefono` (
  `idCliente` int(11) NOT NULL,
  `claveTelefono` int(11) NOT NULL,
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `default` tinyint(1) NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=1813 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cobro`
--

DROP TABLE IF EXISTS `cobro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cobro` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `claveNota` int(11) NOT NULL,
  `fechaCobro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total` float NOT NULL,
  `entregado` float NOT NULL,
  `cambio` float NOT NULL,
  `idUsuarioCancel` int(11) DEFAULT NULL,
  `fechaCancelacion` timestamp NULL DEFAULT NULL,
  `idUsuario` int(11) DEFAULT NULL,
  `impresora` int(11) DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=MyISAM AUTO_INCREMENT=8919 DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detalle_nota`
--

DROP TABLE IF EXISTS `detalle_nota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_nota` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `folio` int(11) NOT NULL,
  `claveArticulo` varchar(8) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descripcion` varchar(256) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `precio` float DEFAULT '0',
  `accesorio` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=11076 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `domicilio`
--

DROP TABLE IF EXISTS `domicilio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domicilio` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `calle` varchar(256) DEFAULT NULL,
  `numint` varchar(7) DEFAULT NULL,
  `numext` varchar(7) DEFAULT NULL,
  `cruzacon` varchar(128) DEFAULT NULL,
  `ycon` varchar(128) DEFAULT NULL,
  `colonia` varchar(124) DEFAULT NULL,
  `ciudad` varchar(64) DEFAULT NULL,
  `estado` varchar(64) DEFAULT NULL,
  `cp` int(11) DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=1813 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `imagen`
--

DROP TABLE IF EXISTS `imagen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imagen` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `ruta` text NOT NULL,
  `nombre` int(128) NOT NULL,
  `otro` text NOT NULL,
  PRIMARY KEY (`clave`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_estatus`
--

DROP TABLE IF EXISTS `log_estatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_estatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_orden`
--

DROP TABLE IF EXISTS `log_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idRealizo` int(11) NOT NULL,
  `log` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1',
  `idElimino` int(11) NOT NULL,
  `idOrden` int(11) NOT NULL,
  `fechaCambio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idCambio` int(11) NOT NULL,
  `idAsignado` int(11) NOT NULL DEFAULT '-1',
  `idEstatus` int(11) NOT NULL DEFAULT '1',
  `fechaTermino` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5098 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nota`
--

DROP TABLE IF EXISTS `nota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nota` (
  `numero` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaCobro` timestamp NULL DEFAULT NULL,
  `idCliente` int(11) DEFAULT NULL,
  `subtotal` float NOT NULL,
  `iva` float DEFAULT NULL,
  `total` float NOT NULL,
  `estatus` int(11) NOT NULL,
  `folio` int(11) DEFAULT NULL,
  `claveCobro` int(11) DEFAULT NULL,
  PRIMARY KEY (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=8983 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nota_estatus`
--

DROP TABLE IF EXISTS `nota_estatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nota_estatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orden_estatus`
--

DROP TABLE IF EXISTS `orden_estatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orden_estatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orden_servicio`
--

DROP TABLE IF EXISTS `orden_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orden_servicio` (
  `numero` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idCliente` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `idRecibio` int(11) NOT NULL,
  `idAsignado` int(11) NOT NULL,
  `estatus` int(11) NOT NULL,
  `idEntrego` int(11) DEFAULT NULL,
  `fechaT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `accesorios` varchar(256) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  PRIMARY KEY (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=3028 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `persona`
--

DROP TABLE IF EXISTS `persona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persona` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) NOT NULL,
  `apellido1` varchar(30) NOT NULL,
  `apellido2` varchar(30) NOT NULL,
  `fechanacimiento` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `telefono`
--

DROP TABLE IF EXISTS `telefono`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telefono` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) NOT NULL,
  `numero` varchar(15) NOT NULL,
  `tipo2` int(11) DEFAULT NULL,
  `numero2` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=1813 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipotelefono`
--

DROP TABLE IF EXISTS `tipotelefono`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipotelefono` (
  `clave` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(32) NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(15) NOT NULL,
  `password` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `activo` char(1) NOT NULL,
  `idpersona` int(11) NOT NULL,
  `interno` char(1) NOT NULL DEFAULT '0',
  `alias` varchar(20) DEFAULT NULL,
  `super` tinyint(4) NOT NULL DEFAULT '0',
  `imagen` varchar(200) DEFAULT NULL,
  `ajuste` varchar(45) DEFAULT NULL,
  `posicion` varchar(45) DEFAULT NULL,
  `profile` varchar(45) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-12 16:37:45
