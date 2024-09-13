<?php

class TVA_Lessons_Controller extends TVA_REST_Controller {

	/**
	 * @var string
	 */
	public $base = 'lessons';

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
	 * Register Routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'new_lesson' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_lesson' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_lesson' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/update_order/', array(
			array(
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'update_lessons_order' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
		) );
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/move_lessons/', array(
			array(
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'move_lessons' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
		) );

		$this->register_v2_routes();
	}

	/**
	 * Registers v2 api routes
	 */
	public function register_v2_routes() {

		register_rest_route( self::$namespace . 2, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( static::$namespace . 2, '/select2-' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_select2_items' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(
					'search'  => array(
						'type'     => 'string',
						'required' => false,
					),
					'exclude' => array(
						'type'     => 'integer',
						'required' => false,
					),
				),
			),
		) );

		register_rest_route( self::$namespace . 2, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_lesson' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . 2, '/' . $this->base . '/duplicate', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'duplicate' ),
				'permission_callback' => array( $this, 'lessons_permissions_check' ),
				'args'                => array(
					'id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			),
		) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Lesson|WP_Error|true
	 */
	public function update_item( $request ) {

		$data = array_merge(
			$request->get_params(),
			[
				'edit_date'     => current_time( 'mysql' ),
				'post_date'     => $request->get_param( 'publish_date' ),
				'post_date_gmt' => tva_get_post_date_gmt( $request->get_param( 'publish_date' ) ),
			]
		);

		$lesson = new TVA_Lesson( $data );

		try {
			$lesson->save();
			$lesson->get_course_v2()->compute_type();

			return $lesson;
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Saves a new post lesson in DB
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Lesson|WP_Error
	 */
	public function create_item( $request ) {

		$lesson = new TVA_Lesson( $request->get_params() );

		if ( ! $lesson->ID && ! $lesson->post_name ) {
			$lesson->post_name = $lesson->post_title;
		}

		try {
			$lesson->save();

			return $lesson;
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Duplicates a lesson in DB
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function duplicate( $request ) {
		$id     = (int) $request->get_param( 'id' );
		$lesson = new TVA_Lesson( $id );

		try {
			$new_lesson = $lesson->duplicate( $lesson->post_parent, 'Clone of ' . $lesson->post_title );
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}

		return rest_ensure_response( $new_lesson );
	}

	/**
	 * Add a new lesson to the db
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function new_lesson( $request ) {

		/**
		 * We should add the new course
		 */
		$this->request = $request;

		$args = array(
			'post_title'     => $this->request->get_param( 'post_title' ),
			'post_type'      => TVA_Const::LESSON_POST_TYPE,
			'post_excerpt'   => $this->request->get_param( 'post_excerpt' ),
			'comment_status' => $this->request->get_param( 'comment_status' ),
			'post_category'  => array( $this->request->get_param( 'course_id' ) ),
			'post_status'    => 'draft',
			'post_parent'    => $this->request->get_param( 'post_parent' ),
		);

		$lesson_id = wp_insert_post( $args );

		if ( ! is_wp_error( $lesson_id ) ) {

			$this->post_id = $lesson_id;

			$lesson = get_post( $lesson_id );
			$this->_update_lesson_meta( $lesson );

			$order = '';

			if ( $lesson->post_parent ) {
				$parent = get_post( $lesson->post_parent );
				$order  = $parent->post_type === TVA_Const::CHAPTER_POST_TYPE ? $parent->tva_chapter_order : $parent->tva_module_order;
			}

			$order .= $this->request->get_param( 'order' );
			update_post_meta( $lesson_id, 'tva_lesson_order', $order );

			$lesson->course_id = (int) $this->request->get_param( 'course_id' );

			$lesson = tva_get_post_data( $lesson );

			$template = array(
				'lesson_type'    => $this->request->get_param( 'lesson_type' ),
				'post_media'     => $this->request->get_param( 'post_media' ),
				'comment_status' => $this->request->get_param( 'comment_status' ),
			);

			update_term_meta( $lesson->course_id, 'tva_term_lesson_template', $template );

			return new WP_REST_Response( $lesson, 200 );
		}

		return new WP_Error( 'no-results', __( $lesson_id, 'thrive-apprentice' ) );
	}

	/**
	 * Add/Update the lesson meta
	 *
	 * @param WP_Post $lesson
	 */
	private function _update_lesson_meta( $lesson ) {

		if ( false === $lesson instanceof WP_Post ) {
			return;
		}

		$lesson_id   = $lesson->ID;
		$cover_image = $this->request->get_param( 'cover_image' );

		update_post_meta( $lesson_id, 'tva_cover_image', $cover_image );
		update_post_meta( $lesson_id, 'tva_post_media', $this->request->get_param( 'post_media' ) );
		update_post_meta( $lesson_id, 'tva_lesson_type', $this->request->get_param( 'lesson_type' ) );

		$this->course_id = (int) $this->request->get_param( 'course_id' );

		wp_set_object_terms( $lesson_id, $this->course_id, TVA_Const::COURSE_TAXONOMY );
	}

	/**
	 * Delete the lesson
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_lesson( $request ) {

		$post = get_post( (int) $request->get_param( 'ID' ) );

		$lesson = TVA_Post::factory( $post );
		$course = $lesson->get_course_v2();
		/**
		 * if success then reorder its siblings
		 */
		if ( $lesson->delete() ) {

			$course->compute_type();

			return new WP_REST_Response( true, 200 );
		}

		return new WP_Error( 'no-results', __( array(), 'thrive-apprentice' ) );
	}

	/**
	 * Edit the lesson
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_lesson( $request ) {

		$this->request = $request;

		$args = array(
			'post_title'     => $this->request->get_param( 'post_title' ),
			'post_type'      => TVA_Const::LESSON_POST_TYPE,
			'post_excerpt'   => $this->request->get_param( 'post_excerpt' ),
			'post_status'    => $this->request->get_param( 'post_status' ),
			'ID'             => $this->request->get_param( 'ID' ),
			'comment_status' => $this->request->get_param( 'comment_status' ),
			'post_parent'    => $this->request->get_param( 'post_parent' ),
		);

		$lesson_id = wp_update_post( $args );

		if ( ! is_wp_error( $lesson_id ) ) {
			$this->post_id = $lesson_id;

			$this->_update_lesson_meta( get_post( $lesson_id ) );
			$course_id = (int) $this->request->get_param( 'course_id' );

			tva_integration_manager()->save_rules( $course_id, tva_integration_manager()->get_rules( new TVA_Course( $course_id ) ) );

			return new WP_REST_Response( $lesson_id, 200 );
		}

		return new WP_Error( 'no-results', __( $lesson_id, 'thrive-apprentice' ) );
	}

	/**
	 * @param $request
	 *
	 * Update the order to show the lessons to
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_lessons_order( $request ) {

		/**
		 * @var WP_REST_Request $request
		 */
		$new_order = $request->get_param( 'new_order' );

		foreach ( $new_order as $post_id => $data ) {
			if ( ! $this->course_id ) {
				$post            = get_post( (int) $post_id );
				$terms           = wp_get_post_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );
				$this->course_id = $terms[0]->term_id;
			}

			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_parent' => $data['parent'],
				)
			);
			update_post_meta( $post_id, 'tva_lesson_order', $data['order'] );
		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Move lessons form one course to another
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function move_lessons( $request ) {

		/**
		 * @var WP_REST_Request $request
		 */
		$this->request = $request;
		$ids           = $request->get_param( 'ids' );

		foreach ( $ids as $post_id ) {
			$this->post_id = $post_id;
			$result        = wp_set_object_terms( $post_id, (int) $this->request->get_param( 'course_id' ), TVA_Const::COURSE_TAXONOMY );

			if ( empty( $result ) || is_wp_error( $result ) ) {
				return new WP_Error( 'no-results', __( $result, 'thrive-apprentice' ) );
			}
		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Check if user is logged in and is an administrator
	 *
	 * @return bool
	 */
	public function lessons_permissions_check() {
		return TVA_Product::has_access();
	}

	/**
	 * Update comment status for all lessons which belongs to a given course
	 *
	 * @param array $ids
	 * @param       $comment_status
	 */
	public static function update_lessons_comment_status( $ids, $comment_status ) {

		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				wp_update_post(
					array(
						'ID'             => $id,
						'comment_status' => $comment_status,
					) );
			}
		}
	}

	/**
	 * Searches lessons and put then in response grouped by course
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_select2_items( $request ) {
		$search_key   = (string) $request->get_param( 'search' );
		$excluded_id  = (int) $request->get_param( 'exclude' );
		$lessons      = TVA_Manager::search_for_lessons( array( 's' => $search_key ) );
		$lessons      = array_reverse( $lessons );
		$courses      = array();
		$lesson_order = array();
		$excluded     = new TVA_Lesson( $excluded_id );
		$first_course = $excluded->get_course_v2()->get_id();


		foreach ( $lessons as $lesson ) {
			if ( $lesson->post_status === 'draft' ) {
				continue;
			}

			$tva_lesson = new TVA_Lesson( $lesson );
			$course     = $tva_lesson->get_course_v2();

			if ( false === isset( $courses[ $course->get_id() ] ) ) {
				$lesson_order[ $course->get_id() ] = 1;
				$name                              = $course->name;

				if ( $course->get_id() === $first_course ) {
					$name .= ' <span style="text-transform: lowercase; display: inline;">(this course)</span>';
				}

				$courses[ $course->get_id() ] = array(
					'id'       => $course->get_id(),
					'text'     => $name,
					'label'    => 'Course',
					'children' => array(),
					'data'     => array(),
				);
			}

			$text = $tva_lesson->post_title;
			if ( $lesson->ID === $excluded_id ) {
				$text .= ' (this lesson)';
			}

			$courses[ $course->get_id() ]['data'][ $tva_lesson->ID ] = array(
				'id'       => $tva_lesson->ID,
				'text'     => $text,
				'disabled' => (int) $lesson->ID === $excluded_id,
				'type'     => 'lesson',
			);
		}

		/**
		 * We need to order the lessons as they are displayed in the course structure
		 */
		foreach ( $courses as $course_id => $data ) {
			$c = new TVA_Course_V2( (int) $course_id );
			/**
			 * @var TVA_Lesson $lesson
			 */
			$ordered_lessons_ids = array_map( static function ( $lesson ) {
				return $lesson->ID;
			}, $c->get_all_lessons() );

			$ordered_lessons = array();
			$index           = 1;

			foreach ( $ordered_lessons_ids as $ordered_lesson_id ) {
				if ( ! empty( $courses[ $course_id ]['data'][ $ordered_lesson_id ] ) ) {
					$ordered_lessons[] = array_merge(
						$courses[ $course_id ]['data'][ $ordered_lesson_id ],
						array(
							'label' => 'L' . $index,
						)
					);

					$index ++;
				}
			}
			$courses[ $course_id ]['children'] = $ordered_lessons;
			unset( $courses[ $course_id ]['data'] );
		}

		/**
		 * The order of the courses is set as it appears in the admin area
		 */
		$courses = tva_order_courses_by_order_flag( $courses );
		$index   = array_search( $first_course, array_column( $courses, 'id' ), true );

		if ( $index !== false ) {
			$current_course = $courses[ $index ];

			unset( $courses[ $index ] );

			array_unshift( $courses, $current_course );
		}

		return new WP_REST_Response( array_values( $courses ) );
	}
}
