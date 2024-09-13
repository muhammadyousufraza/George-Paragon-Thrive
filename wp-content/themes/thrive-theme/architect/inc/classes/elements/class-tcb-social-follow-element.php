<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Social_Follow_Element
 */
class TCB_Social_Follow_Element extends TCB_Social_Element {
	/**
	 * Element name
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Social Follow', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'business-social-links';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive_author_links,.thrv_social_follow';
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['styles-templates'] = [ 'hidden' => true ];

		$components['social_follow'] = $components['social'];

		$components['social_follow']['disabled_controls'] = [ 'type', 'has_custom_url', 'custom_url', 'counts', 'total_share', 'CssVarChanger', 'CommonButtonWidth' ];

		$styles = [];

		foreach ( range( 1, 8 ) as $i ) {
			$styles[ 'tve_links_style_' . $i ] = 'Style ' . $i;
		}
		$components['social_follow']['config']['size']['config']['label'] = __( 'Size and Alignment', 'thrive-cb' );

		$components['social_follow']['config']['Align']          = array(
			'config' => array(
				'buttons' => array(
					array(
						'icon'    => 'a_left',
						'value'   => 'left',
						'default' => true,
						'tooltip' => __( 'Align Left', 'thrive-cb' ),
					),
					array(
						'icon'    => 'a_center',
						'value'   => 'center',
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
		);
		$components['social_follow']['config']['stylePicker']    = array(
			'config' => array(
				'label' => __( 'Change style', 'thrive-cb' ),
				'match' => 'tve_links_style_',
				'items' => $styles,
			),
		);
		$components['social_follow']['config']['preview']        = [
			'config' => [
				'sortable'      => true,
				'settings_icon' => 'pen-regular',
				'tpl'           => 'controls/preview-check-list-item',
			],
		];
		$components['social_follow']['config']['CustomBranding'] = array(
			'config'  => array(
				'name'    => '',
				'label'   => __( 'Custom branding', 'thrive-cb' ),
				'default' => true,
			),
			'extends' => 'Switch',
		);

		$components['social_follow']['config']['SocialFollowPalettes'] = [
			'config'    => [],
			'extends'   => 'Palettes',
			'important' => true,
		];

		$components['scroll'] = [
			'hidden'            => false,
			'disabled_controls' => [ '[data-value="parallax"]' ],
		];

		$components['layout']['disabled_controls'] = [ 'Width', 'Height', 'Display', 'Overflow', 'ScrollStyle' ];

		unset( $components['social'] );

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
					'value'    => 'social_options',
					'selector' => '.tve_s_item',
					'name'     => __( 'Grouped Social Buttons', 'thrive-cb' ),
					'singular' => __( '-- Option Label %s', 'thrive-cb' ),
				),
			),
		);
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
				'url'  => 'social_follow',
				'link' => 'https://help.thrivethemes.com/en/articles/4472330-how-to-use-the-social-follow-element',
			],
		];
	}
}

return new TCB_Social_Follow_Element( 'thrive_social_follow' );
