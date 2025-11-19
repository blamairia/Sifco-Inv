/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: sifco_inv
-- ------------------------------------------------------
-- Server version	11.8.3-MariaDB-0+deb13u1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `bon_entree_items`
--

DROP TABLE IF EXISTS `bon_entree_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_entree_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_entree_id` bigint(20) unsigned NOT NULL,
  `item_type` enum('bobine','product','pallet') DEFAULT 'product',
  `product_id` bigint(20) unsigned NOT NULL,
  `ean_13` varchar(64) DEFAULT NULL COMMENT 'For bobines only',
  `batch_number` varchar(100) DEFAULT NULL COMMENT 'Supplier batch number',
  `roll_id` bigint(20) unsigned DEFAULT NULL,
  `qty_entered` decimal(15,2) NOT NULL,
  `weight_kg` decimal(12,3) DEFAULT NULL,
  `length_m` decimal(12,3) DEFAULT NULL,
  `sheet_width_mm` decimal(10,2) DEFAULT NULL,
  `sheet_length_mm` decimal(10,2) DEFAULT NULL,
  `price_ht` decimal(12,2) NOT NULL COMMENT 'Unit price before fees',
  `price_ttc` decimal(12,2) NOT NULL COMMENT 'Unit price after fees distribution',
  `line_total_ttc` decimal(15,2) GENERATED ALWAYS AS (`qty_entered` * `price_ttc`) STORED,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bon_entree_items_ean_13_unique` (`ean_13`),
  KEY `bon_entree_items_product_id_foreign` (`product_id`),
  KEY `bon_entree_items_bon_entree_id_index` (`bon_entree_id`),
  KEY `bon_entree_items_roll_id_foreign` (`roll_id`),
  KEY `bon_entree_items_item_type_bon_entree_id_index` (`item_type`,`bon_entree_id`),
  CONSTRAINT `bon_entree_items_bon_entree_id_foreign` FOREIGN KEY (`bon_entree_id`) REFERENCES `bon_entrees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_entree_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_entree_items_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_entree_items`
--

LOCK TABLES `bon_entree_items` WRITE;
/*!40000 ALTER TABLE `bon_entree_items` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `bon_entree_items` VALUES
(1,1,'bobine',3,'1842',NULL,1,1.00,1842.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 19:52:23','2025-11-18 19:52:57'),
(2,2,'bobine',2,'81212318',NULL,28,1.00,2628.000,9224.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(3,2,'bobine',3,'93550637',NULL,29,1.00,1894.000,6626.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(4,2,'bobine',1,'1830',NULL,30,1.00,1830.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(5,2,'bobine',3,'93550640',NULL,31,1.00,1896.000,6632.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(6,2,'bobine',1,'1970',NULL,32,1.00,1970.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(7,2,'bobine',1,'1808',NULL,33,1.00,1808.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(8,2,'bobine',1,'1814',NULL,34,1.00,1814.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(9,2,'bobine',1,'1892',NULL,35,1.00,1892.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(10,2,'bobine',1,'1912',NULL,36,1.00,1912.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(11,2,'bobine',1,'1836',NULL,37,1.00,1836.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(12,2,'bobine',13,'13560280',NULL,38,1.00,2310.000,8370.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(13,2,'bobine',3,'13488966',NULL,39,1.00,2354.000,8529.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:02','2025-11-19 00:12:48'),
(14,2,'bobine',4,'1253618152',NULL,40,1.00,2946.000,8546.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:02','2025-11-19 00:12:48'),
(15,2,'bobine',4,'1253621212',NULL,41,1.00,2739.000,8040.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:33:02','2025-11-19 00:12:48'),
(70,8,'bobine',1,'1922',NULL,2,1.00,1922.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(71,8,'bobine',1,'1862',NULL,3,1.00,1862.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(72,8,'bobine',2,'81212322',NULL,4,1.00,2681.000,9373.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(73,8,'bobine',1,'1902',NULL,5,1.00,1902.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(74,8,'bobine',1,'1770',NULL,6,1.00,1770.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(75,8,'bobine',1,'1840',NULL,7,1.00,1840.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(76,8,'bobine',1,'1872',NULL,8,1.00,1872.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(77,8,'bobine',1,'1776',NULL,9,1.00,1776.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(78,8,'bobine',1,'1906',NULL,10,1.00,1906.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(79,8,'bobine',1,'1832',NULL,11,1.00,1832.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(80,8,'bobine',1,'1860',NULL,12,1.00,1860.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(81,8,'bobine',1,'1806',NULL,13,1.00,1806.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(82,8,'bobine',1,'1868',NULL,14,1.00,1868.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(83,8,'bobine',1,'1858',NULL,15,1.00,1858.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(84,8,'bobine',1,'1838',NULL,16,1.00,1838.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(85,8,'bobine',1,'1930',NULL,17,1.00,1930.000,1.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(86,8,'bobine',2,'81212365',NULL,18,1.00,2523.000,9348.000,NULL,NULL,0.00,0.00,0.00,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(87,8,'bobine',10,'312653294',NULL,19,1.00,2725.000,9750.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:08:09','2025-11-19 00:12:17'),
(88,8,'bobine',11,'312653785',NULL,20,1.00,3030.000,10600.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:08:09','2025-11-19 00:12:17'),
(89,8,'bobine',13,'13560267',NULL,21,1.00,2308.000,8362.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:08:09','2025-11-19 00:12:17'),
(90,8,'bobine',4,'1253219762',NULL,22,1.00,2766.000,8025.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:08:09','2025-11-19 00:12:17'),
(91,8,'bobine',4,'1253621252',NULL,23,1.00,2755.000,8078.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:08:09','2025-11-19 00:12:17'),
(92,8,'bobine',4,'1253219752',NULL,24,1.00,2754.000,7994.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:08:09','2025-11-19 00:12:17'),
(93,8,'bobine',10,'312653752',NULL,25,1.00,2730.000,9750.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:11:38','2025-11-19 00:12:17'),
(94,8,'bobine',10,'312653300',NULL,26,1.00,2725.000,9751.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:11:38','2025-11-19 00:12:17'),
(95,8,'bobine',11,'31265379',NULL,27,1.00,3030.000,10600.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 00:11:38','2025-11-19 00:12:17'),
(96,9,'bobine',8,'12650242241392',NULL,NULL,1.00,2546.000,9588.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 12:16:51','2025-11-19 12:16:51'),
(97,9,'bobine',9,'12650175341392',NULL,NULL,1.00,2738.000,9649.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 12:16:51','2025-11-19 12:16:51'),
(98,9,'bobine',9,'12650182241392',NULL,NULL,1.00,2790.000,9743.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 12:16:51','2025-11-19 12:16:51'),
(99,9,'bobine',9,'12650168441392',NULL,NULL,1.00,2792.000,9550.000,NULL,NULL,0.00,0.00,0.00,'2025-11-19 12:17:22','2025-11-19 12:17:22');
/*!40000 ALTER TABLE `bon_entree_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_entrees`
--

DROP TABLE IF EXISTS `bon_entrees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_entrees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_number` varchar(191) NOT NULL,
  `sourceable_type` varchar(191) DEFAULT NULL,
  `sourceable_id` bigint(20) unsigned DEFAULT NULL,
  `document_number` varchar(191) DEFAULT NULL COMMENT 'Supplier invoice/delivery number',
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `expected_date` date DEFAULT NULL COMMENT 'Expected arrival date',
  `received_date` date DEFAULT NULL COMMENT 'Actual received date',
  `status` enum('draft','pending','validated','received','cancelled') NOT NULL DEFAULT 'draft',
  `total_amount_ttc` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Including frais d''approche',
  `total_amount_ht` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Before frais',
  `frais_approche` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Transport, D3, transitaire',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bon_entrees_bon_number_unique` (`bon_number`),
  KEY `bon_entrees_warehouse_id_receipt_date_index` (`warehouse_id`),
  KEY `bon_entrees_sourceable_index` (`sourceable_type`,`sourceable_id`),
  CONSTRAINT `bon_entrees_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_entrees`
--

LOCK TABLES `bon_entrees` WRITE;
/*!40000 ALTER TABLE `bon_entrees` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `bon_entrees` VALUES
(1,'BENT-20251118-0001','App\\Models\\Supplier',1,NULL,1,NULL,'2025-11-18','received',0.00,0.00,0.00,NULL,'2025-11-18 19:52:23','2025-11-18 19:52:57'),
(2,'BENT-20251118-0002','App\\Models\\Supplier',1,NULL,1,NULL,'2025-11-18','received',0.00,0.00,0.00,NULL,'2025-11-18 23:33:01','2025-11-19 00:12:48'),
(8,'BENT-20251118-0003','App\\Models\\Supplier',1,NULL,1,NULL,'2025-11-18','received',0.00,0.00,0.00,NULL,'2025-11-18 23:58:39','2025-11-19 00:12:17'),
(9,'BENT-20251119-0001','App\\Models\\Supplier',1,NULL,1,NULL,NULL,'draft',0.00,0.00,0.00,NULL,'2025-11-19 12:16:51','2025-11-19 12:16:51');
/*!40000 ALTER TABLE `bon_entrees` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_receptions`
--

DROP TABLE IF EXISTS `bon_receptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_receptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_number` varchar(191) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `receipt_date` date NOT NULL,
  `delivery_note_ref` varchar(191) DEFAULT NULL COMMENT 'Bon de livraison fournisseur',
  `purchase_order_ref` varchar(191) DEFAULT NULL COMMENT 'Bon de commande',
  `status` enum('received','verified','conformity_issue','rejected') NOT NULL DEFAULT 'received',
  `verified_by_id` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `conformity_issues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{missing, surplus, damaged}' CHECK (json_valid(`conformity_issues`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bon_receptions_bon_number_unique` (`bon_number`),
  KEY `bon_receptions_verified_by_id_foreign` (`verified_by_id`),
  KEY `bon_receptions_supplier_id_receipt_date_index` (`supplier_id`,`receipt_date`),
  KEY `bon_receptions_status_index` (`status`),
  CONSTRAINT `bon_receptions_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_receptions_verified_by_id_foreign` FOREIGN KEY (`verified_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_receptions`
--

LOCK TABLES `bon_receptions` WRITE;
/*!40000 ALTER TABLE `bon_receptions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bon_receptions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_reintegration_items`
--

DROP TABLE IF EXISTS `bon_reintegration_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_reintegration_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_reintegration_id` bigint(20) unsigned NOT NULL,
  `item_type` enum('product','roll') NOT NULL DEFAULT 'product',
  `product_id` bigint(20) unsigned NOT NULL,
  `roll_id` bigint(20) unsigned DEFAULT NULL,
  `qty_returned` decimal(15,2) NOT NULL,
  `previous_weight_kg` decimal(12,3) DEFAULT NULL,
  `previous_length_m` decimal(12,3) DEFAULT NULL,
  `returned_weight_kg` decimal(12,3) DEFAULT NULL,
  `returned_length_m` decimal(12,3) DEFAULT NULL,
  `weight_delta_kg` decimal(12,3) DEFAULT NULL,
  `length_delta_m` decimal(12,3) DEFAULT NULL,
  `cump_at_return` decimal(12,2) DEFAULT NULL,
  `value_returned` decimal(15,2) NOT NULL COMMENT 'qty * cump_at_return',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bon_reintegration_items_product_id_foreign` (`product_id`),
  KEY `bon_reintegration_items_bon_reintegration_id_index` (`bon_reintegration_id`),
  KEY `bon_reintegration_items_roll_id_foreign` (`roll_id`),
  CONSTRAINT `bon_reintegration_items_bon_reintegration_id_foreign` FOREIGN KEY (`bon_reintegration_id`) REFERENCES `bon_reintegrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_reintegration_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_reintegration_items_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_reintegration_items`
--

LOCK TABLES `bon_reintegration_items` WRITE;
/*!40000 ALTER TABLE `bon_reintegration_items` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bon_reintegration_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_reintegrations`
--

DROP TABLE IF EXISTS `bon_reintegrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_reintegrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_number` varchar(191) NOT NULL,
  `bon_sortie_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `return_date` date NOT NULL,
  `status` enum('draft','received','verified','confirmed','archived') NOT NULL DEFAULT 'draft',
  `verified_by_id` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `cump_at_return` decimal(12,2) NOT NULL COMMENT 'CUMP from original issue date',
  `notes` text DEFAULT NULL,
  `physical_condition` varchar(191) DEFAULT NULL COMMENT 'unopened, slight_damage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bon_reintegrations_bon_number_unique` (`bon_number`),
  KEY `bon_reintegrations_bon_sortie_id_foreign` (`bon_sortie_id`),
  KEY `bon_reintegrations_verified_by_id_foreign` (`verified_by_id`),
  KEY `bon_reintegrations_warehouse_id_return_date_index` (`warehouse_id`,`return_date`),
  KEY `bon_reintegrations_status_index` (`status`),
  CONSTRAINT `bon_reintegrations_bon_sortie_id_foreign` FOREIGN KEY (`bon_sortie_id`) REFERENCES `bon_sorties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_reintegrations_verified_by_id_foreign` FOREIGN KEY (`verified_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bon_reintegrations_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_reintegrations`
--

LOCK TABLES `bon_reintegrations` WRITE;
/*!40000 ALTER TABLE `bon_reintegrations` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bon_reintegrations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_sortie_items`
--

DROP TABLE IF EXISTS `bon_sortie_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_sortie_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` varchar(20) NOT NULL DEFAULT 'product',
  `bon_sortie_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `roll_id` bigint(20) unsigned DEFAULT NULL,
  `qty_issued` decimal(15,2) DEFAULT NULL,
  `weight_kg` decimal(12,3) DEFAULT NULL,
  `length_m` decimal(12,3) DEFAULT NULL,
  `sheet_width_mm` decimal(10,2) DEFAULT NULL,
  `sheet_length_mm` decimal(10,2) DEFAULT NULL,
  `cump_at_issue` decimal(12,2) NOT NULL COMMENT 'CUMP snapshot for valuation',
  `value_issued` decimal(15,2) GENERATED ALWAYS AS (`qty_issued` * `cump_at_issue`) STORED,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bon_sortie_items_product_id_foreign` (`product_id`),
  KEY `bon_sortie_items_bon_sortie_id_index` (`bon_sortie_id`),
  KEY `bon_sortie_items_roll_id_foreign` (`roll_id`),
  CONSTRAINT `bon_sortie_items_bon_sortie_id_foreign` FOREIGN KEY (`bon_sortie_id`) REFERENCES `bon_sorties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_sortie_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_sortie_items_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_sortie_items`
--

LOCK TABLES `bon_sortie_items` WRITE;
/*!40000 ALTER TABLE `bon_sortie_items` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bon_sortie_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_sorties`
--

DROP TABLE IF EXISTS `bon_sorties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_sorties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_number` varchar(191) NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `issued_date` date NOT NULL,
  `status` enum('draft','issued','confirmed','archived') NOT NULL DEFAULT 'draft',
  `issued_by_id` bigint(20) unsigned DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `destination` varchar(191) NOT NULL COMMENT 'Production, Client, department',
  `destinationable_type` varchar(191) DEFAULT NULL,
  `destinationable_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bon_sorties_bon_number_unique` (`bon_number`),
  KEY `bon_sorties_issued_by_id_foreign` (`issued_by_id`),
  KEY `bon_sorties_warehouse_id_issued_date_index` (`warehouse_id`,`issued_date`),
  KEY `bon_sorties_status_index` (`status`),
  KEY `bon_sorties_destinationable_index` (`destinationable_type`,`destinationable_id`),
  CONSTRAINT `bon_sorties_issued_by_id_foreign` FOREIGN KEY (`issued_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bon_sorties_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_sorties`
--

LOCK TABLES `bon_sorties` WRITE;
/*!40000 ALTER TABLE `bon_sorties` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bon_sorties` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_transfert_items`
--

DROP TABLE IF EXISTS `bon_transfert_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_transfert_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` varchar(20) NOT NULL DEFAULT 'product' COMMENT 'roll or product',
  `bon_transfert_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `roll_id` bigint(20) unsigned DEFAULT NULL,
  `movement_out_id` bigint(20) unsigned DEFAULT NULL,
  `movement_in_id` bigint(20) unsigned DEFAULT NULL,
  `qty_transferred` decimal(15,2) NOT NULL,
  `weight_transferred_kg` decimal(12,3) DEFAULT NULL,
  `length_transferred_m` decimal(12,3) DEFAULT NULL,
  `cump_at_transfer` decimal(12,2) NOT NULL COMMENT 'Transfer at original cost',
  `value_transferred` decimal(15,2) GENERATED ALWAYS AS (`qty_transferred` * `cump_at_transfer`) STORED,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bon_transfert_items_product_id_foreign` (`product_id`),
  KEY `bon_transfert_items_bon_transfert_id_index` (`bon_transfert_id`),
  KEY `bon_transfert_items_roll_id_foreign` (`roll_id`),
  KEY `bon_transfert_items_movement_out_id_foreign` (`movement_out_id`),
  KEY `bon_transfert_items_movement_in_id_foreign` (`movement_in_id`),
  CONSTRAINT `bon_transfert_items_bon_transfert_id_foreign` FOREIGN KEY (`bon_transfert_id`) REFERENCES `bon_transferts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_transfert_items_movement_in_id_foreign` FOREIGN KEY (`movement_in_id`) REFERENCES `stock_movements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bon_transfert_items_movement_out_id_foreign` FOREIGN KEY (`movement_out_id`) REFERENCES `stock_movements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bon_transfert_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_transfert_items_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_transfert_items`
--

LOCK TABLES `bon_transfert_items` WRITE;
/*!40000 ALTER TABLE `bon_transfert_items` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bon_transfert_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bon_transferts`
--

DROP TABLE IF EXISTS `bon_transferts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bon_transferts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_number` varchar(191) NOT NULL,
  `warehouse_from_id` bigint(20) unsigned NOT NULL,
  `warehouse_to_id` bigint(20) unsigned NOT NULL,
  `transfer_date` date NOT NULL,
  `status` enum('draft','in_transit','received','confirmed','cancelled','archived') DEFAULT 'draft',
  `requested_by_id` bigint(20) unsigned DEFAULT NULL,
  `transferred_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `received_by_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bon_transferts_bon_number_unique` (`bon_number`),
  KEY `bon_transferts_warehouse_to_id_foreign` (`warehouse_to_id`),
  KEY `bon_transferts_requested_by_id_foreign` (`requested_by_id`),
  KEY `bon_transferts_received_by_id_foreign` (`received_by_id`),
  KEY `bon_transferts_warehouse_from_id_warehouse_to_id_index` (`warehouse_from_id`,`warehouse_to_id`),
  KEY `bon_transferts_status_index` (`status`),
  CONSTRAINT `bon_transferts_received_by_id_foreign` FOREIGN KEY (`received_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bon_transferts_requested_by_id_foreign` FOREIGN KEY (`requested_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bon_transferts_warehouse_from_id_foreign` FOREIGN KEY (`warehouse_from_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bon_transferts_warehouse_to_id_foreign` FOREIGN KEY (`warehouse_to_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bon_transferts`
--

LOCK TABLES `bon_transferts` WRITE;
/*!40000 ALTER TABLE `bon_transferts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bon_transferts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `cache` VALUES
('laravel-cache-livewire-rate-limiter:84c59cafc4d9775da48ae1f4fa6123ec79857a57','i:1;',1763490032),
('laravel-cache-livewire-rate-limiter:84c59cafc4d9775da48ae1f4fa6123ec79857a57:timer','i:1763490032;',1763490032),
('laravel-cache-livewire-rate-limiter:a6aeb513f8f2164956915a4a203342569402f717','i:1;',1763536366),
('laravel-cache-livewire-rate-limiter:a6aeb513f8f2164956915a4a203342569402f717:timer','i:1763536366;',1763536366),
('laravel-cache-livewire-rate-limiter:d290ce23ae267aed29fb0e6f770c58a4e7f67924','i:1;',1763535935),
('laravel-cache-livewire-rate-limiter:d290ce23ae267aed29fb0e6f770c58a4e7f67924:timer','i:1763535935;',1763535935);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `categories` VALUES
(1,'Papiers Kraftliner','Papiers kraft pour couvertures','2025-11-18 15:04:54','2025-11-18 15:04:54'),
(2,'Papiers Test/Fluting','Papiers pour cannelures','2025-11-18 15:04:54','2025-11-18 15:04:54'),
(3,'Papiers Recyclés','Papiers à base recyclée','2025-11-18 15:04:54','2025-11-18 15:04:54'),
(4,'Consommables Production','Fournitures production','2025-11-18 15:04:54','2025-11-18 15:04:54'),
(5,'Produits Finis','Cartons ondulés finis','2025-11-18 15:04:54','2025-11-18 15:04:54'),
(6,'Accessoires Emballage','Films, adhésifs, etc.','2025-11-18 15:04:54','2025-11-18 15:04:54'),
(7,'Papier Maron',NULL,'2025-11-18 18:29:50','2025-11-18 18:29:50'),
(8,'TLM',NULL,'2025-11-18 18:30:08','2025-11-18 18:30:08'),
(10,'KL Kraft ',NULL,'2025-11-18 18:30:31','2025-11-18 18:30:31'),
(11,'TLB Blanc',NULL,'2025-11-18 18:30:41','2025-11-18 18:30:41'),
(12,'FL Fluiting ',NULL,'2025-11-18 18:30:57','2025-11-18 18:30:57'),
(13,'CB Couché Blanc',NULL,'2025-11-18 18:31:52','2025-11-18 18:31:52'),
(14,'SCB Semi Couché Blanc',NULL,'2025-11-18 18:37:15','2025-11-18 18:37:15');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(191) NOT NULL,
  `contact_person` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `mobile` varchar(191) DEFAULT NULL,
  `tax_number` varchar(191) DEFAULT NULL,
  `address_line1` varchar(191) DEFAULT NULL,
  `address_line2` varchar(191) DEFAULT NULL,
  `city` varchar(191) DEFAULT NULL,
  `country` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_code_unique` (`code`),
  KEY `clients_is_active_city_index` (`is_active`,`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `import_records`
--

DROP TABLE IF EXISTS `import_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `external_id` varchar(191) NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `import_records_external_id_unique` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_records`
--

LOCK TABLES `import_records` WRITE;
/*!40000 ALTER TABLE `import_records` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `import_records` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `low_stock_alerts`
--

DROP TABLE IF EXISTS `low_stock_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `low_stock_alerts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `current_qty` decimal(15,2) NOT NULL COMMENT 'Quantity at alert time',
  `min_stock` decimal(15,2) NOT NULL COMMENT 'Minimum stock threshold',
  `safety_stock` decimal(15,2) DEFAULT NULL COMMENT 'Safety stock threshold',
  `severity` enum('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL DEFAULT 'MEDIUM',
  `status` enum('ACTIVE','RESOLVED','IGNORED') NOT NULL DEFAULT 'ACTIVE',
  `notes` text DEFAULT NULL,
  `resolved_by` bigint(20) unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `low_stock_alerts_warehouse_id_foreign` (`warehouse_id`),
  KEY `low_stock_alerts_resolved_by_foreign` (`resolved_by`),
  KEY `low_stock_alerts_product_id_warehouse_id_index` (`product_id`,`warehouse_id`),
  KEY `low_stock_alerts_status_severity_index` (`status`,`severity`),
  KEY `low_stock_alerts_created_at_index` (`created_at`),
  CONSTRAINT `low_stock_alerts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `low_stock_alerts_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `low_stock_alerts_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `low_stock_alerts`
--

LOCK TABLES `low_stock_alerts` WRITE;
/*!40000 ALTER TABLE `low_stock_alerts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `low_stock_alerts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2025_10_30_210220_create_categories_table',1),
(5,'2025_10_30_210220_create_units_table',1),
(6,'2025_10_30_210221_create_suppliers_table',1),
(7,'2025_10_30_210221_create_warehouses_table',1),
(8,'2025_10_30_210227_create_products_table',1),
(9,'2025_10_30_210228_create_product_category_table',1),
(10,'2025_10_30_210228_create_stock_quantities_table',1),
(11,'2025_10_30_210229_create_rolls_table',1),
(12,'2025_10_30_210234_create_stock_movements_table',1),
(13,'2025_10_30_210235_create_bon_receptions_table',1),
(14,'2025_10_30_210236_create_bon_entrees_table',1),
(15,'2025_10_30_210237_create_bon_entree_items_table',1),
(16,'2025_10_30_210243_create_bon_sorties_table',1),
(17,'2025_10_30_210244_create_bon_sortie_items_table',1),
(18,'2025_10_30_210244_create_bon_transferts_table',1),
(19,'2025_10_30_210245_create_bon_transfert_items_table',1),
(20,'2025_10_30_210250_create_bon_reintegrations_table',1),
(21,'2025_10_30_210251_create_bon_reintegration_items_table',1),
(22,'2025_10_30_220000_add_is_roll_to_products_table',1),
(23,'2025_11_02_120341_modify_bon_entrees_table_structure',1),
(24,'2025_11_02_123204_add_created_at_to_stock_quantities_table',1),
(25,'2025_11_03_071859_add_bon_entree_id_to_rolls_table',1),
(26,'2025_11_03_091852_add_bobine_fields_to_bon_entree_items_table',1),
(27,'2025_11_03_124253_add_roll_id_to_bon_sortie_items_table',1),
(28,'2025_11_03_124258_add_roll_id_to_bon_sortie_items_table',1),
(29,'2025_11_03_131044_add_item_type_to_bon_sortie_items_table',1),
(30,'2025_11_03_135852_add_item_type_and_roll_id_to_bon_transfert_items_table',1),
(31,'2025_11_03_143952_update_bon_transferts_status_enum',1),
(32,'2025_11_05_121013_create_stock_adjustments_table',1),
(33,'2025_11_05_121159_add_adjustment_type_to_stock_movements_table',1),
(34,'2025_11_05_121609_create_low_stock_alerts_table',1),
(35,'2025_11_06_100936_create_roll_adjustments_table',1),
(36,'2025_11_06_100950_add_manual_fields_to_rolls_table',1),
(37,'2025_11_06_120000_add_weight_columns_to_roll_adjustments_table',1),
(38,'2025_11_06_120010_add_total_weight_to_stock_quantities_table',1),
(39,'2025_11_06_120020_add_weight_columns_to_stock_movements_table',1),
(40,'2025_11_06_120030_add_weight_adjustment_type_to_roll_adjustments_table',1),
(41,'2025_11_06_130000_add_roll_fields_to_bon_reintegration_items_table',1),
(42,'2025_11_06_140500_add_weight_columns_to_bon_items_tables',1),
(43,'2025_11_06_141010_add_last_movement_to_stock_quantities_table',1),
(44,'2025_11_06_150500_add_weight_metrics_to_stock_adjustments_table',1),
(45,'2025_11_09_000001_add_movement_links_to_bon_transfert_items_table',1),
(46,'2025_11_09_103500_update_stock_movements_status_enum',1),
(47,'2025_11_09_113000_add_length_metrics_to_rolls_and_stock_tables',1),
(48,'2025_11_09_130000_add_length_metrics_to_outbound_and_adjustment_tables',1),
(49,'2025_11_09_140000_create_roll_lifecycle_events_table',1),
(50,'2025_11_11_094428_create_production_lines_table',1),
(51,'2025_11_11_095711_add_product_type_to_products_table',1),
(52,'2025_11_11_095919_add_sourceable_columns_to_bon_entrees_table',1),
(53,'2025_11_11_095930_add_destinationable_columns_to_bon_sorties_table',1),
(54,'2025_11_11_160000_create_clients_table',1),
(55,'2025_11_11_160100_add_sheet_dimensions_to_products_table',1),
(56,'2025_11_11_160200_add_sheet_dimensions_to_bon_items_tables',1),
(57,'2025_11_11_170500_extend_item_type_for_pallets_on_bon_entree_items_table',1),
(58,'2025_11_11_171500_add_timestamps_to_product_category_table',1),
(59,'2025_11_12_112900_refactor_product_classification_system',1),
(60,'2025_11_12_130000_create_import_records_table',1),
(61,'2025_11_16_100000_add_auto_code_to_products',1),
(62,'2025_11_18_180000_extend_ean13_columns',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `product_category`
--

DROP TABLE IF EXISTS `product_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Primary category for quick access',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_category_product_id_category_id_unique` (`product_id`,`category_id`),
  KEY `product_category_category_id_foreign` (`category_id`),
  CONSTRAINT `product_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_category_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_category`
--

LOCK TABLES `product_category` WRITE;
/*!40000 ALTER TABLE `product_category` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `product_category` VALUES
(1,2,8,1,'2025-11-18 18:38:14','2025-11-18 18:38:14'),
(2,3,8,1,'2025-11-18 18:38:58','2025-11-18 18:38:58'),
(3,4,8,1,'2025-11-18 18:40:09','2025-11-18 18:40:09'),
(4,5,8,1,'2025-11-18 18:41:17','2025-11-18 18:41:17'),
(5,6,8,1,'2025-11-18 18:41:55','2025-11-18 18:41:55'),
(6,7,8,1,'2025-11-18 18:42:57','2025-11-18 18:42:57'),
(7,8,12,1,'2025-11-18 19:27:02','2025-11-18 19:27:02'),
(8,9,12,0,'2025-11-18 19:27:54','2025-11-18 19:28:09'),
(9,10,11,1,'2025-11-18 19:29:12','2025-11-18 19:29:12'),
(10,11,11,1,'2025-11-18 19:30:46','2025-11-18 19:30:46'),
(11,12,11,1,'2025-11-18 19:31:18','2025-11-18 19:31:18'),
(12,13,10,1,'2025-11-18 19:32:56','2025-11-18 19:32:56');
/*!40000 ALTER TABLE `product_category` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `production_lines`
--

DROP TABLE IF EXISTS `production_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'active',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `production_lines_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_lines`
--

LOCK TABLES `production_lines` WRITE;
/*!40000 ALTER TABLE `production_lines` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `production_lines` VALUES
(1,'FOSBER','FOSBER','active',NULL,'2025-11-18 15:04:54','2025-11-18 15:04:54'),
(2,'MACARBOX','MACARBOX','active',NULL,'2025-11-18 15:04:54','2025-11-18 15:04:54'),
(3,'ETERNA','ETERNA','active',NULL,'2025-11-18 15:04:54','2025-11-18 15:04:54'),
(4,'CURIONI','CURIONI','active',NULL,'2025-11-18 15:04:54','2025-11-18 15:04:54');
/*!40000 ALTER TABLE `production_lines` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `auto_code` tinyint(1) NOT NULL DEFAULT 1,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `grammage` int(11) DEFAULT NULL COMMENT 'GSM - grammes par mètre carré (only for papier_roll)',
  `laize` int(11) DEFAULT NULL COMMENT 'Width in mm (only for papier_roll)',
  `sheet_width_mm` decimal(10,2) DEFAULT NULL,
  `sheet_length_mm` decimal(10,2) DEFAULT NULL,
  `flute` varchar(10) DEFAULT NULL COMMENT 'Flute type: E, B, C, etc. (only for papier_roll)',
  `type_papier` varchar(50) DEFAULT NULL COMMENT 'Kraft, Test, etc. (only for papier_roll)',
  `extra_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Flexible storage for other specs' CHECK (json_valid(`extra_attributes`)),
  `unit_id` bigint(20) unsigned DEFAULT NULL,
  `product_type` enum('raw_material','semi_finished','finished_good') NOT NULL DEFAULT 'raw_material' COMMENT 'Manufacturing stage: raw material, semi-finished, finished good',
  `form_type` enum('roll','sheet','consumable','other') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `min_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `safety_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  KEY `products_unit_id_foreign` (`unit_id`),
  KEY `products_form_type_grammage_index` (`form_type`,`grammage`),
  KEY `products_form_type_laize_index` (`form_type`,`laize`),
  KEY `products_form_type_flute_index` (`form_type`,`flute`),
  CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `products` VALUES
(1,'PROD-TL160-001',1,'TLM 230 x 160',NULL,160,230,NULL,NULL,NULL,'TLM','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 18:29:23','2025-11-18 18:29:23'),
(2,'PROD-TL115-001',1,'TLM 245 x 115',NULL,115,245,NULL,NULL,NULL,'TLM','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 18:38:14','2025-11-18 18:38:14'),
(3,'PROD-TL120-001',1,'TLM 230 x 120',NULL,120,230,NULL,NULL,NULL,NULL,'[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 18:38:42','2025-11-18 18:38:58'),
(4,'PROD-TL135-001',1,'TLM 250 x 135',NULL,135,250,NULL,NULL,NULL,'TLM','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 18:40:09','2025-11-18 18:40:09'),
(5,'PROD-TL115-002',1,'TLM 245 x 115',NULL,115,245,NULL,NULL,NULL,'TLM','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 18:41:17','2025-11-18 18:41:17'),
(6,'PROD-TL110-001',1,'TLM 250 X 110',NULL,110,250,NULL,NULL,NULL,'TLM','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 18:41:55','2025-11-18 18:41:55'),
(7,'PROD-TL110-002',1,'TLM 243 X 110',NULL,110,243,NULL,NULL,NULL,'TLM','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 18:42:57','2025-11-18 18:42:57'),
(8,'PROD-FL120-001',1,'FL 230 X 120',NULL,120,230,NULL,NULL,NULL,'FL','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 19:25:56','2025-11-18 19:27:02'),
(9,'PROD-FL120-002',1,'FL 250 X 120',NULL,120,250,NULL,NULL,NULL,NULL,'[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 19:27:54','2025-11-18 19:28:09'),
(10,'PROD-TL230-001',1,'TLB 230 X 120',NULL,120,230,NULL,NULL,NULL,'TLB','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 19:28:40','2025-11-18 19:29:12'),
(11,'PROD-TL120-002',1,'TLB 235 X 120',NULL,120,235,NULL,NULL,NULL,'TLB','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 19:30:46','2025-11-18 19:30:46'),
(12,'PROD-TL125-001',1,'TLB 230 X 125',NULL,125,230,NULL,NULL,NULL,'TLB','[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 19:31:18','2025-11-18 19:31:18'),
(13,'PROD-KL120-001',1,'KL 230 X 120',NULL,120,230,NULL,NULL,NULL,NULL,'[]',2,'raw_material','roll',1,15.00,30.00,'2025-11-18 19:32:56','2025-11-18 19:32:56');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `roll_adjustments`
--

DROP TABLE IF EXISTS `roll_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roll_adjustments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_number` varchar(191) NOT NULL,
  `roll_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `adjustment_type` enum('ADD','REMOVE','DAMAGE','RESTORE','WEIGHT_ADJUST') NOT NULL,
  `previous_status` enum('in_stock','reserved','consumed','damaged','archived') DEFAULT NULL,
  `new_status` enum('in_stock','reserved','consumed','damaged','archived') NOT NULL,
  `previous_weight_kg` decimal(12,3) DEFAULT NULL,
  `previous_length_m` decimal(12,3) DEFAULT NULL,
  `new_weight_kg` decimal(12,3) DEFAULT NULL,
  `new_length_m` decimal(12,3) DEFAULT NULL,
  `weight_delta_kg` decimal(12,3) DEFAULT NULL,
  `length_delta_m` decimal(12,3) DEFAULT NULL,
  `reason` text NOT NULL,
  `adjusted_by` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roll_adjustments_adjustment_number_unique` (`adjustment_number`),
  KEY `roll_adjustments_roll_id_foreign` (`roll_id`),
  KEY `roll_adjustments_adjusted_by_foreign` (`adjusted_by`),
  KEY `roll_adjustments_approved_by_foreign` (`approved_by`),
  KEY `roll_adjustments_warehouse_id_adjustment_type_index` (`warehouse_id`,`adjustment_type`),
  KEY `roll_adjustments_product_id_adjustment_type_index` (`product_id`,`adjustment_type`),
  CONSTRAINT `roll_adjustments_adjusted_by_foreign` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `roll_adjustments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `roll_adjustments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roll_adjustments_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roll_adjustments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roll_adjustments`
--

LOCK TABLES `roll_adjustments` WRITE;
/*!40000 ALTER TABLE `roll_adjustments` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `roll_adjustments` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `roll_lifecycle_events`
--

DROP TABLE IF EXISTS `roll_lifecycle_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roll_lifecycle_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `roll_id` bigint(20) unsigned NOT NULL,
  `stock_movement_id` bigint(20) unsigned DEFAULT NULL,
  `event_type` varchar(191) NOT NULL COMMENT 'RECEPTION, TRANSFER, SORTIE, REINTEGRATION, ADJUSTMENT',
  `reference_number` varchar(191) DEFAULT NULL COMMENT 'Related bon number',
  `weight_before_kg` decimal(12,3) NOT NULL,
  `weight_after_kg` decimal(12,3) NOT NULL,
  `weight_delta_kg` decimal(12,3) NOT NULL,
  `length_before_m` decimal(12,3) NOT NULL,
  `length_after_m` decimal(12,3) NOT NULL,
  `length_delta_m` decimal(12,3) NOT NULL,
  `has_waste` tinyint(1) NOT NULL DEFAULT 0,
  `waste_weight_kg` decimal(12,3) NOT NULL DEFAULT 0.000,
  `waste_length_m` decimal(12,3) NOT NULL DEFAULT 0.000,
  `waste_reason` varchar(191) DEFAULT NULL,
  `warehouse_from_id` bigint(20) unsigned DEFAULT NULL,
  `warehouse_to_id` bigint(20) unsigned DEFAULT NULL,
  `triggered_by_id` bigint(20) unsigned DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Event-specific data like roll_spec changes' CHECK (json_valid(`metadata`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roll_lifecycle_events_stock_movement_id_foreign` (`stock_movement_id`),
  KEY `roll_lifecycle_events_warehouse_from_id_foreign` (`warehouse_from_id`),
  KEY `roll_lifecycle_events_warehouse_to_id_foreign` (`warehouse_to_id`),
  KEY `roll_lifecycle_events_triggered_by_id_foreign` (`triggered_by_id`),
  KEY `roll_lifecycle_events_roll_id_event_type_index` (`roll_id`,`event_type`),
  KEY `roll_lifecycle_events_event_type_reference_number_index` (`event_type`,`reference_number`),
  KEY `roll_lifecycle_events_has_waste_index` (`has_waste`),
  CONSTRAINT `roll_lifecycle_events_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roll_lifecycle_events_stock_movement_id_foreign` FOREIGN KEY (`stock_movement_id`) REFERENCES `stock_movements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `roll_lifecycle_events_triggered_by_id_foreign` FOREIGN KEY (`triggered_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `roll_lifecycle_events_warehouse_from_id_foreign` FOREIGN KEY (`warehouse_from_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `roll_lifecycle_events_warehouse_to_id_foreign` FOREIGN KEY (`warehouse_to_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roll_lifecycle_events`
--

LOCK TABLES `roll_lifecycle_events` WRITE;
/*!40000 ALTER TABLE `roll_lifecycle_events` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `roll_lifecycle_events` VALUES
(1,1,1,'RECEPTION','BENT-20251118-0001',0.000,1842.000,1842.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1842','2025-11-18 19:52:57','2025-11-18 19:52:57'),
(2,2,2,'RECEPTION','BENT-20251118-0003',0.000,1922.000,1922.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1922','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(3,3,3,'RECEPTION','BENT-20251118-0003',0.000,1862.000,1862.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1862','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(4,4,4,'RECEPTION','BENT-20251118-0003',0.000,2681.000,2681.000,0.000,9373.000,9373.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 81212322','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(5,5,5,'RECEPTION','BENT-20251118-0003',0.000,1902.000,1902.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1902','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(6,6,6,'RECEPTION','BENT-20251118-0003',0.000,1770.000,1770.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1770','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(7,7,7,'RECEPTION','BENT-20251118-0003',0.000,1840.000,1840.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1840','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(8,8,8,'RECEPTION','BENT-20251118-0003',0.000,1872.000,1872.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1872','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(9,9,9,'RECEPTION','BENT-20251118-0003',0.000,1776.000,1776.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1776','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(10,10,10,'RECEPTION','BENT-20251118-0003',0.000,1906.000,1906.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1906','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(11,11,11,'RECEPTION','BENT-20251118-0003',0.000,1832.000,1832.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1832','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(12,12,12,'RECEPTION','BENT-20251118-0003',0.000,1860.000,1860.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1860','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(13,13,13,'RECEPTION','BENT-20251118-0003',0.000,1806.000,1806.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1806','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(14,14,14,'RECEPTION','BENT-20251118-0003',0.000,1868.000,1868.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1868','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(15,15,15,'RECEPTION','BENT-20251118-0003',0.000,1858.000,1858.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1858','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(16,16,16,'RECEPTION','BENT-20251118-0003',0.000,1838.000,1838.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1838','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(17,17,17,'RECEPTION','BENT-20251118-0003',0.000,1930.000,1930.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1930','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(18,18,18,'RECEPTION','BENT-20251118-0003',0.000,2523.000,2523.000,0.000,9348.000,9348.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 81212365','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(19,19,19,'RECEPTION','BENT-20251118-0003',0.000,2725.000,2725.000,0.000,9750.000,9750.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 312653294','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(20,20,20,'RECEPTION','BENT-20251118-0003',0.000,3030.000,3030.000,0.000,10600.000,10600.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 312653785','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(21,21,21,'RECEPTION','BENT-20251118-0003',0.000,2308.000,2308.000,0.000,8362.000,8362.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 13560267','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(22,22,22,'RECEPTION','BENT-20251118-0003',0.000,2766.000,2766.000,0.000,8025.000,8025.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1253219762','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(23,23,23,'RECEPTION','BENT-20251118-0003',0.000,2755.000,2755.000,0.000,8078.000,8078.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1253621252','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(24,24,24,'RECEPTION','BENT-20251118-0003',0.000,2754.000,2754.000,0.000,7994.000,7994.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1253219752','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(25,25,25,'RECEPTION','BENT-20251118-0003',0.000,2730.000,2730.000,0.000,9750.000,9750.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 312653752','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(26,26,26,'RECEPTION','BENT-20251118-0003',0.000,2725.000,2725.000,0.000,9751.000,9751.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 312653300','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(27,27,27,'RECEPTION','BENT-20251118-0003',0.000,3030.000,3030.000,0.000,10600.000,10600.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 31265379','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(28,28,28,'RECEPTION','BENT-20251118-0002',0.000,2628.000,2628.000,0.000,9224.000,9224.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 81212318','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(29,29,29,'RECEPTION','BENT-20251118-0002',0.000,1894.000,1894.000,0.000,6626.000,6626.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 93550637','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(30,30,30,'RECEPTION','BENT-20251118-0002',0.000,1830.000,1830.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1830','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(31,31,31,'RECEPTION','BENT-20251118-0002',0.000,1896.000,1896.000,0.000,6632.000,6632.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 93550640','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(32,32,32,'RECEPTION','BENT-20251118-0002',0.000,1970.000,1970.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1970','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(33,33,33,'RECEPTION','BENT-20251118-0002',0.000,1808.000,1808.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1808','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(34,34,34,'RECEPTION','BENT-20251118-0002',0.000,1814.000,1814.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1814','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(35,35,35,'RECEPTION','BENT-20251118-0002',0.000,1892.000,1892.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1892','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(36,36,36,'RECEPTION','BENT-20251118-0002',0.000,1912.000,1912.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1912','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(37,37,37,'RECEPTION','BENT-20251118-0002',0.000,1836.000,1836.000,0.000,1.000,1.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1836','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(38,38,38,'RECEPTION','BENT-20251118-0002',0.000,2310.000,2310.000,0.000,8370.000,8370.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 13560280','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(39,39,39,'RECEPTION','BENT-20251118-0002',0.000,2354.000,2354.000,0.000,8529.000,8529.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 13488966','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(40,40,40,'RECEPTION','BENT-20251118-0002',0.000,2946.000,2946.000,0.000,8546.000,8546.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1253618152','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(41,41,41,'RECEPTION','BENT-20251118-0002',0.000,2739.000,2739.000,0.000,8040.000,8040.000,0,0.000,0.000,NULL,NULL,1,1,NULL,'Réception bobine 1253621212','2025-11-19 00:12:48','2025-11-19 00:12:48');
/*!40000 ALTER TABLE `roll_lifecycle_events` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `rolls`
--

DROP TABLE IF EXISTS `rolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bon_entree_item_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `ean_13` varchar(64) NOT NULL COMMENT 'Unique barcode for this roll',
  `batch_number` varchar(191) DEFAULT NULL COMMENT 'Supplier batch',
  `received_date` date NOT NULL,
  `received_from_movement_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to stock_movements - added later',
  `status` enum('in_stock','reserved','consumed','damaged','archived') NOT NULL DEFAULT 'in_stock',
  `weight_kg` decimal(10,3) DEFAULT NULL,
  `length_m` decimal(12,3) DEFAULT NULL,
  `cump_value` decimal(15,4) DEFAULT NULL,
  `is_manual_entry` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rolls_ean_13_unique` (`ean_13`),
  KEY `rolls_warehouse_id_status_index` (`warehouse_id`,`status`),
  KEY `rolls_product_id_status_index` (`product_id`,`status`),
  KEY `rolls_bon_entree_item_id_index` (`bon_entree_item_id`),
  CONSTRAINT `rolls_bon_entree_item_id_foreign` FOREIGN KEY (`bon_entree_item_id`) REFERENCES `bon_entree_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rolls_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rolls_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rolls`
--

LOCK TABLES `rolls` WRITE;
/*!40000 ALTER TABLE `rolls` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `rolls` VALUES
(1,1,3,1,'1842',NULL,'2025-11-18',1,'in_stock',1842.000,1.000,0.0000,0,NULL,'2025-11-18 19:52:57','2025-11-18 19:52:57'),
(2,70,1,1,'1922',NULL,'2025-11-18',2,'in_stock',1922.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(3,71,1,1,'1862',NULL,'2025-11-18',3,'in_stock',1862.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(4,72,2,1,'81212322',NULL,'2025-11-18',4,'in_stock',2681.000,9373.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(5,73,1,1,'1902',NULL,'2025-11-18',5,'in_stock',1902.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(6,74,1,1,'1770',NULL,'2025-11-18',6,'in_stock',1770.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(7,75,1,1,'1840',NULL,'2025-11-18',7,'in_stock',1840.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(8,76,1,1,'1872',NULL,'2025-11-18',8,'in_stock',1872.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(9,77,1,1,'1776',NULL,'2025-11-18',9,'in_stock',1776.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(10,78,1,1,'1906',NULL,'2025-11-18',10,'in_stock',1906.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(11,79,1,1,'1832',NULL,'2025-11-18',11,'in_stock',1832.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(12,80,1,1,'1860',NULL,'2025-11-18',12,'in_stock',1860.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(13,81,1,1,'1806',NULL,'2025-11-18',13,'in_stock',1806.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(14,82,1,1,'1868',NULL,'2025-11-18',14,'in_stock',1868.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(15,83,1,1,'1858',NULL,'2025-11-18',15,'in_stock',1858.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(16,84,1,1,'1838',NULL,'2025-11-18',16,'in_stock',1838.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(17,85,1,1,'1930',NULL,'2025-11-18',17,'in_stock',1930.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(18,86,2,1,'81212365',NULL,'2025-11-18',18,'in_stock',2523.000,9348.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(19,87,10,1,'312653294',NULL,'2025-11-18',19,'in_stock',2725.000,9750.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(20,88,11,1,'312653785',NULL,'2025-11-18',20,'in_stock',3030.000,10600.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(21,89,13,1,'13560267',NULL,'2025-11-18',21,'in_stock',2308.000,8362.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(22,90,4,1,'1253219762',NULL,'2025-11-18',22,'in_stock',2766.000,8025.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(23,91,4,1,'1253621252',NULL,'2025-11-18',23,'in_stock',2755.000,8078.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(24,92,4,1,'1253219752',NULL,'2025-11-18',24,'in_stock',2754.000,7994.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(25,93,10,1,'312653752',NULL,'2025-11-18',25,'in_stock',2730.000,9750.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(26,94,10,1,'312653300',NULL,'2025-11-18',26,'in_stock',2725.000,9751.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(27,95,11,1,'31265379',NULL,'2025-11-18',27,'in_stock',3030.000,10600.000,0.0000,0,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(28,2,2,1,'81212318',NULL,'2025-11-18',28,'in_stock',2628.000,9224.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(29,3,3,1,'93550637',NULL,'2025-11-18',29,'in_stock',1894.000,6626.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(30,4,1,1,'1830',NULL,'2025-11-18',30,'in_stock',1830.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(31,5,3,1,'93550640',NULL,'2025-11-18',31,'in_stock',1896.000,6632.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(32,6,1,1,'1970',NULL,'2025-11-18',32,'in_stock',1970.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(33,7,1,1,'1808',NULL,'2025-11-18',33,'in_stock',1808.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(34,8,1,1,'1814',NULL,'2025-11-18',34,'in_stock',1814.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(35,9,1,1,'1892',NULL,'2025-11-18',35,'in_stock',1892.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(36,10,1,1,'1912',NULL,'2025-11-18',36,'in_stock',1912.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(37,11,1,1,'1836',NULL,'2025-11-18',37,'in_stock',1836.000,1.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(38,12,13,1,'13560280',NULL,'2025-11-18',38,'in_stock',2310.000,8370.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(39,13,3,1,'13488966',NULL,'2025-11-18',39,'in_stock',2354.000,8529.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(40,14,4,1,'1253618152',NULL,'2025-11-18',40,'in_stock',2946.000,8546.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48'),
(41,15,4,1,'1253621212',NULL,'2025-11-18',41,'in_stock',2739.000,8040.000,0.0000,0,NULL,'2025-11-19 00:12:48','2025-11-19 00:12:48');
/*!40000 ALTER TABLE `rolls` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `sessions` VALUES
('aEJJ0JM4pnfGWwOMA2UbElBWnOv08gJM4W5LrZTy',NULL,'139.59.5.247','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0FhOEg5UXlhR3dGdk96RVBEREtDN3RHblNtdEFudVBqT0dPVEo0SCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly8xNTQuMjQyLjM3LjM1OjgwODAiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1763533039),
('CB5DtsN7jlguNEpDu1usfx8yAwJLIC1nua4SBbgm',NULL,'204.76.203.212','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 Edg/90.0.818.46','YToyOntzOjY6Il90b2tlbiI7czo0MDoidXF1TnF2Z1ROMVZub0VKMThKVURMdE1qTklMZ2ZiRWFlczdvNUpucCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1763536612),
('eA1wouTotVFtttzLpmsaaLB6yjBSLFBht99oTdYZ',NULL,'66.249.83.100','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiR0NTZzVIOGp4ZUdHbDJUdUphUjg2Ylo4NHRCSTdoemlGR1BpZ2J5SiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MzoiaHR0cDovL3NpZmNvY2FydG9uLmR1Y2tkbnMub3JnOjgwODAvYWRtaW4vYm9uLWVudHJlZXMiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0NzoiaHR0cDovL3NpZmNvY2FydG9uLmR1Y2tkbnMub3JnOjgwODAvYWRtaW4vbG9naW4iO3M6NToicm91dGUiO3M6MjU6ImZpbGFtZW50LmFkbWluLmF1dGgubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1763535901),
('K5y2ajwuL0bGhrGO4rhC5LY8i0n4JiBsTDPQ55t0',NULL,'204.76.203.212','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 Edg/90.0.818.46','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNWNORVpibDdZamRjQ0F3S2locFBNOHpXajVHVGFFUThUekx0S1RBViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1763531429),
('K6n7XZVzkamVxbNqapqFck5CpZbUKHl2kTn0gIQB',1,'192.168.1.22','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','YTo4OntzOjY6Il90b2tlbiI7czo0MDoidDFsN2g1NVl0cTlhVjg3bGpueGtvUlh3eTBGcEVoSXdsSGNpRHdvRyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ3OiJodHRwOi8vMTkyLjE2OC4xLjE0OjgwODAvYWRtaW4vYm9iaW5lLWRhc2hib2FyZCI7czo1OiJyb3V0ZSI7czozNzoiZmlsYW1lbnQuYWRtaW4ucGFnZXMuYm9iaW5lLWRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjA6IiQyeSQxMiRKYjRVeDBIZktpTzkxak5DS3BPT0V1LmFnMWw4MEdFRHVxL0JwbUZnQnV1bXdXbXE0M0tIQyI7czo2OiJ0YWJsZXMiO2E6Mzp7czo0MDoiOGZhYzZlYjFjZWMyNjgwM2IzZjdmYjQ0MGEyNzExMWJfY29sdW1ucyI7YToxNzp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6ImNvZGUiO3M6NToibGFiZWwiO3M6NDoiQ29kZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NDoibmFtZSI7czo1OiJsYWJlbCI7czo0OiJOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoicHJvZHVjdF90eXBlIjtzOjU6ImxhYmVsIjtzOjEyOiJUeXBlIExvZ2lxdWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6ImZvcm1fdHlwZSI7czo1OiJsYWJlbCI7czoxNDoiRm9ybWUgUGh5c2lxdWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJjYXRlZ29yaWVzLm5hbWUiO3M6NToibGFiZWwiO3M6MTA6IkNhdMOpZ29yaWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToidW5pdC5uYW1lIjtzOjU6ImxhYmVsIjtzOjY6IlVuaXTDqSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJpc19hY3RpdmUiO3M6NToibGFiZWwiO3M6OToiSXMgYWN0aXZlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo4OiJncmFtbWFnZSI7czo1OiJsYWJlbCI7czo4OiJHcmFtbWFnZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6ODthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJsYWl6ZSI7czo1OiJsYWJlbCI7czo1OiJMYWl6ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6OTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJmbHV0ZSI7czo1OiJsYWJlbCI7czo1OiJGbHV0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6MTA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6InR5cGVfcGFwaWVyIjtzOjU6ImxhYmVsIjtzOjExOiJUeXBlIHBhcGllciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6MTE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6InNoZWV0X3dpZHRoX21tIjtzOjU6ImxhYmVsIjtzOjEyOiJMYXJnZXVyIChtbSkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjEyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJzaGVldF9sZW5ndGhfbW0iO3M6NToibGFiZWwiO3M6MTM6Ikxvbmd1ZXVyIChtbSkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjEzO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6Im1pbl9zdG9jayI7czo1OiJsYWJlbCI7czo5OiJNaW4gc3RvY2siO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjE0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJzYWZldHlfc3RvY2siO3M6NToibGFiZWwiO3M6MTI6IlNhZmV0eSBzdG9jayI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6MTU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTA6IkNyZWF0ZWQgYXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjE2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEwOiJVcGRhdGVkIGF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fXM6NDA6IjhlMDljOGE4MjgzYTVlYjRkNmY0OTU5NjkyMjgzZTM3X2NvbHVtbnMiO2E6MTA6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiYm9uX251bWJlciI7czo1OiJsYWJlbCI7czo3OiJOwrAgQm9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToib3JpZ2luX25hbWUiO3M6NToibGFiZWwiO3M6NzoiT3JpZ2luZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6Im9yaWdpbl90eXBlIjtzOjU6ImxhYmVsIjtzOjQ6IlR5cGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJkb2N1bWVudF9udW1iZXIiO3M6NToibGFiZWwiO3M6MTI6Ik7CsCBEb2N1bWVudCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoid2FyZWhvdXNlLm5hbWUiO3M6NToibGFiZWwiO3M6OToiRW50cmVww7R0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6InN0YXR1cyI7czo1OiJsYWJlbCI7czo2OiJTdGF0dXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJleHBlY3RlZF9kYXRlIjtzOjU6ImxhYmVsIjtzOjEzOiJEYXRlIEF0dGVuZHVlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJyZWNlaXZlZF9kYXRlIjtzOjU6ImxhYmVsIjtzOjE1OiJEYXRlIFLDqWNlcHRpb24iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjg7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6InRvdGFsX2Ftb3VudF90dGMiO3M6NToibGFiZWwiO3M6MTE6Ik1vbnRhbnQgVFRDIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6OTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czo5OiJDcsOpw6kgbGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319czo0MDoiMTIwNmMwZmFkYjJiNDc5MGRhZjVlZjA0YjExNTIzZDBfY29sdW1ucyI7YToxMDp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJwcm9kdWN0X25hbWUiO3M6NToibGFiZWwiO3M6NzoiUHJvZHVpdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6ImNhdGVnb3J5X25hbWUiO3M6NToibGFiZWwiO3M6MTA6IkNhdMOpZ29yaWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE0OiJ3YXJlaG91c2VfbmFtZSI7czo1OiJsYWJlbCI7czo5OiJFbnRyZXDDtHQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJwcm9kdWN0X2xhaXplIjtzOjU6ImxhYmVsIjtzOjEwOiJMYWl6ZSAobW0pIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNjoicHJvZHVjdF9ncmFtbWFnZSI7czo1OiJsYWJlbCI7czoxNjoiR3JhbW1hZ2UgKGcvbcKyKSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTg6InByb2R1Y3RfcGFwZXJfdHlwZSI7czo1OiJsYWJlbCI7czoxNDoiVHlwZSBkZSBwYXBpZXIiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJwcm9kdWN0X2ZsdXRlIjtzOjU6ImxhYmVsIjtzOjk6IkNhbm5lbHVyZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjc7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InJvbGxfY291bnQiO3M6NToibGFiZWwiO3M6MTc6Ik5vbWJyZSBkZSBib2JpbmVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6ODthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToidG90YWxfd2VpZ2h0X2tnIjtzOjU6ImxhYmVsIjtzOjE2OiJQb2lkcyB0b3RhbCAoa2cpIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6OTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoidG90YWxfbGVuZ3RoX20iO3M6NToibGFiZWwiO3M6MTg6Ik3DqXRyYWdlIHRvdGFsIChtKSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319fXM6ODoiZmlsYW1lbnQiO2E6MDp7fX0=',1763538623),
('kNjJLL97Z7CObPZoS0bG6O1CiRQ2wHx5K4tv94Cz',NULL,'66.249.83.100','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUnhzNDZESnAxYkpQcmZ4cVVMWHdvMnFBbkV0VkI5SHo0VWJuTnNqaCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MzoiaHR0cDovL3NpZmNvY2FydG9uLmR1Y2tkbnMub3JnOjgwODAvYWRtaW4vYm9uLWVudHJlZXMiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0NzoiaHR0cDovL3NpZmNvY2FydG9uLmR1Y2tkbnMub3JnOjgwODAvYWRtaW4vbG9naW4iO3M6NToicm91dGUiO3M6MjU6ImZpbGFtZW50LmFkbWluLmF1dGgubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1763535901),
('wsFuPwXXpuMeNDYyl0vZrNS5K8Ocu9QJQaXQ09hs',NULL,'66.249.83.99','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUmtRTFI3dmxzUExRRjNCYzN2bzhKY3ZSenF1cjY2MW9COVpUSmFOYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MToiaHR0cDovL3NpZmNvY2FydG9uLmR1Y2tkbnMub3JnOjgwODAvYWRtaW4iO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0MToiaHR0cDovL3NpZmNvY2FydG9uLmR1Y2tkbnMub3JnOjgwODAvYWRtaW4iO3M6NToicm91dGUiO3M6MzA6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1763535894),
('zqsW2Kae9WwHKQKxodd7wowz83wyTLfS4h2QY9aN',1,'105.235.136.59','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36','YTo3OntzOjY6Il90b2tlbiI7czo0MDoidmlNMXhmQ1FHZ29uZU9RMG1ySkxKOXRnNEY3SmdTRjI5WDRibVByQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTg6Imh0dHA6Ly9zaWZjb2NhcnRvbi5kdWNrZG5zLm9yZzo4MDgwL2FkbWluL2JvYmluZS1kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6Mzc6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmJvYmluZS1kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJEpiNFV4MEhmS2lPOTFqTkNLcE9PRXUuYWcxbDgwR0VEdXEvQnBtRmdCdXVtd1dtcTQzS0hDIjtzOjY6InRhYmxlcyI7YToyOntzOjQwOiI4ZTA5YzhhODI4M2E1ZWI0ZDZmNDk1OTY5MjI4M2UzN19jb2x1bW5zIjthOjEwOntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImJvbl9udW1iZXIiO3M6NToibGFiZWwiO3M6NzoiTsKwIEJvbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6Im9yaWdpbl9uYW1lIjtzOjU6ImxhYmVsIjtzOjc6Ik9yaWdpbmUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJvcmlnaW5fdHlwZSI7czo1OiJsYWJlbCI7czo0OiJUeXBlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToiZG9jdW1lbnRfbnVtYmVyIjtzOjU6ImxhYmVsIjtzOjEyOiJOwrAgRG9jdW1lbnQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6IndhcmVob3VzZS5uYW1lIjtzOjU6ImxhYmVsIjtzOjk6IkVudHJlcMO0dCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHV0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoiZXhwZWN0ZWRfZGF0ZSI7czo1OiJsYWJlbCI7czoxMzoiRGF0ZSBBdHRlbmR1ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoicmVjZWl2ZWRfZGF0ZSI7czo1OiJsYWJlbCI7czoxNToiRGF0ZSBSw6ljZXB0aW9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo4O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE2OiJ0b3RhbF9hbW91bnRfdHRjIjtzOjU6ImxhYmVsIjtzOjExOiJNb250YW50IFRUQyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjk7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6OToiQ3LDqcOpIGxlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fXM6NDA6IjEyMDZjMGZhZGIyYjQ3OTBkYWY1ZWYwNGIxMTUyM2QwX2NvbHVtbnMiO2E6MTA6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoicHJvZHVjdF9uYW1lIjtzOjU6ImxhYmVsIjtzOjc6IlByb2R1aXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJjYXRlZ29yeV9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJDYXTDqWdvcmllIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoid2FyZWhvdXNlX25hbWUiO3M6NToibGFiZWwiO3M6OToiRW50cmVww7R0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoicHJvZHVjdF9sYWl6ZSI7czo1OiJsYWJlbCI7czoxMDoiTGFpemUgKG1tKSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6InByb2R1Y3RfZ3JhbW1hZ2UiO3M6NToibGFiZWwiO3M6MTY6IkdyYW1tYWdlIChnL23CsikiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE4OiJwcm9kdWN0X3BhcGVyX3R5cGUiO3M6NToibGFiZWwiO3M6MTQ6IlR5cGUgZGUgcGFwaWVyIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoicHJvZHVjdF9mbHV0ZSI7czo1OiJsYWJlbCI7czo5OiJDYW5uZWx1cmUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJyb2xsX2NvdW50IjtzOjU6ImxhYmVsIjtzOjE3OiJOb21icmUgZGUgYm9iaW5lcyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjg7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6InRvdGFsX3dlaWdodF9rZyI7czo1OiJsYWJlbCI7czoxNjoiUG9pZHMgdG90YWwgKGtnKSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjk7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6InRvdGFsX2xlbmd0aF9tIjtzOjU6ImxhYmVsIjtzOjE4OiJNw6l0cmFnZSB0b3RhbCAobSkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX19',1763535910);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `stock_adjustments`
--

DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_adjustments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_number` varchar(191) NOT NULL COMMENT 'ADJ-YYYYMMDD-####',
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `qty_before` decimal(15,2) NOT NULL COMMENT 'Quantity before adjustment',
  `qty_after` decimal(15,2) NOT NULL COMMENT 'Quantity after adjustment',
  `qty_change` decimal(15,2) NOT NULL COMMENT 'Positive or negative change',
  `weight_before_kg` decimal(15,3) DEFAULT NULL,
  `weight_after_kg` decimal(15,3) DEFAULT NULL,
  `weight_change_kg` decimal(15,3) DEFAULT NULL,
  `adjustment_type` enum('INCREASE','DECREASE','CORRECTION') NOT NULL DEFAULT 'CORRECTION',
  `reason` text NOT NULL COMMENT 'Required explanation for adjustment',
  `adjusted_by` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_adjustments_adjustment_number_unique` (`adjustment_number`),
  KEY `stock_adjustments_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_adjustments_adjusted_by_foreign` (`adjusted_by`),
  KEY `stock_adjustments_approved_by_foreign` (`approved_by`),
  KEY `stock_adjustments_product_id_warehouse_id_index` (`product_id`,`warehouse_id`),
  KEY `stock_adjustments_adjustment_type_index` (`adjustment_type`),
  CONSTRAINT `stock_adjustments_adjusted_by_foreign` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_adjustments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_adjustments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_adjustments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustments`
--

LOCK TABLES `stock_adjustments` WRITE;
/*!40000 ALTER TABLE `stock_adjustments` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `movement_number` varchar(191) NOT NULL COMMENT 'BON-MOV-2025-0001',
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_from_id` bigint(20) unsigned DEFAULT NULL,
  `warehouse_to_id` bigint(20) unsigned DEFAULT NULL,
  `movement_type` enum('RECEPTION','ISSUE','TRANSFER','RETURN','ADJUSTMENT') NOT NULL,
  `qty_moved` decimal(15,2) NOT NULL,
  `roll_weight_before_kg` decimal(12,3) DEFAULT NULL,
  `roll_weight_after_kg` decimal(12,3) DEFAULT NULL,
  `roll_weight_delta_kg` decimal(12,3) DEFAULT NULL,
  `roll_length_before_m` decimal(12,3) DEFAULT NULL,
  `roll_length_after_m` decimal(12,3) DEFAULT NULL,
  `roll_length_delta_m` decimal(12,3) DEFAULT NULL,
  `cump_at_movement` decimal(12,2) NOT NULL COMMENT 'CUMP snapshot',
  `value_moved` decimal(15,2) GENERATED ALWAYS AS (`qty_moved` * `cump_at_movement`) STORED,
  `status` enum('draft','pending','confirmed','cancelled') DEFAULT 'draft',
  `reference_number` varchar(191) DEFAULT NULL COMMENT 'Links to bon tables',
  `user_id` bigint(20) unsigned NOT NULL,
  `performed_at` timestamp NOT NULL,
  `approved_by_id` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_movements_movement_number_unique` (`movement_number`),
  KEY `stock_movements_warehouse_to_id_foreign` (`warehouse_to_id`),
  KEY `stock_movements_user_id_foreign` (`user_id`),
  KEY `stock_movements_approved_by_id_foreign` (`approved_by_id`),
  KEY `stock_movements_product_id_status_index` (`product_id`,`status`),
  KEY `stock_movements_warehouse_from_id_warehouse_to_id_index` (`warehouse_from_id`,`warehouse_to_id`),
  KEY `stock_movements_movement_type_index` (`movement_type`),
  CONSTRAINT `stock_movements_approved_by_id_foreign` FOREIGN KEY (`approved_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_warehouse_from_id_foreign` FOREIGN KEY (`warehouse_from_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_warehouse_to_id_foreign` FOREIGN KEY (`warehouse_to_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `stock_movements` VALUES
(1,'MOV-20251118-0001',3,NULL,1,'RECEPTION',1.00,0.000,1842.000,1842.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0001',1,'2025-11-18 19:52:57',NULL,NULL,'Bobine EAN: 1842 depuis Bon d\'Entrée #BENT-20251118-0001','2025-11-18 19:52:57','2025-11-18 19:52:57'),
(2,'MOV-20251118-0002',1,NULL,1,'RECEPTION',1.00,0.000,1922.000,1922.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1922 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(3,'MOV-20251118-0003',1,NULL,1,'RECEPTION',1.00,0.000,1862.000,1862.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1862 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(4,'MOV-20251118-0004',2,NULL,1,'RECEPTION',1.00,0.000,2681.000,2681.000,0.000,9373.000,9373.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 81212322 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(5,'MOV-20251118-0005',1,NULL,1,'RECEPTION',1.00,0.000,1902.000,1902.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1902 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(6,'MOV-20251118-0006',1,NULL,1,'RECEPTION',1.00,0.000,1770.000,1770.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1770 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(7,'MOV-20251118-0007',1,NULL,1,'RECEPTION',1.00,0.000,1840.000,1840.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1840 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(8,'MOV-20251118-0008',1,NULL,1,'RECEPTION',1.00,0.000,1872.000,1872.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1872 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(9,'MOV-20251118-0009',1,NULL,1,'RECEPTION',1.00,0.000,1776.000,1776.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1776 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(10,'MOV-20251118-0010',1,NULL,1,'RECEPTION',1.00,0.000,1906.000,1906.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1906 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(11,'MOV-20251118-0011',1,NULL,1,'RECEPTION',1.00,0.000,1832.000,1832.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1832 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(12,'MOV-20251118-0012',1,NULL,1,'RECEPTION',1.00,0.000,1860.000,1860.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1860 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(13,'MOV-20251118-0013',1,NULL,1,'RECEPTION',1.00,0.000,1806.000,1806.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1806 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(14,'MOV-20251118-0014',1,NULL,1,'RECEPTION',1.00,0.000,1868.000,1868.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1868 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(15,'MOV-20251118-0015',1,NULL,1,'RECEPTION',1.00,0.000,1858.000,1858.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1858 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(16,'MOV-20251118-0016',1,NULL,1,'RECEPTION',1.00,0.000,1838.000,1838.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1838 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(17,'MOV-20251118-0017',1,NULL,1,'RECEPTION',1.00,0.000,1930.000,1930.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1930 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(18,'MOV-20251118-0018',2,NULL,1,'RECEPTION',1.00,0.000,2523.000,2523.000,0.000,9348.000,9348.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 81212365 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(19,'MOV-20251118-0019',10,NULL,1,'RECEPTION',1.00,0.000,2725.000,2725.000,0.000,9750.000,9750.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 312653294 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(20,'MOV-20251118-0020',11,NULL,1,'RECEPTION',1.00,0.000,3030.000,3030.000,0.000,10600.000,10600.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 312653785 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(21,'MOV-20251118-0021',13,NULL,1,'RECEPTION',1.00,0.000,2308.000,2308.000,0.000,8362.000,8362.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 13560267 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(22,'MOV-20251118-0022',4,NULL,1,'RECEPTION',1.00,0.000,2766.000,2766.000,0.000,8025.000,8025.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1253219762 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(23,'MOV-20251118-0023',4,NULL,1,'RECEPTION',1.00,0.000,2755.000,2755.000,0.000,8078.000,8078.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1253621252 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(24,'MOV-20251118-0024',4,NULL,1,'RECEPTION',1.00,0.000,2754.000,2754.000,0.000,7994.000,7994.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 1253219752 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(25,'MOV-20251118-0025',10,NULL,1,'RECEPTION',1.00,0.000,2730.000,2730.000,0.000,9750.000,9750.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 312653752 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(26,'MOV-20251118-0026',10,NULL,1,'RECEPTION',1.00,0.000,2725.000,2725.000,0.000,9751.000,9751.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 312653300 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(27,'MOV-20251118-0027',11,NULL,1,'RECEPTION',1.00,0.000,3030.000,3030.000,0.000,10600.000,10600.000,0.00,0.00,'confirmed','BENT-20251118-0003',1,'2025-11-19 00:12:17',NULL,NULL,'Bobine EAN: 31265379 depuis Bon d\'Entrée #BENT-20251118-0003','2025-11-19 00:12:17','2025-11-19 00:12:17'),
(28,'MOV-20251118-0028',2,NULL,1,'RECEPTION',1.00,0.000,2628.000,2628.000,0.000,9224.000,9224.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 81212318 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(29,'MOV-20251118-0029',3,NULL,1,'RECEPTION',1.00,0.000,1894.000,1894.000,0.000,6626.000,6626.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 93550637 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(30,'MOV-20251118-0030',1,NULL,1,'RECEPTION',1.00,0.000,1830.000,1830.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1830 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(31,'MOV-20251118-0031',3,NULL,1,'RECEPTION',1.00,0.000,1896.000,1896.000,0.000,6632.000,6632.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 93550640 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(32,'MOV-20251118-0032',1,NULL,1,'RECEPTION',1.00,0.000,1970.000,1970.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1970 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(33,'MOV-20251118-0033',1,NULL,1,'RECEPTION',1.00,0.000,1808.000,1808.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1808 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(34,'MOV-20251118-0034',1,NULL,1,'RECEPTION',1.00,0.000,1814.000,1814.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1814 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(35,'MOV-20251118-0035',1,NULL,1,'RECEPTION',1.00,0.000,1892.000,1892.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1892 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(36,'MOV-20251118-0036',1,NULL,1,'RECEPTION',1.00,0.000,1912.000,1912.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1912 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(37,'MOV-20251118-0037',1,NULL,1,'RECEPTION',1.00,0.000,1836.000,1836.000,0.000,1.000,1.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1836 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(38,'MOV-20251118-0038',13,NULL,1,'RECEPTION',1.00,0.000,2310.000,2310.000,0.000,8370.000,8370.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 13560280 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(39,'MOV-20251118-0039',3,NULL,1,'RECEPTION',1.00,0.000,2354.000,2354.000,0.000,8529.000,8529.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 13488966 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(40,'MOV-20251118-0040',4,NULL,1,'RECEPTION',1.00,0.000,2946.000,2946.000,0.000,8546.000,8546.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1253618152 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48'),
(41,'MOV-20251118-0041',4,NULL,1,'RECEPTION',1.00,0.000,2739.000,2739.000,0.000,8040.000,8040.000,0.00,0.00,'confirmed','BENT-20251118-0002',1,'2025-11-19 00:12:48',NULL,NULL,'Bobine EAN: 1253621212 depuis Bon d\'Entrée #BENT-20251118-0002','2025-11-19 00:12:48','2025-11-19 00:12:48');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `stock_quantities`
--

DROP TABLE IF EXISTS `stock_quantities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_quantities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `total_qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_weight_kg` decimal(15,3) NOT NULL DEFAULT 0.000,
  `total_length_m` decimal(15,3) NOT NULL DEFAULT 0.000,
  `reserved_qty` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'For future use',
  `available_qty` decimal(15,2) GENERATED ALWAYS AS (`total_qty` - `reserved_qty`) STORED,
  `cump_snapshot` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Last known CUMP',
  `last_movement_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_quantities_product_id_warehouse_id_unique` (`product_id`,`warehouse_id`),
  KEY `stock_quantities_warehouse_id_product_id_index` (`warehouse_id`,`product_id`),
  KEY `stock_quantities_last_movement_id_foreign` (`last_movement_id`),
  CONSTRAINT `stock_quantities_last_movement_id_foreign` FOREIGN KEY (`last_movement_id`) REFERENCES `stock_movements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_quantities_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_quantities_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_quantities`
--

LOCK TABLES `stock_quantities` WRITE;
/*!40000 ALTER TABLE `stock_quantities` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `stock_quantities` VALUES
(1,3,1,4.00,7986.000,21788.000,0.00,4.00,0.00,NULL,'2025-11-18 19:52:57','2025-11-19 00:12:48'),
(2,1,1,22.00,40904.000,22.000,0.00,22.00,0.00,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:48'),
(3,2,1,3.00,7832.000,27945.000,0.00,3.00,0.00,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:48'),
(4,10,1,3.00,8180.000,29251.000,0.00,3.00,0.00,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(5,11,1,2.00,6060.000,21200.000,0.00,2.00,0.00,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:17'),
(6,13,1,2.00,4618.000,16732.000,0.00,2.00,0.00,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:48'),
(7,4,1,5.00,13960.000,40683.000,0.00,5.00,0.00,NULL,'2025-11-19 00:12:17','2025-11-19 00:12:48');
/*!40000 ALTER TABLE `stock_quantities` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(191) NOT NULL,
  `contact_person` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_terms` varchar(191) DEFAULT NULL COMMENT 'e.g., Net 30',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `suppliers` VALUES
(1,'SUPP-GPM-001','HEINZEL 8051004554',NULL,NULL,NULL,NULL,NULL,1,'2025-11-18 19:33:36','2025-11-18 19:33:36');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_name_unique` (`name`),
  UNIQUE KEY `units_symbol_unique` (`symbol`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `units` VALUES
(1,'Pièce','pcs','Unité individuelle','2025-11-18 15:04:53','2025-11-18 15:04:53'),
(2,'Bobine','roll','Bobine de papier','2025-11-18 15:04:53','2025-11-18 15:04:53'),
(3,'Kilogramme','kg','Poids en kilogrammes','2025-11-18 15:04:53','2025-11-18 15:04:53'),
(4,'Mètre','m','Longueur en mètres','2025-11-18 15:04:53','2025-11-18 15:04:53'),
(5,'Tonne','t','Poids en tonnes','2025-11-18 15:04:53','2025-11-18 15:04:53');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `users` VALUES
(1,'Administrateur Système','admin@sifco.local','2025-11-18 15:04:37','$2y$12$Jb4Ux0HfKiO91jNCKpOOEu.ag1l80GEDuq/BpmFgBuumwWmq43KHC','U30VnaL5YzFfjKNSxgj8SrLDe56R15Px7RbadoVHoZqsUM0O9fnmaUUMIG52','2025-11-18 15:04:37','2025-11-18 15:04:37'),
(2,'Magasinier Principal','magasinier@sifco.local','2025-11-18 15:04:37','$2y$12$pa77j/77t317X6v78UewHuv2E2meqlrMtBnKETgn8LrXe4o31OoYq',NULL,'2025-11-18 15:04:37','2025-11-18 15:04:37'),
(3,'Assistante Magasinage','assistant.magasin@sifco.local','2025-11-18 15:04:37','$2y$12$NO14MIfbFPDE5o8McsSCieJvEssZAj/VUpc1Cl.nx9sDX8dq0BVGC',NULL,'2025-11-18 15:04:37','2025-11-18 15:04:37'),
(4,'Comptable Matières','comptable@sifco.local','2025-11-18 15:04:37','$2y$12$yoNK/rn0qktZCPlrt/YwROxbKjH7VykAxv25.9MMkZJ2dS5BSgTjy',NULL,'2025-11-18 15:04:37','2025-11-18 15:04:37'),
(5,'Responsable Production','production@sifco.local','2025-11-18 15:04:37','$2y$12$tBAg0BcnrddLj26pgQrfyeMPDxBW2NXGpRb//A4YY.PswAAy6ndGq',NULL,'2025-11-18 15:04:38','2025-11-18 15:04:38');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'System warehouses like PRODUCTION_CONSUMED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouses`
--

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `warehouses` VALUES
(1,'Magasin Principal - 1',1,'2025-11-18 15:04:54','2025-11-18 19:34:39'),
(2,'Magasin Secondaire - Production',0,'2025-11-18 15:04:54','2025-11-18 15:04:54'),
(4,'Magasin Principal - 2',0,'2025-11-18 19:34:53','2025-11-18 19:34:53'),
(5,'Magasin Principal - 3',0,'2025-11-18 19:35:02','2025-11-18 19:35:02'),
(6,'Magasin MACAR BOX - 1',0,'2025-11-18 19:35:31','2025-11-18 19:35:31');
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Dumping events for database 'sifco_inv'
--

--
-- Dumping routines for database 'sifco_inv'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-11-19  2:54:07
