<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Post_Status {

	const VARIATION = 'tab_variation';

	public static function init() {

		add_action( 'init', array( __CLASS__, 'register_variation_status' ) );
	}

	public static function register_variation_status() {

		$args = array(
			'label'                     => __( 'TOP Variation', 'thrive-ab-page-testing' ),
			'label_count'               => _n_noop( 'TOP Variation (%s)', 'TOP Variations (%s)', 'thrive-ab-page-testing' ),
			'public'                    => false,//posts shown in frontend ?
			'internal'                  => false,//internal use?
			'private'                   => false,//posts accessed by their url?
			'protected'                 => true,//for own user see class-wp-query.php line 2895
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => false,//posts shown in admin lists?
			'show_in_admin_status_list' => false,//lists among other statues
		);

		register_post_status( self::VARIATION, $args );
	}
}

Thrive_AB_Post_Status::init();
