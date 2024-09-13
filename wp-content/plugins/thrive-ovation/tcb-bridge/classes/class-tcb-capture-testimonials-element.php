<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Capture_Testimonials
 */
class TCB_Capture_Testimonials extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Capture Testimonials', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'thrive, ovation';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'capture_testimonials';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_tvo_capture_testimonials';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'ovation_capture' => array(
				'config' => array(
					'ButtonColor' => array(
						'config'  => array(
							'default' => 'f00',
							'label'   => __( 'Button Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-form-button',
					),
				),
			),
			'typography'      => array( 'hidden' => true ),
			'background'      => array( 'hidden' => true ),
			'borders'         => array( 'hidden' => true ),
			'animation'       => array( 'hidden' => true ),
			'shadow'          => array( 'hidden' => true ),
			'layout'          => array(
				'disabled_controls' => array(),
			),
		);
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		ob_start();
		include dirname( __FILE__ ) . '/../templates/tcb-capture-element.php';
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return static::get_thrive_integrations_label();
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
				'url'  => 'capture_testimonials',
				'link' => 'https://help.thrivethemes.com/en/articles/4426037-how-to-capture-testimonials-with-thrive-ovation',
			),
		);
	}

	public function hide() {
		return true;
	}
}
