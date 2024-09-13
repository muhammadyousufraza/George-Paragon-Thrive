<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Display_Testimonials
 */
class TCB_Display_Testimonials extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Display Testimonials', 'thrive-cb' );
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
		return 'display_testimonials';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_tvo_display_testimonials';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'ovation_display' => array(
				'config' => array(
					'BackgroundColor'     => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'background-color',
							'label'   => __( 'Background Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-apply-background',
					),
					'BorderColor'         => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'border-color',
							'label'   => __( 'Border Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-apply-background',
					),
					'TitleColor'          => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'color',
							'label'   => __( 'Title Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-testimonials-display h4',
					),
					'TextColor'           => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'color',
							'label'   => __( 'Text Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-testimonials-display p',
					),
					'QuoteColor'          => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'color',
							'label'   => __( 'Quote Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-testimonial-quote',
					),
					'NameColor'           => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'color',
							'label'   => __( 'Name Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-testimonial-name',
					),
					'RoleColor'           => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'color',
							'label'   => __( 'Role Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-testimonial-role',
					),
					'InfoBackgroundColor' => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'background-color',
							'label'   => __( 'Info background Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-info-background',
					),
					'InfoBorderColor'     => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'border-color',
							'label'   => __( 'Info border Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-info-background,.tvo-info-border',
					),
					'QuoteBackground'     => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'background-color',
							'label'   => __( 'Quote background', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-quote-background',
					),
					'SeparatorBackground' => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'border-color',
							'label'   => __( 'Separator background', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-testimonial-separator-bg',
					),
					'ImageBorderColor'    => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'border-color',
							'label'   => __( 'Image border color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tvo-testimonial-real-border',
					),
					'ArrowsColor'         => array(
						'config'  => array(
							'default' => 'f00',
							'style'   => 'color',
							'label'   => __( 'Arrows color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.thrlider-next,.thrlider-prev',
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
		include dirname( __FILE__ ) . '/../templates/tcb-display-element.php';
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
				'url'  => 'display_testimonials',
				'link' => 'https://help.thrivethemes.com/en/articles/4426038-how-to-display-your-thrive-ovation-testimonials',
			),
		);
	}

	public function hide() {
		return true;
	}
}
