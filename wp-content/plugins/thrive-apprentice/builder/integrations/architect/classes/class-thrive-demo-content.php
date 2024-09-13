<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Demo_Content
 */
class Thrive_Demo_Content {

	const API_URL = 'https://service-api.thrivethemes.com/demo-content/';

	const POST_TYPE = 'thrive_demo_post';
	const PAGE_TYPE = 'thrive_demo_page';
	const CATEGORY  = 'thrive_demo_category';
	const TAG       = 'thrive_demo_tag';

	/**
	 * Initialize the demo content data post types, taxonomies and make sure we have posts created
	 *
	 * @param bool $force_register
	 */
	public static function init( $force_register = false ) {

		if ( $force_register || is_editor_page_raw() || Thrive_Utils::is_preview() || is_admin() || Thrive_Wizard::is_frontend() ) {

			$is_dev_env = tve_dash_is_debug_on();

			register_post_type( static::POST_TYPE, [
				'label'               => __( 'Demo Posts', 'thrive-theme' ),
				'public'              => $is_dev_env,
				'exclude_from_search' => true,
				'publicly_queryable'  => is_user_logged_in(),
				'query_var'           => false,
				'rewrite'             => false,
				'show_in_rest'        => $is_dev_env,
				'has_archive'         => true,
				'show_in_nav_menus'   => $is_dev_env,
				'show_in_menu'        => $is_dev_env,
				'show_ui'             => $is_dev_env,
				'supports'            => [ 'title', 'editor', 'thumbnail', 'comments' ],
			] );

			register_post_type( static::PAGE_TYPE, [
				'label'               => __( 'Demo Pages', 'thrive-theme' ),
				'public'              => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => is_user_logged_in(),
				'query_var'           => false,
				'rewrite'             => false,
				'show_in_rest'        => $is_dev_env,
				'has_archive'         => true,
				'show_in_nav_menus'   => $is_dev_env,
				'show_ui'             => $is_dev_env,
				'supports'            => [ 'title', 'editor', 'thumbnail', 'comments' ],
			] );

			register_taxonomy( static::CATEGORY, [ static::POST_TYPE ], [
				'label'             => __( 'Demo Categories', 'thrive-theme' ),
				'hierarchical'      => false,
				'show_ui'           => $is_dev_env,
				'show_in_nav_menus' => $is_dev_env,
				'show_admin_column' => $is_dev_env,
				'query_var'         => false,
				'show_in_rest'      => $is_dev_env,
				'public'            => $is_dev_env,
			] );

			register_taxonomy( static::TAG, [ static::POST_TYPE ], [
				'label'             => __( 'Demo Tags', 'thrive-theme' ),
				'hierarchical'      => false,
				'show_ui'           => $is_dev_env,
				'show_in_nav_menus' => $is_dev_env,
				'show_admin_column' => $is_dev_env,
				'query_var'         => false,
				'show_in_rest'      => $is_dev_env,
				'public'            => $is_dev_env,
			] );

			register_taxonomy_for_object_type( static::CATEGORY, static::POST_TYPE );
			register_taxonomy_for_object_type( static::TAG, static::POST_TYPE );

			add_filter( 'get_the_terms', [ __CLASS__, 'get_the_terms' ], 10, 3 );

			add_filter( 'get_terms', [ __CLASS__, 'get_terms' ], 10, 4 );

			add_filter( 'comments_open', [ __CLASS__, 'comments_open' ], 10, 2 );
		}
	}

	/**
	 * Generate posts with tags, categories and featured image
	 *
	 * @param Boolean $clean if we should remove the old posts before
	 */
	public static function generate( $clean = false ) {

		if ( $clean ) {
			static::clean();
		}

		if ( $clean || ! static::has_demo_blog_generated() ) {
			$demo_content = static::get_demo_content_posts();

			$post_author = static::get_post_author_id();

			foreach ( $demo_content as $content ) {
				static::generate_post( $content, $post_author );
			}
		}
	}

	/**
	 * Check if we have demo content generated
	 * @return bool
	 */
	public static function has_demo_blog_generated( $threshold = 4 ) {
		$posts = get_posts( [
			'posts_per_page' => $threshold + 1,
			'post_type'      => static::POST_TYPE,
			'fields'         => 'ids',
		] );

		return ! empty( $posts ) && count( $posts ) > $threshold;
	}

	/**
	 * Generate a sample (demo) post.
	 *
	 * @param array    $post_arr    Optional, post data to use.
	 * @param int|null $post_author Optional, post author to set.
	 *
	 * @return int|WP_Error the inserted post ID or WP in case of failure
	 */
	public static function generate_post( $post_arr = null, $post_author = null ) {
		if ( null === $post_arr ) {
			$post_arr = static::get_demo_content_posts( 1 );
			if ( empty( $post_arr ) ) {
				return new WP_Error( 'demo_404', 'Could not retrieve demo content' );
			}
			$post_arr = reset( $post_arr );
		}

		if ( null === $post_author ) {
			$post_author = static::get_post_author_id();
		}
		$post_arr['post_author'] = $post_author;
		$post_arr['post_status'] = 'publish';
		$post_arr['post_type']   = static::POST_TYPE;

		$post_id = wp_insert_post( $post_arr );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		/* attach tags to the post */
		if ( ! isset( $post_arr['tags'] ) ) {
			$post_arr['tags'] = [];
		}
		foreach ( $post_arr['tags'] as $tag ) {
			if ( ! term_exists( $tag, static::TAG ) ) {
				$term = wp_insert_term( $tag, static::TAG );
			} else {
				$term = (array) get_term_by( 'name', $tag, static::TAG );
			}

			wp_set_post_terms( $post_id, [ $term['term_id'] ], static::TAG );
		}

		/* and also categories */
		if ( ! isset( $post_arr['categories'] ) ) {
			$post_arr['categories'] = [];
		}
		foreach ( $post_arr['categories'] as $category ) {
			if ( ! term_exists( $category, static::CATEGORY ) ) {
				$term = wp_insert_term( $category, static::CATEGORY );
			} else {
				$term = (array) get_term_by( 'name', $category, static::CATEGORY );
			}

			wp_set_post_terms( $post_id, [ $term['term_id'] ], static::CATEGORY );
		}

		/* create the featured image and attach it; these will not be visible in the media library( list or modal ) */
		if ( isset( $post_arr['featured_image'] ) ) {
			$attachment_id = Thrive_Utils::create_attachment_from_image( $post_arr['featured_image'] );
			if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		}

		return $post_id;
	}

	/**
	 * Get a user that we will set as author for our demo content posts
	 *
	 * @return int
	 */
	private static function get_post_author_id() {
		$users = get_users( [ 'number' => 1 ] );

		return empty( $users ) ? 1 : $users[0]->ID;
	}

	/**
	 * Remove all demo content posts, tags, categories and featured images
	 */
	public static function clean() {
		$posts = get_posts( [
			'posts_per_page' => - 1,
			'post_type'      => static::POST_TYPE,
		] );

		foreach ( $posts as $post ) {
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			if ( $thumbnail_id ) {
				wp_delete_attachment( $thumbnail_id );
			}

			delete_post_thumbnail( $post->ID );
			wp_delete_object_term_relationships( $post->ID, [ static::TAG, static::CATEGORY ] );

			wp_delete_post( $post->ID );
		}

		$tags = get_terms( [
			'taxonomy'   => static::TAG,
			'hide_empty' => false,
		] );
		if ( ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				wp_delete_term( $tag->term_id, static::TAG );
			}
		}

		$categories = get_terms( [
			'taxonomy'   => static::CATEGORY,
			'hide_empty' => false,
		] );
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				wp_delete_term( $category->term_id, static::CATEGORY );
			}
		}
	}

	/**
	 * While displaying demo content posts, instead of tags and categories, we display the demo content ones
	 *
	 * @param $terms
	 * @param $post_id
	 * @param $taxonomy
	 *
	 * @return array|WP_Error
	 */
	public static function get_the_terms( $terms, $post_id, $taxonomy ) {

		if ( static::on_demo_content_page() ) {
			if ( $taxonomy === 'post_tag' ) {
				$terms = wp_get_post_terms( $post_id, static::TAG );
			} elseif ( $taxonomy === 'category' ) {
				$terms = wp_get_post_terms( $post_id, static::CATEGORY );
			}
		}

		return $terms;
	}

	/**
	 * Return demo content terms on the demo content page
	 *
	 * @param $terms
	 * @param $taxonomy
	 * @param $query_vars
	 * @param $term_query
	 *
	 * @return array|int|WP_Error
	 */
	public static function get_terms( $terms, $taxonomy, $query_vars, $term_query ) {

		if ( is_array( $taxonomy ) && static::on_demo_content_page() ) {
			if ( in_array( 'post_tag', $taxonomy, true ) || in_array( 'category', $taxonomy, true ) ) {
				$terms = get_terms( array_merge( $query_vars, [
					'taxonomy'   => $taxonomy === 'category' ? static::CATEGORY : static::TAG,
					'hide_empty' => false,
					'orderby'    => 'count',
					'order'      => 'DESC',
					/* make sure this always returns the expected format */
					'fields'     => isset( $query_vars['fields'] ) ? $query_vars['fields'] : 'all',
				] ) );
			}
		}

		return $terms;
	}

	/**
	 * Demo content posts always have comments open
	 *
	 * @param $open
	 * @param $post_id
	 *
	 * @return bool
	 */
	public static function comments_open( $open, $post_id ) {

		if ( get_post_type( $post_id ) === static::POST_TYPE ) {
			$open = true;
		}

		return $open;
	}

	/**
	 * Check to see if we're on a demo content page
	 *
	 * @return bool
	 */
	public static function on_demo_content_page() {
		$queried_object = get_queried_object();

		if ( $queried_object ) {
			$is_demo_content_page = is_singular( static::POST_TYPE ) || is_post_type_archive( static::POST_TYPE );
		} else {
			global $post;

			$is_demo_content_page = $post && $post->post_type === static::POST_TYPE;
		}

		return $is_demo_content_page;
	}

	/**
	 * Return demo content content url
	 *
	 * @param $preview
	 * @param $singular
	 * @param $template_id
	 *
	 * @return string
	 */
	public static function url( $preview = false, $singular = null, $template_id = 0 ) {

		if ( empty( $template_id ) ) {
			$template_id = thrive_template()->ID;
		}

		if ( $singular === null ) {
			$singular = thrive_template()->is_singular();
		}

		/**
		 * Filter that allows displaying a custom demo content when editing templates, instead of the default from TTB
		 * If a non-empty string is returned from any of the filter implementations, it will skip searching for TTB demo content
		 *
		 * @param string|null $url the URL being filtered
		 */
		$url = apply_filters( 'thrive_theme_demo_content_url', '' );

		if ( ! $url ) {
			if ( $singular ) {
				$posts = get_posts( [
					'posts_per_page' => 1,
					'post_type'      => static::POST_TYPE,
				] );

				$url = empty( $posts ) ? '' : get_permalink( $posts[0]->ID );
			} else {
				$url = get_post_type_archive_link( static::POST_TYPE );
			}
		}

		if ( empty( $url ) ) {
			return '';
		}

		if ( $preview ) {
			$args = [
				THRIVE_THEME_FLAG   => $template_id,
				THRIVE_PREVIEW_FLAG => 1,
			];
		} else {
			$args = [
				TVE_EDITOR_FLAG   => 'true',
				THRIVE_THEME_FLAG => $template_id,
				TVE_FRAME_FLAG    => wp_create_nonce( TVE_FRAME_FLAG ),
			];
		}

		return add_query_arg( $args, $url );
	}

	/**
	 * Get random demo content data
	 *
	 * @param int    $limit Limit the results to x items
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_demo_content_posts( $limit = 10, $type = 'animals' ) {
		/* make sure we have enough memory to process the results */
		wp_raise_memory_limit();
		$response = tve_dash_api_remote_get( static::API_URL . $type . '.json' );

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			$posts = [];
		} else {
			try {
				$posts = json_decode( $response['body'], true );
			} catch ( Exception $e ) {
				$posts = [];
			}
		}

        if ( ! $posts ) {
            return [];
        }

		return array_slice( $posts, 0, $limit );
	}

	/**
	 * Get a single demo custom post (used when no regular post / pages are available).
	 *
	 * @param array $args wp query args
	 *
	 * @return WP_Post|null
	 */
	public static function get_one( $args = [] ) {
		$args = wp_parse_args( $args, [
			'post_type'   => static::POST_TYPE,
			'post_status' => 'publish',
			'numberposts' => 1,
			'meta_query'  => Thrive_Utils::meta_query_no_landing_pages(),
		] );

		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			return reset( $posts );
		}

		/* nothing found, insert new post */
		$post_id = static::generate_post();

		/* error */
		if ( empty( $post_id ) || is_wp_error( $post_id ) ) {
			return null;
		}

		return get_post( $post_id );
	}
}
