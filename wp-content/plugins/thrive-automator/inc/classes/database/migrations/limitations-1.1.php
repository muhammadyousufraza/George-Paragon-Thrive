<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
$this->create_table( 'limitations', '
`id` INT NOT NULL AUTO_INCREMENT ,
`automation_id` INT NOT NULL,
`date_started` DATETIME NOT NULL,
`user_id` INT NULL,
`trigger_id` VARCHAR(255) NULL DEFAULT NULL,
`additional` TEXT NOT NULL, 
PRIMARY KEY (`id`)
', true );
