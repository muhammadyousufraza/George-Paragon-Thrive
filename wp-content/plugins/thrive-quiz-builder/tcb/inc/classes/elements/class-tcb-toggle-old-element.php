<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Toggle_Element
 */
class TCB_Toggle_Old_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Toggle', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'toggle';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_toggle_shortcode';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'toggle_old' => array(
				'config' => array(
					'HoverColor'      => array(
						'css_suffix' => ' > .tve_faq:hover',
						'config'     => array(
							'default' => '000',
							'label'   => __( 'Hover Color', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends'    => 'ColorPicker',
					),
					'HoverTextColor'  => array(
						'css_suffix'      => ' > .tve_faq:hover > .tve_faqI > .tve_faqB h4:not(.tve_toggle_open_text)',
						'icon_css_suffix' => ' > .tve_faq:hover > .tve_faqI > .tve_faqB svg:not(.tve_toggle_open)',
						'css_prefix'      => tcb_selection_root() . ' ',
						'config'          => array(
							'default' => '000',
							'label'   => __( 'Hover Text Color', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends'         => 'ColorPicker',
					),
					'ToggleTextColor' => array(
						'css_suffix'      => ' > .tve_faq > .tve_faqI > .tve_faqB h4',
						'icon_css_suffix' => ' > .tve_faq > .tve_faqI > .tve_faqB',
						'config'          => array(
							'default' => '000',
							'label'   => __( 'Text Color', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends'         => 'ColorPicker',
					),
					'Toggle'          => [],
				),
			),
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'inner', 'text' ],
				],
			],
			'borders'    => [
				'disabled_controls' => [ 'Corners', 'hr' ],
				'config'            => [],
			],
			'typography' => [ 'hidden' => true ],
			'background' => [ 'hidden' => true ],
			'animation'  => [ 'hidden' => true ],
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}

	public function hide() {
		return true;
	}
}
