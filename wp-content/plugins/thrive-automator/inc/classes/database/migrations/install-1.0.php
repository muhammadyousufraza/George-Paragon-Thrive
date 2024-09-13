<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
/*error log table*/
$this->create_table( 'error_log', '
`id` INT NOT NULL AUTO_INCREMENT ,
`automation_id` INT NOT NULL,
`date_started` DATETIME NOT NULL ,
`error` TEXT NOT NULL ,
`raw_data` TEXT NOT NULL, 
PRIMARY KEY (`id`)
', true );
