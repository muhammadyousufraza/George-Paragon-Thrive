<?php


class TCB_User_Profile_Element extends TCB_Cloud_Template_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'User Profile', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'user_profile';
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'profile,user';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-user-profile';
	}

	/**
	 * Whether or not this element is a placeholder
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_integrations_label();
	}

	public function hide() {
		/**
		 * By default this is hidden
		 */
		return apply_filters( 'tve_user_profile_hidden', true );
	}

	public function own_components() {

		$components = array(
			'user_profile'     => array(
				'config' => array(
					'UserProfilePalette' => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'FieldsControl'      => [
						'config' => [
							'sortable'       => true,
							'settings_icon'  => 'pen-light',
							'default_fields' => TCB_User_Profile_Handler::DEFAULT_FIELDS,
						],
					],
					'FieldsLabel'        => array(
						'config'  => array(
							'name'    => __( 'Field label location', 'thrive-cb' ),
							'buttons' => array(
								array(
									'value'   => 'top',
									'text'    => __( 'Above', 'thrive-cb' ),
									'default' => true,
								),
								array(
									'value' => 'left',
									'text'  => __( 'Left', 'thrive-cb' ),
								),
								array(
									'value' => 'hidden',
									'text'  => __( 'Hidden', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'Width'              => array(
						'config'  => array(
							'default' => '0',
							'min'     => '10',
							'max'     => '500',
							'label'   => __( 'Input column width', 'thrive-cb' ),
							'um'      => [ '%', 'px' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
				),
			),
			'layout'           => [
				'disabled_controls' => [
					'Overflow',
					'ScrollStyle',
				],
			],
			'styles-templates' => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'typography'       => [ 'hidden' => true ],
		);

		return array_merge(
			$components,
			$this->group_component()
		);
	}

	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'all_item',
					'selector' => ' .tve-up-item',
					'name'     => __( 'Form items', 'thrive-cb' ),
					'singular' => __( '-- Form item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_fields',
					'selector' => ' .tve-up-input',
					'name'     => __( 'Form inputs', 'thrive-cb' ),
					'singular' => __( '-- Form input %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_labels',
					'selector' => ' .thrv_text_element',
					'name'     => __( 'Form labels', 'thrive-cb' ),
					'singular' => __( '-- Form label %s', 'thrive-cb' ),
				),
			),
		);
	}
}
