<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_Type {

	const NAME = 'tve_notifications';

	/**
	 * @var Post_Type
	 */
	private static $_instance;
	/**
	 * @var \WP_Post
	 */
	private $post;
	/**
	 * @var \WP_Post
	 */
	private $ID;

	/**
	 * Post_Type constructor.
	 *
	 * @param \WP_Post $post
	 */
	public function __construct( $post ) {
		$this->post = $post;
		$this->ID   = $post->ID;
	}

	public static function init() {
		static::register_post_type();

		add_filter( 'tve_dash_exclude_post_types_from_index', [ __CLASS__, 'exclude_from_index' ] );

		add_filter( 'tcb_post_types', [ __CLASS__, 'allow_tcb_edit' ] );

		add_filter( 'tcb_custom_post_layouts', [ __CLASS__, 'notifications_layout' ], 10, 3 );

		add_filter( 'thrive_theme_allow_body_class', [ __CLASS__, 'theme_body_class' ], 99, 1 );

		add_filter( 'thrive_theme_ignore_post_types', [ __CLASS__, 'ignore_post_type' ] );
	}

	public static function register_post_type() {
		register_post_type(
			static::NAME,
			[
				'public'              => isset( $_GET[ TVE_EDITOR_FLAG ] ),
				'publicly_queryable'  => is_user_logged_in(),
				'query_var'           => false,
				'exclude_from_search' => true,
				'rewrite'             => false,
				'_edit_link'          => 'post.php?post=%d',
				'map_meta_cap'        => true,
				'label'               => Main::title(),
				'capabilities'        => [
					'edit_others_posts'    => 'tve-edit-cpt',
					'edit_published_posts' => 'tve-edit-cpt',
				],
				'show_in_nav_menus'   => false,
				'show_in_menu'        => false,
				'show_in_rest'        => true,
				'has_archive'         => false,
			] );
	}

	/**
	 * Don't index this post type
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function exclude_from_index( $post_types ) {
		$post_types[] = static::NAME;

		return $post_types;
	}

	/**
	 * Allow tcb to edit the notification element
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function allow_tcb_edit( $post_types ) {
		if ( static::is_notification() ) {
			if ( ! isset( $post_types['force_whitelist'] ) ) {
				$post_types['force_whitelist'] = [];
			}

			$post_types['force_whitelist'][] = static::NAME;
		}

		return $post_types;
	}

	/**
	 * Insert the custom layout for the notifications editor
	 *
	 * @param $layouts
	 * @param $post_id
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function notifications_layout( $layouts, $post_id, $post_type ) {
		if ( $post_type === static::NAME ) {
			$file_path = TVE_TCB_ROOT_PATH . 'inc/views/notifications/notifications-editor.php';

			$layouts['notification_template'] = $file_path;
		}

		return $layouts;
	}

	/**
	 * Prevent adding ttb classes while editing symbols
	 *
	 * @param $allow_theme_classes
	 *
	 * @return false
	 */
	public static function theme_body_class( $allow_theme_classes ) {
		if ( static::is_notification() ) {
			$allow_theme_classes = false;
		}

		return $allow_theme_classes;
	}

	/**
	 * Do not create theme template for the notifications post type
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function ignore_post_type( $post_types ) {
		$post_types[] = 'tve_notifications';

		return $post_types;
	}

	/**
	 * Return an instance of the post that edits the notifications
	 *
	 * @return Post_Type
	 */
	public static function instance() {
		if ( static::$_instance === null ) {
			if ( Main::is_edit_screen() ) {
				$post = get_post();
			} else {
				$posts = get_posts( [
					'post_type' => static::NAME,
				] );

				if ( empty( $posts ) ) {
					$post = static::create_default();
				} else {
					$post = $posts[0];
				}
			}

			static::$_instance = new self( $post );
		}

		return static::$_instance;
	}

	/**
	 * Create and return the default notifications post
	 *
	 * @return array|\WP_Post|null
	 */
	public static function create_default() {
		$default = [
			'post_title'  => Main::title(),
			'post_type'   => static::NAME,
			'post_status' => 'publish',
			'meta_input'  => [
				'default' => '0',
			],
		];

		$post_id = wp_insert_post( $default );

		update_option( Main::OPTION_NAME, $post_id );

		return get_post( $post_id );
	}

	/**
	 * Get edit url for the notifications page
	 *
	 * @return string
	 */
	public function get_edit_url() {
		return tcb_get_editor_url( $this->ID );
	}

	public function get_id() {
		return $this->ID;
	}

	/**
	 * Check if we're on a notification post type
	 *
	 * @return bool
	 */
	public static function is_notification() {
		$post_type = get_post_type();

		if ( empty( $post_type ) && is_admin() && isset( $_GET['post'] ) ) {
			$post_type = get_post_type( $_GET['post'] );
		}

		return $post_type === static::NAME;
	}
}
