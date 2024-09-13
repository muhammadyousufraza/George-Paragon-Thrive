<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials\Query;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Hooks {
	public static function init() {
		add_action( 'tve_editor_print_footer_scripts', [ __CLASS__, 'editor_print_footer_scripts' ] );

		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );

		add_filter( 'tcb_modal_templates', [ __CLASS__, 'add_modals' ] );

		add_filter( 'tcb_lazy_load_data', [ __CLASS__, 'localize_testimonial_data' ], 10, 3 );
	}

	public static function localize_testimonial_data( $data ) {
		/* it's ok to localize them all since the amount of data is small */
		$data['sets'] = Set::localize_all();

		return $data;
	}

	public static function editor_print_footer_scripts() {
		$templates = tve_dash_get_backbone_templates( __DIR__ . '/views/backbone' );

		tve_dash_output_backbone_templates( $templates, 'tve-display-testimonials-' );
	}

	/**
	 * @param $modals
	 *
	 * @return mixed
	 */
	public static function add_modals( $modals ) {
		$modals[] = __DIR__ . '/views/modals/display-testimonials.php';

		return $modals;
	}

	public static function register_routes() {
		Rest_Api::register_routes();
	}
}
