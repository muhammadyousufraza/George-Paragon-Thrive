<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use TCB\Lightspeed\Css;
use TCB\Lightspeed\JS;
use TCB\Lightspeed\Main;
use TCB\Lightspeed\Woocommerce;

/**
 * Class TQB_Lightspeed
 */
class TQB_Lightspeed {

	const FORM_HEIGHT = 'form-height';

	const VERSION = 2;

	/**
	 * Post types we can and want to optimize
	 *
	 * @var array
	 */
	private static $post_types = array(
		Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
		Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
		Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
	);

	public static function init() {
		add_filter( 'tve_lightspeed_items_to_optimize', array( __CLASS__, 'get_items_to_optimize' ) );

		add_filter( 'tcb_lightspeed_requires_architect_assets', array( __CLASS__, 'requires_architect_assets' ), 10, 2 );

		add_action( 'tcb_lightspeed_item_optimized', array( __CLASS__, 'on_item_optimized' ), 10, 3 );

		add_action( 'tcb_lightspeed_optimize_localize_data', array( __CLASS__, 'localize_data' ) );

		add_filter( 'tcb_lightspeed_excluded_post_types', array( __CLASS__, 'exclude_post_types' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 9 );
	}

	/**
	 * Check if it's one of the post types we use in TQB
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public static function is_tqb_post_type( $post_type = '' ) {
		if ( empty( $post_type ) ) {
			$post_type = get_post_type();
		}

		return in_array( $post_type, static::$post_types, true );
	}

	/**
	 * Add TQB items for the optimization process
	 *
	 * @param array $groups
	 *
	 * @return array|mixed
	 */
	public static function get_items_to_optimize( $groups = array() ) {

		$page_ids = get_posts( array(
			'posts_per_page' => - 1,
			'post_type'      => static::$post_types,
			'fields'         => 'ids',
		) );

		global $tqbdb;

		$variations = $tqbdb->get_page_variations( array( 'post_id' => $page_ids ) );

		foreach ( $variations as $variation ) {
			$post_type = get_post_type( $variation['page_id'] );

			if ( empty( $groups[ $post_type ] ) ) {
				$post_type_object = get_post_type_object( $post_type );

				$groups[ $post_type ] = array(
					'type'  => $post_type,
					'label' => $post_type_object ? $post_type_object->label : 'Quiz Builder Items',
					'items' => array(),
				);
			}

			$groups[ $post_type ]['items'][] = array(
				'id'        => (int) $variation['page_id'],
				'name'      => $variation['post_title'],
				'optimized' => (int) get_post_meta( $variation['page_id'], Main::OPTIMIZATION_VERSION_META . '_' . $variation['id'], true ) === static::VERSION ? 1 : 0,
				'url'       => TQB_Variation_Manager::get_preview_url( $variation['page_id'], $variation['id'] ),
				'key'       => $variation['id'],
			);

			if ( strpos( $variation['content'], '__TQB__dynamic_DELIMITER' ) !== false ) {
				/* get child variations also */
				$child_variations = $tqbdb->get_page_variations( array( 'parent_id' => $variation['id'] ) );

				foreach ( $child_variations as $child_variation ) {
					$groups[ $post_type ]['items'][] = array(
						'id'        => (int) $child_variation['page_id'],
						'name'      => $variation['post_title'] . ' -> ' . $child_variation['post_title'],
						'optimized' => (int) get_post_meta( $variation['page_id'], Main::OPTIMIZATION_VERSION_META . '_' . $child_variation['id'], true ) === static::VERSION ? 1 : 0,
						'url'       => TQB_Variation_Manager::get_preview_url( $variation['page_id'], $variation['id'] ) . '&' . Thrive_Quiz_Builder::VARIATION_QUERY_CHILD_KEY_NAME . '=' . $child_variation['id'],
						'key'       => $child_variation['id'],
					);
				}
			}
		}

		return $groups;
	}

	/**
	 * TQB post types need architect scripts and styles
	 *
	 * @param $requires
	 * @param $post_id
	 *
	 * @return bool|mixed
	 */
	public static function requires_architect_assets( $requires, $post_id ) {
		if ( static::is_tqb_post_type( get_post_type( $post_id ) ) ) {
			$requires = true;
		}

		return $requires;
	}

	/**
	 * update form version and height after optimization
	 *
	 * @param int              $post_id
	 * @param int              $variation_id
	 * @param \WP_REST_Request $request
	 */
	public static function on_item_optimized( $post_id, $variation_id, $request ) {
		if ( static::is_tqb_post_type( get_post_type( $post_id ) ) ) {
			update_post_meta( $post_id, Main::OPTIMIZATION_VERSION_META . "_$variation_id", static::VERSION );

			$extra_data = $request->get_param( 'extra_data' );

			if ( isset( $extra_data[ static::FORM_HEIGHT ] ) ) {
				update_post_meta( $post_id, static::FORM_HEIGHT, $extra_data[ static::FORM_HEIGHT ] );
			}
		}
	}

	/**
	 * Set the key for quiz builder variations
	 *
	 * @param array $localize_object
	 *
	 * @return array|mixed
	 */
	public static function localize_data( $localize_object = array() ) {
		if ( static::is_tqb_post_type() ) {
			if ( isset( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_CHILD_KEY_NAME ] ) ) {
				$localize_object['key'] = $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_CHILD_KEY_NAME ];
			} else if ( isset( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
				$localize_object['key'] = $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ];
			}
		}

		return $localize_object;
	}

	/**
	 * Exclude quizzes from optimization because it has no content
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function exclude_post_types( $post_types ) {
		$post_types[] = Thrive_Quiz_Builder::SHORTCODE_NAME;

		return $post_types;
	}

	/**
	 * Load assets inline with the variation
	 *
	 * @param $variation
	 *
	 * @return array
	 */
	public static function get_optimized_assets( $variation ) {
		$style = '';
		$js    = '';
		$files = [];

		$variation_id = empty( $variation['content_child_variation_id'] ) ? $variation['id'] : $variation['content_child_variation_id'];

		if ( static::has_lightspeed() ) {

			$lightspeed_css = Css::get_instance( $variation['page_id'] );
			$key            = 'base_' . $variation_id;

			$style = $lightspeed_css->get_inline_css( $key );

			if ( ! Main::is_enabled() || empty( $style ) ) {
				$files['flat'] = Css::inline_flat_style();
				$style         = '';
			}

			$js = JS::get_instance( $variation['page_id'], '_' . $variation_id )->load_modules( true );

			// Enqueue html2canvas script
			if ( $variation['post_type'] === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) {
				$js .= '<script id="tve_frontend_html2canvas" type="text/javascript" src="' . tqb()->plugin_url( 'assets/js/dist/tqb-html2canvas.min.js' ) . '" ></script>';

				if ( $variation_id !== $variation['id'] ) {
					$js .= JS::get_instance( $variation['page_id'], '_' . $variation['id'] )->load_modules( true );
					/* also include the js and css from the parent variation because it has everything except the dynamic content */
					if ( ! empty( $style ) ) {
						$style .= $lightspeed_css->get_inline_css( 'base_' . $variation['id'] );
					}
				}
			}

		}

		if ( \TCB\Integrations\WooCommerce\Main::active() && class_exists( 'TCB\Lightspeed\Woocommerce' ) && method_exists( 'TCB\Lightspeed\Woocommerce', 'get_modules' ) && Woocommerce::get_modules( $variation['page_id'], '_' . $variation_id ) ) {
			$woo_js  = Woocommerce::get_woo_js_modules();
			$woo_css = Woocommerce::get_woo_styles();

			$files = array_merge( $files, $woo_js );

			foreach ( $woo_js as $enqueue_key => $file_url ) {
				$files[ $enqueue_key ] = sprintf( '<script id="%s" type="text/javascript" src="%s" ></script>', $enqueue_key, $file_url );
			}

			foreach ( $woo_css as $enqueue_key => $file_url ) {
				$files[ $enqueue_key ] = sprintf(
					'<link rel="stylesheet" id="%s-css" href="%s" type="text/css" media="all" />',
					$enqueue_key,
					$file_url
				);
			}

		}

		return array(
			'style' => $style,
			'files' => $files,
			'js'    => $js,
		);
	}

	/**
	 * Save styles and js for a variation
	 *
	 * @param $post_id
	 * @param $variation_id
	 */
	public static function save_optimized_assets( $post_id, $variation_id ) {
		if ( static::has_lightspeed() ) {
			Css::get_instance( $post_id )->save_optimized_css( "base_$variation_id", isset( $_POST['optimized_styles'] ) ? $_POST['optimized_styles'] : '' );
			JS::get_instance( $post_id, "_$variation_id" )->save_js_modules( isset( $_POST['js_modules'] ) ? $_POST['js_modules'] : array() );

			if ( class_exists( 'TCB\Lightspeed\Main' ) && method_exists( 'TCB\Lightspeed\Main', 'optimized_advanced_assets' ) ) {
				Main::optimized_advanced_assets( $post_id, $_POST, '_' . $variation_id );
			}

			update_post_meta( $post_id, Main::OPTIMIZATION_VERSION_META . "_$variation_id", static::VERSION );
		}

		if ( isset( $_POST[ static::FORM_HEIGHT ] ) ) {
			update_post_meta( $post_id, static::FORM_HEIGHT, $_POST[ static::FORM_HEIGHT ] );
		}
	}

	/**
	 * Return placeholder height style for splash page
	 *
	 * @param int $quiz_id
	 *
	 * @return string
	 */
	public static function get_quiz_placeholder_style( $quiz_id = 0 ) {
		$placeholder_style = '';

		$quiz      = new TQB_Structure_Manager( $quiz_id );
		$splash_id = $quiz->get_splash_page_id();

		if ( ! empty( $splash_id ) ) {
			$form_height = get_post_meta( $splash_id, static::FORM_HEIGHT, true );

			if ( ! empty( $form_height ) && is_array( $form_height ) ) {
				$heights      = '';
				$extra_height = empty( Thrive_Quiz_Builder::get_settings( 'tqb_promotion_badge' ) ) ? 0 : 30;

				foreach ( $form_height as $device => $height ) {
					$heights .= '--tqb-placeholder-height-' . $device . ':' . ( (float) $height + $extra_height ) . 'px;';
				}

				$placeholder_style = ' style="' . $heights . '" ';
			}
		}

		return $placeholder_style;
	}

	/**
	 * Optimize google fonts into one request
	 *
	 * @param $fonts
	 *
	 * @return array
	 */
	public static function optimize_font_imports( $fonts ) {
		$optimized_fonts = [];
		$font_families   = [];

		foreach ( $fonts as $font ) {
			preg_match( '/googleapis\.com\/css\?family=([^:]*:[^&]*)/m', $font, $matches );

			if ( ! empty( $matches ) && count( $matches ) === 2 ) {
				$font_families[] = $matches[1];
			} else {
				$optimized_fonts[] = $font;
			}
		}

		if ( ! empty( $font_families ) ) {
			$optimized_fonts[] = sprintf( 'https://fonts.googleapis.com/css?family=%s&display=swap',
				implode( '|', $font_families )
			);
		}

		return $optimized_fonts;
	}

	/**
	 * Load scripts for optimizing form height on splash pages
	 */
	public static function enqueue_scripts() {
		if ( static::has_lightspeed() && Main::is_optimizing() && get_post_type() === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ) {
			wp_enqueue_script( 'tqb-lightspeed-optimize', tqb()->plugin_url( 'assets/js/dist/lightspeed.min.js' ), array( 'jquery' ) );

			add_filter( 'tcb_lightspeed_front_optimize_dependencies', static function ( $deps ) {
				$deps[] = 'tqb-lightspeed-optimize';

				return $deps;
			} );
		}
	}

	/**
	 * Check if we have the code for lightspeed
	 *
	 * @return bool
	 */
	public static function has_lightspeed() {
		return class_exists( 'TCB\Lightspeed\Main', false );
	}
}
