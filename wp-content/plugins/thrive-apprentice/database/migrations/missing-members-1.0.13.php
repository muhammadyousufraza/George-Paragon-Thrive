<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/** @var $this TD_DB_Migration */
try {
	TVA_Order::fix_orders_without_history();
} catch ( Exception $e ) {
}
