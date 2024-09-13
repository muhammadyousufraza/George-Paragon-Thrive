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
 *
 */
class TCB_Post_List_Filter_Rest {
	const  REST_NAMESPACE = 'tcb/v1';
	const  REST_ROUTE     = '/post-list-filter';

	public static function register_routes() {
		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . '/filter-options', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_filter_options' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'filter_option' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . '/option-template', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_option_template_ajax' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'filter-type'             => [
						'type'     => 'string',
						'required' => true,
					],
					'filter-option'           => [
						'type'     => 'string',
						'required' => true,
					],
					'filter-option-selection' => [
						'type'     => 'string',
						'required' => false,
					],
					'filter-all-option'       => [
						'type'     => 'boolean',
						'required' => true,
					],
				],
			],
		] );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_filter_options( $request ) {
		$options          = [];
		$filter_option    = sanitize_text_field( $request->get_param( 'filter_option' ) );
		$searched_keyword = sanitize_text_field( $request->get_param( 'search' ) );
		$selected_values  = $request->get_param( 'values' );
		$length           = sanitize_text_field( $request->get_param( 'length' ) );

		$attr = self::compute_filter_attr( $selected_values, $searched_keyword, (int) $length );

		switch ( $filter_option ) {
			case 'category':
				foreach ( get_categories( $attr ) as $category ) {
					$options[] = [
						'value' => (string) $category->term_id,
						'label' => $category->name,
					];
				}
				break;
			case 'tag':
				foreach ( get_tags( $attr ) as $tag ) {
					$options[] = [
						'value' => (string) $tag->term_id,
						'label' => $tag->name,
					];
				}
				break;
			case 'author':
				foreach ( get_users( $attr ) as $user ) {
					$options[] = [
						'value' => (string) $user->ID,
						'label' => $user->display_name,
					];
				}
				break;
			default:
				$attr['taxonomy'] = $filter_option;

				foreach ( get_terms( $attr ) as $term ) {
					$options[] = [
						'value' => (string) $term->term_id,
						'label' => $term->name,
					];
				}
		}


		return new \WP_REST_Response( $options );
	}

	/**
	 * Get the post list template according to it's type(button, radio, checkbox etc.)
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public static function get_option_template_ajax( $request ) {
		$filter_type          = $request->get_param( 'filter-type' );
		$filter_option        = $request->get_param( 'filter-option' );
		$filter_all_option    = $request->get_param( 'filter-all-option' );
		$filter_all_label     = $request->get_param( 'filter-all-label' );
		$classes              = $request->get_param( 'classes' );
		$css                  = $request->get_param( 'css' );
		$template             = $request->get_param( 'template' );
		$override_colors      = $request->get_param( 'override-colors' );
		$dropdown_icon        = $request->get_param( 'dropdown_icon' );
		$dropdown_icon_style  = $request->get_param( 'dropdown_icon_style' );
		$dropdown_animation   = $request->get_param( 'dropdown_animation' );
		$dropdown_placeholder = $request->get_param( 'dropdown_placeholder' );

		$filter_option_selection = json_decode( str_replace( [ '|{|', '|}|' ], [ '[', ']' ], $request->get_param( 'filter-option-selection' ) ), true );

		/* Add the 'All' option */
		if ( $filter_all_option ) {
			array_unshift( $filter_option_selection, 'all' );
		}

		$extra_attributes = [
			'css'                  => $css,
			'classes'              => $classes,
			'all_label'            => $filter_all_label,
			'template'             => $template,
			'override_colors'      => $override_colors,
			'dropdown_icon'        => $dropdown_icon,
			'dropdown_icon_style'  => $dropdown_icon_style,
			'dropdown_animation'   => $dropdown_animation,
			'dropdown_placeholder' => $dropdown_placeholder,
		];

		return new WP_REST_Response( array(
			'content' => TCB_Post_List_Filter::get_option_template( $filter_type, $filter_option, $filter_option_selection, $extra_attributes ),
		) );
	}

	/**
	 * Compute the filter attributes based on the selected values and/or search keyword
	 *
	 * @param $selected_values
	 * @param $attr
	 *
	 * @return mixed
	 */
	public static function compute_filter_attr( $selected_values, $searched_keyword, $length ) {
		$attr = [];

		// already selected values can be retrieved via the 'include' argument - which expects a non-empty list of ints
		if ( ! empty( $selected_values ) && is_array( $selected_values ) && array_filter( $selected_values ) ) {
			$attr['include'] = array_map( 'intval', $selected_values );
		} else {
			// keyword search - WP will automatically select best columns to search in
			$attr['search'] = $searched_keyword;
			// make sure there's always a limit set to the returned number of results
			$attr['number'] = empty( $length ) ? 50 : $length;
		}

		return $attr;
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @return \WP_Error|bool
	 */
	public static function route_permission() {
		$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : null;

		return \TCB_Product::has_external_access( $post_id );
	}
}
