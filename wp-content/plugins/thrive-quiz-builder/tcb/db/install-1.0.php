<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/** @var TD_DB_Migration $installer */
$installer = $this;

$installer->create_table( 'api_error_log', '
    `id` INT( 11 ) AUTO_INCREMENT,
    `date` DATETIME NULL,
    `error_message` VARCHAR( 400 ) NULL,
    `api_data` TEXT NULL,
    `connection` VARCHAR( 64 ) NULL,
    `list_id` VARCHAR( 255 ) NULL,
     PRIMARY KEY( `id` )' );
