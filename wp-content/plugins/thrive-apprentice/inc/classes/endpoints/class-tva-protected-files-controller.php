<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TVA_Protected_Files_Controller extends TVA_REST_Controller {
	/**
	 * @var string
	 */
	public $base = 'protected-files';

	public function register_routes() {
		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/check-file-protection', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'check_file_protection_functionality' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/(?P<id>[\d]+)', [
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
			[
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'id'           => [
						'type'     => 'integer',
						'required' => true,
					],
					'post_title'   => [
						'type'     => 'string',
						'required' => true,
					],
					'products_ids' => [
						'type'     => 'array',
						'required' => false,
					],
				],
			],
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [],
			],
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'name'         => [
						'type'     => 'string',
						'required' => true,
					],
					'label'        => [
						'type'     => 'string',
						'required' => true,
					],
					'products_ids' => [
						'type'     => 'array',
						'required' => false,
					],
				],
			],
		] );
	}

	/**
	 * Request that checks if the system is working properly
	 * Checks the status code of the contol file
	 *
	 * @return \WP_REST_Response
	 */
	public function check_file_protection_functionality() {
		$is_ok = true;
		$code  = 0;

		if ( tve_dash_is_apache() ) {
			/**
			 * Create the .htaccess file for apache web servers
			 */
			TVA_Protected_File::create_htaccess_file();
		}

		TVA_Protected_File::create_index_html();

		$upload = wp_upload_dir();

		if ( ! file_exists( trailingslashit( $upload['basedir'] ) . TVA_Protected_File::SUB_DIR . '/index.html' ) ) {
			$is_ok = false;
		}

		if ( $is_ok ) {
			$request = wp_remote_get( $upload['baseurl'] . '/' . TVA_Protected_File::SUB_DIR . '/index.html', [ 'sslverify' => false ] );

			$code = wp_remote_retrieve_response_code( $request );

			$is_ok = $code !== 200;
		}

		return new \WP_REST_Response( [
			'code' => $code,
			'ok'   => (int) $is_ok,
		] );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function delete_item( $request ) {

		$file = new TVA_Protected_File( (int) $request->get_param( 'id' ) );
		$file->delete();

		return new \WP_REST_Response( [] );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function get_item( $request ) {
		return new \WP_REST_Response( new TVA_Protected_File( (int) $request->get_param( 'id' ) ) );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function update_item( $request ) {
		$product_ids = array_map( 'intval', array_values( (array) $request->get_param( 'products_ids' ) ) );

		$file = new TVA_Protected_File( [
			'post_title' => (string) $request->get_param( 'post_title' ),
			'ID'         => (int) $request->get_param( 'id' ),
		] );

		$file->save()->update_products( $product_ids );

		return new \WP_REST_Response( $file );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$editor_page = (int) $request->get_param( 'tar_editor_page' );
		$search      = (string) $request->get_param( 'search' );
		$products    = array_map( 'intval', (array) $request->get_param( 'products' ) );

		$filters = [];
		if ( ! empty( $search ) ) {
			$filters['s'] = trim( $search );
		}

		if ( ! empty( $products ) ) {
			$filters['meta_query'] = [
				'relation' => 'OR',
			];

			foreach ( $products as $product_id ) {
				if ( $product_id === 0 ) {
					$filters['meta_query'][] = [
						'key'     => 'thrive_content_set',
						'value'   => 'a:0:{}',
						'compare' => '=',
					];
				} else {
					$product      = new \TVA\Product( $product_id );
					$content_sets = $product->get_content_sets();

					$set = reset( $content_sets );

					$filters['meta_query'][] = [
						'key'     => 'thrive_content_set',
						'value'   => ';i:' . $set->ID . ';',
						'compare' => 'LIKE',
					];
				}
			}
		}

		$items = TVA_Protected_File::get_items( array_merge( $filters, [
			'posts_per_page' => (int) $request->get_param( 'number' ),
			'offset'         => (int) $request->get_param( 'offset' ),
		] ) );
		$total = TVA_Protected_File::get_items( array_merge( $filters, [ 'posts_per_page' => - 1 ] ), true );

		if ( $editor_page === 1 ) {
			$return = [];
			foreach ( $items as $item ) {
				$return[] = [
					'id'    => $item->ID,
					'value' => $item->ID,
					'label' => $item->post_title,
				];
			}
		} else {
			$return = [
				'items' => $items,
				'total' => $total,
			];
		}

		return new \WP_REST_Response( $return );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function create_item( $request ) {
		$base = TVA_Protected_File::get_upload_base();

		// this must mean that the uploaded file is too large
		if ( empty( $_FILES ) ) {
			return new \WP_REST_Response( 'Missing file, or file is too large', 400 );
		}

		if ( ! empty( $_FILES['file_data']['error'] ) ) {
			return new \WP_REST_Response( 'Error uploading file', 500 );
		}

		$info = pathinfo( $_FILES['file_data']['name'] );

		if ( empty( $info['extension'] ) || empty( $info['filename'] ) ) {
			/* something is wrong here */
			return new \WP_REST_Response( 'Invalid file name', 400 );
		}

		if ( ! in_array( strtolower( $info['extension'] ), TVA_Protected_File::get_allowed_extensions() ) ) {
			return new \WP_REST_Response( 'Invalid file extension', 400 );
		}

		if ( filesize( $_FILES['file_data']['tmp_name'] ) > wp_max_upload_size() ) {
			/**
			 * Filesize security check
			 */
			return new \WP_REST_Response( 'FILE: The selected file is larger than the allowed file size for this website', 400 );
		}

		if ( false === wp_mkdir_p( $base ) ) {
			return new \WP_REST_Response( 'FILE: Could not create the protected folder', 401 );
		}

		if ( tve_dash_is_apache() ) {
			/**
			 * Create the .htaccess file for apache web servers
			 */
			TVA_Protected_File::create_htaccess_file();
		}

		$file = new TVA_Protected_File( [
			'post_title'     => $request->get_param( 'label' ),
			'original_name'  => trim( $info['filename'] ),
			'file_extension' => strtolower( trim( $info['extension'] ) ),
		] );

		$product_ids = array_map( 'intval', array_values( (array) $request->get_param( 'products_ids' ) ) );

		$file->upload( file_get_contents( $_FILES['file_data']['tmp_name'] ) )->update_products( $product_ids );

		return new \WP_REST_Response( $file );
	}
}
