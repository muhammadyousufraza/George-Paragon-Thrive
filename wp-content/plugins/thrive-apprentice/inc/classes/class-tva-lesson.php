<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 07-May-19
 * Time: 01:29 PM
 */

/**
 * Class TVA_Lesson
 *
 * @property string   cover_image
 * @property-read int $resource_count total number of resources stored for this lesson
 * @property string   comment_status
 */
class TVA_Lesson extends TVA_Post {

	/**
	 * Type of Lesson
	 *
	 * @var string
	 */
	protected $_type = '';

	/**
	 * @var array
	 */
	protected $_defaults
		= array(
			'post_type'   => TVA_Const::LESSON_POST_TYPE,
			'post_status' => 'draft',
		);

	/**
	 * Meta names, without prefix, supported by a lesson model
	 *
	 * @var string[]
	 */
	private $_meta_names
		= array(
			'lesson_type',
			'post_media',
			'cover_image',
			'video',
			'audio',
			'resource_count', // total number of resources stored for this lesson
			'freemium',
		);

	/**
	 * Types of lessons
	 *
	 * @var string[]
	 */
	public static $types = array(
		'text',
		'audio',
		'video',
	);

	/**
	 * @var int $_number in whole list of lessons
	 */
	private $_number;

	public function get_number() {

		if ( empty( $this->_number ) || false === is_int( $this->_number ) ) {

			$lessons = $this->get_course_v2()->get_ordered_published_lessons();

			foreach ( $lessons as $key => $lesson ) {
				if ( $lesson->ID === $this->ID ) {
					$this->_number = $key + 1;
					break;
				}
			}
		}

		return $this->_number;
	}

	/**
	 * Gets the previous published post from curse lessons list based on current number
	 *
	 * @param bool $to_object Whether to return a TVA_Lesson object or not
	 *
	 * @return WP_Post|TVA_Lesson|null
	 */
	public function get_previous_published_lesson( $to_object = false ) {

		$prev_lesson = null;
		$key         = $this->get_number();

		$key -= 2;

		if ( $this->get_number() > 1 ) {
			$lessons     = $this->get_course_v2()->get_ordered_published_lessons();
			$prev_lesson = isset( $lessons[ $key ] ) ? $lessons[ $key ] : null;
		}

		if ( ! $to_object && $prev_lesson instanceof TVA_Lesson ) {
			$prev_lesson = $prev_lesson->get_the_post();
		}

		return $prev_lesson;
	}

	/**
	 * Gets the previous visible post from curse lessons list based on current number
	 *
	 * @param bool $to_object Whether to return a TVA_Lesson object or not
	 *
	 * @return WP_Post|TVA_Lesson|null
	 */
	public function get_previous_visible_lesson( $to_object = false ) {

		$prev_lesson = null;
		$key         = $this->get_number();

		$key -= 2;

		if ( $this->get_number() > 1 ) {
			$lessons     = $this->get_course_v2()->get_ordered_visible_lessons();
			$prev_lesson = isset( $lessons[ $key ] ) ? $lessons[ $key ] : null;
		}

		if ( ! $to_object && $prev_lesson instanceof TVA_Lesson ) {
			$prev_lesson = $prev_lesson->get_the_post();
		}

		return $prev_lesson;
	}

	/**
	 * @param bool $to_object Whether to return a TVA_Lesson object or not
	 *
	 * @return WP_Post|TVA_Lesson|null
	 */
	public function get_next_published_lesson( $to_object = false ) {

		$key         = $this->get_number();
		$lessons     = $this->get_course_v2()->get_ordered_published_lessons();
		$next_lesson = isset( $lessons[ $key ] ) ? $lessons[ $key ] : null;

		if ( ! $to_object && $next_lesson instanceof TVA_Lesson ) {
			$next_lesson = $next_lesson->get_the_post();
		}

		return $next_lesson;
	}

	/**
	 * @param bool $to_object Whether to return a TVA_Lesson object or not
	 *
	 * @return WP_Post|TVA_Lesson|null
	 */
	public function get_next_visible_lesson( $to_object = false ) {

		$lessons = $this->get_course_v2()->get_ordered_visible_lessons();
		$key     = - 1;
		foreach ( $lessons as $i => $lesson ) {
			if ( $lesson->ID === $this->ID ) {
				$key = $i + 1;
				break;
			}
		}
		$next_lesson = isset( $lessons[ $key ] ) ? $lessons[ $key ] : null;

		if ( ! $to_object && $next_lesson instanceof TVA_Lesson ) {
			$next_lesson = $next_lesson->get_the_post();
		}

		return $next_lesson;
	}

	/**
	 * Check if this lesson is the last one from course
	 *
	 * @return bool
	 */
	public function is_last_published_lesson() {

		return empty( $this->get_next_published_lesson() );
	}

	/**
	 * Check if this lesson is the first one from course
	 *
	 * @return bool
	 */
	public function is_first_published_lesson() {

		return $this->get_number() === 1;
	}

	public function __get( $key ) {

		if ( in_array( $key, $this->_meta_names, true ) ) {
			return $this->_post->{'tva_' . $key};
		}

		return parent::__get( $key );
	}

	public function get_siblings() {

		if ( $this->post_parent ) {
			//get module or chapter children
			$posts = TVA_Manager::get_children( get_post( $this->post_parent ) );
		} else {
			//get course chapters
			$term  = TVA_Manager::get_post_term( $this->_post );
			$posts = TVA_Manager::get_course_direct_items( $term );
		}

		$siblings = array();

		/** @var WP_Post $item */
		foreach ( $posts as $key => $item ) {
			if ( $item->ID !== $this->_post->ID ) {
				$siblings[] = TVA_Post::factory( $item );
			}
		}

		return $siblings;
	}

	/**
	 * Returns the Lesson Course Object
	 *
	 * @return TVA_Course
	 */
	public function get_course() {
		$terms = wp_get_post_terms( $this->ID, TVA_Const::COURSE_TAXONOMY );

		return new TVA_Course( $terms[0] );
	}

	/**
	 * Returns the lesson details
	 *
	 * @return array
	 */
	public function get_details() {

		$course = $this->get_course();

		return array(
			'lesson_id'        => $this->ID,
			'lesson_url'       => get_permalink( $this->ID ),
			'lesson_title'     => $this->post_title,
			'lesson_type'      => $this->lesson_type,
			'lesson_image_url' => $this->cover_image,
			'module_id'        => $this->post_parent ? $this->post_parent : '',
			'module_title'     => $this->post_parent ? get_the_title( $this->post_parent ) : '',
			'course_id'        => $course->get_id(),
			'course_title'     => $course->name,
		);
	}

	/**
	 * Returns the lesson module details
	 *
	 * @return array
	 */
	public function get_module_details() {
		$module_data = array();

		if ( $this->post_parent ) {
			$course         = $this->get_course();
			$lesson_modules = $course->get_modules();
			$key            = array_search( $this->post_parent, array_column( $lesson_modules, 'ID' ), true );
			$lesson_module  = array();

			if ( $key === false ) {
				$chapter = new TVA_Chapter( $this->post_parent );

				if ( $chapter->post_parent ) {
					$key = array_search( $chapter->post_parent, array_column( $lesson_modules, 'ID' ), true );
				}
			}

			if ( $key !== false ) {
				$lesson_module = $lesson_modules[ $key ];
			}

			$module_data = array(
				'module_id'          => $lesson_module->ID,
				'module_title'       => $lesson_module->post_title,
				'module_description' => $lesson_module->post_excerpt,
				'module_image_url'   => (string) get_post_meta( $lesson_module->ID, 'tva_cover_image', true ),
				'module_url'         => get_permalink( $lesson_module->ID ),
				'course_id'          => $course->get_id(),
				'course_title'       => $course->name,
			);
		}

		return $module_data;
	}

	/**
	 * Returns the lesson module details
	 *
	 * @return TVA_Module|null
	 */
	public function get_module() {
		$module = null;

		if ( $this->post_parent ) {
			$course         = $this->get_course();
			$lesson_modules = $course->get_modules();
			$key            = array_search( $this->post_parent, array_column( $lesson_modules, 'ID' ), true );

			if ( $key === false ) {
				$chapter = new TVA_Chapter( $this->post_parent );

				if ( $chapter->post_parent ) {
					$key = array_search( $chapter->post_parent, array_column( $lesson_modules, 'ID' ), true );
				}
			}

			if ( $key !== false ) {
				$module = new TVA_Module( $lesson_modules[ $key ] );
			}
		}

		return $module;
	}

	/**
	 * Which type of lesson: text/video/etc
	 *
	 * @return string
	 */
	public function get_type() {

		if ( empty( $this->_type ) ) {
			$this->_type = get_post_meta( $this->_post->ID, 'tva_lesson_type', true );
		}

		return $this->_type;
	}

	/**
	 * Based on lesson type return a specific media
	 *
	 * @return TVA_Audio|TVA_Video|null
	 */
	public function get_media() {

		$type = $this->get_type();

		if ( 'text' === $type ) {
			return null;
		}

		return $this->{'get_' . $type}();
	}

	/**
	 * Extends the parent save with
	 * - saving post meta
	 *
	 * @return true
	 * @throws Exception
	 */
	public function save() {

		parent::save();

		foreach ( $this->_meta_names as $meta_name ) {
			if ( isset( $this->_data[ $meta_name ] ) ) {
				update_post_meta( $this->ID, 'tva_' . $meta_name, $this->_data[ $meta_name ] );
			}
		}

		$mark_complete_condition = 'video' === get_post_meta( $this->ID, 'tva_lesson_type', true ) && isset( get_post_meta( $this->ID, 'tva_video', true )['progress_enabled'] ) ? true : false;
		update_post_meta( $this->ID, 'tva_video_progress_mark_complete', [ 'video_progress' => $mark_complete_condition ] );

		return true;
	}

	/**
	 * Duplicates TVA_Lesson
	 *
	 * @param null   $post_parent
	 * @param string $name
	 *
	 * @return TVA_Lesson
	 * @throws Exception
	 */
	public function duplicate( $post_parent = null, $name = '' ) {

		$new_lesson = new TVA_Lesson(
			array(
				'post_parent'    => (int) $post_parent,
				'post_excerpt'   => $this->post_excerpt,
				'order'          => $this->get_order() + 1,
				'post_title'     => $name ? $name : $this->post_title,
				'cover_image'    => $this->cover_image,
				'lesson_type'    => $this->get_type(),
				'video'          => $this->get_video()->jsonSerialize(),
				'audio'          => $this->get_audio()->jsonSerialize(),
				'comment_status' => $this->comment_status,
				'resource_count' => $this->resource_count,
				'freemium'       => $this->get_freemium(),
			)
		);

		$this->shift_siblings();

		$new_lesson->save();
		$new_lesson->assign_to_course( $this->get_course_v2()->get_id() );

		$copy_tva_meta = [ 'tva_hide_default_resources' ];

		foreach ( get_post_meta( $this->ID ) as $meta_key => $post_meta_item ) {
			if ( isset( $post_meta_item[0] ) && ( strpos( $meta_key, 'tva_' ) !== 0 || in_array( $meta_key, $copy_tva_meta, true ) ) ) {
				$data = thrive_safe_unserialize( $post_meta_item[0] );
				update_post_meta( $new_lesson->ID, $meta_key, $data );
			}
		}

		$post_name = wp_unique_post_slug( $this->post_name, $new_lesson->ID, 'publish', TVA_Const::LESSON_POST_TYPE, $post_parent );
		wp_update_post(
			[
				'ID'        => $new_lesson->ID,
				'post_name' => $post_name,
			]
		);

		$new_lesson->_post->post_name = $post_name;

		foreach ( $this->get_resources() as $resource ) {
			$resource->duplicate( $new_lesson->ID );
		}

		return $new_lesson;
	}

	/**
	 * Shifts order of siblings
	 */
	public function shift_siblings() {
		$order = $this->get_order();

		foreach ( $this->get_siblings() as $sibling ) {
			if ( $sibling->get_order() > $order ) {
				update_post_meta( $sibling->ID, 'tva_lesson_order', $sibling->get_order() + 1 );
			}
		}
	}

	/**
	 * Reads video meta
	 *
	 * @return TVA_Video
	 */
	public function get_video() {

		$_defaults = array(
			'options' => array(),
			'type'    => 'youtube',
			'source'  => '',
		);

		$video = $this->_post->tva_video;

		if ( empty( $video ) ) {
			$video = array_merge( $_defaults, $this->_get_media() );
		}

		return new TVA_Video( $video );
	}

	/**
	 * Gets media meta for current lesson
	 * - should be empty for new items
	 *
	 * @return array
	 * @since 3.0
	 */
	private function _get_media() {

		$meta = $this->_post->tva_post_media;

		$media = array();

		if ( ! empty( $meta['media_extra_options'] ) ) {
			$media['options'] = $meta['media_extra_options'];
		}

		if ( ! empty( $meta['media_type'] ) ) {
			$media['type'] = $meta['media_type'];
		}

		if ( ! empty( $meta['media_url'] ) ) {
			$media['source'] = $meta['media_url'];
		}

		return $media;
	}

	/**
	 * Gets audio meta for current lesson
	 *
	 * @return TVA_Audio
	 */
	public function get_audio() {

		$_defaults = array(
			'options' => array(),
			'type'    => 'soundcloud',
			'source'  => '',
		);

		$audio = $this->_post->tva_audio;

		if ( empty( $audio ) ) {
			$audio = array_merge( $_defaults, $this->_get_media() );
		}

		return new TVA_Audio( $audio );
	}

	/**
	 * Serialize specific lesson data
	 * - rather than parent wp post
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		return array_merge(
			parent::jsonSerialize(),
			array(
				'lesson_type'     => $this->get_type(),
				'has_tcb_content' => ! ! $this->get_tcb_content(),
				'video'           => $this->get_video(),
				'audio'           => $this->get_audio(),
				'cover_image'     => $this->cover_image,
				'resource_count'  => $this->resource_count,
				'freemium'        => $this->get_freemium(),
			)
		);
	}

	/**
	 * Returns true if the lesson is completed by the user
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function is_completed( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$course_id = $this->get_course_v2()->get_id();
		$customer  = new TVA_Customer( $user_id );

		$course_learned_lessons = $customer->get_course_learned_lessons( $course_id );

		if ( ! empty( $course_learned_lessons ) && is_array( $course_learned_lessons ) && in_array( $this->ID, array_keys( $course_learned_lessons ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if the current lesson is in progress by the user
	 * Used in Visual Builder. The current lesson id is the post_id
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		return parent::is_in_progress();
	}

	/**
	 * Get a list of resources defined for this lesson
	 *
	 * @return TVA_Resource[]
	 */
	public function get_resources() {
		return TVA_Resource::all( $this->ID );
	}

	/**
	 * Delete a lesson. Also handles resource deletion
	 *
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function delete( $force = false ) {
		$deleted = parent::delete( $force );
		if ( $deleted ) {
			foreach ( $this->get_resources() as $resource ) {
				$deleted = $deleted && $resource->delete( $force );
			}
		}

		return $deleted;
	}

	/**
	 * Returns true if the lesson has resources inside content
	 *
	 * @return bool
	 */
	public function has_resources_in_content() {
		return ! empty( (int) get_post_meta( $this->ID, 'tva_hide_default_resources', true ) );
	}

	/**
	 * Only text lessons have cover image
	 * - try to get image from parent and course
	 *
	 * @return string
	 */
	public function inherit_cover_image() {

		$image = '';

		//only text lessons have cover image
		if ( $this->get_type() === 'text' ) {
			$image = parent::inherit_cover_image();
		}

		//try to get it from parent
		if ( empty( $image ) ) {
			$image = $this->get_parent()->inherit_cover_image();
		}

		//try to get  it from course
		if ( empty( $image ) ) {
			$image = $this->get_course_v2()->cover_image;
		}

		return $image;
	}

	/**
	 * Returns the complete conditions
	 *
	 * @return mixed
	 */
	public function get_complete_conditions() {
		return array_merge( get_post_meta( $this->ID, TVA_Const::TVA_META_NAME_CAN_MARK_COMPLETE ), get_post_meta( $this->ID, 'tva_video_progress_mark_complete' ) );
	}

	/**
	 * Returns true if the TVA_Post can be marked as completed
	 *
	 * @return boolean
	 */
	public function can_be_marked_as_completed() {
		if ( $this->is_completed() ) {
			return true;
		}

		$marked_as_completed_conditions = $this->get_complete_conditions();

		if ( empty( $marked_as_completed_conditions ) ) {
			return true;
		}

		/**
		 * Calls external functionality to check if the post can be marked as completed
		 *
		 * Used in Thrive Quiz Builder plugin
		 *
		 * @param boolean $return Returned value
		 * @param array   $marked_as_completed_conditions
		 * @param int     $lesson_id
		 */
		$can_mark_as_completed = true;
		foreach ( $marked_as_completed_conditions as $condition ) {
			$can_mark_as_completed *= apply_filters( 'tva_can_be_marked_as_completed', true, $condition, $this->ID );
		}

		return $can_mark_as_completed;
	}
}
