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
 * Class Thrive_Transfer_Colors
 */
class Thrive_Transfer_Colors extends Thrive_Transfer_Base {

	/**
	 * Regex to identify the global colors in the content
	 */
	const COLOR_REGEXP = '/--tcb-color-(\d+)\)/';

	/**
	 * Global color option name in the db
	 */
	const COLOR_OPTION = 'thrv_global_colours';

	/**
	 * File in which the global colors are saved
	 *
	 * @var string
	 */
	public static $file = 'colors.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	public $tag = 'colors';

	/**
	 * Read global colors from content
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function read( &$content ) {
		if ( preg_match_all( self::COLOR_REGEXP, $content, $matches ) && Thrive_Utils::is_end_user_site() ) {

			$global_colors = get_option( self::COLOR_OPTION, [] );

			foreach ( $matches[1] as $color_id ) {
				foreach ( $global_colors as $existing_color ) {
					if ( $existing_color['id'] == $color_id ) {
						$this->items[ $color_id ] = $existing_color;

						$content = str_replace( '(' . Thrive_Transfer_Utils::COLOR_PREFIX . $color_id . ')', '(' . Thrive_Transfer_Utils::COLOR_PREFIX . md5( $color_id ) . ')', $content );
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Add Global Colors to archive
	 */
	public function add() {
		$colors = $this->archive_data['colors'];

		foreach ( $this->items as $id => $item ) {
			if ( empty( $colors[ $id ] ) ) {
				$colors[ $id ] = $item;
			}
		}

		$this->archive_data['colors'] = $colors;
	}

	/**
	 * Import global colors taken from the archive
	 */
	public function import() {
		$imported_colors_map = [];
		if ( ! empty( $this->data ) ) {
			$global_colors = get_option( self::COLOR_OPTION, [] );
			foreach ( $this->data as $key => $imported_color ) {
				$exists = false;
				foreach ( $global_colors as $existing_color ) {
					if ( ! empty( $existing_color['color'] ) && $existing_color['color'] === $imported_color['color'] ) {
						$imported_colors_map[ $key ] = $existing_color['id'];
						$exists                      = true;
					}
				}
				if ( ! $exists ) {
					$imported_colors_map[ $key ] = $imported_color['id'] = count( $global_colors );

					$global_colors[] = $imported_color;
				}
			}
			update_option( self::COLOR_OPTION, $global_colors );
		}

		$this->archive_data['colors'] = $imported_colors_map;
	}
}
