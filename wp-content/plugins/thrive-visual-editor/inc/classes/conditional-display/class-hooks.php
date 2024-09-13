<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay;

use TCB\ConditionalDisplay\PostTypes\Conditional_Display_Group;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Hooks {
	public static function add_filters() {
		add_filter( 'tcb_content_allowed_shortcodes', [ __CLASS__, 'allowed_shortcodes' ] );

		add_filter( 'tcb_lazy_load_data', [ __CLASS__, 'load_display_groups' ], 10, 3 );

		add_filter( 'tve_frontend_options_data', [ __CLASS__, 'tve_frontend_data' ] );

		add_filter( 'tve_dash_frontend_ajax_response', [ __CLASS__, 'lazy_load_response' ] );

		add_filter( 'tve_update_symbol_html', [ __CLASS__, 'tve_update_symbol_html' ], 10, 2 );
	}

	public static function add_actions() {
		add_action( 'tcb_ajax_save_post', [ __CLASS__, 'save_post' ] );
		add_action( 'tcb_ajax_before', [ __CLASS__, 'save_post' ] );

		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );

		add_action( 'wp_ajax_nopriv_tcb_conditional_display', [ __CLASS__, 'lazy_load_display_groups' ] );
		add_action( 'wp_ajax_tcb_conditional_display', [ __CLASS__, 'lazy_load_display_groups' ] );
		add_action( 'wp_ajax_dismiss_conditional_tooltip', [ __CLASS__, 'dismiss_conditional_tooltip' ] );

		add_action( 'wp_print_footer_scripts', [ __CLASS__, 'wp_print_footer_scripts' ] );

		add_action( 'admin_bar_menu', [ __CLASS__, 'admin_bar_menu' ], 1000 );

		add_action( 'after_thrive_clone_item', [ __CLASS__, 'after_thrive_clone_item' ], 10, 3 );
	}

	/**
	 * On request for lazy loading other products content we add the conditional display data
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public static function lazy_load_response( $response ) {

		if ( Shortcode::is_preview() && isset( $GLOBALS['conditional_display_preview'] ) ) {
			$response['lazy_load_conditional_preview'] = array_values( $GLOBALS['conditional_display_preview'] );
		}

		return $response;
	}

	/**
	 * @param $allowed_shortcodes
	 *
	 * @return mixed
	 */
	public static function allowed_shortcodes( $allowed_shortcodes ) {
		if ( is_editor_page_raw( true ) ) {
			$allowed_shortcodes[] = Shortcode::NAME;
		}

		return $allowed_shortcodes;
	}

	/**
	 * Lazy load localize display groups inside the editor
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function load_display_groups( $data ) {
		if ( isset( $_POST['conditional-display-groups'] ) && is_array( $_POST['conditional-display-groups'] ) ) {
			foreach ( $_POST['conditional-display-groups'] as $display_group_key ) {
				$display_group = Conditional_Display_Group::get_instance( $display_group_key );

				if ( $display_group !== null ) {
					$display_group->localize( false, true );
				}
			}

			$data['conditional-display-groups'] = array_values( $GLOBALS['conditional_display_preview'] );
		}

		return $data;
	}

	/**
	 * Load displays to be displayed in frontend
	 *
	 * @return void
	 */
	public static function lazy_load_display_groups() {

		$groups             = [];
		$external_resources = [
			'js'  => [],
			'css' => [],
		];

		if ( isset( $_GET['query_vars'] ) ) {
			tve_set_query_vars_data( $_GET['query_vars'] );
		}

		if ( is_array( $_GET['groups'] ) ) {
			foreach ( $_GET['groups'] as $display_group_key ) {
				$display_group = Conditional_Display_Group::get_instance( $display_group_key );

				if ( $display_group !== null ) {
					foreach ( $display_group->get_displays() as $display ) {
						if ( Shortcode::verify_conditions( $display ) ) {
							if ( empty( $display['hide'] ) ) {
								$content = Shortcode::parse_content( $display['html'], false );

								if ( ! empty( $content ) ) {
									$groups[ $display_group_key ] = [
										'content' => $content,
									];

									$external_resources = Conditional_Display_Group::get_external_resources_for_content( $external_resources, $content );
								}
							}

							break;
						}
					}
				}
			}
		}

		ob_start();
		/* retrieve animations and actions events and maybe lightboxes if needed */
		tve_print_footer_events();
		$footer_scripts = ob_get_clean();

		wp_send_json( [
				'groups'             => $groups,
				'footer_scripts'     => $footer_scripts,
				'external_resources' => $external_resources,
			]
		);
	}

	public static function dismiss_conditional_tooltip() {
		$dismissed_tooltips   = (array) get_user_meta( wp_get_current_user()->ID, 'tcb_dismissed_tooltips' );
		$dismissed_tooltips[] = 'conditional-display-tooltip';

		update_user_meta( wp_get_current_user()->ID, 'tcb_dismissed_tooltips', $dismissed_tooltips );
	}

	/**
	 * Save conditional displays
	 */
	public static function save_post() {
		if ( isset( $_REQUEST['conditional-displays'] ) ) {
			Conditional_Display_Group::save_groups( $_REQUEST['conditional-displays'] );
		}
	}

	public static function register_routes() {
		RestApi\General_Data::register_routes();
		RestApi\Global_Sets::register_routes();
	}

	public static function wp_print_footer_scripts() {
		/**
		 * Make sure that is printed only on initial load not while plugins do lazy load
		 */
		if ( Shortcode::is_preview() && ! wp_doing_ajax() ) {
			if ( empty( $GLOBALS['conditional_display_preview'] ) ) {
				$GLOBALS['conditional_display_preview'] = [];
			}
			echo \TCB_Utils::wrap_content( "var tcb_condition_sets=JSON.parse('" . addslashes( json_encode( array_values( $GLOBALS['conditional_display_preview'] ) ) ) . "');", 'script', '', '', [ 'type' => 'text/javascript' ] ); // phpcs:ignore
		}
	}

	/**
	 * Add some data to the frontend localized object
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function tve_frontend_data( $data ) {
		$dismissed_tooltips          = (array) get_user_meta( wp_get_current_user()->ID, 'tcb_dismissed_tooltips', true );
		$data['conditional_display'] = [
			'is_tooltip_dismissed' => in_array( 'conditional-display-tooltip', $dismissed_tooltips, true ),
		];

		return $data;
	}

	/**
	 * @param \WP_Admin_Bar $wp_admin_bar
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {
		if ( ! Shortcode::is_preview() ) {
			return;
		}
		wp_enqueue_style( 'tve-logged-in-style', tve_editor_css( 'logged-in.css' ), false, TVE_VERSION );

		$wp_admin_bar->add_node( [
			'id'     => 'tve-preview-conditions',
			'title'  => '<span class="tve-preview-conditions-icon admin-bar"></span>',
			'parent' => 'top-secondary',
			'meta'   => [ 'class' => 'tcb-preview-hidden' ],
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'tve-conditions-title',
			'title'  => '<span class="tve-preview-conditions-icon"></span>' .
			            '<span class="tve-preview-conditions-title">Preview conditions</span>' .
			            '<div class="tve-preview-conditions-info">
							<div class="tve-preview-conditions-tooltip">
							           This page contains conditional displays on some content . You can preview how the page looks for users that match different conditions by selecting them below.
							<a class="tve-preview-conditions-tooltip-link" target="_blank" href="https://help.thrivethemes.com/en/articles/5814058-how-to-use-the-conditional-display-option">Learn more </a>
							</div>
						</div> ' .
			            '<button class="tve-preview-conditions-close"></button> ',
			'parent' => 'tve-preview-conditions',
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'tve-conditions-tooltip',
			'title'  => '<div class="tve-preview-conditions-tooltip-text">This page contains conditional displays.</div><div class="tve-preview-conditions-tooltip-text">Click here to change your preview settings.</div> ',
			'parent' => 'tve-preview-conditions',
		] );
	}

	/**
	 * Replace conditional display groups inside the cloned item
	 *
	 * @param int   $new_post_id
	 * @param int   $original_post_id
	 * @param array $css_id_map
	 *
	 * @return void
	 */
	public static function after_thrive_clone_item( $new_post_id, $original_post_id, $css_id_map ) {
		$content = tve_get_post_meta( $new_post_id, 'tve_updated_post' );

		$content = Conditional_Display_Group::clone_conditional_groups_in_content( $content, $css_id_map );

		tve_update_post_meta( $new_post_id, 'tve_updated_post', $content );
	}

	/**
	 * Conditionals ids for duplicated symbols needs to be regenerated
	 *
	 * @param $meta_key
	 * @param $meta_value
	 */
	public static function tve_update_symbol_html( $meta_key, $meta_value ) {
		/* For the Conditional Display we need to regenerate the ids of the added conditions */
		if ( $meta_key === 'tve_updated_post' ) {
			$meta_value = Conditional_Display_Group::clone_conditional_groups_in_content( $meta_value );
		}

		return $meta_value;
	}
}
