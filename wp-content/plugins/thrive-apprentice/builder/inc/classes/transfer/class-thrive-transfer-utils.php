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
 * Class Thrive_Transfer_Utils
 */
class Thrive_Transfer_Utils {

	/**
	 * Placeholder used to add menu id in the newly imported content
	 */
	const AUTO_MENU_ID_PLACEHOLDER = '__menu_auto_id__';

	/**
	 * Placeholder used to change template id in the content
	 */
	const TEMPLATE_ID_PLACEHOLDER = '__template_id__';

	/**
	 * Color prefix used to replace colors at import
	 */
	const COLOR_PREFIX = '--tcb-color-';

	/**
	 * Gradient prefix used to replace gradients at import
	 */
	const GRADIENT_PREFIX = '--tcb-gradient-';


	/**
	 * Require all the files from a specific path including files from subdirectories
	 *
	 * @param string $path
	 */
	public static function require_files( $path ) {

		$items = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;

			/* if the item is a folder, do recursion inside it */
			if ( is_dir( $item_path ) ) {
				static::require_files( $item_path );
			}

			if ( is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				require_once $item_path;
			}
		}
	}

	/**
	 * Remove the ID from menu so they will become general
	 *
	 * @param $content
	 */
	public static function replace_content_ids( &$content ) {
		$menu_regex           = '/__CONFIG_widget_menu__\S*?\\\\["|\']menu_id?\\\\["|\']:?\\\\["|\'](\d*)?\\\\["|\']/mx';
		$template_theme_regex = '/\.tve-theme-(\d*)/m';

		$content = preg_replace_callback( $menu_regex, function ( $m ) {
			return str_replace( $m[1], static::AUTO_MENU_ID_PLACEHOLDER, $m[0] );
		}, $content );

		$content = preg_replace_callback( $template_theme_regex, function ( $m ) {
			return str_replace( $m[1], static::TEMPLATE_ID_PLACEHOLDER, $m[0] );
		}, $content );
	}

	/**
	 * Replace image hash inside content
	 *
	 * @param string $content
	 * @param array  $images
	 *
	 * @return string
	 */
	public static function replace_images( $content, $images ) {

		foreach ( $images as $hash => $image_data ) {
			$content = str_replace( '{{img=' . $hash . '}}', $image_data['url'], $content );
		}

		return $content;
	}

	/**
	 * Replace each menu that has a general ID, with the first menu that we find
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public static function replace_auto_menu_id( $content ) {

		if ( ! class_exists( 'TCB_Utils', false ) ) {
			require_once defined( TVE_TCB_ROOT_PATH ) . 'inc/classes/class-tcb-utils.php';
		}

		if ( strpos( $content, static::AUTO_MENU_ID_PLACEHOLDER ) !== false ) {
			$new_menu_id = tve_get_default_menu();

			$content = str_replace( static::AUTO_MENU_ID_PLACEHOLDER, $new_menu_id, $content );
		}

		return $content;
	}

	/**
	 * Replace template ids in style and content
	 *
	 * @param $template_id
	 */
	public static function replace_template_id( $template_id ) {

		$meta_fields = [ 'sections', 'style' ];

		foreach ( $meta_fields as $meta ) {
			$content = get_post_meta( $template_id, $meta, true );

			$content = json_encode( $content );

			$content = str_replace( static::TEMPLATE_ID_PLACEHOLDER, $template_id, $content );

			$content = json_decode( $content, true );

			update_post_meta( $template_id, $meta, $content );
		}
	}

	/**
	 * @param        $content
	 * @param        $map
	 * @param string $prefix
	 * @param bool   $hash_key
	 *
	 * @return mixed
	 */
	public static function replace_keys_in_content( $content, $map, $prefix = '', $hash_key = false ) {
		if ( ! empty( $content ) && ! empty( $map ) ) {
			foreach ( $map as $key => $new_key ) {
				$search  = $prefix . ( $hash_key ? md5( $key ) : $key );
				$replace = $prefix . $new_key;

				$content = str_replace( $search, $replace, $content );
			}
		}

		return $content;
	}

	/**
	 * Replace symbol ids hash with the new imported map
	 *
	 * @param string $content
	 * @param array  $symbols_map
	 *
	 * @return mixed
	 */
	public static function replace_theme_symbols( $content, $symbols_map ) {
		preg_match_all( '/\[thrive_symbol id=\\\'(.*)\\\'\]/U', $content, $matches );
		if ( ! empty( $matches ) ) {
			foreach ( $matches[1] as $key => $hash ) {
				if ( ! empty( $symbols_map[ $hash ] ) ) {
					$old_shortcode = $matches[0][ $key ];
					$new_shortcode = str_replace( $hash, $symbols_map[ $hash ], $old_shortcode );

					/* replace symbol shortcode id with the new symbol id */
					$content = str_replace( $old_shortcode, $new_shortcode, $content );
				} else {
					throw new Exception( 'Hash not found' );
				}
			}
		}

		return $content;
	}

	/**
	 * Replace global colors and gradients used in global styles
	 *
	 * @param array $colors
	 * @param array $gradients
	 * @param mixed $styles
	 *
	 * @return array
	 */
	public static function replace_global_styles_data( $colors, $gradients, $styles ) {
		$styles = json_encode( $styles );

		$styles = static::replace_keys_in_content( $styles, $colors, self::COLOR_PREFIX, true );
		$styles = static::replace_keys_in_content( $styles, $gradients, self::GRADIENT_PREFIX, true );

		return json_decode( $styles, true );
	}

	/**
	 * Do some logic before saving the new thumbnail data - rename the old file and the old url.
	 *
	 * @param $thumb_data
	 * @param $id
	 */
	public static function save_thumbnail( $thumb_data, $id ) {

		$file = isset( $thumb_data['file'] ) ? $thumb_data['file'] : '';

		/* if the file doesn't exist, stop */
		if ( ! file_exists( $file ) ) {
			return;
		}

		unset( $thumb_data['file'] );

		/* copy the file and rename using the right ID */
		copy( $file, str_replace( basename( $file ), $id . '.png', $file ) );

		/* delete the old uploaded file because the name ( section ID ) was from the exported section  */
		unlink( $file );

		$url = $thumb_data['url'];

		$thumb_data['url'] = str_replace( basename( $url ), $id . '.png', $url );

		TCB_Utils::save_thumbnail_data( $id, $thumb_data );
	}

	/**
	 * Prepare content before save
	 *
	 * @param array $data
	 * @param array $map
	 *
	 * @return array|mixed|object
	 */
	public static function prepare_content( $data, $map ) {
		$content = json_encode( $data );

		/* replace images for content and style */
		$content = static::replace_images( $content, $map['images'] );

		$content = static::replace_theme_symbols( $content, $map['symbols'] );

		$content = static::replace_keys_in_content( $content, $map['colors'], self::COLOR_PREFIX, true );
		$content = static::replace_keys_in_content( $content, $map['gradient'], self::GRADIENT_PREFIX, true );
		$content = static::replace_keys_in_content( $content, $map['styles'], '', true );
		$content = static::replace_tar_symbols( $content, $map['symbols'] );
		$content = static::replace_auto_menu_id( $content );

		return json_decode( $content, true );
	}

	/**
	 * Remove site url from content
	 *  > just the site url
	 *  > site url inside static link
	 *  > site url inside the pagination shortcode
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public static function replace_site_url( $content ) {

		$site_url = site_url();

		$content = preg_replace( "/(\"|')(" . preg_quote( $site_url, '/' ) . "\/?)(\"|')/m", '$1$3', $content );

		$site_url = str_replace( '://', ':\\/\\/', $site_url );

		$site_url = preg_quote( $site_url, '/' );

		$content = preg_replace( "/(\"|')({$site_url}\/?)(\"|')/m", '$1$3', $content );

		$content = preg_replace( '/\s?data-attr-static-link=\\\\"[^"]*' . $site_url . '[^"]*"/m', '', $content );

		$content = preg_replace( "/\s?static-link='[^']*{$site_url}[^']*'/m", '', $content );

		$content = preg_replace_callback( '/\[tcb_pagination\s.*\[\\\\\/tcb_pagination\]/ms', static function ( $match ) {
			return preg_replace( '/href=\\\\"[^"]*\\\\?"/m', 'href=\"javascript:void(0)\"', $match[0] );
		}, $content );

		return $content;
	}

	/**
	 * Check if we also have symbols with the format of TAR content
	 *
	 * @param string $content
	 * @param array  $symbols
	 *
	 * @return mixed
	 */
	public static function replace_tar_symbols( &$content, $symbols ) {
		$tar_symbols_regex = '__CONFIG_post_symbol__{[^\d]*([a-f0-9]{32})[^}]*}__CONFIG_post_symbol__';
		preg_match_all( '/' . $tar_symbols_regex . '/U', $content, $matches );
		if ( ! empty( $matches ) ) {
			foreach ( $matches[1] as $key => $hash ) {
				$old_shortcode = $matches[0][ $key ];
				$new_shortcode = str_replace( $hash, $symbols[ $hash ], $old_shortcode );
				/* replace old symbol data with the hash */
				$content = str_replace( [ $old_shortcode, "thrv_symbol_{$hash}" ], [ $new_shortcode, "thrv_symbol_{$symbols[$hash]}" ], $content );
				$content = str_replace( "data-id=\"{$hash}\"", "data-id=\"{$symbols[$hash]}\"", $content );
			}
		}

		return $content;
	}
}
