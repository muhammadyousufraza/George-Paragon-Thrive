<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TCB_Symbols_Block
 */
class TCB_Symbols_Block {
	const NAME = 'architect-block';

	static public function init() {
		if ( static::can_use_blocks() ) {
			TCB_Symbols_Block::hooks();
			TCB_Symbols_Block::register_block();
		}
	}

	static public function can_use_blocks() {
		return function_exists( 'register_block_type' );
	}


	static public function hooks() {
		global $wp_version;
		add_filter( version_compare( $wp_version, '5.7.9', '>' ) ? 'block_categories_all' : 'block_categories', [ __CLASS__, 'register_block_category' ], 10, 2 );
	}

	static public function register_block() {

		$asset_file = include TVE_TCB_ROOT_PATH . 'blocks/build/block.asset.php';

		// Register our block script with WordPress
		wp_register_script( 'tar-block-editor', TVE_EDITOR_URL . 'blocks/build/block.js', $asset_file['dependencies'], $asset_file['version'], false );

		register_block_type(
			'thrive/' . self::NAME,
			array(
				'render_callback' => 'TCB_Symbols_Block::render_block',
				'editor_script'   => 'tar-block-editor',
				'editor_style'    => 'tar-block-editor',
			)
		);

		wp_localize_script( 'tar-block-editor', 'TAR_Block',
			array(
				'block_preview' => tve_editor_url() . '/admin/assets/images/block-preview.png',
			)
		);
	}

	static public function render_block( $attributes ) {
		if ( isset( $attributes['selectedBlock'] ) && ! is_admin() ) {

			return TCB_Symbol_Template::symbol_render_shortcode( [
				'id' => $attributes['selectedBlock'],
			], true );

		}

		return '';
	}

	static public function register_block_category( $categories, $post ) {
		$category_slugs = wp_list_pluck( $categories, 'slug' );

		return in_array( 'thrive', $category_slugs, true ) ? $categories : array_merge(
			array(
				array(
					'slug'  => 'thrive',
					'title' => __( 'Thrive Library', 'thrive-cb' ),
					'icon'  => '',
				),
			),
			$categories
		);
	}
}
