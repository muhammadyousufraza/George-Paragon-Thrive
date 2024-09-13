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
 * Class TCB_Element_Abstract
 */
abstract class TCB_Element_Abstract {

	/**
	 * Element alternate.
	 *
	 * @var string
	 */
	protected $_alternate = '';

	/**
	 * Set from TCB_Elements class get_for_front function
	 *
	 * @var boolean
	 */
	public $pinned;

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return $this->_alternate;
	}


	/**
	 * Element tag.
	 *
	 * @var string
	 */
	protected $_tag = '';

	/**
	 * TCB_Element_Abstract constructor.
	 *
	 * @param string $tag element tag.
	 */
	public function __construct( $tag = '' ) {
		if ( empty( $this->_tag ) ) {
			$this->_tag = $tag;
		}
	}

	/**
	 * Get element tag
	 *
	 * @return string
	 */
	public function tag() {
		return $this->_tag;
	}

	/**
	 * Element identifier that will help us understand on what we click and open the right menu
	 *
	 * @return string
	 */
	public function identifier() {
		return '';
	}

	/**
	 * Configuration of the element with components and elements
	 *
	 * @return array
	 */
	public function components() {
		$own_components = array_merge( $this->own_components(), $this->multiple_select_component() );
		$components     = tve_array_replace_recursive( $this->general_components(), $own_components );
		foreach ( $own_components as $key => $component ) {

			if ( isset( $component['disabled_controls'] ) ) {
				$components[ $key ]['disabled_controls'] = $component['disabled_controls'];
			}
		}

		$components = $this->normalize_components( $components );

		return $components;
	}

	/**
	 * Components that apply only to the element
	 *
	 * @return array
	 */
	public function own_components() {
		return [];
	}

	/**
	 * General components that apply to all elements
	 *
	 * @return array
	 */
	protected function general_components() {
		$prefix_config = tcb_selection_root();

		/**
		 * Avoid creating extra javascript configuration data
		 */
		if ( $this->inherit_components_from() || $this->is_placeholder() ) {
			return [];
		}
		$texts = [
			' p',
			' li',
			' blockquote',
			' address',
			' .tcb-plain-text',
			' label',
		];

		$headings = [
			' h1',
			' h2',
			' h3',
			' h4',
			' h5',
			' h6',
		];

		$h1_spacing = $h2_spacing = $h3_spacing = $p_spacing = [
			'css_suffix' => ' p',
			'important'  => true,
			'config'     => [
				'default' => '',
				'min'     => '0',
				'max'     => '100',
				'label'   => __( 'Paragraph Spacing', 'thrive-cb' ),
				'um'      => [ 'px', 'em' ],
				'css'     => 'fontSize',
			],
			'extends'    => 'Slider',
		];

		$h1_spacing['css_suffix']      = ' h1';
		$h1_spacing['config']['label'] = __( 'H1 Spacing', 'thrive-cb' );
		$h2_spacing['css_suffix']      = ' h2';
		$h2_spacing['config']['label'] = __( 'H2 Spacing', 'thrive-cb' );
		$h3_spacing['css_suffix']      = ' h3';
		$h3_spacing['config']['label'] = __( 'H3 Spacing', 'thrive-cb' );

		return [
			'typography' => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
				],
				'order'             => 90,
				'config'            => [
					'ParagraphStyle'       => [
						'hidden'  => true,
						'config'  => [
							'label' => __( 'Paragraph Style', 'thrive-cb' ),
						],
						'extends' => 'StyleChange',
					],
					'ParagraphStylePicker' => [
						'config' => [
							'label'         => __( 'Choose Default Paragraph Style', 'thrive-cb' ),
							'default'       => '',
							'default_label' => __( tcb_post()->is_landing_page() ? 'Landing Page Default' : 'Default', 'thrive-cb' ),
						],
					],
					'ToggleControls'       => [
						'config'  => [
							'buttons' => [
								[
									'value'   => 'tcb-typography-font-size',
									'text'    => __( 'Font Size', 'thrive-cb' ),
									'default' => true,
								],
								[
									'value' => 'tcb-typography-line-height',
									'text'  => __( 'Line Height', 'thrive-cb' ),
								],
								[
									'value' => 'tcb-typography-letter-spacing',
									'text'  => __( 'Letter Spacing', 'thrive-cb' ),
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'FontSize'             => [
						'css_suffix' => $texts,
						'css_prefix' => $prefix_config . ' ',
						'config'     => [
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px', 'em' ],
							'css'     => 'fontSize',
						],
						'extends'    => 'FontSize',
					],
					'LetterSpacing'        => [
						'css_suffix' => $texts,
						'config'     => [
							'default' => 'auto',
							'min'     => '0',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px', 'em' ],
							'css'     => 'letterSpacing',
						],
						'extends'    => 'Slider',
					],
					'FontColor'            => [
						'css_suffix' => array_merge( $texts, $headings ),
						'css_prefix' => $prefix_config . ' ',
						'config'     => [
							'default' => '000',
							'label'   => 'Color',
							'options' => [
								'output' => 'object',
							],
						],
						'extends'    => 'ColorPicker',
					],
					'TextAlign'            => [
						'config'  => [
							'name'    => __( 'Alignment', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'format-align-left',
									'text'    => '',
									'value'   => 'left',
									'default' => true,
								],
								[
									'icon'  => 'format-align-center',
									'text'  => '',
									'value' => 'center',
								],
								[
									'icon'  => 'format-align-right',
									'text'  => '',
									'value' => 'right',
								],
								[
									'icon'  => 'format-align-justify',
									'text'  => '',
									'value' => 'justify',
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'TextStyle'            => [
						'css_suffix' => $texts,
						'css_prefix' => $prefix_config . ' ',
					],
					'TextTransform'        => [
						'css_suffix' => array_merge( $texts, $headings ),
						'css_prefix' => $prefix_config . ' ',
						'config'     => [
							'name'    => __( 'Transform', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'none',
									'text'    => '',
									'value'   => 'none',
									'default' => true,
								],
								[
									'icon'  => 'format-all-caps',
									'text'  => '',
									'value' => 'uppercase',
								],
								[
									'icon'  => 'format-capital',
									'text'  => '',
									'value' => 'capitalize',
								],
								[
									'icon'  => 'format-lowercase',
									'text'  => '',
									'value' => 'lowercase',
								],
							],
						],
						'extends'    => 'ButtonGroup',
					],
					'FontFace'             => [
						'css_suffix' => array_merge( $texts, $headings ),
						'css_prefix' => $prefix_config . ' ',
						'config'     => [
							'template' => 'controls/font-manager',
							'inline'   => false,
						],
					],
					'LineHeight'           => [
						'css_suffix' => $texts,
						'css_prefix' => $prefix_config . ' ',
						'config'     => [
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px', 'em' ],
							'css'     => 'lineHeight',
						],
						'extends'    => 'LineHeight',
					],
					'p_spacing'            => $p_spacing,
					'h1_spacing'           => $h1_spacing,
					'h2_spacing'           => $h2_spacing,
					'h3_spacing'           => $h3_spacing,
				],
			],
			'carousel'   => [
				'hidden' => true,
				'order'  => 85,
				'config' => [
					'MovementSettings'    => array(
						'config'  => array(
							'name'               => '',
							'large_buttons_text' => true,
							'checkbox'           => true,
							'default'            => 'arrows',
							'buttons'            => array(
								array(
									'value'   => 'arrows',
									'text'    => __( 'Show arrows', 'thrive-cb' ),
									'icon'    => 'carousel-show-arrows',
									'default' => true,
								),
								array(
									'value'   => 'infinite',
									'text'    => __( 'Infinite sliding', 'thrive-cb' ),
									'icon'    => 'carousel-infinite-sliding',
									'default' => false,
								),
								array(
									'value'   => 'dots',
									'text'    => __( 'Show dots', 'thrive-cb' ),
									'icon'    => 'carousel-show-dots',
									'default' => false,
								),
								array(
									'value'   => 'draggable',
									'text'    => __( 'Drag to scroll', 'thrive-cb' ),
									'icon'    => 'carousel-drag-to-scroll',
									'default' => false,
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'SlidesToShow'        => array(
						'config'  => array(
							'min'   => '1',
							'max'   => '10',
							'label' => __( 'Columns', 'thrive-cb' ),
							'um'    => [],
						),
						'extends' => 'Slider',
					),
					'SlidesToScroll'      => array(
						'config'  => array(
							'min'   => '1',
							'max'   => '3',
							'label' => __( 'Slides to scroll', 'thrive-cb' ),
							'type'  => 'number',
						),
						'extends' => 'Input',
					),
					'Autoplay'            => array(
						'config'  => array(
							'name'       => '',
							'label'      => __( 'Autoplay', 'thrive-cb' ),
							'default'    => false,
							'info'       => true,
							'info_hover' => true,
						),
						'extends' => 'Switch',
					),
					'AutoplaySpeed'       => array(
						'config'  => array(
							'default' => '3000',
							'min'     => '1',
							'max'     => '10000',
							'label'   => __( 'Speed', 'thrive-cb' ),
							'um'      => [ 'ms' ],
						),
						'extends' => 'Input',
					),
					'PauseOn'             => array(
						'config'  => array(
							'name'     => __( 'Pause on', 'thrive-cb' ),
							'checkbox' => true,
							'buttons'  => array(
								array(
									'value'   => 'pauseOnFocus',
									'text'    => 'Focus',
									'default' => true,
								),
								array(
									'value'   => 'pauseOnHover',
									'text'    => 'Hover',
									'default' => true,
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'CenterMode'          => array(
						'config'  => array(
							'name'       => '',
							'label'      => __( 'Overlap end items', 'thrive-cb' ),
							'default'    => false,
							'info'       => true,
							'info_hover' => true,
						),
						'extends' => 'Switch',
					),
					'CenterPadding'       => array(
						'config'  => array(
							'min'   => '0',
							'max'   => '160',
							'label' => __( 'Distance', 'thrive-cb' ),
							'um'    => [ 'px', '%' ],
						),
						'extends' => 'Slider',
					),
					'AdaptiveHeight'      => array(
						'config'  => array(
							'name'       => '',
							'label'      => __( 'Adaptive carousel height' ),
							'info'       => true,
							'info_hover' => true,
						),
						'extends' => 'Switch',
					),
					'UniformSlidesHeight' => array(
						'config'  => array(
							'name'       => '',
							'label'      => __( 'Uniform slides height' ),
							'info'       => true,
							'info_hover' => true,
						),
						'extends' => 'Switch',
					),
					'VerticalPosition'    => array(
						'config'  => array(
							'name'    => __( 'Vertical Position', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'top',
									'default' => true,
									'value'   => 'top',
								],
								[
									'icon'  => 'vertical',
									'value' => 'center',
								],
								[
									'icon'  => 'bot',
									'value' => 'bottom',
								],
							],
						),
						'extends' => 'ButtonGroup',
					),
					'Fade'                => array(
						'config'  => array(
							'name'       => '',
							'label'      => __( 'Single slide fader', 'thrive-cb' ),
							'default'    => false,
							'info'       => true,
							'info_hover' => true,
						),
						'extends' => 'Switch',
					),
					'FadeImageWidth'      => array(
						'config'  => array(
							'default' => '0',
							'min'     => '10',
							'max'     => '1080',
							'um'      => [ '%', 'px' ],
							'label'   => __( 'Item resize', 'thrive-cb' ),
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
				],
			],
			'layout'     => [
				'order'             => 100,
				'disabled_controls' => [
					'ScrollStyle',
				],
			],
			'background' => [
				'order'             => 110,
				'config'            => [
					'ColorPicker'       => [
						'config' => [
							'icon'      => true,
							'important' => true,
							'options'   => [ 'noBeforeInit' => false ],
						],
					],
					'PreviewFilterList' => [
						'config' => [
							'sortable'    => false,
							'extra_class' => 'tcb-preview-list-white',
						],
					],
					'PreviewList'       => [
						'config' => [
							'sortable' => true,
						],
					],
				],
				'disabled_controls' => [
					'video',
				],
			],
			'borders'    => [
				'order'  => 120,
				'config' => [
					'Corners' => [
						'overflow' => true,
					],
				],
			],
			'animation'  => [
				'order' => 130,
			],
			/**
			 * Reorder the sidebar, now the Shadow is before Responsive
			 */

			'shadow'              => [
				'order'          => 140,
				'inline_text_to' => '',
			],
			'responsive'          => [
				'order' => 150,
			],
			'conditional-display' => [
				'hidden' => true,
				'order'  => 155,
				'config' => [
					'LazyLoad'          => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Lazy load', 'thrive-cb' ),
							'default' => false,

						],
						'extends' => 'Switch',
					],
					'UniformHeights'    => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Uniform display heights', 'thrive-cb' ),
							'default' => false,
						],
						'extends' => 'Switch',
					],
					'InheritBackground' => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Lazy load background inherited from', 'thrive-cb' ),
							'options' => [],
						],
						'extends' => 'Select',
					],
				],
			],
			'styles-templates'    => [
				'order' => 160,
			],
			'scroll'              => [
				'hidden' => true,
				'order'  => 125,
			],

		];
	}

	/**
	 * Return element config containing: components, identifier, name, icon, hide
	 *
	 * @return array|mixed
	 * @throws Exception
	 */
	public function config() {

		$config = [
			'components'                  => $this->components(),
			'identifier'                  => $this->identifier(),
			'name'                        => $this->name(),
			'icon'                        => $this->icon(),
			'hide'                        => $this->hide(),
			'tag'                         => $this->tag(),
			'is_placeholder'              => $this->is_placeholder(),
			'hover'                       => $this->has_hover_state(),
			'active'                      => $this->active_state_config(),
			'expanded'                    => $this->expanded_state_config(),
			'expanded_state_label'        => $this->expanded_state_label(),
			'expanded_state_apply_inline' => $this->expanded_state_apply_inline(),
			'has_group'                   => $this->has_group_editing(),
			'category'                    => $this->category(),
			'info'                        => $this->info(),
		];
		if ( ( $inherit_from = $this->inherit_components_from() ) ) {
			$config['inherit_from'] = $inherit_from;
		}

		$config = apply_filters( 'tcb_element_' . $this->tag() . '_config', $config );

		return $config;
	}

	/**
	 * Normalize components by making sure all the variables are present
	 *
	 * @param array $components element config.
	 *
	 * @return array
	 */
	private function normalize_components( $components = [] ) {
		$i = 1;
		foreach ( $components as $key => $c ) {
			/**
			 * If component is marked as hidden, completely remove it from the localization object
			 */
			if ( ! empty( $c['hidden'] ) ) {
				unset( $components[ $key ] );
				continue;
			}

			/* update the order of the component in case it's not set */
			if ( ! isset( $c['order'] ) ) {
				$components[ $key ]['order'] = $i ++;
			}

			/* set the 'to' as empty if nothing is added => in this case the component will apply to the wrapper */
			if ( ! isset( $c['to'] ) ) {
				$components[ $key ]['to'] = '';
			}

			/* by default, if nothing is set, the nothing is hidden in the component */
			if ( ! isset( $c['hide'] ) ) {
				$components[ $key ]['hide'] = [];
			}

			/* if nothing is added, by default the config is empty */
			if ( ! isset( $c['config'] ) ) {
				$components[ $key ]['config'] = [];
			}
		}

		return $components;
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return '';
	}

	/**
	 * The toString override
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->name();
	}

	/**
	 * The icon of the element
	 *
	 * @return string
	 */
	public function icon() {
		return '';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return false;
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return tcb_template( 'elements/' . $this->tag() . '.php', $this, true );
	}

	/**
	 * Return the element html layout after applying the filter
	 *
	 * @param string $html Element layout.
	 *
	 * @return mixed
	 */
	public function layout( $html = '' ) {

		if ( empty( $html ) ) {
			$html = $this->html();
		}

		return apply_filters( 'tcb_' . $this->tag() . '_element_layout', $html );
	}

	/**
	 * Get all custom sidebar states
	 * This can be overridden if an element needs multiple sidebar states
	 *
	 * @return array
	 */
	public function get_custom_sidebars() {
		$extra_state = $this->get_sidebar_extra_state();

		if ( empty( $extra_state ) ) {
			$sidebars = [];
		} else {
			$sidebars = [
				$this->_tag => [
					'template' => $extra_state,
					'title'    => $this->name(),
				],
			];
		}

		return $sidebars;
	}

	/**
	 * Get an extra state for the sidebar. Can be used to switch the sidebar to this state
	 *
	 * @return string|null
	 */
	public function get_sidebar_extra_state() {
		return '';
	}

	/**
	 * Whether or not this element is only a placeholder ( it has no menu, it's not selectable etc )
	 * e.g. Content Templates
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Whether or not this element is available and should be included in the current page
	 *
	 * @return bool
	 */
	public function is_available() {
		return true;
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return false;
	}

	/**
	 * Whether or not the this element can be styles for active state
	 *
	 * Can return the active class .tcb-active-state or the pseudo class :active
	 *
	 * @return bool | string
	 */
	public function active_state_config() {
		return false;
	}

	/**
	 * If the element has active state it offers possibility of changing default label
	 *
	 * @return bool | string
	 */
	public function expanded_state_label() {
		return __( 'Expanded', 'thrive-cb' );
	}

	/**
	 * Whether or not we can apply inline css too on element
	 * e.g for tabs/toggle we cant because elements can be in two states at the time
	 *
	 * @return bool | string
	 */
	public function expanded_state_apply_inline() {
		return false;
	}

	/**
	 * Whether or not this element can be styled for expanded state
	 *
	 * @return bool | string
	 */
	public function expanded_state_config() {
		return false;
	}

	/**
	 * Whether or not this element has cloud templates
	 *
	 * @return bool
	 */
	public function has_cloud_templates() {
		return false;
	}

	/**
	 * Allows different element names to use the same exact components as a base building block
	 * Example: a Testimonial element uses the same exact components as the Columns element ( because it is a column container element ) and
	 * has extra testimonial options
	 *
	 * @return null|string
	 */
	public function inherit_components_from() {
		return null;
	}

	/**
	 * Unified place for the "Thrive Integrations" category. Implemented here so that we can have a single translation for this
	 *
	 * @return string
	 */
	public static function get_thrive_integrations_label() {
		return __( 'Integrations', 'thrive-cb' );
	}

	/**
	 * Unified place for the "Thrive Foundation (Basic)" category. Implemented here so that we can have a single translation for this
	 *
	 * @return string
	 */
	public static function get_thrive_basic_label() {
		return __( 'Foundation', 'thrive-cb' );
	}

	/**
	 * Unified place for the "Thrive Building Blocks (Advanced)" category. Implemented here so that we can have a single translation for this
	 *
	 * @return string
	 */
	public static function get_thrive_advanced_label() {
		return __( 'Building Blocks', 'thrive-cb' );
	}

	/**
	 * Widgets section label
	 *
	 * @return string
	 */
	public static function get_widgets_label() {
		return __( 'Widgets', 'thrive-cb' );
	}

	/**
	 * Returns the HTML placeholder for an element (contains a wrapper, and a button with icon + element name)
	 *
	 * @param string $title Optional. Defaults to the name of the current element
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		return tcb_template( 'elements/element-placeholder', [
			'icon'  => $this->icon(),
			'class' => str_replace( [ ',.', ',', '.' ], [ ' ', '', '' ], $this->identifier() ),
			'title' => $title,
		], true );
	}

	/**
	 * Element category that will be displayed in the sidebar.
	 * If the element is hidden it's ok not to have a category defined.
	 *
	 * @throws Exception
	 */
	public function category() {
		if ( ! $this->hide() ) {
			throw new Exception( 'Please define category for element:' . $this->name() );
		}

		return '';
	}

	/**
	 * Determines if an element has group editing or not
	 *
	 * @return bool
	 */
	public function has_group_editing() {
		return false;
	}

	/**
	 * Shows if the element is promoted.
	 *
	 * If promoted it will have a special place inside the element sidebar
	 *
	 * @return bool
	 */
	public function promoted() {
		return false;
	}

	public function info() {
		return '';
	}

	/**
	 * Outputs Group Component Settings
	 *
	 * @return array
	 */
	public function group_component() {
		return [
			'group' => [
				'config' => [
					'ButtonToggle' => [
						'config' => [
							'label'         => '',
							'class'         => 'tcb-group-toggle-btn',
							'icon_active'   => 'unlock-alt-regular',
							'icon_inactive' => 'lock-alt-regular',
							'tooltip'       => [
								'active'   => __( 'Group styling disabled. The styling will be applied only for the selected element.', 'thrive-cb' ),
								'inactive' => __( 'Group styling active. The same styling will be applied to similar elements.', 'thrive-cb' ),
							],
						],
					],
					'preview'      => [
						'config'  => [
							'name'       => '',
							'full-width' => true,
							'options'    => [],
						],
						'extends' => 'Select',
					],

				],
			],
		];
	}

	/**
	 * Outputs Shared Styles Component Settings
	 *
	 * @return array
	 */
	public function shared_styles_component() {
		return [
			'shared-styles' => [
				'order'  => 1,
				'config' => [
					'global_style' => [
						'config' => [],
					],
					'preview'      => [
						'config' => [
							'label' => __( 'Style', 'thrive-cb' ),
							'items' => [],
						],
					],
				],
			],
		];
	}

	public function multiple_select_component() {
		return [
			'multiple-select-mode' => [
				'order'  => 0,
				'config' => [
					'selectedElementsType' => [
						'config'  => [
							'name'        => '',
							'full-width'  => true,
							'options'     => [],
							'placeholder' => __( 'Select items type', 'thrive-cb' ),
						],
						'extends' => 'Select',
					],
				],
			],
		];
	}
}
