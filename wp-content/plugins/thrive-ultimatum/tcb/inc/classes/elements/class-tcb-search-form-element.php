<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Search_Form_Element
 */
class TCB_Search_Form_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Search', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'search_elem';
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
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-search-form';
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
			'extra_attr' => 'data-ct="search_form-0" data-tcb-elem-type="search_form" data-specific-modal="search_form"',
		), true );
	}

	/**
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * @return array
	 */
	public function own_components() {

		return array(
			'search_form'      => array(
				'config' => array(
					'SearchPalette'  => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'PostTypes'      => [
						'config' => [
							'sortable'  => false,
							'clickable' => false,
						],
					],
					'ButtonLayout'   => array(
						'config'  => array(
							'buttons' => array(
								array(
									'text'  => __( 'Text only', 'thrive-cb' ),
									'value' => 'text',
								),
								array(
									'text'  => __( 'Icon only', 'thrive-cb' ),
									'value' => 'icon',
								),
								array(
									'text'    => __( 'Icon & text', 'thrive-cb' ),
									'value'   => 'icon_text',
									'default' => true,
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'FormType'       => array(
						'config'  => array(
							'buttons' => array(
								array(
									'text'    => __( 'With button', 'thrive-cb' ),
									'value'   => 'with',
									'default' => true,
								),
								array(
									'text'  => __( 'Without button', 'thrive-cb' ),
									'value' => 'without',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'InputPosition'  => array(
						'config'  => array(
							'buttons' => array(
								array(
									'text'    => __( 'Left', 'thrive-cb' ),
									'value'   => 'left',
									'default' => true,
								),
								array(
									'text'  => __( 'Right', 'thrive-cb' ),
									'value' => 'right',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'DisplayOptions' => array(
						'config'  => array(
							'name'   => __( 'Display options', 'thrive-cb' ),
							'groups' => array(
								array(
									'label'   => 'On click',
									'options' => array(
										'expand'        => __( 'Expand search', 'thrive-cb' ),
										'overlay'       => __( 'Show overlay', 'thrive-cb' ),
										'tooltip-click' => __( 'Show tooltip', 'thrive-cb' ),
									),
								),
								array(
									'label'   => 'On hover',
									'options' => array(
										'tooltip-hover' => __( 'Show tooltip', 'thrive-cb' ),
									),
								),
							),
						),
						'extends' => 'Optgroup',
					),
					'ContentWidth'   => array(
						'config'  => array(
							'default' => '1024',
							'min'     => '100',
							'label'   => __( 'Search field width', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'Size'           => array(
						'config'     => array(
							'default' => '20',
							'min'     => '10',
							'max'     => '150',
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => [ 'px', 'em' ],
							'css'     => 'font-size',
						),
						'css_prefix' => tcb_selection_root() . ' ',
						'extends'    => 'Slider',
					),
					'EditorPreview'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'View expanded search in the editor', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
				),
			),
			'shadow'           => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'typography'       => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
			'layout'           => [
				'disabled_controls' => [
					'padding-left',
					'padding-right',
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
				'url'  => 'search_element',
				'link' => 'https://help.thrivethemes.com/en/articles/4425871-how-to-use-the-search-element',
			],
		];
	}
}

