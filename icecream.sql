/*
 Navicat Premium Dump SQL

 Source Server         : Wamp
 Source Server Type    : MySQL
 Source Server Version : 90100 (9.1.0)
 Source Host           : localhost:3306
 Source Schema         : icecream

 Target Server Type    : MySQL
 Target Server Version : 90100 (9.1.0)
 File Encoding         : 65001

 Date: 27/01/2026 14:23:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for vc_activity_log
-- ----------------------------
DROP TABLE IF EXISTS `vc_activity_log`;
CREATE TABLE `vc_activity_log`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `event` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `subject_id` bigint UNSIGNED NULL DEFAULT NULL,
  `causer_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `causer_id` bigint UNSIGNED NULL DEFAULT NULL,
  `properties` json NULL,
  `batch_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `subject`(`subject_type`, `subject_id`) USING BTREE,
  INDEX `causer`(`causer_type`, `causer_id`) USING BTREE,
  INDEX `vc_activity_log_log_name_index`(`log_name`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 137 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_activity_log
-- ----------------------------
INSERT INTO `vc_activity_log` VALUES (133, 'default', 'Permission has been created', 'App\\Models\\Permission', 'created', 35, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"create_sales\", \"guard_name\": \"web\"}}', NULL, '2026-01-26 09:52:26', '2026-01-26 09:52:26');
INSERT INTO `vc_activity_log` VALUES (134, 'default', 'Permission has been created', 'App\\Models\\Permission', 'created', 36, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"view_reports\", \"guard_name\": \"web\"}}', NULL, '2026-01-26 09:52:32', '2026-01-26 09:52:32');
INSERT INTO `vc_activity_log` VALUES (135, 'default', 'Permission has been created', 'App\\Models\\Permission', 'created', 37, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"view_profile\", \"guard_name\": \"web\"}}', NULL, '2026-01-26 09:52:37', '2026-01-26 09:52:37');
INSERT INTO `vc_activity_log` VALUES (136, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', NULL, '2026-01-26 16:21:20', '2026-01-26 16:21:20');
INSERT INTO `vc_activity_log` VALUES (118, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', NULL, '2026-01-19 09:16:15', '2026-01-19 09:16:15');
INSERT INTO `vc_activity_log` VALUES (119, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', NULL, '2026-01-19 11:40:31', '2026-01-19 11:40:31');
INSERT INTO `vc_activity_log` VALUES (120, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', NULL, '2026-01-20 02:26:06', '2026-01-20 02:26:06');
INSERT INTO `vc_activity_log` VALUES (121, 'default', 'updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"vc\"}, \"attributes\": {\"name\": \"vcស\"}}', NULL, '2026-01-20 04:26:15', '2026-01-20 04:26:15');
INSERT INTO `vc_activity_log` VALUES (122, 'default', 'updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"vcស\"}, \"attributes\": {\"name\": \"vc\"}}', NULL, '2026-01-20 04:26:19', '2026-01-20 04:26:19');
INSERT INTO `vc_activity_log` VALUES (123, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', NULL, '2026-01-20 04:33:46', '2026-01-20 04:33:46');
INSERT INTO `vc_activity_log` VALUES (124, 'default', 'updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"vc\"}, \"attributes\": {\"name\": \"vcddd\"}}', NULL, '2026-01-20 04:36:51', '2026-01-20 04:36:51');
INSERT INTO `vc_activity_log` VALUES (125, 'default', 'updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"vcddd\"}, \"attributes\": {\"name\": \"vc\"}}', NULL, '2026-01-20 04:36:57', '2026-01-20 04:36:57');
INSERT INTO `vc_activity_log` VALUES (126, 'default', 'updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '{\"old\": {\"email\": \"kuytangkoan@gmail.com00\"}, \"attributes\": {\"email\": \"kuytangkoan@gmail.com\"}}', NULL, '2026-01-20 04:37:00', '2026-01-20 04:37:00');
INSERT INTO `vc_activity_log` VALUES (127, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', NULL, '2026-01-20 07:26:15', '2026-01-20 07:26:15');
INSERT INTO `vc_activity_log` VALUES (128, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', NULL, '2026-01-21 06:23:15', '2026-01-21 06:23:15');
INSERT INTO `vc_activity_log` VALUES (129, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36\"}', NULL, '2026-01-26 07:35:02', '2026-01-26 07:35:02');
INSERT INTO `vc_activity_log` VALUES (130, 'default', 'logged in', NULL, NULL, NULL, 'App\\Models\\User', 8, '{\"ip\": \"127.0.0.1\", \"browser\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', NULL, '2026-01-26 09:51:31', '2026-01-26 09:51:31');
INSERT INTO `vc_activity_log` VALUES (131, 'default', 'Permission has been created', 'App\\Models\\Permission', 'created', 33, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"view_dashboard\", \"guard_name\": \"web\"}}', NULL, '2026-01-26 09:52:14', '2026-01-26 09:52:14');
INSERT INTO `vc_activity_log` VALUES (132, 'default', 'Permission has been created', 'App\\Models\\Permission', 'created', 34, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"view_products\", \"guard_name\": \"web\"}}', NULL, '2026-01-26 09:52:19', '2026-01-26 09:52:19');
INSERT INTO `vc_activity_log` VALUES (116, 'default', 'Permission has been updated', 'App\\Models\\Permission', 'updated', 32, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"d\"}, \"attributes\": {\"name\": \"ds\"}}', NULL, '2026-01-19 08:37:33', '2026-01-19 08:37:33');
INSERT INTO `vc_activity_log` VALUES (114, 'default', 'Role has been deleted', 'App\\Models\\Role', 'deleted', 12, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"ដ\", \"level\": 10, \"guard_name\": \"web\"}}', NULL, '2026-01-19 08:29:05', '2026-01-19 08:29:05');
INSERT INTO `vc_activity_log` VALUES (115, 'default', 'Permission has been created', 'App\\Models\\Permission', 'created', 32, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"d\", \"guard_name\": \"web\"}}', NULL, '2026-01-19 08:37:30', '2026-01-19 08:37:30');
INSERT INTO `vc_activity_log` VALUES (113, 'default', 'Role has been created', 'App\\Models\\Role', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"ដ\", \"level\": 10, \"guard_name\": \"web\"}}', NULL, '2026-01-19 08:29:02', '2026-01-19 08:29:02');
INSERT INTO `vc_activity_log` VALUES (105, 'default', 'updated', 'App\\Models\\User', 'updated', 8, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"tg\"}, \"attributes\": {\"name\": \"tgស\"}}', NULL, '2026-01-19 07:58:00', '2026-01-19 07:58:00');
INSERT INTO `vc_activity_log` VALUES (106, 'default', 'updated user role', 'App\\Models\\User', NULL, 8, 'App\\Models\\User', 1, '{\"role\": \"Admin\"}', NULL, '2026-01-19 07:58:00', '2026-01-19 07:58:00');
INSERT INTO `vc_activity_log` VALUES (107, 'default', 'updated', 'App\\Models\\User', 'updated', 8, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"tgស\"}, \"attributes\": {\"name\": \"tg\"}}', NULL, '2026-01-19 07:58:03', '2026-01-19 07:58:03');
INSERT INTO `vc_activity_log` VALUES (108, 'default', 'updated user role', 'App\\Models\\User', NULL, 8, 'App\\Models\\User', 1, '{\"role\": \"Admin\"}', NULL, '2026-01-19 07:58:03', '2026-01-19 07:58:03');
INSERT INTO `vc_activity_log` VALUES (109, 'default', 'created', 'App\\Models\\User', 'created', 16, 'App\\Models\\User', 1, '{\"attributes\": {\"name\": \"T\", \"role\": null, \"email\": \"t@gmail.com\"}}', NULL, '2026-01-19 07:58:17', '2026-01-19 07:58:17');
INSERT INTO `vc_activity_log` VALUES (110, 'default', 'create user role', 'App\\Models\\User', NULL, 16, 'App\\Models\\User', 1, '{\"role\": \"Admin\"}', NULL, '2026-01-19 07:58:17', '2026-01-19 07:58:17');
INSERT INTO `vc_activity_log` VALUES (111, 'default', 'Role has been updated', 'App\\Models\\Role', 'updated', 5, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"Accounting\"}, \"attributes\": {\"name\": \"Accountings\"}}', NULL, '2026-01-19 08:17:22', '2026-01-19 08:17:22');
INSERT INTO `vc_activity_log` VALUES (112, 'default', 'Role has been updated', 'App\\Models\\Role', 'updated', 5, 'App\\Models\\User', 1, '{\"old\": {\"name\": \"Accountings\"}, \"attributes\": {\"name\": \"Accounting\"}}', NULL, '2026-01-19 08:17:27', '2026-01-19 08:17:27');

-- ----------------------------
-- Table structure for vc_cache
-- ----------------------------
DROP TABLE IF EXISTS `vc_cache`;
CREATE TABLE `vc_cache`  (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_cache
-- ----------------------------
INSERT INTO `vc_cache` VALUES ('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1769444527;', 1769444527);
INSERT INTO `vc_cache` VALUES ('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1769444527);
INSERT INTO `vc_cache` VALUES ('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:5:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";s:1:\"j\";s:5:\"level\";}s:11:\"permissions\";a:30:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:9:\"user-list\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:11:\"user-create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:9:\"user-edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"user-delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:9:\"role-list\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"role-create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:9:\"role-edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"role-delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"product-list\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:14:\"product-create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"product-edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:14:\"product-delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:15:\"permission-list\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:17:\"permission-create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:15:\"permission-edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:17:\"permission-delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:11:\"role-assign\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:17;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:11:\"theme-color\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:9:\"rule-list\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:9:\"rule.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:13:\"activity-list\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:15:\"activity-delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:17:\"setting-shop_info\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:23;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:11:\"config-list\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:11:\"config-edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:25;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:14:\"view_dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:5;}}i:26;a:3:{s:1:\"a\";i:34;s:1:\"b\";s:13:\"view_products\";s:1:\"c\";s:3:\"web\";}i:27;a:3:{s:1:\"a\";i:35;s:1:\"b\";s:12:\"create_sales\";s:1:\"c\";s:3:\"web\";}i:28;a:3:{s:1:\"a\";i:36;s:1:\"b\";s:12:\"view_reports\";s:1:\"c\";s:3:\"web\";}i:29;a:3:{s:1:\"a\";i:37;s:1:\"b\";s:12:\"view_profile\";s:1:\"c\";s:3:\"web\";}}s:5:\"roles\";a:3:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"Super Admin\";s:1:\"j\";i:99;s:1:\"c\";s:3:\"web\";}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"Admin\";s:1:\"j\";i:60;s:1:\"c\";s:3:\"web\";}i:2;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:10:\"Accounting\";s:1:\"j\";i:52;s:1:\"c\";s:3:\"web\";}}}', 1769507592);

-- ----------------------------
-- Table structure for vc_cache_locks
-- ----------------------------
DROP TABLE IF EXISTS `vc_cache_locks`;
CREATE TABLE `vc_cache_locks`  (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_cache_locks
-- ----------------------------

-- ----------------------------
-- Table structure for vc_configs
-- ----------------------------
DROP TABLE IF EXISTS `vc_configs`;
CREATE TABLE `vc_configs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `tip` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `vc_configs_name_unique`(`name`) USING BTREE,
  INDEX `vc_configs_group_index`(`group`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_configs
-- ----------------------------
INSERT INTO `vc_configs` VALUES (1, 'site_name', 'Basic', 'Site Name', 'string', 'Ice Cream Shop', NULL, NULL, 1, '2025-12-05 03:13:13', '2025-12-05 07:13:15');
INSERT INTO `vc_configs` VALUES (2, 'activity_log_retention', 'System', 'Log Retention (Days)', 'number', '0', NULL, 'Auto delete logs older than X days', 1, '2025-12-05 03:13:13', '2025-12-05 07:13:15');

-- ----------------------------
-- Table structure for vc_failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `vc_failed_jobs`;
CREATE TABLE `vc_failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `vc_failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for vc_job_batches
-- ----------------------------
DROP TABLE IF EXISTS `vc_job_batches`;
CREATE TABLE `vc_job_batches`  (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cancelled_at` int NULL DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_job_batches
-- ----------------------------

-- ----------------------------
-- Table structure for vc_jobs
-- ----------------------------
DROP TABLE IF EXISTS `vc_jobs`;
CREATE TABLE `vc_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `vc_jobs_queue_index`(`queue`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for vc_migrations
-- ----------------------------
DROP TABLE IF EXISTS `vc_migrations`;
CREATE TABLE `vc_migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_migrations
-- ----------------------------
INSERT INTO `vc_migrations` VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `vc_migrations` VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `vc_migrations` VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `vc_migrations` VALUES (4, '2025_11_30_105243_add_theme_settings_to_users_table', 2);
INSERT INTO `vc_migrations` VALUES (5, '2025_12_01_112225_create_permission_tables', 3);
INSERT INTO `vc_migrations` VALUES (6, '2025_12_02_064027_add_sidebar_style_to_vc_themes_table', 4);
INSERT INTO `vc_migrations` VALUES (7, '2025_12_02_064952_add_sidebar_style_to_themes_table', 5);
INSERT INTO `vc_migrations` VALUES (8, '2025_12_02_185030_create_role_assignable_permissions_table', 6);
INSERT INTO `vc_migrations` VALUES (9, '2025_12_03_133300_add_level_to_roles_table', 7);
INSERT INTO `vc_migrations` VALUES (10, '2025_12_04_041038_create_activity_log_table', 8);
INSERT INTO `vc_migrations` VALUES (11, '2025_12_04_041039_add_event_column_to_activity_log_table', 8);
INSERT INTO `vc_migrations` VALUES (12, '2025_12_04_041040_add_batch_uuid_column_to_activity_log_table', 8);
INSERT INTO `vc_migrations` VALUES (14, '2025_12_04_094718_create_settings_table', 9);
INSERT INTO `vc_migrations` VALUES (15, '2025_12_05_030934_create_configs_table', 10);
INSERT INTO `vc_migrations` VALUES (16, '2026_01_12_085046_create_shop_infos_table', 11);

-- ----------------------------
-- Table structure for vc_model_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `vc_model_has_permissions`;
CREATE TABLE `vc_model_has_permissions`  (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_permissions_model_id_model_type_index`(`model_id`, `model_type`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_model_has_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for vc_model_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `vc_model_has_roles`;
CREATE TABLE `vc_model_has_roles`  (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_roles_model_id_model_type_index`(`model_id`, `model_type`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_model_has_roles
-- ----------------------------
INSERT INTO `vc_model_has_roles` VALUES (1, 'App\\Models\\User', 1);
INSERT INTO `vc_model_has_roles` VALUES (2, 'App\\Models\\User', 8);
INSERT INTO `vc_model_has_roles` VALUES (2, 'App\\Models\\User', 16);

-- ----------------------------
-- Table structure for vc_password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `vc_password_reset_tokens`;
CREATE TABLE `vc_password_reset_tokens`  (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for vc_permissions
-- ----------------------------
DROP TABLE IF EXISTS `vc_permissions`;
CREATE TABLE `vc_permissions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `vc_permissions_name_guard_name_unique`(`name`, `guard_name`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 38 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_permissions
-- ----------------------------
INSERT INTO `vc_permissions` VALUES (1, 'user-list', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (2, 'user-create', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (3, 'user-edit', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (4, 'user-delete', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (5, 'role-list', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (6, 'role-create', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (7, 'role-edit', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (8, 'role-delete', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (9, 'product-list', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (10, 'product-create', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (11, 'product-edit', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (12, 'product-delete', 'web', '2025-12-01 11:41:28', '2025-12-01 11:41:28');
INSERT INTO `vc_permissions` VALUES (13, 'permission-list', 'web', '2025-12-02 02:31:08', '2025-12-02 02:31:08');
INSERT INTO `vc_permissions` VALUES (14, 'permission-create', 'web', '2025-12-02 02:31:17', '2025-12-02 02:31:17');
INSERT INTO `vc_permissions` VALUES (15, 'permission-edit', 'web', '2025-12-02 02:31:26', '2025-12-02 16:44:15');
INSERT INTO `vc_permissions` VALUES (18, 'permission-delete', 'web', '2025-12-02 16:43:15', '2025-12-02 16:44:14');
INSERT INTO `vc_permissions` VALUES (19, 'role-assign', 'web', '2025-12-02 18:43:03', '2025-12-02 18:43:03');
INSERT INTO `vc_permissions` VALUES (20, 'theme-color', 'web', '2025-12-03 02:57:22', '2025-12-03 02:57:22');
INSERT INTO `vc_permissions` VALUES (21, 'rule-list', 'web', '2025-12-04 02:46:58', '2025-12-04 02:46:58');
INSERT INTO `vc_permissions` VALUES (22, 'rule.edit', 'web', '2025-12-04 02:47:08', '2025-12-04 02:47:08');
INSERT INTO `vc_permissions` VALUES (23, 'activity-list', 'web', '2025-12-04 04:36:53', '2025-12-04 04:36:53');
INSERT INTO `vc_permissions` VALUES (24, 'activity-delete', 'web', '2025-12-04 04:36:58', '2025-12-04 04:36:58');
INSERT INTO `vc_permissions` VALUES (29, 'setting-shop_info', 'web', '2026-01-13 04:17:11', '2026-01-15 03:16:55');
INSERT INTO `vc_permissions` VALUES (27, 'config-list', 'web', '2025-12-04 09:04:18', '2025-12-04 09:04:18');
INSERT INTO `vc_permissions` VALUES (28, 'config-edit', 'web', '2025-12-04 09:04:25', '2026-01-13 09:40:07');
INSERT INTO `vc_permissions` VALUES (33, 'view_dashboard', 'web', '2026-01-26 09:52:14', '2026-01-26 09:52:14');
INSERT INTO `vc_permissions` VALUES (34, 'view_products', 'web', '2026-01-26 09:52:19', '2026-01-26 09:52:19');
INSERT INTO `vc_permissions` VALUES (35, 'create_sales', 'web', '2026-01-26 09:52:26', '2026-01-26 09:52:26');
INSERT INTO `vc_permissions` VALUES (36, 'view_reports', 'web', '2026-01-26 09:52:32', '2026-01-26 09:52:32');
INSERT INTO `vc_permissions` VALUES (37, 'view_profile', 'web', '2026-01-26 09:52:37', '2026-01-26 09:52:37');

-- ----------------------------
-- Table structure for vc_role_assignable_permissions
-- ----------------------------
DROP TABLE IF EXISTS `vc_role_assignable_permissions`;
CREATE TABLE `vc_role_assignable_permissions`  (
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`) USING BTREE,
  INDEX `vc_role_assignable_permissions_permission_id_foreign`(`permission_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of vc_role_assignable_permissions
-- ----------------------------
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 1);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 2);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 3);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 4);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 5);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 6);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 7);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 8);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 9);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 10);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 11);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 12);
INSERT INTO `vc_role_assignable_permissions` VALUES (2, 19);

-- ----------------------------
-- Table structure for vc_role_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `vc_role_has_permissions`;
CREATE TABLE `vc_role_has_permissions`  (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`) USING BTREE,
  INDEX `vc_role_has_permissions_role_id_foreign`(`role_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of vc_role_has_permissions
-- ----------------------------
INSERT INTO `vc_role_has_permissions` VALUES (1, 1);
INSERT INTO `vc_role_has_permissions` VALUES (1, 2);
INSERT INTO `vc_role_has_permissions` VALUES (1, 5);
INSERT INTO `vc_role_has_permissions` VALUES (2, 1);
INSERT INTO `vc_role_has_permissions` VALUES (2, 2);
INSERT INTO `vc_role_has_permissions` VALUES (2, 5);
INSERT INTO `vc_role_has_permissions` VALUES (3, 1);
INSERT INTO `vc_role_has_permissions` VALUES (3, 2);
INSERT INTO `vc_role_has_permissions` VALUES (3, 5);
INSERT INTO `vc_role_has_permissions` VALUES (4, 1);
INSERT INTO `vc_role_has_permissions` VALUES (4, 2);
INSERT INTO `vc_role_has_permissions` VALUES (5, 1);
INSERT INTO `vc_role_has_permissions` VALUES (5, 2);
INSERT INTO `vc_role_has_permissions` VALUES (5, 5);
INSERT INTO `vc_role_has_permissions` VALUES (6, 1);
INSERT INTO `vc_role_has_permissions` VALUES (6, 2);
INSERT INTO `vc_role_has_permissions` VALUES (6, 5);
INSERT INTO `vc_role_has_permissions` VALUES (7, 1);
INSERT INTO `vc_role_has_permissions` VALUES (7, 2);
INSERT INTO `vc_role_has_permissions` VALUES (7, 5);
INSERT INTO `vc_role_has_permissions` VALUES (8, 1);
INSERT INTO `vc_role_has_permissions` VALUES (8, 2);
INSERT INTO `vc_role_has_permissions` VALUES (9, 1);
INSERT INTO `vc_role_has_permissions` VALUES (9, 2);
INSERT INTO `vc_role_has_permissions` VALUES (10, 1);
INSERT INTO `vc_role_has_permissions` VALUES (10, 2);
INSERT INTO `vc_role_has_permissions` VALUES (11, 1);
INSERT INTO `vc_role_has_permissions` VALUES (11, 2);
INSERT INTO `vc_role_has_permissions` VALUES (12, 1);
INSERT INTO `vc_role_has_permissions` VALUES (12, 2);
INSERT INTO `vc_role_has_permissions` VALUES (13, 1);
INSERT INTO `vc_role_has_permissions` VALUES (13, 2);
INSERT INTO `vc_role_has_permissions` VALUES (14, 1);
INSERT INTO `vc_role_has_permissions` VALUES (14, 2);
INSERT INTO `vc_role_has_permissions` VALUES (15, 1);
INSERT INTO `vc_role_has_permissions` VALUES (15, 2);
INSERT INTO `vc_role_has_permissions` VALUES (18, 1);
INSERT INTO `vc_role_has_permissions` VALUES (18, 2);
INSERT INTO `vc_role_has_permissions` VALUES (19, 1);
INSERT INTO `vc_role_has_permissions` VALUES (19, 2);
INSERT INTO `vc_role_has_permissions` VALUES (19, 5);
INSERT INTO `vc_role_has_permissions` VALUES (20, 1);
INSERT INTO `vc_role_has_permissions` VALUES (21, 1);
INSERT INTO `vc_role_has_permissions` VALUES (21, 2);
INSERT INTO `vc_role_has_permissions` VALUES (22, 1);
INSERT INTO `vc_role_has_permissions` VALUES (22, 2);
INSERT INTO `vc_role_has_permissions` VALUES (23, 1);
INSERT INTO `vc_role_has_permissions` VALUES (23, 2);
INSERT INTO `vc_role_has_permissions` VALUES (24, 1);
INSERT INTO `vc_role_has_permissions` VALUES (24, 2);
INSERT INTO `vc_role_has_permissions` VALUES (27, 1);
INSERT INTO `vc_role_has_permissions` VALUES (28, 1);
INSERT INTO `vc_role_has_permissions` VALUES (29, 5);
INSERT INTO `vc_role_has_permissions` VALUES (33, 2);
INSERT INTO `vc_role_has_permissions` VALUES (33, 5);

-- ----------------------------
-- Table structure for vc_roles
-- ----------------------------
DROP TABLE IF EXISTS `vc_roles`;
CREATE TABLE `vc_roles`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int NOT NULL DEFAULT 0,
  `guard_name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `vc_roles_name_guard_name_unique`(`name`, `guard_name`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_roles
-- ----------------------------
INSERT INTO `vc_roles` VALUES (1, 'Super Admin', 99, 'web', '2025-12-01 12:06:18', '2025-12-03 16:32:04');
INSERT INTO `vc_roles` VALUES (2, 'Admin', 60, 'web', '2025-12-01 13:56:12', '2026-01-13 09:30:52');
INSERT INTO `vc_roles` VALUES (5, 'Accounting', 52, 'web', '2025-12-01 13:56:26', '2026-01-19 08:17:27');

-- ----------------------------
-- Table structure for vc_sessions
-- ----------------------------
DROP TABLE IF EXISTS `vc_sessions`;
CREATE TABLE `vc_sessions`  (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `vc_sessions_user_id_index`(`user_id`) USING BTREE,
  INDEX `vc_sessions_last_activity_index`(`last_activity`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_sessions
-- ----------------------------
INSERT INTO `vc_sessions` VALUES ('U1HCVZ4LiSLmSh0iRkG6z6YvgCgQ2oLlMOsonFhd', 1, '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/17.5 Mobile/15A5370a Safari/602.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV2E3dERTMVVRaDUzMDdnbGpudmxNeUZYZmtPa05DOUhJU2M5T0xXYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTU6ImFkbWluLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1769444945);

-- ----------------------------
-- Table structure for vc_shop_infos
-- ----------------------------
DROP TABLE IF EXISTS `vc_shop_infos`;
CREATE TABLE `vc_shop_infos`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_en` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `shop_kh` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `description_kh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `phone_number` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `address_kh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `logo` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fav` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note_kh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_shop_infos
-- ----------------------------
INSERT INTO `vc_shop_infos` VALUES (1, 'VC Shop', 'វីស៊ី​ សប', NULL, NULL, '066764217', 'Siem Reap', 'ស្រុកក្រឡាញ់ ខេត្តសៀមរាប', 'uploads/shops/logos/gAXfLYblZN5BnULW4fiMyTLfUPqgTX9e62lkLTU8.webp', 'uploads/shops/favs/1bfY77eogiPv7VLUaxZpyF3i4cFfXRu7lfqu4v09.webp', NULL, 1, '2026-01-12 09:12:34', '2026-01-20 07:57:11');

-- ----------------------------
-- Table structure for vc_themes
-- ----------------------------
DROP TABLE IF EXISTS `vc_themes`;
CREATE TABLE `vc_themes`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `mode` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `sidebar_style` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'style-1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `vc_themes_user_id_foreign`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_themes
-- ----------------------------
INSERT INTO `vc_themes` VALUES (1, 1, 'light', 'style-1', NULL, '2025-12-02 07:23:53');

-- ----------------------------
-- Table structure for vc_users
-- ----------------------------
DROP TABLE IF EXISTS `vc_users`;
CREATE TABLE `vc_users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `theme_settings` json NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `vc_users_email_unique`(`email`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vc_users
-- ----------------------------
INSERT INTO `vc_users` VALUES (1, 'vc', 'avatars/2TEYKt24BL4aSCTV7h5ESm6atFUSEzAqePqcUYn7.jpg', 'kuytangkoan@gmail.com', NULL, '$2y$12$a/TFJveXaMjGVbpexCxRaO9jHDHKybyE.Ef0XIxx9JXE8myr1geI6', NULL, '2025-11-29 12:40:33', '2026-01-20 04:53:28', '{\"dark\": {\"border\": \"#4a4a4a\", \"cardBg\": \"#000000\", \"pageBg\": \"#000000\", \"inputBg\": \"#000000\", \"primary\": \"#5da27e\", \"headerBg\": \"#000000\", \"secondary\": \"#94a3b8\", \"sidebarBg\": \"#000000\", \"inputBorder\": \"#ff0000\", \"primaryText\": \"#ffffff\", \"sidebarText\": \"#f8fafc\", \"borderOpacity\": \"100\", \"cardBgOpacity\": 100, \"pageBgOpacity\": 100, \"inputBgOpacity\": 100, \"primaryOpacity\": 100, \"sidebarHoverBg\": \"#ffffff\", \"headerBgOpacity\": 100, \"secondaryOpacity\": 100, \"sidebarBgOpacity\": \"100\", \"sidebarHoverText\": \"#ffffff\", \"inputBorderOpacity\": \"79\", \"sidebarTextOpacity\": 100, \"sidebarHoverBgOpacity\": 10}, \"light\": {\"border\": \"#e2e8f0\", \"cardBg\": \"#ffffff\", \"pageBg\": \"#f3f4f6\", \"inputBg\": \"#ffffff\", \"primary\": \"#3b82f6\", \"headerBg\": \"#ffffff\", \"secondary\": \"#64748b\", \"sidebarBg\": \"#ffffff\", \"primaryText\": \"#ffffff\", \"sidebarText\": \"#1e293b\", \"borderOpacity\": 100, \"cardBgOpacity\": 100, \"pageBgOpacity\": 100, \"inputBgOpacity\": 100, \"primaryOpacity\": 100, \"sidebarHoverBg\": \"#f1f5f9\", \"headerBgOpacity\": 100, \"secondaryOpacity\": 100, \"sidebarBgOpacity\": 100, \"sidebarHoverText\": \"#0f172a\", \"sidebarTextOpacity\": 100, \"sidebarHoverBgOpacity\": 100}, \"shadow\": true}');
INSERT INTO `vc_users` VALUES (8, 'tg', NULL, 'tg@gmail.com', NULL, '$2y$12$z5XhwIPtV2CmFQKtcjJYPeIihjtPBsNcZf6H9uww.HVjesBCNorva', NULL, '2025-12-02 18:05:52', '2026-01-19 07:58:03', NULL);
INSERT INTO `vc_users` VALUES (16, 'T', NULL, 't@gmail.com', NULL, '$2y$12$vrVcIh0/wzECJ0pEdZOPs.WWM/KL44y7uPWo2IYyBKEpCihlYG1Tm', NULL, '2026-01-19 07:58:17', '2026-01-19 07:58:17', NULL);

SET FOREIGN_KEY_CHECKS = 1;
