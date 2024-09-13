<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Css
 *
 * @package TCB\Lightspeed
 */
class Css {

	private static $instances = [];

	private $styles_loaded = [];

	private $ID;

	private $post;

	public static function get_instance( $post_id = 0 ) {
		if ( empty( self::$instances[ $post_id ] ) ) {
			self::$instances[ $post_id ] = new self( $post_id );
		}

		return self::$instances[ $post_id ];
	}

	public function __construct( $post_id ) {
		$this->ID   = (int) $post_id;
		$this->post = get_post( $this->ID );
	}

	/**
	 * Css file is saved because we change it every time
	 *
	 * @param string $type
	 * @param bool   $regenerate
	 *
	 * @return mixed|string
	 */
	public function get_css_filename( $type = '', $regenerate = false ) {
		$meta_key = '_tve_' . $type . '_css_file';
		$filename = get_post_meta( $this->ID, $meta_key, true );

		if ( $regenerate ) {
			$filename = 'tcb-' . $type . '-css-' . $this->ID . '-' . time() . '.css';
			update_post_meta( $this->ID, $meta_key, $filename );
		}

		return $filename;
	}

	/**
	 * Return the handle used for enqueue-ing the style for the current post
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_style_handle( $type = '' ) {
		return sprintf( 'tcb-style-%s-%s-%s', $type, $this->post->post_type, $this->ID );
	}

	/**
	 * Save css either in post meta or in individual file
	 *
	 * @param string $type
	 * @param string $css
	 */
	public function save_optimized_css( $type = 'base', $css = '' ) {
		$inline_css_meta_key = "_tve_{$type}_inline_css";

		if ( ! is_string( $css ) ) {
			$css = '';
		}

		/* we want to keep the encoded open/close tag characters (they might be part of background SVGs) */
		$css = str_replace( [ '%3C', '%3E' ], [ '[tcb-encoded-open-tag]', '[tcb-encoded-close-tag]' ], $css );
		$css = sanitize_text_field( $css );
		/* restore the encoded tag characters */
		$css = str_replace( [ '[tcb-encoded-open-tag]', '[tcb-encoded-close-tag]' ], [ '%3C', '%3E' ], $css );

		$css = tve_minify_css( $css );

		$css = static::compat( $css, $this->ID );

		switch ( $this->get_css_location( $type ) ) {
			case 'file':
				if ( ! $this->write_css_file( $type, $css ) ) {
					/* as fallback, if we can't write the file, save the css in meta */
					update_post_meta( $this->ID, $inline_css_meta_key, $css );
				}

				break;
			case 'head':
			case 'footer':
			default:
				update_post_meta( $this->ID, $inline_css_meta_key, $css );
				break;
		}

		/* if no css is being saved, then remove the lightspeed version */
		update_post_meta( $this->ID, Main::OPTIMIZATION_VERSION_META, empty( $css ) ? 0 : Main::LIGHTSPEED_VERSION );
	}

	/**
	 * Depending on settings, load post style
	 *      > if nothing is set, load the the whole flat styles file
	 *      > if css optimization is on, load only specific styles, either from post meta or from individual file
	 *
	 * @param string $type
	 * @param array  $deps
	 */
	public function load_optimized_style( $type, $deps = [] ) {

		if ( empty( $this->styles_loaded[ $type ] ) ) {
			if ( $this->should_load_optimized_styles() ) {
				$location = $this->get_css_location( $type );

				if ( $location === 'file' ) {
					/* if by any chance the file doesn't exist, load the css in head */
					$file = $this->get_css_filename( $type );
					if ( empty( $file ) || ! file_exists( Main::upload_dir( 'basedir' ) . $file ) ) {
						$location = 'head';
					}
				}

				switch ( $location ) {
					case 'file':
						if ( did_action( 'wp_head' ) ) {
							echo $this->get_optimized_styles( 'file', $type );
						} else {
							$this->enqueue_style( $type, $deps );
						}
						break;

					case 'head':
					case 'footer':
						$hook = 'wp_' . $location;

						if ( tve_post_is_landing_page( $this->ID ) || did_action( $hook ) ) {
							echo $this->get_optimized_styles( 'inline', $type );
						} else {
							add_action( $hook, function () use ( $type ) {
								echo $this->get_optimized_styles( 'inline', $type );
							}, 0 );
						}

						break;
					case 'inline':
					default:
						echo $this->get_optimized_styles( 'inline', $type );
				}
			} else {
				/* load thrive flat only if this post needs it. */
				if ( static::should_load_flat() || ! Main::is_enabled() || Main::requires_architect_assets( $this->ID ) ) {
					static::enqueue_flat();
				}

				/**
				 * Action called when we're not optimizing the css, just so we can take any other action needed
				 *
				 * @param $type string type of css that we wanted to display
				 * @param $post \WP_Post post object with all the information
				 */
				do_action( 'tcb_lightspeed_load_unoptimized_styles', $type, $this->post );
			}
		}

		$this->styles_loaded[ $type ] = true;
	}

	/**
	 * Enqueue style based on type
	 *
	 * @param string $type
	 * @param array  $deps
	 */
	public function enqueue_style( $type = '', $deps = [] ) {
		wp_enqueue_style( $this->get_style_handle( $type ), Main::upload_dir( 'baseurl' ) . $this->get_css_filename( $type ), $deps );
	}

	/**
	 * Enqueue thrive flat styles
	 */
	public static function enqueue_flat() {
		tve_enqueue_style( tve_get_style_enqueue_id(), static::get_flat_url( false ) );
	}

	/**
	 * Return the inline css for the current post
	 *
	 * @param string $type
	 *
	 * @return mixed|void
	 */
	public function get_inline_css( $type = 'base' ) {
		$inline_css = get_post_meta( $this->ID, "_tve_{$type}_inline_css", true );

		if ( ! is_string( $inline_css ) ) {
			$inline_css = '';
		}

		$inline_css = static::get_compat_css( $inline_css, $this->ID );

		/**
		 * Filters the inline css for the current post.
		 *
		 * @param $inline_css string the css that we want to use
		 * @param $type       string type of css in case we have multiple saves for a single post
		 * @param $ID         int ID of the current post
		 *
		 * @return string
		 */
		return apply_filters( 'tcb_lightspeed_inline_css', $inline_css, $type, $this->ID );
	}

	/**
	 * Run compat function on CSS
	 *
	 * @param $inline_css
	 * @param $id
	 *
	 * @return string
	 */
	public static function get_compat_css( $inline_css, $id = 0 ) {
		$inline_css = Fonts::parse_google_fonts( $inline_css );

		$inline_css = tve_minify_css( $inline_css );

		$inline_css = static::compat( $inline_css, $id );

		return $inline_css;
	}

	/**
	 * Render styles node for the current post
	 *
	 * @param string  $location
	 * @param string  $type
	 * @param boolean $optimize_on_load
	 */
	public function get_optimized_styles( $location = 'inline', $type = 'base', $optimize_on_load = true ) {
		switch ( $location ) {
			case 'file':
				$styles = sprintf(
					"<link rel='stylesheet' id='%s'  href='%s' type='text/css' media='all' />",
					$this->get_style_handle( $type ),
					Main::upload_dir( 'baseurl' ) . $this->get_css_filename( $type )
				);
				break;

			case 'inline':
			default:
				$styles = static:: get_inline_style_node( $this->get_style_handle( $type ), $this->get_inline_css( $type ), $optimize_on_load );
				break;
		}

		return $styles;
	}

	/**
	 * Build a style node for CSS
	 *
	 * @param $handle
	 * @param $css
	 * @param $should_optimize
	 *
	 * @return string
	 */
	public static function get_inline_style_node( $handle, $css, $should_optimize = true ) {
		return sprintf( '<style type="text/css" id="%s" %s class="tcb-lightspeed-style">%s</style>',
			$handle,
			$should_optimize ? ' onLoad="typeof window.lightspeedOptimizeStylesheet === \'function\' && window.lightspeedOptimizeStylesheet()"' : '',
			$css );
	}

	/**
	 * Always load flat file in the editor and when we set a specific param
	 *
	 * @return bool
	 */
	public static function should_load_flat() {
		$should_load_flat = isset( $_GET['force-flat'] ) || is_editor_page_raw( true );

		/**
		 * Allow plugins to short-circuit the loading of optimized assets on certain pages
		 *
		 * @param boolean $should_load_flat if true, we'll load thrive_flat.css on the current request
		 */
		return apply_filters( 'tcb_lightspeed_should_load_flat', $should_load_flat );
	}

	/**
	 * General check to see if we load flat or we load optimized styles
	 *
	 * @return bool
	 */
	public function should_load_optimized_styles() {
		return Main::has_optimized_assets( $this->ID ) && ! static::should_load_flat();
	}

	/**
	 * Save css in individual file
	 *
	 * @param string $type
	 * @param string $css
	 *
	 * @return boolean
	 */
	public function write_css_file( $type, $css ) {
		$styles_dir = Main::upload_dir( 'basedir' );

		/* remove old style files for this post */
		foreach ( scandir( $styles_dir ) as $file ) {
			if ( strpos( $file, 'tcb-' . $type . '-css-' . $this->ID . '-' ) === 0 ) {
				unlink( $styles_dir . $file );
			}
		}

		return \TCB_Utils::write_file( $styles_dir . $this->get_css_filename( $type, true ), wp_unslash( $css ) );
	}

	/**
	 * Each post reads from specific stylesheets and save styles
	 *
	 * @return mixed|void
	 */
	public function get_styles_to_optimize() {
		$default_styles = [
			'tve_style_family_tve_flt',
			'tve_landing_page_base_css',
			'the_editor_no_theme',
		];

		/**
		 * Filters used to decide for each post what styles need optimization and from what stylesheets
		 *
		 * @param array $default_styles array
		 * @param int   $post_id
		 *
		 * @return array
		 */
		return apply_filters( 'tcb_lightspeed_styles_to_optimize', $default_styles, $this->ID );
	}

	/**
	 * If in some cases we just need the inline flat style file
	 *
	 * @return string
	 */
	public static function inline_flat_style() {
		return sprintf(
			'<link rel="stylesheet" id="%s-css" href="%s" type="text/css" media="all" onLoad="typeof window.lightspeedOptimizeFlat === \'function\' && window.lightspeedOptimizeFlat(this)" />',
			tve_get_style_enqueue_id(),
			static::get_flat_url()
		);
	}

	/**
	 * Get the location of thrive flat
	 *
	 * @param bool $include_version
	 *
	 * @return string
	 */
	public static function get_flat_url( $include_version = true ) {
		return tve_editor_css( 'thrive_flat.css' ) . ( $include_version ? '?v=' . TVE_VERSION : '' );
	}

	/**
	 * Add specific browser rules in case they weren't included
	 *
	 * @param string $css
	 * @param int    $id
	 *
	 * @return string
	 */
	public static function compat( $css, $id ) {

		/* fit-content for chrome vs. -moz-fit-content for firefox */
		$css = preg_replace_callback( '/[;|{]([^:]*):(-moz-)?fit-content( !important)?/m', static function ( $matches ) {
			$pre          = stripos( $matches[0], '-moz' ) === false ? '-moz-' : '';
			$is_important = strpos( $matches[0], 'important' );

			return $matches[0] . ';' . $matches[1] . ':' . $pre . 'fit-content' . ( $is_important ? ' !important' : '' );
		}, $css );

		$css = preg_replace_callback( '/inset:([^;]*);/m', static function ( $matches ) {
			/* there are cases where 'inset:' comes with a space after ':' */
			$matched_values = explode( ' ', ltrim( $matches[1] ) );

			switch ( count( $matched_values ) ) {
				case 4:
					/* inset:2.4em 3em 3em 5em; - top right bottom left */
					list( $top, $right, $bottom, $left ) = $matched_values;
					break;
				case 3:
					/* inset:5% 15px 10px; - top left/right bottom */
					$top    = $matched_values[0];
					$left   = $matched_values[1];
					$right  = $matched_values[1];
					$bottom = $matched_values[2];
					break;
				case 2:
					list( $top, $right ) = $matched_values;
					/* inset:4px 8px; - top/bottom left/right */
					list( $bottom, $left ) = $matched_values;
					break;
				/* inset:10px; - applied to all edges */
				case 1:
				default:
					$top = $bottom = $left = $right = $matches[1];
					break;
			}

			return
				'top:' . $top . ';' .
				'right:' . $right . ';' .
				'bottom:' . $bottom . ';' .
				'left:' . $left . ';';
		}, $css );

		$css = preg_replace_callback( '/mask-\w*:[^;]*;/m', static function ( $matches ) {
			return $matches[0] . '-webkit-' . $matches[0];
		}, $css );


		/**
		 * Filters the inline css for the current post.
		 *
		 * @param $css string the css that we want to use
		 * @param $id  int  content ID
		 *
		 * @return string
		 */
		return apply_filters( 'tcb_lightspeed_css_compat', $css, $id );
	}

	/**
	 * Where should we display the css
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public function get_css_location( $type ) {
		/* this will allow controlling the location of stored CSS directly on client sites by defining this constant */
		$css_location = defined( 'TVE_CSS_LOCATION' ) ? TVE_CSS_LOCATION : 'inline';

		/**
		 * Filter the location of the css for the current type and post
		 *
		 * @param string $location file|inline|head|footer
		 * @param string $type
		 * @param int    $post_id
		 */
		return apply_filters( 'tcb_lightspeed_css_location', $css_location, $type, $this->ID );
	}
}
