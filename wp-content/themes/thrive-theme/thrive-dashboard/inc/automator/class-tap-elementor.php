<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace TVE\Dashboard\Automator;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Elementor
 */
class Elementor {

	const FORM_SUBMIT_HOOK = 'elementor_pro/forms/process';

	public static function init() {
		if ( static::exists() ) {
			static::hooks();
		}
	}

	public static function hooks() {
		add_action( static::FORM_SUBMIT_HOOK, static function ( $form_record ) {
			if ( ! empty( $_POST['form_id'] ) ) {
				do_action( static::create_dynamic_trigger( static::FORM_SUBMIT_HOOK, strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $_POST['form_id'] ) ) ) ), [ $form_record ] );
			}
		} );
		add_filter( 'td_automator_should_load_file', [ __CLASS__, 'should_load_elementor_files' ], 10, 2 );


		add_filter( 'tvd_automator_api_data_sets', [ __CLASS__, 'add_api_data_sets' ], 10, 2 );
	}

	public static function add_api_data_sets( $sets ) {
		$data_sets[] = Elementor_Form_Data::get_id();

		return $sets;
	}

	public static function exists() {
		return class_exists( 'ElementorPro\Plugin', false );
	}

	public static function get_elementor_posts() {
		$args = array(
			'post_type'  => [ 'page', 'elementor_library', 'post' ],
			'meta_query' => array(
				array(
					'key'     => '_elementor_data',
					'value'   => 'form',
					'compare' => 'LIKE',
				),
			),
		);
		header( 'Content-type: text/html' );

		return ( new WP_Query( $args ) )->get_posts();
	}

	public static function should_load_elementor_files( $load, $filename ) {
		if ( strpos( basename( $filename, '.php' ), '-elementor-' ) !== false && ! static::exists() ) {
			$load = false;
		}

		return $load;
	}

	public static function create_dynamic_trigger( $prefix, $id ) {
		return $prefix . '_' . $id;
	}

}
