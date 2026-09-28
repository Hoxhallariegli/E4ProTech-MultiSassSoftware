-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: grand
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `audit_trails`
--

DROP TABLE IF EXISTS `audit_trails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_trails` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` text COLLATE utf8mb4_unicode_ci,
  `reference_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_trails_user_id_foreign` (`user_id`),
  KEY `audit_trails_created_at_index` (`created_at`),
  CONSTRAINT `audit_trails_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_trails`
--

LOCK TABLES `audit_trails` WRITE;
/*!40000 ALTER TABLE `audit_trails` DISABLE KEYS */;
INSERT INTO `audit_trails` VALUES ('01a0fd5d-f567-4c11-8f1b-d3002c33e547','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'8','Bookings','update','[]','[]','2026-09-23 07:32:35','2026-09-23 07:32:35',NULL),('01db7b39-e70d-4fe4-9d8b-f0b55a3dc90f','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 08:22:29','2026-09-18 08:22:29',NULL),('04f09c3c-dc92-4836-aab8-e4161556a105','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'14','Payments','update','[]','[]','2026-09-25 11:59:29','2026-09-25 11:59:29',NULL),('05e60b83-64cc-4549-9ccc-79a64eaaba75','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Customer',NULL,'3','Customers','create',NULL,'{\"id\": 3, \"name\": \"EGLI\", \"email\": \"\", \"phone\": \"064849\", \"photo\": \"\", \"blocked_at\": null, \"created_at\": \"2026-09-22T14:22:27.000000Z\", \"updated_at\": \"2026-09-22T14:22:27.000000Z\", \"no_show_count\": 0, \"barber_shop_id\": 16, \"total_bookings\": 0}','2026-09-22 12:22:27','2026-09-22 12:22:27',NULL),('0721b5ec-2890-4589-9f77-33af6eb4ea31','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','[]','[]','2026-09-24 09:27:34','2026-09-24 09:27:34',NULL),('0b686872-0898-431a-9461-5ee7bb31089c','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-24 09:26:58','2026-09-24 09:26:58',NULL),('0c2e33fe-64bc-4af3-974a-e7824fd58737','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'7','Payments','create',NULL,'{\"id\": 7, \"amount\": \"1200.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 10, \"created_at\": \"2026-09-24T12:02:56.000000Z\", \"updated_at\": \"2026-09-24T12:02:56.000000Z\", \"barber_shop_id\": 16}','2026-09-24 10:02:56','2026-09-24 10:02:56',NULL),('0d4497e3-8586-4783-90bf-9ed8ff34eafc','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'9','Bookings','update','[]','[]','2026-09-24 10:32:04','2026-09-24 10:32:04',NULL),('0d85c31b-abc8-4292-b483-0024a60adeec','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-18 14:00:07','2026-09-18 14:00:07',NULL),('0df8719f-a686-4e47-8d09-6e555f914a55','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'11','Payments','update','[]','[]','2026-09-25 10:31:47','2026-09-25 10:31:47',NULL),('0ee7e058-365b-4bdc-bd4c-e5ecb58a3c2e','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'2','Payments','create',NULL,'{\"id\": 2, \"amount\": \"1200.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 10, \"created_at\": \"2026-09-24T11:27:23.000000Z\", \"updated_at\": \"2026-09-24T11:27:23.000000Z\", \"barber_shop_id\": 16}','2026-09-24 09:27:23','2026-09-24 09:27:23',NULL),('0f5c2a3c-7243-4f4e-983c-15c81cd9e24b','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 07:44:40','2026-09-18 07:44:40',NULL),('0fbfe1b3-51c4-4c9f-9fd6-5c5d3ebf8a0e','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'26','Bookings','create',NULL,'{\"id\": 26, \"notes\": \"Shërbimet: Dhenderr Edition\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-26T10:25:49.000000Z\", \"service_id\": 6, \"updated_at\": \"2026-09-26T10:25:49.000000Z\", \"customer_id\": 6, \"total_price\": \"1000.00\", \"appointment_at\": \"2026-09-26T14:40:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 08:25:49','2026-09-26 08:25:49',NULL),('10caefeb-53ae-4924-b8d5-6aafb244ef1a','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'14','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-24 11:37:11','2026-09-24 11:37:11',NULL),('12e378b8-7641-47c2-96c4-dda5e5dfb8b0','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 07:49:56','2026-09-17 07:49:56',NULL),('130939c3-99a7-4003-b5da-54921c071dd6','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 08:49:43','2026-09-18 08:49:43',NULL),('13d535e2-4bf7-4ac0-870e-0471c470db85','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'23','Bookings','create',NULL,'{\"id\": 23, \"notes\": \"Shërbimet: rroje\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-26T06:59:16.000000Z\", \"service_id\": 5, \"updated_at\": \"2026-09-26T06:59:16.000000Z\", \"customer_id\": 6, \"total_price\": \"600.00\", \"appointment_at\": \"2026-09-26T09:15:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 04:59:16','2026-09-26 04:59:16',NULL),('149c6a76-ef13-4b06-a560-9d7057173758','04732a98-8b73-4005-9c1f-55db6d3ab283','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-18 07:26:23','2026-09-18 07:26:23',NULL),('14b5875d-d0b2-4463-a69a-55474ee81c92','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'8','Bookings','create',NULL,'{\"id\": 8, \"notes\": \"\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-23T09:31:13.000000Z\", \"service_id\": 3, \"updated_at\": \"2026-09-23T09:31:13.000000Z\", \"customer_id\": 5, \"total_price\": \"800.00\", \"appointment_at\": \"2026-09-23T14:45:00.000000Z\", \"barber_shop_id\": 16}','2026-09-23 07:31:14','2026-09-23 07:31:14',NULL),('1543eeca-1137-48c5-8086-1d41c3514507','76edf897-20fa-4c3a-b757-6d8bc980333c','Update BarberShop',NULL,'18','BarberShops','update','{\"expires_at\": \"2027-09-28T10:59:12.000000Z\", \"primary_color\": \"#DC2626\", \"trial_ends_at\": \"2026-10-28T10:59:12.000000Z\"}','{\"expires_at\": \"2027-09-28 10:59:00\", \"primary_color\": \"#D97706\", \"trial_ends_at\": \"2026-10-28 10:59:00\"}','2026-09-28 09:45:12','2026-09-28 09:45:12',NULL),('154eb456-f3a6-4267-9f9b-246cc3e94d3b','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 09:36:33','2026-09-17 09:36:33',NULL),('169471bf-8101-43fd-855b-893ed2dd53ce','76edf897-20fa-4c3a-b757-6d8bc980333c','Create WorkingHour',NULL,'1','WorkingHours','create',NULL,'{\"id\": 1, \"barber_id\": 3, \"is_closed\": false, \"open_time\": \"2026-09-22T08:00:00.000000Z\", \"close_time\": \"2026-09-22T20:00:00.000000Z\", \"created_at\": \"2026-09-22T12:47:52.000000Z\", \"updated_at\": \"2026-09-22T12:47:52.000000Z\", \"day_of_week\": \"Monday\"}','2026-09-22 10:47:52','2026-09-22 10:47:52',NULL),('19015192-703a-4a84-a5f2-df08d31bced2','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'21','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-26 04:06:40','2026-09-26 04:06:40',NULL),('191e39f4-6a42-45c6-8b5d-9c7b9ecfca91','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Service',NULL,'5','Services','update','{\"price\": \"400.00\"}','{\"price\": 600}','2026-09-24 10:13:53','2026-09-24 10:13:53',NULL),('1a84fed8-7547-4b8c-a1da-6b5ce96837d1','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'22','Bookings','update','[]','[]','2026-09-26 17:08:39','2026-09-26 17:08:39',NULL),('1b8cf50a-0777-41f4-b237-544730771202','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Menaxher','http://10.10.12.14:5000/admin/settings/roles/e54f8f78-a794-4ee1-97e3-55fd44630ab7/edit','0','Roles','Update',NULL,NULL,'2026-09-17 10:28:35','2026-09-17 10:28:35',NULL),('1c6dfa56-2e01-4f4d-bc94-643ea165c269','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'14','Bookings','create',NULL,'{\"id\": 14, \"notes\": \"Shërbimet: rroje + Rrojre qethje\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-24T13:35:36.000000Z\", \"service_id\": 5, \"updated_at\": \"2026-09-24T13:35:36.000000Z\", \"customer_id\": 3, \"total_price\": \"1400.00\", \"appointment_at\": \"2026-09-24T17:15:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 11:35:36','2026-09-24 11:35:36',NULL),('1dfce6fe-9cc2-4a99-befd-2991731c37e0','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 09:17:30','2026-09-17 09:17:30',NULL),('1e09b1ae-c087-4851-98a1-a7d1e614fb43','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'26','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-26 08:46:26','2026-09-26 08:46:26',NULL),('1ed9bb67-06ab-490e-876c-97944ee22bef','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'15','Payments','create',NULL,'{\"id\": 15, \"amount\": \"1100.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 19, \"created_at\": \"2026-09-25T14:05:53.000000Z\", \"updated_at\": \"2026-09-25T14:05:53.000000Z\", \"barber_shop_id\": 16}','2026-09-25 12:05:53','2026-09-25 12:05:53',NULL),('215e60c2-0539-41d6-bd72-2506a557f430','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'8','Bookings','update','{\"status\": \"completed\"}','{\"status\": \"no-show\"}','2026-09-23 08:44:52','2026-09-23 08:44:52',NULL),('2256791b-0ac9-4634-858e-7290d6cb8886','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'20','Bookings','update','[]','[]','2026-09-25 12:05:30','2026-09-25 12:05:30',NULL),('25a2862e-9134-4280-88c9-127f6ab227b8','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'9','Bookings','create',NULL,'{\"id\": 9, \"notes\": \"\", \"source\": \"walk-in\", \"status\": \"completed\", \"barber_id\": 3, \"created_at\": \"2026-09-23T14:54:18.000000Z\", \"service_id\": 4, \"updated_at\": \"2026-09-23T14:54:18.000000Z\", \"customer_id\": 5, \"total_price\": \"500.00\", \"appointment_at\": \"2026-09-23T08:00:00.000000Z\", \"barber_shop_id\": 16}','2026-09-23 12:54:18','2026-09-23 12:54:18',NULL),('26cb16f5-9d8f-49be-90e9-0c71d84f4b8c','04732a98-8b73-4005-9c1f-55db6d3ab283','updated Ardit Berberi\'s roles','http://10.10.12.14:5000/admin/users/04732a98-8b73-4005-9c1f-55db6d3ab283/edit','0','Users','Update',NULL,NULL,'2026-09-18 07:24:14','2026-09-18 07:24:14',NULL),('270ef5e4-88ff-433b-bfec-cf46222efa22','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'12','Bookings','create',NULL,'{\"id\": 12, \"notes\": \"Shërbimet: rroje + Larje dhe Stilim\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 4, \"created_at\": \"2026-09-24T12:37:09.000000Z\", \"service_id\": 5, \"updated_at\": \"2026-09-24T12:37:09.000000Z\", \"customer_id\": 5, \"total_price\": \"1100.00\", \"appointment_at\": \"2026-09-24T17:30:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 10:37:09','2026-09-24 10:37:09',NULL),('29daf8f3-4892-4f67-9f7b-da1ac47a2de4','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'16','Payments','create',NULL,'{\"id\": 16, \"amount\": \"1000.00\", \"method\": \"cash\", \"status\": \"pending\", \"booking_id\": 21, \"created_at\": \"2026-09-26T06:06:48.000000Z\", \"updated_at\": \"2026-09-26T06:06:48.000000Z\", \"barber_shop_id\": 16}','2026-09-26 04:06:48','2026-09-26 04:06:48',NULL),('2a2829ef-53eb-4fce-8bbf-112abd4660b4','76edf897-20fa-4c3a-b757-6d8bc980333c','Update BarberShop',NULL,'18','BarberShops','update','{\"banner\": \"placeholder.png\"}','{\"banner\": \"uploads/barber-shops/500PXi4c0H3rltCXDLp3Q4rqL9j5AT.webp\"}','2026-09-28 10:08:40','2026-09-28 10:08:40',NULL),('2aaf6151-445f-4450-b829-20cec7b7ad16','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-18 08:45:44','2026-09-18 08:45:44',NULL),('2c883ab1-cec1-4ffb-9f89-d3ee2cf26080','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'19','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-25 11:09:08','2026-09-25 11:09:08',NULL),('2ca60dff-dffe-4466-af3b-dd68f23a7d91','76edf897-20fa-4c3a-b757-6d8bc980333c','Create Barber',NULL,'8','Barbers','create',NULL,'{\"id\": 8, \"bio\": \"Dolorum ab corporis \", \"name\": \"Hedwig Mcpherson\", \"phone\": \"+1 (103) 152-1292\", \"photo\": \"\", \"active\": false, \"user_id\": \"04732a98-8b73-4005-9c1f-55db6d3ab283\", \"created_at\": \"2026-09-18T16:38:13.000000Z\", \"updated_at\": \"2026-09-18T16:38:13.000000Z\", \"barber_shop_id\": 7}','2026-09-18 14:38:13','2026-09-18 14:38:13',NULL),('2cc9764f-764e-472e-b961-84904000fc16','76edf897-20fa-4c3a-b757-6d8bc980333c','Create WorkingHour',NULL,'2','WorkingHours','create',NULL,'{\"id\": 2, \"barber_id\": 3, \"is_closed\": false, \"open_time\": \"08:00\", \"close_time\": \"20:00\", \"created_at\": \"2026-09-22T12:54:40.000000Z\", \"updated_at\": \"2026-09-22T12:54:40.000000Z\", \"day_of_week\": \"Tuesday\"}','2026-09-22 10:54:40','2026-09-22 10:54:40',NULL),('2d8022e9-96c2-44c6-b481-cb75a6187776','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 08:58:11','2026-09-17 08:58:11',NULL),('3028212f-54d5-45b9-9b2c-48c7b93bee67','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'8','Payments','create',NULL,'{\"id\": 8, \"amount\": \"500.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 9, \"created_at\": \"2026-09-24T12:32:11.000000Z\", \"updated_at\": \"2026-09-24T12:32:11.000000Z\", \"barber_shop_id\": 16}','2026-09-24 10:32:11','2026-09-24 10:32:11',NULL),('317b2956-165b-4772-81b0-24caf98621ae','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'15','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-24 13:08:39','2026-09-24 13:08:39',NULL),('3216dfea-b263-4dd8-be7b-debe5babf937','76edf897-20fa-4c3a-b757-6d8bc980333c','Create Barber',NULL,'7','Barbers','create',NULL,'{\"id\": 7, \"bio\": \"Quasi ut magna quide\", \"name\": \"Yael Powers\", \"phone\": \"+1 (338) 794-3525\", \"photo\": \"\", \"active\": false, \"user_id\": \"04732a98-8b73-4005-9c1f-55db6d3ab283\", \"created_at\": \"2026-09-18T16:37:33.000000Z\", \"updated_at\": \"2026-09-18T16:37:33.000000Z\", \"barber_shop_id\": 8}','2026-09-18 14:37:33','2026-09-18 14:37:33',NULL),('335e9948-ac44-4136-9b84-76efbe0854eb','76edf897-20fa-4c3a-b757-6d8bc980333c','Create Service',NULL,'3','Services','create',NULL,'{\"id\": 3, \"name\": \"Rrojre qethje\", \"price\": \"800.00\", \"active\": true, \"category\": \"\", \"created_at\": \"2026-09-22T13:45:58.000000Z\", \"updated_at\": \"2026-09-22T13:45:58.000000Z\", \"description\": \"21\", \"barber_shop_id\": 16, \"duration_minutes\": 45}','2026-09-22 11:45:58','2026-09-22 11:45:58',NULL),('337eb54f-be46-4386-893e-7844bb7bbdaf','dbb77630-40f2-45b4-9427-5cd634868ec2','Create Booking',NULL,'34','Bookings','create',NULL,'{\"id\": 34, \"notes\": \"Shërbimet: Qethje & Modelim Femrash\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 6, \"created_at\": \"2026-09-28T12:41:09.000000Z\", \"service_id\": 10, \"updated_at\": \"2026-09-28T12:41:09.000000Z\", \"customer_id\": 8, \"total_price\": \"2000.00\", \"appointment_at\": \"2026-09-29T12:45:00.000000Z\", \"barber_shop_id\": 18}','2026-09-28 10:41:09','2026-09-28 10:41:09',NULL),('347d7515-4efc-4140-84ad-4468f0ee3710','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 11:58:10','2026-09-17 11:58:10',NULL),('353e19cb-892d-4f51-908a-e2f700d994f4','76edf897-20fa-4c3a-b757-6d8bc980333c','Create WorkingHour',NULL,'3','WorkingHours','create',NULL,'{\"id\": 3, \"barber_id\": 3, \"is_closed\": false, \"open_time\": \"08:00\", \"close_time\": \"20:00\", \"created_at\": \"2026-09-22T12:55:05.000000Z\", \"updated_at\": \"2026-09-22T12:55:05.000000Z\", \"day_of_week\": \"Wednesday\"}','2026-09-22 10:55:05','2026-09-22 10:55:05',NULL),('35e1cdea-1a09-49a6-bef7-3de7e937dfd2','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','[]','[]','2026-09-24 09:40:31','2026-09-24 09:40:31',NULL),('36321822-3033-4e8a-ae8d-e99e8222bfc8','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'26','Bookings','update','[]','[]','2026-09-26 08:47:15','2026-09-26 08:47:15',NULL),('38d0a8f1-a2a9-4be7-83a5-5d0449beb406','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Menaxher','http://10.10.12.14:5000/admin/settings/roles/e54f8f78-a794-4ee1-97e3-55fd44630ab7/edit','0','Roles','Update',NULL,NULL,'2026-09-17 09:36:11','2026-09-17 09:36:11',NULL),('39cb9bfb-5dab-49cb-a585-967fb2fb2dc2','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 10:31:22','2026-09-17 10:31:22',NULL),('39fb0041-08a6-455d-abd9-b2faca143fb4','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'16','Payments','update','[]','[]','2026-09-26 04:44:21','2026-09-26 04:44:21',NULL),('3a303854-0df0-4e56-a9a0-10e4478f5347','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-18 14:42:33','2026-09-18 14:42:33',NULL),('3a68101f-973e-42cd-ba58-9ba276a5f610','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'11','Payments','update','[]','[]','2026-09-25 10:32:25','2026-09-25 10:32:25',NULL),('3a707960-9d22-4c61-afef-ed1891f9ba4c','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 11:59:29','2026-09-17 11:59:29',NULL),('3aac3892-7238-4058-8a95-b20ba575f37a','76edf897-20fa-4c3a-b757-6d8bc980333c','Update Subscription',NULL,'4','Subscriptions','update','{\"ends_at\": \"2027-09-28T09:46:06.000000Z\", \"starts_at\": \"2026-09-28T09:46:06.000000Z\"}','{\"ends_at\": \"2026-10-28 09:46:00\", \"starts_at\": \"2026-09-28 09:46:00\"}','2026-09-28 09:27:22','2026-09-28 09:27:22',NULL),('3acb4017-67ce-47e8-a326-8b3bb8de681b','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar','http://10.10.12.14:5000/admin/settings/roles/2567fa55-f7e6-4bb8-8e6c-33e35e0279bd/edit','0','Roles','Update',NULL,NULL,'2026-09-17 11:27:59','2026-09-17 11:27:59',NULL),('3afdef77-b8ba-49f1-9945-b6878bbb925a','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar','http://10.10.12.14:5000/admin/settings/roles/2567fa55-f7e6-4bb8-8e6c-33e35e0279bd/edit','0','Roles','Update',NULL,NULL,'2026-09-17 10:50:41','2026-09-17 10:50:41',NULL),('3d2c223f-130b-4d85-bf42-a5394197bf9c','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'8','Bookings','update','{\"status\": \"no-show\"}','{\"status\": \"completed\"}','2026-09-23 08:45:12','2026-09-23 08:45:12',NULL),('3ead7413-55fb-45a8-9408-e9b932c460fe','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'21','Bookings','create',NULL,'{\"id\": 21, \"notes\": \"Shërbimet: Dhenderr Edition\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-26T06:06:26.000000Z\", \"service_id\": 6, \"updated_at\": \"2026-09-26T06:06:26.000000Z\", \"customer_id\": 3, \"total_price\": \"1000.00\", \"appointment_at\": \"2026-09-26T09:45:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 04:06:26','2026-09-26 04:06:26',NULL),('3f1e5921-655f-4bbd-a4a0-cc2151264eeb','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'18','Bookings','update','[]','[]','2026-09-25 10:17:53','2026-09-25 10:17:53',NULL),('3f48a8e8-cc42-4b8f-ae85-6ce36e30b2f8','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar','http://10.10.12.14:5000/admin/settings/roles/2567fa55-f7e6-4bb8-8e6c-33e35e0279bd/edit','0','Roles','Update',NULL,NULL,'2026-09-17 07:48:44','2026-09-17 07:48:44',NULL),('3f6d7edb-3f1b-4678-9d05-1e4d267459b6','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','{\"status\": \"completed\"}','{\"status\": \"no-show\"}','2026-09-24 10:03:27','2026-09-24 10:03:27',NULL),('3f8b8db7-ed9c-41a2-88ce-4098890afbf9','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 11:47:36','2026-09-17 11:47:36',NULL),('43c6c5bf-c308-4594-85ce-6f6740f2f79a','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','[]','[]','2026-09-24 09:27:11','2026-09-24 09:27:11',NULL),('44745ae0-e6b4-4e72-bfde-73ec2a1c8493','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','created role Berber','http://10.10.12.14:5000/admin/settings/roles/0663f25d-7fc2-4c9f-bd56-3b9622eae87a/edit','0','Roles','created',NULL,NULL,'2026-09-17 14:08:55','2026-09-17 14:08:55',NULL),('44871b1d-7244-4738-8197-a2525ce998a4','76edf897-20fa-4c3a-b757-6d8bc980333c','Create WorkingHour',NULL,'6','WorkingHours','create',NULL,'{\"id\": 6, \"barber_id\": 3, \"is_closed\": false, \"open_time\": \"08:00\", \"close_time\": \"20:00\", \"created_at\": \"2026-09-22T12:56:29.000000Z\", \"updated_at\": \"2026-09-22T12:56:29.000000Z\", \"day_of_week\": \"Saturday\"}','2026-09-22 10:56:29','2026-09-22 10:56:29',NULL),('463cbcb2-208a-4cd7-af95-b4fa84787785','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'16','Bookings','create',NULL,'{\"id\": 16, \"notes\": \"Shërbimet: rroje + Larje dhe Stilim\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-24T15:09:09.000000Z\", \"service_id\": 5, \"updated_at\": \"2026-09-24T15:09:09.000000Z\", \"customer_id\": 3, \"total_price\": \"1100.00\", \"appointment_at\": \"2026-09-25T11:00:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 13:09:09','2026-09-24 13:09:09',NULL),('48f03789-c0a7-4e0b-920e-95a0be372daf','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'21','Bookings','update','[]','[]','2026-09-26 04:44:42','2026-09-26 04:44:42',NULL),('4a9428cf-4088-4dd7-b9f3-84258962fc37','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'14','Bookings','update','[]','[]','2026-09-24 14:08:45','2026-09-24 14:08:45',NULL),('4c8297a9-d2bf-4b26-836f-3de862289804','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Barber',NULL,'2','Barbers','update','{\"photo\": \"barber2.png\"}','{\"photo\": \"\"}','2026-09-21 14:45:41','2026-09-21 14:45:41',NULL),('4d706762-d728-4b22-9b9d-29f9c4763da0','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 13:32:34','2026-09-17 13:32:34',NULL),('4eefbe06-e250-4b93-b63d-21c868f583db','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Berber','http://10.10.12.14:5000/admin/settings/roles/0663f25d-7fc2-4c9f-bd56-3b9622eae87a/edit','0','Roles','Update',NULL,NULL,'2026-09-18 14:43:12','2026-09-18 14:43:12',NULL),('4f273bdc-11df-4a1c-871a-6e1d0f63854d','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'23','Bookings','update','{\"notes\": \"Shërbimet: rroje\", \"source\": \"online\"}','{\"notes\": \"Shërbimet: rroje + Larje dhe Stilim\", \"source\": \"walk-in\"}','2026-09-26 04:59:42','2026-09-26 04:59:42',NULL),('4f46b88c-d4b7-410c-8597-5ce3715c4ee1','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','invited Germane Velasquez','','0','Auth','Join',NULL,NULL,'2026-09-17 14:09:34','2026-09-17 14:09:34',NULL),('4faeb3a0-0dcb-47b0-87ad-33e316ccba90','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-18 15:14:03','2026-09-18 15:14:03',NULL),('4fef5696-1c0d-44ec-972c-382851ab0b6e','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 14:20:14','2026-09-17 14:20:14',NULL),('503d01bb-b0e4-4c4b-a57b-87c081b68435','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'11','Bookings','create',NULL,'{\"id\": 11, \"notes\": \"Shërbimet: Rrojre qethje + Larje dhe Stilim\", \"source\": \"walk-in\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-24T11:33:52.000000Z\", \"service_id\": 3, \"updated_at\": \"2026-09-24T11:33:52.000000Z\", \"customer_id\": 6, \"total_price\": \"1300.00\", \"appointment_at\": \"2026-09-24T14:30:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 09:33:52','2026-09-24 09:33:52',NULL),('51bb953c-29a2-40a9-9fa4-b19299e17f26','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-18 10:35:36','2026-09-18 10:35:36',NULL),('52813799-abaa-4231-aad6-d69f8567769d','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Barber',NULL,'3','Barbers','create',NULL,'{\"id\": 3, \"bio\": \"124524\", \"name\": \"POG\", \"phone\": \"+566465\", \"photo\": \"\", \"active\": true, \"user_id\": \"04732a98-8b73-4005-9c1f-55db6d3ab283\", \"created_at\": \"2026-09-18T16:35:06.000000Z\", \"updated_at\": \"2026-09-18T16:35:06.000000Z\", \"barber_shop_id\": 8}','2026-09-18 14:35:06','2026-09-18 14:35:06',NULL),('5325c096-938a-4f55-8c42-5b85184e5b3f','76edf897-20fa-4c3a-b757-6d8bc980333c','updated Demo Admin E4ProTech\'s roles','http://10.10.12.14:5000/admin/users/76edf897-20fa-4c3a-b757-6d8bc980333c/edit','0','Users','Update',NULL,NULL,'2026-09-17 11:59:45','2026-09-17 11:59:45',NULL),('5418d2cd-5739-4e9a-945e-4a8b4c633ca5','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'19','Payments','create',NULL,'{\"id\": 19, \"amount\": \"1000.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 25, \"created_at\": \"2026-09-26T19:09:00.000000Z\", \"updated_at\": \"2026-09-26T19:09:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 17:09:00','2026-09-26 17:09:00',NULL),('54e4c2d6-293d-43c4-a6fa-f611294a5acf','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'5','Payments','create',NULL,'{\"id\": 5, \"amount\": \"1200.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 10, \"created_at\": \"2026-09-24T11:40:44.000000Z\", \"updated_at\": \"2026-09-24T11:40:44.000000Z\", \"barber_shop_id\": 16}','2026-09-24 09:40:44','2026-09-24 09:40:44',NULL),('5690e4dd-d029-4d99-8080-816772c77f82','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'18','Bookings','update','[]','[]','2026-09-25 10:18:36','2026-09-25 10:18:36',NULL),('56b34bcb-59f6-402c-8020-935e7d99d732','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'17','Bookings','create',NULL,'{\"id\": 17, \"notes\": \"Shërbimet: rroje\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-24T15:10:32.000000Z\", \"service_id\": 5, \"updated_at\": \"2026-09-24T15:10:32.000000Z\", \"customer_id\": 4, \"total_price\": \"600.00\", \"appointment_at\": \"2026-09-25T10:30:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 13:10:32','2026-09-24 13:10:32',NULL),('58f76af1-6be6-4639-81ee-fcf8180c6215','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-21 09:02:14','2026-09-21 09:02:14',NULL),('5a8e3ec8-a08b-4863-889d-c9684876f042','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'17','Bookings','update','[]','[]','2026-09-25 10:31:36','2026-09-25 10:31:36',NULL),('5b8fb920-4f65-46d0-a95c-337270b59c65','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 10:54:47','2026-09-17 10:54:47',NULL),('5c773d97-80fc-4c5a-9354-e5a56a92a150','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-16 15:05:57','2026-09-16 15:05:57',NULL),('5d1c469b-027a-4f8f-a0cf-86fdc99e47d8','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'8','Bookings','update','{\"appointment_at\": \"2026-09-23T14:45:00.000000Z\"}','{\"appointment_at\": \"2026-09-23 16:45:00\"}','2026-09-23 12:51:52','2026-09-23 12:51:52',NULL),('5dc674cd-c237-4912-83e4-28f860e0140f','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 07:50:20','2026-09-17 07:50:20',NULL),('5df4ac1c-79db-4b38-aca3-3f3f48f651bd','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-21 08:59:08','2026-09-21 08:59:08',NULL),('5e48e9ec-12be-4bbe-a302-38920537f90e','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'21','Bookings','update','[]','[]','2026-09-26 04:06:57','2026-09-26 04:06:57',NULL),('5ee52bc6-3244-49a7-bf29-4ffcbba66908','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 07:35:15','2026-09-18 07:35:15',NULL),('5f56d8e2-31b8-4f86-a2d5-a3afcdf72a36','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'5','Bookings','create',NULL,'{\"id\": 5, \"notes\": \"\", \"source\": \"walk-in\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-22T14:24:17.000000Z\", \"service_id\": 3, \"updated_at\": \"2026-09-22T14:24:17.000000Z\", \"customer_id\": 4, \"total_price\": \"800.00\", \"appointment_at\": \"2026-09-22T19:00:00.000000Z\", \"barber_shop_id\": 16}','2026-09-22 12:24:18','2026-09-22 12:24:18',NULL),('5fdce612-9939-4c49-951c-bec49c46b6f9','76edf897-20fa-4c3a-b757-6d8bc980333c','created role Owner1','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','created',NULL,NULL,'2026-09-17 14:03:12','2026-09-17 14:03:12',NULL),('5ffc6826-0da3-4807-a17a-037643d931a0','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 10:56:06','2026-09-17 10:56:06',NULL),('6065134e-43f1-4407-a02e-4bab03a685d9','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-16 12:58:26','2026-09-16 12:58:26',NULL),('619bc83c-e919-404b-8e7b-1c92212edccf','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'18','Bookings','create',NULL,'{\"id\": 18, \"notes\": \"Shërbimet: Rrojre qethje + Dhenderr Edition\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-24T16:05:40.000000Z\", \"service_id\": 3, \"updated_at\": \"2026-09-24T16:05:40.000000Z\", \"customer_id\": 6, \"total_price\": \"1800.00\", \"appointment_at\": \"2026-09-25T12:30:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 14:05:40','2026-09-24 14:05:40',NULL),('61f34124-a041-4664-9af1-b18ec9202e90','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-18 14:00:55','2026-09-18 14:00:55',NULL),('63260b1d-4360-4565-9429-ec80b70f5301','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-18 07:23:27','2026-09-18 07:23:27',NULL),('63dde4e9-f7db-4d5e-94ef-d8018d3ee936','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 10:46:05','2026-09-17 10:46:05',NULL),('63f41b1d-3a89-4e91-9e3d-4139ba87a8b6','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'18','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-24 14:05:54','2026-09-24 14:05:54',NULL),('66e73542-bb1b-4dfa-8462-66adcc0cfab9','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 09:01:07','2026-09-17 09:01:07',NULL),('68138af2-e688-47ff-b671-f3d909d4c36a','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 12:02:40','2026-09-17 12:02:40',NULL),('6952ee62-f9a8-4a49-81ed-707930b18497','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'4','Payments','create',NULL,'{\"id\": 4, \"amount\": \"500.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 9, \"created_at\": \"2026-09-24T11:29:50.000000Z\", \"updated_at\": \"2026-09-24T11:29:50.000000Z\", \"barber_shop_id\": 16}','2026-09-24 09:29:50','2026-09-24 09:29:50',NULL),('699b0311-cb0c-4fd6-a8ba-fb428c7afd3e','76edf897-20fa-4c3a-b757-6d8bc980333c','Update BarberShop',NULL,'18','BarberShops','update','{\"logo\": \"placeholder.png\", \"expires_at\": \"2026-10-28T11:48:52.000000Z\"}','{\"logo\": \"uploads/barber-shops/QZgXgTwepAY4IqCD9sUgAE0patHhYk.webp\", \"expires_at\": \"2026-10-28 11:48:00\"}','2026-09-28 10:06:37','2026-09-28 10:06:37',NULL),('6af00a63-e496-425c-870f-c7f8e127a679','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 08:45:05','2026-09-18 08:45:05',NULL),('6d9457d8-3871-43dd-8f52-83c70f29b281','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'10','Payments','create',NULL,'{\"id\": 10, \"amount\": \"1500.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 15, \"created_at\": \"2026-09-24T15:31:58.000000Z\", \"updated_at\": \"2026-09-24T15:31:58.000000Z\", \"barber_shop_id\": 16}','2026-09-24 13:31:58','2026-09-24 13:31:58',NULL),('6e3741e5-ad18-41bb-b5f7-421fc7a76cbc','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'8','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-23 07:32:00','2026-09-23 07:32:00',NULL),('6ef9e861-aea2-4d95-a67a-cff9ab7c3d4b','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Customer',NULL,'4','Customers','create',NULL,'{\"id\": 4, \"name\": \"EGLI HOXHALLARI\", \"email\": \"\", \"phone\": \"0675397560\", \"photo\": \"\", \"blocked_at\": null, \"created_at\": \"2026-09-22T14:24:07.000000Z\", \"updated_at\": \"2026-09-22T14:24:07.000000Z\", \"no_show_count\": 0, \"barber_shop_id\": 16, \"total_bookings\": 0}','2026-09-22 12:24:07','2026-09-22 12:24:07',NULL),('6fa3e59b-9668-4d1f-be48-800cac73b207','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'20','Bookings','update','[]','[]','2026-09-25 11:59:16','2026-09-25 11:59:16',NULL),('723427d4-fa82-4226-abee-b703e1937e46','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'14','Bookings','update','[]','[]','2026-09-24 14:09:17','2026-09-24 14:09:17',NULL),('72778352-4eaa-497a-9e66-55cb82ec7f71','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'9','Payments','create',NULL,'{\"id\": 9, \"amount\": \"1100.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 16, \"created_at\": \"2026-09-24T15:11:27.000000Z\", \"updated_at\": \"2026-09-24T15:11:27.000000Z\", \"barber_shop_id\": 16}','2026-09-24 13:11:27','2026-09-24 13:11:27',NULL),('72db7d7b-a97d-4e6a-a2dc-f776c9ce17e0','76edf897-20fa-4c3a-b757-6d8bc980333c','updated Elton Berberi\'s roles','http://10.10.12.14:5000/admin/users/4b3d5dc3-8544-4aca-bd09-eab94e01f20c/edit','0','Users','Update',NULL,NULL,'2026-09-17 11:57:17','2026-09-17 11:57:17',NULL),('751b5c99-0778-4993-9a64-06a9a3537bac','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'17','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-24 14:04:43','2026-09-24 14:04:43',NULL),('7748c61b-01a6-4700-8c53-ba1388494deb','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','updated role QQQ Role','http://10.10.12.14:5000/admin/settings/roles/ec873a64-4a1a-4e94-842f-270f3ae3962e/edit','0','Roles','Update',NULL,NULL,'2026-09-17 07:06:51','2026-09-17 07:06:51',NULL),('7d1e7302-1905-4378-9bbe-680a14d83412','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','[]','[]','2026-09-24 10:03:08','2026-09-24 10:03:08',NULL),('811c6842-8f5a-4f1d-91de-f49a43f74ce5','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'21','Bookings','update','[]','[]','2026-09-26 04:10:10','2026-09-26 04:10:10',NULL),('82848ea6-a5b9-4d78-b5b7-1538ff3cb249','76edf897-20fa-4c3a-b757-6d8bc980333c','updated Elton Berberi\'s roles','http://10.10.12.14:5000/admin/users/4b3d5dc3-8544-4aca-bd09-eab94e01f20c/edit','0','Users','Update',NULL,NULL,'2026-09-17 14:04:11','2026-09-17 14:04:11',NULL),('82a41308-bb60-4bcc-b488-b32139dcc483','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar','http://10.10.12.14:5000/admin/settings/roles/2567fa55-f7e6-4bb8-8e6c-33e35e0279bd/edit','0','Roles','Update',NULL,NULL,'2026-09-17 07:50:34','2026-09-17 07:50:34',NULL),('834c6728-384a-4bf8-ba65-c141f78f99b3','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'22','Bookings','create',NULL,'{\"id\": 22, \"notes\": \"Shërbimet: Dhenderr Edition\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-26T06:45:55.000000Z\", \"service_id\": 6, \"updated_at\": \"2026-09-26T06:45:55.000000Z\", \"customer_id\": 3, \"total_price\": \"2000.00\", \"appointment_at\": \"2026-09-26T11:20:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 04:45:55','2026-09-26 04:45:55',NULL),('83b4719a-343c-46e0-b4e7-6e775cf9517e','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'22','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-26 04:47:26','2026-09-26 04:47:26',NULL),('8442bf83-a4a6-4b87-aa00-cd0b4e36c83e','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Create Barber',NULL,'6','Barbers','create',NULL,'{\"id\": 6, \"bio\": \"rryyr\", \"name\": \"qee\", \"phone\": \"t6646577\", \"photo\": \"\", \"active\": true, \"user_id\": \"4b3d5dc3-8544-4aca-bd09-eab94e01f20c\", \"created_at\": \"2026-09-17T16:11:26.000000Z\", \"updated_at\": \"2026-09-17T16:11:26.000000Z\", \"barber_shop_id\": 6}','2026-09-17 14:11:26','2026-09-17 14:11:26',NULL),('8606e72f-1210-4b9e-9fb5-5e03033e2136','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 07:06:27','2026-09-17 07:06:27',NULL),('8665efe0-3243-4573-9a90-bc80f37374bc','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'20','Bookings','update','{\"total_price\": \"1900.00\"}','{\"total_price\": 1700}','2026-09-25 14:56:19','2026-09-25 14:56:19',NULL),('86a48eb5-7eb0-4470-ac22-1c938dd862bb','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Service',NULL,'7','Services','create',NULL,'{\"id\": 7, \"name\": \"Larje Koke\", \"price\": \"250.00\", \"active\": true, \"category\": \"\", \"created_at\": \"2026-09-26T10:34:10.000000Z\", \"updated_at\": \"2026-09-26T10:34:10.000000Z\", \"description\": \"\", \"barber_shop_id\": 16, \"duration_minutes\": 15}','2026-09-26 08:34:10','2026-09-26 08:34:10',NULL),('88870608-fb1c-4d63-973e-b0dda6858942','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'13','Bookings','create',NULL,'{\"id\": 13, \"notes\": \"Shërbimet: rroje + Larje dhe Stilim\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 4, \"created_at\": \"2026-09-24T12:39:14.000000Z\", \"service_id\": 5, \"updated_at\": \"2026-09-24T12:39:14.000000Z\", \"customer_id\": 5, \"total_price\": \"1100.00\", \"appointment_at\": \"2026-09-24T17:00:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 10:39:15','2026-09-24 10:39:15',NULL),('8aa10a98-5e97-48c4-a48a-27ee5c2d8344','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'20','Bookings','create',NULL,'{\"id\": 20, \"notes\": \"Shërbimet: Larje dhe Stilim + rroje\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-25T12:26:16.000000Z\", \"service_id\": 4, \"updated_at\": \"2026-09-25T12:26:16.000000Z\", \"customer_id\": 5, \"total_price\": \"1100.00\", \"appointment_at\": \"2026-09-25T16:10:00.000000Z\", \"barber_shop_id\": 16}','2026-09-25 10:26:16','2026-09-25 10:26:16',NULL),('8be61594-66ab-41f0-9aff-0d7ff87f6023','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'13','Payments','create',NULL,'{\"id\": 13, \"amount\": \"1400.00\", \"method\": \"cash\", \"status\": \"pending\", \"booking_id\": 14, \"created_at\": \"2026-09-24T16:08:52.000000Z\", \"updated_at\": \"2026-09-24T16:08:52.000000Z\", \"barber_shop_id\": 16}','2026-09-24 14:08:52','2026-09-24 14:08:52',NULL),('8d7d863e-59fd-4f9c-ae7d-a14df78d5c8c','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'19','Bookings','create',NULL,'{\"id\": 19, \"notes\": \"Shërbimet: Dhenderr Edition\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-25T12:25:21.000000Z\", \"service_id\": 6, \"updated_at\": \"2026-09-25T12:25:21.000000Z\", \"customer_id\": 6, \"total_price\": \"1000.00\", \"appointment_at\": \"2026-09-25T14:35:00.000000Z\", \"barber_shop_id\": 16}','2026-09-25 10:25:22','2026-09-25 10:25:22',NULL),('8ddc030c-3808-47a2-b4f7-fbebbd0fb7b3','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-18 07:44:20','2026-09-18 07:44:20',NULL),('8f8f6520-2c59-4d95-bed5-cacc6c097e89','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 14:24:27','2026-09-17 14:24:27',NULL),('90edbbd7-4dcc-47c3-bad4-ed3e252e1956','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 10:19:07','2026-09-17 10:19:07',NULL),('9685b81b-18c3-43d0-bf7c-d8afc613564d','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 14:20:10','2026-09-17 14:20:10',NULL),('97099ca8-29a7-430b-8986-b8e51efe7d3a','04732a98-8b73-4005-9c1f-55db6d3ab283','updated Elton Berberi\'s roles','http://10.10.12.14:5000/admin/users/4b3d5dc3-8544-4aca-bd09-eab94e01f20c/edit','0','Users','Update',NULL,NULL,'2026-09-17 07:00:47','2026-09-17 07:00:47',NULL),('9a8a5329-e37d-4b4e-9d90-24e5dcdbd18e','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'12','Payments','create',NULL,'{\"id\": 12, \"amount\": \"1800.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 18, \"created_at\": \"2026-09-24T16:06:04.000000Z\", \"updated_at\": \"2026-09-24T16:06:04.000000Z\", \"barber_shop_id\": 16}','2026-09-24 14:06:04','2026-09-24 14:06:04',NULL),('9c00847b-d152-42f3-8647-81a378137f77','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 08:52:42','2026-09-17 08:52:42',NULL),('9cbe5e7c-ab0f-43d3-9647-5c1be351a953','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'22','Bookings','update','{\"notes\": \"Shërbimet: Dhenderr Edition\", \"source\": \"online\"}','{\"notes\": \"Shërbimet: Dhenderr Edition + rroje + Larje dhe Stilim + Rrojre qethje\", \"source\": \"walk-in\"}','2026-09-26 04:57:09','2026-09-26 04:57:09',NULL),('a0a1c6e5-39bc-4dd1-9633-569fa40434c2','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 10:34:52','2026-09-18 10:34:52',NULL),('a0a78a06-8b09-495c-aa8d-b43a222b893c','76edf897-20fa-4c3a-b757-6d8bc980333c','Update Payment',NULL,'14','Payments','update','{\"amount\": \"1700.00\"}','{\"amount\": 1200}','2026-09-25 14:56:47','2026-09-25 14:56:47',NULL),('a1ae0404-cf86-4550-b0a2-806d1441c8bb','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 09:01:44','2026-09-17 09:01:44',NULL),('a2b17c96-b1f5-49e1-a9b3-b8d10f2a7490','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 10:35:13','2026-09-17 10:35:13',NULL),('a3667996-da8b-409b-a1e1-fb4a9ed1c4f2','76edf897-20fa-4c3a-b757-6d8bc980333c','updated Elton Berberi\'s roles','http://10.10.12.14:5000/admin/users/4b3d5dc3-8544-4aca-bd09-eab94e01f20c/edit','0','Users','Update',NULL,NULL,'2026-09-17 08:28:56','2026-09-17 08:28:56',NULL),('a413a6cc-c159-4821-8180-76c2edd4ea85','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Barber',NULL,'4','Barbers','create',NULL,'{\"id\": 4, \"bio\": \"\", \"name\": \"Robert Nurelli\", \"phone\": \"0675397560\", \"photo\": \"\", \"active\": true, \"user_id\": \"04732a98-8b73-4005-9c1f-55db6d3ab283\", \"created_at\": \"2026-09-24T12:08:28.000000Z\", \"updated_at\": \"2026-09-24T12:08:28.000000Z\", \"barber_shop_id\": 16}','2026-09-24 10:08:28','2026-09-24 10:08:28',NULL),('a54e8802-c637-437f-8b9d-d59f7df0adea','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-22 10:28:18','2026-09-22 10:28:18',NULL),('a66c0078-f694-40a3-bb4a-45214e72c0f7','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'15','Bookings','update','[]','[]','2026-09-24 13:31:50','2026-09-24 13:31:50',NULL),('a6bb3e78-b482-47fc-8204-664ee2aad4d0','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 14:48:25','2026-09-18 14:48:25',NULL),('a86e7c67-c75d-4d72-8898-aa9ba93f20db','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'14','Payments','create',NULL,'{\"id\": 14, \"amount\": \"1100.00\", \"method\": \"cash\", \"status\": \"pending\", \"booking_id\": 20, \"created_at\": \"2026-09-25T13:09:47.000000Z\", \"updated_at\": \"2026-09-25T13:09:47.000000Z\", \"barber_shop_id\": 16}','2026-09-25 11:09:47','2026-09-25 11:09:47',NULL),('a936e1bd-a1db-4712-9791-5ed3dd04715a','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Menaxher','http://10.10.12.14:5000/admin/settings/roles/e54f8f78-a794-4ee1-97e3-55fd44630ab7/edit','0','Roles','Update',NULL,NULL,'2026-09-17 08:23:59','2026-09-17 08:23:59',NULL),('aacf173e-11b6-478d-97b9-e8b10564ec80','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 07:47:20','2026-09-17 07:47:20',NULL),('aade4c88-1018-43b9-ac09-f2913fb12507','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 14:08:21','2026-09-17 14:08:21',NULL),('aba76366-36d1-4dbc-99db-88f7fd1a2b7c','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'11','Payments','update','[]','[]','2026-09-25 10:32:45','2026-09-25 10:32:45',NULL),('ac01b62a-779f-4aba-a303-9d1748b9d6cd','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-17 14:23:21','2026-09-17 14:23:21',NULL),('ac081b09-7c5c-4752-9254-f90291496775','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-22 10:57:51','2026-09-22 10:57:51',NULL),('ad74dc88-716c-462c-80fa-2c31f5620409','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Berber','http://10.10.12.14:5000/admin/settings/roles/0663f25d-7fc2-4c9f-bd56-3b9622eae87a/edit','0','Roles','Update',NULL,NULL,'2026-09-17 14:21:36','2026-09-17 14:21:36',NULL),('ae5235ae-f1b2-43d3-8308-38c713f52246','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 10:34:19','2026-09-17 10:34:19',NULL),('aee661f7-149c-49e4-b272-908b008782a0','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'15','Bookings','create',NULL,'{\"id\": 15, \"notes\": \"Shërbimet: Dhenderr Edition + Larje dhe Stilim\", \"source\": \"phone\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-24T15:08:15.000000Z\", \"service_id\": 6, \"updated_at\": \"2026-09-24T15:08:15.000000Z\", \"customer_id\": 5, \"total_price\": \"1500.00\", \"appointment_at\": \"2026-09-25T09:15:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 13:08:15','2026-09-24 13:08:15',NULL),('af2065d4-ba51-4271-b499-f07b0d5e668b','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','{\"status\": \"no-show\"}','{\"status\": \"completed\"}','2026-09-24 10:03:38','2026-09-24 10:03:38',NULL),('b053fd5b-172e-4572-8221-06b57309c46e','76edf897-20fa-4c3a-b757-6d8bc980333c','updated Elton Berberi\'s roles','http://10.10.12.14:5000/admin/users/4b3d5dc3-8544-4aca-bd09-eab94e01f20c/edit','0','Users','Update',NULL,NULL,'2026-09-17 07:49:35','2026-09-17 07:49:35',NULL),('b25aa42f-5e22-4ce5-859d-d4e5d8f99378','04732a98-8b73-4005-9c1f-55db6d3ab283','updated role QQQ Role','http://10.10.12.14:5000/admin/settings/roles/ec873a64-4a1a-4e94-842f-270f3ae3962e/edit','0','Roles','Update',NULL,NULL,'2026-09-16 15:06:50','2026-09-16 15:06:50',NULL),('b26071d6-b9e9-48cb-9050-27f9c1d6b541','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'7','Payments','update','[]','[]','2026-09-24 10:03:16','2026-09-24 10:03:16',NULL),('b30d9367-00e6-4064-9eda-010d2fe81c4f','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Customer',NULL,'6','Customers','create',NULL,'{\"id\": 6, \"name\": \"Ferid\", \"email\": \"\", \"phone\": \"0658884\", \"photo\": \"\", \"blocked_at\": null, \"created_at\": \"2026-09-24T11:33:19.000000Z\", \"updated_at\": \"2026-09-24T11:33:19.000000Z\", \"no_show_count\": 0, \"barber_shop_id\": 16, \"total_bookings\": 0}','2026-09-24 09:33:19','2026-09-24 09:33:19',NULL),('b33cdeea-66c5-43ab-a784-67a9d39e32f1','76edf897-20fa-4c3a-b757-6d8bc980333c','created role Menaxher','http://10.10.12.14:5000/admin/settings/roles/e54f8f78-a794-4ee1-97e3-55fd44630ab7/edit','0','Roles','created',NULL,NULL,'2026-09-17 08:22:18','2026-09-17 08:22:18',NULL),('b49b3b8f-58fb-4333-bec2-5d813ae05b39','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'20','Bookings','update','{\"notes\": \"Shërbimet: Larje dhe Stilim + rroje\", \"source\": \"online\", \"total_price\": \"1100.00\"}','{\"notes\": \"Shërbimet: Larje dhe Stilim + rroje + Rrojre qethje\", \"source\": \"walk-in\", \"total_price\": 1900}','2026-09-25 12:00:03','2026-09-25 12:00:03',NULL),('b4e06074-85de-4dc3-803e-c76e8f5cbc7d','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'27','Bookings','create',NULL,'{\"id\": 27, \"notes\": \"Shërbimet: Larje Koke\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-26T10:34:24.000000Z\", \"service_id\": 7, \"updated_at\": \"2026-09-26T10:34:24.000000Z\", \"customer_id\": 5, \"total_price\": \"250.00\", \"appointment_at\": \"2026-09-26T14:25:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 08:34:24','2026-09-26 08:34:24',NULL),('b50db0ae-c273-4766-a19a-2bc8fed5072b','76edf897-20fa-4c3a-b757-6d8bc980333c','created role PronarDyqani','http://10.10.12.14:5000/admin/settings/roles/0b5c47e4-181c-431b-af31-79c80bb3bcdf/edit','0','Roles','created',NULL,NULL,'2026-09-17 11:55:43','2026-09-17 11:55:43',NULL),('b5d37b10-ef01-45f7-bc42-2fc34e9ef3bf','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'18','Payments','create',NULL,'{\"id\": 18, \"amount\": \"2000.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 22, \"created_at\": \"2026-09-26T19:08:45.000000Z\", \"updated_at\": \"2026-09-26T19:08:45.000000Z\", \"barber_shop_id\": 16}','2026-09-26 17:08:45','2026-09-26 17:08:45',NULL),('b5d61a0b-70ea-4d84-ac44-d1ac49a3645a','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'17','Bookings','update','[]','[]','2026-09-25 10:32:37','2026-09-25 10:32:37',NULL),('b5f9d65b-0fae-46b0-aeca-d8a8fcfb679a','76edf897-20fa-4c3a-b757-6d8bc980333c','Create WorkingHour',NULL,'5','WorkingHours','create',NULL,'{\"id\": 5, \"barber_id\": 3, \"is_closed\": false, \"open_time\": \"08:00\", \"close_time\": \"20:00\", \"created_at\": \"2026-09-22T12:56:05.000000Z\", \"updated_at\": \"2026-09-22T12:56:05.000000Z\", \"day_of_week\": \"Friday\"}','2026-09-22 10:56:05','2026-09-22 10:56:05',NULL),('b6294b27-c576-4b4e-9892-643acd005bfe','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'21','Bookings','update','[]','[]','2026-09-26 04:44:05','2026-09-26 04:44:05',NULL),('b6bd077c-32e0-4890-bf88-66a1b392eb47','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'19','Bookings','update','[]','[]','2026-09-25 12:05:44','2026-09-25 12:05:44',NULL),('b7af0f6a-680f-4c01-b8ec-468e64924a80','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-18 08:35:32','2026-09-18 08:35:32',NULL),('b7b27a96-b6ae-41cf-a2fa-67f8ef8ce8cc','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'13','Payments','update','[]','[]','2026-09-24 14:09:40','2026-09-24 14:09:40',NULL),('b92c340d-af95-44cf-ad31-33b149f1d66f','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Berber','http://10.10.12.14:5000/admin/settings/roles/0663f25d-7fc2-4c9f-bd56-3b9622eae87a/edit','0','Roles','Update',NULL,NULL,'2026-09-17 14:22:14','2026-09-17 14:22:14',NULL),('ba0d8f5b-20b5-4d7d-b443-c532a29e5a21','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Create Barber',NULL,'3','Barbers','create',NULL,'{\"id\": 3, \"bio\": \"testtsadad\", \"name\": \"bERBERI1 b2\", \"phone\": \"+454545d4d6ad4\", \"photo\": \"\", \"active\": true, \"user_id\": \"4b3d5dc3-8544-4aca-bd09-eab94e01f20c\", \"created_at\": \"2026-09-17T16:06:53.000000Z\", \"updated_at\": \"2026-09-17T16:06:53.000000Z\", \"barber_shop_id\": 6}','2026-09-17 14:06:53','2026-09-17 14:06:53',NULL),('baa7a3ac-1fb4-4e61-8ee1-1b754263f19e','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 12:00:51','2026-09-17 12:00:51',NULL),('bbd62eb2-6898-4e2f-a30a-5e3feffb4644','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar','http://10.10.12.14:5000/admin/settings/roles/0b5c47e4-181c-431b-af31-79c80bb3bcdf/edit','0','Roles','Update',NULL,NULL,'2026-09-17 11:56:30','2026-09-17 11:56:30',NULL),('bbe4c550-fae2-4f06-98e1-a1dd1f6ddc2e','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'16','Payments','update','[]','[]','2026-09-26 04:07:08','2026-09-26 04:07:08',NULL),('be8e481a-8037-49e9-b523-52d44797cff7','76edf897-20fa-4c3a-b757-6d8bc980333c','Create Barber',NULL,'3','Barbers','create',NULL,'{\"id\": 3, \"bio\": \"test\", \"name\": \"Egli\", \"phone\": \"+355675397560\", \"photo\": \"\", \"active\": true, \"user_id\": \"a9be7713-4099-49e7-9e6e-3b5ac1d87628\", \"created_at\": \"2026-09-22T12:46:22.000000Z\", \"updated_at\": \"2026-09-22T12:46:22.000000Z\", \"barber_shop_id\": 16}','2026-09-22 10:46:22','2026-09-22 10:46:22',NULL),('c049fcb9-a08f-4796-966a-295dbf6ac61a','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Service',NULL,'5','Services','create',NULL,'{\"id\": 5, \"name\": \"rroje\", \"price\": \"400.00\", \"active\": true, \"category\": \"\", \"created_at\": \"2026-09-23T15:32:00.000000Z\", \"updated_at\": \"2026-09-23T15:32:00.000000Z\", \"description\": \"\", \"barber_shop_id\": 16, \"duration_minutes\": 20}','2026-09-23 13:32:00','2026-09-23 13:32:00',NULL),('c1021287-f532-4619-afd6-9bfdf908467a','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'17','Bookings','update','[]','[]','2026-09-25 10:31:58','2026-09-25 10:31:58',NULL),('c13b2bba-4279-474a-9aca-96e37c08e583','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Create Service',NULL,'3','Services','create',NULL,'{\"id\": 3, \"name\": \"qqq\", \"price\": \"345.00\", \"active\": false, \"category\": \"6565\", \"created_at\": \"2026-09-17T09:14:58.000000Z\", \"updated_at\": \"2026-09-17T09:14:58.000000Z\", \"description\": \"qqqq\", \"barber_shop_id\": 2, \"duration_minutes\": 56}','2026-09-17 07:14:58','2026-09-17 07:14:58',NULL),('c16e4503-1f18-4bd8-975f-607f4342541d','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'20','Bookings','update','[]','[]','2026-09-25 11:10:12','2026-09-25 11:10:12',NULL),('c393a43c-d485-45b8-ae81-2fa2cf28b44a','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'20','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-25 11:09:35','2026-09-25 11:09:35',NULL),('c7f63d8d-98cc-44e4-a1cd-0286ff37499a','76edf897-20fa-4c3a-b757-6d8bc980333c','Create WorkingHour',NULL,'8','WorkingHours','create',NULL,'{\"id\": 8, \"barber_id\": 3, \"is_closed\": true, \"open_time\": null, \"close_time\": null, \"created_at\": \"2026-09-22T13:03:31.000000Z\", \"updated_at\": \"2026-09-22T13:03:31.000000Z\", \"day_of_week\": \"Sunday\"}','2026-09-22 11:03:31','2026-09-22 11:03:31',NULL),('c8362b64-9759-41fc-86d7-af436d0f61ed','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'17','Payments','create',NULL,'{\"id\": 17, \"amount\": \"600.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 23, \"created_at\": \"2026-09-26T19:08:29.000000Z\", \"updated_at\": \"2026-09-26T19:08:29.000000Z\", \"barber_shop_id\": 16}','2026-09-26 17:08:29','2026-09-26 17:08:29',NULL),('c9327ef2-ba13-40d8-92bf-c5c87430478e','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'10','Bookings','update','[]','[]','2026-09-24 10:02:48','2026-09-24 10:02:48',NULL),('cacef586-9b6f-4a27-888b-8692230c3c9a','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'25','Bookings','create',NULL,'{\"id\": 25, \"notes\": \"Shërbimet: Dhenderr Edition\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-26T10:20:05.000000Z\", \"service_id\": 6, \"updated_at\": \"2026-09-26T10:20:05.000000Z\", \"customer_id\": 6, \"total_price\": \"1000.00\", \"appointment_at\": \"2026-09-26T13:35:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 08:20:05','2026-09-26 08:20:05',NULL),('cc6864c5-f727-476e-a1b5-f3d8d95af2d2','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'15','Bookings','update','[]','[]','2026-09-25 10:19:26','2026-09-25 10:19:26',NULL),('cdafc63c-5f24-4a05-973c-ffe5f80bd4bd','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'9','Bookings','update','[]','[]','2026-09-24 09:29:32','2026-09-24 09:29:32',NULL),('cdd4ba8c-5e31-4f8b-88d9-0ba9c61e57f2','76edf897-20fa-4c3a-b757-6d8bc980333c','Create Barber',NULL,'4','Barbers','create',NULL,'{\"id\": 4, \"bio\": \"Consequatur Aperiam\", \"name\": \"Isabelle Mcmillan\", \"phone\": \"+1 (534) 975-7294\", \"photo\": \"\", \"active\": false, \"user_id\": \"04732a98-8b73-4005-9c1f-55db6d3ab283\", \"created_at\": \"2026-09-18T16:35:49.000000Z\", \"updated_at\": \"2026-09-18T16:35:49.000000Z\", \"barber_shop_id\": 8}','2026-09-18 14:35:49','2026-09-18 14:35:49',NULL),('cf70da55-846b-40a3-a4c3-832819d57f3c','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-17 12:01:26','2026-09-17 12:01:26',NULL),('cfa3079f-5e4d-4778-81e3-b094ecf6dc86','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-18 07:18:42','2026-09-18 07:18:42',NULL),('d1afeb02-06bf-44e1-abc3-c49aa43e29c5','76edf897-20fa-4c3a-b757-6d8bc980333c','Create WorkingHour',NULL,'4','WorkingHours','create',NULL,'{\"id\": 4, \"barber_id\": 3, \"is_closed\": false, \"open_time\": \"08:00\", \"close_time\": \"20:00\", \"created_at\": \"2026-09-22T12:55:44.000000Z\", \"updated_at\": \"2026-09-22T12:55:44.000000Z\", \"day_of_week\": \"Thursday\"}','2026-09-22 10:55:44','2026-09-22 10:55:44',NULL),('d2f24796-47ea-41dc-b2ed-73738048dff5','76edf897-20fa-4c3a-b757-6d8bc980333c','created role Pronar','http://10.10.12.14:5000/admin/settings/roles/2567fa55-f7e6-4bb8-8e6c-33e35e0279bd/edit','0','Roles','created',NULL,NULL,'2026-09-17 07:47:50','2026-09-17 07:47:50',NULL),('d3814198-c325-4828-bda0-3fd5f9a9364b','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'23','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-26 17:08:20','2026-09-26 17:08:20',NULL),('d597657a-e639-440a-ac84-64aa6428c5a0','76edf897-20fa-4c3a-b757-6d8bc980333c','Create Barber',NULL,'9','Barbers','create',NULL,'{\"id\": 9, \"bio\": \"Excepturi veniam an\", \"name\": \"Sonya Bartlett\", \"phone\": \"+1 (757) 137-9857\", \"photo\": \"\", \"active\": true, \"user_id\": \"04732a98-8b73-4005-9c1f-55db6d3ab283\", \"created_at\": \"2026-09-18T16:38:40.000000Z\", \"updated_at\": \"2026-09-18T16:38:40.000000Z\", \"barber_shop_id\": 7}','2026-09-18 14:38:40','2026-09-18 14:38:40',NULL),('d5bdf787-3298-4c6c-ae22-01297169d107','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'24','Bookings','create',NULL,'{\"id\": 24, \"notes\": \"Shërbimet: Dhenderr Edition\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-26T09:53:09.000000Z\", \"service_id\": 6, \"updated_at\": \"2026-09-26T09:53:09.000000Z\", \"customer_id\": 4, \"total_price\": \"1000.00\", \"appointment_at\": \"2026-09-26T13:35:00.000000Z\", \"barber_shop_id\": 16}','2026-09-26 07:53:09','2026-09-26 07:53:09',NULL),('d5f0148a-96e1-4cd0-b6cf-37de02deffb3','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','updated role Berber','http://10.10.12.14:5000/admin/settings/roles/0663f25d-7fc2-4c9f-bd56-3b9622eae87a/edit','0','Roles','Update',NULL,NULL,'2026-09-17 14:09:16','2026-09-17 14:09:16',NULL),('d65afa43-7254-4b4d-91ca-cf413095be61','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','updated role Berber','http://10.10.12.14:5000/admin/settings/roles/0663f25d-7fc2-4c9f-bd56-3b9622eae87a/edit','0','Roles','Update',NULL,NULL,'2026-09-17 14:27:27','2026-09-17 14:27:27',NULL),('d6764df2-8d2d-4afc-baad-fcc79a1ebb5a','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'16','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-24 13:11:17','2026-09-24 13:11:17',NULL),('d6caed08-c80a-4fef-9307-f005f45a1b0d','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'7','Bookings','create',NULL,'{\"id\": 7, \"notes\": \"\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-23T09:16:34.000000Z\", \"service_id\": 3, \"updated_at\": \"2026-09-23T09:16:34.000000Z\", \"customer_id\": 5, \"total_price\": \"800.00\", \"appointment_at\": \"2026-09-23T15:30:00.000000Z\", \"barber_shop_id\": 16}','2026-09-23 07:16:34','2026-09-23 07:16:34',NULL),('d77f519e-416d-4a4c-b198-387327940cd1','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-18 08:55:38','2026-09-18 08:55:38',NULL),('d90327cd-656f-4915-b54f-edb3ce11d3f7','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'4','Bookings','create',NULL,'{\"id\": 4, \"notes\": \"\", \"source\": \"phone\", \"status\": \"confirmed\", \"barber_id\": 2, \"created_at\": \"2026-09-22T12:03:56.000000Z\", \"service_id\": 1, \"updated_at\": \"2026-09-22T12:03:56.000000Z\", \"customer_id\": 2, \"total_price\": \"10.00\", \"appointment_at\": \"2026-09-22T16:30:00.000000Z\", \"barber_shop_id\": 15}','2026-09-22 10:03:56','2026-09-22 10:03:56',NULL),('dc2c517c-664f-4aa4-bf31-3748eacae598','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner1','http://10.10.12.14:5000/admin/settings/roles/98c2b068-a59c-49ec-8d8e-fdf87e816ede/edit','0','Roles','Update',NULL,NULL,'2026-09-17 14:03:58','2026-09-17 14:03:58',NULL),('dcb2dfc3-3ebf-42a0-b801-f10fd0a262dd','76edf897-20fa-4c3a-b757-6d8bc980333c','Delete WorkingHour',NULL,'8','WorkingHours','delete',NULL,NULL,'2026-09-22 11:03:46','2026-09-22 11:03:46',NULL),('dcf80e42-1fc9-4f25-90c7-edbbfc239be3','04732a98-8b73-4005-9c1f-55db6d3ab283','Create WorkingHour',NULL,'7','WorkingHours','create',NULL,'{\"id\": 7, \"barber_id\": 3, \"is_closed\": true, \"open_time\": null, \"close_time\": null, \"created_at\": \"2026-09-22T13:03:14.000000Z\", \"updated_at\": \"2026-09-22T13:03:14.000000Z\", \"day_of_week\": \"Sunday\"}','2026-09-22 11:03:14','2026-09-22 11:03:14',NULL),('dfa020e1-bee4-46ba-a997-c7c9e1df2d44','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'11','Payments','create',NULL,'{\"id\": 11, \"amount\": \"600.00\", \"method\": \"cash\", \"status\": \"pending\", \"booking_id\": 17, \"created_at\": \"2026-09-24T16:04:57.000000Z\", \"updated_at\": \"2026-09-24T16:04:57.000000Z\", \"barber_shop_id\": 16}','2026-09-24 14:04:57','2026-09-24 14:04:57',NULL),('e384daca-f7bb-414f-8ea1-db9080714a52','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Barber',NULL,'2','Barbers','update','{\"name\": \"Elton Berberi\"}','{\"name\": \"Elton Berberi1\"}','2026-09-21 14:45:59','2026-09-21 14:45:59',NULL),('e46f498f-aeab-4d35-a8f4-ff7377510781','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Create Barber',NULL,'4','Barbers','create',NULL,'{\"id\": 4, \"bio\": \"Quibusdam reprehende\", \"name\": \"Warren Curry\", \"phone\": \"+1 (725) 737-9681\", \"photo\": \"\", \"active\": false, \"user_id\": \"a9be7713-4099-49e7-9e6e-3b5ac1d87628\", \"created_at\": \"2026-09-17T16:09:44.000000Z\", \"updated_at\": \"2026-09-17T16:09:44.000000Z\", \"barber_shop_id\": 6}','2026-09-17 14:09:44','2026-09-17 14:09:44',NULL),('e7a2dfff-23a9-400b-aad4-b5e9817c7b11','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','updated role QQQ Role','http://10.10.12.14:5000/admin/settings/roles/ec873a64-4a1a-4e94-842f-270f3ae3962e/edit','0','Roles','Update',NULL,NULL,'2026-09-17 07:07:18','2026-09-17 07:07:18',NULL),('e8e28a49-2883-4b8b-9cd0-bb7866d3ea8a','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Owner','http://10.10.12.14:5000/admin/settings/roles/0b5c47e4-181c-431b-af31-79c80bb3bcdf/edit','0','Roles','Update',NULL,NULL,'2026-09-17 14:02:40','2026-09-17 14:02:40',NULL),('e99ab682-8ccd-47f5-9e6e-a8189c19a510','76edf897-20fa-4c3a-b757-6d8bc980333c','Create Barber',NULL,'3','Barbers','create',NULL,'{\"id\": 3, \"bio\": \"Est provident qui d\", \"name\": \"Ahmed Casey\", \"phone\": \"+1 (654) 251-2704\", \"photo\": \"\", \"active\": true, \"user_id\": \"04732a98-8b73-4005-9c1f-55db6d3ab283\", \"created_at\": \"2026-09-18T13:50:28.000000Z\", \"updated_at\": \"2026-09-18T13:50:28.000000Z\", \"barber_shop_id\": 3}','2026-09-18 11:50:28','2026-09-18 11:50:28',NULL),('e9de01e5-e5e9-4ac5-a06a-efbd4780f931','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-16 14:26:32','2026-09-16 14:26:32',NULL),('eaacc1f1-5af0-4504-b28b-1d26d544c4ac','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Service',NULL,'6','Services','create',NULL,'{\"id\": 6, \"name\": \"Dhenderr Edition\", \"price\": \"1000.00\", \"active\": true, \"category\": \"\", \"created_at\": \"2026-09-24T15:07:44.000000Z\", \"updated_at\": \"2026-09-24T15:07:44.000000Z\", \"description\": \"\", \"barber_shop_id\": 16, \"duration_minutes\": 50}','2026-09-24 13:07:44','2026-09-24 13:07:44',NULL),('eb8f8ce4-080a-48b9-ae06-6f7e73ee913f','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar i Biznesit / Sallonit','http://10.10.12.14:5000/admin/settings/roles/46d5ce5b-5460-4bca-bdf5-27bba4f9858f/edit','0','Roles','Update',NULL,NULL,'2026-09-28 09:32:07','2026-09-28 09:32:07',NULL),('ec6f5f3c-5854-4980-b2a4-732b0d3c9faa','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'6','Bookings','create',NULL,'{\"id\": 6, \"notes\": \"\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-23T09:15:12.000000Z\", \"service_id\": 3, \"updated_at\": \"2026-09-23T09:15:12.000000Z\", \"customer_id\": 4, \"total_price\": \"800.00\", \"appointment_at\": \"2026-09-23T16:00:00.000000Z\", \"barber_shop_id\": 16}','2026-09-23 07:15:12','2026-09-23 07:15:12',NULL),('ed59d94c-ddc2-47e2-a014-d1d6ca04be58','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Payment',NULL,'3','Payments','create',NULL,'{\"id\": 3, \"amount\": \"1200.00\", \"method\": \"cash\", \"status\": \"paid\", \"booking_id\": 10, \"created_at\": \"2026-09-24T11:27:42.000000Z\", \"updated_at\": \"2026-09-24T11:27:42.000000Z\", \"barber_shop_id\": 16}','2026-09-24 09:27:42','2026-09-24 09:27:42',NULL),('eddb1295-caa4-4672-99c2-1261f9fba6f3','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Logged in',NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Auth','Login',NULL,NULL,'2026-09-17 08:51:22','2026-09-17 08:51:22',NULL),('f0b00b51-f9ef-4958-b381-64aa4c357ad9','76edf897-20fa-4c3a-b757-6d8bc980333c','Logged in',NULL,'76edf897-20fa-4c3a-b757-6d8bc980333c','Auth','Login',NULL,NULL,'2026-09-18 11:32:35','2026-09-18 11:32:35',NULL),('f0bed470-ff5c-4f55-bde0-d3a5d4b8f9ae','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Payment',NULL,'7','Payments','update','[]','[]','2026-09-24 10:03:46','2026-09-24 10:03:46',NULL),('f1166895-e713-45f3-a347-2e218f21c718','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'25','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-26 17:08:56','2026-09-26 17:08:56',NULL),('f2566cf0-c828-42af-811a-3aa84de5abbd','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Booking',NULL,'10','Bookings','create',NULL,'{\"id\": 10, \"notes\": \"Shërbimet: rroje + Rrojre qethje\", \"source\": \"online\", \"status\": \"pending\", \"barber_id\": 3, \"created_at\": \"2026-09-24T10:56:11.000000Z\", \"service_id\": 5, \"updated_at\": \"2026-09-24T10:56:11.000000Z\", \"customer_id\": 5, \"total_price\": \"1200.00\", \"appointment_at\": \"2026-09-24T14:00:00.000000Z\", \"barber_shop_id\": 16}','2026-09-24 08:56:12','2026-09-24 08:56:12',NULL),('f28383df-5cbe-4aff-b491-7efcdc004f75','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar','http://10.10.12.14:5000/admin/settings/roles/0b5c47e4-181c-431b-af31-79c80bb3bcdf/edit','0','Roles','Update',NULL,NULL,'2026-09-17 14:01:40','2026-09-17 14:01:40',NULL),('f3309244-076b-49fb-a8d8-2bdcc65db6b1','04732a98-8b73-4005-9c1f-55db6d3ab283','Update Booking',NULL,'11','Bookings','update','{\"status\": \"pending\"}','{\"status\": \"completed\"}','2026-09-24 09:54:31','2026-09-24 09:54:31',NULL),('f4485ec7-a0aa-4b8d-a597-ccfbc1c04acb','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-17 11:32:56','2026-09-17 11:32:56',NULL),('f5d59072-1852-4e32-96ea-088ec7e5e694','04732a98-8b73-4005-9c1f-55db6d3ab283','Logged in',NULL,'04732a98-8b73-4005-9c1f-55db6d3ab283','Auth','Login',NULL,NULL,'2026-09-18 08:21:52','2026-09-18 08:21:52',NULL),('f842d15e-71c2-47ec-96bb-d899ce8d7664','4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Create Barber',NULL,'5','Barbers','create',NULL,'{\"id\": 5, \"bio\": \"\", \"name\": \"ewewe\", \"phone\": \"wewewe\", \"photo\": \"\", \"active\": true, \"user_id\": \"4b3d5dc3-8544-4aca-bd09-eab94e01f20c\", \"created_at\": \"2026-09-17T16:10:17.000000Z\", \"updated_at\": \"2026-09-17T16:10:17.000000Z\", \"barber_shop_id\": 6}','2026-09-17 14:10:17','2026-09-17 14:10:17',NULL),('f8f42099-7af4-424b-9e6d-65fd76a8a4b7','76edf897-20fa-4c3a-b757-6d8bc980333c','updated role Pronar i Biznesit / Sallonit','http://10.10.12.14:5000/admin/settings/roles/46d5ce5b-5460-4bca-bdf5-27bba4f9858f/edit','0','Roles','Update',NULL,NULL,'2026-09-28 08:35:56','2026-09-28 08:35:56',NULL),('fcf29e8f-e74d-4aab-a39c-a75d3b8c8ab2','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Service',NULL,'4','Services','create',NULL,'{\"id\": 4, \"name\": \"Larje dhe Stilim\", \"price\": \"500.00\", \"active\": true, \"category\": \"\", \"created_at\": \"2026-09-23T14:52:55.000000Z\", \"updated_at\": \"2026-09-23T14:52:55.000000Z\", \"description\": \"\", \"barber_shop_id\": 16, \"duration_minutes\": 20}','2026-09-23 12:52:55','2026-09-23 12:52:55',NULL),('fda18316-5531-4255-a6bc-f6349f3caa36','04732a98-8b73-4005-9c1f-55db6d3ab283','Create Customer',NULL,'5','Customers','create',NULL,'{\"id\": 5, \"name\": \"Paulin\", \"email\": \"\", \"phone\": \"066665745656\", \"photo\": \"\", \"blocked_at\": null, \"created_at\": \"2026-09-23T09:15:52.000000Z\", \"updated_at\": \"2026-09-23T09:15:52.000000Z\", \"no_show_count\": 0, \"barber_shop_id\": 16, \"total_bookings\": 0}','2026-09-23 07:15:52','2026-09-23 07:15:52',NULL);
/*!40000 ALTER TABLE `audit_trails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barber_shop_user`
--

DROP TABLE IF EXISTS `barber_shop_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barber_shop_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barber_shop_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barber_shop_user_user_id_barber_shop_id_unique` (`user_id`,`barber_shop_id`),
  CONSTRAINT `barber_shop_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barber_shop_user`
--

LOCK TABLES `barber_shop_user` WRITE;
/*!40000 ALTER TABLE `barber_shop_user` DISABLE KEYS */;
INSERT INTO `barber_shop_user` VALUES (113,'76edf897-20fa-4c3a-b757-6d8bc980333c',13,NULL,NULL),(114,'76edf897-20fa-4c3a-b757-6d8bc980333c',14,NULL,NULL),(117,'76edf897-20fa-4c3a-b757-6d8bc980333c',15,NULL,NULL),(118,'76edf897-20fa-4c3a-b757-6d8bc980333c',16,NULL,NULL),(120,'04732a98-8b73-4005-9c1f-55db6d3ab283',16,NULL,NULL),(121,'04732a98-8b73-4005-9c1f-55db6d3ab283',15,NULL,NULL),(122,'7ca3ce60-df50-41db-88f3-4983ab29f0ef',17,NULL,NULL),(123,'283b5e00-4b0e-4fab-aa0a-894e51ccd8aa',18,NULL,NULL),(124,'1d1df8c6-0d8a-4779-9c8c-acc2247789dc',19,NULL,NULL),(125,'dbb77630-40f2-45b4-9427-5cd634868ec2',18,NULL,NULL);
/*!40000 ALTER TABLE `barber_shop_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barber_shops`
--

DROP TABLE IF EXISTS `barber_shops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barber_shops` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'barbershop',
  `staff_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_label_plural` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shop_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secondary_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `sms_enabled` tinyint(1) NOT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_no_show_before_block` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `barber_shops_owner_id_foreign` (`owner_id`),
  CONSTRAINT `barber_shops_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barber_shops`
--

LOCK TABLES `barber_shops` WRITE;
/*!40000 ALTER TABLE `barber_shops` DISABLE KEYS */;
INSERT INTO `barber_shops` VALUES (15,'04732a98-8b73-4005-9c1f-55db6d3ab283','BERBERANA 1','barbershop',NULL,NULL,NULL,NULL,'BAR1','berberana-1','placeholder.png','placeholder.png','#111111','#f5a623','2026-10-05 15:57:49','2026-10-28 11:48:52',1,1,'Europe/Tirane',3,'2026-09-21 13:57:49','2026-09-28 09:48:52'),(16,'04732a98-8b73-4005-9c1f-55db6d3ab283','BERBERANA 2','barbershop',NULL,NULL,NULL,NULL,'BAR2','berberana-2','placeholder.png','placeholder.png','#059669','#e44d26','2026-10-05 15:57:49','2026-10-28 11:48:52',1,0,'Europe/Tirane',5,'2026-09-21 13:57:49','2026-09-28 09:48:52'),(17,'7ca3ce60-df50-41db-88f3-4983ab29f0ef','Gentlemen Barber Shop','barbershop','Berber','Berberët','Berberanë','Shërbimi','Gentlemen Barber','gentlemen-barber-shop','placeholder.png','placeholder.png','#1E293B','#F59E0B','2026-10-28 10:59:11','2026-10-28 11:48:52',1,1,'Europe/Tirane',3,'2026-09-28 07:45:27','2026-09-28 09:48:52'),(18,'283b5e00-4b0e-4fab-aa0a-894e51ccd8aa','Elegance Beauty Salon','beauty_salon','Parukier/e','Parukierët','Sallon Bukurie','Shërbimi','Elegance Salon','elegance-beauty-salon','uploads/barber-shops/QZgXgTwepAY4IqCD9sUgAE0patHhYk.webp','uploads/barber-shops/500PXi4c0H3rltCXDLp3Q4rqL9j5AT.webp','#DB2777','#8B5CF6','2026-10-28 10:59:00','2026-10-28 11:48:00',1,1,'Europe/Tirane',3,'2026-09-28 07:46:06','2026-09-28 12:04:28'),(19,'1d1df8c6-0d8a-4779-9c8c-acc2247789dc','Glamour Nail Studio','nail_studio','Teknik/e','Teknikët','Studio Thonjsh','Shërbimi','Glamour Nails','glamour-nail-studio','placeholder.png','placeholder.png','#10B981','#F43F5E','2026-10-28 10:59:12','2026-10-28 11:48:52',1,1,'Europe/Tirane',3,'2026-09-28 07:46:07','2026-09-28 09:48:52');
/*!40000 ALTER TABLE `barber_shops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barbers`
--

DROP TABLE IF EXISTS `barbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barbers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `barbers_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `barbers_user_id_foreign` (`user_id`),
  CONSTRAINT `barbers_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `barbers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barbers`
--

LOCK TABLES `barbers` WRITE;
/*!40000 ALTER TABLE `barbers` DISABLE KEYS */;
INSERT INTO `barbers` VALUES (1,15,'04732a98-8b73-4005-9c1f-55db6d3ab283','Ardit Berberi','+355691111111','barber1.png','10 vjet eksperiencë.',1,'2026-09-21 13:57:49','2026-09-21 13:57:49'),(2,15,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Elton Berberi1','+355692222222','','Specialist në flokë kaçurrela.',1,'2026-09-21 13:57:50','2026-09-21 14:45:59'),(3,16,'a9be7713-4099-49e7-9e6e-3b5ac1d87628','Egli','+355675397560','','test',1,'2026-09-22 10:46:22','2026-09-22 10:46:22'),(4,16,'04732a98-8b73-4005-9c1f-55db6d3ab283','Robert Nurelli','0675397560','','',1,'2026-09-24 10:08:28','2026-09-24 10:08:28'),(5,17,'000b74d7-adb2-46ab-b64f-e8296997b52f','Mario Berberi','+355691111111','barber1.png','Master Barber me 8 vite eksperiencë në prerje me stil.',1,'2026-09-28 07:45:28','2026-09-28 07:45:28'),(6,18,'dbb77630-40f2-45b4-9427-5cd634868ec2','Klara Stylist','+355692222222','barber2.png','Specialiste për lyerje, balayage dhe trajtime keratine.',1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(7,19,'c8c0cb93-57e7-4073-a4d1-76b07d80cdcc','Ledia NailTech','+355693333333','barber3.png','Speciale në artin e thonjve me xhel, akrilik dhe nail-art.',1,'2026-09-28 07:46:07','2026-09-28 07:46:07');
/*!40000 ALTER TABLE `barbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `barber_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `appointment_at` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled','no-show') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` enum('unpaid','partially_paid','paid','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `total_price` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `source` enum('online','walk-in','phone') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bookings_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `bookings_barber_id_foreign` (`barber_id`),
  KEY `bookings_service_id_foreign` (`service_id`),
  KEY `bookings_customer_id_foreign` (`customer_id`),
  CONSTRAINT `bookings_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`),
  CONSTRAINT `bookings_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `bookings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `bookings_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (14,16,3,5,3,'2026-09-24 17:15:00','completed','paid',1400.00,'Shërbimet: rroje + Rrojre qethje','online','2026-09-24 11:35:36','2026-09-24 14:09:40'),(15,16,3,6,5,'2026-09-25 09:15:00','completed','paid',1500.00,'Shërbimet: Dhenderr Edition + Larje dhe Stilim','phone','2026-09-24 13:08:15','2026-09-24 13:31:58'),(16,16,3,5,3,'2026-09-25 11:00:00','completed','paid',1100.00,'Shërbimet: rroje + Larje dhe Stilim','online','2026-09-24 13:09:09','2026-09-24 13:11:27'),(17,16,3,5,4,'2026-09-25 10:30:00','completed','unpaid',600.00,'Shërbimet: rroje','online','2026-09-24 13:10:32','2026-09-24 14:04:43'),(18,16,3,3,6,'2026-09-25 12:30:00','completed','paid',1800.00,'Shërbimet: Rrojre qethje + Dhenderr Edition','online','2026-09-24 14:05:40','2026-09-24 14:06:04'),(19,16,3,6,6,'2026-09-25 14:35:00','completed','paid',1000.00,'Shërbimet: Dhenderr Edition','online','2026-09-25 10:25:21','2026-09-25 12:05:53'),(20,16,3,4,5,'2026-09-25 16:10:00','completed','paid',1700.00,'Shërbimet: Larje dhe Stilim + rroje + Rrojre qethje','walk-in','2026-09-25 10:26:16','2026-09-25 14:56:19'),(21,16,3,6,3,'2026-09-26 09:45:00','completed','unpaid',1000.00,'Shërbimet: Dhenderr Edition','online','2026-09-26 04:06:26','2026-09-26 04:06:40'),(22,16,3,6,3,'2026-09-26 11:20:00','completed','paid',2000.00,'Shërbimet: Dhenderr Edition + rroje + Larje dhe Stilim + Rrojre qethje','walk-in','2026-09-26 04:45:55','2026-09-26 17:08:45'),(23,16,3,5,6,'2026-09-26 09:15:00','completed','paid',600.00,'Shërbimet: rroje + Larje dhe Stilim','walk-in','2026-09-26 04:59:16','2026-09-26 17:08:29'),(25,16,3,6,6,'2026-09-26 13:35:00','completed','paid',1000.00,'Shërbimet: Dhenderr Edition','online','2026-09-26 08:20:05','2026-09-26 17:09:00'),(26,16,3,6,6,'2026-09-26 14:40:00','completed','unpaid',1000.00,'Shërbimet: Dhenderr Edition','online','2026-09-26 08:25:49','2026-09-26 08:46:26'),(27,16,3,7,5,'2026-09-26 14:25:00','pending','unpaid',250.00,'Shërbimet: Larje Koke','online','2026-09-26 08:34:24','2026-09-26 08:34:24'),(28,17,5,8,7,'2026-09-28 11:46:41','confirmed','unpaid',1000.00,NULL,'online','2026-09-28 07:46:41','2026-09-28 07:46:41'),(29,18,6,10,8,'2026-09-28 12:46:41','confirmed','unpaid',2000.00,NULL,'online','2026-09-28 07:46:41','2026-09-28 07:46:41'),(30,19,7,12,9,'2026-09-28 13:46:41','confirmed','unpaid',2500.00,NULL,'online','2026-09-28 07:46:41','2026-09-28 07:46:41'),(31,17,5,8,7,'2026-09-28 12:59:13','confirmed','unpaid',1000.00,NULL,'online','2026-09-28 08:59:13','2026-09-28 08:59:13'),(32,18,6,10,8,'2026-09-28 13:59:13','cancelled','unpaid',2000.00,NULL,'online','2026-09-28 08:59:13','2026-09-28 10:12:43'),(33,19,7,12,9,'2026-09-28 14:59:13','confirmed','unpaid',2500.00,NULL,'online','2026-09-28 08:59:13','2026-09-28 08:59:13'),(34,18,6,10,8,'2026-09-29 12:45:00','pending','unpaid',2000.00,'Shërbimet: Qethje & Modelim Femrash','online','2026-09-28 10:41:09','2026-09-28 10:41:09');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bookings` int NOT NULL,
  `no_show_count` int NOT NULL,
  `blocked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_barber_shop_id_foreign` (`barber_shop_id`),
  CONSTRAINT `customers_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,15,'Gentian Klienti','+355693333333','gentian@test.com','cust1.png',2,1,NULL,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(2,15,'Sara Kliente','+355694444444','sara@test.com','cust2.png',1,0,NULL,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(3,16,'EGLI','064849','','',0,0,NULL,'2026-09-22 12:22:27','2026-09-22 12:22:27'),(4,16,'EGLI HOXHALLARI','0675397560','','',0,0,NULL,'2026-09-22 12:24:07','2026-09-22 12:24:07'),(5,16,'Paulin','066665745656','','',0,0,NULL,'2026-09-23 07:15:52','2026-09-23 07:15:52'),(6,16,'Ferid','0658884','','',0,0,NULL,'2026-09-24 09:33:19','2026-09-24 09:33:19'),(7,17,'Albano Klienti','+355698888881','albano@test.com',NULL,1,0,NULL,'2026-09-28 07:46:41','2026-09-28 07:46:41'),(8,18,'Anisa Kliente','+355698888882','anisa@test.com',NULL,1,0,NULL,'2026-09-28 07:46:41','2026-09-28 07:46:41'),(9,19,'Dorina Kliente','+355698888883','dorina@test.com',NULL,1,0,NULL,'2026-09-28 07:46:41','2026-09-28 07:46:41');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fcm_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` enum('android','ios') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sms_gateway` tinyint(1) NOT NULL DEFAULT '0',
  `device_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_tokens_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `device_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `device_tokens_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_tokens`
--

LOCK TABLES `device_tokens` WRITE;
/*!40000 ALTER TABLE `device_tokens` DISABLE KEYS */;
INSERT INTO `device_tokens` VALUES (1,15,'04732a98-8b73-4005-9c1f-55db6d3ab283','test-fcm-token-6ab153fee5665','android',0,NULL,'2026-09-21 15:57:50','2026-09-21 13:57:50','2026-09-21 13:57:50'),(5,18,'dbb77630-40f2-45b4-9427-5cd634868ec2','device-id-1790594436001','android',1,'Android Phone (Klara Stylist)','2026-09-28 12:55:17','2026-09-28 10:55:17','2026-09-28 10:55:17');
/*!40000 ALTER TABLE `device_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_settings`
--

DROP TABLE IF EXISTS `event_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `realtime_event_id` bigint unsigned NOT NULL,
  `reverb_enabled` tinyint(1) NOT NULL,
  `firebase_enabled` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_settings_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `event_settings_realtime_event_id_foreign` (`realtime_event_id`),
  CONSTRAINT `event_settings_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `event_settings_realtime_event_id_foreign` FOREIGN KEY (`realtime_event_id`) REFERENCES `realtime_events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_settings`
--

LOCK TABLES `event_settings` WRITE;
/*!40000 ALTER TABLE `event_settings` DISABLE KEYS */;
INSERT INTO `event_settings` VALUES (1,15,1,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(2,15,2,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(3,15,3,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(4,15,4,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(5,15,5,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(6,15,6,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(7,15,7,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(8,15,8,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(9,15,9,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(10,15,10,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(11,15,11,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(12,15,12,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(13,15,13,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(14,15,14,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(15,15,15,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(16,15,16,1,1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(17,15,17,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(18,15,18,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(19,15,19,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(20,15,20,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(21,15,21,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(22,15,22,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(23,15,23,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(24,15,24,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(25,15,25,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(26,15,26,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(27,15,27,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(28,15,28,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(29,15,29,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(30,15,30,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(31,15,31,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(32,15,32,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(33,15,33,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(34,15,34,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(35,15,35,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(36,15,36,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(37,15,37,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(38,15,38,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(39,15,39,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(40,15,40,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(41,15,41,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(42,15,42,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(43,15,43,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(44,15,44,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(45,15,45,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(46,15,46,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(47,15,47,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(48,15,48,1,0,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(49,17,1,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(50,17,2,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(51,17,3,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(52,17,4,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(53,17,5,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(54,17,6,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(55,17,7,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(56,17,8,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(57,17,9,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(58,17,10,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(59,17,11,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(60,17,12,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(61,17,13,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(62,17,14,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(63,17,15,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(64,17,16,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(65,17,17,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(66,17,18,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(67,17,19,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(68,17,20,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(69,17,21,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(70,17,22,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(71,17,23,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(72,17,24,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(73,17,25,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(74,17,26,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(75,17,27,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(76,17,28,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(77,17,29,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(78,17,30,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(79,17,31,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(80,17,32,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(81,17,33,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(82,17,34,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(83,17,35,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(84,17,36,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(85,17,37,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(86,17,38,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(87,17,39,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(88,17,40,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(89,17,41,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(90,17,42,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(91,17,43,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(92,17,44,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(93,17,45,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(94,17,46,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(95,17,47,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(96,17,48,1,1,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(97,18,1,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(98,18,2,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(99,18,3,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(100,18,4,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(101,18,5,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(102,18,6,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(103,18,7,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(104,18,8,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(105,18,9,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(106,18,10,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(107,18,11,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(108,18,12,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(109,18,13,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(110,18,14,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(111,18,15,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(112,18,16,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(113,18,17,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(114,18,18,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(115,18,19,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(116,18,20,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(117,18,21,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(118,18,22,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(119,18,23,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(120,18,24,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(121,18,25,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(122,18,26,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(123,18,27,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(124,18,28,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(125,18,29,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(126,18,30,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(127,18,31,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(128,18,32,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(129,18,33,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(130,18,34,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(131,18,35,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(132,18,36,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(133,18,37,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(134,18,38,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(135,18,39,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(136,18,40,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(137,18,41,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(138,18,42,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(139,18,43,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(140,18,44,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(141,18,45,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(142,18,46,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(143,18,47,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(144,18,48,1,1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(145,19,1,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(146,19,2,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(147,19,3,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(148,19,4,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(149,19,5,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(150,19,6,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(151,19,7,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(152,19,8,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(153,19,9,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(154,19,10,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(155,19,11,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(156,19,12,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(157,19,13,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(158,19,14,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(159,19,15,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(160,19,16,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(161,19,17,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(162,19,18,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(163,19,19,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(164,19,20,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(165,19,21,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(166,19,22,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(167,19,23,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(168,19,24,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(169,19,25,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(170,19,26,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(171,19,27,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(172,19,28,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(173,19,29,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(174,19,30,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(175,19,31,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(176,19,32,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(177,19,33,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(178,19,34,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(179,19,35,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(180,19,36,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(181,19,37,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(182,19,38,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(183,19,39,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(184,19,40,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(185,19,41,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(186,19,42,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(187,19,43,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(188,19,44,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(189,19,45,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(190,19,46,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(191,19,47,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(192,19,48,1,1,'2026-09-28 07:46:07','2026-09-28 07:46:07');
/*!40000 ALTER TABLE `event_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_logs`
--

DROP TABLE IF EXISTS `message_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `channel` enum('sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_logs_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `message_logs_customer_id_foreign` (`customer_id`),
  CONSTRAINT `message_logs_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `message_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_logs`
--

LOCK TABLES `message_logs` WRITE;
/*!40000 ALTER TABLE `message_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_queues`
--

DROP TABLE IF EXISTS `message_queues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_queues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `booking_id` bigint unsigned NOT NULL,
  `channel` enum('sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('pending','processing','sent','failed','skipped_limit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `retry_count` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_queues_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `message_queues_booking_id_foreign` (`booking_id`),
  CONSTRAINT `message_queues_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `message_queues_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_queues`
--

LOCK TABLES `message_queues` WRITE;
/*!40000 ALTER TABLE `message_queues` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_queues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_templates`
--

DROP TABLE IF EXISTS `message_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `channel` enum('sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('reminder','confirmation','welcome') COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_templates_barber_shop_id_foreign` (`barber_shop_id`),
  CONSTRAINT `message_templates_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_templates`
--

LOCK TABLES `message_templates` WRITE;
/*!40000 ALTER TABLE `message_templates` DISABLE KEYS */;
INSERT INTO `message_templates` VALUES (1,15,'sms','reminder','Përshëndetje {customer_name}, ju kujtojmë rezervimin tuaj në orën {time}.','2026-09-21 13:57:50','2026-09-21 13:57:50'),(2,15,'sms','confirmation','Rezervimi juaj për {time} u konfirmua. Faleminderit {customer_name}!','2026-09-21 13:57:50','2026-09-21 13:57:50'),(3,15,'sms','welcome','Mirë se erdhe {customer_name}! Faleminderit që zgjodhe sallonin tonë.','2026-09-21 13:57:50','2026-09-21 13:57:50'),(4,17,'sms','confirmation','Përshëndetje {customer_name}! Rezervimi juaj për {service_name} me {staff_name} në {shop_name} u konfirmua për orën {time}. Faleminderit!','2026-09-28 07:46:05','2026-09-28 07:46:05'),(5,17,'sms','reminder','Përshëndetje {customer_name}, ju kujtojmë takimin tuaj për {service_name} në {shop_name} sot në orën {time}.','2026-09-28 07:46:05','2026-09-28 07:46:05'),(6,18,'sms','confirmation','Përshëndetje {customer_name}! Rezervimi juaj për {service_name} me {staff_name} në {shop_name} u konfirmua për orën {time}. Faleminderit!','2026-09-28 07:46:06','2026-09-28 07:46:06'),(7,18,'sms','reminder','Përshëndetje {customer_name}, ju kujtojmë takimin tuaj për {service_name} në {shop_name} sot në orën {time}.','2026-09-28 07:46:06','2026-09-28 07:46:06'),(8,19,'sms','confirmation','Përshëndetje {customer_name}! Rezervimi juaj për {service_name} me {staff_name} në {shop_name} u konfirmua për orën {time}. Faleminderit!','2026-09-28 07:46:07','2026-09-28 07:46:07'),(9,19,'sms','reminder','Përshëndetje {customer_name}, ju kujtojmë takimin tuaj për {service_name} në {shop_name} sot në orën {time}.','2026-09-28 07:46:07','2026-09-28 07:46:07');
/*!40000 ALTER TABLE `message_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=589 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2013_04_12_000000_create_users_table',1),(2,'2014_10_12_000000_create_audit_trails_table',1),(3,'2014_10_12_100000_create_password_reset_tokens_table',1),(4,'2019_08_19_000000_create_failed_jobs_table',1),(5,'2019_12_14_000001_create_personal_access_tokens_table',1),(6,'2021_04_27_164928_create_settings_table',1),(7,'2021_05_08_200523_create_notifications_table',1),(8,'2023_02_20_005102_create_permission_tables',1),(9,'2024_02_16_211228_create_jobs_table',1),(10,'2026_09_11_100000_create_realtime_events_table',1),(44,'2026_09_16_173000_unify_database_collations',19),(78,'2026_09_16_143707_repair_database_schema_for_teams',36),(143,'2026_09_16_183000_fix_spatie_teams_primary_keys',53),(177,'2026_09_16_190000_force_fix_spatie_pk',54),(461,'2026_09_21_132158_create_barber_shops_table',55),(567,'2026_09_21_155553_create_plans_table',56),(568,'2026_09_21_155602_create_subscriptions_table',57),(569,'2026_09_21_155610_create_barbers_table',58),(570,'2026_09_21_155618_create_services_table',59),(572,'2026_09_21_155633_create_customers_table',61),(573,'2026_09_21_155642_create_notification_channels_table',62),(574,'2026_09_21_155647_create_event_settings_table',63),(575,'2026_09_21_155651_create_message_templates_table',64),(576,'2026_09_21_155656_create_bookings_table',65),(577,'2026_09_21_155708_create_payments_table',66),(578,'2026_09_21_155714_create_message_queues_table',67),(579,'2026_09_21_155726_create_message_logs_table',68),(580,'2026_09_21_155733_create_device_tokens_table',69),(581,'2026_09_21_155740_create_reviews_table',70),(583,'2026_09_22_123924_create_working_hours_table',71),(584,'2026_09_22_123958_add_payment_status_to_bookings_table',72),(585,'2026_09_22_130103_make_working_hours_time_columns_nullable',73),(586,'2026_09_22_132016_add_lunch_break_to_working_hours_table',74),(587,'2026_09_28_120000_add_business_type_to_barber_shops_table',75),(588,'2026_09_28_130000_add_is_sms_gateway_to_device_tokens_table',76);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barber_shop_id` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`barber_shop_id`,`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `model_has_permissions_barber_shop_id_index` (`barber_shop_id`),
  KEY `model_has_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
INSERT INTO `model_has_permissions` VALUES ('59135fd8-cb05-498b-b81b-aa6d91c98b9f','App\\Models\\User','04732a98-8b73-4005-9c1f-55db6d3ab283',16);
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barber_shop_id` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`barber_shop_id`,`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `model_has_roles_barber_shop_id_index` (`barber_shop_id`),
  KEY `model_has_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES ('cf6877ac-6e54-48f0-b90e-856dc6b371ea','App\\Models\\User','76edf897-20fa-4c3a-b757-6d8bc980333c',0),('cf6877ac-6e54-48f0-b90e-856dc6b371ea','App\\Models\\User','76edf897-20fa-4c3a-b757-6d8bc980333c',13),('cf6877ac-6e54-48f0-b90e-856dc6b371ea','App\\Models\\User','76edf897-20fa-4c3a-b757-6d8bc980333c',14),('0663f25d-7fc2-4c9f-bd56-3b9622eae87a','App\\Models\\User','04732a98-8b73-4005-9c1f-55db6d3ab283',15),('cf6877ac-6e54-48f0-b90e-856dc6b371ea','App\\Models\\User','76edf897-20fa-4c3a-b757-6d8bc980333c',15),('98c2b068-a59c-49ec-8d8e-fdf87e816ede','App\\Models\\User','04732a98-8b73-4005-9c1f-55db6d3ab283',16),('cf6877ac-6e54-48f0-b90e-856dc6b371ea','App\\Models\\User','76edf897-20fa-4c3a-b757-6d8bc980333c',16),('46d5ce5b-5460-4bca-bdf5-27bba4f9858f','App\\Models\\User','7ca3ce60-df50-41db-88f3-4983ab29f0ef',17),('46d5ce5b-5460-4bca-bdf5-27bba4f9858f','App\\Models\\User','283b5e00-4b0e-4fab-aa0a-894e51ccd8aa',18),('46d5ce5b-5460-4bca-bdf5-27bba4f9858f','App\\Models\\User','dbb77630-40f2-45b4-9427-5cd634868ec2',18),('46d5ce5b-5460-4bca-bdf5-27bba4f9858f','App\\Models\\User','1d1df8c6-0d8a-4779-9c8c-acc2247789dc',19);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_channels`
--

DROP TABLE IF EXISTS `notification_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_channels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `channel` enum('sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `daily_limit` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_channels_barber_shop_id_foreign` (`barber_shop_id`),
  CONSTRAINT `notification_channels_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_channels`
--

LOCK TABLES `notification_channels` WRITE;
/*!40000 ALTER TABLE `notification_channels` DISABLE KEYS */;
INSERT INTO `notification_channels` VALUES (1,15,'sms',1,100,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(2,15,'whatsapp',0,NULL,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(3,17,'sms',1,200,'2026-09-28 07:46:05','2026-09-28 07:46:05'),(4,18,'sms',1,200,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(5,19,'sms',1,200,'2026-09-28 07:46:07','2026-09-28 07:46:07');
/*!40000 ALTER TABLE `notification_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_to_user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_from_user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `viewed` tinyint(1) DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_assigned_to_user_id_foreign` (`assigned_to_user_id`),
  KEY `notifications_assigned_from_user_id_foreign` (`assigned_from_user_id`),
  CONSTRAINT `notifications_assigned_from_user_id_foreign` FOREIGN KEY (`assigned_from_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `notifications_assigned_to_user_id_foreign` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `booking_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` enum('cash','card') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','paid','refunded','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `payments_booking_id_foreign` (`booking_id`),
  CONSTRAINT `payments_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (9,16,16,1100.00,'cash','paid','2026-09-24 13:11:27','2026-09-24 13:11:27'),(10,16,15,1500.00,'cash','paid','2026-09-24 13:31:58','2026-09-24 13:31:58'),(11,16,17,600.00,'cash','refunded','2026-09-24 14:04:57','2026-09-25 10:32:45'),(12,16,18,1800.00,'cash','paid','2026-09-24 14:06:04','2026-09-24 14:06:04'),(13,16,14,1400.00,'cash','paid','2026-09-24 14:08:52','2026-09-24 14:09:39'),(14,16,20,1200.00,'cash','paid','2026-09-25 11:09:47','2026-09-25 14:56:47'),(15,16,19,1100.00,'cash','paid','2026-09-25 12:05:53','2026-09-25 12:05:53'),(16,16,21,1000.00,'cash','pending','2026-09-26 04:06:48','2026-09-26 04:44:42'),(17,16,23,600.00,'cash','paid','2026-09-26 17:08:29','2026-09-26 17:08:29'),(18,16,22,2000.00,'cash','paid','2026-09-26 17:08:45','2026-09-26 17:08:45'),(19,16,25,1000.00,'cash','paid','2026-09-26 17:09:00','2026-09-26 17:09:00'),(20,17,28,1000.00,'cash','paid','2026-09-28 07:46:41','2026-09-28 07:46:41'),(21,18,29,2000.00,'cash','paid','2026-09-28 07:46:41','2026-09-28 07:46:41'),(22,19,30,2500.00,'cash','paid','2026-09-28 07:46:42','2026-09-28 07:46:42'),(23,17,31,1000.00,'cash','paid','2026-09-28 08:59:13','2026-09-28 08:59:13'),(24,18,32,2000.00,'cash','paid','2026-09-28 08:59:13','2026-09-28 08:59:13'),(25,19,33,2500.00,'cash','paid','2026-09-28 08:59:13','2026-09-28 08:59:13');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES ('037bdf96-5e40-4c18-80f8-ff4101364b42','add_subscriptions','Add Subscription','Subscriptions','web','2026-09-21 13:56:09','2026-09-21 13:56:09'),('0c69c66b-d42b-4b17-9728-47ae2de6a9d5','edit_message_queues','Edit MessageQueue','MessageQueues','web','2026-09-21 13:57:24','2026-09-21 13:57:24'),('139a95f3-6f66-4a34-b35b-107aab1b5508','delete_notification_channels','Delete NotificationChannel','NotificationChannels','web','2026-09-21 13:56:45','2026-09-21 13:56:45'),('14305210-e51a-4be9-a990-86b05d8413f9','view_subscriptions','View Subscription','Subscriptions','web','2026-09-21 13:56:09','2026-09-21 13:56:09'),('15921bcd-5145-414c-a782-faf392071144','edit_customers','Edit Customer','Customers','web','2026-09-21 13:56:41','2026-09-21 13:56:41'),('15da3acb-132e-4002-b760-40359dd25ddb','add_message_logs','Add MessageLog','MessageLogs','web','2026-09-21 13:57:31','2026-09-21 13:57:31'),('16f24fb6-4789-4c42-92bc-be09cc44e833','add_services','Add Service','Services','web','2026-09-21 13:56:25','2026-09-21 13:56:25'),('1b7e0705-218a-445d-92d7-769a901a272c','view_barber_shops','View BarberShop','BarberShops','web','2026-09-21 11:22:09','2026-09-21 11:22:09'),('1d8a6964-f4fa-467d-aeac-4e260058639e','view_users_profiles','View Users Profiles','Users','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('1f1abbd7-d266-4ea2-b97e-e1c1fab15037','add_working_hours','Add WorkingHour','WorkingHours','web','2026-09-22 10:39:29','2026-09-22 10:39:29'),('2282901c-f6f4-4fd7-b71e-0e452d9fd6bd','edit_roles','Edit Roles','Roles','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('283ca8e0-d318-4058-abb2-cab0826660e5','add_device_tokens','Add DeviceToken','DeviceTokens','web','2026-09-21 13:57:38','2026-09-21 13:57:38'),('2f572e05-3bab-494a-91d6-838ec99961e9','add_barbers','Add Barber','Barbers','web','2026-09-21 13:56:17','2026-09-21 13:56:17'),('315ab74a-c537-44a1-a2d3-8bc4d866cf6e','view_users_activity','View Users Activity','Users','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('37bd9069-6c66-46cb-a029-18416d8f27f6','view_payments','View Payment','Payments','web','2026-09-21 13:57:13','2026-09-21 13:57:13'),('396f8e7e-49df-4083-9854-e2e4e747a5d0','delete_payments','Delete Payment','Payments','web','2026-09-21 13:57:13','2026-09-21 13:57:13'),('39c0834c-30e5-4be4-a142-183c30caed89','view_device_tokens','View DeviceToken','DeviceTokens','web','2026-09-21 13:57:38','2026-09-21 13:57:38'),('3a016d37-3b96-4ff8-ae1d-59a2f55f1a05','add_reviews','Add Review','Reviews','web','2026-09-21 13:57:45','2026-09-21 13:57:45'),('3ad66477-71ab-4de6-b38f-687b8cacb479','add_event_settings','Add EventSetting','EventSettings','web','2026-09-21 13:56:50','2026-09-21 13:56:50'),('3b54c5b7-6344-46f7-84d2-58148cbb09ac','delete_reviews','Delete Review','Reviews','web','2026-09-21 13:57:45','2026-09-21 13:57:45'),('3b59f8d7-edd3-4410-877b-5c93f66ddd06','view_system_settings','View System Settings','Settings','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('3cfdb947-4618-48b7-aef0-cc622b93230c','view_audit_trails','View Audit Trails','Settings','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('3e48d907-2106-49d1-9cde-395492c3a2f6','edit_event_settings','Edit EventSetting','EventSettings','web','2026-09-21 13:56:50','2026-09-21 13:56:50'),('406a62e3-c602-4261-bac2-055b6e9201dd','view_message_logs','View MessageLog','MessageLogs','web','2026-09-21 13:57:31','2026-09-21 13:57:31'),('45ac8163-1d7f-42a6-b30c-bb9849585b2f','delete_event_settings','Delete EventSetting','EventSettings','web','2026-09-21 13:56:50','2026-09-21 13:56:50'),('49bde613-adb7-4f69-9811-8b98d3198400','add_message_queues','Add MessageQueue','MessageQueues','web','2026-09-21 13:57:24','2026-09-21 13:57:24'),('4e84e638-21e3-467b-a2e6-993cb0cd787f','delete_users','Delete Users','Users','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('505471d0-d49b-46b9-a389-a4ce78095d33','view_services','View Service','Services','web','2026-09-21 13:56:25','2026-09-21 13:56:25'),('50daa1eb-4f34-4d9c-97dd-8cab9d235a57','add_roles','Add Roles','Roles','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('512cd6d3-1ca8-427d-ac72-6cd1cbd01440','edit_reviews','Edit Review','Reviews','web','2026-09-21 13:57:45','2026-09-21 13:57:45'),('5718f56c-035c-430f-9bb6-183b5655ea48','edit_device_tokens','Edit DeviceToken','DeviceTokens','web','2026-09-21 13:57:38','2026-09-21 13:57:38'),('5828dbba-bc40-41bb-903b-d70881df7508','view_message_queues','View MessageQueue','MessageQueues','web','2026-09-21 13:57:24','2026-09-21 13:57:24'),('59135fd8-cb05-498b-b81b-aa6d91c98b9f','view_bookings','View Booking','Bookings','web','2026-09-21 13:57:06','2026-09-21 13:57:06'),('59df9929-29f1-403b-8bd5-571b4c0d6f02','delete_plans','Delete Plan','Plans','web','2026-09-21 13:56:01','2026-09-21 13:56:01'),('652d3931-7a39-43e3-9e5d-bd6e2b5fc860','add_message_templates','Add MessageTemplate','MessageTemplates','web','2026-09-21 13:56:55','2026-09-21 13:56:55'),('6645cdbc-ed2e-494b-acdc-93ab466d1abe','view_dashboard','View Dashboard','App','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('672d11ac-77de-4b11-a781-b6a4c133f19c','delete_device_tokens','Delete DeviceToken','DeviceTokens','web','2026-09-21 13:57:38','2026-09-21 13:57:38'),('6bacd33c-b8de-496c-9e0c-a9d5afd763d3','view_event_settings','View EventSetting','EventSettings','web','2026-09-21 13:56:50','2026-09-21 13:56:50'),('6e4291b6-03fc-4763-b35f-963e0e0e9c10','delete_barber_shops','Delete BarberShop','BarberShops','web','2026-09-21 11:22:09','2026-09-21 11:22:09'),('73fcca2a-cba7-4197-a5f3-6a573acff63b','delete_roles','Delete Roles','Roles','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('764800d6-821d-4973-b58e-859d6afd4b21','view_working_hours','View WorkingHour','WorkingHours','web','2026-09-22 10:39:29','2026-09-22 10:39:29'),('838ef7b0-b8f1-47ea-8d9c-5e7e4c48d6de','edit_payments','Edit Payment','Payments','web','2026-09-21 13:57:13','2026-09-21 13:57:13'),('887004c7-c634-4156-8b16-f8286fe6c156','edit_subscriptions','Edit Subscription','Subscriptions','web','2026-09-21 13:56:09','2026-09-21 13:56:09'),('8cd5a860-4e5e-4d7b-8484-3fb3fcc39613','view_customers','View Customer','Customers','web','2026-09-21 13:56:41','2026-09-21 13:56:41'),('8f4eed46-9421-43fb-b0c8-b7901a561ba4','delete_working_hours','Delete WorkingHour','WorkingHours','web','2026-09-22 10:39:29','2026-09-22 10:39:29'),('90afff39-df4c-40dd-8ea8-9f9acdb93fe9','view_notifications','View Notifications','App','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('917ddc0c-b180-450d-ae0e-eb1de86b0ff4','edit_barber_shops','Edit BarberShop','BarberShops','web','2026-09-21 11:22:09','2026-09-21 11:22:09'),('920b11d8-cb46-4dcd-bce6-df9b3fb0ff13','add_plans','Add Plan','Plans','web','2026-09-21 13:56:01','2026-09-21 13:56:01'),('9401528f-2899-4233-aa09-313e0a6568d4','add_customers','Add Customer','Customers','web','2026-09-21 13:56:41','2026-09-21 13:56:41'),('9635db26-5b93-4562-b97c-aec9aa8fce07','view_reviews','View Review','Reviews','web','2026-09-21 13:57:45','2026-09-21 13:57:45'),('96a5e70c-c030-4bfd-bec1-6269ed9f3522','edit_message_logs','Edit MessageLog','MessageLogs','web','2026-09-21 13:57:31','2026-09-21 13:57:31'),('9786ed02-fbfa-4f81-9da2-72a3c2167517','delete_subscriptions','Delete Subscription','Subscriptions','web','2026-09-21 13:56:09','2026-09-21 13:56:09'),('9d7aacae-b518-4b7c-be3a-4d278b4cad70','edit_plans','Edit Plan','Plans','web','2026-09-21 13:56:01','2026-09-21 13:56:01'),('9d9396ae-d6a1-490d-b7d8-c23e73ae3dec','view_plans','View Plan','Plans','web','2026-09-21 13:56:01','2026-09-21 13:56:01'),('9e576426-e8e9-4b1e-9e76-f6ca0293e31f','delete_message_queues','Delete MessageQueue','MessageQueues','web','2026-09-21 13:57:24','2026-09-21 13:57:24'),('a591361c-c655-41fd-a0c0-b8de11a80e12','add_payments','Add Payment','Payments','web','2026-09-21 13:57:13','2026-09-21 13:57:13'),('b762b972-77fa-4be5-88ae-b398615addba','view_barbers','View Barber','Barbers','web','2026-09-21 13:56:17','2026-09-21 13:56:17'),('b86249a8-36de-43c3-acbe-3d70e9a89b5b','delete_message_templates','Delete MessageTemplate','MessageTemplates','web','2026-09-21 13:56:55','2026-09-21 13:56:55'),('bd89b944-ea65-416e-9d41-c0eb1be6750e','edit_own_account','Edit Own Account','Users','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('bdc61e08-fc05-4aa8-9d01-4c6323e0ee9a','edit_services','Edit Service','Services','web','2026-09-21 13:56:25','2026-09-21 13:56:25'),('be18213f-47f7-40d2-9899-36007ce933f3','delete_services','Delete Service','Services','web','2026-09-21 13:56:25','2026-09-21 13:56:25'),('be27ed35-8824-4b93-8bd5-c7b81bc0aac8','edit_working_hours','Edit WorkingHour','WorkingHours','web','2026-09-22 10:39:29','2026-09-22 10:39:29'),('c14f9bc9-aae8-4e41-99e4-7ec2f2706729','view_roles','View Roles','Roles','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('c212bedf-bb0a-4939-ab0b-b06df0f00db9','delete_customers','Delete Customer','Customers','web','2026-09-21 13:56:41','2026-09-21 13:56:41'),('c602e3e0-8159-4038-9c56-94e320fffc7e','edit_bookings','Edit Booking','Bookings','web','2026-09-21 13:57:06','2026-09-21 13:57:06'),('c7b02ad2-cf7a-4b55-bca2-90e06bf6b4ee','delete_barbers','Delete Barber','Barbers','web','2026-09-21 13:56:17','2026-09-21 13:56:17'),('d2b8ebab-afdf-421c-91ad-35e82291ade8','edit_users','Edit Users','Users','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('d2f1c0ec-f266-4fab-b995-56a9beb9906f','edit_barbers','Edit Barber','Barbers','web','2026-09-21 13:56:17','2026-09-21 13:56:17'),('d6d950e6-d2e2-4d02-be53-175c6d9c7087','edit_notification_channels','Edit NotificationChannel','NotificationChannels','web','2026-09-21 13:56:45','2026-09-21 13:56:45'),('d71f461c-b85c-48c4-bb65-4227275e0b89','view_users','View Users','Users','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('dc537b8d-3503-4580-b6e3-282477215934','view_notification_channels','View NotificationChannel','NotificationChannels','web','2026-09-21 13:56:45','2026-09-21 13:56:45'),('e065d5fe-419c-4451-80f0-7e2adb08c0d6','add_barber_shops','Add BarberShop','BarberShops','web','2026-09-21 11:22:09','2026-09-21 11:22:09'),('e34c0b60-d105-4d10-a1ab-4a531b2cc8fb','add_notification_channels','Add NotificationChannel','NotificationChannels','web','2026-09-21 13:56:45','2026-09-21 13:56:45'),('e7dec957-5bf3-493a-b7c5-0a28f3e0ff45','edit_message_templates','Edit MessageTemplate','MessageTemplates','web','2026-09-21 13:56:55','2026-09-21 13:56:55'),('e802983d-0066-45a9-8bb9-f66c994ab68f','delete_bookings','Delete Booking','Bookings','web','2026-09-21 13:57:06','2026-09-21 13:57:06'),('e8f9277a-b2c5-4325-9a51-0f1a740690bf','add_bookings','Add Booking','Bookings','web','2026-09-21 13:57:06','2026-09-21 13:57:06'),('e9087eb5-e69f-45ef-b094-f5b5b07b435d','view_message_templates','View MessageTemplate','MessageTemplates','web','2026-09-21 13:56:55','2026-09-21 13:56:55'),('f84ab01f-e7d2-4f23-90cf-ddbc943fc48e','add_users','Add Users','Users','web','2026-09-16 13:48:54','2026-09-16 13:48:54'),('f9121adc-8dbf-4a93-81cd-3a72643c1b4a','delete_message_logs','Delete MessageLog','MessageLogs','web','2026-09-21 13:57:31','2026-09-21 13:57:31');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES ('6d631441-9d62-44e5-9cbf-a779b06589c0','App\\Models\\User','dbb77630-40f2-45b4-9427-5cd634868ec2','Mobile APK','637aecb010ddd669b7effe933a716f14a5b14e49464f0d13dd586198f5b5f4e2','[\"*\"]','2026-09-28 12:59:29',NULL,'2026-09-28 11:41:04','2026-09-28 12:59:29');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `duration_months` int NOT NULL,
  `max_barbers` int NOT NULL,
  `max_services` int NOT NULL,
  `max_shops` int NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'Starter',19.99,1,3,10,1,1,'2026-09-21 13:57:49','2026-09-21 13:57:49'),(2,'Pro',49.99,1,10,50,3,1,'2026-09-21 13:57:49','2026-09-21 13:57:49'),(3,'Unlimited',99.99,1,999,999,999,1,'2026-09-21 13:57:49','2026-09-21 13:57:49'),(4,'Pro Unlimited',49.99,1,20,100,5,1,'2026-09-28 07:45:27','2026-09-28 07:45:27');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realtime_events`
--

DROP TABLE IF EXISTS `realtime_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realtime_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firebase_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realtime_events_event_unique` (`event`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realtime_events`
--

LOCK TABLES `realtime_events` WRITE;
/*!40000 ALTER TABLE `realtime_events` DISABLE KEYS */;
INSERT INTO `realtime_events` VALUES (1,'barber-shops.created','New Barber Shops',NULL,'Push notification when a new record is created in BarberShops.',1,'2026-09-16 12:29:48','2026-09-21 13:14:28'),(2,'plans.created','New Plans',NULL,'Push notification when a new record is created in Plans.',1,'2026-09-16 12:30:04','2026-09-21 13:57:47'),(3,'subscriptions.created','New Subscriptions',NULL,'Push notification when a new record is created in Subscriptions.',1,'2026-09-16 12:30:16','2026-09-21 13:57:47'),(4,'barbers.created','New Barbers',NULL,'Push notification when a new record is created in Barbers.',1,'2026-09-16 12:30:27','2026-09-23 12:42:17'),(5,'services.created','New Services',NULL,'Push notification when a new record is created in Services.',1,'2026-09-16 12:30:37','2026-09-21 13:57:47'),(6,'working-hours.created','New WorkingHour',NULL,'Push notification when a new record is created in WorkingHours.',0,'2026-09-16 12:30:46','2026-09-22 10:39:24'),(7,'customers.created','New Customers',NULL,'Push notification when a new record is created in Customers.',1,'2026-09-16 12:30:57','2026-09-21 13:57:47'),(8,'notification-channels.created','New Notification Channels',NULL,'Push notification when a new record is created in NotificationChannels.',1,'2026-09-16 12:31:10','2026-09-21 13:57:47'),(9,'event-settings.created','New Event Settings',NULL,'Push notification when a new record is created in EventSettings.',1,'2026-09-16 12:31:16','2026-09-21 13:57:47'),(10,'message-templates.created','New Message Templates',NULL,'Push notification when a new record is created in MessageTemplates.',1,'2026-09-16 12:31:23','2026-09-21 13:57:47'),(11,'bookings.created','New Bookings',NULL,'Push notification when a new record is created in Bookings.',1,'2026-09-16 12:31:31','2026-09-21 13:57:47'),(12,'payments.created','New Payments',NULL,'Push notification when a new record is created in Payments.',1,'2026-09-16 12:31:46','2026-09-21 13:57:47'),(13,'message-queues.created','New Message Queues',NULL,'Push notification when a new record is created in MessageQueues.',1,'2026-09-16 12:31:53','2026-09-28 10:47:32'),(14,'message-logs.created','New Message Logs',NULL,'Push notification when a new record is created in MessageLogs.',1,'2026-09-16 12:32:03','2026-09-21 13:57:47'),(15,'device-tokens.created','New Device Tokens',NULL,'Push notification when a new record is created in DeviceTokens.',1,'2026-09-16 12:32:11','2026-09-21 13:57:47'),(16,'reviews.created','New Reviews',NULL,'Push notification when a new record is created in Reviews.',1,'2026-09-16 12:32:19','2026-09-21 13:57:47'),(17,'barber-shops.updated','Barber Shops Updated',NULL,'Push notification when a record is updated in BarberShops.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(18,'barber-shops.deleted','Barber Shops Deleted',NULL,'Push notification when a record is deleted from BarberShops.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(19,'plans.updated','Plans Updated',NULL,'Push notification when a record is updated in Plans.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(20,'plans.deleted','Plans Deleted',NULL,'Push notification when a record is deleted from Plans.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(21,'subscriptions.updated','Subscriptions Updated',NULL,'Push notification when a record is updated in Subscriptions.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(22,'subscriptions.deleted','Subscriptions Deleted',NULL,'Push notification when a record is deleted from Subscriptions.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(23,'barbers.updated','Barbers Updated',NULL,'Push notification when a record is updated in Barbers.',1,'2026-09-16 12:48:12','2026-09-23 12:42:17'),(24,'barbers.deleted','Barbers Deleted',NULL,'Push notification when a record is deleted from Barbers.',1,'2026-09-16 12:48:12','2026-09-23 12:42:17'),(25,'services.updated','Services Updated',NULL,'Push notification when a record is updated in Services.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(26,'services.deleted','Services Deleted',NULL,'Push notification when a record is deleted from Services.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(27,'working-hours.updated','Working Hours Updated',NULL,'Push notification when a record is updated in WorkingHours.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(28,'working-hours.deleted','Working Hours Deleted',NULL,'Push notification when a record is deleted from WorkingHours.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(29,'customers.updated','Customers Updated',NULL,'Push notification when a record is updated in Customers.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(30,'customers.deleted','Customers Deleted',NULL,'Push notification when a record is deleted from Customers.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(31,'notification-channels.updated','Notification Channels Updated',NULL,'Push notification when a record is updated in NotificationChannels.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(32,'notification-channels.deleted','Notification Channels Deleted',NULL,'Push notification when a record is deleted from NotificationChannels.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(33,'event-settings.updated','Event Settings Updated',NULL,'Push notification when a record is updated in EventSettings.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(34,'event-settings.deleted','Event Settings Deleted',NULL,'Push notification when a record is deleted from EventSettings.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(35,'message-templates.updated','Message Templates Updated',NULL,'Push notification when a record is updated in MessageTemplates.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(36,'message-templates.deleted','Message Templates Deleted',NULL,'Push notification when a record is deleted from MessageTemplates.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(37,'bookings.updated','Bookings Updated',NULL,'Push notification when a record is updated in Bookings.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(38,'bookings.deleted','Bookings Deleted',NULL,'Push notification when a record is deleted from Bookings.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(39,'payments.updated','Payments Updated',NULL,'Push notification when a record is updated in Payments.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(40,'payments.deleted','Payments Deleted',NULL,'Push notification when a record is deleted from Payments.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(41,'message-queues.updated','Message Queues Updated',NULL,'Push notification when a record is updated in MessageQueues.',1,'2026-09-16 12:48:12','2026-09-28 10:47:32'),(42,'message-queues.deleted','Message Queues Deleted',NULL,'Push notification when a record is deleted from MessageQueues.',1,'2026-09-16 12:48:12','2026-09-28 10:47:32'),(43,'message-logs.updated','Message Logs Updated',NULL,'Push notification when a record is updated in MessageLogs.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(44,'message-logs.deleted','Message Logs Deleted',NULL,'Push notification when a record is deleted from MessageLogs.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(45,'device-tokens.updated','Device Tokens Updated',NULL,'Push notification when a record is updated in DeviceTokens.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(46,'device-tokens.deleted','Device Tokens Deleted',NULL,'Push notification when a record is deleted from DeviceTokens.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(47,'reviews.updated','Reviews Updated',NULL,'Push notification when a record is updated in Reviews.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12'),(48,'reviews.deleted','Reviews Deleted',NULL,'Push notification when a record is deleted from Reviews.',0,'2026-09-16 12:48:12','2026-09-16 12:48:12');
/*!40000 ALTER TABLE `realtime_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `barber_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `booking_id` bigint unsigned NOT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `reviews_barber_id_foreign` (`barber_id`),
  KEY `reviews_customer_id_foreign` (`customer_id`),
  KEY `reviews_booking_id_foreign` (`booking_id`),
  CONSTRAINT `reviews_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`),
  CONSTRAINT `reviews_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES ('6645cdbc-ed2e-494b-acdc-93ab466d1abe','0663f25d-7fc2-4c9f-bd56-3b9622eae87a'),('90afff39-df4c-40dd-8ea8-9f9acdb93fe9','0663f25d-7fc2-4c9f-bd56-3b9622eae87a'),('0c69c66b-d42b-4b17-9728-47ae2de6a9d5','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('15921bcd-5145-414c-a782-faf392071144','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('15da3acb-132e-4002-b760-40359dd25ddb','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('16f24fb6-4789-4c42-92bc-be09cc44e833','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('1f1abbd7-d266-4ea2-b97e-e1c1fab15037','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('2282901c-f6f4-4fd7-b71e-0e452d9fd6bd','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('2f572e05-3bab-494a-91d6-838ec99961e9','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('37bd9069-6c66-46cb-a029-18416d8f27f6','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('396f8e7e-49df-4083-9854-e2e4e747a5d0','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('3a016d37-3b96-4ff8-ae1d-59a2f55f1a05','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('3b54c5b7-6344-46f7-84d2-58148cbb09ac','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('406a62e3-c602-4261-bac2-055b6e9201dd','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('49bde613-adb7-4f69-9811-8b98d3198400','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('505471d0-d49b-46b9-a389-a4ce78095d33','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('50daa1eb-4f34-4d9c-97dd-8cab9d235a57','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('512cd6d3-1ca8-427d-ac72-6cd1cbd01440','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('5828dbba-bc40-41bb-903b-d70881df7508','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('59135fd8-cb05-498b-b81b-aa6d91c98b9f','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('652d3931-7a39-43e3-9e5d-bd6e2b5fc860','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('73fcca2a-cba7-4197-a5f3-6a573acff63b','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('764800d6-821d-4973-b58e-859d6afd4b21','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('838ef7b0-b8f1-47ea-8d9c-5e7e4c48d6de','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('8cd5a860-4e5e-4d7b-8484-3fb3fcc39613','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('8f4eed46-9421-43fb-b0c8-b7901a561ba4','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('9401528f-2899-4233-aa09-313e0a6568d4','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('9635db26-5b93-4562-b97c-aec9aa8fce07','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('96a5e70c-c030-4bfd-bec1-6269ed9f3522','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('9e576426-e8e9-4b1e-9e76-f6ca0293e31f','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('a591361c-c655-41fd-a0c0-b8de11a80e12','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('b762b972-77fa-4be5-88ae-b398615addba','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('b86249a8-36de-43c3-acbe-3d70e9a89b5b','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('bdc61e08-fc05-4aa8-9d01-4c6323e0ee9a','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('be18213f-47f7-40d2-9899-36007ce933f3','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('be27ed35-8824-4b93-8bd5-c7b81bc0aac8','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('c14f9bc9-aae8-4e41-99e4-7ec2f2706729','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('c212bedf-bb0a-4939-ab0b-b06df0f00db9','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('c602e3e0-8159-4038-9c56-94e320fffc7e','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('c7b02ad2-cf7a-4b55-bca2-90e06bf6b4ee','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('d2f1c0ec-f266-4fab-b995-56a9beb9906f','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('e7dec957-5bf3-493a-b7c5-0a28f3e0ff45','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('e802983d-0066-45a9-8bb9-f66c994ab68f','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('e8f9277a-b2c5-4325-9a51-0f1a740690bf','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('e9087eb5-e69f-45ef-b094-f5b5b07b435d','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('f9121adc-8dbf-4a93-81cd-3a72643c1b4a','46d5ce5b-5460-4bca-bdf5-27bba4f9858f'),('15921bcd-5145-414c-a782-faf392071144','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('15da3acb-132e-4002-b760-40359dd25ddb','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('16f24fb6-4789-4c42-92bc-be09cc44e833','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('1f1abbd7-d266-4ea2-b97e-e1c1fab15037','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('2282901c-f6f4-4fd7-b71e-0e452d9fd6bd','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('2f572e05-3bab-494a-91d6-838ec99961e9','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('37bd9069-6c66-46cb-a029-18416d8f27f6','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('396f8e7e-49df-4083-9854-e2e4e747a5d0','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('406a62e3-c602-4261-bac2-055b6e9201dd','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('505471d0-d49b-46b9-a389-a4ce78095d33','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('50daa1eb-4f34-4d9c-97dd-8cab9d235a57','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('59135fd8-cb05-498b-b81b-aa6d91c98b9f','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('73fcca2a-cba7-4197-a5f3-6a573acff63b','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('764800d6-821d-4973-b58e-859d6afd4b21','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('838ef7b0-b8f1-47ea-8d9c-5e7e4c48d6de','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('8cd5a860-4e5e-4d7b-8484-3fb3fcc39613','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('8f4eed46-9421-43fb-b0c8-b7901a561ba4','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('9401528f-2899-4233-aa09-313e0a6568d4','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('96a5e70c-c030-4bfd-bec1-6269ed9f3522','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('a591361c-c655-41fd-a0c0-b8de11a80e12','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('b762b972-77fa-4be5-88ae-b398615addba','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('bdc61e08-fc05-4aa8-9d01-4c6323e0ee9a','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('be18213f-47f7-40d2-9899-36007ce933f3','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('be27ed35-8824-4b93-8bd5-c7b81bc0aac8','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('c14f9bc9-aae8-4e41-99e4-7ec2f2706729','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('c212bedf-bb0a-4939-ab0b-b06df0f00db9','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('c602e3e0-8159-4038-9c56-94e320fffc7e','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('c7b02ad2-cf7a-4b55-bca2-90e06bf6b4ee','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('d2f1c0ec-f266-4fab-b995-56a9beb9906f','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('e802983d-0066-45a9-8bb9-f66c994ab68f','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('e8f9277a-b2c5-4325-9a51-0f1a740690bf','98c2b068-a59c-49ec-8d8e-fdf87e816ede'),('f9121adc-8dbf-4a93-81cd-3a72643c1b4a','98c2b068-a59c-49ec-8d8e-fdf87e816ede');
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barber_shop_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_team_name_guard_unique` (`barber_shop_id`,`name`,`guard_name`),
  KEY `roles_barber_shop_id_index` (`barber_shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES ('0663f25d-7fc2-4c9f-bd56-3b9622eae87a',NULL,'berber','Berber','web','2026-09-17 14:08:55','2026-09-17 14:08:55'),('46d5ce5b-5460-4bca-bdf5-27bba4f9858f',NULL,'pronar_i_biznesit_/_sallonit','Pronar i Biznesit / Sallonit','web','2026-09-28 07:45:27','2026-09-28 08:35:56'),('98c2b068-a59c-49ec-8d8e-fdf87e816ede',NULL,'owner','Owner','web','2026-09-17 14:03:12','2026-09-18 07:18:42'),('cf6877ac-6e54-48f0-b90e-856dc6b371ea',NULL,'admin','admin','web','2026-09-16 13:48:54','2026-09-16 13:48:54');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(12,2) NOT NULL,
  `duration_minutes` int NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_barber_shop_id_foreign` (`barber_shop_id`),
  CONSTRAINT `services_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,15,'Prerje flokësh','Prerje standarde.',10.00,30,'flokë',1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(2,15,'Rregullim mjekre','Rregullim + trim.',7.00,20,'mjekër',1,'2026-09-21 13:57:50','2026-09-21 13:57:50'),(3,16,'Rrojre qethje','21',800.00,45,'',1,'2026-09-22 11:45:58','2026-09-22 11:45:58'),(4,16,'Larje dhe Stilim','',500.00,20,'',1,'2026-09-23 12:52:55','2026-09-23 12:52:55'),(5,16,'rroje','',600.00,20,'',1,'2026-09-23 13:32:00','2026-09-24 10:13:53'),(6,16,'Dhenderr Edition','',1000.00,50,'',1,'2026-09-24 13:07:44','2026-09-24 13:07:44'),(7,16,'Larje Koke','',250.00,15,'',1,'2026-09-26 08:34:10','2026-09-26 08:34:10'),(8,17,'Prerje Flokësh Standarde','Prerje moderne, larje dhe stilim me dylli.',1000.00,30,'Flokë',1,'2026-09-28 07:45:28','2026-09-28 07:45:28'),(9,17,'Rregullim & Modelim Mjekre','Rroje tradicionale me brilantë dhe peshqir të ngrohtë.',600.00,20,'Mjekër',1,'2026-09-28 07:45:28','2026-09-28 07:45:28'),(10,18,'Qethje & Modelim Femrash','Prerje sipas formës së fytyrës, larje dhe krehje me furçë.',2000.00,45,'Flokë Femra',1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(11,18,'Lyerje Flokësh + Breshing','Lyerje e plotë me bojëra profesionale pa amoniak.',4000.00,90,'Koloristikë',1,'2026-09-28 07:46:06','2026-09-28 07:46:06'),(12,19,'Manikyr me Xhel & Dizajn','Pastrim kutikulash, trajtim me xhel dhe dizajn të personalizuar.',2500.00,60,'Thonj',1,'2026-09-28 07:46:07','2026-09-28 07:46:07'),(13,19,'Pedikyr Estetik & Spa','Trajtim hidratues me parafinë dhe masazh për këmbët.',3000.00,60,'Pedikyr',1,'2026-09-28 07:46:07','2026-09-28 07:46:07');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('cb6d3c40-a47c-4047-98ac-6fac3919da8c','notify_firebase_Barber','1','2026-09-23 12:42:17','2026-09-23 12:42:17'),('e94aa6f9-6140-4055-b6d8-bc4f47df1259','notify_firebase_MessageQueue','1','2026-09-28 10:47:32','2026-09-28 10:47:32');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_shop_id` bigint unsigned NOT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` enum('trial','active','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `auto_renew` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_barber_shop_id_foreign` (`barber_shop_id`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  CONSTRAINT `subscriptions_barber_shop_id_foreign` FOREIGN KEY (`barber_shop_id`) REFERENCES `barber_shops` (`id`),
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,15,2,'2026-09-21 15:57:49','2026-10-28 11:48:52','active',1,'2026-09-21 13:57:49','2026-09-28 09:48:52'),(2,16,1,'2026-09-21 15:57:49','2026-10-28 11:48:52','active',1,'2026-09-21 13:57:49','2026-09-28 09:48:52'),(3,17,4,'2026-09-28 09:45:27','2026-10-28 11:48:52','active',1,'2026-09-28 07:45:27','2026-09-28 09:48:52'),(4,18,4,'2026-09-28 09:46:00','2026-10-28 11:48:00','active',1,'2026-09-28 07:46:06','2026-09-28 10:06:37'),(5,19,4,'2026-09-28 09:46:07','2026-10-28 11:48:52','active',1,'2026-09-28 07:46:07','2026-09-28 09:48:52');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_office_login_only` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_logged_in_at` timestamp NULL DEFAULT NULL,
  `two_fa_active` tinyint(1) NOT NULL DEFAULT '0',
  `two_fa_secret_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invited_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invited_at` timestamp NULL DEFAULT NULL,
  `joined_at` timestamp NULL DEFAULT NULL,
  `invite_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `barber_shop_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('000b74d7-adb2-46ab-b64f-e8296997b52f','Mario Berberi','mario-berberi','staff.barber@test.com','$2y$12$6HYq2Feh0qFCw.WIIwgAgu0QANN2mUzq3rawIuF3a9LshVZYgDYwi',NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-28 07:45:28','2026-09-28 07:45:28',NULL,17),('04732a98-8b73-4005-9c1f-55db6d3ab283','Ardit Berberi','ardit-berberi','barber1@test.com','$2y$12$X1GnSKJIiWyH0WKiP2ykM.IzAcpVN0tLonfSobf4GhKaR24DTSu6a',NULL,0,1,'2026-09-16 12:32:28','2026-09-21 09:02:14',0,NULL,NULL,NULL,'2026-09-16 12:32:28',NULL,NULL,NULL,'2026-09-16 12:32:31','2026-09-26 09:26:43',NULL,16),('1d1df8c6-0d8a-4779-9c8c-acc2247789dc','Sonia Nails','sonia-nails','owner.nails@test.com','$2y$12$dUPMpltnAWi27iuPbW.IBe7cQI07MW0mnBm1/O1iysDwFyD6vm.8.',NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-28 07:46:07','2026-09-28 07:46:07',NULL,19),('283b5e00-4b0e-4fab-aa0a-894e51ccd8aa','Elena Parukieria','elena-parukieria','owner.salon@test.com','$2y$12$ezXra1cvutJTxkvWzzYd/OwgVMB5DXOuHRWkF920nP9CyCYx6oaBK',NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-28 07:46:06','2026-09-28 07:46:06',NULL,18),('4b3d5dc3-8544-4aca-bd09-eab94e01f20c','Elton Berberi','elton-berberi','barber2@test.com','$2y$12$X1GnSKJIiWyH0WKiP2ykM.IzAcpVN0tLonfSobf4GhKaR24DTSu6a',NULL,0,1,'2026-09-16 12:32:28','2026-09-17 14:20:10',0,NULL,NULL,NULL,'2026-09-16 12:32:28',NULL,NULL,NULL,'2026-09-16 12:56:08','2026-09-18 15:13:40',NULL,8),('76edf897-20fa-4c3a-b757-6d8bc980333c','Demo Admin E4ProTech','demo-admin-e4protech','demo@e4protech.com','$2y$12$X1GnSKJIiWyH0WKiP2ykM.IzAcpVN0tLonfSobf4GhKaR24DTSu6a',NULL,1,1,'2026-09-16 12:32:28','2026-09-18 14:48:25',0,NULL,NULL,NULL,'2026-09-16 12:32:28',NULL,NULL,NULL,'2026-09-16 12:32:28','2026-09-26 08:39:39',NULL,15),('7ca3ce60-df50-41db-88f3-4983ab29f0ef','Arian Berberi','arian-berberi','owner.barber@test.com','$2y$12$d2KrGHO/.JXhu4I5qCSieeI/Ace5UZrVFI8H2PiBgDX0QAL/G6qj2',NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-28 07:45:27','2026-09-28 07:45:27',NULL,17),('a9be7713-4099-49e7-9e6e-3b5ac1d87628','Germane Velasquez','germane-velasquez','fucytybefa@mailinator.com',NULL,'users/a9be7713-4099-49e7-9e6e-3b5ac1d87628.png',0,0,NULL,NULL,0,NULL,'4b3d5dc3-8544-4aca-bd09-eab94e01f20c','2026-09-17 14:09:33',NULL,'hkgqm5ihDGWP04LD9efcH6paoUtz9eXD',NULL,NULL,'2026-09-17 14:09:33','2026-09-17 14:09:33',NULL,NULL),('c8c0cb93-57e7-4073-a4d1-76b07d80cdcc','Ledia NailTech','ledia-nailtech','staff.nails@test.com','$2y$12$gG2BHBa2laivzefsznBo7OaTHKe6ZxA79r2yTkPYQM5Pxdet7uUBa',NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-28 07:46:07','2026-09-28 07:46:07',NULL,19),('dbb77630-40f2-45b4-9427-5cd634868ec2','Klara Stylist','klara-stylist','staff.salon@test.com','$2y$12$uW4nR50KnI0ARMXBbG9W5eB5fMv39oX960pHgv1ubQ1nQHWRgI2qO',NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-28 07:46:06','2026-09-28 07:46:06',NULL,18);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `working_hours`
--

DROP TABLE IF EXISTS `working_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `working_hours` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barber_id` bigint unsigned NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `lunch_start` time DEFAULT NULL,
  `lunch_end` time DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `working_hours_barber_id_foreign` (`barber_id`),
  CONSTRAINT `working_hours_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `working_hours`
--

LOCK TABLES `working_hours` WRITE;
/*!40000 ALTER TABLE `working_hours` DISABLE KEYS */;
INSERT INTO `working_hours` VALUES (1,3,'Monday','08:00:00','20:00:00','13:00:00','13:30:00',0,'2026-09-22 10:47:52','2026-09-24 14:34:06'),(2,3,'Tuesday','08:00:00','20:00:00','16:00:00','18:00:00',0,'2026-09-22 10:54:40','2026-09-24 14:34:06'),(3,3,'Wednesday','08:00:00','20:00:00','13:00:00','13:30:00',0,'2026-09-22 10:55:05','2026-09-24 14:34:06'),(4,3,'Thursday','08:00:00','20:00:00','13:00:00','13:30:00',0,'2026-09-22 10:55:44','2026-09-24 14:34:06'),(5,3,'Friday','08:00:00','20:00:00','13:00:00','13:30:00',0,'2026-09-22 10:56:05','2026-09-24 14:34:06'),(6,3,'Saturday','08:00:00','20:00:00','13:00:00','13:30:00',0,'2026-09-22 10:56:29','2026-09-24 14:34:06'),(7,3,'Sunday',NULL,NULL,NULL,NULL,1,'2026-09-22 11:03:14','2026-09-22 11:03:14'),(9,1,'Monday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:13:53','2026-09-22 11:18:02'),(10,1,'Tuesday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:13:53','2026-09-22 11:18:02'),(11,1,'Wednesday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:13:54','2026-09-22 11:18:02'),(12,1,'Thursday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:13:54','2026-09-22 11:18:02'),(13,1,'Friday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:13:54','2026-09-22 11:18:02'),(14,1,'Saturday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:13:54','2026-09-22 11:18:02'),(15,1,'Sunday',NULL,NULL,NULL,NULL,1,'2026-09-22 11:13:54','2026-09-22 11:13:54'),(16,2,'Monday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:17:30','2026-09-22 11:19:02'),(17,2,'Tuesday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:17:30','2026-09-22 11:19:02'),(18,2,'Wednesday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:17:30','2026-09-22 11:19:02'),(19,2,'Thursday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:17:30','2026-09-22 11:19:02'),(20,2,'Friday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:17:30','2026-09-22 11:19:02'),(21,2,'Saturday','08:00:00','20:00:00',NULL,NULL,0,'2026-09-22 11:17:30','2026-09-22 11:19:02'),(22,2,'Sunday',NULL,NULL,NULL,NULL,1,'2026-09-22 11:17:30','2026-09-22 11:17:30'),(23,5,'Monday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:05','2026-09-28 08:59:11'),(24,5,'Tuesday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:05','2026-09-28 08:59:11'),(25,5,'Wednesday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:05','2026-09-28 08:59:11'),(26,5,'Thursday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:05','2026-09-28 08:59:11'),(27,5,'Friday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:05','2026-09-28 08:59:12'),(28,5,'Saturday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:05','2026-09-28 08:59:12'),(29,6,'Monday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:06','2026-09-28 08:59:12'),(30,6,'Tuesday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:06','2026-09-28 08:59:12'),(31,6,'Wednesday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:06','2026-09-28 08:59:12'),(32,6,'Thursday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:06','2026-09-28 08:59:12'),(33,6,'Friday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:06','2026-09-28 08:59:12'),(34,6,'Saturday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:06','2026-09-28 08:59:12'),(35,7,'Monday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:07','2026-09-28 08:59:13'),(36,7,'Tuesday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:07','2026-09-28 08:59:13'),(37,7,'Wednesday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:07','2026-09-28 08:59:13'),(38,7,'Thursday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:07','2026-09-28 08:59:13'),(39,7,'Friday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:07','2026-09-28 08:59:13'),(40,7,'Saturday','09:00:00','19:00:00','13:00:00','14:00:00',0,'2026-09-28 07:46:07','2026-09-28 08:59:13');
/*!40000 ALTER TABLE `working_hours` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-28 18:06:54
