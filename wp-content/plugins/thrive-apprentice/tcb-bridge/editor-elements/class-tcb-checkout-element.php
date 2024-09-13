<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/15/2018
 * Time: 1:51 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class TCB_Checkout_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'checkout';
	}


	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Checkout', 'thrive-apprentice' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'shopping-cart-light';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-checkout';
	}

	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_integrations_label();
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$content = '';
		ob_start();
		include TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/checkout.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Construct payment provider options
	 *
	 * @return array
	 */
	private function get_payment_provider() {

		$return   = array();
		$return[] = array( 'value' => '', 'name' => __( 'None', 'thrive-apprentice' ) );
		$apis     = Thrive_Dash_List_Manager::get_available_apis( true, [ 'only_names' => true ] );

		if ( ! empty( $apis['sendowl'] ) ) {
			$return[] = array( 'value' => $apis['sendowl'], 'name' => $apis['sendowl'] );
		}

		return $return;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$checkout = array(
			'checkout'   => array(
				'config' => array(
					'AddRemoveLabels'  => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Labels', 'thrive-apprentice' ),
							'default' => true,
						),
						'css_suffix' => ' label',
						'css_prefix' => '',
						'extends'    => 'Switch',
					),
					'payment_provider' => array(
						'config'  => array(
							'name'        => __( 'Payment Platform', 'thrive-apprentice' ),
							'label_col_x' => 12,
							'options'     => $this->get_payment_provider(),
						),
						'extends' => 'Select',
					),
				),
			),
			'typography' => array(
				'hidden' => true,
			),
			'animation'  => array(
				'hidden' => true,
			),
		);

		return array_merge( $checkout, $this->group_component() );
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {

		return array(
			'exit_label'    => __( 'Exit Group Styling', 'thrive-apprentice' ),
			'select_values' => array(
				array(
					'value'    => 'all_form_items',
					'selector' => '.tve-form-item',
					'name'     => __( 'Grouped Form Items', 'thrive-apprentice' ),
					'singular' => __( '-- Form Item %s', 'thrive-apprentice' ),
				),
				array(
					'value'    => 'all_inputs',
					'selector' => '.tve-form-input',
					'name'     => __( 'Grouped Inputs', 'thrive-apprentice' ),
					'singular' => __( '-- Input %s', 'thrive-apprentice' ),
				),
				array(
					'value'    => 'all_labels',
					'selector' => '.tve-form-item label',
					'name'     => __( 'Grouped Labels', 'thrive-apprentice' ),
					'singular' => __( '-- Label %s', 'thrive-apprentice' ),
				),
				array(
					'value'    => 'all_submit_buttons',
					'selector' => '.tve-form-submit',
					'name'     => __( 'Submit Buttons', 'thrive-apprentice' ),
					'singular' => __( '-- Label %s', 'thrive-apprentice' ),
				),
				array(
					'value'    => 'all_back_buttons',
					'selector' => '.tcb-go-back',
					'name'     => __( 'Go Back Buttons', 'thrive-apprentice' ),
					'singular' => __( '-- Label %s', 'thrive-apprentice' ),
				),
				array(
					'value'    => 'all_back_icons',
					'selector' => '.tcb-go-back .thrv_icon',
					'name'     => __( 'Go Back Icon', 'thrive-apprentice' ),
					'singular' => __( '-- Label %s', 'thrive-apprentice' ),
				),
				array(
					'value'    => 'all_back_primary_text',
					'selector' => '.tcb-go-back .thrv-inline-text',
					'name'     => __( 'Go Back Text', 'thrive-apprentice' ),
					'singular' => __( '-- Label %s', 'thrive-apprentice' ),
				),
			),
		);
	}
}