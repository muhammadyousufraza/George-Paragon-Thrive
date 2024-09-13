<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Login_Editor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Body_Wrapper
 *
 * @package TVD\Login_Editor
 */
class Body_Wrapper extends \TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'WordPress Login Page', 'thrive-dash' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return 'body';
	}

	public function own_components() {

		$typography_suffix = array(
			'css_suffix' => array( ' a', ' p', ' label', ' input', ' #login_error' ),
			'important'  => true,
		);

		$components = array(
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'borders'          => array(),
			'shadow'           => array(),
			'background'       => array(
				'disabled_controls' => array( '.video-bg' ),
			),
			'typography'       => array(
				'config' => array(
					'LineHeight'    => $typography_suffix,
					'FontFace'      => $typography_suffix,
					'TextTransform' => $typography_suffix,
					'TextStyle'     => $typography_suffix,
					'TextAlign'     => $typography_suffix,
					'FontColor'     => $typography_suffix,
					'LetterSpacing' => $typography_suffix,
					'FontSize'      => $typography_suffix,
				),
			),
		);

		$components['layout']['disabled_controls'] = array( 'Display', 'Alignment', '.tve-advanced-controls' );

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new Body_Wrapper( 'tvd_login_screen' );
