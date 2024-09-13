<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Utils
 */
class TCB_Utils {
	/**
	 * Wrap content in tag with id and/or class
	 *
	 * @param              $content
	 * @param string       $tag
	 * @param string       $id
	 * @param string|array $class
	 * @param array        $attr
	 *
	 * @return string
	 */
	public static function wrap_content( $content, $tag = '', $id = '', $class = '', $attr = [] ) {
		$class = is_array( $class ) ? trim( implode( ' ', $class ) ) : $class;

		if ( empty( $tag ) && ! ( empty( $id ) && empty( $class ) ) ) {
			$tag = 'div';
		}

		$attributes = '';
		foreach ( $attr as $key => $value ) {
			/* if the value is null, only add the key ( this is used for attributes that have no value, such as 'disabled', 'checked', etc ) */
			if ( is_null( $value ) ) {
				$attributes .= ' ' . $key;
			} else {
				$attributes .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}
		}

		if ( ! empty( $tag ) ) {
			$content = '<' . $tag . ( empty( $id ) ? '' : ' id="' . $id . '"' ) . ( empty( $class ) ? '' : ' class="' . $class . '"' ) . $attributes . '>' . $content . '</' . $tag . '>';
		}

		return $content;
	}

	/**
	 * Get all the banned post types for the post list/grid.
	 *
	 * @return mixed|void
	 */
	public static function get_banned_post_types() {
		$banned_types = [
			'attachment',
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'project',
			'et_pb_layout',
			'tcb_lightbox',
			'focus_area',
			'thrive_optin',
			'thrive_ad_group',
			'thrive_ad',
			'thrive_slideshow',
			'thrive_slide_item',
			'tve_lead_shortcode',
			'tve_lead_2s_lightbox',
			'tve_form_type',
			'tve_lead_group',
			'tve_lead_1c_signup',
			TCB_CT_POST_TYPE,
			'tcb_symbol',
			'td_nm_notification',
		];

		/**
		 * Filter that other plugins can hook to add / remove ban types from post grid
		 */
		return apply_filters( 'tcb_post_grid_banned_types', $banned_types );
	}

	/**
	 * Get the image source for the id.
	 * This is used in TTB, don't delete it
	 *
	 * @param        $image_id
	 * @param string $size
	 *
	 * @return mixed
	 */
	public static function get_image_src( $image_id, $size = 'full' ) {
		$image_info = wp_get_attachment_image_src( $image_id, $size );

		return empty( $image_info ) || empty( $image_info[0] ) ? '' : $image_info[0];
	}

	/**
	 * Get the pagination data that we want to localize.
	 *
	 * @return array
	 */
	public static function get_pagination_localized_data() {
		$localized_data = [];

		/* Apply a filter in case we want to add more pagination types from elsewhere. */
		$all_pagination_types = apply_filters( 'tcb_post_list_pagination_types', TCB_Pagination::$all_types );

		foreach ( $all_pagination_types as $type ) {
			$instance = tcb_pagination( $type );

			$localized_data[ $instance->get_type() ] = $instance->get_content();
		}

		/* we need this when we add new post lists to the page and they need a pagination element wrapper */
		$localized_data['pagination_wrapper'] = tcb_pagination( TCB_Pagination::NONE )->render();

		$localized_data['label_formats'] = array(
			'pages' => tcb_template( 'pagination/label-pages.php', null, true ),
			'posts' => tcb_template( 'pagination/label-posts.php', null, true ),
		);

		return $localized_data;
	}

	/**
	 * Adapt the pagination button component that inherits the button component by disabling some controls and adding new controls.
	 *
	 * @param $components
	 *
	 * @return mixed
	 */
	public static function get_pagination_button_config( $components ) {
		$components['pagination_button'] = $components['button'];
		unset( $components['button'] );

		$all_controls = array_keys( $components['pagination_button']['config'] );

		/* disable all the controls except the ones that we want to be enabled */
		$disabled_controls = array_diff( $all_controls, [ 'MasterColor', 'icon_side' ] );

		/* we have to add this manually */
		$disabled_controls = array_merge( $disabled_controls, [ '.tcb-button-link-container' ] );

		$components['pagination_button']['disabled_controls']     = array_values( $disabled_controls );
		$components['pagination_button']['config']['icon_layout'] = array(
			'config'  => array(
				'name'       => __( 'Button Layout', 'thrive-cb' ),
				'full-width' => true,
				'buttons'    => array(
					array(
						'value' => 'text',
						'text'  => __( 'Text Only', 'thrive-cb' ),
					),
					array(
						'value' => 'icon',
						'text'  => __( 'Icon Only', 'thrive-cb' ),
					),
					array(
						'value' => 'text_plus_icon',
						'text'  => __( 'Icon&Text', 'thrive-cb' ),
					),
				),
				/* default is defined here so it can be overwritten by elements that inherit */
				'default'    => 'text',
			),
			'extends' => 'ButtonGroup',
		);

		$components['animation']['disabled_controls'] = [ '.btn-inline.anim-link', '.btn-inline.anim-popup' ];

		/* add the root prefix in order to make this more specific than paragraph spacing settings from containers */
		$components['layout']['config']['MarginAndPadding']['css_prefix'] = tcb_selection_root() . ' ';

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment' ];

		$components['scroll']        = [ 'hidden' => true ];
		$components['responsive']    = [ 'hidden' => true ];
		$components['shared-styles'] = [ 'hidden' => true ];

		return $components;
	}

	/**
	 * Get the date/time format options for the wordpress date settings, and append a custom setting.
	 * Can return an associative array of key-value pairs, or multiple arrays of name/value.
	 *
	 * @param string $type
	 * @param bool   $key_value_pairs
	 *
	 * @return array
	 */
	public static function get_post_date_format_options( $type = 'date', $key_value_pairs = false ) {

		if ( $type === 'time' ) {
			/**
			 * Filters the default time formats.
			 *
			 * @param string[] Array of default time formats.
			 */
			$formats = array_unique( apply_filters( 'time_formats', array( __( 'g:i a', 'thrive-cb' ), 'g:i A', 'H:i' ) ) );
		} else {
			/**
			 * Filters the default date formats.
			 *
			 * @param string[] Array of default date formats.
			 */
			$formats = array_unique( apply_filters( 'date_formats', array( __( 'F j, Y', 'thrive-cb' ), 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );
		}

		$custom_option_name = __( 'Custom', 'thrive-cb' );

		if ( $key_value_pairs ) {
			foreach ( $formats as $format ) {
				$options[ $format ] = get_the_time( $format );
			}

			$options['custom'] = $custom_option_name;
		} else {
			$options = array_map( function ( $format ) {
				return array(
					'name'  => get_the_time( $format ),
					'value' => $format,
				);
			}, $formats );

			$options[] = [
				'name'  => $custom_option_name,
				'value' => '',
			];
		}

		return $options;
	}

	/**
	 * Get some inline shortcodes for the Pagination Label element.
	 *
	 * @return array
	 */
	public static function get_pagination_inline_shortcodes() {
		return array(
			'Post list pagination' => array(
				array(
					'option' => __( 'Current page number', 'thrive-cb' ),
					'value'  => 'tcb_pagination_current_page',
				),
				array(
					'option' => __( 'Total number of pages', 'thrive-cb' ),
					'value'  => 'tcb_pagination_total_pages',
				),
				array(
					'option' => __( 'Number of posts on this page', 'thrive-cb' ),
					'value'  => 'tcb_pagination_current_posts',
				),
				array(
					'option' => __( 'Total number of posts', 'thrive-cb' ),
					'value'  => 'tcb_pagination_total_posts',
				),
			),
		);
	}

	/**
	 * Get inline shortcodes for the Post List element.
	 *
	 * @return array
	 */
	public static function get_post_list_inline_shortcodes() {
		$date_format_options = static::get_post_date_format_options( 'date', true );
		$date_formats        = array_keys( $date_format_options );

		$time_format_options = static::get_post_date_format_options( 'time', true );

		return array(
			'Post'       => array(
				array(
					'name'   => __( 'Post title', 'thrive-cb' ),
					'option' => __( 'Post title', 'thrive-cb' ),
					'value'  => 'tcb_post_title',
					'input'  => array(
						'link'   => array(
							'type'  => 'checkbox',
							'label' => __( 'Link to post title', 'thrive-cb' ),
							'value' => true,
						),
						'target' => array(
							'type'       => 'checkbox',
							'label'      => __( 'Open in new tab', 'thrive-cb' ),
							'value'      => false,
							'disable_br' => true,
						),
						'rel'    => array(
							'type'  => 'checkbox',
							'label' => __( 'No follow', 'thrive-cb' ),
							'value' => false,
						),
					),
				),
				array(
					'name'   => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
					'option' => __( 'Post date', 'thrive-cb' ),
					'value'  => 'tcb_post_published_date',
					'input'  => array(
						'type'               => array(
							'type'  => 'select',
							'label' => __( 'Display', 'thrive-cb' ),
							'value' => array(
								'published' => __( 'Published date', 'thrive-cb' ),
								'modified'  => __( 'Modified date', 'thrive-cb' ),
							),
						),
						'date-format-select' => array(
							'type'  => 'select',
							'label' => __( 'Date format', 'thrive-cb' ),
							'value' => $date_format_options,
						),
						'date-format'        => array(
							'type'  => 'input',
							'label' => __( 'Format string', 'thrive-cb' ),
							'value' => $date_formats[0],
						),
						'show-time'          => array(
							'type'  => 'checkbox',
							'label' => __( 'Show time?', 'thrive-cb' ),
							'value' => false,
						),
						'time-format-select' => array(
							'type'  => 'select',
							'label' => __( 'Time format', 'thrive-cb' ),
							'value' => $time_format_options,
						),
						'time-format'        => array(
							'type'  => 'input',
							'label' => __( 'Format string', 'thrive-cb' ),
							'value' => '',
						),
						'link'               => array(
							'type'  => 'checkbox',
							'label' => __( 'Link to archive', 'thrive-cb' ),
							'value' => false,
						),
						'target'             => array(
							'type'       => 'checkbox',
							'label'      => __( 'Open in new tab', 'thrive-cb' ),
							'value'      => true,
							'disable_br' => true,
						),
						'rel'                => array(
							'type'  => 'checkbox',
							'label' => __( 'No follow', 'thrive-cb' ),
							'value' => false,
						),
					),
				),
				array(
					'name'   => __( 'Author name', 'thrive-cb' ),
					'option' => __( 'Author name', 'thrive-cb' ),
					'value'  => 'tcb_post_author_name',
					'input'  => array(
						'link'   => array(
							'type'  => 'checkbox',
							'label' => __( 'Link to author profile', 'thrive-cb' ),
							'value' => false,
						),
						'target' => array(
							'type'       => 'checkbox',
							'label'      => __( 'Open in new tab', 'thrive-cb' ),
							'value'      => true,
							'disable_br' => true,
						),
						'rel'    => array(
							'type'  => 'checkbox',
							'label' => __( 'No follow', 'thrive-cb' ),
							'value' => false,
						),
					),
				),
				array(
					'name'   => __( 'Author role', 'thrive-cb' ),
					'option' => __( 'Author role', 'thrive-cb' ),
					'value'  => 'tcb_post_author_role',
				),
				array(
					'name'   => __( 'Author bio', 'thrive-cb' ),
					'option' => __( 'Author bio', 'thrive-cb' ),
					'value'  => 'tcb_post_author_bio',
				),
				array(
					'name'   => 24,
					'option' => __( 'Number of comments', 'thrive-cb' ),
					'value'  => 'tcb_post_comments_number',
				),
			),
			'Taxonomies' => array(
				array(
					'name'   => __( 'Category-1, Category-2, Category-3', 'thrive-cb' ),
					'option' => __( 'List of categories', 'thrive-cb' ),
					'value'  => 'tcb_post_categories',
					'input'  => array(
						'link'   => array(
							'type'  => 'checkbox',
							'label' => __( 'Link to archive', 'thrive-cb' ),
							'value' => true,
						),
						'target' => array(
							'type'       => 'checkbox',
							'label'      => __( 'Open in new tab', 'thrive-cb' ),
							'value'      => false,
							'disable_br' => true,
						),
						'rel'    => array(
							'type'  => 'checkbox',
							'label' => __( 'No follow', 'thrive-cb' ),
							'value' => false,
						),
					),
				),
				array(
					'name'   => __( 'Tag-1, Tag-2, Tag-3', 'thrive-cb' ),
					'option' => __( 'List of tags', 'thrive-cb' ),
					'value'  => 'tcb_post_tags',
					'input'  => array(
						'default' => array(
							'type'  => 'input',
							'label' => __( 'Default value', 'thrive-cb' ),
							'value' => '',
						),
						'link'    => array(
							'type'  => 'checkbox',
							'label' => __( 'Link to archive', 'thrive-cb' ),
							'value' => true,
						),
						'target'  => array(
							'type'       => 'checkbox',
							'label'      => __( 'Open in new tab', 'thrive-cb' ),
							'value'      => false,
							'disable_br' => true,
						),
						'rel'     => array(
							'type'  => 'checkbox',
							'label' => __( 'No follow', 'thrive-cb' ),
							'value' => false,
						),
					),
				),
			),
		);
	}

	/**
	 * Return the post formats supported by the current theme.
	 *
	 * @return array|mixed
	 */
	public static function get_supported_post_formats() {
		$post_formats = [];

		if ( current_theme_supports( 'post-formats' ) ) {
			$post_formats = get_theme_support( 'post-formats' );

			if ( is_array( $post_formats[0] ) ) {
				$post_formats = $post_formats[0];
			}
		}

		return $post_formats;
	}

	/**
	 * Return the preview URL for this post( symbol or anything that has post meta ) ID along with height/width.
	 * If no URL is found, this can return a placeholder, if one was provided through the parameter.
	 *
	 * @param int    $post_id
	 * @param string $sub_path
	 * @param array  $placeholder_data
	 *
	 * @return array|mixed
	 */
	public static function get_thumb_data( $post_id, $sub_path, $placeholder_data = [] ) {

		$upload_dir = wp_upload_dir();
		$path       = $sub_path . '/' . $post_id . '.png';

		$thumb_path = static::get_uploads_path( $path, $upload_dir );
		$thumb_url  = trailingslashit( $upload_dir['baseurl'] ) . $path;

		/* check if we have preview data in the post meta */
		$thumb_data = static::get_thumbnail_data_from_id( $post_id );

		/* if the post meta is empty, look inside the file and get the data directly from the it */
		if ( empty( $thumb_data['url'] ) ) {
			if ( file_exists( $thumb_path ) && ini_get( 'allow_url_fopen' ) ) {
				list( $width, $height ) = getimagesize( $thumb_path );

				$thumb_data = [
					'url' => $thumb_url,
					'h'   => $height,
					'w'   => $width,
				];
			} else {
				/* if no file is found and no placeholder is provided, return all the values set to blank */
				if ( empty( $placeholder_data ) ) {
					$thumb_data = [
						'url' => '',
						'h'   => '',
						'w'   => '',
					];
				} else {
					/* if a placeholder is provided, use it */
					$thumb_data = $placeholder_data;
				}
			}
		}

		return $thumb_data;
	}

	/**
	 * Return the uploads physical path.
	 * Things can be appended to it by providing something in $path.
	 *
	 * @param string $path
	 * @param array  $upload_dir
	 *
	 * @return string
	 */
	public static function get_uploads_path( $path = '', $upload_dir = [] ) {
		if ( empty( $upload_dir ) ) {
			$upload_dir = wp_upload_dir();
		}

		return trailingslashit( $upload_dir['basedir'] ) . $path;
	}

	/**
	 * Retrieve the image metadata for the provided post ID.
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function get_thumbnail_data_from_id( $post_id ) {
		return get_post_meta( $post_id, TCB_THUMBNAIL_META_KEY, true );
	}

	/**
	 * Set the image metadata for the provided post ID.
	 *
	 * @param $post_id
	 * @param $thumb_data
	 */
	public static function save_thumbnail_data( $post_id, $thumb_data ) {
		update_post_meta( $post_id, TCB_THUMBNAIL_META_KEY, $thumb_data );
	}

	/**
	 * Check if we're inside the editor and filter the result.
	 *
	 * @param boolean $ajax_check
	 *
	 * @return mixed|void
	 */
	public static function in_editor_render( $ajax_check = false ) {
		return apply_filters( 'tcb_in_editor_render', is_editor_page_raw( $ajax_check ) );
	}

	/**
	 * Check if we're in a REST request.
	 *
	 * @return bool
	 */
	public static function is_rest() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	/**
	 * Returns the content at the given path
	 * A more simpler and general version of tcb_template()
	 *
	 * @param string $full_path
	 *
	 * @return string
	 */
	public static function return_part( $full_path ) {
		$content = '';

		if ( file_exists( $full_path ) ) {
			ob_start();
			include $full_path;
			$content = trim( ob_get_contents() );
			ob_end_clean();
		}

		return $content;
	}

	/**
	 * parse and combine all font links => ensure a single request is sent out for a font
	 * Combines
	 *
	 * @import url("//fonts.googleapis.com/css?family=Muli:400,800,900,600&subset=latin");
	 * +
	 * @import url("//fonts.googleapis.com/css?family=Muli:400,900,600&subset=latin");
	 * into:
	 * @import url("//fonts.googleapis.com/css?family=Muli:400,600,800,900&subset=latin");
	 *
	 * @param array  $imports
	 * @param string $return return type. Can be 'link' or 'import'
	 *
	 * @return array array of unique @import statements
	 */
	public static function merge_google_fonts( $imports, $return = 'import' ) {
		return array_map( function ( $font_data ) use ( $return ) {
			return TCB_Utils::build_font_string( $font_data, $return );
		}, array_values( static::parse_css_imports( $imports ) ) );
	}

	/**
	 * Parses an array of CSS import statements and structures the data based on family and weights
	 * Merges all weights for a family
	 *
	 * @param array $imports
	 *
	 * @return array
	 */
	public static function parse_css_imports( $imports ) {
		$data = [];
		foreach ( array_unique( $imports ) as $import ) {
			$font = static::parse_css_import( $import );
			if ( ! isset( $data[ $font['family'] ] ) ) {
				$data[ $font['family'] ] = $font;
			} else {
				$data[ $font['family'] ]['weights'] += $font['weights'];
			}
		}

		return $data;
	}

	/**
	 * Parses an `@import` statement and generates an array containing useful data about the font (family, weights, subset etc)
	 * !!IMPORTANT: this only treats google fonts!!
	 *
	 * @param string $import import string
	 *
	 * @return array
	 */
	public static function parse_css_import( $import ) {
		$data   = [
			'base_url' => '',
			'family'   => '',
			'weights'  => [],
			'query'    => [],
		];
		$import = str_replace( array( '"', "'", '@import url(', ')', ';' ), '', $import );

		$result = parse_url( $import );
		if ( $result ) {
			$data['base_url'] = ( isset( $result['host'] ) ? 'https://' . $result['host'] : '' ) . $result['path'];

			if ( ! empty( $result['query'] ) ) {
				parse_str( $result['query'], $query );

				list( $family, $weights ) = explode( ':', $query['family'] );
				unset( $query['family'] );

				$data['family'] = $family;
				/* hold weights as keys, so it's less expensive to get unique weights */
				$data['weights'] = array_flip( array_filter( explode( ',', $weights ), function ( $weight ) {
					/**
					 * Preserve italic weights
					 */
					if ( substr( $weight, - 1 ) === 'i' ) {
						$weight = substr( $weight, 0, - 1 );
					}

					return filter_var( $weight, FILTER_VALIDATE_INT );
				} ) );
				$data['query']   = $query;
			}
		}

		return $data;
	}

	/**
	 * Builds an @import CSS rule based on $font_data
	 *
	 * @param array  $font_data   array in the form returned by self::parse_css_import
	 * @param string $return_type Controls the output. Can be 'link'/'url' or 'import'
	 *
	 * @return string
	 */
	public static function build_font_string( $font_data, $return_type = 'import' ) {
		if ( empty( $font_data ) ) {
			return '';
		}
		$font_data['query'] = array( 'family' => $font_data['family'] . ':' . implode( ',', array_keys( $font_data['weights'] ) ) ) + $font_data['query'];

		/**
		 * https://web.dev/font-display/
		 * this ensures font is readable while external gfonts are loaded
		 *
		 */
		$font_data['query']['display'] = 'swap';

		$result = $font_data['base_url'] . '?' . rawurldecode( http_build_query( $font_data['query'], '', '&' ) );

		if ( $return_type === 'import' ) {
			$result = '@import url("' . $result . '");';
		}

		return $result;
	}

	/**
	 * Create a rest nonce for our ajax requests
	 *
	 * @return mixed|void
	 */
	public static function create_nonce() {
		return wp_create_nonce( 'wp_rest' );
	}

	/**
	 * Mark that we started processing custom CSS
	 */
	public static function before_custom_css_processing() {
		$GLOBALS[ TVE_IS_PROCESSING_CUSTOM_CSS ] = true;
	}

	/**
	 * Mark that we finished processing custom CSS
	 */
	public static function after_custom_css_processing() {
		$GLOBALS[ TVE_IS_PROCESSING_CUSTOM_CSS ] = false;
	}

	/**
	 * Check if we're currently processing CSS.
	 * Useful in shortcode handlers in case you want to see if the shortcode is called from CSS or HTML.
	 *
	 * @return bool
	 */
	public static function is_processing_custom_css() {
		return ! empty( $GLOBALS[ TVE_IS_PROCESSING_CUSTOM_CSS ] );
	}

	/**
	 * If we're in a debug environment, we're not minifying JS files.
	 * The constant should be defined in the wp-config.php file
	 *
	 * @return string
	 */
	public static function get_js_suffix() {
		return tve_dash_is_debug_on() ? '.js' : '.min.js';
	}

	/**
	 * Shortcut function that will output json encoded data or return it as it is based on the second parameter.
	 *
	 * @param mixed $data
	 * @param bool  $output if true, it will use WordPress's wp_send_json to output data
	 *
	 * @return mixed
	 */
	public static function maybe_send_json( $data, $output = true ) {
		if ( $output ) {
			wp_send_json( $data );
		} else {
			return $data;
		}
	}

	/**
	 * Write file using wordpress functionality
	 *
	 * @param        $file
	 * @param string $content
	 * @param int    $mode
	 *
	 * @return boolean
	 */
	public static function write_file( $file, $content = '', $mode = 0644 ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		/* make sure that the FS_METHOD defaults to 'direct' so we use WP_Filesystem_direct in order to avoid the FTP implementations of put_contents */
		defined( 'FS_METHOD' ) || define( 'FS_METHOD', 'direct' );

		global $wp_filesystem;

		return WP_Filesystem() && $wp_filesystem->put_contents( $file, $content, $mode );
	}

	/**
	 * Restore some fields of the $_POST data that have been previously replaced from javascript
	 * Wordfence blocks certain strings in POST - these are replaced from javascript before sending the ajax request to some equivalents
	 * Currently, this method handles:
	 * - <svg and </svg> ( these are replaced from javascript into <_wafsvg_ and </_wafsvg )
	 */
	public static function restore_post_waf_content() {
		/**
		 * Filter the list of fields that should be processed
		 *
		 * @param array $field_list
		 */
		$field_list = apply_filters( 'tcb_waf_fields_restore', [
			'template',
			'template_content',
			'tve_content',
			'tve_stripped_content',
			'symbol_content',
		] );
		/* map of search_string => replace_string */
		$replace_map = [
			'_wafsvg_' => 'svg',
		];
		$search      = array_keys( $replace_map );
		$replace     = array_values( $replace_map );

		foreach ( $field_list as $field ) {
			if ( ! empty( $_POST[ $field ] ) && is_string( $_POST[ $field ] ) ) {
				$_POST[ $field ] = str_replace( $search, $replace, $_POST[ $field ] );
			}
		}
	}

	/**
	 * @return string[]
	 */
	public static function get_api_list_with_tag_support() {
		$apis_that_support_tags = [ 'activecampaign', 'aweber', 'convertkit', 'drip', 'klicktipp', 'mailchimp', 'sendlane', 'zapier' ];

		return apply_filters( 'tcb_lead_generation_apis_with_tag_support', $apis_that_support_tags );
	}

	/**
	 * Get some values from the queried object.
	 *
	 * @return array
	 */
	public static function get_filtered_queried_object() {
		$queried_object = get_queried_object();
		$qo             = [];

		if ( $queried_object ) {
			/* when we're using demo content the $queried_object can get weird, so check stuff before using it */
			if (
				property_exists( $queried_object, 'data' )
				&& property_exists( $queried_object->data, 'ID' )
				&&
				! empty( $queried_object->data->ID )
			) {
				$qo ['ID'] = $queried_object->data->ID;
			} elseif ( property_exists( $queried_object, 'ID' ) && ! empty( $queried_object->ID ) ) {
				$qo ['ID'] = $queried_object->ID;
			}

			/* only keep the values for the specified keys */
			$qo = array_filter( (array) $queried_object, function ( $key ) {
				return in_array( $key, [ 'post_author', 'taxonomy', 'term_id', 'ID' ] );
			}, ARRAY_FILTER_USE_KEY );
		}

		/**
		 * Filters the queried object needed in the frontend localization (used in generating "Edit with TTB" links that will show the same content)
		 *
		 * @param array $qo associative array the should have at least an `ID` key
		 */
		return apply_filters( 'tcb_frontend_queried_object', $qo );
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_url() {
		return tve_editor_url( 'admin/assets/images/no-template-preview.jpg' );
	}

	/**
	 * Get placeholder information for the template preview
	 *
	 * @return array
	 */
	public static function get_placeholder_data() {
		return [
			'url' => static::get_placeholder_url(),
			/* hardcoded sizes taken from 'no-template-preview.jpg' */
			'h'   => '120',
			'w'   => '250',
		];
	}

	/**
	 * Used to hide elements on certain post types, called in hide() method from element classes
	 *
	 * @return bool
	 */
	public static function should_hide_element_on_blacklisted_post_types() {
		$blacklisted_post_types = static::get_banned_post_types();

		$blacklisted_post_types = array_diff( $blacklisted_post_types, [ 'tcb_symbol' ] );

		return in_array( get_post_type( get_the_ID() ), $blacklisted_post_types );
	}
}
