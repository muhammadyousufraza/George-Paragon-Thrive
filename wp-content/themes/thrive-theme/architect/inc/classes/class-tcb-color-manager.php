<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Color_Manager
 *
 * @project  : thrive-visual-editor
 */
class TCB_Color_Manager {

	/**
	 * @var TCB_Color_Manager
	 */
	private static $instance;

	/**
	 * @var boolean
	 */
	private $is_multisite = false;

	/**
	 * Dynamically computed depending on the context
	 *
	 * Modified in Theme/Content/Landing Builder websites to suite our needs
	 *
	 * @var string
	 */
	private $option_name;

	/**
	 * TCB_Color_Manager constructor.
	 */
	private function __construct() {
		$this->is_multisite = apply_filters( 'tcb_allow_global_colors_multisite', false );
		$this->option_name  = apply_filters( 'tcb_global_colors_option_name', 'thrv_global_colours' );
	}

	/**
	 * @return TCB_Color_Manager
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Updates the color list with the provided color array
	 *
	 * @param array $colors
	 */
	public function update_list( $colors = [] ) {
		if ( $this->is_multisite ) {
			update_site_option( $this->option_name, $colors );
		} else {
			update_option( $this->option_name, $colors );
		}
	}

	/**
	 * Returns the color list
	 *
	 * @return array
	 */
	public function get_list() {

		if ( $this->is_multisite ) {
			$colors = get_site_option( $this->option_name, [] );
		} else {
			$colors = get_option( $this->option_name, [] );
		}

		/**
		 * Allow other functionality to be injected here to append or modify the color list
		 *
		 * Used in ThriveTheme Builder Website
		 *
		 * @param array $colors
		 */
		return apply_filters( 'tcb_global_colors_list', $colors );
	}
}

function tcb_color_manager() {
	return TCB_Color_Manager::get_instance();
}
