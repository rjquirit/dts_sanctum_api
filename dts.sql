/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80032 (8.0.32)
 Source Host           : localhost:3306
 Source Schema         : dts

 Target Server Type    : MySQL
 Target Server Version : 80032 (8.0.32)
 File Encoding         : 65001

 Date: 27/08/2025 15:50:21
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cache_locks
-- ----------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dts_docroutes
-- ----------------------------
DROP TABLE IF EXISTS `dts_docroutes`;
CREATE TABLE `dts_docroutes`  (
  `action_id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `previous_route_id` int NOT NULL,
  `route_fromuser_id` int NOT NULL,
  `route_from` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `route_fromsection_id` int NOT NULL,
  `route_fromsection` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `route_tosection_id` int NOT NULL,
  `route_tosection` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `route_touser_id` int NOT NULL,
  `route_purpose` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `fwd_remarks` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `datetime_forwarded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `datetime_route_accepted` datetime NOT NULL,
  `receivedby_id` int NOT NULL,
  `received_by` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `accepting_remarks` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `actions_datetime` datetime NOT NULL,
  `actions_taken` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `actedby_id` int NOT NULL,
  `acted_by` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `doc_copy` int NOT NULL DEFAULT 0,
  `out_released_to` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `logbook_page` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `route_accomplished` int NOT NULL DEFAULT 0,
  `end_remarks` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `def_reason` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `def_datetime` datetime NOT NULL,
  `duplicate` int NOT NULL DEFAULT 0,
  `del_reason` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `updatedby_id` int NOT NULL,
  `active` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`action_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 275047 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for dts_docs
-- ----------------------------
DROP TABLE IF EXISTS `dts_docs`;
CREATE TABLE `dts_docs`  (
  `doc_id` int NOT NULL AUTO_INCREMENT,
  `doc_tracking` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `track_issuedby_userid` int NOT NULL,
  `doc_type_id` int NOT NULL,
  `tempdocs_id` int NOT NULL,
  `docs_description` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `origin_fname` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `origin_userid` int NOT NULL,
  `origin_school_id` int NOT NULL,
  `origin_school` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `origin_section` int NOT NULL,
  `receiving_section` int NOT NULL,
  `actions_needed` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `datetime_posted` datetime NOT NULL,
  `datetime_accepted` datetime NOT NULL,
  `acceptedby_userid` int NOT NULL,
  `acct_dvnum` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `acct_payee` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `acct_particulars` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `acct_amount` decimal(11, 0) NOT NULL,
  `final_actions_made` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `done` int NOT NULL DEFAULT 0,
  `datetime_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedby_id` int NOT NULL,
  `archive_id` int NOT NULL,
  `active` int NOT NULL,
  `deactivate_reason` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tags` json NULL,
  `additional_receivers` json NULL,
  PRIMARY KEY (`doc_id`) USING BTREE,
  UNIQUE INDEX `doc_tracking`(`doc_tracking` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 118139 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dts_docstype
-- ----------------------------
DROP TABLE IF EXISTS `dts_docstype`;
CREATE TABLE `dts_docstype`  (
  `doctype_id` int NOT NULL AUTO_INCREMENT,
  `doctype_description` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `display_sequence` int NOT NULL,
  `public_display` int NOT NULL DEFAULT 1,
  `with_form` int NOT NULL DEFAULT 0,
  `active` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`doctype_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dts_sections
-- ----------------------------
DROP TABLE IF EXISTS `dts_sections`;
CREATE TABLE `dts_sections`  (
  `section_id` int NOT NULL AUTO_INCREMENT,
  `section_description` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `office_id` int NOT NULL DEFAULT 2,
  `initial_receipt` int NOT NULL DEFAULT 0,
  `public_view` int NOT NULL DEFAULT 0,
  `datetime_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hidden` int NOT NULL DEFAULT 0,
  `active` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`section_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 361 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dts_tempdocs
-- ----------------------------
DROP TABLE IF EXISTS `dts_tempdocs`;
CREATE TABLE `dts_tempdocs`  (
  `tempdoc_id` int NOT NULL AUTO_INCREMENT,
  `temp_doctype_id` int NOT NULL,
  `tempdoc_description` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `school_id` int NOT NULL,
  `school` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `fromsection_id` int NOT NULL DEFAULT 0,
  `submitted_by` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `submitter_id` int NOT NULL DEFAULT 0,
  `receiving_section_id` int NOT NULL DEFAULT 0,
  `actions_needed` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `remarks` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `datetime_posted` datetime NOT NULL,
  `datetime_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedby_id` int NOT NULL,
  `accepted` int NOT NULL DEFAULT 0,
  `active` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`tempdoc_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 127 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dts_users
-- ----------------------------
DROP TABLE IF EXISTS `dts_users`;
CREATE TABLE `dts_users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `position` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'Employee',
  `division_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '0',
  `section_id` int NULL DEFAULT 17,
  `office_nameid` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `user_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `station_id` int NULL DEFAULT NULL,
  `system_admin` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '0',
  `approved` tinyint(1) NULL DEFAULT 0,
  `active` tinyint(1) NULL DEFAULT 1,
  `contactnumber` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `google_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `google_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `google_refresh_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `security_code` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 199 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for error_logs
-- ----------------------------
DROP TABLE IF EXISTS `error_logs`;
CREATE TABLE `error_logs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `stack_trace` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `status_code` int NULL DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'frontend, api, server, etc.',
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `environment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `additional_data` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for grades
-- ----------------------------
DROP TABLE IF EXISTS `grades`;
CREATE TABLE `grades`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quiz_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1004 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for job_batches
-- ----------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cancelled_at` int NULL DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `jobs_queue_index`(`queue` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `personal_access_tokens_token_unique`(`token` ASC) USING BTREE,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type` ASC, `tokenable_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 167 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for schools
-- ----------------------------
DROP TABLE IF EXISTS `schools`;
CREATE TABLE `schools`  (
  `sch_id` int NOT NULL AUTO_INCREMENT,
  `school_code` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `district` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `school_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `office_type` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `sch_type` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `sch_address` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `date_establish` date NULL DEFAULT NULL,
  `sch_head_id` int NULL DEFAULT 0,
  `property_number_prefix` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `active` tinyint NULL DEFAULT 1,
  PRIMARY KEY (`sch_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 600348 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sessions_user_id_index`(`user_id` ASC) USING BTREE,
  INDEX `sessions_last_activity_index`(`last_activity` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tb_student
-- ----------------------------
DROP TABLE IF EXISTS `tb_student`;
CREATE TABLE `tb_student`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `stud_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `course` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for temp_users
-- ----------------------------
DROP TABLE IF EXISTS `temp_users`;
CREATE TABLE `temp_users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `actkey` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `fullname` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_management
-- ----------------------------
DROP TABLE IF EXISTS `user_management`;
CREATE TABLE `user_management`  (
  `uam_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `inv_school_id` int NOT NULL,
  `inv_user` int NOT NULL DEFAULT 0,
  `inv_user_designation` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `inv_admin` int NOT NULL DEFAULT 0,
  `active` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`uam_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `users_id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `contact_number` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `secured_pass` int NOT NULL,
  `first_name` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `middle_name` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `last_name` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `sex` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `designation` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Employee',
  `dts_admin` int NOT NULL DEFAULT 0,
  `dts_section_id` int NOT NULL DEFAULT 17,
  `dts_image_url` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'assets/dist/img/avatar2.png',
  `staff_id` int NOT NULL,
  `station_id` int NOT NULL,
  `school_head` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ict_coordinator` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `property_custodian` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `user_type` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `system_admin` int NOT NULL DEFAULT 0,
  `approved` int NOT NULL DEFAULT 0,
  `active` tinyint NOT NULL DEFAULT 1,
  PRIMARY KEY (`users_id`) USING BTREE,
  UNIQUE INDEX `TIN`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 194 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for web_sessions
-- ----------------------------
DROP TABLE IF EXISTS `web_sessions`;
CREATE TABLE `web_sessions`  (
  `id` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `timestamp` int UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  `active` int NOT NULL,
  INDEX `ci_sessions_timestamp`(`timestamp` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wp_posts
-- ----------------------------
DROP TABLE IF EXISTS `wp_posts`;
CREATE TABLE `wp_posts`  (
  `ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_author` bigint UNSIGNED NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_title` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_excerpt` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `to_ping` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pinged` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_parent` bigint UNSIGNED NOT NULL DEFAULT 0,
  `guid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_order` int NOT NULL DEFAULT 0,
  `post_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_count` bigint NOT NULL DEFAULT 0,
  `url_type` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `local_url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` datetime NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `type_status_date`(`post_type`, `post_status`, `post_date`, `ID`) USING BTREE,
  INDEX `post_parent`(`post_parent`) USING BTREE,
  INDEX `post_author`(`post_author`) USING BTREE,
  INDEX `post_name`(`post_name`(191)) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 11801 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
