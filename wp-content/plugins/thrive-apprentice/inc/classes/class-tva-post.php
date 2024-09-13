<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 30-May-19
 * Time: 01:49 PM
 */

use TVA\Course\Structure\TVA_Course_Structure;
use TVA\Drip\Campaign;
use TVA\Product;
use TVD\Content_Sets\Set;
use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

/**
 * Class TVA_Post
 * - wrapper over WP_Post so it can help saving data and metas for a post
 * - sets order and course to a post
 * - saves a post
 *
 * @property int                  ID
 * @property int                  course_id
 * @property string|integer       order
 * @property array                item_ids used for grouping into chapters
 * @property int                  post_parent
 * @property string               post_status
 * @property string               post_title
 * @property string               post_excerpt
 * @property string               post_name
 * @property string               post_date
 * @property string               post_date_gmt
 * @property TVA_Course_Structure $structure
 */
class TVA_Post implements JsonSerializable {

	/**
	 * Accepted post statuses
	 *
	 * @var string[]
	 */
	public static $accepted_statuses = array(
		'draft',
		'publish',
		'future',
	);

	protected $_defaults
		= array(
			'post_type'   => 'post',
			'post_status' => 'draft',
		);

	/**
	 * @var WP_Post
	 */
	protected $_post;

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @var string
	 */
	protected $_tcb_content = '';

	/**
	 * Used in drip. Determines if the TVA_Post is visible in drip context
	 *
	 * @var boolean|null
	 */
	protected $_is_content_visible;

	/**
	 * Used when duplicating. Stores pairs previous_id => current_id
	 *
	 * @var array
	 */
	protected $duplication_id_map = [];

	/**
	 * @var TVA_Course_V2
	 */
	protected $course;

	/**
	 * TVA_Post constructor.
	 *
	 * @param array|WP_Post|int $data
	 */
	public function __construct( $data ) {

		if ( true === $data instanceof WP_Post ) {
			$this->_post = $data;
		}

		if ( true === is_array( $data ) ) {
			$this->_data = $data;
		}

		if ( is_numeric( $data ) ) {
			$data = array(
				'ID' => $data,
			);
		}

		$this->_data = wp_parse_args( $data, $this->_defaults );

		if ( $this->ID && ! ( $this->_post instanceof WP_Post ) ) {
			$this->_post = get_post( $this->ID );
		}
	}

	/**
	 * Returns values from
	 * - calling method if exists
	 * - _data
	 * - _post
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {

		$value = null;

		if ( method_exists( $this, $key ) ) {
			return $this->$key();
		}

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		} elseif ( true === $this->_post instanceof WP_Post ) {
			$value = $this->_post->$key;
		}

		return $value;
	}

	/**
	 * Set values into _data
	 * - updates order
	 * - assigns course to current post
	 * - updates _post for ID key
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set( $key, $value ) {

		if ( 'order' === $key ) {
			$this->set_order( $value );

			return;
		}

		if ( 'course_id' === $key ) {
			$this->assign_to_course( $value );

			return;
		}

		if ( is_string( $key ) ) {
			$this->_data[ $key ] = $value;
		}

		if ( 'ID' === $key ) {
			$this->_post = get_post( $value );
		}
	}

	/**
	 * Based on _post->post_type saves a post meta
	 *
	 * @param string $order
	 *
	 * @return bool
	 */
	public function set_order( $order ) {

		$set = false;

		$this->_data['order'] = $order;

		if ( true === $this->_post instanceof WP_Post ) {
			update_post_meta( $this->ID, $this->_post->post_type . '_order', $order );
			$set = true;
		}

		return $set;
	}

	public function get_order() {

		$order = '';

		if ( true === $this->_post instanceof WP_Post ) {
			$order = $this->_post->{$this->_post->post_type . '_order'};
		}

		return $order;
	}

	/**
	 * Update the post or inset the data as post
	 * - updates the order
	 * - assign to a course
	 *
	 * @return true
	 * @throws Exception
	 */
	public function save() {

		$initial_post_status = $this->post_status;
		$this->post_status   = 'publish';//force WP to calculate unique post_name/slug

		if ( $this->ID ) {
			$post_id = wp_update_post( $this->_data, true );
		} else {
			$post_id = wp_insert_post( $this->_data, true );
		}

		if ( is_wp_error( $post_id ) ) {
			throw new Exception( $post_id->get_error_message(), $post_id->get_error_code() );
		}

		$this->_post = get_post( $post_id );

		if ( $this->course_id ) {
			$this->assign_to_course( $this->course_id );
		}

		if ( 'publish' !== $initial_post_status ) {//if published then there is no use to update the status again
			$this->_post->post_status = $initial_post_status;
			wp_update_post( $this->_post );
		}

		if ( isset( $this->_data['order'] ) ) {
			$this->set_order( $this->order );
		}

		$course = $this->get_course_v2();

		if ( $course ) {
			$course->update_last_edit_date();
		}

		return true;
	}

	/**
	 * duplicate Post
	 *
	 * @return TVA_Post
	 * @throws Exception
	 */
	public function duplicate() {

		$new_post = new TVA_Post(
			[
				'post_title' => $this->post_title,
				'course_id'  => $this->get_course_v2()->get_id(),
			]
		);
		$new_post->save();

		foreach ( get_post_meta( $this->ID ) as $meta_key => $post_meta_item ) {
			if ( isset( $post_meta_item[0] ) ) {
				update_post_meta( $new_post->ID, $meta_key, $post_meta_item[0] );
			}
		}

		return $new_post;
	}

	/**
	 * Assign current _post to a course term
	 *
	 * @param int|WP_Term $course
	 *
	 * @return bool
	 */
	public function assign_to_course( $course ) {

		if ( true === $course instanceof WP_Term ) {
			$course_id = $course->term_id;
		} else {
			$course_id = (int) $course;
		}

		$result = $this->_assign_post_to_course( $this->_post, $course_id );

		return false === is_wp_error( $result );
	}

	/**
	 * Assign a post and its children to a course
	 *
	 * @param $post
	 * @param $course_id
	 *
	 * @return bool
	 */
	private function _assign_post_to_course( $post, $course_id ) {

		$result = wp_set_object_terms( $post->ID, $course_id, TVA_Const::COURSE_TAXONOMY );

		if ( false === is_wp_error( $result ) ) {

			$children = TVA_Manager::get_children( $post );

			foreach ( $children as $child ) {
				$result = $this->_assign_post_to_course( $child, $course_id );
			}
		}

		return false === is_wp_error( $result );
	}

	/**
	 * Returns true if the post is published
	 *
	 * @return bool
	 */
	public function is_published() {
		return 'publish' === $this->_post->post_status;
	}

	/**
	 * Returns true if the post has been completed by the user
	 *
	 * @return false
	 */
	public function is_completed() {
		return false;
	}

	/**
	 * Return true if the object post is locked
	 * Checks the drip campaign settings
	 *
	 * @return bool
	 */
	public function is_locked() {
		return tva_access_manager()->is_object_locked( $this->_post );
	}

	/**
	 * Returns the type (text|video|audio) of the TVA_Post
	 *
	 * @return string
	 */
	public function get_type() {
		return '';
	}

	/**
	 * @return bool
	 */
	public function is_in_progress() {
		/**
		 * @var TVA_Post $active_object
		 */
		$active_object = tcb_tva_visual_builder()->get_active_object();

		return ! empty( $active_object ) && $active_object->ID === $this->ID;
	}

	/**
	 * Returns all lessons corresponding to the TVA_POST
	 * Is overwritten in TVA_Module and TVA_Chapter classes
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public function get_lessons( $filters = array() ) {
		return array();
	}


	/**
	 * Returns all items(lessons, assessments) corresponding to the TVA_POST
	 * Is overwritten in TVA_Module and TVA_Chapter classes
	 *
	 * @param $filters
	 *
	 * @return array
	 */
	public function get_items( $filters = [] ) {
		return [];
	}

	/**
	 * Factory an instance based on $post_type
	 *
	 * @param WP_Post|array $post
	 *
	 * @return TVA_Post
	 */
	public static function factory( $post ) {

		$class_name = 'TVA_Post';
		$type       = '';

		if ( is_array( $post ) ) {
			$type = $post['post_type'];
		} elseif ( true === $post instanceof WP_Post ) {
			$type = $post->post_type;
		}

		switch ( $type ) {
			case TVA_Const::LESSON_POST_TYPE:
				$class_name = 'TVA_Lesson';
				break;
			case TVA_Const::CHAPTER_POST_TYPE:
				$class_name = 'TVA_Chapter';
				break;
			case TVA_Const::MODULE_POST_TYPE:
				$class_name = 'TVA_Module';
				break;
			case TVA_Const::ASSESSMENT_POST_TYPE:
				$class_name = 'TVA_Assessment';
				break;
		}

		return new $class_name( $post );
	}

	public function get_siblings() {
		return array();
	}

	public function get_direct_children() {
		return array();
	}

	/**
	 * @return WP_Post|null
	 */
	public function get_the_post() {

		return $this->_post;
	}

	/**
	 * Factory a parent based on current post's post_parent
	 *
	 * @return TVA_Post
	 */
	public function get_parent() {

		return self::factory( $this->get_the_post() && $this->get_the_post()->post_parent ? get_post( $this->get_the_post()->post_parent ) : null );
	}

	/**
	 * Returns the parent based on post type
	 *
	 * Useful for directly get the module parent of a lesson that has also chapters
	 *
	 * @param string $post_type
	 *
	 * @return TVA_Chapter|TVA_Lesson|TVA_Module|TVA_Post|null
	 */
	public function get_parent_by_type( $post_type ) {
		$ancestors = get_post_ancestors( $this->_post );

		$return = null;

		foreach ( $ancestors as $ancestor_id ) {
			if ( get_post_type( $ancestor_id ) === $post_type ) {
				$return = self::factory( get_post( $ancestor_id ) );
				break;
			}
		}

		return $return;
	}

	/**
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function delete( $force = false ) {

		$tva_parent = $this->get_parent();
		$course     = $this->get_course_v2();
		$deleted    = false;

		if ( true === $this->_post instanceof WP_Post ) {
			$deleted = (bool) wp_delete_post( $this->_post->ID, $force );
		}

		if ( $deleted ) {
			TVA_Manager::review_status( $tva_parent->get_the_post() );
			$this->_delete_children();
		}

		if ( $course instanceof TVA_Course_V2 ) {
			$course->update_last_edit_date();
		}

		return $deleted;
	}

	protected function _delete_children() {
		/**
		 * delete lessons
		 */
		$children = TVA_Manager::get_children( $this->_post );
		/** @var WP_Post $child */
		foreach ( $children as $child ) {
			$child = self::factory( $child );
			$child->delete();
		}
	}

	public function load_structure() {
		return null;
	}

	/**
	 * Serialize data to be available on localize
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {

		return array(
			'ID'             => $this->_post->ID,
			'id'             => $this->_post->ID,
			'order'          => (int) $this->get_order(),
			'tcb_edit_url'   => tcb_get_editor_url( $this->_post->ID ),
			'post_title'     => $this->_post->post_title,
			'post_status'    => empty( $this->_post->post_status ) ? 'draft' : $this->_post->post_status,
			'post_excerpt'   => $this->_post->post_excerpt,
			'post_type'      => $this->_post->post_type,
			'post_parent'    => $this->_post->post_parent,
			'structure'      => $this->structure,
			'comment_status' => $this->_post->comment_status,
			'preview_url'    => $this->get_preview_url(),
			'post_name'      => $this->_post->post_name,
			'publish_date'   => $this->_post->post_date,
		);
	}

	/**
	 * Gets URL of current lesson|post
	 *
	 * @return string
	 */
	public function get_url() {

		return get_permalink( $this->_post );
	}

	public function get_preview_url() {

		if ( false === $this->_post instanceof WP_Post ) {
			return '';
		}

		return add_query_arg(
			array(
				'preview' => 'true',
			),
			get_permalink( $this->_post )
		);
	}

	public function get_course_v2() {

		if ( ! isset( $this->course ) ) {
			$terms = wp_get_post_terms( $this->ID, TVA_Const::COURSE_TAXONOMY );

			if ( ! empty( $terms ) ) {
				$this->course = new TVA_Course_V2( $terms[0] );
			}
		}

		return $this->course;
	}

	/**
	 * @return string
	 */
	public function get_tcb_content() {

		if ( empty( $this->_tcb_content ) ) {
			$this->_tcb_content = get_post_meta( $this->_post->ID, 'tve_updated_post', true );
		}

		return $this->_tcb_content;
	}

	/**
	 * Get post url
	 *
	 * @return string
	 */
	public function get_link() {

		return get_permalink( $this->ID );
	}

	/**
	 * If WP_Post is assigned then read the meta for cover_image
	 *
	 * @return string url for cover image
	 */
	public function inherit_cover_image() {

		$image = '';

		if ( $this->_post instanceof WP_Post ) {
			$image = $this->_post->tva_cover_image;
		}

		return $image;
	}

	/**
	 * Default implementation for a video for a course item(lesson/chapter/module)
	 *
	 * @return TVA_Video
	 */
	public function get_video() {
		return new TVA_Video( array( 'source' => '<iframe src="" frameborder="0"></iframe>' ) );
	}

	/**
	 * Get post's freemium value
	 *
	 * @return string
	 */
	public function get_freemium() {
		return $this->tva_freemium ? $this->tva_freemium : TVA_Const::FREEMIUM_INHERIT;
	}

	/**
	 * Checks if content should be available for everyone
	 *
	 * @return bool
	 */
	public function is_free_for_all() {
		return $this->get_freemium() === TVA_Const::FREEMIUM_FREE;
	}

	/**
	 * Checks if content should be free only for logged in users
	 *
	 * @return bool
	 */
	public function is_free_for_logged() {
		return $this->get_freemium() === TVA_Const::FREEMIUM_FREE_FOR_LOGGED;
	}

	/**
	 * @return array
	 */
	public function get_duplication_id_map() {
		return $this->duplication_id_map;
	}

	/**
	 * Setter for course instance
	 *
	 * @param TVA_Course_V2 $course
	 */
	public function set_course_v2( $course ) {
		$this->course = $course;
	}

	/**
	 * Used in Drip.
	 * Determines if the TVA_Post is visible in drip context
	 *
	 * @return bool
	 */
	public function is_content_visible() {

		if ( ! is_bool( $this->_is_content_visible ) ) {
			$this->_is_content_visible = true;

			/* directly check access on the course itself, not on the lesson / module - avoid a bunch of queries */
			$course = $this->get_course_v2();

			$product = Product::get_from_set( Set::get_for_object( $course->get_wp_term(), $course->get_id() ), array(), $course->get_wp_term() );

			if ( $product instanceof Product ) {
				$campaign = $product->get_drip_campaign_for_course( $this->get_course_v2()->get_id() );

				if ( $campaign instanceof Campaign && ! $campaign->should_unlock( $product->get_id(), $this->ID ) ) {
					if ( ! $campaign->get_visibility_for_post( $this->get_the_post() ) ) {
						$this->_is_content_visible = false;
					}
				}
			}
		}

		return $this->_is_content_visible;
	}

	/**
	 * By default all apprentice posts can be marked as completed
	 *
	 * @return bool
	 */
	public function can_be_marked_as_completed() {
		return true;
	}

	/**
	 * Returns true if the post is a demo content post
	 *
	 * @return bool
	 */
	public function is_demo_content() {
		return ! empty( get_post_meta( $this->ID, 'tva_is_demo', true ) );
	}

	public function pluck( $args ) {
		$data = [];
		foreach ( $args as $arg ) {
			$data[ $arg ] = $this->{$arg};
		}

		return $data;
	}
}
