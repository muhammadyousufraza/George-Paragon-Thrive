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
$this->create_index( 'IDX_product_id', 'access_history', 'product_id' );
$this->create_index( 'IDX_source', 'access_history', 'source' );
