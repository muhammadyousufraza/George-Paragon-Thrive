<?php
/**
 * Thrive Dashboard - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Login_Editor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Post_Type
 *
 * @package TVD\Login_Editor
 */
class Post_Type {

	const NAME = 'tvd_login_edit';

	/**
	 * @var Post_Type
	 */
	private static $_instance;

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var int
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

	/**
	 * Return an instance of the post that edits the login screen
	 *
	 * @return Post_Type
	 */
	public static function instance() {

		if ( static::$_instance === null ) {
			if ( Main::is_edit_screen() ) {
				$post = get_post();
			} else {
				$posts = get_posts( array(
					'post_type'  => static::NAME,
					'meta_query' => array(
						array(
							'key'   => 'default',
							'value' => '1',
						),
					),
				) );

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
	 * Get edit url for the login page
	 *
	 * @return string
	 */
	public function get_edit_url() {
		return tcb_get_editor_url( $this->ID );
	}

	/**
	 * Get the preview url for the login page
	 *
	 * @return string|void
	 */
	public function get_preview_url() {
		return add_query_arg( array( Main::EDIT_FLAG => 1 ), get_permalink( $this->post ) );
	}

	/**
	 * Display login styles
	 *
	 * @param bool $wrap
	 * @param bool $echo
	 *
	 * @return mixed|string
	 */
	public function get_styles( $wrap = true, $echo = true ) {
		$styles = tve_get_post_meta( $this->ID, 'tve_custom_css', true );

		if ( ! empty( $styles ) ) {
			$styles = tve_prepare_global_variables_for_front( $styles );

			$styles = Main::update_logo_in_content( $styles );
		}

		if ( $wrap ) {
			$styles = sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', $styles );
		}

		if ( $echo ) {
			echo $styles; //phpcs:ignore
		} else {
			return $styles;
		}
	}

	public static function init() {
		static::register_post_type();

		add_filter( 'tve_dash_exclude_post_types_from_index', array( __CLASS__, 'exclude_from_index' ) );

		add_filter( 'tcb_post_types', array( __CLASS__, 'allow_tcb_edit' ) );

		add_filter( 'tvd_default_post_style', array( __CLASS__, 'tvd_default_post_style' ), 10, 2 );
	}

	/**
	 * Create and return the default login edit post
	 *
	 * @return array|\WP_Post|null
	 */
	public static function create_default() {
		$default = array(
			'post_title'  => Main::title(),
			'post_type'   => static::NAME,
			'post_status' => 'publish',
			'meta_input'  => array(
				'tve_custom_css' => static::get_default_style(),
			),
		);

		$post_id = wp_insert_post( $default );

		update_post_meta( $post_id, 'default', '1' );

		return get_post( $post_id );
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
	 * Allow tcb to edit the login screen
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function allow_tcb_edit( $post_types ) {
		$post_type = get_post_type();

		if ( $post_type === static::NAME ) {
			if ( ! isset( $post_types['force_whitelist'] ) ) {
				$post_types['force_whitelist'] = array();
			}

			$post_types['force_whitelist'][] = static::NAME;
		}

		return $post_types;
	}

	public static function register_post_type() {
		register_post_type(
			static::NAME,
			array(
				'public'              => isset( $_GET[ TVE_EDITOR_FLAG ] ),
				'publicly_queryable'  => is_user_logged_in(),
				'query_var'           => false,
				'exclude_from_search' => true,
				'rewrite'             => false,
				'_edit_link'          => 'post.php?post=%d',
				'map_meta_cap'        => true,
				'label'               => Main::title(),
				'capabilities'        => array(
					'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
					'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
				),
			) );
	}

	/**
	 * Return the css for the default login design
	 *
	 * @return false|string
	 */
	public static function get_default_style() {
		ob_start();
		include dirname( __DIR__ ) . '/assets/css/default-design.css';

		$default_style = ob_get_clean();

		$logo_image = Main::get_main_logo_image();
		/* We are adding also the theme logo to the default css */
		if ( ! empty( $logo_image ) ) {
			$default_style = str_replace( '#login > h1 > a {', '#login > h1 > a { background-image: url("' . $logo_image . '"); ', $default_style );
		}

		return $default_style;
	}

	/**
	 * When resetting a login design, set the default to our design
	 *
	 * @param $default_design
	 * @param $post_id
	 *
	 * @return false|string
	 */
	public static function tvd_default_post_style( $default_design, $post_id ) {

		if ( get_post_type( $post_id ) === static::NAME ) {
			$default_design = static::get_default_style();
		}

		return $default_design;
	}
}
