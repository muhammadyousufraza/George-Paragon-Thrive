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
 * Class TCB_Pagination_Element
 */
class TCB_Pagination_Element extends TCB_Cloud_Template_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Pagination', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_Pagination::IDENTIFIER;
	}

	/**
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Hide this in the sidebar.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'pagination'       => array(
				'config' => array(
					'Type'               => array(
						'config'  => array(
							'default' => TCB_Pagination::NONE,
							/* if this is the control from the post list, change the name a bit */
							'name'    => __( 'Type', 'thrive-cb' ),
							/* the option list is populated in JS */
							'options' => [],
						),
						'extends' => 'Select',
					),
					'PageNumbersToggle'  => array(
						'config'  => array(
							'label' => __( 'Page Numbers', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'PagesNearCurrent'   => array(
						'config'  => array(
							'default' => '2',
							'min'     => '0',
							'max'     => '10',
							'label'   => __( 'Pages Displayed Near Current Page', 'thrive-cb' ),
							'um'      => [ '' ],
						),
						'extends' => 'Slider',
					),
					'NextPreviousToggle' => array(
						'config'  => array(
							'label' => __( 'Next/Previous', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'FirstLastToggle'    => array(
						'config'  => array(
							'label' => __( 'First/Last', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'LabelToggle'        => array(
						'config'  => array(
							'label' => __( 'Pagination Label', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'PageSpacing'        => array(
						'config'  => array(
							'default' => '10',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Page Number Spacing', 'thrive-cb' ),
							'um'      => [ 'px', 'em' ],
							'css'     => 'margin-left',
						),
						'extends' => 'Slider',
					),
					'Alignment'          => array(
						'config'  => array(
							'default' => 'space-between',
							'name'    => __( 'Alignment', 'thrive-cb' ),
							'options' => [
								[
									'name'  => 'Centered',
									'value' => 'center',
								],
								[
									'name'  => 'Left',
									'value' => 'flex-start',
								],
								[
									'name'  => 'Right',
									'value' => 'flex-end',
								],
								[
									'name'  => 'Space Between',
									'value' => 'space-between',
								],
							],
						),
						'extends' => 'Select',
					),
				),
			),
			'layout'           => [
				'disabled_controls' => [ 'Display' ],
			],
			'animation'        => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'scroll'           => [ 'hidden' => true ],
			'typography'       => [ 'hidden' => true ],
		);

		return array_merge( $components, $this->group_component() );

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
					'value'    => 'prev_next_pagination_buttons',
					'selector' => '.' . TCB_Pagination_Numeric::PREV_NEXT_BUTTON_CLASS,
					'name'     => __( 'Next/Previous Buttons', 'thrive-cb' ),
					'singular' => __( 'Next/Previous Button', 'thrive-cb' ),
				),
				array(
					'value'    => 'prev_next_pagination_button_icons',
					'selector' => '.' . TCB_Pagination_Numeric::PREV_NEXT_BUTTON_CLASS . ' .thrv_icon',
					'name'     => __( 'Next/Previous Button Icons', 'thrive-cb' ),
					'singular' => __( 'Next/Previous Button Icon', 'thrive-cb' ),
				),
				array(
					'value'    => 'prev_next_pagination_button_texts',
					'selector' => '.' . TCB_Pagination_Numeric::PREV_NEXT_BUTTON_CLASS . ' .tcb-button-text',
					'name'     => __( 'Next/Previous Button Texts', 'thrive-cb' ),
					'singular' => __( 'Next/Previous Button Text', 'thrive-cb' ),
				),
				array(
					'value'    => 'first_last_pagination_buttons',
					'selector' => '.' . TCB_Pagination_Numeric::FIRST_LAST_BUTTON_CLASS,
					'name'     => __( 'First/Last Buttons', 'thrive-cb' ),
					'singular' => __( 'First/Last Button', 'thrive-cb' ),
				),
				array(
					'value'    => 'first_last_pagination_buttons_icons',
					'selector' => '.' . TCB_Pagination_Numeric::FIRST_LAST_BUTTON_CLASS . ' .thrv_icon',
					'name'     => __( 'First/Last Button Icons', 'thrive-cb' ),
					'singular' => __( 'First/Last Button Icon', 'thrive-cb' ),
				),
				array(
					'value'    => 'first_last_pagination_buttons_texts',
					'selector' => '.' . TCB_Pagination_Numeric::FIRST_LAST_BUTTON_CLASS . ' .tcb-button-text',
					'name'     => __( 'First/Last Button Texts', 'thrive-cb' ),
					'singular' => __( 'First/Last Button Text', 'thrive-cb' ),
				),
				array(
					'value'     => 'all_pagination_numbers',
					'selector'  => '.tcb-pagination-link',
					'name'      => __( 'Grouped Pagination Numbers', 'thrive-cb' ),
					'singular'  => __( '-- Pagination Number %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
			),
		);
	}
}
