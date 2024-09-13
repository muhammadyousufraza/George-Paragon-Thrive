<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-menu-element.php';

/**
 * Class TCB_Menu_Element
 */
class TCB_Megamenu_Element extends TCB_Menu_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Simple Mega Menu', 'thrive-cb' );
	}

	/**
	 * Hide element from panel
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-mega-std';
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'top_level',
					'selector' => '.thrive-shortcode-html ul.tve_w_menu > li',
					'element'  => '.thrive-shortcode-html li',
					'name'     => __( 'Top Level Items', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'dropdowns',
					'selector' => '.tcb-mega-drop-inner',
					'element'  => '.tcb-mega-drop-inner',
					'name'     => __( 'All Dropdowns', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Dropdown %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'dropdown-columns',
					'selector' => '.tcb-mega-drop-inner li.lvl-1',
					'element'  => '.tcb-mega-drop-inner li.lvl-1',
					'name'     => __( 'All Dropdown Columns', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Column %s', 'thrive-cb' ),
				),
				array(
					'value'     => 'menu-descriptions',
					'selector'  => '.tcb-mega-drop-inner li.lvl-1 > .thrv_text_element',
					'element'   => '.tcb-mega-drop-inner li.lvl-1 > .thrv_text_element',
					'name'      => __( 'All Menu Descriptions', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular'  => __( '-- Menu Description %s', 'thrive-cb' ),
					'no_unlock' => true, // mark the fact that this type of element cannot be unlocked
				),
				array(
					'value'    => 'menu-images',
					'selector' => '.tcb-mega-drop-inner li.lvl-1 .tcb-mm-image:not(.tcb-elem-placeholder)',
					'element'  => '.tcb-mega-drop-inner li.lvl-1 .tcb-mm-image',
					'name'     => __( 'All Menu Images', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Menu Image %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'menu-images',
					'selector' => '.tcb-menu-item-image:not(.tcb-elem-placeholder)',
					'element'  => '.tcb-menu-item-image',
					'name'     => __( 'All Menu Images', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Menu Image %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'second-lvl',
					'selector' => '.tcb-mega-drop-inner > ul > li > a',
					'element'  => '.tcb-mega-drop-inner > ul > li > a',
					'name'     => __( 'Second Level Items', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'third-lvl',
					'selector' => '.tcb-mega-drop li li a',
					'element'  => '.tcb-mega-drop li li a',
					'name'     => __( 'Third Level Items', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Item %s', 'thrive-cb' ),
				),
			),
		);
	}

	/**
	 * Use another set of cloud templates - since the revamp
	 *
	 * @return string
	 */
	public function get_template_tag() {
		return 'megamenu';
	}

	public function inherit_components_from() {
		return 'menu';
	}
}
