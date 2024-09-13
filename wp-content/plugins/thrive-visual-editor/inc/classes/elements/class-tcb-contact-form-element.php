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
 * Class TCB_Contact_Form_Element
 *
 * Element class
 */
class TCB_Contact_Form_Element extends TCB_Cloud_Template_Element_Abstract {

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

	/**
	 * Name of the Element in sidebar
	 *
	 * @return string
	 */
	public function name() {

		return __( 'Contact Form (Old)', 'thrive-cb' );
	}

	/**
	 * Which svg symbol id to use
	 *
	 * @return string
	 */
	public function icon() {

		return 'contact_form';
	}

	/**
	 * When element is selected in editor this identifier
	 * establishes element _type
	 *
	 * @return string
	 * @see TVE.main.element_selected() TVE._type()
	 *
	 */
	public function identifier() {

		return '.thrv-contact-form';
	}

	protected function html() {
		return tcb_template( 'elements/' . $this->tag() . '.php', $this, true );
	}

	/**
	 * Filters the Contact Form Input Types
	 *
	 * @return array
	 */
	public function get_types() {
		require_once dirname( dirname( __FILE__ ) ) . '/class-tcb-contact-form.php';

		$types = TCB_Contact_Form::get_types();

		foreach ( $types as $key => $value ) {
			if ( ! empty( $value['validation_error'] ) ) {
				unset( $types[ $key ]['validation_error'] );
			}
		}

		return $types;
	}

	public function own_components() {

		$contact_form = array(

			'contact_form' => array(
				'config' => array(
					'FieldsControl'          => array(
						'config' => array(
							'sortable'      => true,
							'settings_icon' => 'edit',
							'types'         => $this->get_types(),
						),
					),
					'AddRemoveLabels'        => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Labels', 'thrive-cb' ),
							'default' => true,
						),
						'css_suffix' => ' label',
						'css_prefix' => '',
						'extends'    => 'Switch',
					),
					'AddRemoveRequiredMarks' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Required Marks', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'ZapierConnection'       => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Connect to Zapier', 'thrive-cb' ),
							'icon'    => 'zapier-logo',
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'ZapierTags'             => array(
						'config' => array(
							'label' => __( 'Tags', 'thrive-cb' ),
						),
					),
					'ZapierIp'               => array(
						'config' => array(
							'label' => __( 'Send IP Address', 'thrive-cb' ),
						),
					),
				),
			),
			'typography'   => [
				'hidden' => true,
			],
			'animation'    => [
				'hidden' => true,
			],
			'layout'       => [
				'config'            => [
					'Width' => [
						'important' => true,
					],
				],
				'disabled_controls' => [
					'Overflow',
					'ScrollStyle',
				],
			],
		);

		return array_merge( $contact_form, $this->group_component() );
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
					'value'    => 'all_cf_items',
					'selector' => '.tve-cf-item',
					'name'     => __( 'Grouped Form Items', 'thrive-cb' ),
					'singular' => __( '-- Form Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_inputs',
					'selector' => '.tve-cf-input',
					'name'     => __( 'Grouped Inputs', 'thrive-cb' ),
					'singular' => __( '-- Input %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_labels',
					'selector' => '.tve-cf-item label',
					'name'     => __( 'Grouped Labels', 'thrive-cb' ),
					'singular' => __( '-- Label %s', 'thrive-cb' ),
				),
			),
		);
	}

	public function is_placeholder() {
		return false;
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
				'url'  => 'contact_form',
				'link' => 'https://help.thrivethemes.com/en/articles/4430139-how-to-use-the-contact-form-element',
			],
		];
	}
}
