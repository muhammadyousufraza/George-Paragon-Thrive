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
 * Class Thrive_Transfer_Typography
 */
class Thrive_Transfer_Typography extends Thrive_Transfer_Base {
	/**
	 * Json filename where to keep the data for the typography
	 *
	 * @var string
	 */
	public static $file = 'typography.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'typography';

	/**
	 * Read typographies for export
	 *
	 * @param array $typographies
	 */
	public function read( $typographies ) {

		$typographies = array_map( function ( $t ) {
			return $t->export();
		}, $typographies );

		$content = json_encode( $typographies );

		$this->controller
			->process_global_gradients( $content )
			->process_global_colors( $content )
			->process_images( $content )
			->process_global_gradients( $content );

		$this->archive_data['typography'] = json_decode( $content, true );
	}

	/**
	 * Import typography
	 *
	 * @throws Exception
	 */
	public function import() {

		if ( ! empty( $this->data ) ) {
			$this->controller
				->import_images()
				->import_global_gradients()
				->import_global_colors()
				->import_global_gradients();

			$this->save();
		}
	}

	/**
	 * Save typography
	 */
	public function save() {
		$this->data = Thrive_Transfer_Utils::prepare_content( $this->data, $this->archive_data->get_data() );

		foreach ( $this->data as $typography_item ) {
			unset( $typography_item['ID'] );
			$typography_item['post_status'] = 'publish';
			$typography_item['post_type']   = THRIVE_TYPOGRAPHY;
			$typography_id                  = wp_insert_post( $typography_item );

			/* so we can reset to this. */
			update_post_meta( $typography_id, Thrive_Typography::META_DEFAULT_STYLE, $typography_item['meta_input']['style'] );

			if ( $typography_id ) {
				wp_set_object_terms( $typography_id, $this->archive_data['skin_id'], SKIN_TAXONOMY );
			}
		}
	}
}

