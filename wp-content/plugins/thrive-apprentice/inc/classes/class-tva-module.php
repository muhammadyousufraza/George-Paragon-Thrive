<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 29-May-19
 * Time: 01:55 PM
 */

use TVA\Course\Structure\Builder\TVA_Course_Modules_Structure_Builder;
use TVA\Course\Structure\TVA_Course_Structure;
use TVA\Course\Structure\TVA_Structure_Director;
use TVA\TTB\Main;

/**
 * Class TVA_Module
 * - wrapper over WP_Post to handle Chapter Logic
 */
class TVA_Module extends TVA_Post {

	/**
	 * @var array
	 */
	protected $_defaults
		= array(
			'post_type' => TVA_Const::MODULE_POST_TYPE,
		);

	/**
	 * Meta names, without prefix, supported by a module model
	 *
	 * @var string[]
	 */
	private $_meta_names = array(
		'cover_image',
		'freemium',
	);

	public function __get( $key ) {

		if ( in_array( $key, $this->_meta_names, true ) ) {
			return $this->_post->{'tva_' . $key};
		}

		return parent::__get( $key );
	}

	public function get_siblings() {

		$siblings = array();
		$term     = TVA_Manager::get_post_term( $this->_post );

		/** @var WP_Post $item */
		foreach ( TVA_Manager::get_course_modules( $term ) as $key => $item ) {
			if ( $item->ID !== $this->_post->ID ) {
				$siblings[] = TVA_Post::factory( $item );
			}
		}

		return $siblings;
	}

	/**
	 * @return TVA_Post[]
	 */
	public function get_direct_children() {

		if ( $this->structure ) {
			return $this->structure->jsonSerialize();
		}

		$tva_children = array();
		$children     = TVA_Manager::get_module_chapters( $this->_post );

		if ( true === empty( $children ) ) {
			$children = TVA_Manager::get_topic_items( $this->_post );
		}

		foreach ( $children as $child ) {
			$tva_children[] = TVA_Post::factory( $child );
		}

		return $tva_children;
	}

	/**
	 * @return TVA_Chapter[]
	 */
	public function get_published_chapters() {

		if ( false === isset( $this->_data['published_chapters'] ) ) {
			$posts    = TVA_Manager::get_module_chapters( $this->get_the_post(), array( 'post_status' => 'publish' ) );
			$chapters = array();
			foreach ( $posts as $post ) {
				$chapters[] = TVA_Post::factory( $post );
			}
			$this->_data['published_chapters'] = $chapters;
		}

		return $this->_data['published_chapters'];
	}

	/**
	 * @return TVA_Chapter[]
	 */
	public function get_visible_chapters() {

		if ( false === isset( $this->_data['visible_chapters'] ) ) {
			$posts    = TVA_Manager::get_module_chapters( $this->get_the_post(), array( 'post_status' => 'publish' ) );
			$chapters = array();
			foreach ( $posts as $post ) {
				$chapter = TVA_Post::factory( $post );

				if ( ! is_editor_page_raw( true ) && ! $chapter->is_content_visible() ) {
					continue;
				}

				$chapters[] = $chapter;
			}
			$this->_data['visible_chapters'] = $chapters;
		}

		return $this->_data['visible_chapters'];
	}

	/**
	 * @return TVA_Lesson[]
	 */
	public function get_published_lessons() {

		if ( false === isset( $this->_data['published_lessons'] ) ) {
			$posts   = TVA_Manager::get_all_module_items( $this->get_the_post(), [
				'post_type'   => TVA_Const::LESSON_POST_TYPE,
				'post_status' => 'publish',
			] );
			$lessons = [];
			foreach ( $posts as $post ) {
				$lessons[] = TVA_Post::factory( $post );
			}
			$this->_data['published_lessons'] = $lessons;
		}

		return $this->_data['published_lessons'];
	}

	public function get_published_items() {
		if ( false === isset( $this->_data['published_items'] ) ) {
			$posts = TVA_Manager::get_all_module_items( $this->get_the_post(), [
				'post_status' => 'publish',
			] );
			$items = [];
			foreach ( $posts as $post ) {
				$items[] = TVA_Post::factory( $post );
			}
			$this->_data['published_items'] = $items;
		}

		return $this->_data['published_items'];
	}

	/**
	 * return all visible lessons from a module even though they are in chapters
	 *
	 * @return TVA_Lesson[]
	 */
	public function get_visible_lessons() {

		if ( false === isset( $this->_data['visible_lessons'] ) ) {
			$posts   = TVA_Manager::get_all_module_items( $this->get_the_post(), [
				'post_type'   => TVA_Const::LESSON_POST_TYPE,
				'post_status' => 'publish',
			] );
			$lessons = [];
			foreach ( $posts as $post ) {
				$tva_post = TVA_Post::factory( $post );

				if ( ! is_editor_page_raw( true ) && ! $tva_post->is_content_visible() && tva_access_manager()->is_object_locked( $tva_post->get_the_post() ) ) {
					continue;
				}

				$lessons[] = $tva_post;
			}
			$this->_data['visible_lessons'] = $lessons;
		}

		return $this->_data['visible_lessons'];
	}

	/**
	 * return all visible items from a module even though they are in chapters
	 *
	 * @return TVA_Post[]
	 */
	public function get_visible_items() {

		if ( false === isset( $this->_data['visible_items'] ) ) {
			$posts = TVA_Manager::get_all_module_items( $this->get_the_post(), [ 'post_status' => 'publish' ] );
			$items = [];
			foreach ( $posts as $post ) {
				$tva_post = TVA_Post::factory( $post );

				if ( ! is_editor_page_raw( true ) && ! $tva_post->is_content_visible() && tva_access_manager()->is_object_locked( $tva_post->get_the_post() ) ) {
					continue;
				}

				$items[] = $tva_post;
			}
			$this->_data['visible_items'] = $items;
		}

		return $this->_data['visible_items'];
	}

	/**
	 * Counts direct published children chapters
	 *
	 * @return int
	 */
	public function get_published_chapters_count() {

		return count( $this->get_published_chapters() );
	}

	/**
	 * Counts direct visible children chapters
	 *
	 * @return int
	 */
	public function get_visible_chapters_count() {

		return count( $this->get_visible_chapters() );
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
	 * Returns all lessons from current module even they are in chapters
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public function get_lessons( $filters = array() ) {

		$is_editor_page_raw = is_editor_page_raw( true );

		if ( ! $is_editor_page_raw ) {
			$filters['post_status'] = 'publish';
		}
		$filters['post_type'] = TVA_Const::LESSON_POST_TYPE;
		$lessons              = TVA_Manager::get_all_module_items( $this->get_the_post(), $filters );

		$lessons_objects = array();

		foreach ( $lessons as $lesson ) {
			$lessons_objects[] = TVA_Post::factory( $lesson );
		}

		return $lessons_objects;
	}

	/**
	 * Returns the first lesson from the module
	 *
	 * @return false|TVA_Lesson
	 */
	public function get_first_lesson() {
		$lessons = $this->get_lessons();

		if ( empty( $lessons ) ) {
			return false;
		}

		return reset( $lessons );
	}

	public function get_first_item() {
		$items = $this->get_items();

		if ( empty( $items ) ) {
			return false;
		}

		return reset( $items );
	}

	/**
	 * Returns the first visible lesson from the module
	 *
	 * @return false|TVA_Lesson
	 */
	public function get_first_visible_lesson() {
		$lessons = $this->get_visible_lessons();

		if ( empty( $lessons ) ) {
			return false;
		}

		return reset( $lessons );
	}

	public function get_first_visible_item() {
		$items = $this->get_visible_items();

		if ( empty( $items ) ) {
			return false;
		}

		return reset( $items );
	}

	/**
	 * Return true if the module is completed by the user
	 *
	 * A module is completed by the user if satisfy one of the following
	 *
	 * 1. If a module has chapters -> all the chapters must be completed by the user
	 * or
	 * 2. If a module has only lessons -> all the lessons must be completed by the user
	 *
	 * @return bool
	 */
	public function is_completed() {

		$is_completed = true;

		$children = $this->get_direct_children();

		/**
		 * @var TVA_Chapter|TVA_Lesson
		 */
		foreach ( $children as $child ) {
			if ( $child->is_published() && ! $child->is_completed() ) {
				$is_completed = false;
				break;
			}
		}

		return $is_completed;
	}

	/**
	 * Returns true if the user is located on the module page or if a direct child is in progress state
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		$in_progress = parent::is_in_progress();

		if ( ! $in_progress ) {

			$children = $this->get_direct_children();

			/**
			 * @var TVA_Chapter|TVA_Lesson
			 */
			foreach ( $children as $child ) {
				if ( $child->is_published() && $child->is_in_progress() ) {
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

		$builder  = new TVA_Course_Modules_Structure_Builder( $this->ID );
		$director = new TVA_Structure_Director( $builder );
		$director->build_structure();

		$this->structure = $builder->get_structure();

		foreach ( $this->structure as $item ) {
			$item->load_structure();
		}

		return $this->structure;
	}

	/**
	 * Serialize specific module data rather than parent wp post
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {

		return array_merge(
			parent::jsonSerialize(),
			array(
				'cover_image'     => $this->_post->tva_cover_image,
				'freemium'        => $this->get_freemium(),
				'has_tcb_content' => ! ! $this->get_tcb_content(),
			)
		);
	}

	/**
	 * Inherit from parent save with some meta
	 *
	 * @return true
	 * @throws Exception
	 */
	public function save() {

		parent::save();

		if ( isset( $this->_data['cover_image'] ) ) {
			update_post_meta( $this->_post->ID, 'tva_cover_image', $this->_data['cover_image'] );
		}

		if ( isset( $this->_data['freemium'] ) ) {
			update_post_meta( $this->_post->ID, 'tva_freemium', $this->_data['freemium'] );
		}

		if ( $this->get_course_v2() && false === $this->get_course_v2()->editable_module() && ! Main::uses_builder_templates() ) {
			$this->_ensure_tar_content();
		} else {
			update_post_meta( $this->_post->ID, 'tcb2_ready', true );
		}

		return true;
	}

	/**
	 * Duplicates module
	 *
	 * @return TVA_Module
	 * @throws Exception
	 */
	public function duplicate() {

		$new_module = new TVA_Module( array(
			'post_title'     => $this->post_title,
			'order'          => (int) $this->get_order(),
			'comment_status' => $this->comment_status,
			'post_excerpt'   => $this->post_excerpt,
		) );

		$new_module->save();

		foreach ( get_post_meta( $this->ID ) as $meta_key => $post_meta_item ) {
			if ( isset( $post_meta_item[0] ) ) {
				update_post_meta( $new_module->ID, $meta_key, $post_meta_item[0] );
			}
		}

		$post_name = wp_unique_post_slug( $this->post_name, $new_module->ID, 'publish', TVA_Const::MODULE_POST_TYPE, 0 );
		wp_update_post( [
			'ID'        => $new_module->ID,
			'post_name' => $post_name,
		] );

		$new_module->_post->post_name = $post_name;

		foreach ( $this->load_structure() as $child_element ) {
			$new_element                                    = $child_element->duplicate( $new_module->ID );
			$this->duplication_id_map[ $child_element->ID ] = $new_element->ID;
			foreach ( $child_element->get_duplication_id_map() as $original => $new ) {
				$this->duplication_id_map[ $original ] = $new;
			}
		}

		return $new_module;
	}

	/**
	 * Displays the module content
	 * - in TAr editor page converts the content into TAr elements
	 */
	public function the_content() {

		if ( is_editor_page() ) {

			if ( false === $this->_editable() && false === $this->get_course_v2()->editable_module() ) { //ensures old modules a content in editor
				$this->_ensure_tar_content();
				tve_load_custom_css( $this->ID );
			}

			the_content();

			return;
		}

		if ( (int) $this->tcb2_ready ) {//has been edited with TAr
			the_content();

			return;
		}

		echo tva_get_file_contents(
			'templates/module/content.php',
			array(
				'module'  => $this,
				'allowed' => tva_access_manager()->has_access(),
			)
		);
	}

	/**
	 * Sets a default TAr content for a TVA_Module post
	 */
	private function _ensure_tar_content() {

		$content = tva_get_file_contents(
			'templates/module/tar-content.php',
			array(
				'module' => $this,
			)
		);

		$style = tva_get_file_contents( 'templates/module/tar-style.php' );

		update_post_meta( $this->ID, 'tve_updated_post', $content );
		update_post_meta( $this->ID, 'tve_custom_css', $style );
		update_post_meta( $this->ID, 'tcb2_ready', 1 );
		update_post_meta( $this->ID, 'tcb_editor_enabled', 1 );
		update_post_meta( $this->ID, 'tva_editable', true );
	}

	public function _editable() {

		return (bool) $this->tva_editable;
	}

	/**
	 * Try to get image for course if the module doesn't have it set
	 *
	 * @return string
	 */
	public function inherit_cover_image() {

		$image = parent::inherit_cover_image();

		if ( empty( $image ) ) {
			$image = $this->get_course_v2()->cover_image;
		}

		return $image;
	}

	/**
	 * Check if this module is the first published one from the course
	 *
	 * @return bool
	 */
	public function is_first_published_module() {

		$modules = $this->get_course_v2()->get_published_modules();
		if ( ! empty( $modules[0] ) ) {
			return $this->ID === $modules[0]->ID;
		}

		return false;
	}

	public function get_items( $filters = [] ) {
		$is_editor_page_raw = is_editor_page_raw( true );

		if ( ! $is_editor_page_raw ) {
			$filters['post_status'] = 'publish';
		}

		$items = TVA_Manager::get_all_module_items( $this->_post, $filters );

		$items_objects = [];

		foreach ( $items as $item ) {
			$items_objects[] = TVA_Post::factory( $item );
		}

		return $items_objects;
	}
}
