<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP;

use TCB_Post_List;
use TCB_Utils;

use Thrive_Section;
use Thrive_Utils;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package Thrive\Theme\AMP
 */
class Main {

	/**
	 * Constant for the AMP keyword
	 */
	const AMP = 'amp';

	/**
	 * Path towards the AMP folder
	 */
	const AMP_PATH = THEME_PATH . '/integrations/amp/';

	/**
	 * Key for the globals flag that we're using to allow/prevent generating AMP links according to the context
	 */
	const GENERATE_AMP_PERMALINK_KEY = 'thrive_theme_should_generate_amp_permalink';

	/**
	 * The AMP full template content. We store it here at the start so we can use it when calculating CSS.
	 * @var string
	 */
	public static $content = '';

	/**
	 * Include the dependencies and initialize the hooks
	 */
	public static function init() {
		static::includes();

		if ( Settings::is_enabled() ) {
			add_rewrite_endpoint( static::AMP, EP_PERMALINK );

			static::hooks();

			Parser::init();
		}
	}

	public static function includes() {
		$dir = static::AMP_PATH . 'classes/';

		/* iterate through the folder and include each file */
		foreach ( scandir( $dir ) as $file ) {
			if ( in_array( $file, [ '.', '..' ] ) ) {
				continue;
			}

			$item_path = $dir . $file;

			/* if the item is a file, include it */
			if ( is_file( $item_path ) ) {
				require_once $item_path;
			}
		}

		require_once static::AMP_PATH . 'classes/video/class-youtube.php';
	}

	public static function hooks() {
		add_action( 'parse_query', [ __CLASS__, 'correct_query_for_amp_front_page' ] );
		add_action( 'wp', [ __CLASS__, 'add_actions_after_wp_init' ] );

		add_filter( 'request', [ __CLASS__, 'force_query_var_value' ] );
	}

	public static function add_actions_after_wp_init() {
		/* here we should also check if we actually have an AMP template to show, if we do not, then we shouldn't redirect */
		if ( static::is_amp_url() && static::is_active() ) {

			/* stop W3TC from adding scripts to AMP pages by 'sabotaging' their global buffer before it prints HTML */
			$GLOBALS['_w3tc_ob_callbacks'] = [];

			/* initialize this as true, then set it to false when needed */
			$GLOBALS[ static::GENERATE_AMP_PERMALINK_KEY ] = true;

			/* priority 9 is used for 'tcb_custom_editable_content' ( LP redirect ), therefore this has to be 8 in order to stop that */
			add_action( 'template_redirect', [ __CLASS__, 'render' ], 8 );

			/* don't show the optimization script when we're on an AMP page */
			remove_action( 'wp_head', [ \TCB\Lightspeed\Hooks::class, 'insert_optimization_script' ], - 24 );
			/* don't load optimized assets on AMP pages because we don't need them */
			add_filter( 'tcb_lightspeed_has_optimized_assets', '__return_false', 42 );

			if ( Settings::is_internal_linking_enabled() ) {
				/* these filters ensure that the permalinks on an AMP page also link towards AMP pages */
				add_filter( 'post_link', [ __CLASS__, 'maybe_link_to_amp' ], 10, 2 );

				add_filter( 'page_link', [ __CLASS__, 'maybe_link_to_amp' ], 10, 2 );

				add_filter( 'post_type_link', static function ( $url, $post ) {
					return static::maybe_link_to_amp( $url, $post->ID );
				}, 10, 2 );
			}

			/* replace the output of the post content with a simpler version */
			add_filter( 'tcb_render_shortcode_tcb_post_content', function ( $output, $attr, $content ) {

				if ( TCB_Post_List::is_outside_post_list_render() ) {
					$output = static::get_post_content( get_post() );
				}

				return $output;
			}, 10, 3 );
		}
	}

	/**
	 * Replace the URL with an AMP URL, but only when it makes sense ( if the globals flag is set, and if AMP is enabled on this post type )
	 *
	 * @param $url
	 * @param $post_id
	 *
	 * @return string|string[]|null
	 */
	public static function maybe_link_to_amp( $url, $post_id ) {

		if ( $GLOBALS[ static::GENERATE_AMP_PERMALINK_KEY ] && Settings::enabled_on_post_type( $post_id ) ) {
			$url = static::generate_amp_permalink( $url, $post_id );
		}

		return $url;
	}

	/**
	 * Render the template for the current page
	 */
	public static function render() {
		get_header( static::AMP );

		/* print the full AMP template content */
		echo static::$content;

		/* call wp_footer and close body and html tag */
		get_footer( static::AMP );

		exit;
	}

	/**
	 * Fix the front page WP_Query when the AMP query var is present.
	 * Normally the front page would not get served if a query var other than [ preview, page, paged, and cpage ] is present
	 * This is taken from the AMP plugin
	 *
	 * @param WP_Query $query Query.
	 *
	 * @see   WP_Query::parse_query()
	 * @link  https://github.com/WordPress/wordpress-develop/blob/0baa8ae85c670d338e78e408f8d6e301c6410c86/src/wp-includes/class-wp-query.php#L951-L971
	 */
	public static function correct_query_for_amp_front_page( $query ) {
		if ( static::is_front_page_query( $query ) ) {
			$query->is_home     = false;
			$query->is_page     = true;
			$query->is_singular = true;
			$query->set( 'page_id', get_option( 'page_on_front' ) );
		}
	}

	/**
	 * Check if AMP is active for this post
	 *
	 * @return bool
	 */
	public static function is_active() {
		return is_singular() && /* singular check */
		       Settings::is_enabled() && /* check if AMP is generally site-wide */
		       Settings::enabled_on_post_type( get_the_ID() ) && /* check if AMP is enabled for this post type */
		       ! thrive_post()->is_amp_disabled(); /* check if AMP is not disabled on this post */
	}

	/**
	 * If AMP is active and we're not in the editor, print the link towards the AMP page
	 *
	 * @param int $id
	 */
	public static function print_amp_permalink( $id = 0 ) {
		if ( ! is_editor_page_raw( true ) && static::is_active() && static::is_css_valid() ) {
			$permalink = static::get_permalink( empty( $id ) ? get_queried_object_id() : $id );

			echo '<link rel="amphtml" href="' . $permalink . '">';
		}
	}

	/**
	 * The css is 'valid' if the size is under 75KB.
	 *
	 * @return bool
	 */
	public static function is_css_valid() {
		$css_size = (int) strlen( static::get_styles() );

		/* since we skip shared styles ( because they rely on the content, which we don't have on non-AMP pages ), approximate it to 0.1 KB */
		$shared_styles_size = 100;

		/* approximate inline style size to 0.5 KB */
		$inline_styles_size = 500;

		return ( $css_size + $shared_styles_size + $inline_styles_size ) / 1000 < 75;
	}

	/**
	 * Prepare the content of the AMP post and store it so we can use it when calculating CSS
	 */
	public static function initialize_content() {
		$queried_object = get_queried_object();

		if ( $queried_object && static::is_post( $queried_object ) ) {
			$post_id = $queried_object->ID;
			$is_lp   = ! empty( tve_post_is_landing_page( $post_id ) );

			the_post();

			$header_content = Parser::parse_content( static::get_hf_content( THRIVE_HEADER_SECTION, $post_id, $is_lp ) );
			$footer_content = Parser::parse_content( static::get_hf_content( THRIVE_FOOTER_SECTION, $post_id, $is_lp ) );

			if ( $is_lp ) {
				$post_content = Parser::parse_content( static::get_post_content( $queried_object ) );

				$content = $header_content . $post_content . $footer_content;
			} else {
				$top_section_content = Parser::parse_content( static::get_section_content( 'top' ) );

				$content_section = Parser::parse_content( static::get_section_content( 'content' ) );
				$content         = static::get_amp_file( 'templates/content.php', [ 'content' => $content_section ] );

				$bottom_section_content = Parser::parse_content( static::get_section_content( 'bottom' ) );

				$content = $header_content . $top_section_content . $content . $bottom_section_content . $footer_content;
				$content = TCB_Utils::wrap_content( $content, 'div', 'wrapper', 'tcb-style-wrap thrive-amp-wrapper' );
			}

			static::$content = $content;
		}
	}

	/**
	 * Get the content of any theme section
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public static function get_section_content( $type ) {
		$section_data = thrive_template()->get_section( $type );

		$section = new Thrive_Section( $section_data['id'], $section_data );

		return $section->render();
	}

	/**
	 * Return the post content for this post
	 *
	 * @param $post
	 *
	 * @return string
	 */
	public static function get_post_content( $post ) {
		$post_id = $post->ID;
		$is_lp   = ! empty( tve_post_is_landing_page( $post_id ) );

		/* there are some instances where 'tcb_editor_enabled' is not set, because the LP was generated by the theme */
		$tcb_enabled = $is_lp || (int) get_post_meta( $post_id, 'tcb_editor_enabled', true );

		if ( $tcb_enabled && tve_is_post_type_editable( get_post_type( $post_id ) ) ) {
			$post_content = static::prepare_architect_content( $post_id );
		} else {
			$post_content = $post->post_content;
		}

		return $post_content;
	}

	/**
	 * Get the content of the header/footer. The style is also rendered here, but it's removed in 'parse_content' because we can't have <style>s in AMP content
	 * The style is gathered separately in the *-amp-styles class
	 *
	 * @param $type
	 * @param $post_id
	 * @param $is_lp
	 *
	 * @return string
	 */
	public static function get_hf_content( $type, $post_id, $is_lp ) {
		if ( $is_lp ) {
			$content = \TCB_Symbol_Template::symbol_render_shortcode( [
				'id'                   => get_post_meta( $post_id, '_tve_' . $type, true ),
				'tve_shortcode_config' => false,
			], true );
		} else {
			$content = thrive_template()->render_theme_hf_section( $type );
		}

		return $content;
	}

	/**
	 * Prepare the TAR content for rendering ( do_shortcode(), etc) - this uses some parts from tve_editor_content()
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function prepare_architect_content( $post_id ) {
		$tve_saved_content = tve_get_post_meta( $post_id, 'tve_updated_post' );

		$tve_saved_content = tve_thrive_shortcodes( $tve_saved_content );

		/* render the content added through WP Editor (element: "WordPress Content") */
		$tve_saved_content = tve_do_wp_shortcodes( $tve_saved_content );

		$tve_saved_content = do_shortcode( $tve_saved_content );

		$tve_saved_content = apply_filters( 'tcb_clean_frontend_content', $tve_saved_content );
		$tve_saved_content = tcb_remove_deprecated_strings( $tve_saved_content );

		/* make images responsive */
		if ( function_exists( 'wp_filter_content_tags' ) ) {
			try {
				$tve_saved_content = wp_filter_content_tags( $tve_saved_content );
			} catch ( Error $e ) {
				/* just so it won't break the page */
			}
		} elseif ( function_exists( 'wp_make_content_images_responsive' ) ) {
			$tve_saved_content = wp_make_content_images_responsive( $tve_saved_content );
		}

		$tve_saved_content = TCB_Utils::wrap_content( $tve_saved_content, 'div', 'tve_editor', 'tve_shortcode_editor' );
		$tve_saved_content = TCB_Utils::wrap_content(
			$tve_saved_content,
			'div',
			'tve_flt',
			'tve_flt tcb-style-wrap' );

		return $tve_saved_content;
	}

	/**
	 * @param $queried_object
	 *
	 * @return bool
	 */
	public static function is_post( $queried_object ) {
		return $queried_object instanceof \WP_Post;
	}

	/**
	 * Check if we're on an AMP page
	 *
	 * @return bool
	 */
	public static function is_amp_url() {
		return get_query_var( static::AMP, false ) !== false;
	}

	/**
	 * Retrieves the full AMP-specific permalink for the given post ID.
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public static function get_permalink( $post_id ) {
		$permalink = get_permalink( $post_id );

		return static::generate_amp_permalink( $permalink, $post_id );
	}

	/**
	 * @param $permalink
	 * @param $post_id
	 *
	 * @return string|string[]|null
	 */
	public static function generate_amp_permalink( $permalink, $post_id ) {
		$parsed_url = wp_parse_url( $permalink );
		$structure  = get_option( 'permalink_structure' );

		$use_query_var = (
			empty( $structure ) || /* if pretty permalinks aren't available, then query var must be used */
			! empty( $parsed_url['query'] ) || /* if there are existing query vars, then always use the amp query var as well */
			is_post_type_hierarchical( get_post_type( $post_id ) ) || /* if the post type is hierarchical then the /amp/ endpoint isn't available */
			'attachment' === get_post_type( $post_id )/* attachment pages don't accept the /amp/ endpoint */
		);

		if ( $use_query_var ) {
			$amp_url = add_query_arg( static::AMP, '', $permalink );
		} else {
			$amp_url = preg_replace( '/#.*/', '', $permalink );
			$amp_url = trailingslashit( $amp_url ) . user_trailingslashit( static::AMP, 'single_amp' );
			if ( ! empty( $parsed_url['fragment'] ) ) {
				$amp_url .= '#' . $parsed_url['fragment'];
			}
		}

		return $amp_url;
	}

	/**
	 * Return the current canonical URL
	 * Adapted from the AMP WP Plugin
	 *
	 * @return false|string|void
	 */
	public static function get_canonical_url() {

		/* we don't want 'amp' appended to the canonical URL */
		$GLOBALS[ static::GENERATE_AMP_PERMALINK_KEY ] = false;

		if ( is_singular() ) {
			$url = wp_get_canonical_url();
		} else {
			global $wp, $wp_rewrite;

			/* for non-singular queries, make use of the request URI and public query vars to determine canonical URL */
			$added_query_vars = $wp->query_vars;

			if ( ! $wp_rewrite->permalink_structure || empty( $wp->request ) ) {
				$url = home_url( '/' );
			} else {
				$url = home_url( user_trailingslashit( $wp->request ) );
				parse_str( $wp->matched_query, $matched_query_vars );

				foreach ( $wp->query_vars as $key => $value ) {

					/* remove query vars that were matched in the rewrite rules for the request */
					if ( isset( $matched_query_vars[ $key ] ) ) {
						unset( $added_query_vars[ $key ] );
					}
				}
			}
		}

		if ( ! empty( $added_query_vars ) ) {
			$url = add_query_arg( $added_query_vars, $url );
		}

		$GLOBALS[ static::GENERATE_AMP_PERMALINK_KEY ] = true;

		return $url;
	}

	/**
	 * Make sure the 'amp' query var has an explicit value so we can check if it exists with get_query_var()
	 * Taken from the AMP WP plugin
	 *
	 * @param array $query_vars Query vars.
	 *
	 * @return array Query vars.
	 */
	public static function force_query_var_value( $query_vars ) {
		if ( isset( $query_vars[ static::AMP ] ) && $query_vars[ static::AMP ] === '' ) {
			$query_vars[ static::AMP ] = 1;
		}

		return $query_vars;
	}

	/**
	 * Wrapper for the get_styles() function ( so we don't call it from the outside )
	 *
	 * @return array|mixed
	 */
	public static function get_styles() {
		$queried_object = get_queried_object();

		return static::is_post( $queried_object ) ? Styles::get_styles( $queried_object->ID ) : '';
	}

	/**
	 * Wrapper for the get_scripts() function
	 *
	 * @return array|mixed
	 */
	public static function get_scripts() {
		$queried_object = get_queried_object();

		return static::is_post( $queried_object ) ? Scripts::get_scripts( $queried_object ) : '';
	}

	/**
	 * Wrapper for get_fonts() from class-fonts.php
	 *
	 * @return mixed
	 */
	public static function get_fonts() {
		$queried_object = get_queried_object();

		return static::is_post( $queried_object ) ? Fonts::get_fonts( $queried_object->ID ) : '';
	}

	/**
	 * Wrapper for the get_boilerplate_styles() function.
	 *
	 * @return string
	 */
	public static function get_amp_default_styles() {
		return Styles::get_boilerplate_styles();
	}

	/**
	 * @param string $subpath
	 * @param array  $attr
	 *
	 * @return string
	 */
	public static function get_amp_file( $subpath, $attr = [] ) {
		return Thrive_Utils::return_part( '/integrations/amp/' . $subpath, $attr );
	}

	/**
	 * Check if this is the front page AMP query.
	 *
	 * @param WP_Query $query Query.
	 *
	 * @return bool
	 */
	public static function is_front_page_query( $query ) {
		return
			$query->is_main_query() &&
			$query->is_home() &&
			$query->get( static::AMP, false ) !== false && /* Is AMP endpoint */
			! $query->is_front_page() && /* the query is not yet 'front page' fixed */
			get_option( 'show_on_front' ) === 'page' && /* Is showing pages on front */
			get_option( 'page_on_front' ) && /* Has page on front set */
			/* Added 'amp' to the array from WP_Query::parse_query() at <https://github.com/WordPress/wordpress-develop/blob/0baa8ae/src/wp-includes/class-wp-query.php#L961>. */
			count( array_diff( array_keys( wp_parse_args( $query->query ) ), [ static::AMP, 'preview', 'page', 'paged', 'cpage' ] ) ) === 0;
	}
}
