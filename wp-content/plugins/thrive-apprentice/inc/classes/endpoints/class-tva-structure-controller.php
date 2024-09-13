<?php

/**
 * Class TVA_Structure_Controller
 * - works with collection of course items: modules, chapters, lessons
 */
class TVA_Structure_Controller extends WP_REST_Controller {

	/**
	 * @var string namespace
	 */
	protected $namespace = 'tva/v1';

	/**
	 * Registers rest routes
	 */
	public function register_routes() {

		/**
		 * Updates the collection of items sent from client
		 */
		register_rest_route(
			$this->namespace,
			'/structure',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/structure/update_children_comment_status',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_children_comment_status' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}


	/**
	 * Updates the collection of items
	 *
	 * @param WP_REST_Request $request
	 */
	public function update_children_comment_status( $request ) {
		$children_ids  = $request->get_param( 'ids' );
		$parent_id     = (int) $request->get_param( 'parent_id' );
		$parent_type   = $request->get_param( 'parent_type' );
		$current_value = $request->get_param( 'current_value' ); //check for opened and closed

		if ( ! in_array( $current_value, array( 'open', 'closed' ) ) || ! is_array( $children_ids ) ) {
			return new WP_REST_Response( array(
				'update' => 0,
			), 401 );
		}

		$post   = $parent_type === 'course' ? new TVA_Course( $parent_id ) : TVA_Post::factory( get_post( $parent_id ) );
		$update = $post->comment_status !== $current_value;

		if ( $update ) {

			foreach ( $children_ids as $id ) {
				wp_update_post(
					array(
						'ID'             => $id,
						'comment_status' => $current_value,
					) );
			}
		}

		return new WP_REST_Response( array(
			'update' => (int) $update,
		), 200 );
	}


	/**
	 * Updates the collection of items
	 *
	 * @param WP_REST_Request $request
	 */
	public function update( $request ) {

		$items    = $request->get_params();
		$response = null;

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				/**
				 * WishList adds to this request another param 'wlmdebug' which should be skipped in order for our structure change functionality to work
				 * I haven't found an option in their plugin to disable this
				 */
				continue;
			}
			$post              = TVA_Post::factory( get_post( $item['id'] ) );
			$old_parent        = $post->get_parent();
			$post->order       = $item['order'];
			$post->post_parent = $item['parent'];
			try {
				$post->save();
				TVA_Manager::review_status( $post->get_parent()->ID );
				TVA_Manager::review_status( $old_parent->ID );
			} catch ( Exception $e ) {
				$response = new WP_Error( 'structure_save_error', $e->getMessage() );
				break;
			}
		}

		if ( ! empty( $post ) && true === $post instanceof TVA_Post ) {
			$course   = $post->get_course_v2();
			$response = $course->load_structure();
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if the request is allowed
	 *
	 * @return bool
	 */
	public function permissions_check() {
		return TVA_Product::has_access();
	}
}
