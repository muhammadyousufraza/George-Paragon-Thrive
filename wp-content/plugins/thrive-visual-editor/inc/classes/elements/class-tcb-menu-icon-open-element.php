<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TCB_Menu_Icon_Element_Abstract', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-menu-icon-element-abstract.php';
}

/**
 * Class TCB_Menu_Icon_Open_Element
 */
class TCB_Menu_Icon_Open_Element extends TCB_Menu_Icon_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Open Menu Icon', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-icon-open';
	}
}
