<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks - specific for Display Testimonial element and functionality
 *
 * @package TVO\DisplayTestimonials
 */
class Hooks {
	public static function add_filters() {
		add_filter( 'tcb_element_instances', [ __CLASS__, 'tvo_tcb_add_elements' ] );

		add_filter( 'tcb_menu_path_display_testimonials', [ __CLASS__, 'tvo_tcb_display_menu_path' ] );

		add_filter( 'tcb_inline_shortcodes', [ __CLASS__, 'tvo_tcb_inline_shortcodes' ] );

		add_filter( 'tcb_content_allowed_shortcodes', [ __CLASS__, 'content_allowed_shortcodes' ] );

		add_filter( 'thrive_theme_shortcode_prefixes', [ __CLASS__, 'tvo_shortcode_prefixes' ] );

		add_filter( 'tcb_categories_order', [ __CLASS__, 'tvo_categories_order' ] );

	}

	public static function add_actions() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'tvo_display_testimonials_style' ) );

		add_action( 'rest_api_init', [ Rest_Api::class, 'register_routes' ] );

		add_filter( 'tcb_lazy_load_data', [ __CLASS__, 'localize_testimonial_shortcodes' ], 10, 3 );

		add_action( 'wp_print_footer_scripts', [ __CLASS__, 'tvo_print_footer_scripts' ] );

		add_action( 'tve_frontend_options_data', [ __CLASS__, 'tvo_tcb_frontend_data' ] );

	}

	/**
	 * Add shortcode prefixes for Thrive Ovation
	 *
	 * @param $prefixes
	 *
	 * @return mixed
	 */
	public static function tvo_shortcode_prefixes( $prefixes ) {
		$prefixes[] = 'tvo_testimonial_';

		return $prefixes;
	}

	/**
	 * Include Thrive Ovation elements into tcb
	 *
	 * @param $elements
	 *
	 * @return mixed
	 */
	public static function tvo_tcb_add_elements( $elements ) {
		$files = array_diff( scandir( __DIR__ . '/elements' ), array( '.', '..' ) );

		/* Require all elements files */
		foreach ( $files as $file ) {
			require_once __DIR__ . '/elements/' . $file;
		}

		$sub_elements = [
			'display_testimonial_title',
			'display_testimonial_content',
			'display_testimonial_author',
			'display_testimonial_role',
			'display_testimonial_website',
			'display_testimonial_image',
		];
		$class_prefix = 'TVO\\DisplayTestimonials\\TCB_';

		/* Add the main element */
		$elements['display_testimonials'] = new \TVO\DisplayTestimonials\TCB_Display_Testimonials( 'display_testimonials' );

		/* Add the sub-elements */
		foreach ( $sub_elements as $key ) {
			$class_name = $class_prefix . str_replace( ' ', '_', ucwords( str_replace( '_', ' ', str_replace( 'display_', '', $key ) ) ) ) . '_Element';

			$elements[ $key ] = new $class_name( $key );
		}

		return $elements;
	}

	/**
	 * Return capture testimonial menu component
	 *
	 * @return string
	 */
	public static function tvo_tcb_display_menu_path() {
		return __DIR__ . '/views/component.phtml';
	}

	/**
	 * Adds the Display Testimonials shortcodes in the general array of TAR shortcodes
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public static function tvo_tcb_inline_shortcodes( $shortcodes ) {
		return array_merge_recursive( Utils::get_ovation_inline_shortcodes(), $shortcodes );
	}

	/**
	 * Enqueues the style used in front-end
	 */
	public static function tvo_display_testimonials_style() {
		wp_enqueue_style( 'tvo-frontend', TVO_URL . 'tcb-bridge/frontend/css/frontend.css' );
	}

	/**
	 * Add the Display Testimonials shortcodes in the TAR allowed shortcodes array
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public static function content_allowed_shortcodes( $shortcodes = [] ) {
		return array_merge( $shortcodes, Shortcodes::$shortcodes, [ Main::SHORTCODE ] );
	}

	/**
	 * Print the array of testimonials on the page, it will be later used in frontend, for Pagination
	 */
	public static function tvo_print_footer_scripts() {
		if ( ! \TCB_Editor()->is_inner_frame() && ! is_editor_page_raw() && ! empty( $GLOBALS[ Main::JS_LOCALIZE_CONST ] ) ) {
			foreach ( $GLOBALS[ Main::JS_LOCALIZE_CONST ] as $display_testimonials ) {
				echo \TCB_Utils::wrap_content(
					str_replace( [ '[', ']' ], array( '{({', '})}' ), $display_testimonials['content'] ),
					'script',
					'',
					'thrive-display-testimonials-template',
					[
						'type'            => 'text/template',
						'data-identifier' => $display_testimonials['template'],
					]
				);
			}

			/* remove the content before localizing */
			$testimonials_localize = array_map( static function ( $item ) {
				unset( $item['content'] );

				return $item;
			}, $GLOBALS[ Main::JS_LOCALIZE_CONST ] );

			$script_contents = "var tvo_display_testimonials_list=JSON.parse('" . addslashes( json_encode( $testimonials_localize ) ) . "');";

			echo \TCB_Utils::wrap_content( $script_contents, 'script', '', '', array( 'type' => 'text/javascript' ) );
		}

	}

	/**
	 * Localize the route for front-end requests
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function tvo_tcb_frontend_data( $data ) {
		if ( ! empty( $data['routes'] ) ) {
			$data['routes']['testimonials'] = get_rest_url() . 'tcb/v1/testimonials';
		}

		return $data;
	}

	/**
	 * Localize the shortcode data specific for testimonials (only for the ones currently existing in the page).
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function localize_testimonial_shortcodes( $data ) {
		if ( isset( $_POST['testimonial-existing-ids'] ) && is_array( $_POST['testimonial-existing-ids'] ) ) {
			$data['testimonial_shortcodes'] = Main::get_testimonial_shortcodes( $_POST['testimonial-existing-ids'] );
		}

		$data['has_at_least_one_testimonial'] = count( Main::get_testimonials( [ 'posts_per_page' => 1 ] ) ) === 1;

		return $data;
	}

	/**
	 * Change the order of the testimonial elements in the sidebar, so they will show first
	 *
	 * @param $order
	 *
	 * @return mixed
	 */
	public static function tvo_categories_order( $order ) {
		$order[2] = Main::elements_group_label();

		return $order;
	}

}
