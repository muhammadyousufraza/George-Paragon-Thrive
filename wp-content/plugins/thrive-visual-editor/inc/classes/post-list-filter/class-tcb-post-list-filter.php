<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Post_List_Filter {
	/* component name */
	const COMPONENT = 'post_list_filter';
	/* identifier used as a class name */
	const IDENTIFIER = 'tcb-post-list-filter';


	/**
	 * Render the Post List Filter
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render_filter( $attr = [] ) {
		$filter_type       = $attr['data-filter-type'];
		$filter_option     = $attr['data-filter-option'];
		$filter_all_option = $attr['data-all-option'] === 'true';

		$content                        = '';
		$attr['data-options-selection'] = str_replace( [ '|{|', '|}|' ], [ '[', ']' ], $attr['data-options-selection'] );
		$filter_options_selection       = json_decode( $attr['data-options-selection'], true );

		/* Add the 'All' option */
		if ( $filter_all_option ) {
			array_unshift( $filter_options_selection, 'all' );
		}

		$extra_attributes = [
			'classes' => $attr['inner-classes'],
		];


		if ( ! empty( $attr['data-css'] ) ) {
			$extra_attributes['css'] = $attr['data-css'];
		}

		if ( ! empty( $attr['icon'] ) ) {
			$extra_attributes['icon'] = $attr['icon'];
		}

		if ( ! empty( $attr['template'] ) ) {
			$extra_attributes['template'] = $attr['template'];
		}

		if ( ! empty( $attr['search'] ) ) {
			$extra_attributes['search'] = $attr['search'];
		}

		if ( ! empty( $attr['data-all-label'] ) ) {
			$extra_attributes['all_label'] = $attr['data-all-label'];
		}

		if ( ! empty( $attr['dropdown_icon'] ) ) {
			$extra_attributes['dropdown_icon'] = $attr['dropdown_icon'];
			unset( $attr['dropdown_icon'] );
		}

		if ( ! empty( $attr['dropdown_animation'] ) ) {
			$extra_attributes['dropdown_animation'] = $attr['dropdown_animation'];
			unset( $attr['dropdown_animation'] );
		}

		if ( ! empty( $attr['dropdown_placeholder'] ) ) {
			$extra_attributes['dropdown_placeholder'] = $attr['dropdown_placeholder'];
			unset( $attr['dropdown_placeholder'] );
		}

		$content .= static::get_option_template( $filter_type, $filter_option, $filter_options_selection, $extra_attributes );

		unset( $attr['icon'], $attr['search'], $attr['inner-classes'] );

		return TCB_Utils::wrap_content( $content, 'div', '', static::get_classes( $attr ), static::get_attr( $attr ) );
	}

	/**
	 * Return the specific template according to the filter type(button, checkbox, radio etc.)
	 *
	 * @param $filter_type
	 * @param $filter_option
	 * @param $filter_options_selection
	 * @param $extra_attributes
	 *
	 * @return string
	 */
	public static function get_option_template( $filter_type, $filter_option, $filter_options_selection, $extra_attributes ) {
		$filter_element_template        = 'elements/post-list-filter-elements/elements/';
		$filter_option_identifier_class = '';
		$content                        = '';

		/* Select template for filter group */
		switch ( $filter_type ) {
			case 'button':
				$filter_element_template        .= 'filter-button';
				$filter_option_identifier_class .= 'tcb-filter-button';
				break;
			case 'checkbox':
				$filter_element_template        .= 'filter-checkbox';
				$filter_option_identifier_class .= 'tcb-filter-checkbox';
				break;
			case 'radio':
				$filter_element_template        .= 'filter-radio';
				$filter_option_identifier_class .= 'tcb-filter-radio';
				break;
			case 'dropdown':
				$filter_element_template        .= 'filter-dropdown-option';
				$filter_option_identifier_class .= 'tcb-filter-dropdown-option';
				break;
			case 'list':
				$filter_element_template        .= 'filter-list';
				$filter_option_identifier_class .= 'tcb-filter-list';
				break;
		}
		$all_label = 'All';
		if ( ! empty( $extra_attributes['all_label'] ) ) {
			$all_label = $extra_attributes['all_label'];
		}

		if ( $filter_type !== 'search' ) {
			foreach ( $filter_options_selection as $filter_option_id ) {
				$filter_option_name = static::get_option_name( $filter_option, $filter_option_id, $all_label );

				$template_attributes = [
					'id'      => $filter_option_id,
					'name'    => $filter_option_name,
					'classes' => 'tcb-filter-option ' . $filter_option_identifier_class . ' ' . $extra_attributes['classes'],
				];

				$template_attributes['display_name'] = $template_attributes['name'];

				if ( $filter_option === 'author' && $filter_option_id !== 'all' ) {
					$template_attributes['display_name'] = static::get_author_name( $filter_option_id );
				}

				if ( ! empty( $extra_attributes['css'] ) ) {
					$template_attributes['css'] = '[data-css="' . $extra_attributes['css'] . '"] .' . $filter_option_identifier_class;
				}
				if ( ! empty( $extra_attributes['icon'] ) ) {
					$template_attributes['icon'] = $extra_attributes['icon'];
				}
				if ( ! empty( $extra_attributes['template'] ) ) {
					$template_attributes['template'] = $extra_attributes['template'];
				}
				if ( ! empty( $extra_attributes['override_colors'] ) ) {
					$template_attributes['override_colors'] = $extra_attributes['override_colors'];
				}

				$content .= tcb_template( $filter_element_template, $template_attributes, true );
			}
		} else if ( ! empty( $extra_attributes['search'] ) ) {
			$content = $extra_attributes['search'];

			unset( $extra_attributes['search'] );
		}

		$wrapper_attr = [];

		if ( ! empty( $extra_attributes['css'] ) ) {
			$wrapper_attr['data-selector'] = '.tcb-post-list-filter[data-css="' . $extra_attributes['css'] . '"] .' . $filter_option_identifier_class;
		}

		if ( $filter_type === 'dropdown' ) {
			$content = TCB_Utils::wrap_content( $content, 'ul', '', 'tve-lg-dropdown-list tve-dynamic-dropdown-editable', $wrapper_attr );
			$content = tcb_template( 'elements/post-list-filter-elements/elements/filter-dropdown', array(
				'dropdown_content'     => $content,
				'template'             => ! empty( $template_attributes['template'] ) ? $template_attributes['template'] : 'default',
				'css'                  => '.tcb-post-list-filter[data-css="' . $extra_attributes['css'] . '"] .tve-dynamic-dropdown',
				'dropdown_icon_style'  => ! empty( $extra_attributes['dropdown_icon_style'] ) ? $extra_attributes['dropdown_icon_style'] : 'style_1',
				'dropdown_icon'        => ! empty( $extra_attributes['dropdown_icon'] ) ? $extra_attributes['dropdown_icon'] : '',
				'dropdown_animation'   => ! empty( $extra_attributes['dropdown_animation'] ) ? $extra_attributes['dropdown_animation'] : '',
				'dropdown_placeholder' => ! empty( $extra_attributes['dropdown_placeholder'] ) ? $extra_attributes['dropdown_placeholder'] : 'Choose ' . $filter_option,
				'override_colors'      => ! empty( $template_attributes['override_colors'] ) ? $template_attributes['override_colors'] : '',
			), true );
		}
		if ( $filter_type === 'list' ) {
			$content                       = TCB_Utils::wrap_content( $content, 'ul', '', 'tcb-styled-list' );
			$wrapper_attr['data-icon']     = 'icon-angle-right-light';
			$wrapper_attr['data-selector'] = '.tcb-post-list-filter[data-css="' . $extra_attributes['css'] . '"] .tcb-filter-list-group';
			$content                       = TCB_Utils::wrap_content( $content, 'div', '', 'thrv_wrapper tcb-filter-list-group', $wrapper_attr );
		}

		return $content;
	}

	/**
	 * Return the option name according to its id and type
	 *
	 * @param        $filter_option
	 * @param        $filter_option_id
	 * @param string $all_label
	 *
	 * @return string|WP_Error
	 */
	public static function get_option_name( $filter_option, $filter_option_id, $all_label = 'All' ) {
		if ( $filter_option_id === 'all' ) {
			$name = $all_label;
		} else {
			switch ( $filter_option ) {
				case 'category':
					$name = get_the_category_by_ID( $filter_option_id );
					break;
				case 'tag':
					$name = get_tag( $filter_option_id )->name;
					break;
				case 'author':
					$name = get_user_by( 'ID', $filter_option_id )->user_nicename;
					break;
				default:
					$name = get_term( $filter_option_id )->name;
			}
		}

		return $name;
	}

	public static function get_author_name( $filter_option_id ) {
		return get_user_by( 'ID', $filter_option_id )->display_name;
	}

	/**
	 * @param $attr
	 *
	 * @return string
	 */
	private static function get_classes( $attr ) {
		$class = [ static::IDENTIFIER, THRIVE_WRAPPER_CLASS ];

		/* set responsive/animation classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$class[] = $attr['class'];
		}

		return implode( ' ', $class );
	}

	/**
	 * @param $attr
	 *
	 * @return array
	 */
	private static function get_attr( $attr ) {
		return $attr;
	}

	/**
	 * Register REST Routes for the Post List
	 */
	public static function rest_api_init() {
		require_once TVE_TCB_ROOT_PATH . '/inc/classes/post-list-filter/class-tcb-post-list-filter-rest.php';

		TCB_Post_List_Filter_Rest::register_routes();
	}

	/**
	 * Get the filter option from the url query strings
	 *
	 * @param $post_list_query
	 *
	 * @return array
	 */
	public static function get_filter_option( $post_list_query ) {
		$filters = [];

		foreach ( $_GET as $key => $values ) {
			/* If we have the key in the query we proceed with the filtering */
			if ( ! empty( $post_list_query['dynamic_filter'][ $key ] ) ) {
				$values = explode( ',', $values );

				foreach ( $values as $value ) {
					$filters[] = [
						'filter' => $post_list_query['dynamic_filter'][ $key ],
						'name'   => $value,
						'origin' => $key,
					];
				}
			}
		}

		return $filters;
	}

	/**
	 * Change the query to include the filter options
	 *
	 * @param $post_list_query
	 *
	 * @return mixed
	 */
	public static function filter( $post_list_query, $filters = [] ) {
		if ( empty( $filters ) ) {
			$filters = static::get_filter_option( $post_list_query );
		}

		if ( empty( $filters ) ) {
			return $post_list_query;
		}

		$rules = [];

		foreach ( $filters as $filter ) {
			if ( ! empty( $filter['name'] ) || ( ! empty( $filter['id'] ) && ! in_array( $filter['id'], [ 'all', 'none' ] ) ) ) {
				switch ( $filter['filter'] ) {
					case 'author':
						static::set_filter_query_for_author( $post_list_query, $filter );
						break;
					case 'search':
						$post_list_query['s'] = $filter['name'];
						break;
					case 'category':
					case 'tag':
					default:
						static::set_filter_for_terms( $filter, $rules );
						break;
				}
			}
		}

		$rule_count = count( $rules );

		/* If we have rules, we update the Post List Query */
		if ( $rule_count > 0 ) {
			$rules = array_values( $rules );

			/* If we have more than 1 rule we need to add 'AND' relation */
			if ( count( $rules ) > 1 ) {
				$rules['relation'] = 'AND';
			}

			if ( empty( $post_list_query['tax_query'] ) ) {
				$post_list_query['tax_query'] = $rules;
			} else {
				$post_list_query['tax_query'] = [
					$post_list_query['tax_query'],
					'relation' => 'AND',
					$rules,
				];
			}
		}

		return $post_list_query;
	}

	/**
	 * Compute the query rules with the set of filters form Post List filter element
	 *
	 * @param $filter
	 * @param $rules
	 */
	public static function set_filter_for_terms( $filter, &$rules ) {
		$filter_by   = isset( $filter['name'] ) ? 'name' : 'id';
		$filter_slug = $filter['filter'] === 'tag' ? 'post_tag' : $filter['filter'];

		if ( isset( $filter[ $filter_by ] ) ) {
			$taxonomy_obj = get_term_by( $filter_by, $filter[ $filter_by ], $filter_slug );

			if ( ! empty( $taxonomy_obj ) ) {
				$taxonomy = (string) $taxonomy_obj->term_id;

				if ( ! empty( $rules[ $filter['origin'] ] ) ) {
					$rules[ $filter['origin'] ]['terms'][] = $taxonomy;
				} else {
					$rules[ $filter['origin'] ] = [
						'taxonomy' => $filter_slug,
						'terms'    => [ $taxonomy ],
						'operator' => 'IN',
					];
				}
			}
		}
	}

	/**
	 * Update the post list query with the rules for authors.
	 *
	 * @param $post_list_query
	 * @param $filter
	 */
	public static function set_filter_query_for_author( &$post_list_query, $filter ) {
		$author = null;

		if ( isset( $filter['name'] ) ) {
			$author = get_user_by( 'slug', $filter['name'] );
		} else if ( isset( $filter['id'] ) ) {
			$author = get_user_by( 'id', $filter['id'] );
		}

		if ( ! empty( $author ) ) {
			$author_id = (int) $author->ID;
			if ( ! empty( $post_list_query['author__in'] ) ) {
				$post_list_query['author__in'][] = $author_id;
			} else {
				$post_list_query['author__in'] = [ $author_id ];
			}
		}
	}

	/**
	 * Returns the first 5 categories from the page
	 *
	 * @return array
	 */
	public static function localize_filter_categories() {
		$categories = [];

		foreach ( get_categories( [ 'number' => 3 ] ) as $category ) {
			$categories[ (int) $category->term_id ] = $category->name;
		}

		return $categories;
	}
}
