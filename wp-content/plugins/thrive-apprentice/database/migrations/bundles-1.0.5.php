<?php
/** @var $this TD_DB_Migration */
$this->create_table( 'bundles', '
	`id` BIGINT NOT NULL AUTO_INCREMENT,
	`number` VARCHAR(30) NULL DEFAULT NULL,
	`name` VARCHAR(255) NOT NULL,
	`products` LONGTEXT NULL DEFAULT NULL,
	`created` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
	`edited` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
	PRIMARY KEY (`id`)
', true );
/**
 * Modify product_id column to varchar to be able to support bundle numbers
 */
$this->add_or_modify_column( 'order_items', 'product_id', 'VARCHAR(255) NULL DEFAULT NULL' );
