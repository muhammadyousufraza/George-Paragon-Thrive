<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_List_Element
 */
class TCB_Post_List_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post List', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-list';
	}

	/**
	 * This element is not a placeholder
	 *
	 * @return bool|true
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_LIST_CLASS;
	}

	/**
	 * Hide this element in the places where it doesn't make sense, but show it on posts, pages, custom post types, etc.
	 *
	 * @return bool
	 */
	public function hide() {
		return apply_filters( 'tcb_hide_post_list_element', TCB_Utils::should_hide_element_on_blacklisted_post_types() );
	}

	/**
	 * Override the parent implementation of this method in order to add more classes.
	 *
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
		$post_list_args = TCB_Post_List::default_args();

		$attr = array(
			'query'          => $post_list_args['query'],
			'ct'             => $this->tag() . '-0',
			'tcb-elem-type'  => $this->tag(),
			'element-name'   => esc_attr( $this->name() ),
			'specific-modal' => 'post-list',
		);

		$extra_attr = '';

		foreach ( $attr as $key => $value ) {
			$extra_attr .= 'data-' . $key . '="' . $value . '" ';
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'       => $this->icon(),
			'class'      => 'tcb-ct-placeholder tcb-compact-element',
			'title'      => $title,
			'extra_attr' => $extra_attr,
		), true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$pagination_types = [];

		/* for each pagination instance, get the label and the type for the select control config */
		foreach ( TCB_Pagination::$all_types as $type ) {
			$instance = tcb_pagination( $type );

			$pagination_types[] = array(
				'name'  => $instance->get_label(),
				'value' => $instance->get_type(),
			);
		}

		$components = array(
			'carousel'         => [ 'hidden' => false ],
			'animation'        => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'typography'       => [ 'hidden' => true ],
			'layout'           => [
				'disabled_controls' => [ 'MaxWidth', 'Float', 'hr', 'Position', 'PositionFrom', 'Display', 'Overflow', 'ScrollStyle' ],
			],
			'post_list'        => array(
				'order'  => 1,
				'config' => array(
					'Type'            => array(
						'config'  => array(
							'default'       => 'grid',
							'large_buttons' => true,
							'name'          => __( 'Display type', 'thrive-cb' ),
							'buttons'       => array(
								array(
									'data'    => array(
										'tooltip'  => __( 'Grid', 'thrive-cb' ),
										'position' => 'top',
									),
									'icon'    => 'gallery-grid',
									'value'   => 'grid',
									'default' => true,
								),
								array(
									'data'  => array(
										'tooltip'  => __( 'Masonry', 'thrive-cb' ),
										'position' => 'top',
										'width'    => '100%',
									),
									'icon'  => 'gallery-vertical-masonry',
									'value' => 'masonry',
								),
								array(
									'data'  => array(
										'tooltip'  => __( 'List', 'thrive-cb' ),
										'position' => 'top',
										'width'    => '100%',
									),
									'icon'  => 'display-list',
									'value' => 'list',
								),
								array(
									'data'  => array(
										'tooltip'  => __( 'Carousel', 'thrive-cb' ),
										'position' => 'top',
										'width'    => '100%',
									),
									'icon'  => 'gallery-carousel',
									'value' => 'carousel',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'ColumnsNumber'   => array(
						'config'  => array(
							'default' => '3',
							'min'     => '1',
							'max'     => '10',
							'label'   => __( 'Columns', 'thrive-cb' ),
							'um'      => [ '' ],
						),
						'extends' => 'Slider',
					),
					'VerticalSpace'   => array(
						'config'  => array(
							'min'   => '0',
							'max'   => '240',
							'label' => __( 'Vertical space', 'thrive-cb' ),
							'um'    => [ 'px' ],
						),
						'extends' => 'Slider',
					),
					'HorizontalSpace' => array(
						'config'  => array(
							'min'   => '0',
							'max'   => '240',
							'label' => __( 'Horizontal space', 'thrive-cb' ),
							'um'    => [ 'px' ],
						),
						'extends' => 'Slider',
					),
					/* get the select control for the pagination type */
					'PaginationType'  => array(
						'config'  => array(
							'default' => TCB_Pagination::NONE,
							/* if this is the control from the post list, change the name a bit */
							'name'    => __( 'Pagination type', 'thrive-cb' ),
							'options' => $pagination_types,
						),
						'extends' => 'Select',
					),
					'ContentSize'     => array(
						'config'  => array(
							'name'    => __( 'Content', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'  => '',
									'text'  => 'Full',
									'value' => 'content',
								],
								[
									'icon'  => '',
									'text'  => 'Excerpt',
									'value' => 'excerpt',
								],
								[
									'icon'    => '',
									'text'    => 'Words',
									'value'   => 'words',
									'default' => true,
								],
							],
						),
						'extends' => 'ButtonGroup',
					),
					'WordsTrim'       => array(
						'config'  => array(
							'name'      => __( 'Word count', 'thrive-cb' ),
							'default'   => 12,
							'maxlength' => 2,
							'min'       => 1,
						),
						'extends' => 'Input',
					),
					'ReadMoreText'    => array(
						'config'  => array(
							'label'       => __( 'Read more text', 'thrive-cb' ),
							'default'     => '',
							'placeholder' => __( 'e.g. Continue reading', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'Linker'          => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Link entire item to content', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'Featured'        => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Show featured content' ),
							'info'  => true,
						),
						'extends' => 'Switch',
					),
					'NumberOfItems'   => array(
						'config'  => array(
							'name'      => __( 'Number of items', 'thrive-cb' ),
							'default'   => get_option( 'posts_per_page' ),
							'maxlength' => 4,
							'min'       => 1,
						),
						'extends' => 'Input',
					),
				),
			),
		);

		/* Add group components for the Carousel Arrows */
		$components = array_merge( $components, $this->group_component() );

		return $components;
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
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'post_list',
				'link' => 'https://help.thrivethemes.com/en/articles/4425844-how-to-use-the-post-list-element-in-thrive-architect',
			],
		];
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
					'value'    => 'arrows',
					'selector' => '.tcb-carousel-arrow',
					'name'     => __( 'Next/Previous buttons Icons', 'thrive-cb' ),
					'singular' => __( '-- Next/Previous buttons Icon', 'thrive-cb' ),
				),
			),
		);
	}
}
