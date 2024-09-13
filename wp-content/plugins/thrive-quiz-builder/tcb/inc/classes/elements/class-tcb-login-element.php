<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Login_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return esc_html__( 'Login & Registration Form', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'login_elem';
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'login,registration,forgot,password,register';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-login-element';
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
		return $this->get_thrive_integrations_label();
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		return
			'<div class="element">' . $this->html_placeholder( 'Insert Login & Registration Form' ) . '</div>' .
			'<div class="template">' . tcb_template( 'elements/login.php', [], true ) . '</div>';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$login = array(
			'login'            => array(
				'config' => array(
					'Palettes'         => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'formType'         => array(
						'config' => array(
							'name'       => __( 'Type', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => [
								[
									'text'    => 'Login',
									'default' => true,
									'value'   => 'login',
								],
								[
									'text'  => 'Register',
									'value' => 'register',
								],
								[
									'text'  => 'Both',
									'value' => 'both',
								],
							],
						),
					),
					'defaultState'     => array(
						'config' => array(
							'name'    => __( 'Default state', 'thrive-cb' ),
							'options' => array(
								'login'    => __( 'Login', 'thrive-cb' ),
								'register' => __( 'Register', 'thrive-cb' ),
							),
						),
					),
					'hideWhenLoggedIn' => array(
						'config' => array(
							'label' => __( 'Hide form when user is logged in', 'thrive-cb' ),
						),
					),
					'PassResetUrl'     => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Password Reset Link', 'thrive-cb' ),
							'default' => true,
						),
						'css_suffix' => ' .tcb-lost-password-link',
						'css_prefix' => '',
						'extends'    => 'Switch',
					),
					'Align'            => array(
						'config'  => array(
							'name'       => __( 'Size and Alignment', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'icon'    => 'a_left',
									'value'   => 'left',
									'tooltip' => __( 'Align Left', 'thrive-cb' ),
								),
								array(
									'icon'    => 'a_center',
									'value'   => 'center',
									'default' => true,
									'tooltip' => __( 'Align Center', 'thrive-cb' ),
								),
								array(
									'icon'    => 'a_right',
									'value'   => 'right',
									'tooltip' => __( 'Align Right', 'thrive-cb' ),
								),
								array(
									'text'    => 'FULL',
									'value'   => 'full',
									'tooltip' => __( 'Full Width', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'FormWidth'        => array(
						'config'  => array(
							'default' => '400',
							'min'     => '10',
							'max'     => '1080',
							'label'   => __( 'Form width', 'thrive-cb' ),
							'um'      => [ '%', 'px' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
				),
			),
			'typography'       => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'styles-templates' => [
				'config' => [
					'ID' => [
						'hidden' => true,
					],
				],
			],
			'scroll'           => [
				'hidden' => false,
			],
			'layout'           => [
				'disabled_controls' => [],
			],
		);

		return array_merge( $login, $this->group_component() );
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return TCB_Login_Element_Handler::get_group_editing_options();
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
				'url'  => 'login_registration',
				'link' => 'https://help.thrivethemes.com/en/articles/4425883-how-to-use-the-login-registration-form-element',
			],
		];
	}
}
