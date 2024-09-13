<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main - includes the main functionality for Display Testimonial element
 *
 * @package TVO\DisplayTestimonials
 */
class Main {

	const SHORTCODE = 'thrive_display_testimonials';

	const WRAPPER_CLASS = 'thrive-display-testimonials';

	const JS_LOCALIZE_CONST = 'tvo_display_testimonials_localize';

	public static $testimonial;
	public static $query;
	/**
	 * Dataset attributes that have to be displayed on the frontend because they're used there ( normally, 'data-' is removed on front, so this acts as a whitelist )
	 *
	 * @var array
	 */
	public static $front_attr = array(
		'tcb-events',
		'css',
		'masonry',
		'type',
		'pagination-type',
		'no_posts_text',
		'layout',
		'total_post_count',
		'pages_near_current',
		'template-id',
		'styled-scrollbar',
	);

	/**
	 * Return the Display Testimonials specific elements label
	 *
	 * @return string
	 */
	public static function elements_group_label() {
		return __( 'Ovation Elements', 'thrive-ovation' );
	}

	/**
	 * Include the files needed
	 */
	public static function includes() {
		require_once __DIR__ . '/class-utils.php';
		require_once __DIR__ . '/class-rest-api.php';
		require_once __DIR__ . '/class-hooks.php';
		require_once __DIR__ . '/class-shortcodes.php';
		require_once __DIR__ . '/query/class-main.php';
	}

	/**
	 * Init the functionality for Display Testimonials
	 */
	public static function init() {
		static::includes();
		static::add_shortcode();

		Query\Main::init();

		Hooks::add_filters();
		Hooks::add_actions();

		Shortcodes::init();
	}

	/**
	 * Add the Display Testimonial shortcode
	 */
	public static function add_shortcode() {
		add_shortcode( static::SHORTCODE, array( __CLASS__, 'render' ) );
	}

	/**
	 * Default args for display testimonial
	 *
	 * @return array
	 */
	public static function default_args() {
		return [
			'query'               => str_replace( '"', '\'', json_encode( static::get_default_query() ) ),
			'type'                => 'grid',
			'columns-d'           => 3,
			'columns-t'           => 2,
			'columns-m'           => 1,
			'vertical-space-d'    => 30,
			'horizontal-space-d'  => 30,
			'ct'                  => 'display_testimonials--1',
			'ct-name'             => 'Default Display Testimonials',
			'tcb-elem-type'       => 'display_testimonials',
			'pagination-type'     => 'none',
			'pages_near_current'  => '2',
			'posts_per_page'      => 6,
			'no_posts_text_color' => '#999999',
		];
	}

	/**
	 * Returns the default query used for getting the testimonials
	 *
	 * @return array
	 */
	public static function get_default_query( $return_type = 'array' ) {
		$query = array_merge( static::DEFAULT_ARGS, [
			'posts_per_page' => 3,
			'paged'          => 1,
			'no_posts_text'  => 'There are no testimonials to display.',
		] );

		if ( $return_type === 'string' ) {
			$query = str_replace( '"', '\'', json_encode( $query ) );
		}

		return $query;
	}

	/**
	 * Given the query from element data set, we parse it to get the query as an array
	 *
	 * @param string|array $attr_query
	 *
	 * @return array
	 */
	public static function init_query( $attr_query = [] ) {
		if ( is_string( $attr_query ) ) {
			/* replace single quotes with double quotes */
			$decoded_string = str_replace( "'", '"', html_entity_decode( $attr_query, ENT_QUOTES ) );
			/* escape attributes and decode [ and ] -> mostly used for json_encode */
			$decoded_string = str_replace( array( '|{|', '|}|' ), array( '[', ']' ), $decoded_string );

			/* replace newlines and tabs */
			$decoded_string = preg_replace( '/[\r\n]+/', ' ', $decoded_string );

			$query = json_decode( $decoded_string, true );
		} elseif ( is_array( $attr_query ) ) {
			$query = $attr_query;
		}

		/* Sometimes the query gets transformed in string */
		$query['paged'] = ! empty( $query['paged'] ) ? (int) $query['paged'] : 1;

		$default_query = static::get_default_query();

		/* if this is a set, we do not need a default posts_per_page value - more information about this can be found in class-set.php */
		if ( ! empty( $query['set_id'] ) ) {
			unset( $default_query['posts_per_page'] );
		}

		return array_merge( $default_query, is_array( $query ) ? $query : [] );
	}

	/**
	 * Render the Display Testimonial element
	 *
	 * @param array  $attr
	 * @param string $testimonial_content
	 *
	 * @return string
	 */
	public static function render( $attr = [], $testimonial_content = '' ) {
		$attr = \TCB_Post_List_Shortcodes::parse_attr( $attr, static::SHORTCODE );

		/* Before we bring any testimonials, the total post count is 0 */
		$attr['total_post_count'] = 0;
		/* If the css attr is empty, generate a new one */
		$attr['css'] = empty( $attr['css'] ) ? substr( uniqid( 'tve-u-', true ), 0, 17 ) : $attr['css'];
		/* Init the query used for fetching the posts */

		if ( ( \TCB_Utils::is_rest() || wp_doing_ajax() ) && isset( $_REQUEST['query'] ) ) {
			$query = $_REQUEST['query'];

			$attr['query'] = str_replace( '"', '\'', json_encode( $query ) );
		} else {
			$query = empty( $attr['query'] ) ? [] : $attr['query'];
		}

		$query = static::init_query( $query );

		$testimonials = static::get_testimonials( $query, ! empty( $attr['pagination-type'] ) && $attr['pagination-type'] !== 'none' );
		/* Update the total count */
		$attr['total_post_count'] = static::$query->found_posts;

		/* Content of the testimonial, will include content for every testimonial(article) */
		$content = '';

		$class = static::class_attr( $attr );
		/* if there are not testimonials hide everything inside */
		if ( empty( $testimonials ) ) {
			$content .= static::render_one();

			$class .= ' empty-list';
		} else {
			foreach ( $testimonials as $tvo_testimonial ) {
				$content .= static::render_one( $tvo_testimonial, $testimonial_content );
			}
		}

		/* Localize the testimonials so they can be printed on page to be used in front end */
		$GLOBALS[ static::JS_LOCALIZE_CONST ][] = array(
			'identifier' => '[data-css="' . $attr['css'] . '"]',
			'template'   => $attr['css'],
			'query'      => $query,
			'content'    => $testimonial_content,
			'attr'       => $attr,
			'posts'      => array_map( function ( $testimonial ) {
				return $testimonial->ID;
			}, $testimonials ),
		);

		$content = \TCB_Post_List::prepare_carousel( $content, $attr );

		return \TCB_Utils::wrap_content( $content, 'div', '', $class, static::parse_attr( $attr ) );
	}

	/**
	 * Todo re-think this later, it might be to time consuming to change the keys to an array
	 *
	 * @param $attr
	 *
	 * @return mixed
	 */
	public static function parse_attr( $attr ) {
		foreach ( $attr as $key => $value ) {
			if ( $key !== 'style' ) {
				$attr[ 'data-' . $key ] = $value;
				unset( $attr[ $key ] );
			}
		}

		return $attr;
	}

	/**
	 * Returns the classes used for the main element of Display Testimonials
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function class_attr( $attr ) {
		/* tve-content-list identifies lists with dynamic content */
		$classes = [ static::WRAPPER_CLASS, THRIVE_WRAPPER_CLASS ];

		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}

		if ( \TCB_Utils::in_editor_render( true ) ) {
			$classes[] = 'tcb-compact-element';
		}

		if ( isset( $attr['type'] ) && $attr['type'] === 'masonry' ) {
			$classes[] = 'tve_post_grid_masonry';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Returns the content for one individual testimonial that will be displayed in Display Testimonial element
	 *
	 * @param        $testimonial
	 * @param string $content
	 *
	 * @return false|mixed
	 */
	public static function render_one( $testimonial = null, $content = '' ) {
		global $post;

		$original_post = $post;

		$post = $testimonial;

		$article_attr = [];

		if ( empty( $content ) ) {
			$content = Utils::tvo_template( 'display-testimonial.php', $post );
		}

		if ( ! empty( $testimonial ) ) {
			$tvo_testimonial = static::testimonial_info();

			$article_attr = [
				'data-id'       => $tvo_testimonial['ID'],
				'data-selector' => '.post-wrapper',
			];

			/* In editor, we change the name of the article */
			if ( \TCB_Utils::in_editor_render( true ) ) {
				$article_attr['data-element-name'] = __( 'Testimonial', 'thrive-ovation' );
			}
		} else {
			$tvo_testimonial = [
				'ID' => '',
			];
		}

		$content = do_shortcode( $content );
		/* restore the original post */
		$post = $original_post;

		return Utils::before_wrap(
			[
				'content' => $content,
				'tag'     => 'article',
				'id'      => 'post-' . ! empty( $tvo_testimonial['ID'] ) ? $tvo_testimonial['ID'] : '',
				'class'   => 'thrive-testimonial-wrapper post-wrapper',
				'attr'    => $article_attr,
			]
		);
	}

	/**
	 * @param     $testimonial
	 * @param int $order
	 *
	 * @return array
	 */
	public static function testimonial_info( $order = 0 ) {
		$id = get_the_ID();

		/* data related to testimonials */
		$data          = Utils::get_testimonial_meta_data();
		$data['order'] = empty( $order ) ? $id : $order;

		return $data;
	}

	/**
	 * Returns the css used for handling the Testimonial Image
	 *
	 * @param $testimonial
	 *
	 * @return string
	 */
	public static function get_dynamic_variables() {
		$variables = '';

		$testimonial_image_var = TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'testimonial-image';
		$testimonial_picture   = Utils::get_testimonial_meta_value( 'picture' );

		if ( ! empty( $testimonial_picture ) ) {
			$variables .= $testimonial_image_var . ':url("' . $testimonial_picture . '");';
		}

		return 'article.thrive-testimonial-wrapper[data-id="' . get_the_id() . '"]{' . $variables . '}';
	}

	/**
	 * Returns an array with all the shortcode data for the required testimonials
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public static function get_testimonial_shortcodes( $ids = [] ) {
		$testimonial_data = [];

		$query = array_merge( static::init_query(), [ 'post__in' => $ids ] );

		if ( ! empty( $ids ) ) {
			$query['posts_per_page'] = - 1;
		}

		$wp_query = new \WP_Query( $query );

		/* Parse the testimonials */
		foreach ( $wp_query->posts as $testimonial ) {
			$testimonial_data[ $testimonial->ID ] = static::get_testimonial_shortcode_data( $testimonial );
		}

		return $testimonial_data;
	}

	/**
	 * Returns the shortcode data for one testimonial
	 *
	 * @param $testimonial
	 *
	 * @return array
	 */
	public static function get_testimonial_shortcode_data( $testimonial ) {
		$testimonial_shortcode = [];

		global $post;

		$original_post = $post;

		/* for do_shortcode we need the current testimonial in the global $post */
		$post = $testimonial;

		/* Parse each shortcode and store the content */
		foreach ( array_keys( Shortcodes::$shortcodes ) as $shortcode ) {
			$testimonial_shortcode[ $shortcode ] = do_shortcode( '[' . $shortcode . ']' );
		}

		$testimonial_shortcode['ID']    = get_the_id();
		$testimonial_shortcode['image'] = Utils::get_testimonial_meta_value( 'picture' );

		/* restore the original post */
		$post = $original_post;

		return $testimonial_shortcode;
	}

	/**
	 * Fetch testimonials based on arguments
	 *
	 * @param array   $args
	 * @param boolean $has_pagination
	 *
	 * @return array
	 */
	public static function get_testimonials( $args = [], $has_pagination = false ) {

		$args = array_merge( static::DEFAULT_ARGS, $args );

		if ( ! empty( $args['set_id'] ) ) {
			$set_data = Query\Set::get_instance_with_id( $args['set_id'] )->get();

			if ( ! empty( $set_data ) ) {
				$args = Query\Set::parse_set_data( $set_data, $args, $has_pagination );
			}
			unset( $args['set_id'] );
		}

		if ( ! isset( $args['posts_per_page'] ) ) {
			$args['posts_per_page'] = '6';
		}

		/* 'offset' and pagination don't mix, so only apply it when pagination is not available */
		if ( $has_pagination ) {
			unset( $args['offset'] );
		}

		/* The author data is kept in an serialized array in post meta,
		so we need to get all testimonials, and return the first posts_per_page filtered by the author name */
		if ( $args['orderby'] === 'author' ) {
			$query_all = array_merge( [
				'post_type' => TVO_TESTIMONIAL_POST_TYPE,
				'order'     => $args['order'],
			], $args, [ 'posts_per_page' => - 1 ] );

			static::$query = new \WP_Query( $query_all );

			$testimonials = static::$query->posts;

			/* Get all the testimonials and add the author to them */
			foreach ( $testimonials as &$testimonial ) {
				$testimonial_meta = get_post_meta( $testimonial->ID, TVO_POST_META_KEY, true );

				$testimonial->author = $testimonial_meta['name'];
			}

			/* Set the order based on wha we get from the query */
			$order = $args['order'] === 'DESC' ? - 1 : 1;

			/* Sort the testimonials */
			uasort( $testimonials, function ( $a, $b ) use ( $order ) {
				return $order * strcasecmp( $a->author, $b->author );
			} );

			/* keep only number of testimonials we need, based on posts_per_page */
			$testimonials = array_slice( $testimonials, empty( $args['offset'] ) ? 0 : $args['offset'], $args['posts_per_page'] );
		} else {
			static::$query = new \WP_Query( $args );

			$testimonials = static::$query->posts;
		}

		return $testimonials;
	}

	const DEFAULT_ARGS = [
		'post_type'  => TVO_TESTIMONIAL_POST_TYPE,
		'orderby'    => 'date',
		'order'      => 'DESC',
		'meta_query' => [
			[
				'key'     => TVO_STATUS_META_KEY,
				'value'   => TVO_STATUS_READY_FOR_DISPLAY,
				'compare' => '=',
			],
		],
	];
}
