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
 * Class Thrive_Utils
 */
class Thrive_Utils {

	/* these cloud template element types should also be available in TAr Light */
	const UNLOCKED_CT_TYPES
		= [
			'post_list',
			'post_list_featured',
		];

	/**
	 * The keys generated as suffixes after saving attributes with .mediaAttr() in the JS editor
	 */
	const RESPONSIVE_DEVICE_KEYS = [ 'd', 't', 'm' ];

	/**
	 * Check if the current page is an inner frame for architect
	 *
	 * @return bool
	 */
	public static function is_inner_frame() {
		/**
		 * Allow theme scripts to be included in architect for different post types
		 */
		$allowed_post_types = apply_filters( 'thrive_theme_scripts_post_types', [ TCB_Symbols_Post_Type::SYMBOL_POST_TYPE ] );

		/**
		 * 1. Make sure we're logged in.
		 * 2. Check if we're inside the architect editor for a post type where we want to use theme builder elements
		 * 3. Check if we are on a template edit page and not on preview
		 */
		return is_user_logged_in() && (
				( isset( $_GET[ TVE_EDITOR_FLAG ] ) && in_array( get_post_type(), $allowed_post_types, true ) ) ||
				( isset( $_GET[ THRIVE_THEME_FLAG ] ) && empty( $_GET[ THRIVE_PREVIEW_FLAG ] ) )
			);
	}

	/**
	 * Check we're on the preview page of a template while being logged in
	 *
	 * @return bool
	 */
	public static function is_preview() {
		return is_user_logged_in() && ( isset( $_GET[ THRIVE_PREVIEW_FLAG ] ) || isset( $_GET['preview'] ) );
	}

	/**
	 * Return template id when we're inside the inner_frame
	 *
	 * @return int
	 */
	public static function inner_frame_id() {
		return isset( $_GET[ THRIVE_THEME_FLAG ] ) ? (int) $_GET[ THRIVE_THEME_FLAG ] : 0;
	}

	/**
	 * Check if the current page is a theme template
	 *
	 * @return bool
	 */
	public static function is_theme_template() {
		$is_theme_template = get_post_type() === THRIVE_TEMPLATE;

		/* Also handles ajax requests (both admin-ajax and REST requests) */
		if ( ! empty( $_REQUEST['is_theme_template'] ) && ( wp_doing_ajax() || TCB_Utils::is_rest() ) ) {
			$is_theme_template = true;
		}

		return $is_theme_template;
	}

	/**
	 * Check if the template was loaded from TAR
	 *
	 * @return bool|mixed
	 */
	public static function from_tar() {
		return empty( $_GET['from_tar'] ) ? false : $_GET['from_tar'];
	}

	/**
	 * Check if the current page is a theme typography
	 *
	 * @return bool
	 */
	public static function is_theme_typography() {
		return get_post_type() === THRIVE_TYPOGRAPHY;
	}

	/**
	 * Checks if this site instance is end user or is the builder website
	 *
	 * Makes magic happen in relation to Themes Builder Website
	 *
	 * @return bool
	 */
	public static function is_end_user_site() {
		return apply_filters( 'tcb_theme_is_end_user_site', true );
	}

	/**
	 * Returns true if skin style panel should show
	 *
	 * @return bool
	 */
	public static function has_skin_style_panel() {
		return ( static::is_theme_template() || ( thrive_post()->is_landing_page() && ! empty( thrive_post()->get_meta( 'theme_skin_tag' ) ) ) ) &&
		       static::is_end_user_site() && ! empty( thrive_skin()->get_palettes() );
	}

	/**
	 * Check to see if we can show theme elements in different layouts
	 *
	 * @return mixed
	 */
	public static function allow_theme_scripts() {
		/**
		 * Add the possibility to add theme elements for other post types beside theme templates
		 */
		$post_types = [ THRIVE_TEMPLATE, TCB_Symbols_Post_Type::SYMBOL_POST_TYPE ];
		$is_allowed = in_array( get_post_type(), $post_types );

		/**
		 * Overwrite this if necessary
		 */
		return apply_filters( 'allow_theme_scripts', $is_allowed );
	}

	/**
	 * Return a list of all categories
	 *
	 * @return array
	 */
	public static function get_categories() {

		$all = get_terms( [
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
		] );

		$categories = [];
		foreach ( $all as $category ) {
			$categories[ $category->term_id ] = $category->name;
		}

		return $categories;
	}

	/**
	 * Return a list of all post types except the ignored ones
	 *
	 * @return array
	 */
	public static function get_post_types() {

		$ignored_types = apply_filters( 'thrive_ignored_post_types', [
			'attachment',
			'tcb_lightbox',
			THRIVE_TEMPLATE,
			'tcb_symbol',
		] );

		$all = get_post_types( [ 'public' => true ] );

		$post_types = [];

		foreach ( $all as $key => $post_type ) {
			if ( in_array( $key, $ignored_types, true ) ) {
				continue;
			}

			$type = get_post_type_object( $key );

			if ( $type !== null ) {
				$post_types[ $key ] = $type->labels->name;
			}
		}

		return $post_types;
	}

	/**
	 * Get all tags
	 *
	 * @return array
	 */
	public static function get_tags() {

		$tags = [];

		foreach ( get_tags( [ 'hide_empty' => false ] ) as $tag ) {
			$tag                     = $tag->to_array();
			$tags[ $tag['term_id'] ] = $tag['name'];
		}

		return $tags;
	}

	/**
	 * Get all users with their display name
	 *
	 * @return array
	 */
	public static function get_users() {

		$users = [];

		/* set a number for the users just in case we have a big site with a lot of users, so it won't crash */
		foreach ( get_users( [ 'role__not_in' => [ 'subscriber' ], 'number' => 100 ] ) as $user ) {
			$users[ $user->ID ] = $user->get( 'display_name' );
		}

		return $users;
	}

	/**
	 * Saves the new URLs inside the user meta.
	 *
	 * @param $user_id
	 */
	public static function save_user_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$social_urls = [];
		/* update url for current key */
		foreach ( Thrive_Defaults::social_labels() as $key => $value ) {
			$social_urls[ $key ] = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
		}

		update_user_meta( $user_id, THRIVE_SOCIAL_OPTION_NAME, $social_urls );
	}

	/**
	 * Create attributes from the data.
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function create_attributes( $data ) {
		$attr = [];

		if ( empty( $data ) ) {
			return [];
		}

		foreach ( $data as $key => $value ) {
			$attr[ 'data-' . $key ] = esc_attr( $value );
		}

		return $attr;
	}

	/**
	 * Detect what's the template of the current page, what type it is and maybe the id
	 *
	 * @return array
	 */
	public static function localize_url() {
		/**
		 * Filter to check for external template meta values.
		 * If something is set, return it without doing our own checks.
		 */
		$template_meta = apply_filters( 'thrive_theme_template_meta', [] );

		if ( ! empty( $template_meta['primary_template'] ) ) {
			return $template_meta;
		}

		$primary_template   = '';
		$secondary_template = '';
		$variable_template  = '';

		if ( is_home() || is_front_page() ) {
			$primary_template   = THRIVE_HOMEPAGE_TEMPLATE;
			$secondary_template = is_home() ? THRIVE_BLOG_TEMPLATE : THRIVE_PAGE_TEMPLATE;
		} elseif ( is_tax() ) {
			$queried_object = get_queried_object();

			if ( empty( $queried_object ) ) {
				$primary_template = THRIVE_ERROR404_TEMPLATE;
			} else {
				$primary_template   = THRIVE_ARCHIVE_TEMPLATE;
				$secondary_template = $queried_object->taxonomy;
				$variable_template  = $queried_object->term_id;
			}
		} elseif ( is_archive() ) {
			$primary_template = THRIVE_ARCHIVE_TEMPLATE;
			$queried_object   = get_queried_object();

			if ( is_author() ) {
				$secondary_template = THRIVE_AUTHOR_ARCHIVE_TEMPLATE;
				$variable_template  = static::get_queried_author_id();
			} elseif ( is_date() ) {
				$secondary_template = THRIVE_DATE_TEMPLATE;
			} elseif ( empty( $queried_object ) ) {
				$primary_template = THRIVE_ERROR404_TEMPLATE;
			} elseif ( is_category() ) {
				$secondary_template = THRIVE_CATEGORY_TEMPLATE;
				$variable_template  = $queried_object->term_id;
			} elseif ( is_tag() ) {
				$secondary_template = THRIVE_TAG_TEMPLATE;
				$variable_template  = $queried_object->term_id;
			} elseif ( is_post_type_archive() ) {
				$secondary_template = $queried_object->query_var;
			}
		} elseif ( is_search() ) {
			$primary_template = THRIVE_SEARCH_TEMPLATE;
		} elseif ( is_404() ) {
			$primary_template = THRIVE_ERROR404_TEMPLATE;
		} elseif ( is_singular() ) {
			$primary_template   = THRIVE_SINGULAR_TEMPLATE;
			$secondary_template = get_post_type();
			$variable_template  = get_the_ID();
		}

		return compact( 'primary_template', 'secondary_template', 'variable_template' );
	}

	/**
	 * Get all templates types for the admin dashboard
	 *
	 * @return array
	 */
	public static function list_templates() {
		global $wp_post_types;

		return [
			[
				'key'       => THRIVE_BLOG_TEMPLATE,
				'name'      => __( 'Post Types', 'thrive-theme' ),
				'secondary' => array_filter( static::get_content_types(), static function ( $post_type ) use ( $wp_post_types ) {
					return $post_type['key'] === 'post' || ! empty( $wp_post_types[ $post_type['key'] ]->has_archive );
				} ),
			],
			[
				'key'       => THRIVE_ARCHIVE_TEMPLATE,
				'name'      => __( 'Archives', 'thrive-theme' ),
				'secondary' => static::get_archives(),
			],
			[
				'key'  => THRIVE_SEARCH_TEMPLATE,
				'name' => __( 'Search', 'thrive-theme' ),
			],
		];
	}

	/**
	 * Get archives used at creating templates
	 *
	 * @return array
	 */
	public static function get_archives() {
		$archives = [
			THRIVE_DATE_TEMPLATE           => [
				'key'  => THRIVE_DATE_TEMPLATE,
				'name' => __( 'Date', 'thrive-theme' ),
			],
			THRIVE_AUTHOR_ARCHIVE_TEMPLATE => [
				'key'      => THRIVE_AUTHOR_ARCHIVE_TEMPLATE,
				'name'     => __( 'Authors', 'thrive-theme' ),
				'variable' => [], // these will be populated via ajax search / suggest
			],
		];

		$taxonomies = static::get_taxonomies();

		return array_merge( $archives, $taxonomies );
	}

	/**
	 * Get all public taxonomies
	 *
	 * @return array
	 */
	public static function get_taxonomies() {

		$all = get_taxonomies( [ 'public' => true ], 'objects' );

		$ignored_taxonomies = apply_filters( 'thrive_ignore_taxonomies',
			[
				TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
				'post_format',
			]
		);

		$taxonomies = [];
		foreach ( $all as $taxonomy ) {
			if ( in_array( $taxonomy, $ignored_taxonomies, true ) ) {
				continue;
			}

			$taxonomies[ $taxonomy->name ] = [
				'name'     => $taxonomy->label,
				'variable' => [], // these will be populated via ajax search / suggest
			];

		}

		return $taxonomies;
	}

	/**
	 * Return template path content
	 *
	 * @param string  $path
	 * @param array   $attr
	 * @param boolean $do_shortcode
	 *
	 * @return string
	 */
	public static function return_part( $path = '', $attr = [], $do_shortcode = true ) {

		if ( empty( $path ) || ! file_exists( THEME_PATH . $path ) ) {
			return '';
		}

		ob_start();

		include THEME_PATH . $path;

		$content = trim( ob_get_contents() );

		ob_end_clean();

		if ( $do_shortcode ) {
			$content = do_shortcode( $content );
		}

		$path = str_replace( [ '/', '-' ], '_', $path );

		return apply_filters( 'thrive_template_' . $path, $content );
	}

	/**
	 * Wrapper for 'return_part()' used for getting architect element content.
	 * Get the content for the given element name.
	 *
	 * @param       $element_name
	 * @param array $attr
	 * @param bool  $do_shortcode
	 *
	 * @return string
	 */
	public static function get_element( $element_name, $attr = [], $do_shortcode = true ) {
		$path = '/integrations/architect/views/elements/' . $element_name . '.php';

		return static::return_part( $path, $attr, $do_shortcode );
	}

	/**
	 * Fill array with value in case it's empty
	 *
	 * @param $array
	 * @param $keys
	 * @param $fill
	 *
	 * @return mixed
	 */
	public static function empty_array( $array, $keys, $fill ) {
		foreach ( $keys as $key ) {
			if ( empty( $array[ $key ] ) ) {
				$array[ $key ] = $fill;
			}
		}

		return $array;
	}

	/**
	 * Check if we're on a specific thrive admin page.
	 * If the $page parameter is an array, check each element and return if one of them matches the current screen.
	 *
	 * @param $page string|array If empty we'll just look for the word 'thrive' inside the screen id
	 *
	 * @return bool
	 */
	public static function is_thrive_page( $page = '' ) {

		if ( ! is_admin() ) {
			return false;
		}

		$screen = tve_get_current_screen_key();

		if ( empty( $page ) ) {
			return strpos( $screen, 'thrive' ) !== false;
		}

		return is_array( $page ) ? in_array( $screen, $page, true ) : $screen === $page;
	}

	/**
	 * Return post types that have at least one thrive template created
	 *
	 * @return array
	 */
	public static function get_post_types_with_template() {
		$post_types = [];
		$templates  = get_posts( [
			'posts_per_page' => - 1,
			'post_type'      => THRIVE_TEMPLATE,
			'meta_query'     => [
				[
					'key'   => THRIVE_PRIMARY_TEMPLATE,
					'value' => THRIVE_SINGULAR_TEMPLATE,
				],
			],
		] );

		foreach ( $templates as $template ) {
			$type = get_post_meta( $template->ID, THRIVE_SECONDARY_TEMPLATE, true );
			if ( ! empty( $type ) && ! in_array( $type, $post_types ) ) {
				$post_types[] = $type;
			}
		}

		return $post_types;
	}

	/**
	 * Get the last X months (including current month) along with their years. Optionally, the count can start from $offset.
	 *
	 * @param $month_number
	 * @param $offset
	 *
	 * @return mixed
	 */
	public static function get_the_last_x_months( $month_number, $offset = 0 ) {
		$months_years = [];

		/* deduct the offset from the current date */
		$date = strtotime( '-' . $offset . ' months' );

		for ( $i = 0; $i < $month_number; $i ++ ) {
			$month = date( 'm', $date );
			$year  = date( 'Y', $date );

			$months_years[] = [
				'month' => $month,
				'year'  => $year,
			];

			/* deduct 1 month from the current date */
			$date = strtotime( '-1 months', $date );
		}

		return $months_years;
	}

	/**
	 * Get the picture for the post author
	 *
	 * @param $post
	 *
	 * @return false|string
	 */
	public static function get_author_picture( $post ) {

		if ( $post === null ) {
			$image_url = THRIVE_AUTHOR_IMAGE_PLACEHOLDER;
		} else {
			$image_url = get_avatar_url( $post->post_author, [ 'default' => THRIVE_AUTHOR_IMAGE_PLACEHOLDER ] );
		}

		return $image_url;
	}

	/**
	 * Get post featured image
	 *
	 * @param $post
	 *
	 * @return array|false|string
	 */
	public static function get_post_featured_image( $post ) {

		if ( $post === null || ! has_post_thumbnail( $post->ID ) ) {
			$image_url = Thrive_Featured_Image::get_default_url();
		} else {
			$image_url = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
		}

		return $image_url;
	}

	/**
	 * Get ajax url.
	 *
	 * @return string
	 */
	public static function get_ajax_url() {
		$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
		if ( is_ssl() ) {
			$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
		}

		return $admin_base_url . 'admin-ajax.php';
	}

	/**
	 * Get the image source for the id.
	 *
	 * @param        $image_id
	 * @param string $size
	 *
	 * @return mixed
	 */
	public static function get_image_src( $image_id, $size = 'full' ) {
		$attachment = wp_get_attachment_image_src( $image_id, $size );

		return empty( $attachment ) ? '' : $attachment[0];
	}

	/**
	 * Get the active global colors used by the current skin -> the global colors present in the skin templates along with their headers and footers,
	 * and the global colors from the skin's default typography set.
	 *
	 * @return array
	 */
	public static function get_used_global_colors() {
		/* get all the global colors */
		$global_colors      = get_option( 'thrv_global_colours', [] );
		$used_global_colors = [];

		/* get the current skin's templates */
		$templates = thrive_skin()->get_templates( 'object' );

		/* get the global colors from the default typography set */
		$typography_colors  = thrive_typography()->get_global_colors( $global_colors );
		$used_global_colors = array_merge( $typography_colors, $used_global_colors );

		/* get the global colors from each existing template, and their linked headers and footers */
		foreach ( $templates as $template ) {
			$used_global_colors = array_merge( $template->get_global_colors( $global_colors ), $used_global_colors );
		}

		return $used_global_colors;
	}

	/**
	 * Check if we are in the skin preview mode
	 *
	 * @return bool
	 */
	public static function is_skin_preview() {
		return ( isset( $_GET[ THRIVE_SKIN_PREVIEW ] ) ) ? $_GET[ THRIVE_SKIN_PREVIEW ] : false;
	}

	/**
	 * Return registered sidebars
	 *
	 * @param $only_active_sidebars
	 *
	 * @return array
	 */
	public static function get_sidebars( $only_active_sidebars = true ) {
		global $wp_registered_sidebars;

		$sidebars = [];

		foreach ( $wp_registered_sidebars as $sidebar ) {
			if ( $only_active_sidebars && ! is_active_sidebar( $sidebar['id'] ) ) {
				continue;
			}

			$sidebars[] = [
				'name'  => $sidebar['name'],
				'value' => $sidebar['id'],
			];
		}

		return $sidebars;
	}

	/**
	 * Create a WordPress attachment from a base64 encoded image
	 * This accepts a custom filename, and is uploaded by default to the theme subdirectory
	 *
	 * @param string  $base64_image
	 * @param string  $filename
	 * @param boolean $use_custom_subdir
	 *
	 * @return int|WP_Error
	 */
	public static function create_attachment_from_image( $base64_image, $filename = '', $use_custom_subdir = true ) {

		if ( $use_custom_subdir ) {
			/* add ( and then remove ) the 'upload_dir' filter in order to overwrite the default upload directory */
			add_filter( 'upload_dir', [ __CLASS__, 'get_image_upload_dir' ] );
		}

		/* 'wp_upload_dir' also creates the directory if it doesn't exist, so we still have to call it even if we changed the upload dir function */
		$upload_dir = wp_upload_dir();

		if ( $use_custom_subdir ) {
			remove_filter( 'upload_dir', [ __CLASS__, 'get_image_upload_dir' ] );
		}

		$image_data = base64_decode( $base64_image );

		if ( empty( $filename ) ) {
			$filename = uniqid( 'random_image_', true ) . '.jpg';
		}

		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		file_put_contents( $file, $image_data );

		$file_type = wp_check_filetype( $filename, null );

		$attachment = [
			'post_mime_type' => $file_type['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'meta_input'     => [
				THRIVE_DEMO_CONTENT_THUMBNAIL => 1,
			],
		];

		$attachment_id = wp_insert_attachment( $attachment, $file );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$attach_data = wp_generate_attachment_metadata( $attachment_id, $file );

		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return $attachment_id;
	}

	/**
	 * Return the upload directory data after appending the sub-directory path to it.
	 *
	 * @param $upload
	 * @param $sub_dir
	 *
	 * @return mixed
	 */
	public static function get_image_upload_dir( $upload, $sub_dir = 'images' ) {
		return static::get_upload_dir( $upload, THEME_FOLDER . '/' . $sub_dir );
	}

	/**
	 * Check for custom templates assigned to the current page or a given post_id if post ID is not empty
	 * Used also in Thrive Apprentice Visual Editing
	 *
	 * @param int $post_id
	 *
	 * @return array|int[]|WP_Post[]
	 */
	public static function get_page_custom_templates( $post_id = 0 ) {
		$templates = [];

		/* except posts, pages and other custom post types can have also custom template */
		$custom_template = thrive_post( $post_id )->get_meta( THRIVE_META_POST_TEMPLATE );

		if ( ! empty( $custom_template ) ) {
			$args = [
				'posts_per_page' => 1,
				'post_type'      => THRIVE_TEMPLATE,
				'tax_query'      => [ thrive_skin()->build_skin_query_params() ],
				'order'          => 'ASC',
				'include'        => [ $custom_template ],
			];

			$templates = get_posts( $args );
		}

		return $templates;
	}

	/**
	 * Clear cache functionality from some of the most known plugins
	 */
	public static function clear_cache() {

		/* TODO: research and add more plugins here */
		$cache_plugins = [
			[
				'file'   => 'w3-total-cache/w3-total-cache.php',
				'clear'  => 'w3tc_flush_all',
				'source' => ABSPATH . PLUGINDIR . 'w3-total-cache/w3-total-cache-api.php',
			],
			[
				'file'   => 'wp-super-cache/wp-cache.php',
				'clear'  => 'wp_cache_clear_cache',
				'source' => ABSPATH . PLUGINDIR . 'wp-super-cache/wp-cache-phase2.php',
			],
			[
				'file'   => 'wp-rocket/wp-rocket.php',
				'clear'  => 'rocket_clean_domain',
				'source' => ABSPATH . PLUGINDIR . 'wp-rocket/inc/functions/files.php',
			],
			[
				'file'   => 'simple-cache/simple-cache.php',
				'clear'  => 'sc_cache_flush',
				'source' => ABSPATH . PLUGINDIR . 'simple-cache/inc/functions.php',
			],
			[
				'file'       => 'wp-fastest-cache/wpFastestCache.php',
				'clear'      => [ 'WpFastestCache', 'deleteCssAndJsCacheToolbar' ],
				'source'     => ABSPATH . PLUGINDIR . 'wp-super-cache/wpFastestCache.php',
				'nonce_data' => [ 'request_key' => 'nonce', 'nonce_value' => 'wpfc' ],
			],
			[
				'file'   => 'cache-enabler/cache-enabler.php',
				'clear'  => [ 'Cache_Enabler', 'clear_total_cache' ],
				'source' => ABSPATH . PLUGINDIR . 'cache-enabler/inc/cache_enabler.class.php',
			],
			[
				'file'   => 'cachify/cachify.php',
				'clear'  => [ 'Cachify', 'flush_total_cache' ],
				'source' => ABSPATH . PLUGINDIR . 'cachify/inc/cachify.class.php',
			],
		];

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin['file'] ) ) {
				$has_nonce = false;

				// check if the plugin needs a nonce
				if ( ! empty( $plugin['nonce_data'] ) ) {
					$has_nonce = true;
					if ( isset( $_REQUEST[ $plugin['nonce_data']['request_key'] ] ) ) {
						$previous_nonce = $_REQUEST[ $plugin['nonce']['request_key'] ];
					}
					$_REQUEST[ $plugin['nonce_data']['request_key'] ] = wp_create_nonce( $plugin['nonce_data']['nonce_value'] );
				}

				if ( is_file( $plugin['source'] ) ) {
					require_once $plugin['source'];
				}

				$callback = $plugin['clear'];
				if ( is_array( $callback ) ) {
					if ( class_exists( $callback[0], false ) && method_exists( $callback[0], $callback[1] ) ) {
						$instance    = new $callback[0]();
						$callback[0] = $instance;
					} else {
						continue;
					}
				} elseif ( ! function_exists( $callback ) ) {
					continue;
				}

				call_user_func( $callback );

				//handle nonce after the cache is cleared
				if ( $has_nonce ) {
					if ( isset( $previous_nonce ) ) {
						$_REQUEST[ $plugin['nonce_data']['request_key'] ] = $previous_nonce;
					} else {
						unset( $_REQUEST[ $plugin['nonce_data']['request_key'] ] );
					}
				}
			}
		}
	}

	/**
	 * Check if we're anywhere in the editor
	 *
	 * @param bool $or_ajax
	 *
	 * @return bool
	 */
	public static function is_editor( $or_ajax = false ) {
		return static::is_inner_frame() || static::is_theme_template() || ( $or_ajax && static::during_ajax() );
	}

	/**
	 * Check if we're on a ajax/rest request
	 *
	 * @return bool
	 */
	public static function during_ajax() {
		return wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Return true only if we're inside the architect editor.
	 *
	 * @param int|boolean $post_id
	 *
	 * @return bool
	 */
	public static function is_architect_editor( $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		return static::is_allowed_post_type( $post_id ) && ! isset( $_GET[ THRIVE_THEME_FLAG ] ) && is_editor_page_raw( true );
	}

	/**
	 * Return an array of routes used in the editor
	 *
	 * @return array
	 */
	public static function get_rest_routes() {

		$blog_id = get_current_blog_id();
		$routes  = [
			'symbols' => get_rest_url( $blog_id, 'wp/v2/' . TCB_Symbols_Post_Type::SYMBOL_POST_TYPE ),
		];

		foreach ( [ 'templates', 'sections', 'options', 'posts', 'list', 'content', 'sidebar', 'demo-content', 'post', 'layouts', 'skins', 'woo', 'palette' ] as $route ) {
			$routes[ $route ] = get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/' . $route );
		}

		return $routes;
	}

	/**
	 * Generate and return a template for the pagination url type.
	 *
	 * @return string|string[]|null
	 */
	public static function get_pagination_url_template() {
		$random_nr       = mt_rand( PHP_INT_MAX / 100, PHP_INT_MAX );
		$pagination_href = wp_specialchars_decode( get_pagenum_link( $random_nr ) );

		$pagination_regex = '/(?:paged=|page\/)(' . $random_nr . ')/';

		$pagination_href = preg_replace_callback( $pagination_regex, static function ( $m ) {
			return empty( $m[1] ) ? '' : str_replace( $m[1], '[thrive_page_number]', $m[0] );
		}, $pagination_href );

		return $pagination_href;
	}

	/**
	 * Generate and return a taxonomy query in order to get the selected post format.
	 * If the format is standard, we have to negate all the other formats in the query.
	 *
	 * @param $format
	 *
	 * @return array
	 */
	public static function get_post_format_tax_query( $format ) {
		$term_prefix = 'post-format-';

		$tax_query = [
			'taxonomy' => 'post_format',
			'field'    => 'slug',
		];

		/* if the format is 'standard', add all the other post formats in a 'NOT IN' query */
		if ( empty( $format ) || ( $format === THRIVE_STANDARD_POST_FORMAT ) ) {
			$tax_query['terms'] = array_map( static function ( $post_format ) use ( $term_prefix ) {
				return $term_prefix . $post_format;
			}, Thrive_Theme::post_formats() );

			$tax_query['operator'] = 'NOT IN';
		} else {
			$tax_query['terms'] = [ $term_prefix . $format ];
		}

		return [ $tax_query ];
	}

	/**
	 * Add config to post element inside architect
	 *
	 * @param $config
	 *
	 * @return array
	 */
	public static function tcb_element_post_config( $config ) {

		$config['components']['post-type-template-settings']['order'] = 1;
		$config['components']['page_content_settings']                = [
			'config' => [
				'PostTitle'     => [
					'config'  => [
						'label' => __( 'Post title', 'thrive-theme' ),
					],
					'extends' => 'LabelInput',
				],
				'FeaturedImage' => [
					'config' => [
						'label' => __( 'Change Featured Image', 'thrive-theme' ),
					],
				],
			],
			'order'  => 2,
		];

		return $config;
	}

	/**
	 * @param $string
	 *
	 * @return string
	 */
	public static function remove_extra_spaces( $string ) {
		return trim( preg_replace( '/\s+/', ' ', $string ) );
	}

	/**
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function get_section_visibility( $type ) {

		/* during ajax, sections are always visible */
		$is_visible = static::during_ajax() || static::get_template_visibility( $type );

		$is_architect = is_singular() && ! static::is_inner_frame() && ! ( static::is_preview() && static::inner_frame_id() );

		if ( $is_architect ) {
			$post_visibility = thrive_post()->get_visibility( $type );

			/* empty means that the post is inheriting the template's visibility */
			if ( ! empty( $post_visibility ) ) {
				$is_visible = $post_visibility === 'show';
			}
		}

		return $is_visible;
	}

	/**
	 * Check if the section is visible.
	 * - top/bottom - the template decides
	 * - sidebar - the layout decides
	 * - content - always visible ( can't be hidden )
	 *
	 * @param string type
	 *
	 * @return bool
	 */
	public static function get_template_visibility( $type ) {
		switch ( $type ) {
			case THRIVE_HEADER_SECTION:
			case THRIVE_FOOTER_SECTION:
			case 'top':
			case 'bottom':
				/* top & bottom visibility info is stored in the template */
				$visibility = empty( thrive_template()->get_section( $type )['hide'] );
				break;
			case 'sidebar':
				$visibility = thrive_template()->is_sidebar_visible();
				break;
			case 'content':
			default:
				$visibility = true;
		}

		return $visibility;
	}

	/**
	 * Add Theme specific section for header and footer
	 *
	 * @param $config
	 *
	 * @return mixed
	 */
	public static function tcb_element_hf_config( $config ) {
		if ( static::is_theme_template() ) {
			$config['components']['theme-hf'] = [
				'config' => [
					'Visibility'         => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Visibility', 'thrive-theme' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
					'InheritContentSize' => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Inherit content size from layout', 'thrive-theme' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
					'StretchBackground'  => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Stretch background to full width', 'thrive-theme' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
					'ContentWidth'       => [
						'config'  => [
							'default' => '1080',
							'min'     => '1',
							'max'     => '1980',
							'label'   => __( 'Content Width', 'thrive-theme' ),
							'um'      => [ 'px' ],
							'css'     => 'max-width',
						],
						'extends' => 'Slider',
					],
					'StretchContent'     => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Stretch content to full width', 'thrive-theme' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
					'SectionHeight'      => [
						'config'  => [
							'default' => '80',
							'min'     => '1',
							'max'     => '1000',
							'label'   => __( 'Section Minimum Height', 'thrive-theme' ),
							'um'      => [ 'px', 'vh' ],
							'css'     => 'min-height',
						],
						'to'      => ' .symbol-section-in',
						'extends' => 'Slider',
					],
					'VerticalPosition'   => [
						'config'  => [
							'name'    => __( 'Vertical Position', 'thrive-theme' ),
							'buttons' => [
								[
									'icon'    => 'top',
									'default' => true,
									'value'   => '',
								],
								[
									'icon'  => 'vertical',
									'value' => 'center',
								],
								[
									'icon'  => 'bot',
									'value' => 'flex-end',
								],
							],
						],
						'extends' => 'ButtonGroup',
						'to'      => '.symbol-section-in',
					],
					'HeaderPosition'     => [
						'config'  => [
							'name'       => 'Header Position',
							'full-width' => true,
							'buttons'    => [
								[
									'value'   => 'push',
									'text'    => __( 'Push Content', 'thrive-theme' ),
									'default' => true,
								],
								[
									'value' => 'over',
									'text'  => __( 'Over Content', 'thrive-theme' ),
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
				],
				'order'  => 1,
			];
		}

		return $config;
	}

	/**
	 * @param $id
	 * @param $args
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function create_preview( $id, $args ) {
		/* make sure there's enough memory to process a large image */
		ini_set( 'memory_limit', TVE_EXTENDED_MEMORY_LIMIT );

		$defaults = [
			'action'     => false,
			'upload_dir' => [ 'Thrive_Utils', 'get_preview_upload_dir_callback' ],
			'name_fn'    => function () use ( $id ) {
				return $id . '.png';
			},
		];

		$args = wp_parse_args( $args, $defaults );

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		add_filter( 'upload_dir', $args['upload_dir'] );

		$moved_file = wp_handle_upload(
			$_FILES['img_data'],
			[
				'action'                   => $args['action'], //this needs to match the action sent int the FormData from js
				'unique_filename_callback' => $args['name_fn'],
			]
		);

		remove_filter( 'upload_dir', $args['upload_dir'] );

		if ( empty( $moved_file['url'] ) ) {
			throw new Exception( __( 'The file could not be saved: ' . ( isset( $moved_file['error'] ) ? $moved_file['error'] : '' ), 'thrive-theme' ) );
		}

		$preview = wp_get_image_editor( $moved_file['file'] );

		if ( ! is_wp_error( $preview ) && $args['crop_width'] && $args['crop_height'] ) {

			/* resize to the given width while using the image's native height */
			$preview->resize( $args['crop_width'], null );

			$preview_sizes = $preview->get_size();

			/* crop to the given height ( only if the current height exceeds the required height ) */
			if ( $preview_sizes['height'] > $args['crop_height'] ) {
				$width_to_crop = min( $args['crop_width'], $preview_sizes['width'] );

				$preview->crop( 0, 0, $width_to_crop, $args['crop_height'] );
			}

			$preview->save( $moved_file['file'] );
		}

		$image_editor = wp_get_image_editor( $moved_file['file'] );

		if ( is_wp_error( $image_editor ) ) {
			throw new Exception( $image_editor->get_error_message() );
		}

		$dimensions = $image_editor->get_size();

		$thumb = [
			'url' => $moved_file['url'],
			'h'   => $dimensions['height'],
			'w'   => $dimensions['width'],
		];

		/* save the thumbnail data to the template post meta */
		TCB_Utils::save_thumbnail_data( $id, $thumb );

		return $thumb;
	}

	/**
	 * Return the upload directory data after appending the preview sub-directory path to it.
	 *
	 * @param $upload
	 *
	 * @return mixed
	 */
	public static function get_preview_upload_dir_callback( $upload ) {
		return static::get_upload_dir( $upload, THEME_UPLOADS_PREVIEW_SUB_DIR );
	}

	/**
	 * Return the upload directory data after appending the sub-directory path to it.
	 *
	 * @param $upload
	 * @param $sub_dir
	 *
	 * @return mixed
	 */
	public static function get_upload_dir( $upload, $sub_dir = THEME_FOLDER ) {

		if ( ! empty( $sub_dir ) ) {
			$upload['path']   = $upload['basedir'] . '/' . $sub_dir;
			$upload['url']    = $upload['baseurl'] . '/' . $sub_dir;
			$upload['subdir'] = $sub_dir;
		}

		return $upload;
	}

	/**
	 * Get query vars based on the template that we are editing
	 *
	 * @return array
	 */
	public static function get_query_vars() {
		global $wp_query;
		$queried_object = get_queried_object();
		$rules          = [];

		if ( empty( $wp_query->query['post_type'] ) ) {
			$post_type = get_post_type();

			if ( empty( $post_type ) ) {
				$post_type = 'post';
			}
		} else {
			$post_type = $wp_query->query['post_type'];
		}

		if ( is_search() ) {
			$post_type = isset( $_GET['tcb_sf_post_type'] ) ? $_GET['tcb_sf_post_type'] : $post_type;
		}
		$query_vars = [ 'post_type' => $post_type ];

		if ( is_archive() || is_post_type_archive() || is_home() || is_search() || is_date() ) {
			$query_vars['posts_per_page'] = empty( $wp_query->query_vars['posts_per_page'] ) ? 0 : $wp_query->query_vars['posts_per_page'];

			if ( is_search() ) {
				$query_vars['s'] = $wp_query->query['s'];
			}

			if ( is_date() ) {
				$query_vars['year'] = $wp_query->query['year'];
				if ( isset( $wp_query->query['monthnum'] ) ) {
					$query_vars['monthnum'] = $wp_query->query['monthnum'];
				}
				if ( isset( $wp_query->query['day'] ) ) {
					$query_vars['day'] = $wp_query->query['day'];
				}
			}

			if ( ! empty( $wp_query->query['paged'] ) ) {
				$query_vars['paged'] = $wp_query->query['paged'];
			}
		}

		if ( is_singular() ) {
			$query_vars['page_id'] = get_the_ID();
		} else {
			if ( ! empty( $queried_object->taxonomy ) ) {
				$rules[] = [
					'taxonomy' => $queried_object->taxonomy,
					'terms'    => [ $queried_object->term_id ],
					'operator' => 'IN',
				];
			}

			if ( isset( $wp_query->query['author_name'] ) ) {
				$rules[] = [
					'taxonomy' => 'author',
					'terms'    => [ static::get_queried_author_id() ],
					'operator' => 'IN',
				];
			}
		}

		if ( is_home() ) {
			/* Default query for blog - because this is the only one editable */
			$query_vars['order']   = $wp_query->query_vars['order'];
			$query_vars['filter']  = 'custom';
			$query_vars['orderby'] = 'date';

			if ( ! empty( $wp_query->query['posts_per_page'] ) ) {
				$query_vars['posts_per_page'] = $wp_query->query['posts_per_page'];
			}
		}

		if ( ! empty( $rules ) ) {
			$query_vars['rules'] = $rules;
		}

		return apply_filters( 'thrive_theme_query_vars', $query_vars );
	}


	/**
	 * Get content types defined for the theme
	 *
	 * @param string $context
	 *
	 * @return array
	 */
	public static function get_content_types( $context = '' ) {
		$types             = [];
		$post_types        = get_post_types( [ 'public' => true ], 'objects' );
		$ignore_post_types = apply_filters( 'thrive_theme_ignore_post_types', [
			'attachment',
			'content_template',
			'tcb_lightbox',
			'tve_form_type',
			TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
		] );

		foreach ( $post_types as $post_type ) {
			if ( ! in_array( $post_type->name, $ignore_post_types ) ) {
				$types[ $post_type->name ] = [
					'key'  => $post_type->name,
					'name' => $post_type->labels->singular_name,
				];
			}
		}

		return apply_filters( 'thrive_theme_content_types', $types, $context );
	}

	/**
	 * Get post type singular name
	 *
	 * @param string|null $post_type
	 *
	 * @return string
	 */
	public static function get_post_type_name( $post_type = '' ) {
		if ( empty( $post_type ) ) {
			$post_type = thrive_post()->get( 'post_type' );
		}

		$post_type_object = get_post_type_object( $post_type );

		return $post_type_object === null ? '' : $post_type_object->labels->singular_name;
	}

	/**
	 * Check if we are on a post / page or a custom post type handled by the theme
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	public static function is_allowed_post_type( $post_id ) {
		return array_key_exists( (string) get_post_type( $post_id ), static::get_content_types() );
	}

	/**
	 * Load default icons for the elements
	 *
	 * @param string $name If this is sent, return a single icon else return all of them
	 *
	 * @return mixed|string
	 */
	public static function load_default_icons( $name ) {
		$icons = require THEME_PATH . '/inc/templates/default/icons.php';

		if ( empty( $name ) ) {
			$html = '<svg style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg"><defs>';

			foreach ( $icons as $icon ) {
				$html .= str_replace( 'svg', 'symbol', $icon );
			}

			$html .= '</defs></svg>';
		} else {
			$html = empty( $icons[ $name ] ) ? '' : $icons[ $name ];
		}

		return $html;
	}

	/**
	 * Homepage options required for the editor.
	 *      - check if we have blog or a page on the homepage
	 *      - if we have a landing page on the homepage, we also get the edit_url and the set
	 *
	 * @return array
	 */
	public static function get_homepage_options() {
		$options = [];
		foreach ( [ 'show_on_front', 'page_on_front', 'page_for_posts' ] as $option ) {
			$options[ $option ] = get_option( $option );
		}

		if ( $options['show_on_front'] === 'page' && ! empty( $options['page_on_front'] ) ) {
			$options['page']            = get_post( $options['page_on_front'] );
			$options['is_landing_page'] = tve_post_is_landing_page( $options['page_on_front'] );
			if ( ! empty( $options['is_landing_page'] ) ) {
				$options['edit_url']         = tcb_get_editor_url( $options['page_on_front'] );
				$options['landing_page_set'] = get_post_meta( $options['page_on_front'], 'tve_landing_set', true );
			}
		}

		return $options;
	}

	/**
	 * Check if this cloud template type should be available in TAr Light
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public static function ct_type_is_unlocked_light_template( $type ) {
		return in_array( $type, static::UNLOCKED_CT_TYPES, true );
	}

	/**
	 * Generates an unique string based on unix timestamp
	 *
	 * @return string
	 */
	public static function get_unique_id() {
		return base_convert( time(), 10, 36 );
	}

	/**
	 * Replace menu id  in the shortcode config and delete custom menu from html
	 *
	 * @param int    $menu_id
	 * @param string $html
	 *
	 * @return string|string[]|null
	 */
	public static function replace_menu_in_html( $menu_id, $html ) {
		if ( ! $menu_id ) {
			return $html;
		}
		/**
		 * Identify a custom menu in html / used when building templates on the cloud
		 */
		$custom_menu_pattern = '/<div class="thrive-shortcode-html tve-custom-menu-type(.+?)<\/ul>(\s*)(<\/div>(\s*)<\/div>)(?!\s*<\/li>)/s';

		/**
		 * Identify thrive menu shortcode in html / this always needs to be present
		 */
		$shortcode_menu_pattern = '/__CONFIG_widget_menu__(.+?)__CONFIG_widget_menu__/s';

		//Replace menu id with the new one in the CONFIG
		$html = preg_replace_callback( $shortcode_menu_pattern, static function ( $match ) use ( $menu_id ) {
			$menu_config = json_decode( $match[1], true );
			$old_id      = $menu_config['menu_id'];

			return str_replace( $old_id, $menu_id, $match[0] );

		}, $html );

		//Delete the custom menu part from the html
		return preg_replace( $custom_menu_pattern, '</div>', $html );
	}

	/**
	 * Get queried author using $wp_query->query instead of queried object
	 *
	 * @return int
	 */
	public static function get_queried_author_id() {
		global $wp_query;
		$author = empty( $wp_query->query['author_name'] ) ? false : get_user_by( 'slug', $wp_query->query['author_name'] );

		return empty( $author ) ? 0 : $author->ID;
	}

	/**
	 * Array to be used in get_posts() `meta_query` field when the results must not contain any landing pages.
	 *
	 * @return array
	 */
	public static function meta_query_no_landing_pages() {
		return [
			'relation' => 'OR',
			[
				'key'     => 'tve_landing_page',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'   => 'tve_landing_page',
				'value' => '',
			],
		];
	}

	/**
	 * Grab website URL just in case wp functions are not available
	 *
	 * @param bool $with_uri
	 *
	 * @return string
	 */
	public static function get_site_url( $with_uri = false ) {
		return sprintf(
			'%s://%s%s',
			isset( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ? 'https' : 'http',
			isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : 'localhost/',
			$with_uri && isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : ''
		);
	}

	/**
	 * Check if this tag is a thrive shortcode
	 *
	 * @param $tag
	 *
	 * @return bool
	 */
	public static function is_thrive_shortcode( $tag ) {
		$starts_with_thrive_prefix = false;

		/**
		 * Allow other plugins to add their prefixes
		 */
		$thrive_shortcode_prefixes = apply_filters( 'thrive_theme_shortcode_prefixes', Thrive_Shortcodes::$thrive_shortcode_prefixes );

		foreach ( $thrive_shortcode_prefixes as $prefix ) {
			if ( strpos( $tag, $prefix ) === 0 ) {
				$starts_with_thrive_prefix = true;
				break;
			}
		}

		return $starts_with_thrive_prefix;
	}

	/**
	 * Check if we have a iframe specific request or if an ajax originated from an iframe
	 *
	 * @return bool
	 */
	public static function is_iframe() {

		/**
		 * Check if the request is specific to a ttb iframe
		 */
		$is_iframe = isset( $_GET[ THRIVE_NO_BAR ] ) || Thrive_Wizard::is_frontend() || static::is_theme_typography();

		/**
		 *  if the referer has thrive_no_bar -> we are in preview mode context
		 *  if the referer has ttb wizard -> we are in the wizard context
		 */
		$is_ajax_from_iframe = isset( $_SERVER['HTTP_REFERER'] ) && ( strpos( $_SERVER['HTTP_REFERER'], THRIVE_NO_BAR ) !== false || strpos( $_SERVER['HTTP_REFERER'], 'ttb-wizard' ) !== false );

		return $is_iframe || $is_ajax_from_iframe;
	}

	/**
	 * @return string|void
	 */
	public static function get_current_url() {
		global $wp;

		return home_url( add_query_arg( [], $wp->request ) );
	}

	/**
	 * make sure iframe URLs are not loaded via http from a https dashboard
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function ensure_https( $url ) {
		if ( is_ssl() ) {
			$url = preg_replace( '#^http(s)?://#mi', 'https://', $url );
		}

		return $url;
	}

	/**
	 * Filter the default `get_posts` arguments used in various contexts
	 *
	 * @param array  $args    current list of arguments
	 * @param string $context current context
	 *
	 * @return array
	 */
	public static function filter_default_get_posts_args( $args, $context = 'content_switch' ) {

		/**
		 * Filter the arguments before showing the results for a number of places inside the theme
		 *
		 * @param array  $args    current arguments
		 * @param string $context context in which this is called
		 */
		return apply_filters( 'thrive_theme_get_posts_args', $args, $context );
	}

	/**
	 * Whether or not the cloud api requests should be cached.
	 *
	 * @return bool
	 */
	public static function bypass_transient_cache() {
		$bypass_cache = defined( 'THRIVE_THEME_CLOUD_DEBUG' ) && THRIVE_THEME_CLOUD_DEBUG;

		/**
		 * Filter that allows forcing a cloud download regardless of the constant from above.
		 * If a truthy value is returned from the filter implementation, the templates will be fetched from the cloud instead of getting them from a transient.
		 *
		 * @param boolean $bypass_cache
		 */
		return (bool) apply_filters( 'thrive_theme_bypass_cloud_transient', $bypass_cache );
	}

	/**
	 * Get the value of a transient. It makes sure that cache is bypassed when needed.
	 *
	 * @param string $transient_name
	 *
	 * @return mixed
	 */
	public static function get_transient( $transient_name ) {
		if ( static::bypass_transient_cache() ) {
			delete_transient( $transient_name );
		}

		return get_transient( $transient_name );
	}

	/**
	 * Set query variables and on singular pages call the_post
	 *
	 * @param $query_vars
	 */
	public static function set_query_vars( $query_vars ) {
		if ( ! empty( $query_vars ) ) {
			/** @var WP_Query */
			global $wp_query;
			/* set the global query the same one we we're editing on so we can convert shortcodes better */
			$wp_query->query( $query_vars );
			if ( $wp_query->is_singular() ) {
				$wp_query->the_post();
			}

			/**
			 * Allows running custom code after the query has been set.
			 * Makes it possible to perform query-related interrogations during REST API requests based on query_vars (e.g. is_tax())
			 */
			do_action( 'thrive_theme_after_query_vars' );
		}
	}

	/**
	 * Check if we are inside the theme dashboard
	 *
	 * @return bool
	 */
	public static function in_theme_dashboard() {
		global $pagenow;

		return $pagenow === 'admin.php' && ( isset( $_GET['page'] ) && $_GET['page'] === THRIVE_MENU_SLUG );
	}

	/**
	 * If 'get_option( 'page_for_posts' )' is empty, get_permalink( 0 ) returns the link to the current post, so in that case we return the home url
	 *
	 * @return false|string
	 */
	public static function get_blog_url() {
		$page_for_posts = get_option( 'page_for_posts' );

		return empty( $page_for_posts ) ? get_home_url() : get_permalink( $page_for_posts );
	}

	/**
	 * Replace css ids with new ones
	 *
	 * @param string $content
	 *
	 * @return array
	 */
	public static function replace_css_ids( &$content ) {
		$map = [];

		if ( preg_match_all( '/tve-u-([a-zA-Z0-9]*)/', $content, $matches ) ) {
			$css_ids = $matches[1];

			/* remove duplicates and re-index by using array_values() */
			$css_ids = array_values( array_unique( $css_ids ) );

			$new_ids = [];

			/* add to the replacements map */
			foreach ( $css_ids as $id ) {
				/* In order to have unique ids generated, we need to have the length >= 14 */

				do {
					/* make sure we don't create the same id twice */
					$new_id = 'tve-u-' . substr( uniqid( '', true ), 0, 14 );
				} while ( in_array( $new_id, $new_ids ) );

				$new_ids[] = $new_id;

				$map[ 'tve-u-' . $id ] = $new_id;
			}
		}

		/* replace everything in the map with their equivalent */
		$content = str_replace( array_keys( $map ), array_values( $map ), $content );

		return $map;
	}

	/**
	 * Replace LG identifiers with new ones
	 *
	 * @param $content
	 * @param $new_post_title
	 *
	 * @return array|string|string[]
	 */
	public static function replace_form_identifiers( $content, $new_post_title ) {
		$new_post_title = sanitize_title( $new_post_title );
		$map            = [];

		if ( preg_match_all( '/__TCB_FORM__{.*form_identifier&quot;:&quot;(.*)&quot;.*}__TCB_FORM__/', $content, $matches ) ) {
			$form_settings = $matches[1];
			foreach ( $form_settings as $old_identifier ) {
				$new_identifier         = $new_post_title . '-form-' . substr( uniqid( '', true ), 0, 6 );
				$map[ $old_identifier ] = $new_identifier;
			}
		}

		/* replace everything in the map with their equivalent */
		$content = str_replace( array_keys( $map ), array_values( $map ), $content );

		return $content;
	}
}
