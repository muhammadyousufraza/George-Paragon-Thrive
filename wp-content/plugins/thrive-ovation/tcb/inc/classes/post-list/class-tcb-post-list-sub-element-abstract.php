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
 * Class TCB_Post_List_Sub_Element_Abstract
 */
abstract class TCB_Post_List_Sub_Element_Abstract extends TCB_Element_Abstract {

	/**
	 * Thrive_Theme_Element_Abstract constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_element_' . $this->tag() . '_config', [ $this, 'add_config' ] );
	}

	public function add_config( $config ) {
		$config['shortcode']      = $this->shortcode();
		$config['is_sub_element'] = $this->is_sub_element();

		return $config;
	}

	/**
	 * Mark this as a sub-element
	 *
	 * @return bool
	 */
	public function is_sub_element() {
		return true;
	}

	/**
	 * If an element has a shortcode tag (empty by default, override by children who have shortcode tags).
	 *
	 * @return bool
	 */
	public function shortcode() {
		return '';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return TCB_Post_List::elements_group_label();
	}

	/**
	 * Default components that most post list sub-elements use
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix = tcb_selection_root() . ' ';

		return [
			'styles-templates' => [ 'hidden' => true ],
			'animation'        => [ 'disabled_controls' => [ '.btn-inline.anim-link' ] ],
			'typography'       => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
				],
				'config'            => [
					'css_suffix'    => '',
					'css_prefix'    => '',
					'TextShadow'    => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'FontColor'     => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'FontSize'      => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'TextStyle'     => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'LineHeight'    => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'FontFace'      => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'LetterSpacing' => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'TextAlign'     => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
					'TextTransform' => [
						'css_suffix' => '',
						'css_prefix' => $prefix,
					],
				],
			],
		];
	}
}
