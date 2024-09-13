<?php
/**
 * Migration for creating required table for emails log
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/** @var TD_DB_Migration $installer */
$installer = $this;

$installer->add_query( "UPDATE {emails} SET `email` = MD5(`email`)" );
