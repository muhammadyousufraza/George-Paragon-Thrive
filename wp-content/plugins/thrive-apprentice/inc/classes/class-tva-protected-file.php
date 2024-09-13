<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * File class
 *
 * @property int    ID
 * @property string post_title
 * @property string url
 * @property string file_name
 * @property string file_type
 * @property string file_extension
 * @property string original_name
 * @property string download_url
 * @property string download_counter
 * @property array  products
 */
class TVA_Protected_File implements JsonSerializable {

	/**
	 * The subdirectory the file is under on.
	 * This directory is a subdirectory of WordPress uploads folder
	 */
	const SUB_DIR = 'thrive-protected-files';

	/**
	 * Post type used to store the protected file
	 */
	const POST_TYPE              = 'tva_protected_file';
	const POST_TYPE_LABEL        = 'Protected file';
	const POST_TYPE_LABEL_PLURAL = 'Protected files';

	/**
	 * Protected file download URL Query Variable Name
	 */
	const DOWNLOAD_URL_QUERY_NAME          = 'tva_protected_file';
	const DOWNLOAD_RESOURCE_URL_QUERY_NAME = 'tva_resource_protected_file';

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * File constructor
	 *
	 * @param int|array|\WP_Post $data
	 */
	public function __construct( $data ) {

		if ( is_array( $data ) ) {
			$this->data = $data;
		} elseif ( $data instanceof WP_Post ) {
			$this->data = $data->to_array();
		} elseif ( is_numeric( $data ) ) {
			$this->data = [ 'ID' => (int) $data ];
		}

		$this->data = wp_parse_args( $this->data, [
			'post_type'   => static::POST_TYPE,
			'post_status' => 'publish',
		] );

		if ( $this->ID && ! $this->post_title ) {
			$this->data = get_post( $this->ID, ARRAY_A );
		}
	}

	/**
	 * Magic method
	 * Returns value from data
	 *
	 * @param $key
	 *
	 * @return int|mixed|null
	 */
	public function __get( $key ) {
		$value = null;

		if ( isset( $this->data[ $key ] ) ) {
			$value = $this->data[ $key ];
		} elseif ( method_exists( $this, 'get_' . $key ) ) {
			$method_name = 'get_' . $key;
			$value       = $this->$method_name();
		}

		return $value;
	}

	/**
	 * Returns the file name that is stored on the disk
	 *
	 * @return string
	 */
	public function get_file_name() {
		return (string) get_post_meta( $this->ID, 'tva_file_name', true );
	}

	/**
	 * Returns the file type needed for the UI
	 *
	 * @return string
	 */
	public function get_file_type() {
		$extension = $this->file_extension;
		$type      = '';
		if ( $extension === 'pdf' ) { //Special types
			$type = 'pdf';
		} elseif ( strpos( $extension, 'doc' ) !== false ) {
			$type = 'doc';//DOC and DOCX
		} elseif ( strpos( $extension, 'xls' ) !== false ) {
			$type = 'xls';//XLS and xlsx
		} else {
			$wp_ext_types = wp_get_ext_types();

			foreach ( $wp_ext_types as $category => $extension_arr ) {
				if ( in_array( $extension, $extension_arr ) ) {
					$type = $category;
					break;
				}
			}
		}

		return $type;
	}

	/**
	 * Returns the file extension
	 *
	 * @return string
	 */
	public function get_file_extension() {
		return (string) get_post_meta( $this->ID, 'tva_file_extension', true );
	}

	/**
	 * Returns the file original name
	 *
	 * @return string
	 */
	public function get_original_name() {
		return (string) get_post_meta( $this->ID, 'tva_original_name', true );
	}


	/**
	 * Returns the file download counter
	 *
	 * @return int
	 */
	public function get_download_counter() {
		return (int) get_post_meta( $this->ID, 'tva_file_download_counter', true );
	}

	/**
	 * Increments the file download counter in cache
	 * Used to display the counter in the file list in dashboard
	 *
	 * @return void
	 */
	public function increment_download_counter() {
		update_post_meta( $this->ID, 'tva_file_download_counter', ( (int) $this->download_counter + 1 ) );
	}

	/**
	 * If exists, returns the product associated with the file
	 *
	 * @return \TVA\Product[]|null
	 */
	public function get_products() {
		return \TVA\Product::get_from_set( \TVD\Content_Sets\Set::get_for_object( $this->get_post_object(), $this->ID ), array( 'return_all' => true ) );
	}

	/**
	 * Returns File post object
	 *
	 * @return WP_Post
	 */
	public function get_post_object() {
		return get_post( $this->ID );
	}

	/**
	 * @return $this|void
	 */
	public function save() {
		if ( $this->ID ) {
			$post_id = wp_update_post( $this->data, true );
		} else {
			$post_id = wp_insert_post( $this->data, true );
		}

		if ( is_wp_error( $post_id ) ) {
			return $this;
		}

		if ( ! empty( $this->data['original_name'] ) ) {
			update_post_meta( $post_id, 'tva_original_name', $this->data['original_name'] );
		}
		if ( ! empty( $this->data['file_name'] ) ) {
			update_post_meta( $post_id, 'tva_file_name', $this->data['file_name'] );
		}
		if ( ! empty( $this->data['file_extension'] ) ) {
			update_post_meta( $post_id, 'tva_file_extension', $this->data['file_extension'] );
		}

		$this->data = get_post( $post_id, ARRAY_A );

		return $this;
	}

	/**
	 * Updates products access types
	 *
	 * @param array $new_ids
	 *
	 * @return $this
	 */
	public function update_products( $new_ids = [] ) {
		$old_ids = array_map( static function ( $product ) {
			return (int) $product->get_id();
		}, $this->products );

		$ids_to_add    = array_values( array_unique( array_diff( $new_ids, $old_ids ) ) );
		$ids_to_remove = array_values( array_unique( array_diff( $old_ids, $new_ids ) ) );

		$rule = \TVD\Content_Sets\Rule::factory( [
			'content_type' => 'post',
			'content'      => static::POST_TYPE,
			'field'        => \TVD\Content_Sets\Rule::FIELD_TITLE,
			'operator'     => \TVD\Content_Sets\Rule::OPERATOR_IS,
			'value'        => [ $this->ID ],
		] );

		if ( ! empty( $ids_to_add ) ) {
			foreach ( $ids_to_add as $product_id ) {
				$product      = new \TVA\Product( (int) $product_id );
				$content_sets = $product->get_content_sets();
				$content_set  = reset( $content_sets );

				if ( $content_set instanceof TVD\Content_Sets\Set ) {
					$content_set->add_static_rule( $rule )->update();
				}
			}
		}

		if ( ! empty( $ids_to_remove ) ) {
			foreach ( $ids_to_remove as $product_id ) {
				$product      = new \TVA\Product( (int) $product_id );
				$content_sets = $product->get_content_sets();
				$content_set  = reset( $content_sets );

				if ( $content_set instanceof TVD\Content_Sets\Set ) {
					$content_set->remove_rule( $rule )->remove_rule_value( $this->ID )->update();
				}
			}
		}

		return $this;
	}

	/**
	 * Deletes the file from the disk and also deletes the post created for the file
	 *
	 * @return bool
	 */
	public function delete() {

		$uploaded_file = $this->get_uploaded_file();
		if ( file_exists( $uploaded_file ) ) {
			@unlink( $uploaded_file );
		}

		return (bool) wp_delete_post( $this->ID, true );
	}

	/**
	 * Returns the uploaded file path
	 *
	 * @return string
	 */
	public function get_uploaded_file() {
		return static::get_upload_base() . $this->file_name . '.' . $this->file_extension;
	}

	/**
	 * Upload a file to the WordPress file system
	 *
	 * @param string $file_contents
	 *
	 * @return $this
	 */
	public function upload( $file_contents ) {
		$this->data['file_name'] = static::generate_unique_file_name( $this->data['original_name'], $this->data['file_extension'] );
		$file_name               = $this->data['file_name'] . '.' . $this->data['file_extension'];

		add_filter( 'upload_dir', [ __CLASS__, 'protected_folder' ], PHP_INT_MAX );
		$uploaded = wp_upload_bits( $file_name, null, $file_contents );
		remove_filter( 'upload_dir', [ __CLASS__, 'protected_folder' ], PHP_INT_MAX );

		if ( false === $uploaded['error'] ) {
			$this->save();
		}

		return $this;
	}

	/**
	 * Generates file download URL
	 * Used in ThriveApprentice plugin from the dynamic link shortcode
	 *
	 * @return string
	 */
	public function get_download_url() {

		if ( $this->ID === null ) {
			/**
			 * This can happen when the file post has been deleted
			 */
			return '#';
		}

		return add_query_arg( [
			static::DOWNLOAD_URL_QUERY_NAME => $this->ID,
		], home_url() );
	}

	/**
	 * @param int $resource_id
	 *
	 * @return string
	 */
	public function get_download_resource_url( $resource_id ) {
		return add_query_arg( [
			static::DOWNLOAD_RESOURCE_URL_QUERY_NAME => $resource_id,
		], home_url() );
	}

	/**
	 * Returns the post permalink of the file
	 * Used to send the user to this link when he doesn't have access to download the file
	 * Used in ThriveApprentice plugin
	 *
	 * @return string
	 */
	public function get_url() {
		return get_permalink( $this->ID );
	}

	/**
	 * Checks if the file is valid.
	 * A valid file is a file that has a post associated with it
	 * A valid file is a file that has a readable path. -> there exists a file on disk associated with the post
	 *
	 * @return bool
	 */
	public function is_valid() {
		if ( ! is_numeric( $this->ID ) ) {
			return false;
		}

		if ( ! is_readable( $this->get_uploaded_file() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'ID'               => $this->ID,
			'post_title'       => $this->post_title,
			'download_counter' => $this->download_counter,
			'original_name'    => $this->original_name,
			'file_extension'   => $this->file_extension,
			'file_type'        => $this->file_type,
			'admin_download'   => add_query_arg( [ 'tva-admin-download' => 1 ], $this->download_url ),
			'products'         => array_map( static function ( $product ) {
				return [
					'id'   => $product->get_id(),
					'name' => $product->get_name(),
				];
			}, $this->products ),
		];
	}

	/**
	 * @return array|int
	 */
	public static function get_items( $filters = [], $count = false ) {
		$posts = get_posts( wp_parse_args( $filters, [
			'post_type'   => static::POST_TYPE,
			'post_status' => 'publish',
		] ) );

		if ( true === $count ) {
			return count( $posts );
		}

		$data = [];

		foreach ( $posts as $post ) {
			$data[] = new static( $post->to_array() );
		}

		return $data;
	}

	/**
	 * Register file post type.
	 * Is called on init hook
	 *
	 * @return void
	 */
	public static function init_post_type() {
		register_post_type( static::POST_TYPE, [
			'labels'              => [
				'name'          => static::POST_TYPE_LABEL_PLURAL,
				'singular_name' => static::POST_TYPE_LABEL,
			],
			'publicly_queryable'  => true, //Needs to be queryable on front-end for products
			'public'              => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_ui'             => false,
			'exclude_from_search' => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'map_meta_cap'        => true,
		] );
	}

	public static function init_admin_hooks() {
		add_filter( 'tva_admin_localize', [ __CLASS__, 'get_localization' ] );

		add_filter( 'tvd_content_sets_get_content_types', [ __CLASS__, 'content_sets_get_content_types' ] );

		add_filter( 'tvd_content_sets_get_content_types_exceptions', [ __CLASS__, 'content_sets_get_content_types_exceptions' ] );

		add_filter( 'tva_resource_data_protected_file', static function ( $data ) {
			$file = new static( (int) $data['config']['post']['id'] );

			if ( ! $file->is_valid() ) {
				return $data;
			}

			return array_merge( [
				'extension' => $file->file_extension,
			], $data );
		}, 10, 2 );
	}

	/**
	 * Computes base upload directory
	 *
	 * @return string
	 */
	public static function get_upload_base() {
		$upload = wp_upload_dir();

		return trailingslashit( $upload['basedir'] ) . static::SUB_DIR . '/';
	}

	/**
	 * Create the .htaccess file to protect the upload folder from direct access
	 * Works only on apache web servers
	 *
	 * @return boolean|int
	 */
	public static function create_htaccess_file() {
		$base          = static::get_upload_base();
		$htaccess_file = $base . '.htaccess';

		if ( file_exists( $htaccess_file ) ) {
			return false;
		}

		if ( false === wp_mkdir_p( $base ) ) {
			return false;
		}

		return file_put_contents( $htaccess_file, 'Deny from all' );
	}

	/**
	 * Creates an index.html file inside the protected folder to check if the system is configured properly
	 *
	 * @return boolean|int
	 */
	public static function create_index_html() {
		$base       = static::get_upload_base();
		$index_file = $base . 'index.html';

		if ( file_exists( $index_file ) ) {
			return false;
		}

		if ( false === wp_mkdir_p( $base ) ) {
			return false;
		}

		return file_put_contents( $index_file, '' );
	}

	/**
	 * Returns the allowed files extensions
	 * Is based on WordPress functionality
	 *
	 * @return array
	 * @see wp_get_ext_types
	 */
	public static function get_allowed_extensions() {
		/**
		 * Retrieves the list of common file extensions and their types.
		 */
		$extension_types = wp_get_ext_types();
		/**
		 * We delete some unnecessary types
		 */
		unset( $extension_types['code'] );

		/**
		 * Flattens a 2 dimensional array
		 */
		return call_user_func_array( 'array_merge', array_values( $extension_types ) );
	}

	/**
	 * Returns localization data needed for UI
	 *
	 * @param array $data
	 *
	 * @return array[]
	 */
	public static function get_localization( $data = [] ) {
		$max_upload_size = wp_max_upload_size();
		if ( ! $max_upload_size ) {
			$max_upload_size = 0;
		}

		$data['files'] = [
			'items'         => static::get_items( [
				'posts_per_page' => TVA_Admin::ITEMS_PER_PAGE,
				'offset'         => 0,
			] ),
			'total'         => static::get_items( [ 'posts_per_page' => - 1 ], true ),
			'post_type'     => static::POST_TYPE,
			'errors'        => [
				'invalid_filetype'        => esc_html__( 'Sorry, this file type is not permitted for security reasons.', 'thrive-apprentice' ),
				'default_error'           => esc_html__( 'An error occurred in the upload. Please try again later.', 'thrive-apprentice' ),
				'file_exceeds_size_limit' => esc_html__( 'The file exceeds the maximum upload size for this site.', 'thrive-apprentice' ),
			],
			'upload_params' => [
				'file_data_name' => 'file_data',
				'filters'        => [
					'max_file_size' => $max_upload_size . 'b',
					'mime_types'    => [ [ 'extensions' => implode( ',', static::get_allowed_extensions() ) ] ],
				],
			],
		];

		return $data;
	}

	/**
	 * @param array $exceptions
	 *
	 * @return array
	 */
	public static function content_sets_get_content_types_exceptions( $exceptions = [] ) {

		$exceptions[ static::POST_TYPE ] = [
			[
				'value'    => '',
				'disabled' => true,
				'label'    => __( 'Select your field or taxonomy', 'thrive-apprentice' ),
			],
			[
				'value' => \TVD\Content_Sets\Rule::FIELD_TITLE,
				'label' => __( 'Title', 'thrive-apprentice' ),
			],
		];

		return $exceptions;
	}

	/**
	 * @param array $post_types
	 *
	 * @return array
	 */
	public static function content_sets_get_content_types( $post_types = [] ) {

		$post_types[ static::POST_TYPE ] = static::POST_TYPE_LABEL;

		return $post_types;
	}

	/**
	 * Callback for upload dir filter
	 * called from generate function
	 *
	 * @param array $upload
	 *
	 * @return array
	 */
	public static function protected_folder( $upload ) {
		$sub_dir = '/' . static::SUB_DIR;

		$upload['path']   = $upload['basedir'] . $sub_dir;
		$upload['url']    = $upload['baseurl'] . $sub_dir;
		$upload['subdir'] = $sub_dir;

		return $upload;
	}

	/**
	 * Generates a unique file name that will be stored in the wordpress upload folder.
	 * This unique file name is to protect the file from its original name
	 *
	 * @param string $basename
	 * @param string $extension
	 * @param int    $segments
	 * @param int    $segment_length
	 *
	 * @return string
	 */
	private static function generate_unique_file_name( $basename, $extension, $segments = 4, $segment_length = 4 ) {
		$raw    = strtoupper( md5( get_site_url() . $basename . $extension . time() ) );
		$length = $segments * $segment_length;
		$code   = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$code .= $raw[ $i ];
			if ( ( $i + 1 ) % $segment_length === 0 && $i !== $length - 1 ) {
				$code .= '-';
			}
		}

		return $code;
	}
}
