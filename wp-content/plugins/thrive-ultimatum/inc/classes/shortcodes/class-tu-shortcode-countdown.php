<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TU_Shortcode_Countdown {

	protected static $instance;

	/**
	 * Protected construct to ensure the singleton pattern
	 *
	 * TU_Shortcode_Countdown constructor.
	 */
	protected function __construct() {

	}

	/**
	 *  Access to instance
	 *
	 * @return TU_Shortcode_Countdown
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Generates the shortcode code
	 *
	 * @param $campaign
	 * @param $design
	 *
	 * @return string
	 */
	public function code( $campaign, $design ) {
		return '[tu_countdown id=' . $campaign . ' design=' . $design . '][/tu_countdown]';
	}

	/**
	 * Render the placeholder when the shortcode need to be rendered
	 * This placeholder will pe replaced with corresponding HTML based on campaign settings when
	 * the main ajax request is made
	 *
	 * @param $design_id
	 * @param $campaign_id
	 *
	 * @return string
	 */
	public function placeholder( $design_id, $campaign_id ) {
		$class = "tu-shortcode-$design_id";

		$placeholder_style = tve_ultimatum_get_lightspeed_placeholder( $campaign_id, $design_id );

		if ( $placeholder_style !== 'display:none' ) {
			$class .= ' tve-ult-preload-form';
		}

		return '<div style="' . $placeholder_style . '" class="' . $class . '"></div>';
	}

	/**
	 * Generates countdown shortcode
	 *
	 * @param int $campaign
	 * @param int $design
	 *
	 * @return string
	 */
	public function get_code( $campaign, $design ) {
		$campaign = (int) $campaign;
		$design   = (int) $design;

		return '[tu_countdown id=' . $campaign . ' design=' . $design . '][/tu_countdown]';
	}
}
