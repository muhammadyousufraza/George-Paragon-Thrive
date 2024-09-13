<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Theme_Dashboard
 */
class Thrive_Theme_Dashboard {

	/**
	 * Thrive_Theme_Dashboard constructor.
	 */
	public static function init() {
		/* the priority here must be lower than the one set from thrive-dashboard/version.php */
		add_action( 'after_setup_theme', [ __CLASS__, 'load_dash_version' ], 1 );
		/* add thrive theme to dashboard */
		add_filter( 'tve_dash_installed_products', [ __CLASS__, 'add_to_dashboard' ] );
		/* add admin pages */
		add_filter( 'tve_dash_admin_product_menu', [ __CLASS__, 'add_admin_pages' ] );
		/* notify for licence check */
		add_action( 'init', [ __CLASS__, 'add_license_notice' ] );
		/* called when trying to edit a post to check Thrive Theme capability with TAR deactivated */
		add_filter( 'tcb_user_has_plugin_edit_cap', [ __CLASS__, 'can_use_theme' ] );
		/* extend thrive dashboard global data */
		add_filter( 'tvd_global_data', [ __CLASS__, 'tvd_global_data' ] );
	}

	/**
	 * Check the current version of the dashboard and decide if we load this one or  newer one
	 */
	public static function load_dash_version() {
		$_dash_path      = THEME_PATH . '/thrive-dashboard';
		$_dash_file_path = $_dash_path . '/version.php';

		if ( is_file( $_dash_file_path ) ) {
			$version = require_once( $_dash_file_path );

			$GLOBALS['tve_dash_versions'][ $version ] = [
				'path'   => $_dash_path . '/thrive-dashboard.php',
				'folder' => THEME_DOMAIN,
				'from'   => 'themes',
			];
		}
	}

	/**
	 * Add theme to the dashboard
	 *
	 * @param $items
	 *
	 * @return array
	 */
	public static function add_to_dashboard( $items ) {
		require_once 'class-thrive-theme-product.php';

		$items[] = new Thrive_Theme_Product();

		return $items;
	}

	/**
	 * Add menu pages but hide them
	 *
	 * @param array $menus
	 *
	 * @return array
	 */
	public static function add_admin_pages( $menus = [] ) {
		$menus['thrive_theme_license_validation'] = [
			'parent_slug' => null,
			'page_title'  => null,
			'menu_title'  => null,
			'capability'  => 'edit_theme_options',
			'menu_slug'   => 'thrive_license_validation',
			'function'    => 'thrive_license_validation',
		];
		if ( thrive_theme()->licence_check() ) {
			$menus['thrive_theme_admin_options'] = [
				'parent_slug' => 'tve_dash_section',
				'page_title'  => __( 'Thrive Theme Builder', 'thrive-theme' ),
				'menu_title'  => __( 'Thrive Theme Builder', 'thrive-theme' ),
				'capability'  => 'edit_theme_options',
				'menu_slug'   => THRIVE_MENU_SLUG,
				'function'    => static function () {
					$compatibility = Thrive_Architect::version_compatibility();
					if ( $compatibility['compatible'] ) {
						echo TCB_Utils::wrap_content( '', 'div', 'thrive-theme-dashboard', 'ttd-main ttd-fixed-sidebar' );
					} else {
						// incompatible versions. show warning page
						echo Thrive_Utils::return_part( '/inc/templates/admin/incompatible-architect.php', $compatibility, false );
					}
				},
			];
		}

		return $menus;
	}

	/*
	 * Display top warning if the theme has not activated.
	 */
	public static function add_license_notice() {
		if ( ! static::check_license() ) {
			add_action( 'admin_notices', [ __CLASS__, 'activate_license_notice' ] );
		}
	}

	public static function activate_license_notice() {
		$message = __( 'Your theme has successfully been activated! Next step: please validate your license by entering your email and license key here: ', 'thrive-theme' );
		echo sprintf( '<div class="notice notice-warning"><p>%s <a href="%s">License Activation</a></p></div>', $message, admin_url( 'admin.php?page=tve_dash_license_manager_section' ) );
	}

	/**
	 * Check license status
	 *
	 * @return bool
	 */
	public static function check_license() {
		return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( Thrive_Theme_Product::TAG );
	}

	/**
	 * Check if the post can be edited by checking access and post type
	 *
	 * @param $has_access
	 *
	 * @return mixed
	 */
	public static function can_use_theme( $has_access ) {
		if ( get_post_type() === THRIVE_TEMPLATE ) {
			$has_access = Thrive_Theme_Product::has_access();
		}

		return $has_access;
	}

	/**
	 * Add the logo link from the theme settings. This will be used only by the logo element
	 *
	 * @param $global_data
	 *
	 * @return array
	 */
	public static function tvd_global_data( $global_data ) {
		$global_data[] = [
			'name'      => __( 'Logo Link', 'thrive-theme' ),
			'url'       => Thrive_Branding::get_logo_url( site_url() ),
			'show'      => true,
			'unique_id' => 'logo_link',
		];

		return $global_data;
	}
}
