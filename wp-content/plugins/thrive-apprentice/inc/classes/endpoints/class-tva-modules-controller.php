<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/24/2018
 * Time: 16:38
 */

class TVA_Modules_Controller extends TVA_REST_Controller {
	/**
	 * @var string
	 */
	public $base = 'modules';

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
				'callback'            => array( $this, 'new_module' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_module' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_module' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/update_order/', array(
			array(
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'update_module_order' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/move_modules/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'move_modules' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/update_post_status/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_post_status' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/group_as_module/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'group_as_module' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );

		$this->register_routes_v2();
	}

	/**
	 * Registers v2 api routes
	 */
	public function register_routes_v2() {

		register_rest_route( self::$namespace . '2', '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_item' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( static::$namespace . '2', '/select2-' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_select2_items' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
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
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_module' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_item' ),
				'permission_callback' => array( $this, 'modules_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Saves an item into DB
	 * - adds new item
	 * - updates an existing one
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|TVA_Module|true
	 */
	public function save_item( $request ) {

		$module      = new TVA_Module( $request->get_params() );
		$merge_items = $request->get_param( 'merge_items' );

		if ( ! $module->ID && ! $module->post_name ) {
			$module->post_name = $module->post_title;
		}

		try {
			$module->save();

			if ( ! empty( $merge_items ) && is_array( $merge_items ) ) {
				foreach ( $merge_items as $child_id ) {
					wp_update_post( array(
						'ID'          => (int) $child_id,
						'post_parent' => $module->ID,
					) );
				}
			}

			return $request->get_method() === 'PATCH' ? true : $module;
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function new_module( $request ) {

		$cover_image    = $request->get_param( 'cover_image' );
		$course_id      = (int) $request->get_param( 'course_id' );
		$module_order   = (int) $request->get_param( 'order' );
		$comment_status = $request->get_param( 'comment_status' );

		$existing_chapters = $request->get_param( 'chapters' );

		if ( false === is_array( $existing_chapters ) ) {
			$existing_chapters = array();
		}

		$existing_lessons = $request->get_param( 'lessons' );
		if ( false === is_array( $existing_lessons ) ) {
			$existing_lessons = array();
		}

		$args      = array(
			'post_title'     => $request->get_param( 'post_title' ),
			'post_excerpt'   => $request->get_param( 'post_excerpt' ),
			'post_status'    => $request->get_param( 'post_status' ),
			'post_type'      => TVA_Const::MODULE_POST_TYPE,
			'comment_status' => $comment_status,
		);
		$module_id = wp_insert_post( $args );

		if ( false === is_wp_error( $module_id ) ) {
			update_post_meta( $module_id, 'tva_cover_image', $cover_image );
			/**
			 * set module order
			 */
			update_post_meta( $module_id, 'tva_module_order', $module_order );

			/**
			 * assign module to course
			 */
			wp_set_object_terms( $module_id, (int) $course_id, TVA_Const::COURSE_TAXONOMY );

			/** @var WP_Post $module */
			$module = get_post( $module_id );

			$module->order        = $module_order;
			$module->course_id    = $course_id;
			$module->cover_image  = $cover_image;
			$module->post_excerpt = $request->get_param( 'post_excerpt' );
			$module->chapters     = array();
			$module->lessons      = array();

			foreach ( $existing_chapters as &$existing_chapter ) {

				$chapter = get_post( $existing_chapter['ID'] );

				if ( false === $chapter instanceof WP_Post ) {
					continue;
				}

				wp_update_post(
					array(
						'ID'             => $chapter->ID,
						'post_parent'    => $module_id,
						'comment_status' => $comment_status,
					)
				);

				$chapter_order = $module_order . $chapter->tva_chapter_order;
				update_post_meta( $chapter->ID, 'tva_chapter_order', $chapter_order );

				$existing_chapter['module_id']   = $module_id;
				$existing_chapter['post_parent'] = $module_id;
				$module->chapters[]              = $existing_chapter;

				if ( isset( $existing_chapter['lessons'] ) && is_array( $existing_chapter['lessons'] ) ) {

					foreach ( $existing_chapter['lessons'] as $key => &$existing_chapter_lesson ) {

						$lesson = get_post( $existing_chapter_lesson['ID'] );

						if ( false === $lesson instanceof WP_Post ) {
							continue;
						}

						/**
						 * update comment status
						 */
						wp_update_post(
							array(
								'ID'             => $lesson->ID,
								'comment_status' => $comment_status,
							)
						);

						$lesson_order = $module_order . $lesson->tva_lesson_order;
						update_post_meta( $existing_chapter_lesson['ID'], 'tva_lesson_order', $lesson_order );

						$existing_chapter_lesson['comment_status'] = $comment_status;
					}
				}
			}

			foreach ( $existing_lessons as &$existing_lesson ) {

				$lesson = get_post( $existing_lesson['ID'] );

				if ( false === $lesson instanceof WP_Post ) {
					continue;
				}

				wp_update_post(
					array(
						'ID'             => $lesson->ID,
						'post_parent'    => $module_id,
						'comment_status' => $comment_status,
					)
				);

				$lesson_order = $module_order . $lesson->tva_lesson_order;
				update_post_meta( $lesson->ID, 'tva_lesson_order', $lesson_order );

				$existing_lesson['module_id']   = $module_id;
				$existing_lesson['post_parent'] = $module_id;
				$module->lessons[]              = $existing_lesson;
			}

			$module->tva_module_order = get_post_meta( $module_id, 'tva_module_order' );

			return new WP_REST_Response( $module, 200 );
		}

		return new WP_Error( 'no-results', __( $module_id, 'thrive-apprentice' ) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_module( $request ) {

		$module_id              = $request->get_param( 'ID' );
		$post_title             = $request->get_param( 'post_title' );
		$module_description     = $request->get_param( 'post_excerpt' );
		$cover_image            = $request->get_param( 'cover_image' );
		$chapters               = $request->get_param( 'chapters' );
		$lessons                = $request->get_param( 'lessons' );
		$comment_status_changed = $request->get_param( 'comment_status_changed' );

		$args = array(
			'ID'             => $module_id,
			'post_title'     => $post_title,
			'post_excerpt'   => $module_description,
			'post_status'    => $request->get_param( 'post_status' ),
			'comment_status' => $request->get_param( 'comment_status' ),
		);

		$update = wp_update_post( $args );
		$post   = get_post( $module_id );
		update_post_meta( $module_id, 'tva_cover_image', $cover_image );

		if ( $comment_status_changed ) {
			foreach ( (array) $chapters as $chapter ) {
				wp_update_post(
					array(
						'ID'             => $chapter['ID'],
						'comment_status' => $request->get_param( 'comment_status' ),
					)
				);

				foreach ( (array) $chapter['lessons'] as $lesson ) {
					wp_update_post(
						array(
							'ID'             => $lesson['ID'],
							'comment_status' => $request->get_param( 'comment_status' ),
						)
					);
				}
			}

			foreach ( (array) $lessons as $lesson ) {
				wp_update_post(
					array(
						'ID'             => $lesson['ID'],
						'comment_status' => $request->get_param( 'comment_status' ),
					)
				);
			}
		}

		if ( ! is_wp_error( $update ) ) {
			unset( $post->post_modified );
			unset( $post->post_modified_gmt );

			tva_integration_manager()->save_rules( $this->course_id, tva_integration_manager()->get_rules( new TVA_Course( $this->course_id ) ) );

			return new WP_REST_Response( $post, 200 );
		}

		return new WP_Error( 'no-results', __( $update->get_error_message(), 'thrive-apprentice' ) );
	}

	/**
	 * Update the order to show the modules to
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_module_order( $request ) {

		/**
		 * @var WP_REST_Request $request
		 */
		$order   = $request->get_param( 'order' );
		$post_id = $request->get_param( 'ID' );

		update_post_meta( $post_id, 'tva_module_order', $order );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_module( $request ) {

		$module_id = (int) $request->get_param( 'ID' );

		if ( TVA_Post::factory( get_post( $module_id ) )->delete() ) {
			return new WP_REST_Response( array(), 200 );
		}

		return new WP_Error( 'delete_failed', __( 'Failed to delete module. Please try again later!', 'thrive-apprentice' ) );
	}

	/**
	 * Move modules from one course to another
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function move_modules( $request ) {

		$id     = $request->get_param( 'ids' );
		$result = wp_set_object_terms( $id, (int) $request->get_param( 'course_id' ), TVA_Const::COURSE_TAXONOMY );

		if ( empty( $result ) || is_wp_error( $result ) ) {
			return new WP_Error( 'no-results', __( $result, 'thrive-apprentice' ) );
		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return int|WP_Error
	 */
	public function update_post_status( $request ) {

		$status  = $request->get_param( 'post_status' );
		$post_id = $request->get_param( 'post_id' );

		return wp_update_post( array( 'ID' => $post_id, 'post_status' => $status ) );
	}

	/**
	 * Check if user is logged in and is an administrator
	 *
	 * @return bool
	 */
	public function modules_permissions_check() {
		return TVA_Product::has_access();
	}

	/**
	 * Group items as modules
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function group_as_module( $request ) {

		$items     = $request->get_param( 'items' );
		$course_id = (int) $request->get_param( 'course_id' );

		foreach ( $items as $item ) {

			try {

				$module = new TVA_Module( $item );

				/**
				 * add the new chapter into DB
				 */
				$module->save();

				/**
				 * The newly created module always needs to be checked and its status updated
				 */
				$parent_ids = array( $module->ID );

				/**
				 * assign the lessons to the newly created chapter
				 */
				foreach ( $module->item_ids as $key => $child ) {

					$child_post = get_post( $child );

					if ( $child_post->post_parent ) {
						$parent_ids[] = $child_post->post_parent;
					}

					$order = $module->order . $key;

					if ( $child_post->post_type === TVA_Const::CHAPTER_POST_TYPE ) {

						$chapter              = new TVA_Chapter( array( 'ID' => $child_post->ID ) );
						$chapter->order       = $order;
						$chapter->post_parent = $module->ID;

						$chapter->save();

						$chapter_children = TVA_Manager::get_children( get_post( $chapter->ID ) );

						foreach ( $chapter_children as $index => $chapter_child ) {
							$item        = $chapter_child->post_type === TVA_Const::LESSON_POST_TYPE ? new TVA_Lesson( $chapter_child ) : new TVA_Assessment( $chapter_child );
							$item->order = $order . $index;
							$item->save();
						}
					} elseif ( $child_post->post_type === TVA_Const::LESSON_POST_TYPE ) {

						$tva_lesson              = new TVA_Lesson( $child_post );
						$tva_lesson->post_parent = $module->ID;
						$tva_lesson->order       = $order;

						$tva_lesson->save();
					} elseif ( $child_post->post_type === TVA_Const::ASSESSMENT_POST_TYPE ) {
						$tva_assessment              = new TVA_Assessment( $child_post );
						$tva_assessment->post_parent = $module->ID;
						$tva_assessment->order       = $order;

						$tva_assessment->save();
					}
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

		$course = new TVA_Course_V2( $course_id );

		return rest_ensure_response( $course->load_structure() );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_select2_items( $request ) {
		$search_key   = (string) $request->get_param( 'search' );
		$excluded_id  = (int) $request->get_param( 'exclude' );
		$modules      = TVA_Manager::search_for_modules( array( 's' => $search_key ) );
		$modules      = array_reverse( $modules );
		$courses      = array();
		$module_order = array();
		$excluded     = new TVA_Lesson( $excluded_id );
		$first_course = $excluded->get_course_v2()->get_id();

		foreach ( $modules as $module ) {
			if ( $module->post_status === 'draft' ) {
				continue;
			}

			$tva_module = new TVA_Module( $module );
			$course     = $tva_module->get_course_v2();

			if ( false === isset( $courses[ $course->get_id() ] ) ) {
				$module_order[ $course->get_id() ] = 1;
				$name                              = $course->name;

				if ( $course->get_id() === $first_course ) {
					$name .= ' <span style="text-transform: lowercase; display: inline;">(this course)</span>';
				}

				$courses[ $course->get_id() ] = array(
					'id'       => $course->get_id(),
					'text'     => $name,
					'label'    => 'Course',
					'children' => array(),
				);
			}

			$text = $tva_module->post_title;
			if ( $module->ID === $excluded_id ) {
				$text .= ' (this module)';
			}

			$courses[ $course->get_id() ]['children'][] = array(
				'id'       => $tva_module->ID,
				'label'    => 'M' . $module_order[ $course->get_id() ] ++,
				'text'     => $text,
				'disabled' => (int) $module->ID === $excluded_id,
				'type'     => 'module',
			);
		}
		$courses = tva_order_courses_by_order_flag( $courses );

		$index = array_search( $first_course, array_column( $courses, 'id' ), true );
		if ( $index !== false ) {
			$current_course = $courses[ $index ];
			unset( $courses[ $index ] );
			array_unshift( $courses, $current_course );
		}

		return new WP_REST_Response( array_values( $courses ) );
	}
}
