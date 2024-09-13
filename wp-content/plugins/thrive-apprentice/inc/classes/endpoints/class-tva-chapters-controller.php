<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/23/2018
 * Time: 17:46
 */

class TVA_Chapters_Controller extends TVA_REST_Controller {
	/**
	 * @var string
	 */
	public $base = 'chapters';

	/**
	 * @var
	 */
	public $post_id;
	/**
	 * @var
	 */
	public $course_id;

	/**
	 * @var WP_REST_Request $request
	 */
	public $request = array();

	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * The course Object
	 *
	 * @var TVA_Course array
	 */
	public $course;

	/**
	 * Published items
	 *
	 * @var array
	 */
	public $published = array();

	/**
	 * Register Routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'new_chapter' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_chapter' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_chapter' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/update_order/', array(
			array(
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'update_chapter_order' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
		) );
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/move_chapters/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'move_chapters' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
		) );
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/group_as_chapter/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'group_as_chapter' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
		) );

		$this->register_routes_v2();
	}

	/**
	 * Registers V2 routes
	 */
	public function register_routes_v2() {

		register_rest_route( self::$namespace . 2, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_item' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . 2, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_chapter' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_item' ),
				'permission_callback' => array( $this, 'chapters_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Saves (inserts|updates) chapter post
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Chapter|WP_Error|true
	 */
	public function save_item( $request ) {

		$chapter     = new TVA_Chapter( $request->get_params() );
		$merge_items = $request->get_param( 'merge_items' );

		try {
			$chapter->save();

			if ( ! empty( $merge_items ) && is_array( $merge_items ) ) {
				foreach ( $merge_items as $lesson_id ) {
					wp_update_post( array(
						'ID'          => (int) $lesson_id,
						'post_parent' => $chapter->ID,
					) );
				}
			}

			return $request->get_method() === 'PATCH' ? true : $chapter;
		} catch ( Exception  $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function new_chapter( $request ) {

		$parent_id     = (int) $request->get_param( 'post_parent' );
		$chapter_order = (int) $request->get_param( 'order' );
		$course_id     = (int) $request->get_param( 'course_id' );

		$lessons = $request->get_param( 'lessons' );

		if ( false === is_array( $lessons ) ) {
			$lessons = array();
		}

		$args = array(
			'post_title'     => $request->get_param( 'post_title' ),
			'post_type'      => TVA_Const::CHAPTER_POST_TYPE,
			'post_parent'    => $parent_id,
			'post_status'    => $request->get_param( 'post_status' ),
			'comment_status' => $request->get_param( 'comment_status' ),
		);

		$chapter_id = wp_insert_post( $args );

		if ( ! is_wp_error( $chapter_id ) ) {

			$module = get_post( $parent_id );

			if ( true === $module instanceof WP_Post ) {
				$chapter_order = $module->tva_module_order . $chapter_order;
			}

			/**
			 * set chapter order
			 */
			update_post_meta( $chapter_id, 'tva_chapter_order', $chapter_order );

			/**
			 * assign chapter to course/term
			 */
			wp_set_object_terms( $chapter_id, $course_id, TVA_Const::COURSE_TAXONOMY );

			$chapter            = get_post( $chapter_id );
			$chapter->course_id = $course_id;
			$chapter->order     = $chapter_order;

			if ( ! empty( $lessons ) ) {
				$chapter->lessons = array();

				foreach ( $lessons as $key => $lesson ) {
					wp_update_post( array(
						'ID'          => $lesson['ID'],
						'post_parent' => $chapter_id,
					) );

					$lesson_order               = $chapter->order . $key;
					$lesson['tva_lesson_order'] = $lesson_order;

					update_post_meta( $lesson['ID'], 'tva_lesson_order', $lesson_order );

					$lesson['post_parent'] = $chapter_id;
					$chapter->lessons[]    = $lesson;
				}
			}

			$chapter->tva_chapter_order = $chapter_order;

			return new WP_REST_Response( $chapter, 200 );
		}

		return new WP_Error( 'no-results', __( $chapter_id, 'thrive-apprentice' ) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_chapter( $request ) {

		$chapter_id = $request->get_param( 'ID' );
		$post_title = $request->get_param( 'post_title' );

		$args = array(
			'ID'          => $chapter_id,
			'post_title'  => $post_title,
			'post_status' => $request->get_param( 'post_status' ),
		);

		$update = wp_update_post( $args );
		$post   = get_post( $chapter_id );

		if ( ! is_wp_error( $update ) ) {
			/**
			 * We don't need those data and we unset them because they trigger unnecessary 'change' events in js
			 */
			unset( $post->post_modified );
			unset( $post->post_modified_gmt );

			$this->course_id = $request->get_param( 'course_id' );

			return new WP_REST_Response( $post, 200 );
		}

		return new WP_Error( 'no-results', __( $update->get_error_message(), 'thrive-apprentice' ) );
	}

	/**
	 * Update the order to show the chapters to
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_chapter_order( $request ) {

		/**
		 * @var WP_REST_Request $request
		 */
		$order   = $request->get_param( 'order' );
		$post_id = $request->get_param( 'ID' );

		update_post_meta( $post_id, 'tva_chapter_order', $order );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Move chapters around
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function move_chapters( $request ) {

		$ids    = $request->get_param( 'ids' );
		$result = wp_set_object_terms( $ids, (int) $request->get_param( 'course_id' ), TVA_Const::COURSE_TAXONOMY );

		if ( empty( $result ) || is_wp_error( $result ) ) {
			return new WP_Error( 'no-results', __( $result, 'thrive-apprentice' ) );
		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_chapter( $request ) {

		$chapter_id = (int) $request->get_param( 'ID' );

		if ( TVA_Post::factory( get_post( $chapter_id ) )->delete() ) {

			return new WP_REST_Response( true, 200 );
		}

		return new WP_Error( 'delete_failed', __( 'Failed to delete Chapter. Please try again later!', 'thrive-apprentice' ) );
	}

	/**
	 * Check if user is logged in and is an administrator
	 *
	 * @return bool
	 */
	public function chapters_permissions_check() {
		return TVA_Product::has_access();
	}

	/**
	 * Group items as chapters
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function group_as_chapter( $request ) {

		$items     = $request->get_param( 'items' );
		$course_id = (int) $request->get_param( 'course_id' );

		foreach ( $items as $item ) {

			try {

				if ( ! empty( $item['post_parent'] ) ) {
					$module        = get_post( $item['post_parent'] );
					$item['order'] = $module->tva_module_order . $item['order'];
				}

				$chapter = new TVA_Chapter( $item );

				/**
				 * add the new chapter into DB
				 */
				$chapter->save();

				/**
				 * The newly created chapter always needs to be checked and its status updated
				 */
				$parent_ids = array( $chapter->ID );

				/**
				 * assign the lessons to the newly created chapter
				 */
				foreach ( $chapter->item_ids as $key => $lesson_id ) {

					$post = get_post( $lesson_id );

					if ( $post->post_parent ) {
						$parent_ids[] = $post->post_parent;
					}

					$tva_instance              = $post->post_type === TVA_Const::ASSESSMENT_POST_TYPE ? new TVA_Assessment( $post ) : new TVA_Lesson( $post );
					$tva_instance->post_parent = $chapter->ID;
					$tva_instance->order       = $chapter->order . $key;

					$tva_instance->save();
				}

				$parent_ids = array_unique( $parent_ids );

				/**
				 * review the status of the parents from which the lessons came from
				 */
				foreach ( $parent_ids as $parent_id ) {
					TVA_Manager::review_status( $parent_id );
					TVA_Manager::review_children_order( $parent_id );
				}
			} catch ( Exception $e ) {

				return new WP_REST_Response( $e->getMessage(), 400 );
			}
		}

		/**
		 * Return the course structure
		 */
		$course = new TVA_Course_V2( $course_id );

		return rest_ensure_response( $course->load_structure() );
	}
}
