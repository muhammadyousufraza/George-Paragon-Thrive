<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Calendar_Widget_Element
 */
class Thrive_Calendar_Widget_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Calendar Widget', 'thrive-theme' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'theme-calendar';
	}

	/**
	 * WordPress element identifier
	 * Parent comment will always have depth-1
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-calendar-widget';
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html() {
		return Thrive_Shortcodes::get_calendar();
	}

	/**
	 * This element is a shortcode
	 * @return bool
	 */
	public function is_shortcode() {
		return true;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public static function shortcode() {
		return 'thrive_calendar_widget';
	}
}

return new Thrive_Calendar_Widget_Element( 'thrive_calendar_widget' );
