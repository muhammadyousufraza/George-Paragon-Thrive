<?php

/**
 * @var $this TD_DB_Migration
 */
$this->create_table(
	\TVA\Access\History_Table::get_table_name(),
	'
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`user_id` BIGINT(20) NULL DEFAULT 0,
	`product_id` BIGINT(20) NULL DEFAULT 0,
	`course_id` BIGINT(20) NULL DEFAULT 0,
	`source` VARCHAR(255) NOT NULL,
	`status` TINYINT NOT NULL,
	`created` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX (`user_id`),
	INDEX (`course_id`),
	INDEX (`status`),
	INDEX (`created`)
	', true
);
