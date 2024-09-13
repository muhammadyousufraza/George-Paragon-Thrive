<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Symbols_Post_Type', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/symbols/class-tcb-symbols-post-type.php';
}

if ( ! class_exists( 'TCB_Symbols_Taxonomy', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/symbols/class-tcb-symbols-taxonomy.php';
}


/**
 * Class Thrive_Transfer_Symbols
 */
class Thrive_Transfer_Symbols extends Thrive_Transfer_Base {

	/**
	 * Symbols regular expression it matches the symbols from theme content
	 */
	const SYMBOL_THEME_REGEX = '/thrive_symbol id=\'(\d*)\'/';

	/**
	 * Symbols regular expression it matches the symbols from tar post content
	 */
	const SYMBOL_TAR_REGEX = '/__CONFIG_post_symbol__{[^\d]*(\d*)[^}]*}__CONFIG_post_symbol__/';


	/**
	 * Json filename where to keep the data for the symbols
	 *
	 * @var string
	 */
	public static $file = 'symbols.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'symbols';

	/**
	 * Read all the symbols from the content
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function read_symbols( &$content ) {
		/* Export symbols with the format [thrive_symbol id=123]*/
		$this->theme_content_symbols( $content );

		/* Export symbols with the format __CONFIG_post_symbol__ */
		$this->tar_content_symbols( $content );

		return $this;
	}

	/**
	 * Get all the symbols from the theme content
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function theme_content_symbols( &$content ) {
		preg_match_all( self::SYMBOL_THEME_REGEX, $content, $matches );

		if ( ! empty( $matches ) && count( $matches ) === 2 ) {

			foreach ( $matches[1] as $key => $symbol_id ) {
				$hash = md5( $symbol_id );

				/* Add item to the export */
				$this->add_item( $hash, $symbol_id );

				/* Replace shortcode in the content */
				$content = $this->replace_shortcode( $matches[0][ $key ], $symbol_id, $hash, $content );
			}
		}

		return $this;
	}

	/**
	 * TODO Maybe mix this function with the one from above
	 *
	 * Get all the symbols from the TAR content
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function tar_content_symbols( &$content ) {

		preg_match_all( self::SYMBOL_TAR_REGEX, $content, $matches );

		if ( ! empty( $matches ) && count( $matches ) === 2 ) {

			foreach ( $matches[1] as $key => $symbol_id ) {
				$hash = md5( $symbol_id );
				/* Add item to the export */
				$this->add_item( $hash, $symbol_id );

				/* Replace shortcode in the content */
				$content = $this->replace_shortcode( $matches[0][ $key ], $symbol_id, $hash, $content );

				/* replace old symbol data with the hash */
				$content       = str_replace( "thrv_symbol_{$symbol_id}", "thrv_symbol_{$hash}", $content );
				$data_id_regex = 'data-id=[^\d]*' . $symbol_id . '[^\d]"';

				$content = preg_replace( '/' . $data_id_regex . '/', "data-id=\\\"{$hash}\\\"", $content );
			}
		}

		return $this;
	}

	/**
	 * Replace the old shortcode with a new one created for the export data
	 *
	 * @param string $old_shortcode
	 * @param int    $symbol_id
	 * @param string $hash
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function replace_shortcode( $old_shortcode, $symbol_id, $hash, $content ) {
		$new_shortcode = str_replace( $symbol_id, $hash, $old_shortcode );

		return str_replace( $old_shortcode, $new_shortcode, $content );
	}

	/**
	 * Read header and footer data
	 *
	 * @param array $hf Ids of the header and footer that we want to export
	 *
	 * @return $this
	 */
	public function read_hf( $hf ) {

		if ( ! empty( $hf ) ) {
			$header_id = $hf[ THRIVE_HEADER_SECTION ]['id'];
			$footer_id = $hf[ THRIVE_FOOTER_SECTION ]['id'];

			if ( ! empty( $header_id ) && empty( $this->items[ $header_id ] ) && ! $this->exists( $header_id ) ) {
				$this->items[ md5( $header_id ) ] = static::get_symbol_data( $header_id, THRIVE_HEADER_SECTION );
			}

			if ( ! empty( $footer_id ) && empty( $this->items[ $footer_id ] ) && ! $this->exists( $footer_id ) ) {
				$this->items[ md5( $footer_id ) ] = static::get_symbol_data( $footer_id, THRIVE_FOOTER_SECTION );
			}
		}

		return $this;
	}

	/**
	 * Process dynamic data from the symbols
	 *
	 * @return $this
	 */
	public function parse_dynamic_data() {
		$content = json_encode( $this->items );

		$this->controller
			->process_images( $content )
			->process_global_colors( $content )
			->process_global_gradients( $content );

		foreach ( $this->items as $symbol_data ) {
			if ( ! empty( $symbol_data ) ) {
				$this->controller->process_thumbnail( $symbol_data['ID'], TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER );
			}
		}

		Thrive_Transfer_Utils::replace_content_ids( $content );

		$this->items = json_decode( $content, true );

		return $this;
	}

	/**
	 * Add items to the archive data
	 */
	public function add() {
		$symbols = $this->archive_data['symbols'];

		foreach ( $this->items as $id => $item ) {
			if ( empty( $symbols[ $id ] ) ) {
				$symbols[ $id ] = $item;
			}
		}

		if ( empty( $symbols ) ) {
			$symbols = [];
		}

		$this->archive_data['symbols'] = $symbols;
	}

	/**
	 * Add symbols to the export array
	 *
	 * @param string $hash
	 * @param int    $symbol_id
	 */
	public function add_item( $hash, $symbol_id ) {
		if ( empty( $this->items[ $hash ] ) && ! $this->exists( $hash ) ) {
			$this->items[ $hash ] = static::get_symbol_data( $symbol_id, 'symbol' );
		}
	}

	/**
	 * Import symbols / headers / footers
	 *
	 * @throws Exception
	 */
	public function import() {
		if ( ! empty( $this->data ) ) {
			$this->controller
				->import_images()
				->import_global_colors()
				->import_global_gradients();

			$this->save();
		}
	}

	/**
	 * Save symbols, headers and footers in the db
	 *
	 * @return $this
	 */
	public function save() {
		if ( empty( $this->archive_data[ $this->tag ] ) ) {
			$this->archive_data[ $this->tag ] = [];
		}

		$symbols = $this->archive_data[ $this->tag ];

		foreach ( $this->data as $hash => $element ) {
			if ( isset( $element['type'] ) && ! $this->already_saved( $hash ) ) {
				$type    = $element['type'] . 's';
				$element = $this->replace_content_before_import( $element );
				$new_id  = $this->insert( $element );
				$this->update_custom_css( $new_id, $element );

				if ( in_array( $type, [ 'headers', 'footers' ] ) ) {
					TCB_Symbols_Taxonomy::add_to_tax( $new_id, $type );
				}

				$this->save_symbol_thumbnail( $new_id, $hash );

				$symbols[ $hash ] = $new_id;
			}
		}

		$this->archive_data[ $this->tag ] = $symbols;

		return $this;
	}

	/**
	 * Save the thumbnail for this symbol ( header and footer included ) when it's imported.
	 *
	 * @param $new_id
	 * @param $hash
	 */
	public function save_symbol_thumbnail( $new_id, $hash ) {
		$archive_data = $this->archive_data->get_data();

		if ( ! empty( $archive_data['images'][ $hash ] ) ) {
			Thrive_Transfer_Utils::save_thumbnail( $archive_data['images'][ $hash ], $new_id );
		}
	}

	/**
	 * Insert a post in the db
	 *
	 * @param array $item
	 *
	 * @return int|WP_Error
	 */
	public function insert( $item ) {
		$item['meta_input'][ THRIVE_EXPORT_ID ] = Thrive_Utils::get_unique_id();

		return wp_insert_post(
			[
				'post_title'  => $item['title'],
				'post_type'   => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
				'meta_input'  => $item['meta_input'],
				'post_status' => 'publish',
			]
		);
	}

	/**
	 * Replace old ids from the css selector with the new id
	 *
	 * @param int   $id
	 * @param array $post
	 */
	public function update_custom_css( $id, $post ) {
		update_post_meta( $id, 'tve_custom_css', str_replace( 'thrv_symbol_' . $post['ID'], 'thrv_symbol_' . $id, $post['meta_input']['tve_custom_css'] ) );
	}

	/**
	 * Replace symbol content before saving it in the db
	 *
	 * @param array $post
	 *
	 * @return mixed
	 */
	public function replace_content_before_import( $post ) {
		$colors_map    = $this->archive_data['colors'];
		$gradients_map = $this->archive_data['gradient'];
		$images        = $this->archive_data['images'];
		$site_url      = site_url();

		$post['meta_input']['tve_updated_post'] = Thrive_Transfer_Utils::replace_images( $post['meta_input']['tve_updated_post'], $images );
		$post['meta_input']['tve_updated_post'] = Thrive_Transfer_Utils::replace_auto_menu_id( $post['meta_input']['tve_updated_post'] );
		$post['meta_input']['tve_updated_post'] = str_replace( '{{site_url}}', $site_url, $post['meta_input']['tve_updated_post'] );

		$post['meta_input']['tve_custom_css'] = Thrive_Transfer_Utils::replace_images( $post['meta_input']['tve_custom_css'], $images );
		$post['meta_input']['tve_custom_css'] = Thrive_Transfer_Utils::replace_keys_in_content( $post['meta_input']['tve_custom_css'], $colors_map, Thrive_Transfer_Utils::COLOR_PREFIX, true );
		$post['meta_input']['tve_custom_css'] = Thrive_Transfer_Utils::replace_keys_in_content( $post['meta_input']['tve_custom_css'], $gradients_map, Thrive_Transfer_Utils::GRADIENT_PREFIX, true );

		return $post;
	}

	/**
	 * Get data for a specific symbol
	 *
	 * @param int    $id
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_symbol_data( $id, $type ) {
		$post = get_post( $id );

		$data = [];

		if ( ! empty( $post ) ) {
			$href_site_url = 'href="' . site_url();

			$data = [
				'ID'         => $post->ID,
				'title'      => $post->post_title,
				'meta_input' => [
					'tve_updated_post' => str_replace( $href_site_url, 'href="{{site_url}}', $post->tve_updated_post ),
					'tve_custom_css'   => $post->tve_custom_css,
					'icons'            => $post->icons,
					'tve_globals'      => $post->tve_globals ?: [ 'e' => 1 ],
				],
				'type'       => $type,
			];
		}

		return $data;
	}

}
