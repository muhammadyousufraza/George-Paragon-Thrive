<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 29-May-19
 * Time: 01:55 PM
 */

use TVA\Course\Structure\Builder\TVA_Course_Chapters_Structure_Builder;
use TVA\Course\Structure\TVA_Course_Structure;
use TVA\Course\Structure\TVA_Structure_Director;

/**
 * Class TVA_Chapter
 */
class TVA_Chapter extends TVA_Post {

	/**
	 * @var array
	 */
	protected $_defaults
		= array(
			'post_type' => TVA_Const::CHAPTER_POST_TYPE,
		);

	public function get_siblings() {

		if ( $this->post_parent ) {
			//get module children
			$posts = TVA_Manager::get_module_chapters( get_post( $this->post_parent ) );
		} else {
			//get course chapters
			$term  = TVA_Manager::get_post_term( $this->_post );
			$posts = TVA_Manager::get_course_chapters( $term );
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

	public function get_direct_children() {

		if ( $this->structure ) {
			return $this->structure->jsonSerialize();
		}

		$tva_lessons = array();

		/** @var WP_Post $item */
		foreach ( TVA_Manager::get_topic_items( $this->_post ) as $item ) {
			$tva_lessons[] = TVA_Post::factory( $item );
		}

		return $tva_lessons;
	}

	/**
	 * Returns all lessons from a chapter
	 *
	 * @param array $filters
	 *
	 * @return TVA_Lesson[]
	 */
	public function get_lessons( $filters = [] ) {

		$is_editor_page_raw = is_editor_page_raw( true );
		if ( ! $is_editor_page_raw ) {
			$filters['post_status'] = 'publish';
		}

		$tva_lessons = array();
		$filters     = array_merge( $filters, [ 'post_type' => TVA_Const::LESSON_POST_TYPE ] );
		/** @var WP_Post $item */
		foreach ( TVA_Manager::get_topic_items( $this->_post, $filters ) as $item ) {
			$tva_lessons[] = TVA_Post::factory( $item );
		}

		return $tva_lessons;
	}

	/**
	 * @return TVA_Lesson[]
	 */
	public function get_published_lessons() {

		if ( false === isset( $this->_data['published_lessons'] ) ) {
			$posts   = TVA_Manager::get_topic_items( $this->get_the_post(), [
				'post_status' => 'publish',
				'post_type'   => TVA_Const::LESSON_POST_TYPE,
			] );
			$lessons = [];
			foreach ( $posts as $post ) {
				$tva_post = TVA_Post::factory( $post );

				$lessons[] = $tva_post;
			}
			$this->_data['published_lessons'] = $lessons;
		}

		return $this->_data['published_lessons'];
	}

	/**
	 * Returns all visible lessons
	 *
	 * @return TVA_Lesson[]
	 */
	public function get_visible_lessons() {

		if ( false === isset( $this->_data['visible_lessons'] ) ) {
			$posts   = TVA_Manager::get_topic_items( $this->get_the_post(), [
				'post_status' => 'publish',
				'post_type'   => TVA_Const::LESSON_POST_TYPE,
			] );
			$lessons = [];
			foreach ( $posts as $post ) {
				$lesson = TVA_Post::factory( $post );

				if ( ! is_editor_page_raw( true ) && ! $lesson->is_content_visible() && tva_access_manager()->is_object_locked( $lesson->get_the_post() ) ) {
					continue;
				}

				$lessons[] = $lesson;
			}
			$this->_data['visible_lessons'] = $lessons;
		}

		return $this->_data['visible_lessons'];
	}

	/**
	 * Counts direct children lessons
	 *
	 * @return int
	 */
	public function get_published_lessons_count() {

		return count( $this->get_published_lessons() );
	}

	/**
	 * Counts direct visible children lessons
	 *
	 * @return int
	 */
	public function get_visible_lessons_count() {

		return count( $this->get_visible_lessons() );
	}

	/**
	 * Returns true if the chapter is completed by the user
	 *
	 * The chapter is considered completed if all the lessons have been completed by the user
	 *
	 * @return bool
	 */
	public function is_completed() {
		$is_completed = true;

		/**
		 * @var TVA_Lesson $item
		 */
		foreach ( $this->get_direct_children() as $item ) {
			if ( $item->is_published() && ! $item->is_completed() ) {
				$is_completed = false;
				break;
			}
		}

		return $is_completed;
	}

	/**
	 * Returns true if a user is located on the chapter page or a lesson from the chapter is in status in progress
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		$in_progress = parent::is_in_progress();

		if ( ! $in_progress ) {

			/**
			 * @var TVA_Lesson $item
			 */
			foreach ( $this->get_direct_children() as $item ) {
				if ( $item->is_published() && $item->is_in_progress() ) {
					$in_progress = true;
					break;
				}
			}
		}

		return $in_progress;
	}


	/**
	 * @return TVA_Course_Structure
	 */
	public function load_structure() {

		if ( ! empty( $this->structure ) ) {
			return $this->structure;
		}

		$builder  = new TVA_Course_Chapters_Structure_Builder( $this->ID );
		$director = new TVA_Structure_Director( $builder );
		$director->build_structure();

		$this->structure = $builder->get_structure();

		foreach ( $this->structure as $item ) {
			$item->load_structure();
		}

		return $this->structure;
	}

	/**
	 * Duplicate a chapter
	 *
	 * @param null $post_parent
	 *
	 * @return TVA_Chapter
	 * @throws Exception
	 */
	public function duplicate( $post_parent = null ) {

		$new_chapter = new TVA_Chapter ( array(
			'post_title'  => $this->post_title,
			'post_parent' => (int) $post_parent,
			'order'       => (int) $this->get_order(),
		) );

		$new_chapter->save();

		$this->load_structure();

		foreach ( $this->structure as $child_element ) {
			$new_child                                      = $child_element->duplicate( $new_chapter->ID );
			$this->duplication_id_map[ $child_element->ID ] = $new_child->ID;
			foreach ( $child_element->get_duplication_id_map() as $original => $new ) {
				$this->duplication_id_map[ $original ] = $new;
			}
		}

		return $new_chapter;
	}

	/**
	 * Chapters do not have image(yet)
	 * - try to get it form parent
	 *
	 * @return string
	 */
	public function inherit_cover_image() {

		$image = $this->get_parent()->inherit_cover_image();

		if ( empty( $image ) ) {
			$image = $this->get_course_v2()->cover_image;
		}

		return $image;
	}

	/**
	 * Get lessons/assessments
	 *
	 * @param $filters
	 *
	 * @return array
	 */
	public function get_items( $filters = [] ) {
		$is_editor_page_raw = is_editor_page_raw( true );

		if ( ! $is_editor_page_raw ) {
			$filters['post_status'] = 'publish';
		}

		$items = TVA_Manager::get_topic_items( $this->_post, $filters );

		$items_objects = [];

		foreach ( $items as $item ) {
			$items_objects[] = TVA_Post::factory( $item );
		}

		return $items_objects;
	}
}
