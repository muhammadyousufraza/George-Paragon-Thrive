<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Design_Packs;


use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Rest extends \WP_REST_Controller {
	const REST_NAMESPACE = 'thrive-design-packs/v1';


	public function register_routes() {
		register_rest_route( static::REST_NAMESPACE, '/export_item', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'export_item' ],
				'permission_callback' => [ Main::class, 'has_access' ],
				'args'                => [
					'zip'          => Main::get_rest_optional_string_arg_data(),
					'content_id'   => Main::get_rest_integer_arg_data(),
					'content_type' => Main::get_rest_string_arg_data(),
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, '/import',
			[
				[
					'methods'             => WP_REST_Server::ALLMETHODS,
					'callback'            => [ __CLASS__, 'handle_import' ],
					'permission_callback' => [ Main::class, 'has_access' ],
					'args'                => [
						'id'     => Main::get_rest_integer_arg_data(),
						'action' => Main::get_rest_string_arg_data(),
					],
				],
			]
		);

		$pages_args = [
			'p'      => Main::get_rest_integer_arg_data( false ),
			'limit'  => Main::get_rest_integer_arg_data( false ),
			'search' => Main::get_rest_optional_string_arg_data(),
		];

		register_rest_route( static::REST_NAMESPACE, '/landing_pages', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_landing_pages' ],
				'permission_callback' => [ Main::class, 'has_access' ],
				'args'                => $pages_args,
			],
		] );


		register_rest_route( static::REST_NAMESPACE, '/pages', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_pages' ],
				'permission_callback' => [ Main::class, 'has_access' ],
				'args'                => $pages_args,
			],
		] );

		register_rest_route( static::REST_NAMESPACE, '/posts', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_posts' ],
				'permission_callback' => [ Main::class, 'has_access' ],
				'args'                => [
					'p'      => Main::get_rest_integer_arg_data( false ),
					'limit'  => Main::get_rest_integer_arg_data( false ),
					'cat'    => [
						'type'     => 'array',
						'required' => false,
					],
					'tags'   => [
						'type'     => 'array',
						'required' => false,
					],
					'search' => Main::get_rest_optional_string_arg_data(),
				],
			],
		] );


		register_rest_route( static::REST_NAMESPACE, '/skins', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_skins' ],
				'permission_callback' => [ Main::class, 'has_access' ],
			],
		] );
	}

	/**
	 * Export an item
	 *
	 * @throws \Exception
	 */
	public static function export_item( $request ): WP_REST_Response {
		/**
		 * @param $zip          - use an existing zip file
		 * @param $content_id   - item id that should  be exported
		 * @param $content_type - item's type, so we know how to handle the export properly
		 */
		$zip          = $request->get_param( 'zip' ) ?? '';
		$content_id   = $request->get_param( 'content_id' );
		$content_type = $request->get_param( 'content_type' );

		$handler = new Export( $zip, empty( $zip ) );

		$download_url = $handler->export_item( $content_id, $content_type );

		return new WP_REST_Response( [ 'zip' => $handler->zip_filename, 'download_url' => $download_url ] );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public static function get_landing_pages( $request ): WP_REST_Response {
		/**
		 * @param $pagination - page number
		 * @param $limit      - query limit
		 */
		header( 'Content-type: text/html' );
		$pagination = $request->get_param( 'pag' ) ?: 0;
		$limit      = $request->get_param( 'limit' ) ?: null;
		$search     = $request->get_param( 'search' ) ?: '';
		$args       = compact( 'pagination', 'limit', 'search' );

		return new WP_REST_Response( Data::get_landing_pages( $args ), 200 );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function get_pages( \WP_REST_Request $request ): WP_REST_Response {
		/**
		 * @param $pagination - page number
		 * @param $limit      - query limit
		 */
		header( 'Content-type: text/html' );
		$pagination = $request->get_param( 'pag' ) ?: 0;
		$limit      = $request->get_param( 'limit' ) ?: null;
		$search     = $request->get_param( 'search' ) ?: '';
		$args       = compact( 'pagination', 'limit', 'search' );

		return new WP_REST_Response( Data::get_tar_posts( 'page', $args ) );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public static function get_posts( $request ): WP_REST_Response {
		/**
		 * @param $pagination - page number
		 * @param $categories - categories which a post should belong to
		 * @param $tags       - tags which a post should have
		 * @param $limit      - query limit
		 */
		header( 'Content-type: text/html' );
		$pagination = $request->get_param( 'pag' ) ?: 0;
		$categories = $request->get_param( 'cat' ) ?: [];
		$tags       = $request->get_param( 'tags' ) ?: [];
		$limit      = $request->get_param( 'limit' ) ?: null;
		$search     = $request->get_param( 'search' ) ?: '';
		$args       = compact( 'pagination', 'categories', 'tags', 'limit', 'search' );

		return new WP_REST_Response( Data::get_tar_posts( 'post', $args ) );
	}

	/**
	 * @return WP_REST_Response
	 */
	public static function get_skins(): WP_REST_Response {
		return new WP_REST_Response( \Thrive_Skin_Taxonomy::get_all() );
	}

	public static function handle_import( $request ): WP_REST_Response {
		/**
		 * @param $zip_id    - zip attachment id
		 * @param $action    - what should we do with the zip
		 * @param $filename  - which subitem we should import
		 * @param $file_type - type of the subitem we are importing
		 */
		$zip_id         = $request->get_param( 'id' ) ?: 0;
		$action         = $request->get_param( 'action' ) ?: 'validate';
		$filename       = $request->get_param( 'filename' ) ?: '';
		$file_type      = $request->get_param( 'file_type' ) ?: '';
		$tcb_symbol_map = $request->get_param( 'tcb_symbol_map' ) ?: [];

		$data = compact( 'zip_id', 'filename', 'file_type', 'tcb_symbol_map' );

		return new WP_REST_Response( Import::{$action}( $data ) );
	}
}
