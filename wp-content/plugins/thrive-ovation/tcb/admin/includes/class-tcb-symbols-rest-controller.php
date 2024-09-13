<?php
/**
 * FileName  class-tcb-symbols-rest-controller.php.
 *
 * @project  : thrive-visual-editor
 * @developer: Dragos Petcu
 */

class TCB_REST_Symbols_Controller extends WP_REST_Posts_Controller {

	public static $version = 1;

	/**
	 * Constructor.
	 * We are overwriting the post type for this rest controller
	 */
	public function __construct() {
		parent::__construct( TCB_Symbols_Post_Type::SYMBOL_POST_TYPE );

		$this->namespace = 'tcb/v' . self::$version;
		$this->rest_base = 'symbols';

		$this->register_meta_fields();
		$this->hooks();
	}

	/**
	 * Hooks to change the post rest api
	 */
	public function hooks() {
		add_filter( "rest_prepare_{$this->post_type}", [ $this, 'rest_prepare_symbol' ], 10, 2 );
		add_filter( "rest_insert_{$this->post_type}", [ $this, 'rest_insert_symbol' ], 10, 2 );
		add_action( "rest_after_insert_{$this->post_type}", [ $this, 'rest_after_insert' ], 10, 2 );
		add_action( 'rest_delete_' . TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY, [ $this, 'rest_delete_category' ], 10, 1 );
		add_action( "rest_{$this->post_type}_query", [ $this, 'override_per_page' ], 10, 1 );
	}

	/**
	 * Override the per page limit for the rest api in case there are people with over 100 symbols
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function override_per_page( $params ) {
		if ( isset( $params['posts_per_page'] ) ) {
			$params['posts_per_page'] = '300';
		}

		return $params;
	}

	/**
	 * Register additional rest routes for symbols
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/cloud', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_cloud_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/cloud/(?P<id>.+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'string',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_cloud_item' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			),
		) );
	}

	/**
	 * Check to see if the user hase permission to view cloud items
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return TCB_Product::has_external_access();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function get_cloud_items( $request ) {


		if ( ! ( $type = $request->get_param( 'type' ) ) ) {
			return new WP_Error( 'rest_invalid_element_type', __( 'Invalid element type' ), [ 'status' => 500 ] );
		}

		/** @var TCB_Cloud_Template_Element_Abstract $element */
		if ( ! ( $element = tcb_elements()->element_factory( $type ) ) || ! is_a( $element, 'TCB_Cloud_Template_Element_Abstract' ) ) {
			return new WP_Error( 'rest_invalid_element_type', __( 'Invalid element type', 'thrive-cb' ) . " ({$type})", [ 'status' => 500 ] );
		}

		$templates = $element->get_cloud_templates();

		if ( is_wp_error( $templates ) ) {
			return $templates;
		}

		$templates = $this->prepare_templates_for_response( $templates );

		return new WP_REST_Response( $templates );
	}

	/**
	 * Transform the resulted templates array to be used in a backbone collection
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public function prepare_templates_for_response( $templates ) {
		$results = [];

		foreach ( $templates as $template ) {
			$results[] = $template;
		}

		return $results;
	}

	/**
	 * Check to see if the user has permission to view an item from cloud
	 *
	 * @return bool
	 */
	public function get_cloud_item_permission_check() {
		return tcb_has_external_cap();
	}


	/**
	 * Get symbol template from the cloud
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function get_cloud_item( $request ) {

		if ( ! ( $type = $request->get_param( 'type' ) ) ) {
			return new WP_Error( 'rest_invalid_element_type', __( 'Invalid element type', 'thrive-cb' ), [ 'status' => 500 ] );
		}

		if ( ! ( $id = $request->get_param( 'id' ) ) ) {
			return new WP_Error( 'invalid_id', __( 'Missing template id', 'thrive-cb' ), [ 'status' => 500 ] );
		}

		/** @var TCB_Cloud_Template_Element_Abstract $element */
		if ( ! ( $element = tcb_elements()->element_factory( $type ) ) || ! is_a( $element, 'TCB_Cloud_Template_Element_Abstract' ) ) {
			return new WP_Error( 'rest_invalid_element_type', __( 'Invalid element type', 'thrive-cb' ) . " ({$type})", [ 'status' => 500 ] );
		}

		$data = $element->get_cloud_template_data( $id );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return new WP_REST_Response( $data );
	}

	/**
	 * Checks if a given request has access to create a post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
	 * @since 4.7.0
	 *
	 */
	public function create_item_permissions_check( $request ) {
		$parent_response = parent::create_item_permissions_check( $request );

		//if we are making a duplicate symbol revert to default, do not check for duplicate titles
		if ( isset( $request['old_id'] ) || is_wp_error( $parent_response ) ) {
			return $parent_response;
		}

		return $this->check_duplicate_title( $request );
	}

	/**
	 * Checks if a given request has access to update a post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
	 * @since 4.7.0
	 *
	 */
	public function update_item_permissions_check( $request ) {
		$parent_response = parent::update_item_permissions_check( $request );

		if ( is_wp_error( $parent_response ) ) {
			return $parent_response;
		}

		return $this->check_duplicate_title( $request );
	}

	/**
	 * Check if there already exists a symbol with the same title
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function check_duplicate_title( $request ) {
		$post_title = $this->get_post_title_from_request( $request );

		if ( $post_title ) {
			$post = get_page_by_title( $post_title, OBJECT, TCB_Symbols_Post_Type::SYMBOL_POST_TYPE );

			if ( $post && $post->post_status !== 'trash' ) {
				return new WP_Error( 'rest_cannot_create_post', __( 'Sorry, you are not allowed to create global elements with the same title', 'thrive-cb' ), [ 'status' => 409 ] );
			}
		}

		return true;
	}

	/**
	 * Get post title from request
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_post_title_from_request( $request ) {
		$post_title = '';
		$schema     = $this->get_item_schema();

		if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
			if ( is_string( $request['title'] ) ) {
				$post_title = $request['title'];
			} elseif ( ! empty( $request['title']['raw'] ) ) {
				$post_title = $request['title']['raw'];
			}
		}

		return $post_title;
	}

	/**
	 * Add the taxonomy data to the rest response
	 *
	 * @param WP_REST_Response $response
	 * @param WP_Post          $post
	 *
	 * @return mixed
	 */
	public function rest_prepare_symbol( $response, $post ) {
		$taxonomies = $response->data[ TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY ];

		foreach ( $taxonomies as $key => $term_id ) {
			$term                                                             = get_term_by( 'term_id', $term_id, TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );
			$response->data[ TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY ][ $key ] = $term;
		}

		/* add the thumbnail data */
		$default_thumb_data      = TCB_Utils::get_placeholder_data();
		$response->data['thumb'] = TCB_Utils::get_thumb_data( $post->ID, TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER, $default_thumb_data );

		$response->data['edit_url'] = tcb_get_editor_url( $post->ID );

		return $response;
	}

	/**
	 * After a symbol is created generate a new thumb for it ( if we are duplicating the symbol )
	 *
	 * @param WP_Post         $post    Inserted or updated post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|bool
	 */
	public function rest_insert_symbol( $post, $request ) {
		if ( isset( $request['old_id'] ) ) {
			$this->ensure_unique_title( $request, $post );
			if ( ! $this->copy_thumb( $request['old_id'], $post->ID ) ) {
				return new WP_Error( 'could_not_generate_file', __( 'We were not able to copy the symbol', 'thrive-cb' ), [ 'status' => 500 ] );
			};

			$old_global_data = get_post_meta( $request['old_id'], 'tve_globals', true );

			if ( ! empty( $old_global_data ) ) {
				update_post_meta( $post->ID, 'tve_globals', $old_global_data );
			}
		}

		update_post_meta( $post->ID, 'export_id', base_convert( time(), 10, 36 ) );

		if ( isset( $request['thumb'] ) ) {
			return $this->download_thumb( $request, $post->ID );
		}

		return true;
	}

	/**
	 * Action called after a symbol has been created
	 *
	 * @param WP_Post $post Inserted or updated post object.
	 *
	 */
	public function rest_after_insert( $post ) {
		$head_css = get_post_meta( $post->ID, 'tve_custom_css', true );
		/* update css specially when we get css from the cloud */
		update_post_meta( $post->ID, 'tve_custom_css', str_replace( '|TEMPLATE_ID|', $post->ID, $head_css ) );
	}

	/**
	 * It handles also the case when a symbol is created starting with a template from cloud
	 *
	 * @param $request WP_REST_Request
	 * @param $post_id
	 *
	 * @return bool|WP_Error
	 */
	public function download_thumb( $request, $post_id ) {
		$thumb = $request['thumb'];

		$path       = $thumb['url'];
		$upload_dir = wp_upload_dir();

		if ( strpos( $path, 'no-template-preview' ) !== false ) {
			return new WP_Error( 'could_not_generate_file', __( "The inital thumbnail doesn't exists", 'thrive-cb' ), [ 'status' => 500 ] );
		}

		if ( strpos( $path, 'http' ) === false ) {
			$path = 'http:' . $path;
		} else {
			$thumb_id = isset( $request['thumb_id'] ) ? $request['thumb_id'] : '';
			$path     = trailingslashit( $upload_dir['basedir'] ) . TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER . '/' . $thumb_id . '.png';
		}

		//check first if the directory exists. If not, create it
		$dir_path = trailingslashit( $upload_dir['basedir'] ) . TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER;
		if ( ! is_dir( $dir_path ) ) {
			wp_mkdir_p( $dir_path );
		}

		$new_path = $dir_path . '/' . $post_id . '.png';

		/* add the new thumbnail data to the post meta */
		TCB_Utils::save_thumbnail_data( $post_id, $thumb );

		return @copy( $path, $new_path );
	}

	/**
	 * When we duplicate a post, the duplicate will take the title_{id}, to not have symbols with the same name
	 *
	 * @param WP_REST_Request $request
	 * @param WP_Post         $post
	 */
	public function ensure_unique_title( $request, $post ) {
		$post_title = $this->get_post_title_from_request( $request );
		$new_title  = __( 'Copy of ', 'thrive-cb' ) . $post_title;

		$same_title_post = get_page_by_title( $new_title, OBJECT, TCB_Symbols_Post_Type::SYMBOL_POST_TYPE );

		if ( $same_title_post && $same_title_post->post_status !== 'trash' ) {
			$new_title = $new_title . '_' . $post->ID;
		}

		$post->post_title = $new_title;
		wp_update_post( $post );
	}

	/**
	 * Get path for symbol thumbnail
	 *
	 * @param int $old_id
	 * @param int $new_id
	 *
	 * @return bool
	 */
	public function copy_thumb( $old_id, $new_id ) {

		$upload_dir = wp_upload_dir();

		$old_path = trailingslashit( $upload_dir['basedir'] ) . TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER . '/' . $old_id . '.png';
		$new_path = trailingslashit( $upload_dir['basedir'] ) . TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER . '/' . $new_id . '.png';

		if ( file_exists( $old_path ) ) {
			$thumb_data = TCB_Utils::get_thumb_data( $old_id, TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER );

			if ( ! empty( $thumb_data ) ) {
				TCB_Utils::save_thumbnail_data( $new_id, $thumb_data );
			}

			return copy( $old_path, $new_path );
		}

		return true;
	}

	/**
	 * Return symbol html from meta
	 *
	 * @param array $postdata
	 *
	 * @return mixed
	 */
	public function get_symbol_html( $postdata ) {
		$symbol_id = $postdata['id'];

		return get_post_meta( $symbol_id, 'tve_updated_post', true );
	}

	/**
	 * Update symbol html from meta
	 *
	 * @param string  $meta_value
	 * @param WP_Post $post_obj
	 * @param string  $meta_key
	 *
	 * @return bool|int
	 */
	public function update_symbol_html( $meta_value, $post_obj, $meta_key ) {
		/**
		 * Update the symbol html(form meta value) for a specific meta key
		 *
		 * @param string $meta_key
		 * @param string $meta_value
		 */
		$meta_value = apply_filters( 'tve_update_symbol_html', $meta_key, $meta_value );

		return update_post_meta( $post_obj->ID, $meta_key, $meta_value );
	}

	/**
	 * Get symbol css from meta
	 *
	 * @param array $postdata
	 *
	 * @return mixed
	 */
	public function get_symbol_css( $postdata ) {
		$symbol_id = $postdata['id'];

		return get_post_meta( $symbol_id, 'tve_custom_css', true );
	}

	/**
	 * Update symbols css from meta
	 *
	 * @param string          $css existing css
	 * @param WP_Post         $post_obj
	 * @param string          $meta_key
	 * @param WP_Rest_Request $request
	 *
	 * @return bool|int
	 */
	public function update_symbol_css( $css, $post_obj, $meta_key, $request ) {
		//if old_id is sent -> we are in the duplicate cas, and we need to replace the id from the css with the new one
		if ( isset( $request['old_id'] ) ) {
			$css = str_replace( "_{$request['old_id']}", "_{$post_obj->ID}", $css );
		}

		return update_post_meta( $post_obj->ID, $meta_key, $css );
	}

	/**
	 * Move symbol from one category to another
	 *
	 * @param string  $new_term_id
	 * @param WP_Post $post_obj
	 *
	 * @return array|bool|WP_Error
	 */
	public function move_symbol( $new_term_id, $post_obj ) {

		if ( (int) $new_term_id === 0 ) {
			//if the new category is the uncategorized one, we just have to delete the existing ones
			return $this->remove_current_terms( $post_obj );
		}

		//get the new category and make sure that it exists
		$term = get_term_by( 'term_id', $new_term_id, TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );
		if ( $term ) {
			$this->remove_current_terms( $post_obj );

			return wp_set_object_terms( $post_obj->ID, $term->name, TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );
		}

		return false;
	}

	/**
	 * Remove the symbol from the current category( term )
	 *
	 * @param WP_Post $post_obj
	 *
	 * @return bool|WP_Error
	 */
	public function remove_current_terms( $post_obj ) {
		$post_terms = wp_get_post_terms( $post_obj->ID, TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );
		if ( ! empty( $post_terms ) ) {
			$term_name = $post_terms[0]->name;

			return wp_remove_object_terms( $post_obj->ID, $term_name, TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );
		}

		return true;
	}

	/**
	 * Add custom meta fields for comments to use them with the rest api
	 */
	public function register_meta_fields() {
		register_rest_field( $this->get_object_type(), 'tve_updated_post', [
			'get_callback'    => [ $this, 'get_symbol_html' ],
			'update_callback' => [ $this, 'update_symbol_html' ],
		] );

		register_rest_field( $this->get_object_type(), 'tve_custom_css', [
			'get_callback'    => [ $this, 'get_symbol_css' ],
			'update_callback' => [ $this, 'update_symbol_css' ],
		] );

		register_rest_field( $this->get_object_type(), 'move_symbol', [
			'update_callback' => [ $this, 'move_symbol' ],
		] );
	}

	/**
	 * After a category is deleted we need to move the symbols to uncategorized
	 *
	 * @param WP_Term $term The deleted term.
	 */
	public function rest_delete_category( $term ) {

		$posts = get_posts( array(
			'post_type'   => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
			'numberposts' => - 1,
			'tax_query'   => array(
				array(
					'taxonomy' => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
					'field'    => 'id',
					'terms'    => $term->term_id,
				),
			),
		) );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$this->remove_current_terms( $post );
			}
		}
	}
}
