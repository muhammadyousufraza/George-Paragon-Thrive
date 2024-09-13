<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/15/2018
 * Time: 8:40 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/** @var $this TD_DB_Migration $questions */

$this->drop_column( 'users', 'ip_address' );
