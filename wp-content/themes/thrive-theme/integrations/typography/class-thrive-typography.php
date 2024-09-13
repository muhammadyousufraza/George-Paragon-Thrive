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
 * Class Thrive_Typography
 */
class Thrive_Typography {

	use Thrive_Belongs_To_Skin;

	const DEFAULT_TITLE = 'Default Typography';

	/**
	 * @var Thrive_Typography instance
	 */
	private static $_instance = null;

	/**
	 * typography set id
	 *
	 * @var int
	 */
	public $ID;

	/**
	 * meta key used to store the styles in the postmeta table
	 */
	const META_STYLE = 'style';

	/**
	 * meta key used to store the default styles so we can use them at reset
	 */
	const META_DEFAULT_STYLE = 'default_style';

	/**
	 * meta key used to store the "default typography" flag
	 */
	const META_DEFAULT = 'default';

	/**
	 *
	 * @var WP_Post
	 */
	protected $post;


	/**
	 * Thrive_Typography constructor.
	 *
	 * @param int|string $id
	 */
	public function __construct( $id ) {

		$this->post = get_post( $id );

		if ( $this->post !== null ) {
			$this->ID = $this->post->ID;
		}
	}

	/**
	 * Get post meta by key
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get_meta( $key = self::META_STYLE ) {
		return get_post_meta( $this->ID, $key, true );
	}

	/**
	 * Store styles array or default value
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set_meta( $key, $value ) {
		/* apparently, ID can be null/unset ... ? */
		if ( $this->ID ) {
			update_post_meta( $this->ID, $key, $value );
		}

		return $this;
	}

	/**
	 * Singleton implementation for class instance
	 *
	 * @param int typography_id
	 *
	 * @return Thrive_Typography
	 */
	public static function instance( $typography_id = 0 ) {

		if ( empty( $typography_id ) ) {
			$skin          = thrive_skin();
			$skin_id       = $skin->ID;
			$typography_id = $skin->get_active_typography();
		}

		/* if we don't have any instance or when we send an typography_id that it's not the same as the previous one, we create a new instance */
		if ( null === self::$_instance || ( ! empty( $typography_id ) && self::$_instance->ID !== $typography_id ) || is_wp_error( $typography_id ) ) {
			self::$_instance = new self( $typography_id );
			if ( isset( $skin_id ) ) {
				self::$_instance->set_cached_skin_id( $skin_id );
			}
		}

		return self::$_instance;
	}

	public static function register_post_type() {
		register_post_type( THRIVE_TYPOGRAPHY, [
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => Thrive_Theme_Product::has_access(),
			'query_var'           => false,
			'description'         => 'Thrive Typography',
			'rewrite'             => false,
			'labels'              => [
				'name' => 'Thrive Typography',
			],
			'_edit_link'          => 'post.php?post=%d',
		] );
	}

	/**
	 * Register typography endpoint
	 */
	public static function rest_api_init() {
		require_once 'class-thrive-typography-rest.php';

		Thrive_Typography_REST::register_routes();
	}

	/**
	 * Return the css saved in the typography meta, remove extra spaces and maybe wrap it in a style node.
	 *
	 * @param boolean $wrap
	 *
	 * @return mixed
	 */
	public function style( $wrap = false ) {
		/**
		 * This can happen if users updated TTB and did not update TAr
		 */
		if ( ! function_exists( 'tcb_default_style_provider' ) ) {
			return '';
		}

		$style_api  = tcb_default_style_provider();
		$raw_styles = $this->get_style();

		if ( ! $wrap ) {
			return $raw_styles;
		}

		return thrive_css_helper( $this )
			->set_style( $style_api->get_processed_styles( $raw_styles, 'string' ) )
			->maybe_wrap( $wrap ? 'thrive-typography' : '' );
	}

	/**
	 *
	 * Prepares editor layout and returns a path to the file that needs to be included
	 *
	 * Return typography editor layout
	 */
	public function prepare_layout() {
		if ( $this->is_admin_preview() ) {
			/* removes admin_bar */
			add_filter( 'show_admin_bar', '__return_false' );
		}

		return THEME_PATH . '/integrations/typography/editor-layout.php';
	}

	/**
	 * We don't need any instances on the typography page
	 *
	 * @param $instances
	 *
	 * @return array
	 */
	public static function tcb_remove_instances( $instances ) {
		if ( Thrive_Utils::is_theme_typography() ) {
			$instances = [
				'link'               => $instances['link'],
				'default_typography' => $instances['default_typography'],
			];
		}

		return $instances;
	}

	/**
	 * Returns if a typography is active or not for the active skin
	 *
	 * @return boolean
	 */
	public function is_default() {
		return ! empty( $this->get_meta( 'default' ) );
	}

	/**
	 * Get the active global colors from the skin's default typography set.
	 *
	 * @param array $all_global_colors
	 *
	 * @return array
	 */
	public function get_global_colors( $all_global_colors ) {
		$typography_global_colors = [];

		if ( ! empty( $this->ID ) ) {
			/* get the css from the default typography set */
			$typography_css = json_encode( $this->get_style() );

			/* if a global color is used in the default typography set, add it to the array */
			foreach ( $all_global_colors as $color ) {
				if ( $color['active'] === 1 && strpos( $typography_css, 'var(--tcb-color-' . $color['id'] ) !== false ) {
					$typography_global_colors[ $color['id'] ] = $color;
				}
			}
		}

		return $typography_global_colors;
	}

	/**
	 * Reset the css to the initial default style
	 */
	public function reset() {
		$this->set_meta( static::META_STYLE, $this->get_meta( static::META_DEFAULT_STYLE ) );
	}

	/**
	 * Set current typography set as default
	 *
	 * @param string|int $for_skin_id Optional, allows setting a default typography for another skin
	 *
	 * @return Thrive_Typography
	 */
	public function set_default( $for_skin_id = null ) {

		$posts = get_posts( [
			'posts_per_page' => - 1,
			'post_type'      => THRIVE_TYPOGRAPHY,
			'tax_query'      => [ thrive_skin( $for_skin_id )->build_skin_query_params() ],
		] );

		/* get all the typography sets from the skin and mark them as not being default */
		foreach ( $posts as $p ) {
			update_post_meta( $p->ID, static::META_DEFAULT, 0 );
		}

		$this->set_meta( static::META_DEFAULT, 1 );

		return $this;
	}

	/**
	 * Make a typography active for a skin
	 *
	 * @param $skin_id
	 *
	 * @return Thrive_Typography
	 */
	public function assign_to_skin( $skin_id = null ) {
		if ( empty( $skin_id ) ) {
			$skin_id = thrive_skin()->ID;
		}

		wp_set_object_terms( $this->ID, $skin_id, SKIN_TAXONOMY );

		return $this;
	}

	/**
	 * Export data for a typography
	 *
	 * @return array
	 */
	public function export() {
		return [
			'post_type'  => THRIVE_TYPOGRAPHY,
			'post_title' => $this->post->post_title,
			'meta_input' => [
				'style'   => $this->get_style(),
				'default' => $this->is_default(),
			],
		];
	}

	/**
	 * Helper (shortcut) function for saving styles
	 *
	 * @param array $styles
	 *
	 * @return Thrive_Typography
	 */
	public function set_style( $styles ) {
		return $this->set_meta( static::META_STYLE, $styles );
	}

	/**
	 * Helper (shortcut) function for getting styles
	 *
	 * @return array
	 */
	public function get_style() {
		$styles = (array) $this->get_meta( static::META_STYLE );

		/**
		 * Filters the style array just after reading it from the database
		 *
		 * @param array             $styles
		 * @param Thrive_Typography $instance
		 */
		return apply_filters( 'thrive_theme_typography_style_array', $styles, $this );
	}

	/**
	 * Checks if the current request in for the previewing the style in WP admin
	 *
	 * @return bool
	 */
	public function is_admin_preview() {
		return ! empty( $_REQUEST['admin_preview_id'] ) && (int) $_REQUEST['admin_preview_id'] === $this->ID && Thrive_Utils::is_theme_typography();
	}
}

/**
 * Return Thrive_Typography instance
 *
 * @param int id typography id
 *
 * @return Thrive_Typography
 */
function thrive_typography( $id = 0 ) {
	if ( empty( $id ) && ! empty( $_REQUEST['admin_preview_id'] ) ) {
		$id = (int) $_REQUEST['admin_preview_id'];
	}

	return Thrive_Typography::instance( $id );
}
