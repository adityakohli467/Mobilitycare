<?php
/**
 * Mail Queue - Database table creation script
 * Run once to create the oc_mail_queue table
 * 
 * Usage: php install_mail_queue.php
 */
require_once __DIR__ . '/config.php';

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
if ($db->connect_error) {
    die('DB ERROR: ' . $db->connect_error . PHP_EOL);
}

$sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mail_queue` (
    `mail_queue_id` INT(11) NOT NULL AUTO_INCREMENT,
    `to_email` VARCHAR(255) NOT NULL,
    `from_email` VARCHAR(255) NOT NULL,
    `sender_name` VARCHAR(255) NOT NULL DEFAULT 'MobilityCare',
    `reply_to` VARCHAR(255) NOT NULL DEFAULT '',
    `subject` VARCHAR(255) NOT NULL,
    `html_body` MEDIUMTEXT NOT NULL,
    `priority` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=normal, 2=high',
    `status` ENUM('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
    `attempts` TINYINT(3) NOT NULL DEFAULT 0,
    `max_attempts` TINYINT(3) NOT NULL DEFAULT 3,
    `last_error` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `processed_at` DATETIME NULL,
    PRIMARY KEY (`mail_queue_id`),
    INDEX `idx_status_priority` (`status`, `priority` DESC, `created_at` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$result = $db->query($sql);
if ($db->error) {
    echo 'ERROR: ' . $db->error . PHP_EOL;
    exit(1);
} else {
    echo 'SUCCESS: ' . DB_PREFIX . 'mail_queue table created.' . PHP_EOL;
}

$db->close();
