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
 * Class Thrive_Dynamic_List_Helper
 */
class Thrive_Dynamic_List_Helper {

	/**
	 * @var boolean
	 */
	private $use_demo_content;

	/**
	 * @var mixed
	 */
	private $type;

	/**
	 * @var array
	 */
	private $query;

	/**
	 * Thrive_Dynamic_List_Helper constructor.
	 *
	 * @param array   $query
	 * @param boolean $use_demo_content
	 */
	public function __construct( $query, $use_demo_content ) {
		$this->type             = $this->get_type_from_query( $query );
		$this->use_demo_content = $use_demo_content;

		$defaults    = $this->get_defaults();
		$this->query = wp_parse_args( $query, $defaults[ $this->type ] );
	}

	/**
	 *
	 * @return array
	 */
	public function get_results() {
		$fn = 'get_' . $this->type;

		return method_exists( $this, $fn ) ? $this->$fn() : [];
	}

	/**
	 * Return the list type based on the query arguments
	 *
	 * @param array $query
	 *
	 * @return mixed|string
	 */
	public function get_type_from_query( $query ) {
		$type = is_array( $query['post_type'] ) ? $query['post_type'][0] : $query['post_type'];

		/* Backwards compatibility - we changed the type from posts to post */
		if ( $type === 'posts' ) {
			$type = 'post';
		}

		return $type;
	}

	/**
	 * Return query vars for categories and tags
	 *
	 * @return array
	 */
	public function get_terms_query_vars() {
		if ( $this->type === 'categories' ) {
			$taxonomy = $this->use_demo_content ? Thrive_Demo_Content::CATEGORY : 'category';
		} else {
			$taxonomy = $this->use_demo_content ? Thrive_Demo_Content::TAG : 'post_tag';
		}

		$query_vars = array_merge( $this->query, [
			'taxonomy' => $taxonomy,
			'number'   => isset( $this->query['posts_per_page'] ) ? $this->query['posts_per_page'] : false,
		] );

		$query_vars['orderby'] = empty( $query_vars['orderby'] ) ? 'name' : $query_vars['orderby'];

		if ( isset( $this->query['rules'] ) ) {
			foreach ( $this->query['rules'] as $rule ) {
				if ( ! empty( $rule['terms'] ) ) {
					$query_vars['exclude'] = empty( $query_vars['exclude'] ) ? $rule['terms'] : array_merge( $query_vars['exclude'], $rule['terms'] );
				}
			}
		}

		return $query_vars;
	}

	/**
	 * Generic function to get terms
	 *
	 * @param $args
	 *
	 * @return array
	 */
	private function get_terms( $args = [] ) {
		$terms = [];
		$args  = array_merge( [
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'DESC',
		], $args );

		$all_terms = get_terms( $args );
		foreach ( $all_terms as $term ) {
			$terms[ $term->term_id ] = [
				'name' => $term->name,
				'url'  => get_term_link( $term ),
			];
		}

		return $terms;
	}

	/**
	 * Get categories for the dynamic list element
	 *
	 * @return array
	 */
	public function get_categories() {

		$query_vars = $this->get_terms_query_vars();

		return $this->get_terms( $query_vars );
	}

	/**
	 * Get all tags for the dynamic list element
	 *
	 * @return array
	 */
	public function get_tags() {
		$query_vars = $this->get_terms_query_vars();

		return $this->get_terms( $query_vars );
	}

	/**
	 * Get authors for the dynamic list element
	 *
	 * @return array
	 */
	public function get_authors() {
		$users = [];

		$query_vars = array_merge( $this->query, [
			'number' => isset( $this->query['posts_per_page'] ) ? $this->query['posts_per_page'] : false,
		] );


		if ( isset( $this->query['rules'] ) ) {
			foreach ( $this->query['rules'] as $rule ) {
				if ( ! empty( $rule['terms'] ) ) {
					$query_vars['exclude'] = empty( $query_vars['exclude'] ) ? $rule['terms'] : array_merge( $query_vars['exclude'], $rule['terms'] );
				}
			}
		}

		$all_with_posts = get_users( $query_vars );

		foreach ( $all_with_posts as $user ) {
			$users[ $user->ID ] = [
				'name' => $user->get( 'display_name' ),
				'url'  => get_author_posts_url( $user->ID ),
			];
		}

		return $users;
	}

	/**
	 * Get monthly list. All the months in which posts were published are returned
	 *
	 * @return array
	 */
	public function get_monthly_list() {
		$months       = [];
		$allowed_keys = [ 'limit', 'order', 'echo' ];

		$params = array_merge( $this->query, [ 'limit' => isset( $this->query['posts_per_page'] ) ? $this->query['posts_per_page'] : '' ] );

		$exclude = isset( $this->query['rules'][0]['terms'] ) ? $this->query['rules'][0]['terms'] : [];

		/* Take from all the params just what we need */
		$params   = array_intersect_key( $params, array_flip( $allowed_keys ) );
		$archives = wp_get_archives( $params );

		if ( extension_loaded( 'dom' ) && ! empty( $archives ) ) {
			$doc = new DOMDocument();

			//we need to encode the archives so the special characters (such as ä) are displayed correctly
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$archives      = mb_convert_encoding( $archives, 'HTML-ENTITIES', 'UTF-8' );
				$doc->encoding = 'UTF-8';
			}

			if ( $doc->loadHTML( $archives ) ) {
				$links = $doc->getElementsByTagName( 'a' );

				foreach ( $links as $link ) {
					/* @var $link DOMElement */
					if ( ! in_array( $link->nodeValue, $exclude ) ) {
						$months[ $link->nodeValue ] = [
							'name' => $link->nodeValue,
							'url'  => $link->getAttribute( 'href' ),
						];
					}
				}
			}
		}

		return $months;
	}

	/**
	 * Get a list with all the pages
	 *
	 * @return array
	 */
	public function get_pages() {
		$this->query['post_type'] = 'page';

		return $this->get_post();
	}

	/**
	 * Backwards compatibility stuff
	 *
	 * @return array
	 */
	public function get_posts() {
		$this->query['post_type'] = 'post';

		return $this->get_post();
	}

	/**
	 * Get a list with all the posts
	 *
	 * @return array
	 */
	public function get_post() {
		$posts = [];

		if ( $this->use_demo_content ) {
			$post_type = Thrive_Demo_Content::POST_TYPE;
		} else {
			$post_type = empty( $this->query['post_type'] ) || $this->query['post_type'] === 'posts' ? 'post' : $this->query['post_type'];
		}

		$query_vars = array_merge( $this->query, [
			'post_type' => $post_type,
		] );

		if ( isset( $this->query['rules'] ) && is_array( $this->query['rules'] ) ) {
			foreach ( $this->query['rules'] as $rule ) {

				if ( empty( $rule['terms'] ) ) {
					continue;
				}

				if ( ! isset( $rule['taxonomy'] ) ) {
					$rule['taxonomy'] = 'post';
				}

				switch ( $rule['taxonomy'] ) {
					case 'author':
						$query_vars['author__not_in'] = array_values( array_merge( $query_vars['author__not_in'], $rule['terms'] ) );
						break;
					case 'category':
						$query_vars['category__not_in'] = array_values( array_merge( $query_vars['category__not_in'], $rule['terms'] ) );
						break;
					case 'post_tag':
						$query_vars['tag__not_in'] = array_values( array_merge( $query_vars['tag__not_in'], $rule['terms'] ) );
						break;
					case 'post_format':
						$query_vars['tax_query'][] = [
							'taxonomy' => 'post_format',
							'field'    => 'slug',
							'terms'    => [ 'post-format-' . $rule['terms'] ],
							'operator' => 'NOT IN',
						];
						break;
					default:
						$taxonomy = ( $rule['taxonomy'] === 'pages' ) ? 'page' : $rule['taxonomy'];

						if ( post_type_exists( $taxonomy ) ) {
							$query_vars['post__not_in'] = array_values( array_merge( $query_vars['post__not_in'], $rule['terms'] ) );
						}
						break;
				}
			}
		}

		$all_posts = get_posts( $query_vars );
		foreach ( $all_posts as $post ) {
			$posts[ $post->ID ] = [
				'name' => get_the_title( $post ),
				'url'  => get_permalink( $post ),
			];
		}

		return $posts;
	}

	/**
	 * Return the last 5 comments
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function get_comments() {
		$comments             = [];
		$allowedKeys          = [ 'status', 'order', 'offset' ];
		$query_vars           = array_filter( $this->query, static function ( $key ) use ( $allowedKeys ) { // get only specific arguments for comments query
			return in_array( $key, $allowedKeys, true );
		}, ARRAY_FILTER_USE_KEY );
		$query_vars['number'] = isset( $this->query['posts_per_page'] ) ? $this->query['posts_per_page'] : false;

		if ( isset( $this->query['rules'] ) ) {
			foreach ( $this->query['rules'] as $rule ) {
				if ( ! empty( $rule['terms'] ) ) {
					$query_vars['comment__not_in'] = empty( $query_vars['comment__not_in'] ) ? $rule['terms'] : array_merge( $query_vars['comment__not_in'], $rule['terms'] );
				}
			}
		}

		foreach ( get_comments( $query_vars ) as $comment ) {
			$post      = get_post( $comment->comment_post_ID );
			$post_name = ( isset( $post ) ) ? $post->post_title : '';

			$comments[ $comment->comment_ID ] = [
				'name' => $comment->comment_author . ' on ' . $post_name,
				'url'  => get_comment_link( $comment->comment_ID ),
			];
		}

		return $comments;
	}

	/**
	 *
	 * @return array
	 */
	public function get_meta_list() {
		$meta_register = $this->get_meta_register();
		$loginlogout   = $this->get_loginlogout();

		$meta_list = [
			'loginlogout'       => $loginlogout,
			'rss2_url'          => [
				'name' => __( 'Entries RSS' ),
				'url'  => esc_url( get_bloginfo( 'rss2_url' ) ),
			],
			'comments_rss2_url' => [
				'name' => __( 'Comments RSS', 'thrive-theme' ),
				'url'  => esc_url( get_bloginfo( 'comments_rss2_url' ) ),
			],
			'poweredby'         => [
				'name' => _x( 'WordPress.org', 'meta widget link text' ),
				'url'  => esc_url( __( 'https://wordpress.org/' ) ),
			],
		];

		if ( isset( $meta_register['name'], $meta_register['url'] ) ) {
			$meta_list = [ 'meta_register' => $meta_register ] + $meta_list;
		}

		$meta_list = apply_filters( 'thrive_meta_list', $meta_list );

		$exclude = empty( $this->query['rules'][0] ) ? [] : $this->query['rules'][0]['terms'];

		$meta_list = array_filter( $meta_list, static function ( $key ) use ( $exclude ) {
			return ! in_array( $key, $exclude, true );
		}, ARRAY_FILTER_USE_KEY );

		return $meta_list;
	}

	/**
	 * Return the login or logout url parts
	 *
	 * @return mixed
	 */
	public function get_loginlogout() {
		if ( ! is_user_logged_in() ) {
			$url  = esc_url( wp_login_url() );
			$name = __( 'Log in', 'thrive-theme' );
		} else {
			$url  = esc_url( wp_logout_url() );
			$name = __( 'Log out', 'thrive-theme' );
		}

		return apply_filters( 'thrive_loginout', [ 'name' => $name, 'url' => $url ] );
	}

	/**
	 * Get the registration url parts based on the fact that the user is logged in or not and his capabilities
	 *
	 * @return mixed
	 */
	public function get_meta_register() {
		$url  = '';
		$name = '';

		if ( ! is_user_logged_in() ) {
			if ( get_option( 'users_can_register' ) ) {
				$url  = esc_url( wp_registration_url() );
				$name = __( 'Register', 'thrive-theme' );
			}
		} elseif ( current_user_can( 'read' ) ) {
			$url  = admin_url();
			$name = __( 'Site Admin', 'thrive-theme' );
		}

		$register = apply_filters( 'thrive_meta_register', [ 'name' => $name, 'url' => $url ] );

		return $register;
	}

	/**
	 * Default attributes for different list items
	 *
	 * @return array
	 */
	public function get_defaults() {

		$terms = [
			'number'  => false,
			'order'   => false,
			'orderby' => 'name',
			'offset'  => false,
		];

		$posts = [
			'posts_per_page'   => - 1,
			'order'            => 'DESC',
			'orderby'          => 'date',
			'offset'           => 0,
			'author__not_in'   => [],
			'post__not_in'     => [],
			'tag__not_in'      => [],
			'category__not_in' => [],
			'tax_query'        => [],
		];

		return [
			'categories'   => $terms,
			'tags'         => $terms,
			'pages'        => $posts,
			'post'         => $posts,
			'authors'      => [
				'number'              => false,
				'offset'              => false,
				'order'               => false,
				'orderby'             => 'name',
				'has_published_posts' => [ 'post', 'page' ],
			],
			'monthly_list' => [
				'posts_per_page' => '',
				'order'          => false,
				'echo'           => 0,
			],
			'comments'     => [
				'status' => 'approve',
				'order'  => 'DESC',
				'offset' => 0,
			],
		];
	}
}
