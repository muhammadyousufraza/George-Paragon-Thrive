<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}
/**
 * The version is 1.0.3 instead of 1.0.2 because a fix was applied directly to the 1.0.2 file (this), making it 1.0.3.
 */

/** @var TD_DB_Migration $installer */
$installer = $this;

$installer->create_table( 'thrive_reporting_logs', '
    `id`              BIGINT(20)                                                          NOT NULL AUTO_INCREMENT,
    `event_type`      VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `created`         DATETIME                                                     NULL DEFAULT NULL,
    `item_id`         BIGINT(20) NULL DEFAULT 0,
    `user_id`         BIGINT(20) NULL DEFAULT 0,
    `post_id`         BIGINT(20) NULL DEFAULT 0,
    `int_field_1`     BIGINT(20) NULL DEFAULT NULL,
    `int_field_2`     BIGINT(20) NULL DEFAULT NULL,
    `float_field`     DOUBLE NULL DEFAULT NULL,
    `varchar_field_1` VARCHAR(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `varchar_field_2` VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `text_field_1`    TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX (`event_type`),
    INDEX (`user_id`),
    INDEX (`item_id`),
    INDEX (`created`),
    INDEX (`post_id`)'
);
