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
 * Class TCB_Post_List_Filter_Element
 */
class TCB_Post_List_Filter_Element extends TCB_Element_Abstract {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Post List Filter', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'post-list-filters';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-post-list-filter';
	}

	/**
	 * Hide this element in the places where it doesn't make sense, but show it on posts, pages, custom post types, etc.
	 *
	 * @return bool
	 */
	public function hide() {
		return TCB_Utils::should_hide_element_on_blacklisted_post_types();
	}

	public function own_components() {
		$default_filter_option = array(
			'author'   => __( 'Author', 'thrive-cb' ),
			'category' => __( 'Category', 'thrive-cb' ),
			'tag'      => __( 'Tag', 'thrive-cb' ),
		);

		$filter_options = array_merge( $default_filter_option, static::get_filter_options() );
		/* add the post list filter control */
		$components['post_list_filter'] = array(
			'config' => array(
				'FilterOption'       => array(
					'config'  => array(
						'name'    => __( 'Filter option', 'thrive-cb' ),
						'options' => $filter_options,
					),
					'extends' => 'Select',
				),
				'MultipleSelections' => array(
					'config'  => array(
						'name'    => '',
						'label'   => __( 'Allow multiple selections', 'thrive-cb' ),
						'default' => true,
					),
					'extends' => 'Switch',
				),
				'FilterType'         => array(
					'config'  => array(
						'name'    => __( 'Filter type', 'thrive-cb' ),
						'options' => array(
							'button'   => __( 'Buttons', 'thrive-cb' ),
							'checkbox' => __( 'Checkboxes', 'thrive-cb' ),
							'radio'    => __( 'Radios', 'thrive-cb' ),
							'dropdown' => __( 'Dropdown', 'thrive-cb' ),
							'list'     => __( 'Text links', 'thrive-cb' ),
							'search'   => __( 'Search', 'thrive-cb' ),
						),
					),
					'extends' => 'Select',
				),
				'DisplayOption'      => array(
					'config'  => array(
						'name'    => __( 'Display option', 'thrive-cb' ),
						'options' => array(
							'horizontally' => __( 'Horizontally', 'thrive-cb' ),
							'vertically'   => __( 'Vertically', 'thrive-cb' ),
						),
					),
					'extends' => 'Select',
				),
				'VerticalSpace'      => array(
					'config'  => array(
						'min'   => 0,
						'max'   => 200,
						'label' => __( 'Vertical space', 'thrive-cb' ),
						'um'    => [ 'px' ],
					),
					'extends' => 'Slider',
				),
				'HorizontalSpace'    => array(
					'config'  => array(
						'min'   => 0,
						'max'   => 200,
						'label' => __( 'Horizontal space', 'thrive-cb' ),
						'um'    => [ 'px' ],
					),
					'extends' => 'Slider',
				),
				'URLQueryKey'        => array(
					'config'  => array(
						'label' => __( 'URL query key', 'thrive-cb' ),
					),
					'extends' => 'LabelInput',
				),
				'AllOption'          => array(
					'config'  => array(
						'name'    => '',
						'label'   => __( 'Include "All" option', 'thrive-cb' ),
						'default' => true,
					),
					'extends' => 'Switch',
				),
				'AllLabel'           => array(
					'config'  => array(
						'label' => __( 'Label for "All"', 'thrive-cb' ),
					),
					'extends' => 'LabelInput',
				),
				'OptionsSelection'   => array(
					'config'  => array(
						'label'            => __( 'Content', 'thrive-cb' ),
						'tags'             => false,
						'data'             => '',
						'min_input_length' => 0,
						'remote'           => false,
						'no_results'       => __( 'No posts were found satisfying your Query', 'thrive-cb' ),
					),
					'extends' => 'SelectAutocomplete',
				),
				'DefaultValue'       => array(
					'config'  => array(
						'name'    => __( 'Default value', 'thrive-cb' ),
						'options' => [],
					),
					'extends' => 'Select',
				),
			),
		);

		return array_merge( $components, $this->group_component() );
	}

	public static function get_filter_options() {
		$args         = [
			'public'   => true,
			'_builtin' => false,
		];
		$excluded_tax = [ 'tva_courses', 'tcb_symbols_tax', 'tvo_tags' ];

		$taxonimies = get_taxonomies( $args, 'object' );
		$options    = [];
		foreach ( $taxonimies as $tax ) {
			if ( ! in_array( $tax->name, $excluded_tax, true ) ) {
				$options[ $tax->name ] = $tax->label;
			}
		}

		return $options;
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
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'all_filter_optiopns',
					'selector' => '.tcb-filter-option',
					'name'     => __( 'Grouped Filter Options', 'thrive-cb' ),
					'singular' => __( '-- Filter Option %s', 'thrive-cb' ),
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
				'url'  => 'post_list_filter',
				'link' => 'https://help.thrivethemes.com/en/articles/6533678-how-to-use-the-post-list-filter-element',
			],
		];
	}
}
