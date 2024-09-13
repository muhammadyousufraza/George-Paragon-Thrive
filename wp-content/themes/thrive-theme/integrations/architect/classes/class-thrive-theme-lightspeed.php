<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */


use TCB\Lightspeed\Fonts;
use TCB\Lightspeed\JS;
use TCB\Lightspeed\Main as Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_Theme_Lightspeed
 */
class Thrive_Theme_Lightspeed {

	const THEME_STYLES_HANDLE = 'thrive-theme-styles';

	const VERSION = 1;

	/**
	 * These are the hardcoded styles applied by our default CSS to the reading progress bar, only for Firefox.
	 * When lightspeed is enabled, these are not automatically saved by the browser, so they must be added manually.
	 */
	const FIREFOX_PROGRESS_BAR_STYLES = 'background-color:var(--bar-background-color,#e97954);border:0;height:var(--bar-height,6px);';

	private static $init_done = false;

	public static function init() {
		/* make sure this is only executed once */
		if ( ! static::$init_done ) {
			add_action( 'wp_enqueue_scripts', [ __CLASS__, 'wp_enqueue_scripts' ] );

			add_action( 'tcb_lightspeed_load_unoptimized_styles', [ __CLASS__, 'tcb_lightspeed_load_unoptimized_styles' ], 10, 2 );

			add_action( 'tve_lightspeed_enqueue_module_scripts', [ __CLASS__, 'enqueue_theme_modules' ] );

			add_action( 'tve_lightspeed_preload_assets', [ __CLASS__, 'preload_assets' ] );

			add_action( 'tcb_lightspeed_inline_css', [ __CLASS__, 'tcb_lightspeed_inline_css' ], 10, 3 );

			add_action( 'tcb_lightspeed_item_optimized', [ __CLASS__, 'tcb_lightspeed_item_optimized' ] );

			add_filter( 'tve_lightspeed_items_to_optimize', [ __CLASS__, 'tve_lightspeed_items_to_optimize' ], 10, 2 );

			add_filter( 'tcb_lightspeed_post_to_optimize', [ __CLASS__, 'tcb_lightspeed_post_to_optimize' ] );

			add_filter( 'tcb_lightspeed_requires_architect_assets', [ __CLASS__, 'tcb_lightspeed_requires_architect_assets' ], 10, 2 );

			add_filter( 'tcb_lightspeed_styles_to_optimize', [ __CLASS__, 'tcb_lightspeed_styles_to_optimize' ], 10, 2 );

			add_filter( 'tcb_lightspeed_css_location', [ __CLASS__, 'tcb_lightspeed_css_location' ], 10, 3 );

			add_filter( 'tcb_lightspeed_css_compat', [ __CLASS__, 'tcb_lightspeed_css_compat' ], 10, 2 );

			add_filter( 'tcb_lightspeed_excluded_post_types', [ __CLASS__, 'tcb_lightspeed_excluded_post_types' ] );

			static::$init_done = true;
		}
	}

	/**
	 * Enqueue theme hooks for optimization
	 */
	public static function wp_enqueue_scripts() {
		if ( Lightspeed::is_optimizing() || is_editor_page_raw() ) {
			tve_dash_enqueue_script( 'theme-lightspeed-optimize', THEME_ASSETS_URL . '/lightspeed-optimize.min.js', [ 'jquery' ], THEME_VERSION );
		}
	}

	/**
	 * @param $id
	 */
	public static function enqueue_theme_modules( $id ) {
		if ( get_post_type( $id ) === THRIVE_TEMPLATE ) {
			$thrive_template = new Thrive_Template( $id );

			$template_sections = $thrive_template->meta( 'sections' );

			/* enqueue the modules of the dynamic sections that belong to this template */
			if ( ! empty( $template_sections ) ) {
				foreach ( $template_sections as $section ) {
					if ( ! empty( $section['id'] ) ) {
						JS::get_instance( $section['id'] )->enqueue_scripts();
					}
				}
			}
		}
	}

	/**
	 * When a template preloads its assets, it will also preload the assets of the dynamic sections.
	 *
	 * @param $id
	 */
	public static function preload_assets( $id ) {
		if ( get_post_type( $id ) === THRIVE_TEMPLATE ) {
			$thrive_template = new Thrive_Template( $id );

			$template_sections = $thrive_template->meta( 'sections' );

			if ( ! empty( $template_sections ) ) {
				foreach ( $template_sections as $section ) {
					if ( ! empty( $section['id'] ) ) {
						Lightspeed::preload_assets( $section['id'] );
					}
				}
			}
		}
	}

	/**
	 * In case we don't optimize styles, load default css
	 *
	 * @param string  $type
	 * @param WP_Post $post
	 */
	public static function tcb_lightspeed_load_unoptimized_styles( $type, $post ) {
		if ( ! empty( $post ) ) {
			switch ( $type ) {
				case 'base':
					wp_enqueue_style( static::THEME_STYLES_HANDLE, THEME_ASSETS_URL . '/theme.css', [], THEME_VERSION );
					break;

				case 'template':
					if ( $post->post_type === THRIVE_TEMPLATE ) {
						$template_style = get_option( thrive_skin()->get_template_style_option_name(), '' );

						if ( ! empty( $template_style ) ) {
							wp_enqueue_style( 'thrive-template', UPLOAD_DIR_URL_NO_PROTOCOL . '/thrive/' . $template_style, [], THEME_VERSION );
						}
					}
					break;
			}
		}
	}

	/**
	 * Template css is save in another meta so we have to return it from there
	 *
	 * @param string $inline_css
	 * @param string $type
	 * @param int    $id
	 *
	 * @return string
	 */
	public static function tcb_lightspeed_inline_css( $inline_css, $type, $id ) {
		if ( $type === 'template' ) {
			$template = new Thrive_Template( $id );

			$inline_css = $template->style( false );

			if ( class_exists( '\TCB\Lightspeed\Fonts', false ) ) {
				$inline_css = Fonts::parse_google_fonts( $inline_css );
			}
		}

		return $inline_css;
	}

	/**
	 * Update items with the latest lightspeed version specific to lightspeed
	 *
	 * @param $post_id
	 */
	public static function tcb_lightspeed_item_optimized( $post_id ) {
		update_post_meta( $post_id, Lightspeed::OPTIMIZATION_VERSION_META, static::VERSION );
	}

	/**
	 * Add templates to the list of items we want to optimize
	 *
	 * @param $groups
	 *
	 * @return mixed
	 */
	public static function tve_lightspeed_items_to_optimize( $groups, $request ) {
		$to_analyze = $request->get_param( 'to_analyze' );
		/* for advanced optimization we need to check if we need to analyze ttb templates, for normal lightspeed optimization, we need to analyze ttb templates all the timeF */
		$should_analyze = ( is_array( $to_analyze ) && in_array( 'ttb', $to_analyze ) ) || $to_analyze === null;

		if ( $should_analyze ) {
			$groups[ THRIVE_TEMPLATE ] = [
				'type'  => THRIVE_TEMPLATE,
				'label' => __( 'Theme builder templates', 'thrive-theme' ),
				'items' => array_map( static function ( $template ) {
					return [
						'id'        => $template->ID,
						'name'      => $template->title(),
						'optimized' => (int) $template->meta( Lightspeed::OPTIMIZATION_VERSION_META ) === static::VERSION ? 1 : 0,
						'url'       => $template->preview_url(),
					];
				}, thrive_skin()->get_templates( 'object' ) ),
			];

			$groups[ THRIVE_SECTION ] = [
				'type'  => THRIVE_SECTION,
				'label' => __( 'Theme builder sections', 'thrive-theme' ),
				'items' => array_map( static function ( $section ) {
					return [
						'id'        => $section->ID,
						'name'      => $section->post_title,
						'optimized' => (int) $section->{Lightspeed::OPTIMIZATION_VERSION_META} === static::VERSION ? 1 : 0,
						'url'       => get_permalink( $section->ID ),
					];
				}, get_posts( [
					'posts_per_page' => - 1,
					'post_type'      => THRIVE_SECTION,
				] ) ),
			];
		}


		return $groups;
	}

	/**
	 * When we preview the template, we set it to be optimized
	 *
	 * @param $post_id
	 *
	 * @return int|mixed
	 */
	public static function tcb_lightspeed_post_to_optimize( $post_id ) {
		$preview_template_id = Thrive_Utils::inner_frame_id();

		if ( ! empty( $preview_template_id ) && Thrive_Utils::is_preview() ) {
			$post_id = $preview_template_id;
		}

		return $post_id;
	}

	/**
	 * For the theme templates we save also the theme styles
	 *
	 * @param $styles
	 * @param $post_id
	 *
	 * @return array|string[][]
	 */
	public static function tcb_lightspeed_styles_to_optimize( $styles, $post_id ) {
		/* save styles from the theme also */
		$styles[] = static::THEME_STYLES_HANDLE;

		if ( tve_post_is_landing_page( $post_id ) ) {
			/* landing page styles for sections */
			$styles[] = 'thrive-theme-landingpage-front';
		}

		return $styles;
	}

	/**
	 * For theme templates and sections, we check to see if they have styles saved inside the style meta.
	 *
	 * @param $requires
	 * @param $post_id
	 *
	 * @return bool|mixed
	 */
	public static function tcb_lightspeed_requires_architect_assets( $requires, $post_id ) {

		switch ( get_post_type( $post_id ) ) {
			case THRIVE_TEMPLATE:
			case THRIVE_SECTION:
				$requires = ! empty( get_post_meta( $post_id, 'style', true ) );
				break;
		}

		return $requires;
	}

	/**
	 * Compatibility operations on the lightspeed CSS
	 *
	 * @param string $css
	 * @param int    $id
	 *
	 * @return string
	 */
	public static function tcb_lightspeed_css_compat( $css, $id ) {
		return static::reading_progress_compat( $css );
	}

	/**
	 * Firefox compatibility for the reading progress CSS: '::-moz-progress-bar'
	 *
	 * @param $css
	 *
	 * @return string
	 */
	public static function reading_progress_compat( $css ) {
		$progress_bar_identifier = '.thrive-progress-bar';

		$progress_chrome_selector  = "$progress_bar_identifier::-webkit-progress-value";
		$progress_firefox_selector = "$progress_bar_identifier::-moz-progress-bar";

		/* if a chrome progress selector is found and we didn't already add the styles for mozilla, add them manually */
		if ( strpos( $css, $progress_chrome_selector ) !== false && strpos( $css, $progress_firefox_selector ) === false ) {
			$css = str_replace(
				$progress_chrome_selector,
				$progress_firefox_selector . '{' . static::FIREFOX_PROGRESS_BAR_STYLES . '}' . $progress_chrome_selector,
				$css
			);
		}

		return $css;
	}

	/**
	 * Theme builder saves styles for templates and theme inside files all the time
	 *
	 * @param $location
	 * @param $type
	 * @param $post_id
	 *
	 * @return mixed|string
	 */
	public static function tcb_lightspeed_css_location( $location, $type, $post_id ) {
		if ( get_post_type( $post_id ) === THRIVE_TEMPLATE ) {
			$location = 'file';
		}

		return $location;
	}

	/**
	 * Remove some post types we don't want to optimize
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function tcb_lightspeed_excluded_post_types( $post_types ) {
		if ( class_exists( 'Thrive_Demo_Content', false ) ) {
			$post_types[] = Thrive_Demo_Content::PAGE_TYPE;
			$post_types[] = Thrive_Demo_Content::POST_TYPE;
		}

		$post_types[] = THRIVE_TYPOGRAPHY;

		return $post_types;
	}
}
