<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Query {

	private $_query_vars = array(
		'thrive-variations' => 'true',
		'variation'         => 'int',
		'test-id'           => 'int',
		'generate-stats'    => 'true',
	);

	public function __construct() {

		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	public function add_query_vars( $vars ) {

		foreach ( $this->_query_vars as $key => $value ) {
			$vars[] = $key;
		}

		return $vars;
	}

	public function get_var( $key ) {

		$value = null;

		if ( in_array( $key, array_keys( $this->_query_vars ) ) ) {

			global $wp;

			$value = isset( $wp->query_vars[ $key ] ) ? $wp->query_vars[ $key ] : null;
		}

		return $value;

	}
}
