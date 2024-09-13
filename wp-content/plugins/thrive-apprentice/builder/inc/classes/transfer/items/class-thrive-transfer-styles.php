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
 * Class Thrive_Transfer_Styles
 */
class Thrive_Transfer_Styles extends Thrive_Transfer_Base {

	/**
	 * Json filename where to keep the data for the styles
	 *
	 * @var string
	 */
	public static $file = 'styles.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'styles';

	/**
	 * Styles map used at import in order to replace the identifiers in html
	 *
	 * @var array
	 */
	private $map = [];

	/**
	 * Read global styles
	 *
	 * @param array $styles
	 *
	 * @return $this
	 */
	public function read( $styles ) {

		$content = json_encode( $styles );

		$this->controller->process_global_colors( $content )
		                 ->process_global_gradients( $content );

		$this->items = json_decode( $content, true );

		return $this;
	}

	/**
	 * Add styles to archive data
	 */
	public function add() {
		$this->archive_data['styles'] = $this->items;
	}

	/**
	 * Import global styles
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import() {

		if ( ! empty( $this->data ) ) {
			$this->controller->import_global_colors()
			                 ->import_global_gradients();
			$this->save();
		}

		$this->archive_data['styles'] = $this->map;

		return $this;
	}

	/**
	 * Save global styles
	 */
	public function save() {
		$this->data = Thrive_Transfer_Utils::replace_global_styles_data( $this->archive_data['colors'], $this->archive_data['gradients'], $this->data );

		$skin_tag = thrive_skin( $this->archive_data['skin_id'] )->get_tag();

		foreach ( $this->data as $global_style_option_name => $items ) {
			/* Take the styles that already exist in the db */
			$global_styles = get_option( $global_style_option_name, [] );

			$intersect = array_intersect_key( $items, $global_styles );

			/* If we have already imported styles, we change their identifier to not overwrite the previous one imported*/
			foreach ( $intersect as $identifier => $value ) {
				unset( $items[ $identifier ] );

				$new_identifier = base_convert( time(), 10, 36 ); // the same way the identifier for a style is generated in javascript

				/* We also need to replace the identifier in the style css*/
				if ( ! empty( $value['css'] ) ) {
					foreach ( $value['css'] as $css_media => $css ) {
						$value['css'][ $css_media ] = str_replace( $identifier, $new_identifier, $css );
					}
				}

				$items[ $new_identifier ] = $value;
				$this->map[ $identifier ] = $new_identifier;
			}

			/* Set the skin tag on each style */
			foreach ( $items as $key => $item ) {
				$items[ $key ]['skin_tag'] = $skin_tag;
			}

			/* Merge them with the ones from the archive. */
			$global_styles = array_merge( $global_styles, $items );

			/* Update the db with the new styles */
			update_option( $global_style_option_name, $global_styles );
		}
	}

}

