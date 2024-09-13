<?php

/**
 * Class TQB_Import_Structure_Item
 * - save variations/test/newpost for a new structure item/page
 */
class TQB_Import_Structure_Item {

	const GLOBAL_COLORS_PREFIX = '--tcb-color-';

	const GLOBAL_GRADIENT_PREFIX = '--tcb-gradient-';

	/**
	 * @var int
	 */
	protected $quiz_id;

	/**
	 * @var WP_Post
	 */
	protected $page;

	/**
	 * @var string where import file exists
	 */
	private $_import_path;

	/**
	 * @var string holds TAR global colors
	 */
	private $_global_colours = array(
		'global_colours'   => array(),
		'global_gradients' => array(),
	);

	/**
	 * An array with the allowed meta keys that a structure post can have
	 *
	 * @var array
	 */
	private $_allowed_post_meta = array( TQB_Post_meta::META_NAME_FOR_SKIP_OPTIN );

	/**
	 * Map for quiz results/categories
	 * - key is the old id
	 * - value is the new id
	 *
	 * @var array
	 */
	private $_results_map = array();

	/**
	 * TQB_Import_Structure_Item constructor.
	 *
	 * @param int $quiz_id
	 */
	public function __construct( $quiz_id ) {
		$this->quiz_id = (int) $quiz_id;
	}

	/**
	 * Results map data
	 *
	 * @param array $data
	 */
	public function set_results_map( $data ) {

		$this->_results_map = $data;
	}

	/**
	 * Where to read import files from
	 *
	 * @param string $path
	 */
	public function set_import_path( $path ) {

		$this->_import_path = $path;
	}

	/**
	 * Set global colors data
	 *
	 * @param array $data
	 */
	public function set_global_colours( $data ) {

		foreach ( $this->_global_colours as $key => $arr ) {
			if ( isset( $data->$key ) ) {
				$this->_global_colours[ $key ] = (array) $data->$key;
			}
		}
	}

	/**
	 * Save a new post/page structure item
	 *
	 * @param $data
	 *
	 * @return WP_Post|null
	 */
	public function save_post( $data ) {

		if ( empty( $data['post_title'] ) ) {
			return null;
		}

		$args = array(
			'post_title'  => $data['post_title'],
			'post_parent' => $data['post_parent'],
			'post_type'   => $data['post_type'],
			'post_status' => $data['post_status'],
		);

		$id = wp_insert_post( $args );

		/**
		 * Search for meta keys inside the post.json and update the new post with the specified metas
		 */
		if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {

			foreach ( $data['meta'] as $meta_key => $meta_value ) {
				if ( in_array( $meta_key, $this->_allowed_post_meta, true ) ) {
					update_post_meta( $id, $meta_key, $meta_value );
				}
			}
		}

		$this->page = get_post( $id );

		return $this->page;
	}

	/**
	 * Insert into DB variations
	 *
	 * @param array $variations
	 * @param int   $post_id page to which variations should be assigned
	 *
	 * @return array with old ids as keys and new ids as values
	 */
	public function save_variations( $variations, $post_id ) {

		$variations = (array) $variations;

		$mapping = array();

		/** @var TQB_Database $tqbdb */
		global $tqbdb;

		foreach ( $variations as $item ) {

			if ( empty( $item['id'] ) ) {
				continue;
			}

			$old_id      = $item['id'];
			$old_quiz_id = $item['quiz_id'];
			unset( $item['id'] );

			$item['quiz_id']       = $this->quiz_id;
			$item['date_added']    = date( 'Y-m-d H:i:s' );
			$item['date_modified'] = date( 'Y-m-d H:i:s' );
			$item['page_id']       = $post_id;

			/**
			 * Reset impressions and conversions values
			 */
			$item['cache_impressions']               = 0;
			$item['cache_optins']                    = 0;
			$item['cache_optins_conversions']        = 0;
			$item['cache_social_shares']             = 0;
			$item['cache_social_shares_conversions'] = 0;

			$this->_process_content( $item['tcb_fields']['inline_css'] );
			$this->_process_content( $item['content'], $old_quiz_id );
			$this->_update_global_colors( $item['tcb_fields']['inline_css'], self::GLOBAL_COLORS_PREFIX, 'global_colours' );
			$this->_update_global_colors( $item['tcb_fields']['inline_css'], self::GLOBAL_GRADIENT_PREFIX, 'global_gradients' );

			$new_variation_id   = $tqbdb->save_variation( $item );
			$mapping[ $old_id ] = $new_variation_id;

			if ( ! empty( $item['dynamic_content'] ) ) {
				$this->_import_dynamic_content( $item['dynamic_content'], $mapping, $post_id );
			}
		}

		return $mapping;
	}

	/**
	 * Import dynamic content variations items
	 *
	 * @param array $content_items
	 * @param array $variation_mapping
	 * @param int   $page_id
	 */
	private function _import_dynamic_content( $content_items, $variation_mapping, $page_id ) {

		if ( ! is_array( $content_items ) || ! is_array( $variation_mapping ) ) {
			return;
		}

		/** @var TQB_Database $tqbdb */
		global $tqbdb;

		foreach ( $content_items as $item ) {

			if ( ! isset( $variation_mapping[ $item['parent_id'] ] ) ) {
				continue;
			}

			unset( $item['id'] );

			$item['quiz_id']       = $this->quiz_id;
			$item['date_added']    = date( 'Y-m-d H:i:s' );
			$item['date_modified'] = date( 'Y-m-d H:i:s' );
			$item['parent_id']     = $variation_mapping[ $item['parent_id'] ];
			$item['page_id']       = (int) $page_id;

			if ( ! empty( $item['tcb_fields']['result_id'] ) ) {
				$item['tcb_fields']['result_id'] = isset( $this->_results_map[ $item['tcb_fields']['result_id'] ] )
					? $this->_results_map[ $item['tcb_fields']['result_id'] ] :
					'';
			}

			$this->_process_content( $item['content'] );

			foreach ( $item['tcb_fields']['inline_css'] as &$inline_css ) {
				$this->_process_content( $inline_css );
			}

			$tqbdb->save_variation( $item );
		}
	}

	/**
	 * Save a new test into db
	 *
	 * @param array $test model
	 * @param array $variations_mapping
	 *
	 * @return int|false test id
	 */
	public function save_test( $test, $variations_mapping ) {

		if ( empty( $test ) || empty( $variations_mapping ) ) {
			return false;
		}

		$test               = (array) $test;
		$variations_mapping = (array) $variations_mapping;

		$test_manager = new TQB_Test_Manager( null );

		unset( $test['id'] );

		$test['item_ids'] = array_values( $variations_mapping );
		$test['page_id']  = true === $this->page instanceof WP_Post ? $this->page->ID : null;

		return $test_manager->save_test( $test );
	}

	/**
	 * Parse the content for placeholder and replace it with current url site
	 * - files are imported as media item from import folder
	 *
	 * @param string $content
	 * @param int    $old_quiz_id
	 */
	protected function _process_content( &$content, $old_quiz_id = null ) {

		$audio_extensions = implode( '|', wp_get_audio_extensions() );
		$video_extensions = implode( '|', wp_get_video_extensions() );

		$image_regexp = '#(' . TQB_Export_Step_Structure::URL_PLACEHOLDER . ')([^ "\']+?\.[png|gif|jpg|jpeg|' . $audio_extensions . '|' . $video_extensions . ']+)#is';

		if ( $old_quiz_id ) {
			$content = str_replace( 'next_step_in_quiz_' . $old_quiz_id, 'next_step_in_quiz_' . $this->quiz_id, $content );
			$content = str_replace( 'restart_quiz_' . $old_quiz_id, 'restart_quiz_' . $this->quiz_id, $content );
		}

		if ( preg_match_all( $image_regexp, $content, $matches ) && ! empty( $matches[0] ) ) {

			foreach ( $matches[0] as $index => $placeholder ) {
				$filename   = $matches[2][ $index ];
				$_skip_args = array( 'old_quiz_id' => $old_quiz_id );

				if ( tqb_skip_file_import( $filename, $_skip_args ) ) {
					continue;
				}

				$source     = trailingslashit( $this->_import_path ) . $filename;
				$attachment = tqb_import_file( $source );

				if ( ! empty( $attachment['url'] ) ) {
					$content = str_replace( $placeholder, $attachment['url'], $content );
				}
			}
		}

		$content = str_replace( $old_quiz_id . '.png', $this->quiz_id . '.png', $content );
		$content = str_replace( TQB_Export_Step_Structure::URL_PLACEHOLDER, site_url(), $content );
	}

	/**
	 * Add new global colors in db
	 * Replace old color id with the new one in css
	 *
	 * @param string $css
	 * @param string $prefix
	 * @param string $key
	 */
	private function _update_global_colors( &$css, $prefix, $key ) {

		if ( ! isset( $this->_global_colours[ $key ] ) ) {
			return;
		}

		preg_match_all( '/' . $prefix . '[0-9]+/', $css, $matches );

		$matches       = array_map(
			function ( $item ) use ( $prefix ) {
				return str_replace( $prefix, '', $item );
			},
			$matches
		);
		$color_ids     = array_unique( $matches[0] );
		$option_name   = apply_filters( 'tcb_' . $key . '_option_name', 'thrv_' . $key );
		$global_colors = get_option( $option_name, array() );
		$data          = $this->_global_colours[ $key ];

		foreach ( $color_ids as $id ) {
			$color = array_filter(
				$data,
				function ( $item ) use ( $id ) {
					return (int) $item->id === (int) $id;
				}
			);

			$color = array_values( $color );
			$color = isset( $color[0] ) ? (array) $color[0] : array();

			if ( ! empty( $color ) ) {
				$color_id    = count( $global_colors );
				$color['id'] = $color_id;

				$css = str_replace( $prefix . $id, $prefix . $color_id, $css );

				$global_colors[] = $color;
			}
		}

		update_option( $option_name, $global_colors );
	}
}
