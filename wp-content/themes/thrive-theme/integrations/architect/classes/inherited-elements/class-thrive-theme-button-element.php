<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Theme_Button_Element - inherit the Button element from TCB and adapt some stuff so it's easier to use by the theme buttons.
 */
class Thrive_Theme_Button_Element extends TCB_Button_Element {
	/**
	 * Theme Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . THRIVE_THEME_BUTTON_CLASS . '-identifier';
	}

	/**
	 * Thrive_Theme_Button_Element constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_element_' . $this->tag() . '_config', function ( $config ) {
			/* these fields are not in the config array, so they have to be added to the array */
			$config['is_shortcode']    = $this->is_shortcode();
			$config['has_selector']    = $this->has_selector();
			$config['has_icons']       = $this->has_icons();
			$config['is_theme_button'] = $this->is_theme_button();

			return $config;
		} );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$parent_components = parent::own_components();

		$parent_components['borders']['config']['Borders']['important'] = true;
		$parent_components['borders']['config']['Corners']['important'] = true;

		$components = [
			THRIVE_THEME_BUTTON_COMPONENT => $parent_components['button'],
			'background'                  => $parent_components['background'],
			'borders'                     => $parent_components['borders'],
			'layout'                      => [
				'config'            => $parent_components['layout']['config'],
				/* disable some layout controls */
				'disabled_controls' => [ 'Alignment', 'Display', 'Float' ],
			],
			'typography'                  => $parent_components['typography'],
			'shadow'                      => $parent_components['shadow'],
			'animation'                   => [
				'config'            => $parent_components['animation']['config'],
				'disabled_controls' => [ '.btn-inline.anim-link' ],
			],
			'shared-styles'               => [
				'order'  => 1,
				'hidden' => false,
			],
			'scroll'                      => [ 'hidden' => true ],
		];

		return $components;
	}

	/**
	 * Toggles the given theme_button controls for the inheriting elements that use / don't use them.
	 *
	 * @param $components
	 * @param $control_names - this has to be an array
	 * @param $enable
	 *
	 * @return array
	 */
	public function toggle_button_controls( $components, $control_names, $enable = true ) {
		$disabled_controls = $components[ THRIVE_THEME_BUTTON_COMPONENT ]['disabled_controls'];

		/* add the controls to the disabled controls OR remove the controls from the disabled controls */
		$disabled_controls = $enable ? array_diff( $disabled_controls, $control_names ) : array_merge( $disabled_controls, $control_names );

		/* re-assign the new disabled controls */
		$components[ THRIVE_THEME_BUTTON_COMPONENT ]['disabled_controls'] = $disabled_controls;

		return $components;
	}

	/**
	 * Toggles the 'Align' control, for the inheriting elements that use it / don't use it.
	 * This is a special case because it involves renaming the control label too.
	 *
	 * @param $components
	 * @param $enable
	 *
	 * @return array
	 */
	public function toggle_align_control( $components, $enable = true ) {
		$components = $this->toggle_button_controls( $components, [ 'Align' ], $enable );

		/* since we removed the 'Align' control, we also have to delete it from the control display name (or add it back, for enable=true) */
		$components[ THRIVE_THEME_BUTTON_COMPONENT ]['config']['ButtonSize']['config']['name'] = $enable ? __( 'Size and Alignment', 'thrive-theme' ) : __( 'Size', 'thrive-theme' );

		return $components;
	}

	/**
	 * Check if this element behaves like a shortcode.
	 *
	 * @return bool
	 */
	public function is_shortcode() {
		return false;
	}

	/**
	 * If an element has a selector, the data-css will not be generated.
	 *
	 * @return bool
	 */
	public function has_selector() {
		return false;
	}

	/**
	 * Set the element to have icons or not (Drag, Save as Symbol, Copy, Delete).
	 *
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}

	/**
	 * Hide this.
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Check if the element is a theme button or not.
	 *
	 * @return bool
	 */
	public function is_theme_button() {
		return true;
	}
}

return new Thrive_Theme_Button_Element( THRIVE_THEME_BUTTON_COMPONENT );
