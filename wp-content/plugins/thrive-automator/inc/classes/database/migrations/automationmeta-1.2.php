<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
$this->set_table_prefix( '' );
$this->create_table( 'automationmeta', '
		meta_id bigint(20) NOT NULL AUTO_INCREMENT,
		automation_id bigint(20) NOT NULL DEFAULT "0",
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY meta_id (meta_id),
		KEY automation_id (automation_id),
		KEY meta_key (meta_key)
', true );
$this->set_table_prefix( TAP_DB_PREFIX );