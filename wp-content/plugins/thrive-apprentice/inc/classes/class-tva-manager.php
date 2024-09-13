<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 06-May-19
 * Time: 01:18 PM
 */

/**
 * Class TVA_Manager
 * - models manager
 */
class TVA_Manager {

	/**
	 * Holds a cache array for posts
	 *
	 * @var array
	 */
	public static $MANAGER_GET_POSTS_CACHE = [];

	/**
	 * Is called from functions from this class that are calling get_posts function
	 * Holds cache per request
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function get_posts_from_cache( $args ) {
		$key = md5( json_encode( $args ) );

		if ( ! isset( static::$MANAGER_GET_POSTS_CACHE[ $key ] ) ) {
			static::$MANAGER_GET_POSTS_CACHE[ $key ] = get_posts( $args );
		}

		return static::$MANAGER_GET_POSTS_CACHE[ $key ];
	}

	/**
	 * Get the posts marked as demo ( modules and lessons ). Configurable via $args
	 *
	 * @return array
	 */
	public static function get_demo_posts( $args = [] ) {
		$args = (array) wp_parse_args( $args, [
			'numberposts' => - 1,
			'post_type'   => [ TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE ],
			'meta_key'    => 'tva_is_demo',
			'meta_value'  => 1,
		] );

		return static::get_posts_from_cache( $args );
	}

	/**
	 * @return array of WP_Term(s)
	 */
	public static function get_courses() {

		$courses = [];

		$args = array(
			'taxonomy'   => TVA_Const::COURSE_TAXONOMY,
			'hide_empty' => false,
			'meta_key'   => 'tva_order',
			'orderby'    => 'meta_value',
			'order'      => 'DESC',
		);

		$terms = get_terms( $args );

		if ( false === is_wp_error( $terms ) ) {
			$courses = $terms;
		}

		return $courses;
	}

	/**
	 * Get all items(lesson/assessment) for a course
	 *
	 * @param WP_Term $course_or_arr
	 * @param array   $filters
	 *
	 * @return array
	 */
	public static function get_course_direct_items( $course_or_arr, $filters = [] ) {
		$items = [];

		if ( $course_or_arr instanceof WP_Term || is_array( $course_or_arr ) ) {
			$_defaults = array(
				'posts_per_page' => - 1,
				'post_status'    => TVA_Post::$accepted_statuses,
				'tax_query'      => [
					[
						'taxonomy' => TVA_Const::COURSE_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => is_array( $course_or_arr ) ? $course_or_arr : [ $course_or_arr->term_id ],
						'operator' => 'IN',
					],
				],
				'post_type'      => [ TVA_Const::LESSON_POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE ],
				'meta_key'       => 'tva_lesson_order',
				'post_parent'    => 0,
				'orderby'        => 'meta_value_num', //because tva_order_item is int
				'order'          => 'ASC',
			);

			$args  = wp_parse_args( $filters, $_defaults );
			$items = static::get_posts_from_cache( $args );
		}

		return $items;
	}

	/**
	 * Get all of items(e.g lesson/assessment) of a given topic(module/chapter)
	 *
	 * @param $item
	 * @param $filters
	 *
	 * @return array|mixed
	 */
	public static function get_topic_items( $item, $filters = [] ) {
		$posts = [];
		if ( $item instanceof WP_Post ) {
			$_defaults = array(
				'posts_per_page' => - 1,
				'post_status'    => TVA_Post::$accepted_statuses,
				'post_type'      => [ TVA_Const::ASSESSMENT_POST_TYPE, TVA_Const::LESSON_POST_TYPE ],
				'meta_key'       => 'tva_lesson_order',
				'post_parent'    => $item->ID,
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			);

			$args = wp_parse_args( $filters, $_defaults );

			$posts = static::get_posts_from_cache( $args );
		}

		return $posts;
	}

	/**
	 * Get all items of a module, even the module has chapters
	 *
	 * @param WP_Post $module
	 * @param array   $filters
	 *
	 * @return array
	 */
	public static function get_all_module_items( $module, $filters = [] ) {

		$items = [];

		if ( true === $module instanceof WP_Post ) {
			$items = static::get_topic_items( $module, $filters );

			/**
			 * check in chapters
			 */
			if ( empty( $items ) ) {

				$chapters = static::get_module_chapters( $module );

				foreach ( $chapters as $chapter ) {

					$chapter_items = static::get_topic_items( $chapter, $filters );
					$items         = array_merge( $items, $chapter_items );
				}
			}
		}

		return $items;
	}


	/**
	 * @param array $filters
	 *
	 * @return TVA_Assessment[]
	 */
	public static function get_assessments( $filters = [] ) {
		$posts = TVA_Manager::get_posts_from_cache( array_merge( [
			'posts_per_page' => - 1,
			'post_status'    => TVA_Post::$accepted_statuses,
			'post_type'      => [ TVA_Const::ASSESSMENT_POST_TYPE ],
		], $filters ) );

		return array_map( static function ( $post ) {
			return TVA_Post::factory( $post );
		}, $posts );
	}

	/**
	 * @param WP_Post $module
	 * @param array   $args
	 *
	 * @return WP_Post[]
	 */
	public static function get_module_chapters( $module, $args = [] ) {

		$chapters = [];

		if ( true === $module instanceof WP_Post ) {
			$defaults = array(
				'posts_per_page' => - 1,
				'post_status'    => TVA_Post::$accepted_statuses,
				'post_type'      => array( TVA_Const::CHAPTER_POST_TYPE ),
				'meta_key'       => 'tva_chapter_order',
				'post_parent'    => $module->ID,
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			);
			$args     = wp_parse_args( $args, $defaults );
			$posts    = static::get_posts_from_cache( $args );
			$chapters = $posts;
		}

		return $chapters;
	}

	public static function get_course_items( $course, $filters = [] ) {
		$items = [];
		if ( $course instanceof WP_Term ) {

			$items = static::get_course_direct_items( $course, $filters );

			if ( empty( $items ) ) {
				$modules = static::get_course_modules( $course );

				if ( empty( $modules ) ) {
					$course_chapters = static::get_course_chapters( $course );

					foreach ( $course_chapters as $course_chapter ) {
						$items = array_merge( $items, static::get_topic_items( $course_chapter ) );
					}

				} else {
					foreach ( $modules as $module ) {
						$module_chapters = static::get_module_chapters( $module );

						if ( empty( $module_chapters ) ) {
							$items = array_merge( $items, static::get_topic_items( $module ) );
						} else {
							foreach ( $module_chapters as $module_chapter ) {
								$items = array_merge( $items, static::get_topic_items( $module_chapter ) );
							}
						}
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Gets and returns the modules of a course
	 *
	 * @param WP_Term $course
	 * @param array   $filters
	 *
	 * @return array
	 */
	public static function get_course_modules( $course, $filters = [] ) {

		$modules = [];

		if ( true === $course instanceof WP_Term ) {
			$args    = array(
				'posts_per_page' => - 1,
				'post_type'      => array( TVA_Const::MODULE_POST_TYPE ),
				'post_status'    => TVA_Post::$accepted_statuses,
				'meta_key'       => 'tva_module_order',
				'post_parent'    => 0,
				'tax_query'      => array(
					array(
						'taxonomy' => TVA_Const::COURSE_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => array( $course->term_id ),
						'operator' => 'IN',
					),
				),
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			);
			$modules = static::get_posts_from_cache( wp_parse_args( $filters, $args ) );
		}

		return $modules;
	}

	/**
	 * Gets chapters at course level
	 *
	 * @param WP_Term $course
	 *
	 * @return array
	 */
	public static function get_course_chapters( $course ) {

		$chapters = [];

		if ( true === $course instanceof WP_Term ) {
			$args = array(
				'posts_per_page' => - 1,
				'post_type'      => array( TVA_Const::CHAPTER_POST_TYPE ),
				'post_status'    => TVA_Post::$accepted_statuses,
				'meta_key'       => 'tva_chapter_order',
				'post_parent'    => 0,
				'tax_query'      => array(
					array(
						'taxonomy' => TVA_Const::COURSE_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => array( $course->term_id ),
						'operator' => 'IN',
					),
				),
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			);

			$chapters = static::get_posts_from_cache( $args );
		}

		return $chapters;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public static function get_children( $post ) {

		$children           = [];
		$allowed_post_types = array(
			TVA_Const::CHAPTER_POST_TYPE,
			TVA_Const::MODULE_POST_TYPE,
		);

		if ( true === $post instanceof WP_Post && true === in_array( $post->post_type, $allowed_post_types ) ) {

			switch ( $post->post_type ) {

				case TVA_Const::CHAPTER_POST_TYPE:
					$children = static::get_topic_items( $post );
					break;

				case TVA_Const::MODULE_POST_TYPE:
					$children = static::get_module_chapters( $post );

					if ( empty( $children ) ) {
						$children = static::get_topic_items( $post );
					}
					break;
			}
		}

		return $children;
	}

	/**
	 * Review status for a post
	 * - based on published children
	 * - updates status for its parent
	 *
	 * @param int|WP_Post $post
	 */
	public static function review_status( $post ) {

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( false === $post instanceof WP_Post ) {
			return;
		}

		/**
		 * clear cache before updating children, to not mess up the course structure
		 */
		static::$MANAGER_GET_POSTS_CACHE = [];

		$_has_children = static::has_published_children( $post );

		$new_status = $_has_children ? 'publish' : 'draft';

		wp_update_post(
			array(
				'ID'          => $post->ID,
				'post_status' => $new_status,
			)
		);

		if ( $post->post_parent ) {
			static::review_status( get_post( $post->post_parent ) );
		}
	}

	public static function has_published_children( $post ) {

		$_has = false;

		$children = static::get_children( $post );

		foreach ( $children as $child ) {
			if ( $child->post_status === 'publish' ) {
				$_has = true;
				break;
			}
		}

		return $_has;
	}

	/**
	 * Based on $parent review its children order
	 *
	 * @param int|WP_Post $parent
	 */
	public static function review_children_order( $parent ) {

		if ( false === $parent instanceof WP_Post ) {
			$parent = get_post( (int) $parent );
		}

		if ( false === $parent instanceof WP_Post ) {
			return;
		}

		$post_order = $parent->{$parent->post_type . '_order'};

		$children = TVA_Manager::get_children( $parent );

		/**
		 * @var int      $index
		 * @var  WP_Post $child
		 */
		foreach ( $children as $index => $child ) {

			if ( in_array( $child->post_type, array( TVA_Const::CHAPTER_POST_TYPE, TVA_Const::MODULE_POST_TYPE ) ) ) {
				$child_order_meta = $child->post_type . '_order';
			} else {
				$child_order_meta = 'tva_lesson_order';
			}

			$new_order = $post_order . $index;

			update_post_meta( $child->ID, $child_order_meta, $new_order );

			static::review_children_order( $child );
		}
	}

	/**
	 * Based on post returns post's wp_term instance
	 *
	 * @param WP_Post $post
	 *
	 * @return WP_Term|null
	 */
	public static function get_post_term( $post ) {

		$term = null;

		if ( true === $post instanceof WP_Post ) {
			$terms = wp_get_post_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );

			$term = ! empty( $terms ) ? $terms[0] : null;
		}

		return $term;
	}

	/**
	 * Fetches all the course's posts and returns it's IDs as array
	 *
	 * @param int $course_id
	 *
	 * @return array
	 */
	public static function get_course_item_ids( $course_id ) {

		$course_id = (int) $course_id;

		if ( ! $course_id ) {
			return [];
		}

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => array( TVA_Const::LESSON_POST_TYPE, TVA_Const::CHAPTER_POST_TYPE, TVA_Const::MODULE_POST_TYPE ),
			'post_status'    => TVA_Post::$accepted_statuses,
			'tax_query'      => array(
				array(
					'taxonomy' => TVA_Const::COURSE_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => array( $course_id ),
					'operator' => 'IN',
				),
			),
			'order'          => 'ASC',
		);

		/** @var WP_Post[] $posts */
		$posts = static::get_posts_from_cache( $args );
		$ids   = [];

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$ids[] = $post->ID;
			}
		}

		return $ids;
	}

	/**
	 * Loops through the whole lists of lessons and exclude a specific amount of ids from the beginning
	 * Lessons parents are also excluded(pushed) into excluded ids
	 *
	 * @param int $course_id
	 *
	 * @return array with Post IDs which are excluded: contains TVA_Module, TVA_Chapter, TVA_Lessons
	 */
	public static function get_excluded_course_ids( $course_id ) {

		$course_id = (int) $course_id;

		$course   = get_term( $course_id );
		$excluded = (int) get_term_meta( $course_id, 'tva_excluded', true );

		if ( ! $excluded || false === $course instanceof WP_Term ) {
			return [];
		}

		$lessons = static::get_course_items( $course, [ 'post_type' => TVA_Const::LESSON_POST_TYPE ] );
		$ids     = [];

		/**
		 * loop only for exclusions
		 */
		for ( $i = 0; $i < $excluded; $i ++ ) {

			if ( ! isset( $lessons[ $i ] ) || false === $lessons[ $i ] instanceof WP_Post ) {
				break;
			}

			$lesson = TVA_Post::factory( $lessons[ $i ] );

			/**
			 * Parent can be Nothing / Module / Chapter
			 */
			$parent = $lesson->get_parent();
			if ( $parent->ID ) {
				//exclude module/chapter
				$ids[] = $parent->ID;
			}

			/**
			 * If Parent is Chapter we should get the Module's ID so that it can be set to MM access table
			 * Module Page can be accessed in frontend by visitors
			 */
			$module = $parent && $parent instanceof TVA_Chapter ? $parent->get_parent() : null;
			if ( $module ) {
				//exclude module
				$ids[] = $module->ID;
			}

			/**
			 * Push lesson to excluded IDs
			 */
			$ids[] = $lessons[ $i ]->ID;
		}

		return $ids;
	}

	/**
	 * Get all modules, chapters, lessons for a course as a flat array
	 *
	 * @param WP_Term     $course
	 * @param WP_Post     $post_parent optional, if set it will only get child items for that $post
	 * @param string|null $by_column   if sent, use this column as array keys
	 *
	 * @return WP_Post[]
	 */
	public static function get_all_content( $course, $post_parent = null, $by_column = null ) {
		$items = [];

		if ( true === $course instanceof WP_Term ) {
			$args = array(
				'posts_per_page' => - 1,
				'post_type'      => array( TVA_Const::MODULE_POST_TYPE, TVA_Const::CHAPTER_POST_TYPE, TVA_Const::LESSON_POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE ),
				'post_status'    => TVA_Post::$accepted_statuses,
				'tax_query'      => array(
					array(
						'taxonomy' => TVA_Const::COURSE_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => array( $course->term_id ),
						'operator' => 'IN',
					),
				),
			);
			if ( ! empty( $post_parent ) ) {
				$args['post_parent'] = $post_parent->ID;
			}
			$items = static::get_posts_from_cache( $args );
		}

		if ( $by_column && ! empty( $items[0]->{$by_column} ) ) {
			$result = [];
			foreach ( $items as $item ) {
				$result[ $item->{$by_column} ] = $item;
			}

			$items = $result;
		}

		return $items;
	}

	/**
	 * Returns the next user uncompleted published item
	 *
	 * @param TVA_Course_V2              $course
	 * @param null|TVA_Module|TVA_Lesson $active_object
	 * @param boolean                    $check_active
	 *
	 * @return TVA_Lesson|false
	 */
	public static function get_next_user_uncompleted_published_item( $course, $active_object = null, $check_active = false ) {

		if ( count( $course->get_ordered_published_items() ) === 0 ) {
			//All published lessons are cached on the course object
			return false;
		}

		if ( empty( $active_object ) ) {
			$item = $course->get_first_published_item();

			if ( $item instanceof TVA_Lesson || $item instanceof TVA_Assessment ) {
				return static::get_next_user_uncompleted_published_item( $course, $item, true );
			} else {
				return false;
			}
		}

		if ( $active_object instanceof TVA_Module ) {
			$item = $active_object->get_first_item();

			if ( $item instanceof TVA_Lesson || $item instanceof TVA_Assessment ) {
				return static::get_next_user_uncompleted_published_item( $course, $item, true );
			} else {
				return false;
			}
		}

		if ( $active_object instanceof TVA_Lesson || $active_object instanceof TVA_Assessment ) {

			if ( ! $check_active || $active_object->is_completed() ) {
				$course    = $active_object->get_course_v2();
				$next_item = $course->get_next_visible_item( $active_object->ID, true );

				if ( empty( $next_item ) ) {
					//No next item -> last item in the course
					return false;
				}

				return static::get_next_user_uncompleted_published_item( $course, $next_item, true );
			} else {
				return $active_object;
			}
		}

		return false;
	}

	/**
	 * Returns the next user uncompleted visible item
	 *
	 * @param TVA_Course_V2              $course
	 * @param null|TVA_Module|TVA_Lesson $active_object
	 * @param boolean                    $check_active
	 *
	 * @return TVA_Lesson|false
	 */
	public static function get_next_user_uncompleted_visible_item( $course, $active_object = null, $check_active = false ) {

		if ( count( $course->get_ordered_visible_items() ) === 0 ) {
			//All published lessons are cached on the course object
			return false;
		}

		if ( empty( $active_object ) ) {
			$item = $course->get_first_visible_item();

			if ( $item instanceof TVA_Lesson || $item instanceof TVA_Assessment ) {
				return static::get_next_user_uncompleted_visible_item( $course, $item, true );
			} else {
				return false;
			}
		}

		if ( $active_object instanceof TVA_Module ) {
			$lesson = $active_object->get_first_visible_item();

			if ( $lesson instanceof TVA_Lesson || $lesson instanceof TVA_Assessment ) {
				return static::get_next_user_uncompleted_visible_item( $course, $lesson, true );
			} else {
				return false;
			}
		}

		if ( $active_object instanceof TVA_Lesson || $active_object instanceof TVA_Assessment ) {

			if ( ! $check_active || $active_object->is_completed() ) {
				$course    = $active_object->get_course_v2();
				$next_item = $course->get_next_visible_item( $active_object->ID, true );

				if ( empty( $next_item ) ) {
					//No next item -> last item in the course
					return false;
				}

				return static::get_next_user_uncompleted_visible_item( $course, $next_item, true );
			} else {
				return $active_object;
			}
		}

		return false;
	}

	/**
	 * @param array $filters
	 *                      - 's' string
	 *                      - 'lesson_type' string from [text,video,audio]
	 *                      - 'post_parent' int|int[]
	 *                      - 'courses' array of int
	 *                      - 'author' array of int
	 *
	 * @return int[]|WP_Post[]
	 */
	public static function search_for_course_items( $filters ) {

		$_defaults = array(
			'posts_per_page' => - 1,
			'post_status'    => TVA_Post::$accepted_statuses,
		);

		$parsed = [];

		if ( ! empty( $filters['order'] ) ) {
			$parsed = array_merge( $parsed, $filters['order'] );
		}

		if ( ! empty( $filters['post_type'] ) ) {
			$parsed['post_type'] = $filters['post_type'];
		}

		//search key
		if ( ! empty( $filters['s'] ) ) {
			$parsed['s'] = sanitize_text_field( $filters['s'] );
		}

		//post parent
		if ( ! empty( $filters['post_parent'] ) ) {
			$parsed['post_parent'] = $filters['post_parent'];
		}

		//lesson type: text/video/audio
		if ( ! empty( $filters['lesson_type'] ) && true === in_array( $filters['lesson_type'], TVA_Lesson::$types, true ) ) {
			$parsed['meta_key']   = 'tva_lesson_type';
			$parsed['meta_value'] = $filters['lesson_type'];
		}

		//course/s
		if ( ! empty( $filters['courses'] ) && true === is_array( $filters['courses'] ) ) {
			$parsed['tax_query'] = array(
				array(
					'taxonomy' => TVA_Const::COURSE_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $filters['courses'] ),
					'operator' => 'IN',
				),
			);
		}

		if ( ! empty( $filters['author'] ) && true === is_array( $filters['author'] ) ) {
			$parsed['author__in'] = $filters['author'];
		}

		$parsed['meta_query'] = [
			'demo_content' => [
				'key'     => 'tva_is_demo',
				'compare' => 'NOT EXISTS',
			],
		];

		$args = wp_parse_args( $_defaults, $parsed );

		return static::get_posts_from_cache( $args );
	}

	/**
	 * Search for lesson by various filters
	 *
	 * @param $filters
	 *
	 * @return int[]|WP_Post[]
	 */
	public static function search_for_lessons( $filters ) {

		$filters['post_type'] = array( TVA_Const::LESSON_POST_TYPE );

		//lesson type: text/video/audio
		if ( ! empty( $filters['lesson_type'] ) && true === in_array( $filters['lesson_type'], TVA_Lesson::$types, true ) ) {
			$parsed['meta_key']   = 'tva_lesson_type';
			$parsed['meta_value'] = $filters['lesson_type'];
		}

		return static::search_for_course_items( $filters );
	}

	/**
	 * Search for modules by various filters
	 *
	 * @param $filters
	 *
	 * @return int[]|WP_Post[]
	 */
	public static function search_for_modules( $filters ) {

		$filters['post_type'] = array( TVA_Const::MODULE_POST_TYPE );
		$filters['order']     = array(
			'meta_key' => TVA_Const::MODULE_POST_TYPE . '_order',
			'orderby'  => 'meta_value_num',
			'order'    => 'DESC',
		);

		return static::search_for_course_items( $filters );
	}

	/**
	 * Search for chapters by various filters
	 *
	 * @param $filters
	 *
	 * @return int[]|WP_Post[]
	 */
	public static function search_for_chapters( $filters ) {

		$filters['post_type'] = array( TVA_Const::CHAPTER_POST_TYPE );

		return static::search_for_course_items( $filters );
	}
}
