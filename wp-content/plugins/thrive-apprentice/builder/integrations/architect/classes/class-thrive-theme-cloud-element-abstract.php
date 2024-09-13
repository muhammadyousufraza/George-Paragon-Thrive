<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Thrive_Theme_Cloud_Element_Abstract extends TCB_Cloud_Template_Element_Abstract {
	/**
	 * Thrive_Theme_Element_Abstract constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_element_' . $this->tag() . '_config', function ( $config ) {

			$config['is_shortcode'] = $this->is_shortcode();
			$config['has_selector'] = $this->has_selector();
			$config['has_icons']    = $this->has_icons();
			$config['shortcode']    = static::shortcode();

			return $config;
		} );
	}

	/**
	 * Set this to false so we can add a new component for this. ( otherwise you can't add new components )
	 *
	 * @return true
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Check if this element behaves like a shortcode
	 * @return bool
	 */
	public function is_shortcode() {
		return false;
	}

	/**
	 * If an element has selector or a data-css will be generated
	 * @return bool
	 */
	public function has_selector() {
		return false;
	}

	/**
	 * Check if the element has icons or not
	 * @return bool
	 */
	public function has_icons() {
		return true;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return Thrive_Defaults::theme_group_label();
	}

	/**
	 * Name of the shortcode.
	 *
	 * @return bool
	 */
	public static function shortcode() {
		return '';
	}

	/**
	 * Default components that most theme elements use
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'styles-templates' => [ 'hidden' => true ],
			'typography'       => [
				'config' => [
					'css_suffix'    => '',
					'css_prefix'    => '',
					'TextShadow'    => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'FontColor'     => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'FontSize'      => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'TextStyle'     => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'LineHeight'    => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'FontFace'      => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'LetterSpacing' => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'TextAlign'     => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
					'TextTransform' => [
						'css_suffix' => '',
						'css_prefix' => '',
					],
				],
			],
		];
	}
}
