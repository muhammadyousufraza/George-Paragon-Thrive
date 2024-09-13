<?php
/**
 * Plugin Name: Thrive Apprentice
 * Plugin URI: https://thrivethemes.com
 * Description: Create online courses you can sell with the most customizable LMS plugin for WordPress
 * Author URI: https://thrivethemes.com
 * Version: 5.15
 * Author: <a href="https://thrivethemes.com">Thrive Themes</a>
 * Text Domain: thrive-apprentice
 * Domain Path: /languages/
 */

register_activation_hook( __FILE__, 'thrive_load' );

function thrive_load() {

	/**
	 * This means TTB exists as a stand-alone theme - lower versions of the theme might cause fatal errors
	 */
	if ( defined( 'THEME_VERSION' ) && THEME_VERSION !== '5.15' && version_compare( THEME_VERSION, '2.4', '<' ) ) {

		tve_dash_show_activation_error( __( 'The current version of Thrive Theme Builder is not compatible with Thrive Apprentice. Please update Thrive Theme Builder before activating Thrive Apprentice', 'thrive-apprentice' ) );
	}

	TVA_Const::$tva_during_activation = true;

	/**
	 * Called on plugin activation.
	 * Check for minimum required WordPress version
	 */
	if ( function_exists( 'tcb_wordpress_version_check' ) && ! tcb_wordpress_version_check() ) {
		/**
		 * Dashboard not loaded yet, force it to load here
		 */
		if ( ! function_exists( 'tve_dash_show_activation_error' ) ) {
			/* Load the dashboard included in this plugin */
			tva_load_dash_version();
			tve_dash_load();
		}

		tve_dash_show_activation_error( 'wp_version', 'Thrive Apprentice', TCB_MIN_WP_VERSION );
	} else if ( method_exists( '\TCB\Lightspeed\Main', 'first_time_enable_lightspeed' ) ) {
		\TCB\Lightspeed\Main::first_time_enable_lightspeed();
	}

	/**
	 * Used to check weather or not to show th notification for new thankyou page system
	 */
	update_option( TVA_Sendowl_Settings::SHOW_THANKYOU_TUTORIAL, 0 );

	tva_init();

	TVA_Const::$tva_during_activation = false;

	/* make sure the rewrite rules are flushed on next run */
	delete_option( 'tva_flush_rewrite_rules_version' );
}

require_once dirname( __FILE__ ) . '/init.php';

if ( is_admin() ) {
	require_once dirname( __FILE__ ) . '/admin/init.php';
}
