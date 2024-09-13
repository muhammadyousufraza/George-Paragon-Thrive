<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use function TVA\Architect\Resources\tva_resource_content;

class TCB_Resources_Element extends TCB_Cloud_Template_Element_Abstract {
	/**
	 * @var string
	 */
	protected $_tag = 'resources';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Lesson Resources', 'thrive-apprentice' );
	}

	public function alternate() {
		return 'resource';
	}

	public function icon() {
		return 'resources';
	}

	/**
	 * Decide when to hide the Resource Element
	 *
	 * @return bool
	 */
	public function hide() {

		if ( Thrive_Utils::is_theme_template() ) {
			return ! tva_is_apprentice_template();
		}

		return parent::hide();
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-lesson-resources';
	}

	public function html() {

		if ( apply_filters( 'tva_resource_element_render_default', get_post_type() === TVA_Const::LESSON_POST_TYPE ) ) {
			return tva_resource_content()->default_lesson_resources();
		}

		return tcb_template(
			'elements/element-placeholder',
			array(
				'icon'  => $this->icon(),
				'class' => 'tva-lesson-resources',
				'title' => __( 'Insert Apprentice Resources', 'thrive-apprentice' ),
			),
			true
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tva_is_apprentice_template() ? \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_elements_category() : $this->get_thrive_integrations_label();
	}


	public function is_placeholder() {
		return false;
	}

	public function own_components() {
		$prefix_config = [ 'css_prefix' => tcb_selection_root() . ' ' ];

		$components = [
			'resources'  => [
				'config' => [
					'ResourcesPalette' => array(
						'config'  => array(),
						'extends' => 'PalettesV2',
					),
					'showLabel'        => [
						'config'  => [
							'name'  => '',
							'label' => __( 'Heading', 'thrive-apprentice' ),
						],
						'extends' => 'Switch',
					],
					'showIcons'        => [
						'config'  => [
							'name'  => '',
							'label' => __( 'File type icon', 'thrive-apprentice' ),
						],
						'extends' => 'Switch',
					],
					'showDescriptions' => [
						'config'  => [
							'name'  => '',
							'label' => __( 'Description', 'thrive-apprentice' ),
						],
						'extends' => 'Switch',
					],
					'showDownload'     => [
						'config'  => [
							'name'  => '',
							'label' => __( 'Download button', 'thrive-apprentice' ),
						],
						'extends' => 'Switch',
					],
					'IconAlign'        => [
						'config'  => [
							'name'      => __( 'Icons vertical position', 'thrive-apprentice' ),
							'important' => true,
							'buttons'   => [
								[
									'icon'  => 'top',
									'value' => 'flex-start',
								],
								[
									'icon'  => 'vertical',
									'value' => 'center',
								],
								[
									'icon'  => 'bot',
									'value' => 'flex-end',
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'ButtonAlign'      => [
						'config'  => [
							'name'      => __( 'Buttons vertical position', 'thrive-apprentice' ),
							'important' => true,
							'buttons'   => [
								[
									'icon'  => 'top',
									'value' => 'flex-start',
								],
								[
									'icon'  => 'vertical',
									'value' => 'center',
								],
								[
									'icon'  => 'bot',
									'value' => 'flex-end',
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
				],
			],
			'borders'    => [
				'config' => [
					'Borders' => [
						'important' => true,
					],
					'Corners' => [
						'important' => true,
					],
				],
			],
			'layout'     => [
				'disabled_controls' => [
					'Display',
					'Float',
					'Position',
				],
				'config'            => [
					'Width'  => [
						'important' => true,
					],
					'Height' => [
						'important' => true,
					],
				],
			],
			'background' => [
				'config' => [
					'ColorPicker' => $prefix_config,
					'PreviewList' => $prefix_config,
				],
			],
			'typography' => [
				'hidden' => true,
			],
			'scroll'     => [
				'hidden' => true,
			],
			'animation'  => [
				'hidden' => true,
			],
		];

		return array_merge( $components, $this->group_component() );
	}

	public function has_group_editing() {
		return [
			'select_values' => [
				[
					'value'     => 'all_item',
					'selector'  => '.tva-resource-item',
					'name'      => __( 'Resource item', 'thrive-apprentice' ),
					'singular'  => __( '-- Resource item %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_download',
					'selector'  => '.tva-resource-button.tva-res-download',
					'name'      => __( 'Download buttons', 'thrive-apprentice' ),
					'singular'  => __( '-- Download button %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_open',
					'selector'  => '.tva-resource-button.tva-res-open',
					'name'      => __( 'Open buttons', 'thrive-apprentice' ),
					'singular'  => __( '-- Open button %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_icons',
					'selector'  => '.tva-resource-icon',
					'name'      => __( 'Icons', 'thrive-apprentice' ),
					'singular'  => __( '-- Icon %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_download_icons',
					'selector'  => ' .tva-res-download .thrv_icon',
					'name'      => __( 'Download button icons', 'thrive-apprentice' ),
					'singular'  => __( '-- Download button icon %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_open_icons',
					'selector'  => ' .tva-res-open .thrv_icon',
					'name'      => __( 'Open button icons', 'thrive-apprentice' ),
					'singular'  => __( '-- Open button icon %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_titles',
					'selector'  => '.tva-resource-title',
					'name'      => __( 'Resource titles', 'thrive-apprentice' ),
					'singular'  => __( '-- Resource title %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_descriptions',
					'selector'  => '.tva-resource-description',
					'name'      => __( 'Resource descriptions', 'thrive-apprentice' ),
					'singular'  => __( '-- Resource description %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_text_containers',
					'selector'  => '.tva-resource-details',
					'name'      => __( 'Resource text containers', 'thrive-apprentice' ),
					'singular'  => __( '-- Resource text container %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
				[
					'value'     => 'all_button_containers',
					'selector'  => '.tva-resource-get',
					'name'      => __( 'Resource button containers', 'thrive-apprentice' ),
					'singular'  => __( '-- Resource button container %s', 'thrive-apprentice' ),
					'no_unlock' => true,
				],
			],
		];
	}
}

return new TCB_Resources_Element();
