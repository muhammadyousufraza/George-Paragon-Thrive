<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-leads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Leads_Shortcode_Element extends TCB_Element_Abstract {

	const ICON = 'leads';

	public function __construct() {
		parent::__construct( 'tl_shortcode' );
	}

	public function name() {
		return __( 'Thrive Leads Shortcode', 'thrive-leads' );
	}

	public function identifier() {
		return '.thrive_leads_shortcode';
	}

	public function icon() {
		return self::ICON;
	}

	public function own_components() {
		return array(
			'tl_shortcode'     => array(
				'config' => array(),
			),
			'typography'       => array( 'hidden' => true ),
			'background'       => array( 'hidden' => true ),
			'borders'          => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'shadow'           => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'layout'           => array(
				'disabled_controls' => array(),
			),
		);
	}

	public function category() {
		return $this->get_thrive_integrations_label();
	}

	/**
	 * HTML for the element to be placed on the page
	 *
	 * @return string
	 */
	protected function html() {
		return $this->html_placeholder( __( 'Insert Thrive Leads Shortcode', 'thrive-leads' ) );
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return array(
			'instructions' => array(
				'type' => 'help',
				'url'  => 'leads_shortcode',
				'link' => 'http://help.thrivethemes.com/en/articles/4425952-how-to-build-and-display-your-first-form#building-a-form-with-lead-shortcodes',
			),
		);
	}
}
