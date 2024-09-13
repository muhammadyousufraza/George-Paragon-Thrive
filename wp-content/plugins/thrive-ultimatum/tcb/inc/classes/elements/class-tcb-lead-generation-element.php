<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TCB_Lead_Generation_Element
 */
class TCB_Lead_Generation_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Lead Generation', 'thrive-cb' );
	}

	public function is_placeholder() {
		return false;
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'       => $this->icon(),
			'class'      => 'tcb-ct-placeholder',
			'title'      => $title,
			'extra_attr' => 'data-ct="' . $this->tag() . '-0" data-tcb-elem-type="' . $this->tag() . '" data-tcb-lg-type="' . $this->tag() . '" data-specific-modal="lead-generation"',
		), true );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'form';
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'lead_gen';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.thrv_lead_generation';
	}

	/**
	 * @return array
	 */
	public function get_captcha_credentials() {

		$credentials = Thrive_Dash_List_Manager::credentials( 'recaptcha' );

		return ! empty( $credentials['site_key'] ) ? $credentials : [];
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$credentials     = $this->get_captcha_credentials();
		$lead_generation = array(
			'lead_generation'  => array(
				'config' => array(
					'ModalPicker'         => array(
						'config' => array(
							'label' => __( 'Template', 'thrive-cb' ),
						),
					),
					'FormPalettes'        => [
						'config'  => [],
						'extends' => 'Palettes',
					],
					'connectionType'      => array(
						'config' => array(
							'name'    => __( 'Connection', 'thrive-cb' ),
							'buttons' => [
								[
									'text'    => 'API',
									'value'   => 'api',
									'default' => true,
								],
								[
									'text'  => 'HTML code',
									'value' => 'custom-html',
								],
							],
						),
					),
					'FieldsControl'       => [
						'config' => [
							'sortable'      => true,
							'settings_icon' => 'pen-light',
						],
					],
					'HiddenFieldsControl' => [
						'config'  => [
							'sortable'      => false,
							'settings_icon' => 'pen-light',
						],
						'extends' => 'PreviewList',
					],
					'ApiConnections'      => [
						'config' => [],
					],
					'Captcha'             => array(
						'config'  => array(
							'name'                 => '',
							'label'                => __( 'Captcha spam prevention', 'thrive-cb' ),
							'default'              => false,
							'site_key'             => ! empty( $credentials ) ? $credentials['site_key'] : '',
							'version'              => ! empty( $credentials ) && ! empty( $credentials['connection'] ) ? $credentials['connection']['version'] : '',
							'use_browsing_history' => ! empty( $credentials ) && ! empty( $credentials['connection'] ) && ! empty( $credentials['connection']['browsing_history'] ) ? 1 : '',
						),
						'extends' => 'Switch',
					),
					'consent'             => array(
						'config' => array(
							'labels' => array(
								'wordpress' => __( 'Create WordPress account', 'thrive-cb' ),
								'default'   => __( '{service}', 'thrive-cb' ),
							),
						),
					),
					'FormIdentifier'      => array(
						'config'  => array(
							'label'        => __( 'Form identifier', 'thrive-cb' ),
							'full-width'   => true,
							'tooltip'      => __( 'Used in other Thrive plugins to identify this form. It should be unique.', 'thrive-cb' ),
							'tooltip_side' => 'top',
							'width'        => '100%',
						),
						'extends' => 'LabelInput',
					),
				),
			),
			'typography'       => [
				'hidden' => true,
			],
			'layout'           => [
				'disabled_controls' => [
					'.tve-advanced-controls',
				],
				'config'            => [
					'Width' => [
						'important' => true,
					],
				],
			],
			'borders'          => [
				'disabled_controls' => [],
				'config'            => [
					'Corners' => [
						'overflow' => false,
					],
				],
			],
			'animation'        => [
				'hidden' => true,
			],
			'shadow'           => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'styles-templates' => [
				'config' => [
					'ID' => [
						'hidden' => true,
					],
				],
			],
		);

		return array_merge( $lead_generation, $this->group_component() );
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'lead_generation',
				'link' => 'https://help.thrivethemes.com/en/articles/4425779-how-to-use-the-lead-generation-element',
			],
		];
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
					'value'    => 'all_labels',
					'selector' => '.thrv_text_element[data-label-for]',
					'name'     => __( 'Grouped Lead Generation Labels', 'thrive-cb' ),
					'singular' => __( '-- Label %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_lead_gen_items',
					'selector' => '.tve_lg_input,.tve_lg_textarea',
					'name'     => __( 'Grouped Lead Generation Inputs', 'thrive-cb' ),
					'singular' => __( '-- Input %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_radio_elements',
					'selector' => '.tve_lg_radio',
					'name'     => __( 'Grouped Lead Generation Radio', 'thrive-cb' ),
					'singular' => __( '-- Radio %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_checkbox_elements',
					'selector' => '.tve_lg_checkbox:not(.tcb-lg-consent)',
					'name'     => __( 'Grouped Form Checkbox', 'thrive-cb' ),
					'singular' => __( '-- Checkbox %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_dropdown_elements',
					'selector' => '.tve_lg_dropdown',
					'name'     => __( 'Grouped Dropdown', 'thrive-cb' ),
					'singular' => __( '-- Dropdown %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'radio_options',
					'selector' => '.tve_lg_radio_wrapper',
					'name'     => __( 'Grouped Radio Options', 'thrive-cb' ),
					'singular' => __( '-- Option %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'dropdown_options',
					'selector' => '.tve-lg-dropdown-option',
					'name'     => __( 'Grouped Dropdown Options', 'thrive-cb' ),
					'singular' => __( '-- Option %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'checkbox_options',
					'selector' => '.tve_lg_checkbox_wrapper:not(.tcb-lg-consent .tve_lg_checkbox_wrapper)',
					'name'     => __( 'Grouped Checkbox Options', 'thrive-cb' ),
					'singular' => __( '-- Option %s', 'thrive-cb' ),
				),
			),
		);
	}
}
