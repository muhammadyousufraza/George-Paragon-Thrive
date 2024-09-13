<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Symbols_Post_Type
 */
class TCB_Symbols_Post_Type {

	/**
	 * Symbol custom post type
	 */
	const SYMBOL_POST_TYPE = 'tcb_symbol';

	/**
	 * The folder where to save the thumbnails ( previews )
	 */
	const SYMBOL_THUMBS_FOLDER = 'symbols';

	/**
	 * Symbol template file
	 *
	 * @var string
	 */
	private $_template = 'symbol-content.php';

	/**
	 * Section template file ( header or footer )
	 *
	 * @var string
	 */
	private $_section_template = 'section-content.php';

	/**
	 * TCB_Symbols_Post_Type constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Adds action for register another post type
	 */
	public function init() {
		add_action( 'init', [ $this, 'add_symbol_post_type' ], 5 );

		add_filter( 'tcb_custom_post_layouts', [ $this, 'symbol_layout' ], 10, 3 );

		add_filter( 'tcb_post_types', [ $this, 'edit_symbol_post_type' ], 10, 3 );

		add_filter( 'thrive_theme_allow_body_class', [ $this, 'theme_body_class' ], 99, 1 );

		add_filter( 'tve_dash_exclude_post_types_from_index', [ $this, 'exclude_from_index' ] );
	}

	/**
	 * Register symbol post type
	 */
	public function add_symbol_post_type() {

		if ( post_type_exists( self::SYMBOL_POST_TYPE ) ) {
			return;
		}

		register_post_type( self::SYMBOL_POST_TYPE, array(
			'publicly_queryable'  => true,
			'public'              => true,
			'query_var'           => false,
			'description'         => __( 'Thrive Symbol', 'thrive-cb' ),
			'rewrite'             => false,
			'labels'              => array(
				'name'          => __( 'Thrive Symbols', 'thrive-cb' ),
				'singular_name' => __( 'Thrive Symbol', 'thrive-cb' ),
				'add_new_item'  => __( 'Add New Thrive Symbol', 'thrive-cb' ),
				'edit_item'     => __( 'Edit Thrive Symbol', 'thrive-cb' ),
			),
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'exclude_from_search' => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'_edit_link'          => 'post.php?post=%d',
			'map_meta_cap'        => true,
			'capabilities'        => [
				'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
				'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
			],
		) );

		/**
		 * We need to include frontend scripts if the user wants to edit a symbol when he has a product with TAR( TL, TQB, etc ) and TAR is inactive or not installed
		 */
		if ( ! has_action( 'wp_enqueue_scripts', 'tve_frontend_enqueue_scripts' ) ) {
			add_action( 'wp_enqueue_scripts', 'tve_frontend_enqueue_scripts' );
		}

	}

	/**
	 * Edit symbols when TAR is inactive and the user has products with TAR capabilities ( TL, TQB, TU, etc )
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public function edit_symbol_post_type( $post_types ) {
		if ( isset( $post_types['force_whitelist'] ) ) {
			$post_types['force_whitelist'][] = self::SYMBOL_POST_TYPE;
		}

		return $post_types;
	}

	/**
	 * Render symbol layout
	 *
	 * @param array  $layouts
	 * @param int    $post_id
	 * @param string $post_type
	 *
	 * @return mixed
	 */
	public function symbol_layout( $layouts, $post_id, $post_type ) {

		if ( $post_type === self::SYMBOL_POST_TYPE ) {
			//added here to prevent google indexing
			if ( ! is_user_logged_in() || ! TCB_Product::has_post_access( $post_id ) ) {
				wp_redirect( home_url() );
				exit();
			}

			$type      = TCB_Symbols_Taxonomy::get_symbol_type( get_the_ID() );
			$template  = ( $type === 'headers' || $type === 'footers' ) ? $this->_section_template : $this->_template;
			$file_path = TVE_TCB_ROOT_PATH . 'inc/views/symbols/' . $template;

			if ( ! is_file( $file_path ) ) {
				return $layouts;
			}

			$layouts['symbol_template'] = $file_path;
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
	public function theme_body_class( $allow_theme_classes ) {
		$post_type = get_post_type();

		if ( self::SYMBOL_POST_TYPE === $post_type ) {
			$allow_theme_classes = false;
		}

		return $allow_theme_classes;
	}

	/**
	 * Remove symbols from google index
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public function exclude_from_index( $post_types ) {
		$post_types[] = static::SYMBOL_POST_TYPE;

		return $post_types;
	}
}

global $tcb_symbol;
/**
 *  Main instance of TCB Symbols Post Type
 *
 * @return TCB_Symbols_Post_Type
 */
function tcb_symbol() {
	global $tcb_symbol;
	if ( ! $tcb_symbol ) {
		$tcb_symbol = new TCB_Symbols_Post_Type();
	}

	return $tcb_symbol;
}

tcb_symbol();
