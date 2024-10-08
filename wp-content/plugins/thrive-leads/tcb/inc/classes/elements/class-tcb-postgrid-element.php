<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/26/2017
 * Time: 4:37 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Postgrid_Element
 */
class TCB_Postgrid_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post Grid', 'thrive-cb' );
	}

	/**
	 * We don't use this anymore
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'list';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post_grid';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_post_grid ';
	}

	/**
	 * Post Grid extra sidebar state - used in MANAGE CELLS mode.
	 *
	 * @return null|string
	 */
	public function get_sidebar_extra_state() {
		return tcb_template( 'sidebars/post-grid-edit-grid-options', null, true );
	}

	/**
	 * Gets all the post types for post grid
	 *
	 * @return array
	 */
	private function get_all_post_types() {
		$types        = [];
		$banned_types = TCB_Utils::get_banned_post_types();

		foreach ( get_post_types( [], 'objects' ) as $type ) {
			if ( ! in_array( $type->name, $banned_types ) ) {
				$types[] = [
					'id'   => $type->name,
					'text' => $type->label,
				];
			}
		}

		return $types;
	}

	/**
	 * Construct number of posts data
	 *
	 * @return array
	 */
	private function get_number_of_posts() {
		$return = [
			[
				'value' => 0,
				'name'  => 'All',
			],
		];
		foreach ( range( 1, 19 ) as $number ) {
			$return[] = [
				'value' => $number,
				'name'  => $number,
			];
		}

		return $return;
	}

	/**
	 * Constructs the categories list for "Category filter"
	 *
	 * @return array
	 */
	public static function get_categories( $term ) {
		$taxonomies = [ 'category' ];

		if ( taxonomy_exists( 'apprentice' ) ) {
			$taxonomies[] = 'apprentice';
		}

		$terms = get_terms( $taxonomies, [ 'search' => $term ] );

		$categories = [];
		foreach ( $terms as $item ) {
			$categories[] = [
				'id'   => $item->name,
				'text' => $item->name,
			];
		}

		return $categories;
	}

	/**
	 * Constructs the tags list for "Tags filter"
	 *
	 * @return array
	 */
	public static function get_tags( $term ) {
		$taxonomies = [
			'post_tag',
		];

		if ( taxonomy_exists( 'apprentice' ) ) {
			$taxonomies[] = 'apprentice-tag';
		}

		$terms = get_terms( $taxonomies, [ 'search' => $term ] );

		$tags = [];
		foreach ( $terms as $item ) {
			$tags[] = [
				'id'   => $item->name,
				'text' => $item->name,
			];
		}

		return $tags;
	}

	/**
	 * Constructs the taxonomies list for "Custom Taxonomies filter"
	 *
	 * @return array
	 */
	public static function get_custom_taxonomies( $term ) {
		$items      = get_taxonomies();
		$banned     = [ 'category', 'post_tag' ];
		$taxonomies = [];

		foreach ( $items as $item ) {
			if ( in_array( $item, $banned ) ) {
				continue;
			}

			if ( strpos( $item, $term ) !== false ) {
				$taxonomies[] = [
					'id'   => $item,
					'text' => $item,
				];
			}
		}

		return $taxonomies;
	}

	/**
	 * Constructs the author list for "Authors filter"
	 *
	 * @return array
	 */
	public static function get_authors( $term ) {
		$users   = get_users( [ 'search' => "*$term*" ] );
		$authors = [];
		foreach ( $users as $item ) {
			$authors[] = [
				'id'   => $item->data->user_nicename,
				'text' => $item->data->user_nicename,
			];
		}

		return $authors;
	}

	/**
	 * Constructs the post lists for "Individual Post / Pages filter"
	 *
	 * @return array
	 */
	public static function get_posts_list( $term ) {
		$args    = [
			'order_by'    => 'post_title',
			'post_type'   => [ 'page', 'post' ],
			'post_status' => [ 'publish' ],
			's'           => $term,
		];
		$results = new WP_Query( $args );

		$list = [];
		foreach ( $results->get_posts() as $post ) {
			$list[] = [
				'id'   => $post->ID,
				'text' => $post->post_title,
			];
		}

		return $list;
	}


	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'postgrid'        => array(
				'config' => array(
					'read_more'         => array(
						'config'  => array(
							'label' => __( 'Read More', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'read_more_color'   => array(
						'config'  => array(
							'default'   => 'f00',
							'label'     => __( 'Text Color', 'thrive-cb' ),
							'important' => true,
							'options'   => [ 'allowEmpty' => true ],
						),
						'extends' => 'ColorPicker',
					),
					'img_height'        => [
						'config'  => [
							'default' => '100',
							'min'     => '10',
							'max'     => '999',
							'um'      => [ 'px' ],
						],
						'extends' => 'Slider',
					],
					'title_font_size'   => [
						'css_suffix' => ' .tve-post-grid-title',
						'config'     => [
							'default' => '16',
							'min'     => '10',
							'max'     => '100',
							'um'      => [ 'px' ],
						],
						'extends'    => 'Slider',
					],
					'title_line_height' => [
						'css_suffix' => ' .tve-post-grid-title',
						'config'     => [
							'default' => '16',
							'min'     => '10',
							'max'     => '100',
							'um'      => [ 'px' ],
						],
						'extends'    => 'Slider',
					],
					'tabs'              => array(
						'config' => array(
							'buttons' => array(
								array(
									'value' => 'img-height',
									'text'  => __( 'Image Height', 'thrive-cb' ),
								),
								array(
									'value' => 'title-font',
									'text'  => __( 'Title Font', 'thrive-cb' ),
								),
								array(
									'value' => 'line-height',
									'text'  => __( 'Line Height', 'thrive-cb' ),
								),
							),
						),
					),
				),
			),
			'postgrid-layout' => array(
				'config' => array(
					'number_of_columns' => array(
						'config'  => array(
							'name'    => __( 'Columns', 'thrive-cb' ),
							'default' => 3,
							'options' => range( 1, 6 ),
						),
						'extends' => 'Select',
					),
					'display'           => array(
						'config'  => array(
							'name'    => __( 'Display', 'thrive-cb' ),
							'options' => [
								[
									'value' => 'grid',
									'name'  => 'Grid',
								],
								[
									'value' => 'masonry',
									'name'  => 'Masonry',
								],
							],
						),
						'extends' => 'Select',
					),
					'grid_layout'       => array(
						'config'  => array(
							'name'    => __( 'Grid Layout', 'thrive-cb' ),
							'options' => [
								[
									'value' => 'horizontal',
									'name'  => 'Horizontal',
								],
								[
									'value' => 'vertical',
									'name'  => 'Vertical',
								],
							],
						),
						'extends' => 'Select',
					),
					'featured_image'    => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Featured image', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'title'             => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Title', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'read_more_lnk'     => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Read more link', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'text'              => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Text', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'text_type'         => array(
						'config'  => array(
							'name'    => __( 'Text type', 'thrive-cb' ),
							'options' => [
								[
									'value' => 'summary',
									'name'  => 'Summary',
								],
								[
									'value' => 'excerpt',
									'name'  => 'Excerpt',
								],
								[
									'value' => 'fulltext',
									'name'  => 'Full text',
								],
							],
						),
						'extends' => 'Select',
					),
					'preview'           => array(
						'config' => array(
							'sortable' => true,
							'labels'   => array(
								'featured_image' => __( 'Featured Image', 'thrive-cb' ),
								'title'          => __( 'Title', 'thrive-cb' ),
								'text'           => __( 'Text', 'thrive-cb' ),
								'read_more'      => __( 'Read More', 'thrive-cb' ),
							),
						),
					),
				),
			),
			'postgrid-query'  => array(
				'config' => array(
					'content'         => array(
						'config'  => array(
							'label'            => __( 'Content', 'thrive-cb' ),
							'tags'             => false,
							'data'             => $this->get_all_post_types(),
							'min_input_length' => 0,
							'remote'           => false,
							'no_results'       => __( 'No posts were found satisfying your Query', 'thrive-cb' ),
						),
						'extends' => 'SelectMultiple',
					),
					'order_by'        => array(
						'config'  => array(
							'name'    => __( 'Order By', 'thrive-cb' ),
							'options' => [
								[
									'value' => 'date',
									'name'  => 'Date',
								],
								[
									'value' => 'title',
									'name'  => 'Title',
								],
								[
									'value' => 'author',
									'name'  => 'Author',
								],
								[
									'value' => 'comment_count',
									'name'  => 'Number of Comments',
								],
								[
									'value' => 'rand',
									'name'  => 'Random',
								],
							],
						),
						'extends' => 'Select',
					),
					'order_mode'      => array(
						'config'  => array(
							'name'    => __( 'Order', 'thrive-cb' ),
							'options' => [
								[
									'value' => 'DESC',
									'name'  => 'Descending',
								],
								[
									'value' => 'ASC',
									'name'  => 'Ascending',
								],
							],
						),
						'extends' => 'Select',
					),
					'number_of_posts' => array(
						'config'  => array(
							'name'    => __( 'Number of posts', 'thrive-cb' ),
							'options' => $this->get_number_of_posts(),
						),
						'extends' => 'Select',
					),
					'recent_days'     => array(
						'config'  => array(
							'inline'    => true,
							'name'      => __( 'Days', 'thrive-cb' ),
							'default'   => 0,
							'min'       => 0,
							'max'       => 999,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'start'           => array(
						'config'  => array(
							'name'      => __( 'Start', 'thrive-cb' ),
							'default'   => 0,
							'min'       => 0,
							'max'       => 19,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
				),
			),
			'postgrid-filter' => array(
				'config' => array(
					'categories'            => array(
						'config'  => array(
							'label'            => __( 'Categories', 'thrive-cb' ),
							'tags'             => false,
							'min_input_length' => 2,
							'remote'           => true,
							'custom_ajax'      => 'post_grid_categories',
							'no_results'       => __( 'No categories were found satisfying your Query', 'thrive-cb' ),
						),
						'extends' => 'SelectMultiple',
					),
					'tags'                  => array(
						'config'  => array(
							'label'            => __( 'Tags', 'thrive-cb' ),
							'tags'             => false,
							'custom_ajax'      => 'post_grid_tags',
							'remote'           => true,
							'min_input_length' => 2,
							'no_results'       => __( 'No tags were found satisfying your Query', 'thrive-cb' ),
						),
						'extends' => 'SelectMultiple',
					),
					'authors'               => array(
						'config'  => array(
							'label'            => __( 'Authors', 'thrive-cb' ),
							'tags'             => false,
							'custom_ajax'      => 'post_grid_users',
							'remote'           => true,
							'min_input_length' => 2,
							'no_results'       => __( 'No authors were found satisfying your Query', 'thrive-cb' ),
						),
						'extends' => 'SelectMultiple',
					),
					'custom_taxonomies'     => array(
						'config'  => array(
							'label'            => __( 'Custom Taxonomies', 'thrive-cb' ),
							'tags'             => false,
							'custom_ajax'      => 'post_grid_custom_taxonomies',
							'remote'           => true,
							'min_input_length' => 2,
							'no_results'       => __( 'No taxonomies were found satisfying your Query', 'thrive-cb' ),
						),
						'extends' => 'SelectMultiple',
					),
					'individual_post_pages' => array(
						'config'  => array(
							'label'            => __( 'Individual Posts / Pages', 'thrive-cb' ),
							'tags'             => false,
							'custom_ajax'      => 'post_grid_individual_post_pages',
							'remote'           => true,
							'min_input_length' => 2,
							'no_results'       => __( 'No post / pages were found satisfying your Query', 'thrive-cb' ),
						),
						'extends' => 'SelectMultiple',
					),
				),
			),
			'background'      => [
				'config' => [
					'css_suffix' => ' .tve_pg_container',
				],
			],
			'borders'         => [
				'config' => [
					'Borders'    => [
						'important' => true,
					],
					'css_suffix' => ' .tve_pg_container',
				],
			],
			'typography'      => [
				'config'            => [
					'FontColor' => [
						'css_suffix' => [ ' .tve-post-grid-text', ' .tve-post-grid-title' ],
					],
					'FontFace'  => [
						'css_suffix' => [ ' .tve-post-grid-text', ' .tve-post-grid-title' ],
					],
				],
				'disabled_controls' => [
					'TextStyle',
					'TextTransform',
					'.typography-button-toggle-controls', //Hides FontSize, LineHeight, LetterSpacing
					'.typography-button-toggle-hr',
					'.typography-text-transform-hr',
					'.tve-advanced-controls',
				],
			],
			'layout'          => [
				'disabled_controls' => [
					'Width',
					'Height',
					'.tve-advanced-controls',
					'Alignment',
				],
			],
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}
}
