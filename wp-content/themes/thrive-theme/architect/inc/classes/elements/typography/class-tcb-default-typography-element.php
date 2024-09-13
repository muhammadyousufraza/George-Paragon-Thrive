<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * General element representing each of the individually stylable typography elements
 *
 * Class TCB_Default_Typography_Element
 */
class TCB_Default_Typography_Element extends TCB_Element_Abstract {

	/**
	 * Whether or not the current post is a landing page. Might come in handy
	 *
	 * @var bool|false|string
	 */
	protected $is_landing_page = false;

	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		$this->is_landing_page = tcb_post()->is_landing_page();
	}

	public function name() {
		return $this->is_landing_page ? __( 'Landing Page Text Element', 'thrive-cb' ) : __( 'Text Element', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-typography';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	protected function general_components() {
		$components = parent::general_components();
		foreach ( $components['typography']['config'] as $control => &$config ) {
			$config['css_suffix'] = $config['css_prefix'] = '';
		}

		return array(
			'default_typography' => $components['typography'],
			'layout'             => array_merge(
				$components['layout'],
				array(
					'disabled_controls' => [ 'Width', 'Height', 'hr', 'Alignment', 'Display', '.tve-advanced-controls' ],
					'config'            => [
						'MarginAndPadding' => [
							'important' => false,
						],
					],
				)
			),
			'background'         => $components['background'],
			'borders'            => $components['borders'],
			'shadow'             => array_merge(
				$components['shadow'],
				array(
					'config' => [
						'disabled_controls' => [ 'inner', 'drop' ],
					],
				)
			),
		);
	}
}
