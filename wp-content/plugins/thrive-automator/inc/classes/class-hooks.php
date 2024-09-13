<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;

use Thrive\Automator\Suite\TTW;
use function get_current_user_id;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package Thrive\Automator
 */
class Hooks {
	const APP_ID = 'thrive-automator-admin';

	public static function init() {
		static::add_actions();
		static::add_filters();
	}

	/**
	 * Setup hooks
	 */
	public static function add_actions() {

		add_action( 'rest_api_init', [ __CLASS__, 'tap_rest_controller' ] );

		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );

		/**
		 * Load classes when in DOING_CRON environment
		 */
		if ( defined( 'DOING_CRON' ) && DOING_CRON === true ) {
			add_action( 'init', [ 'Thrive\Automator\Admin', 'load_items' ], 10 );
		}
		/**
		 * Setup listeners for all running automations
		 */
		add_action( 'wp_loaded', [ __CLASS__, 'start_automations' ] );

		/**
		 * Action used for running delayed automations
		 */
		add_action( 'tap_delayed_automations', [
			'Thrive\Automator\Items\Automation',
			'run_delayed_automations',
		], 1, 4 );

		add_action( 'tap_delayed_automations_improved', [
			'Thrive\Automator\Items\Automation',
			'run_delayed_automations_improved',
		], 1, 2 );

		add_action( 'init', [ __CLASS__, 'launch_thrive_automator_init_hook' ], 1001 );

		add_action( 'profile_update', [ __CLASS__, 'launch_thrive_automator_profile_update_hook' ], 10, 2 );

		add_filter( 'tvd_automator_api_data_sets', [ __CLASS__, 'dashboard_sets' ], 10, 1 );

		add_action( 'transition_post_status', [ __CLASS__, 'launch_post_status_hooks' ], 10, 3 );

		add_action( 'post_updated', [ __CLASS__, 'launch_post_updated_hook' ], 1, 3 );

		add_action( 'admin_init', [ TTW::class, 'admin_init' ] );

		add_action( 'admin_init', [ __CLASS__, 'admin_init' ] );

		add_action( 'activated_plugin', [ __CLASS__, 'activation_redirect' ] );

		add_action( 'upgrader_process_complete', [ __CLASS__, 'after_update' ], 10, 2 );

//		add_action( 'wp_ajax_thrive_automator_reset', [ Thrive_Reset::class, 'factory_reset' ] );
	}

	public static function add_filters() {
		/**
		 * Add TAP Product to Thrive Dashboard
		 */
		add_filter( 'tve_dash_installed_products', [ __CLASS__, 'add_to_dashboard' ] );

		add_filter( 'thrive_dashboard_loaded', [ __CLASS__, 'load_product_file' ] );
		add_filter( 'tve_dash_menu_products_order', [ __CLASS__, 'set_admin_menu_order' ] );

		add_filter( 'tve_dash_admin_product_menu', [ __CLASS__, 'add_dash_menu' ] );

		/* enable dashboard features */
		add_filter( 'tve_dash_features', [ __CLASS__, 'tve_dash_features' ] );

		add_filter( 'thrive_automator_filter_duplicate_meta', [ Items\Automation::class, 'duplicate_meta' ], 10, 3 );

	}

	/**
	 * Redirect the user to Automator dashboard once the plugin is activated
	 *
	 * Set default capabilities so it will work with Access Manager
	 *
	 * @param $plugin
	 *
	 * @return void
	 */
	public static function activation_redirect( $plugin ) {
		if ( $plugin === TAP_PLUGIN && ! get_option( 'thrive_automator_activation_redirect' ) ) {
			$capability = 'tve-use-tap';
			$admin      = get_role( 'administrator' );
			if ( $admin && ! $admin->has_cap( $capability ) ) {
				$admin->add_cap( $capability );
			}

			$editor = get_role( 'editor' );
			if ( $editor && ! $editor->has_cap( $capability ) ) {
				$editor->add_cap( $capability );
			}

			update_option( 'thrive_automator_activation_redirect', 1, 'no' );
			exit( wp_redirect( admin_url( 'admin.php?page=thrive_automator#/suite' ) ) );
		}
	}

	public static function admin_menu() {

		add_menu_page(
			TAP_PLUGIN_NAME,
			TAP_PLUGIN_NAME,
			'manage_options',
			'thrive_automator',
			static function () {
				echo '<div id="' . esc_attr( static::APP_ID ) . '"></div>';
			},
			TAP_PLUGIN_URL . 'icons/thrive-logo-icon.png'
		);

		Thrive_Reset::init();

	}

	/**
	 * Register Automator Product to Dashboard
	 *
	 * @param $items
	 *
	 * @return mixed
	 */
	public static function add_to_dashboard( $items ) {
		if ( class_exists( 'TVE_Dash_Product_Abstract', false ) ) {
			$items[] = new TAP_Product();
		}

		return $items;
	}

	/**
	 * Load Automator Product file
	 */
	public static function load_product_file() {
		require_once TAP_PLUGIN_PATH . '/inc/classes/class-tap-product.php';
	}


	/**
	 * Add Automator to TD menu instead of top level menu item
	 *
	 * @param $menus
	 *
	 * @return mixed
	 */
	public static function add_dash_menu( $menus ) {
		remove_menu_page( TAP_SLUG );

		$menus['automator'] = array(
			'parent_slug' => 'tve_dash_section',
			'page_title'  => TAP_PLUGIN_NAME,
			'menu_title'  => TAP_PLUGIN_NAME,
			'capability'  => TAP_Product::cap(),
			'menu_slug'   => TAP_SLUG,
			'function'    => static function () {
				echo '<div id="' . esc_attr( static::APP_ID ) . '"></div>';
			},
		);


		return $menus;
	}

	/**
	 * Push the new Thrive Automator submenu item into an array at a specific order
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public static function set_admin_menu_order( $items ) {

		$items[9] = 'automator';

		return $items;
	}

	/**
	 * Enqueue scripts inside automation editor
	 */
	public static function admin_enqueue_scripts( $screen ) {
		if ( ! empty( $screen ) && $screen === Admin::PAGE_SLUG ) {
			Utils::enqueue_assets( 'admin', static::get_localize_data() );

			do_action( 'tap_output_extra_svg' );
		}
		wp_enqueue_style( 'tap-generic-admin', TAP_PLUGIN_URL . 'assets/css/generic_admin.css' );
	}

	/**
	 * localize data for automation editor
	 */
	public static function get_localize_data() {
		return [
			'app_id'              => static::APP_ID,
			'routes'              => get_rest_url( get_current_blog_id(), Internal_Rest_Controller::NAMESPACE ),
			'delay_units'         => Items\Delay::dropdown_options(),
			'error_log_intervals' => Error_Log_Handler::get_available_intervals(),
			'nonce'               => wp_create_nonce( 'wp_rest' ),
			'log_settings'        => Error_Log_Handler::get_log_settings(),
			'timezone_offset'     => get_option( 'gmt_offset' ),
			'debug_mode'          => defined( 'TVE_DEBUG' ) && TVE_DEBUG,
			'load_limit'          => Utils::OPTIONS_LIMIT,
			'ttw'                 => TTW::localize(),
			'file_nonce'          => wp_create_nonce( 'zip_upload_nonce' ),
			'tooltips'            => [
				'apps_tooltip' => (bool) Utils::get_user_meta( 0, 'apps_tooltip' ),
			],
			'term_cond'           => '//thrivethemes.com/terms/',
			'deactivate_nonce'    => wp_create_nonce( 'tap_deactivate_nonce' ),
			'tracking_enabled'    => Tracking::is_tracking_allowed(),
			'tracking_ribbon_id'  => Tracking::TRACKING_NOTICE_ID,
			'has_suite_access'    => Utils::has_suite_access(),
			'urls'                => [
				'settings_url' => admin_url( 'options-general.php' ),
				'term_cond'    => '//thrivethemes.com/terms/',
				'apps_link'    => admin_url( 'admin.php?page=tve_dash_api_connect' ),
				'consent'      => '//help.thrivethemes.com/en/articles/6796332-thrive-themes-data-collection',
			],
		];
	}

	/**
	 * Setup listeners for all running automations
	 */
	public static function start_automations() {
		Items\Automations::start();
	}

	/**
	 * Setup automator rest controllers
	 */
	public static function tap_rest_controller() {
		$internal = new Internal_Rest_Controller();
		$internal->register_routes();

		$integrations = new Integrations_Rest_Controller();
		$integrations->register_routes();

		$error_log = new Errorlog_Rest_Controller();
		$error_log->register_routes();
	}

	/**
	 * Setup hook for external items loading
	 */
	public static function launch_thrive_automator_init_hook() {
		$can_run      = true;
		$incompatible = [];
		if ( ! defined( 'TVE_DEBUG' ) || ! TVE_DEBUG ) {
			/* TA */
			if ( class_exists( '\TVA_Const', false ) && version_compare( \TVA_Const::PLUGIN_VERSION, '4.3.1', '<' ) ) {
				$can_run         = false;
				$incompatible [] = 'Thrive Apprentice';
			}
			/* TU */
			if ( class_exists( '\TVE_Ult_Const', false ) && version_compare( \TVE_Ult_Const::PLUGIN_VERSION, '3.7.1', '<' ) ) {
				$can_run         = false;
				$incompatible [] = 'Thrive Ultimatum';
			}
			/* TAr */
			if ( defined( 'TVE_IN_ARCHITECT' ) && defined( 'TVE_VERSION' ) && version_compare( TVE_VERSION, '3.9.1', '<' ) ) {
				$can_run         = false;
				$incompatible [] = 'Thrive Architect';
			}
			/* TD */
			if ( defined( 'TVE_DASH_VERSION' ) && (
					version_compare( TVE_DASH_VERSION, '3.7.1', '<' ) &&
					! preg_match( '/0\.\d{8,}/', TVE_DASH_VERSION ) /* dev version on test site*/
				) ) {
				$can_run = false;
				if ( empty( $incompatible ) ) {
					/* only add TD as a last option here, if nothing else was found ( TD does not exist as a stand-alone plugin, so the message might be misleading ) */
					$incompatible [] = 'Thrive Dashboard';
				}
			}
		}

		if ( $can_run ) {
			define( 'THRIVE_AUTOMATOR_RUNNING', true );
			do_action( 'thrive_automator_init' );
		} else {
			add_action( 'admin_notices', static function () use ( $incompatible ) {
				Utils::tap_template( 'notice-incompatible', [
					'incompatible' => $incompatible,
				] );
			} );
		}
	}

	/**
	 * Setup user updates own profile hook for existing trigger
	 */
	public static function launch_thrive_automator_profile_update_hook( $user_id, $old_user_data ) {
		if ( $user_id === get_current_user_id() ) {
			do_action( 'tap_user_updates_own_profile', $user_id, $old_user_data );
		}
	}

	public static function launch_post_status_hooks( $new_status, $old_status, $post ) {
		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			do_action( 'tap_post_publish', $post );
		}
	}

	public static function launch_post_viewed_hook() {
		$can_check_crawler = function_exists( 'tve_dash_is_crawler' );
		if ( ! $can_check_crawler || ( $can_check_crawler && ! tve_dash_is_crawler( true ) ) ) {
			$post = get_post();
			do_action( 'tap_post_view', $post );
		}
	}

	public static function launch_post_updated_hook( $post_id ) {
		// for gutenberg editor a second hook is thrown to save metaboxes
		if ( empty( $_REQUEST['meta-box-loader'] ) && empty( $_REQUEST[ 'tap-post-update-executed-' . $post_id ] ) ) {
			$_REQUEST[ 'tap-post-update-executed-' . $post_id ] = true;
			do_action( 'tap_post_updated', $post_id );
		}
	}

	/**
	 * Enable Api Connections card
	 *
	 * @param $features
	 *
	 * @return mixed
	 */
	public static function tve_dash_features( $features ) {
		$features['api_connections'] = true;

		return $features;
	}

	/**
	 * Enroll comment_data as data that can be used in TD for Automator actions
	 *
	 * @param $sets
	 *
	 * @return mixed
	 */
	public static function dashboard_sets( $sets ) {
		$sets[] = Items\Comment_Data::get_id();

		return $sets;
	}

	/**
	 * Save dismissed flag for the notices
	 *
	 * @return void
	 */
	public static function admin_init() {
		if ( ! empty( $_GET['tap-notice-dismiss'] ) ) {
			Utils::update_user_meta( 0, 'notice-dismissed-' . sanitize_key( $_GET['tap-notice-dismiss'] ), 1 );
		}
	}

	/**
	 * After plugin update remove dismissed notices
	 *
	 * @param $upgrader_object
	 * @param $options
	 *
	 * @return void
	 */
	public static function after_update( $upgrader_object, $options ) {
		if ( ! empty( $options['type'] ) && $options['type'] === 'plugin' && ! empty( $options['plugins'] ) && in_array( TAP_PLUGIN, $options['plugins'], true ) ) {
			Utils::delete_users_meta_by_key( 'tap-notice-dismissed' );
		}
	}
}
