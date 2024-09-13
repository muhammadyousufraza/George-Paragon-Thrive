<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Post_Types {

	const VARIATION = 'thrive_ab_variation';

	public static function init() {

		add_action( 'init', array( __CLASS__, 'register_post_types' ) );

		add_filter( 'tve_landing_page_post_types', array( __CLASS__, 'add_variation_type_as_landing_page' ) );
		add_filter( 'tve_post_having_non_lp_settings', array( __CLASS__, 'has_non_lp_settings' ) );
		add_filter( 'tcb_post_grid_banned_types', array( __CLASS__, 'add_post_grid_banned_types' ) );
	}

	public static function register_post_types() {

		register_post_type( self::VARIATION, array(
			'labels'             => array(
				'name' => 'Thrive Optimize Variation',
			),
			'hierarchical'       => true,
			'publicly_queryable' => true,
			'query_var'          => false,
			'rewrite'            => false,
		) );
	}

	/**
	 * Tells TAr variation post type can be used as landing page
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public static function add_variation_type_as_landing_page( $post_types ) {

		$post_types[] = self::VARIATION;

		return $post_types;
	}

	/**
	 * Add some Optimize post types to Architect Post Grid Element Banned Types
	 *
	 * @param array $banned_types
	 *
	 * @return array
	 */
	public static function add_post_grid_banned_types( $banned_types = array() ) {
		$banned_types[] = self::VARIATION;

		return $banned_types;
	}

	/**
	 * Tells TAr to load some settings for variation post type
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public static function has_non_lp_settings( $post_types ) {

		$post_types[] = self::VARIATION;

		return $post_types;
	}
}

Thrive_AB_Post_Types::init();
