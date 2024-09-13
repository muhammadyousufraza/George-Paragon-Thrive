<?php
/**
 * FileName  class-thrive-comments-version-check.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class Tho_Version_Check
 */
class Thrive_Comments_Version_Check {

	/**
	 * Tho_Version_Check constructor.
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'check_version' ) );

		// Don't run anything else in the plugin, if we're on an incompatible WordPress version
		if ( ! self::compatible_version() ) {
			return;
		}
	}

	/**
	 * The primary sanity check, automatically disable the plugin on activation if it doesn't meet minimum requirements.
	 */
	static function activation_check() {
		if ( ! self::compatible_version() ) {
			deactivate_plugins( Thrive_Comments_Constants::PLUGIN_BASENAME );
			wp_die( __( 'This plugin requires WordPress ' . Thrive_Comments_Constants::TCM_MIN_REQUIRED_WP_VERSION . ' or higher!', Thrive_Comments_Constants::T ) );
		}
	}

	/**
	 * The backup sanity check, in case the plugin is activated in a weird way, or the versions change after activation.
	 */
	function check_version() {
		if ( ! self::compatible_version() ) {
			if ( is_plugin_active( Thrive_Comments_Constants::PLUGIN_BASENAME ) ) {
				deactivate_plugins( Thrive_Comments_Constants::PLUGIN_BASENAME );
				add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}

	/**
	 * Display error notice and link to update
	 */
	function disabled_notice() {
		echo '<div class="error notice"><p>' .
		     esc_html__( 'This plugin requires WordPress ' . Thrive_Comments_Constants::TCM_MIN_REQUIRED_WP_VERSION . ' or higher! ', Thrive_Comments_Constants::T ) .
		     '<a href="' . admin_url( 'update-core.php' ) . '">' . __( 'Please update now.', Thrive_Comments_Constants::T ) . '</a>' .
		     '</p></div>';
	}

	/**
	 * @return bool
	 */
	static function compatible_version() {
		if ( version_compare( $GLOBALS['wp_version'], Thrive_Comments_Constants::TCM_MIN_REQUIRED_WP_VERSION, '<' ) ) {
			return false;
		}

		return true;
	}
}

global $tho_version_check;
$tho_version_check = new Thrive_Comments_Version_Check();

register_activation_hook( __FILE__, array( 'Thrive_Comments_Version_Check', 'activation_check' ) );
