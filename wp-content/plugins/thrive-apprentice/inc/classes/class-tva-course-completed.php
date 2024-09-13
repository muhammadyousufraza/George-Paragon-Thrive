<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use TVA\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVA_Course_Completed
 * - post which helps edit the course completion
 *
 * @property int        ID
 * @property string     title
 * @property string     post_name
 * @property string     type
 * @property string     editor_url
 * @property int|string extra_id
 * @property string     url
 * @property int        comments
 */
final class TVA_Course_Completed implements JsonSerializable {

	use TVA_Course_Post;

	/**
	 * Post type used to store course complete
	 */
	const POST_TYPE = 'tva_course_completed';

	/**
	 * Rewrite slug
	 */
	const REWRITE_SLUG = 'complete';

	/**
	 * ID for the wizard step
	 */
	const WIZARD_ID = 'course_completed';

	/**
	 * Type that represents that the completed post is part of apprentice template
	 */
	const COMPLETED_TYPE = 'completed';

	/**
	 * @var TVA_Course_Completed
	 */
	protected static $_instance;

	/**
	 * @var TVA_Course_V2
	 */
	protected $_course;

	/**
	 * @var WP_Post
	 */
	protected $_post;

	/**
	 * @return TVA_Course_Completed
	 */
	public static function instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Registers filters and actions
	 */
	private function __construct() {

		add_action( 'template_redirect', [ $this, 'template_redirect' ] );

		add_filter( 'tva_access_manager_allow_access', [ $this, 'should_have_access' ] );

		add_filter( 'tva_access_restrict_content', [ $this, 'should_restrict_content' ], 10, 2 );

		add_filter( 'tva_visual_builder_get_title', [ $this, 'should_modify_the_title' ], 10, 3 );
	}

	/**
	 * Magic get
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		$value = null;

		if ( $this->_post instanceof WP_Post ) {
			if ( isset( $this->_post->$key ) ) {
				$value = $this->_post->$key;
			} elseif ( method_exists( $this, 'get_' . $key ) ) {
				$method_name = 'get_' . $key;
				$value       = $this->$method_name();
			}
		}

		return $value;
	}

	/**
	 * @param TVA_Course_V2 $course
	 *
	 * @return TVA_Course_Completed
	 */
	public function set_course( $course ) {

		if ( true === $course instanceof TVA_Course_V2 ) {
			$this->_course = $course;
		}

		return $this;
	}

	/**
	 * @param boolean $force
	 *
	 * @return array|false|WP_Post|null
	 */
	public function ensure_post( $force = false ) {
		if ( false === $this->_course instanceof TVA_Course_V2 ) {
			return false;
		}

		/**
		 * @var $_post WP_Post
		 */
		$_post = $this->_course->has_completed_post();

		if ( $force && ! ( $_post instanceof WP_Post ) ) {

			$id = wp_insert_post(
				array(
					'post_type'   => static::POST_TYPE,
					'post_title'  => $this->_course->name . ' completed page',
					'post_name'   => $this->_course->name . '_completed_page',
					'post_status' => 'publish',
				)
			);

			if ( false === is_wp_error( $id ) ) {
				update_post_meta( $id, 'tcb2_ready', 1 );
				update_post_meta( $id, 'tcb_editor_enabled', 1 );
				update_post_meta( $id, 'tva_completed_type', static::COMPLETED_TYPE );
				update_post_meta( $_post->ID, 'tva_post_name_set', 1 );
				update_term_meta( $this->_course->term_id, 'tva_completed_post', $id );
				wp_set_object_terms( $id, $this->_course->term_id, TVA_Const::COURSE_TAXONOMY );
				$_post = get_post( $id );
			}
		} /**
		 * If post name doesn't end with _certificate, update it
		 */
		elseif ( $_post instanceof WP_Post &&
				 $_post->post_type === static::POST_TYPE &&
				 ! get_post_meta( $_post->ID, 'tva_post_name_set' ) &&
				 strpos( $_post->post_name, '_completed_page' ) === false ) {
			$_post->post_name = $this->_course->name . '_completed_page';
			wp_update_post( $_post );
			update_post_meta( $_post->ID, 'tva_post_name_set', 1 );
		}
		$this->_post = $_post;

		return $_post;
	}


	/**
	 * Returns the URL of the completed post
	 *
	 * @return string
	 */
	public function get_url() {
		$type = $this->get_type();

		if ( $type === static::COMPLETED_TYPE ) {
			$url = get_permalink( $this->_post->ID );
		} else {
			$extra_id = $this->get_extra_id();

			if ( is_numeric( $extra_id ) ) {
				$url = get_permalink( $extra_id );
			} else {
				$url = $extra_id;
			}
		}

		return $url;
	}

	/**
	 * Checks if the completed page is valid and if the front-end functionality can redirect to it
	 *
	 * @return bool
	 */
	public function is_valid() {
		$is_valid = $this->get_type() === static::COMPLETED_TYPE;
		$is_valid = $is_valid || ! empty( $this->get_extra_id() );

		return $is_valid;
	}

	/**
	 * Returns the completed post type
	 *
	 * @return string
	 */
	public function get_type() {
		return get_post_meta( $this->_post->ID, 'tva_completed_type', true );
	}

	/**
	 * In case of custom type, returns the POST ID
	 *
	 * @return int|string
	 */
	public function get_extra_id() {
		$extra_id = trim( get_post_meta( $this->_post->ID, 'tva_extra_id', true ) );

		if ( is_numeric( $extra_id ) ) {
			return (int) $extra_id;
		} elseif ( wp_http_validate_url( $extra_id ) ) {
			return $extra_id;
		}

		return '';
	}

	/**
	 * Updates the extra information for a completed page
	 *
	 * @param int|string $value
	 *
	 * @return void
	 */
	public function update_extra_id( $value ) {
		update_post_meta( $this->_post->ID, 'tva_extra_id', $value );
	}

	/**
	 * @return string
	 */
	public function get_title() {
		$type  = $this->get_type();
		$title = '';

		if ( $type === static::COMPLETED_TYPE ) {
			$title = $this->_post->post_title;
		} else {
			$extra_id = $this->get_extra_id();
			if ( is_numeric( $extra_id ) ) {
				$title = get_the_title( $this->get_extra_id() );
			}
		}

		return $title;
	}

	/**
	 * @return string
	 */
	public function get_editor_url() {
		$type = $this->get_type();

		return tcb_get_editor_url( $type === static::COMPLETED_TYPE ? $this->_post->ID : $this->get_extra_id() );
	}

	/**
	 * @return int
	 */
	public function get_comments() {
		return $this->_post->comment_status === 'open' ? 1 : 0;
	}

	/**
	 * Used on localization
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		$data = [
			'course_id' => $this->_course->term_id,
		];

		if ( $this->_post ) {
			$data = array_merge( [
				'title'          => $this->title,
				'ID'             => $this->_post->ID,
				'post_name'      => $this->post_name,
				'edit_url'       => $this->editor_url,
				'preview_url'    => $this->url,
				'type'           => $this->type,
				'extra_id'       => $this->extra_id,
				'allow_comments' => $this->comments,
			], $data );
		}

		return $data;
	}

	/**
	 * Handles redirect only for the completed page that is attached to a course
	 *
	 * @return void
	 */
	public function template_redirect() {
		if ( get_queried_object() instanceof WP_Post && get_post_type( get_queried_object() ) === static::POST_TYPE && ! tva_access_manager()->has_access() ) {

			$product = tva_access_manager()->get_product();

			tva_access_restriction_settings( $product instanceof Product ? $product->get_term() : null )->template_redirect( is_user_logged_in() ? 'locked' : null );
		}
	}

	/**
	 * Decide if the user should have access to the completed page or not
	 * A logged in user or guest user can access the completed page ONLY IF the course is completed
	 *
	 * @param boolean $access
	 *
	 * @return bool
	 */
	public function should_have_access( $access ) {

		if ( get_queried_object() instanceof WP_Post && get_post_type( get_queried_object() ) === static::POST_TYPE ) {
			if ( ! empty( tva_course()->get_id() ) ) {
				$access = tva_customer()->has_completed_course( tva_course() );
			} else {
				$access = false;
			}
		}

		return $access;
	}

	/**
	 * Modify the title with respect to the access restrictions settings the current course has
	 *
	 * @param string        $title
	 * @param TVA_Course_V2 $course
	 * @param null|TVA_Post $active_object
	 *
	 * @return string
	 */
	public function should_modify_the_title( $title, $course, $active_object ) {
		$post = get_post();

		if ( ! TVA_Product::has_access() && $post instanceof WP_Post && $post->post_type === static::POST_TYPE && ! tva_access_manager()->has_access() ) {
			$title = tva_access_restriction_settings( $course->get_product_term() )->the_title( '', '', false, is_user_logged_in() ? 'locked' : null );
		}

		return $title;
	}

	/**
	 * Should restrict content on completion post
	 *
	 * When the user is not logged in we should the not_logged state
	 * When the user is logged in but he hasn't completed the course we show the locked state
	 *
	 * @param string           $content
	 * @param TVA_Product|null $product
	 *
	 * @return string
	 */
	public function should_restrict_content( $content, $product ) {
		$post = get_post();
		if ( ! TVA_Product::has_access() && $post instanceof WP_Post && $post->post_type === static::POST_TYPE ) {

			if ( ! tva_access_manager()->has_access() ) {
				$scope   = is_user_logged_in() ? 'locked' : 'not_logged';
				$content = tva_access_restriction_settings( $product instanceof Product ? $product->get_term() : null )->output_restricted_access( false, $scope );
			}
		}

		return $content;
	}

	/**
	 * Register the course completed post type
	 *
	 * @return void
	 */
	public static function register_post_type() {
		register_post_type( static::POST_TYPE,
			[
				'labels'             => [
					'name' => 'Thrive Apprentice Course Completed',
				],
				'publicly_queryable' => true,
				'public'             => true,
				'has_archive'        => false,
				'show_ui'            => false,
				'rewrite'            => [ 'slug' => TVA_Routes::get_route( static::POST_TYPE ) ],
				'hierarchical'       => false,
				'show_in_nav_menus'  => true,
				'taxonomies'         => [ TVA_Const::COURSE_TAXONOMY ],
				'show_in_rest'       => true,
				'_edit_link'         => 'post.php?post=%d',
			]
		);
	}

	/**
	 * Duplicates the completed post on the new course
	 *
	 * @param TVA_Course_V2 $new_course
	 *
	 * @return TVA_Course_Completed
	 */
	public function duplicate( $new_course ) {
		$old_completed_post = $this->_post;
		$new_completed_post = $new_course->completed_post;
		$new_completed_post->ensure_post( true );
		wp_update_post(
			array(
				'ID'             => $new_completed_post->ID,
				'comment_status' => $old_completed_post->comment_status,
			)
		);

		$this->duplicate_post_meta( $old_completed_post, $new_completed_post );

		return $new_completed_post;
	}
}
