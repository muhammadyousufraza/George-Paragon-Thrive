<?php

/**
 * Class TQB_Export_Step_Structure
 * - repare data of quiz structure to be exported
 */
class TQB_Export_Step_Structure extends TQB_Export_Step_Abstract {

	/**
	 * used to replace URL's in variations content
	 * so that this is replaced at import with site's URL
	 */
	const URL_PLACEHOLDER = '{tqb_source_url}';

	/**
	 * @var string step name
	 */
	protected $_name = 'structure';

	/**
	 * @var array with meta for quiz structure
	 */
	protected $structure = array();

	/**
	 * Gets structure meta and prepares it
	 *
	 * @return void
	 */
	protected function _prepare_data() {
		$this->structure = get_post_meta( $this->quiz->ID, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, true );
		$this->data      = $this->structure;
	}

	/**
	 * Writes a structure.json file
	 * - exports each item: splash/optin/results
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function execute() {

		parent::execute();

		if ( empty( $this->structure ) ) {
			return true;
		}

		$this->_export_structure_item( $this->structure['splash'] );
		$this->_export_structure_item( $this->structure['optin'] );
		$this->_export_structure_item( $this->structure['results'] );
		$this->_export_colors();

		return true;
	}

	/**
	 * @throws Exception
	 */
	private function _export_colors() {

		$data = array(
			'global_colours'   => get_option( apply_filters( 'tcb_global_colors_option_name', 'thrv_global_colours' ), array() ),
			'global_gradients' => get_option( apply_filters( 'tcb_global_gradients_option_name', 'thrv_global_gradients' ) ),
		);

		$this->write_data_to_file( $data, 'global_colours.json' );
	}

	/**
	 * Export Result page Dynamic Content
	 *
	 * @param array $variation
	 * @param int   $page_id
	 *
	 * @throws Exception
	 */
	private function _export_dynamic_content( &$variation, $page_id = 0 ) {
		$variation['dynamic_content'] = array();

		$variation_manager = new TQB_Variation_Manager( $this->quiz->ID, $page_id );
		$intervals         = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );

		foreach ( (array) $intervals as $interval ) {

			$interval['cache_impressions']               = 0;
			$interval['cache_optins']                    = 0;
			$interval['cache_optins_conversions']        = 0;
			$interval['cache_social_shares']             = 0;
			$interval['cache_social_shares_conversions'] = 0;

			$interval['content'] = $this->_export_content_files( $interval['content'] );

			foreach ( $interval['tcb_fields']['inline_css'] as $key => $inline_css ) {
				$interval['tcb_fields']['inline_css'][ $key ] = $this->_export_content_files( $inline_css );
			}

			$variation['dynamic_content'][] = $interval;
		}
	}

	/**
	 * Exports data for a structure item id which is a page id
	 *
	 * @param int $id page id: splash/optin/results
	 *
	 * @throws Exception
	 */
	protected function _export_structure_item( $id ) {

		$id = (int) $id;

		if ( ! $id ) {
			return;
		}

		$structure_item = new TQB_Export_Structure_Item( $id, $this->quiz->ID );

		$this->write_data_to_file( (array) $structure_item->get_post(), "{$id}_post.json" );
		$this->write_data_to_file( (array) $structure_item->get_test(), "{$id}_test.json" );

		$variations = (array) $structure_item->get_variations();

		foreach ( $variations as &$variation ) {
			$variation['content']                  = $this->_export_content_files( $variation['content'] );
			$variation['tcb_fields']['inline_css'] = $this->_export_content_files( $variation['tcb_fields']['inline_css'] );

			if ( in_array( $id, array( $this->structure['optin'], $this->structure['results'] ) ) ) {
				$this->_export_dynamic_content( $variation, $id );
			}
		}
		unset( $variation );

		$this->write_data_to_file( $variations, "{$id}_variations.json" );
	}

	/**
	 * @param $content
	 *
	 * @return string $content
	 * @throws Exception
	 */
	protected function _export_content_files( &$content ) {

		$audio_extensions = implode( '|', wp_get_audio_extensions() );
		$video_extensions = implode( '|', wp_get_video_extensions() );
		$site_url         = str_replace( array( 'http://', 'https://', '//' ), '', site_url() );
		$image_regexp     = '#(http://|https://|//)(' . preg_quote( $site_url, '#' ) . ')([^ "\']+?)(\.[png|gif|jpg|jpeg|' . $audio_extensions . '|' . $video_extensions . ']+)#is';

		if ( false === preg_match_all( $image_regexp, $content, $matches ) || empty( $matches[0] ) ) {
			return $content;
		}

		foreach ( $matches[0] as $index => $url ) {
			$filename   = basename( $url );
			$attachment = tqb_get_attachment_by_filename( $filename );

			if ( ! $attachment ) {

				/**
				 * Images might be added with a scaled url for a given size, case in which the name does not match the one from db
				 * Give it one more try, now we're removing the thumbnails details, such as 150x54
				 */
				$chunks     = explode( '.', $filename );
				$filename   = preg_replace( '/(-([^-]*[0-9a-zA-Z\._-]+.(png|PNG|gif|GIF|jp[e]?g|JP[E]?G)))+$/', '', $filename );
				$attachment = tqb_get_attachment_by_filename( $filename );
				$filename   = $filename . '.' . end( $chunks );

				if ( ! $attachment ) {
					continue;
				}
			}

			$item           = new stdClass();
			$item->id       = $attachment->ID;
			$item->filename = $filename;
			$copied         = $this->_prepare_file( $item );

			if ( $copied ) {
				$content = str_replace( $url, self::URL_PLACEHOLDER . $filename, $content );
			}
		}

		$content = str_replace( site_url(), self::URL_PLACEHOLDER, $content );

		return $content;
	}
}
