<?php

/**
 * @var $this TD_DB_Migration
 */
$this->create_table(
	TVA_Token::get_table_name(),
	'
	`id` INT NOT NULL AUTO_INCREMENT,
	`key` VARCHAR(255) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`status` INT NOT NULL,
	PRIMARY KEY (`id`)
	'
);
