<?php

use TCB\Lightspeed\Css;
use TCB\Lightspeed\JS;

class TVO_Block {
	const CPT_CAPTURE = 'tvo_capture';
	const CPT_DISPLAY = 'tvo_display';
	const NAME_DISPLAY = 'ovation-display';
	const NAME_CAPTURE = 'ovation-capture';
	const OVATION_BLOCK = 'ovation-block';

	public static function init() {
		if ( self::can_use_blocks() ) {
			TVO_Block::hooks();
			TVO_Block::register_post_type();
			TVO_Block::register_block();
		}
	}

	/**
	 * We can only use blocks while TAR is active
	 *
	 * @return bool
	 */
	public static function can_use_blocks() {
		return function_exists( 'register_block_type' );
	}


	public static function hooks() {
		global $wp_version;
		add_filter( 'tve_allowed_post_type', array( __CLASS__, 'allowed_post_type' ), PHP_INT_MAX, 2 );

		add_filter( 'tcb_custom_post_layouts', array( __CLASS__, 'block_layout' ), 10, 3 );

		add_filter( 'thrive_theme_ignore_post_types', array( __CLASS__, 'thrive_theme_ignore_post_types' ) );

		add_filter( version_compare( $wp_version, '5.7.9', '>' ) ? 'block_categories_all' : 'block_categories', array( __CLASS__, 'register_block_category' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		add_filter( 'rest_prepare_' . self::CPT_DISPLAY, array( __CLASS__, 'rest_prepare' ), 10, 2 );
		add_filter( 'rest_prepare_' . self::CPT_CAPTURE, array( __CLASS__, 'rest_prepare' ), 10, 2 );
		add_filter( 'rest_prepare_' . TVO_CAPTURE_POST_TYPE, array( __CLASS__, 'rest_prepare' ), 10, 2 );
		add_filter( 'rest_prepare_' . TVO_DISPLAY_POST_TYPE, array( __CLASS__, 'rest_prepare' ), 10, 2 );

		add_filter( 'thrive_ignored_post_types', array( __CLASS__, 'ignored_post_types' ), 10, 1 );
	}

	/**
	 * Register both blocks but both have the same render callback
	 */
	public static function register_block() {

		$capture_asset_file = include TVO_PATH . 'blocks/build/capture.asset.php';
		$display_asset_file = include TVO_PATH . 'blocks/build/display.asset.php';

		register_block_type(
			'thrive/' . self::NAME_CAPTURE,
			array(
				'render_callback' => 'TVO_Block::render_block',
				'editor_style'    => 'tvo-capture-block-editor',
			)
		);

		register_block_type(
			'thrive/' . self::NAME_DISPLAY,
			array(
				'render_callback' => 'TVO_Block::render_block',
				'editor_style'    => 'tvo-display-block-editor',
			)
		);

		$unified_asset_file = include TVO_PATH . 'blocks/build/unified.asset.php';
		wp_register_script( 'tvo-unified-block-editor', TVO_URL . 'blocks/build/unified.js', $unified_asset_file['dependencies'], $unified_asset_file['version'], false );
		register_block_type(
			'thrive/' . self::OVATION_BLOCK,
			array(
				'render_callback' => 'TVO_Block::render_block',
				'editor_script'   => 'tvo-unified-block-editor',
				'editor_style'    => 'tvo-unified-block-editor',
			)
		);
	}

	public static function render_block( $attributes ) {
		$html = '';
		if ( isset( $attributes['selectedBlock'] ) && ! is_admin() ) {
			$block_id  = $attributes['selectedBlock'];

			JS::get_instance( $block_id )->enqueue_scripts();

			$block_css = trim( get_post_meta( $block_id, 'tve_custom_css', true ) );
			$block_css = "<style class='tve-symbol-custom-style'>" . tve_prepare_global_variables_for_front( $block_css, true ) . '</style>';
			$lightspeed = Css::get_instance( $block_id );

			if ( $lightspeed->should_load_optimized_styles() ) {
				$block_css = $lightspeed->get_optimized_styles() . $block_css;
			} else {
				Css::enqueue_flat();
			}

			$html = '<div class="tvo-gutenberg-block" id="tvo-gutenberg-block-' . $block_id . '">' . $block_css . self::get_content( $block_id ) . '</div>';
		}

		return $html;
	}


	public static function enqueue_scripts( $hook ) {
		if ( tve_should_load_blocks() ) {
			wp_localize_script( 'tvo-capture-block-editor', 'TVO_Data',
				array(
					'dashboard_url' => admin_url( 'admin.php?page=tvo_admin_dashboard' ),
					'capture_preview' => TVO_URL . '/blocks/img/capture-preview.png',
					'display_preview' => TVO_URL . '/blocks/img/display-preview.png',
				)
			);

			tvo_enqueue_style( 'tvo-block-style', TVO_URL . '/blocks/css/styles.css' );
		}
	}

	/**
	 * Register post types so we can edit them with TAR
	 */
	public static function register_post_type() {

		if ( post_type_exists( TVO_Block::CPT_CAPTURE ) ) {
			return;
		}

		$labels = array(
			'name'               => __( 'Thrive Ovation Blocks', 'thrive-ovation' ),
			'singular_name'      => __( 'Block', 'thrive-ovation' ),
			'add_new'            => __( 'Add New', 'thrive-ovation' ),
			'add_new_item'       => __( 'Add New Block', 'thrive-ovation' ),
			'edit_item'          => __( 'Edit Block', 'thrive-ovation' ),
			'new_item'           => __( 'New Block', 'thrive-ovation' ),
			'all_items'          => __( 'All Blocks', 'thrive-ovation' ),
			'view_item'          => __( 'View Block', 'thrive-ovation' ),
			'search_items'       => __( 'Search Block', 'thrive-ovation' ),
			'not_found'          => __( 'No blocks found', 'thrive-ovation' ),
			'not_found_in_trash' => __( 'No blocks found in trash', 'thrive-ovation' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Thrive Blocks', 'thrive-ovation' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'rewrite'             => false,
			'show_ui'             => false,
			'show_in_rest'        => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'_edit_link'          => 'post.php?post=%d',
		);

		register_post_type( TVO_Block::CPT_CAPTURE, $args );
		register_post_type( TVO_Block::CPT_DISPLAY, $args );
	}

	public static function thrive_theme_ignore_post_types( $post_types ) {

		$post_types[] = self::CPT_CAPTURE;
		$post_types[] = self::CPT_DISPLAY;

		return $post_types;
	}

	/**
	 * Change theme layout for blocks CPTs
	 *
	 * @param $layouts
	 * @param $post_id
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function block_layout( $layouts, $post_id, $post_type ) {
		if ( $post_type === self::CPT_CAPTURE ) {

			$file_path = TVO_PATH . 'blocks/views/capture.php';

			if ( is_file( $file_path ) ) {
				$layouts[ self::CPT_CAPTURE ] = $file_path;
			}

		} elseif ( $post_type === self::CPT_DISPLAY ) {
			$file_path = TVO_PATH . 'blocks/views/display.php';

			if ( is_file( $file_path ) ) {
				$layouts[ self::CPT_DISPLAY ] = $file_path;
			}
		}

		return $layouts;
	}

	/**
	 * Retrieve block content
	 *
	 * @param $block_id
	 *
	 * @return string|string[]
	 */
	public static function get_content( $block_id ) {
		$content = '';
		if ( self::can_use_blocks() ) {

			$content = get_post_meta( (int) $block_id, 'tve_updated_post', true );

			$content = apply_filters( 'tvo_block_content', $content );

			tve_parse_events( $content );

			$do_shortcodes = apply_filters( 'tvo_block_do_shortcodes', true, $block_id );

			if ( $do_shortcodes ) {
				$content = shortcode_unautop( $content );
				$content = do_shortcode( $content );

				//apply thrive shortcodes
				$content = tve_thrive_shortcodes( $content, true );

				/* render the content added through WP Editor (element: "WordPress Content") */
				$content = tve_do_wp_shortcodes( $content, is_editor_page() );
			}
			/**
			 * This only needs to be executed on frontend. Do not execute it in the editor page or when ajax-loading the symbols in the editor
			 */
			if ( ! is_editor_page() && ! wp_doing_ajax() ) {
				$content = tve_restore_script_tags( $content );

				/**
				 * Adds the global style node if it's not in the editor page
				 */
				$content = tve_get_shared_styles( $content ) . $content;
			}

			$content = apply_filters( 'tvo_block_template', $content );

			$content = preg_replace( '!\s+!', ' ', $content );
		}

		return $content;
	}


	public static function allowed_post_type( $allowed, $cpt ) {
		if ( $cpt === self::CPT_DISPLAY || $cpt === self::CPT_CAPTURE ) {
			$allowed = false;
		}

		return $allowed;
	}

	/**
	 * register thrive block category
	 *
	 * @param $categories
	 * @param $post
	 *
	 * @return array|mixed
	 */
	public static function register_block_category( $categories, $post ) {
		$category_slugs = wp_list_pluck( $categories, 'slug' );

		return in_array( 'thrive', $category_slugs, true ) ? $categories : array_merge(
			array(
				array(
					'slug'  => 'thrive',
					'title' => __( 'Thrive Library', 'thrive-ovation' ),
					'icon'  => '',
				),
			),
			$categories
		);
	}

	/**
	 * Add edit & preview link for a block
	 *
	 * @param $response
	 * @param $post
	 *
	 * @return mixed
	 */
	public static function rest_prepare( $response, $post ) {

		if ( function_exists( 'tcb_get_editor_url' ) ) {
			$response->data['edit_url']    = tcb_get_editor_url( $post->ID );
			$response->data['preview_url'] = tcb_get_preview_url( $post->ID );
		}

		return $response;
	}

	public static function ignored_post_types( $post_types ) {
		$post_types[] = self::CPT_CAPTURE;
		$post_types[] = self::CPT_DISPLAY;

		return $post_types;
	}
}
