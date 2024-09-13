<?php
/**
 * FileName  class-tcb-symbol-element-abstract.php.
 *
 * @project  : thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Symbol_Element_Abstract
 */
abstract class TCB_Symbol_Element_Abstract extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Config for symbol
	 *
	 * @var string
	 */
	public $_cfg_code = '__CONFIG_post_symbol__';


	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [];
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_basic_label();
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html() {
		return $this->html_placeholder( sprintf( __( 'Insert %s', 'thrive-cb' ), $this->name() ) );
	}

	/**
	 * Returns the HTML placeholder for an element (contains a wrapper, and a button with icon + element name)
	 *
	 * @param string $title Optional. Defaults to the name of the current element
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		return tcb_template( 'elements/section-placeholder', array(
			'icon'       => $this->icon(),
			'id'         => 'thrive-' . $this->tag(),
			'class'      => 'thrv_symbol ' . 'thrv_' . $this->tag(),
			'title'      => $title,
			'extra_attr' => 'data-shortcode=thrive_' . $this->tag() . ' data-selector="' . '.thrv_symbol' . '.thrv_' . $this->tag() . '" data-tcb-elem-type="' . $this->tag() . '" data-element-name="' . esc_attr( $this->name() ) . '"',
		), true );
	}

	/**
	 * Make sure that a symbol title is provided and it's unique
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error
	 */
	public function ensure_title( $args ) {

		/* If the title is not set, just throw the error */
		if ( ! isset( $args['post_title'] ) ) {
			return new WP_Error( 'rest_cannot_create_post', __( 'Sorry, you are not allowed to create symbols without title' ), [ 'status' => 409 ] );
		}

		$post = get_page_by_title( $args['post_title'], OBJECT, TCB_Symbols_Post_Type::SYMBOL_POST_TYPE );

		if ( $post && $post->post_status !== 'trash' ) {
			return new WP_Error( 'rest_cannot_create_post', __( 'Sorry, you are not allowed to create global elements with the same title' ), [ 'status' => 409 ] );
		}

		return true;
	}

	/**
	 * Get all symbols
	 */
	public function get_all( $args, $is_localize = false ) {
		$result   = [];
		$defaults = [
			'post_type'      => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		];

		//get symbols from a specific category
		if ( isset( $args['category_name'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
					'field'    => 'slug',
					'terms'    => $args['category_name'],
				),
			);
			unset( $args['category_name'] );
		}

		//exclude symbols from categories
		if ( isset( $args['category__not_in'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $args['category__not_in'],
					'operator' => 'NOT IN',
				),
			);

			unset( $args['category__not_in'] );
		}

		$args = wp_parse_args( $args, $defaults );

		/**
		 * Add the possibility for other plugins to change the arguments for getting the symbols
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'tcb_get_symbols_args', $args );

		$symbols = get_posts( $args );

		if ( is_wp_error( $symbols ) ) {
			return new WP_Error( 'query_error', __( 'Error when retrieving symbols', 'thrive-cb' ) );
		}

		ob_start(); // some plugins echo output through shortcodes causing the ajax request to be misshaped
		foreach ( $symbols as $symbol ) {
			$result['local'][ $symbol->ID ] = $this->prepare_symbol( $symbol, $is_localize ) + [ 'is_local' => 1 ];
		}
		ob_end_clean();

		//if we have templates in the cloud get them
		//else just return the local results
		if ( $this->has_cloud_templates() ) {
			//get the templates from the cloud
			$cloud_items = $this->get_cloud_templates();

			if ( is_wp_error( $cloud_items ) ) {
				return $cloud_items;
			}

			$result['cloud'] = $cloud_items;
		} else {
			$result = isset( $result['local'] ) ? $result['local'] : $result;
		}
		/**
		 * Change the symbols array returned
		 *
		 * @param array $result
		 */
		$result = apply_filters( 'tcb_get_symbols_response', $result );

		return $result;
	}

	/**
	 * Prepare symbol before listing in TAR
	 *
	 * @param WP_Post $symbol
	 * @param bool $is_localize
	 *
	 * @return array
	 */
	public function prepare_symbol( $symbol, $is_localize = false ) {
		$symbol_data = [];

		if ( $symbol instanceof WP_Post && $symbol->post_status === 'publish' ) {
			$globals = get_post_meta( $symbol->ID, 'tve_globals', true );

			if ( empty( $globals ) ) {
				$globals = [];
			}

			$symbol_data = [
				'id'          => $symbol->ID,
				'post_title'  => $symbol->post_title,
				'config'      => $this->_get_symbol_config( $symbol ),
				'thumb'       => TCB_Utils::get_thumb_data( $symbol->ID, TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER, TCB_Utils::get_placeholder_data() ),
				'tve_globals' => $globals,
			];

			if ( ! $is_localize ) {
				$symbol_data = array_merge(
					$symbol_data,
					[
						'content' => TCB_Symbol_Template::render_content( [ 'id' => $symbol->ID ] ),
						'css'     => $this->get_symbol_css( $symbol->ID ),
					]
				);
			}
		}

		/**
		 * Change symbol data before showing it in the list
		 *
		 * @param array $symbol_data
		 */
		return apply_filters( 'tcb_symbol_data_before_return', $symbol_data );
	}

	/**
	 * Get css for a certain symbol
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function get_symbol_css( $id ) {
		$custom_css = get_post_meta( $id, 'tve_custom_css', true );

		/* If we want to change the symbol css just before is being inserted in the page */
		$custom_css = apply_filters( 'tcb_symbol_css_before', $custom_css, $id );

		return $custom_css;
	}

	/**
	 * Get config for symbol
	 *
	 * @param WP_Post $symbol
	 *
	 * @return string
	 */
	private function _get_symbol_config( $symbol ) {
		$encoded_config = tve_json_utf8_unslashit( json_encode( array( 'id' => ( string ) $symbol->ID ) ) );

		return $this->_cfg_code . $encoded_config . $this->_cfg_code;
	}

	/**
	 * Save a symbol changed from within the editor page
	 *
	 * @param array $symbol_data
	 *
	 * @return array|WP_Error
	 */
	public function edit_symbol( $symbol_data ) {

		if ( ! isset( $symbol_data['id'] ) ) {
			return new WP_Error( 'id_is_not_set', __( 'Missing symbol id', 'thrive-cb' ), [ 'status' => 500 ] );
		}

		/**
		 * Added some defaults
		 */
		$symbol_data = array_merge( [
			'has_icons' => 0,
			'class'     => '',
		], $symbol_data );

		/**
		 * update CSS text to reflect new symbol id ( replace cloud id placeholder with local id in css text)
		 */
		$symbol_data['css'] = str_replace( '|TEMPLATE_ID|', $symbol_data['id'], $symbol_data['css'] );

		update_post_meta( $symbol_data['id'], 'tve_updated_post', $symbol_data['content'] );
		update_post_meta( $symbol_data['id'], 'tve_custom_css', $symbol_data['css'] );
		update_post_meta( $symbol_data['id'], 'tve_globals', $symbol_data['tve_globals'] );
		update_post_meta( $symbol_data['id'], 'thrive_icon_pack', $symbol_data['has_icons'] );
		update_post_meta( $symbol_data['id'], 'tve_extra_class', $symbol_data['class'] );

		$symbol = get_post( $symbol_data['id'] );

		return [ 'symbol' => $symbol ];
	}

	/**
	 * Create symbol from content elements
	 *
	 * @param array $symbol_data
	 *
	 * @return array|int|WP_Error
	 */
	public function create_symbol( $symbol_data ) {
		$create_symbol_defaults = [
			'post_type'   => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
			'post_status' => 'publish',
		];

		$post_title         = str_replace( '\\', '', $symbol_data['symbol_title'] );
		$create_symbol_args = wp_parse_args( [ 'post_title' => $post_title ], $create_symbol_defaults );

		/**
		 * Add the possibility for other plugins to change the arguments for creating a symbol
		 *
		 * @param array $args
		 */
		$create_symbol_args = apply_filters( 'tcb_create_symbol_args', $create_symbol_args );

		/* Ensure that the title exists and it's unique */
		$check_title = $this->ensure_title( $create_symbol_args );

		if ( is_wp_error( $check_title ) ) {
			return $check_title;
		}

		$post_id = wp_insert_post( $create_symbol_args, true );

		//if something went wrong at insert, just return the error
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		/**
		 * After save actions: add to category and update meta ( html and css )
		 */
		$this->after_save( $post_id, $symbol_data );

		//return the newly created symbol for later use, if needed
		$symbol = get_post( $post_id );

		//prepare the symbol to be inserted in the page after a successful save
		return $this->prepare_symbol( $symbol );
	}

	/**
	 * Actions taken if a symbols is successfully created
	 *
	 * @param int   $post_id
	 * @param array $symbol_data
	 */
	public function after_save( $post_id, $symbol_data ) {
		/**
		 * Added some defaults
		 */
		$symbol_data = array_merge( [
			'has_icons' => 0,
		], $symbol_data );

		//if we are sending the category than assign the symbol to it
		$terms = isset( $symbol_data['term_id'] ) ? [ $symbol_data['term_id'] ] : [];
		wp_set_post_terms( $post_id, $terms, TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );

		/**
		 * update CSS text to reflect new symbol id ( replace cloud id placeholder with local id in css text)
		 */

		$symbol_data['css'] = isset( $symbol_data['css'] ) ? str_replace( '|TEMPLATE_ID|', $post_id, $symbol_data['css'] ) : '';

		$upload_dir = wp_upload_dir();

		/** If created from an existing symbol, replace the old ID with the new ID */
		if ( ! empty( $symbol_data['from_existing_id'] ) ) {
			$symbol_data['css'] = str_replace( 'symbol_' . $symbol_data['from_existing_id'], 'symbol_' . $post_id, $symbol_data['css'] );
			/**
			 * Copy the thumbnail from the original symbol to the new one.
			 */
			if ( empty( $upload_dir['error'] ) ) {
				$thumb_data = TCB_Utils::get_thumbnail_data_from_id( $symbol_data['from_existing_id'] );
				$thumb_path = trailingslashit( $upload_dir['basedir'] ) . TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER . '/' . $symbol_data['from_existing_id'] . '.png';
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				if ( $thumb_data && @is_readable( $thumb_path ) ) {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					$copied = @copy( $thumb_path, dirname( $thumb_path ) . '/' . $post_id . '.png' );
					if ( $copied ) {
						$thumb_data['url'] = str_replace( $symbol_data['from_existing_id'], $post_id, $thumb_data['url'] );
						TCB_Utils::save_thumbnail_data( $post_id, $thumb_data );
					}
				}
			}
		} elseif ( ! empty( $symbol_data['thumb'] ) ) {
			/* We also need to copy the thumbnail if the symbol was created base on a cloud template */
			$thumb_data = $symbol_data['thumb'];
			if ( ! empty( $thumb_data['url'] ) ) {
				$thumb_path = $thumb_data['url'];

				if ( strpos( $thumb_path, 'http' ) === false ) {
					$thumb_path = 'http:' . $thumb_path;
				}

				$destination = TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER . '/' . $post_id . '.png';
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$copied = @copy( $thumb_path, trailingslashit( $upload_dir['basedir'] ) . $destination );
				if ( $copied ) {
					$thumb_data['url'] = trailingslashit( $upload_dir['baseurl'] ) . $destination;
					TCB_Utils::save_thumbnail_data( $post_id, $thumb_data );
				}
			}
		}

		//if the insert was ok, update the meta attributes for the symbol
		update_post_meta( $post_id, 'tve_updated_post', $symbol_data['content'] );
		update_post_meta( $post_id, 'tve_custom_css', $symbol_data['css'] );
		update_post_meta( $post_id, 'tve_globals', $symbol_data['tve_globals'] );
		update_post_meta( $post_id, 'thrive_icon_pack', $symbol_data['has_icons'] );
		update_post_meta( $post_id, 'tve_extra_class', $symbol_data['class'] );

		\TCB\Lightspeed\Main::handle_optimize_saves( $post_id, $symbol_data );
	}

	/**
	 * Save css for elements with extra css. i.e call to action
	 * The css selectors are updated with proper thrv_symbol selectors
	 *
	 * @param array $data
	 *
	 * @return array|WP_Error
	 */
	public function save_extra_css( $data ) {

		if ( ! isset( $data['id'] ) ) {
			return new WP_Error( 'id_is_not_set', __( 'Missing symbol id', 'thrive-cb' ), [ 'status' => 500 ] );
		}

		update_post_meta( $data['id'], 'tve_custom_css', $data['css'] );

		$symbol = get_post( $data['id'] );

		return [ 'symbol' => $symbol ];
	}

	/**
	 * Generate preview for the symbol
	 *
	 * @param int    $post_id
	 * @param string $element_type
	 *
	 * @return array|WP_Error
	 */
	public function generate_preview( $post_id, $element_type = 'symbol' ) {

		add_filter( 'upload_dir', [ $this, 'upload_dir' ] );
		$preview_file = ! empty( $_FILES['preview_file'] ) ? $_FILES['preview_file'] : []; // phpcs:ignore
		$moved_file   = wp_handle_upload( $preview_file, [
			'action'                   => TCB_Editor_Ajax::ACTION,
			'unique_filename_callback' => [ $this, 'get_preview_filename' ],
		] );

		remove_filter( 'upload_dir', [ $this, 'upload_dir' ] );

		if ( empty( $moved_file['url'] ) ) {
			return new WP_Error( 'file_not_saved', __( 'The file could not be saved', 'thrive-cb' ), [ 'status' => 500 ] );
		}

		$new_width = in_array( $element_type, [ 'header', 'footer' ] ) ? 600 : 300;
		$preview   = wp_get_image_editor( $moved_file['file'] );
		if ( ! is_wp_error( $preview ) ) {
			$preview->resize( $new_width, null );
			$preview->save( $moved_file['file'] );
		}
		$editor = wp_get_image_editor( $moved_file['file'] );

		$editor->save( $moved_file['file'] );

		$dimensions = $editor->get_size();

		$thumb = [
			'url' => $moved_file['url'],
			'h'   => $dimensions['height'],
			'w'   => $dimensions['width'],
		];

		TCB_Utils::save_thumbnail_data( $post_id, $thumb );

		return $thumb;
	}

	/**
	 * Get the name for the thumbnail
	 * Prevent wordpress for creating a new file when it already exists in the uploads folder
	 *
	 * @param string $dir
	 * @param string $name
	 * @param string $ext
	 *
	 * @return mixed
	 */
	public function get_preview_filename( $dir, $name, $ext ) {
		return $name;
	}

	/**
	 * Get the upload directory where the file will be kept
	 *
	 * @param array $upload
	 *
	 * @return mixed
	 */
	public static function upload_dir( $upload ) {

		$sub_dir = '/' . TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER;

		$upload['path']   = $upload['basedir'] . $sub_dir;
		$upload['url']    = $upload['baseurl'] . $sub_dir;
		$upload['subdir'] = $sub_dir;

		return $upload;
	}

	/**
	 * @param array $args
	 *
	 * @return array|WP_Error
	 */
	public function get_cloud_templates( $args = [] ) {
		$result          = [];
		$cloud_templates = parent::get_cloud_templates( $args );

		if ( is_wp_error( $cloud_templates ) ) {
			return $cloud_templates;
		}

		$included_cloud_fields = isset( $args['included_cloud_fields'] ) ? $args['included_cloud_fields'] : [];

		//see how the cloud templates are returned when you have elements of that type or when you don't
		if ( ! empty( $cloud_templates ) ) {
			foreach ( (array) $cloud_templates as $cloud_template ) {
				$result[ $cloud_template['id'] ] = array(
					'id'         => $cloud_template['id'],
					'post_title' => $cloud_template['name'],
					'thumb'      => array(
						'url' => $cloud_template['thumb'],
						'w'   => isset( $cloud_template['thumb_size'] ) ? $cloud_template['thumb_size']['w'] : '',
						'h'   => isset( $cloud_template['thumb_size'] ) ? $cloud_template['thumb_size']['h'] : '',
					),
					'from_cloud' => 1,
				);
				foreach ( $included_cloud_fields as $field ) {
					if ( isset( $cloud_template[ $field ] ) ) {
						$result[ $cloud_template['id'] ][ $field ] = $cloud_template[ $field ];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Get path for symbol thumbnail
	 *
	 * @param int $old_path
	 * @param int $new_id
	 *
	 * @return bool
	 */
	public function copy_thumb( $old_path, $new_id ) {

		if ( strpos( $old_path, 'http' ) === false ) {
			$old_path = 'http:' . $old_path;
		}

		$upload_dir = wp_upload_dir();
		$new_path   = trailingslashit( $upload_dir['basedir'] ) . TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER . '/' . $new_id . '.png';

		return copy( $old_path, $new_path );
	}
}
