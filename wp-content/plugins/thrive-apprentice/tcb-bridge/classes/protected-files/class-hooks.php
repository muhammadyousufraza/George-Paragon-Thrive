<?php

namespace TVA\Architect\Protected_Files;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Hooks class
 *
 * Callback for actions & filters
 */
class Hooks {

	public static function init() {
		add_filter( 'tcb_inline_shortcodes', [ __CLASS__, 'inline_shortcodes' ] );
		add_filter( 'tcb_dynamiclink_data', [ __CLASS__, 'dynamic_links' ] );

		add_filter( 'tcb_main_frame_localize', [ __CLASS__, 'main_frame_localize' ], 11 );

		add_action( 'template_redirect', [ __CLASS__, 'file_download_redirect' ], 1, 0 );
		add_action( 'template_redirect', [ __CLASS__, 'file_resource_download_redirect' ], 1, 0 );

		add_action( 'thrive_theme_template_meta', [ __CLASS__, 'thrive_theme_template_meta' ], PHP_INT_MAX );

		add_filter( 'tcb_content_allowed_shortcodes', [ __CLASS__, 'content_allowed_shortcodes_filter' ] );
	}

	/**
	 * Localization data for protected files main frame
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function main_frame_localize( $data = [] ) {
		$data['protected_files'] = [
			'route' => tva_get_route_url( 'protected-files' ),
			'items' => [], //Here we cache items for select2 widget that is used in TAR for searching for protected files
		];

		return $data;
	}

	/**
	 * Inline shortcodes for Protected Files
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public static function inline_shortcodes( $shortcodes = [] ) {

		return array_merge_recursive( [
			'Protected file' => [
				[
					'option' => 'Protected file', //This is a placeholder value
					'value'  => 'tva_protected_file_link',
				],
			], //This will be filled dynamically
		], $shortcodes );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function dynamic_links( $data = [] ) {
		$data['Protected file'] = [
			'links'     => [
				[],//placeholder stuff
			],
			'shortcode' => 'tva_protected_file_dynamic_link',
		];

		return $data;
	}

	/**
	 * In case ThriveTheme is active and the user doesn't have access to download the file, show the 404 template
	 *
	 * @param array $meta
	 *
	 * @return array
	 */
	public static function thrive_theme_template_meta( $meta = [] ) {

		if ( \Thrive_Theme::is_active() ) {
			if ( ! empty( $_GET[ \TVA_Protected_File::DOWNLOAD_URL_QUERY_NAME ] ) && is_numeric( $_GET[ \TVA_Protected_File::DOWNLOAD_URL_QUERY_NAME ] ) ) {
				$file = new \TVA_Protected_File( (int) $_GET[ \TVA_Protected_File::DOWNLOAD_URL_QUERY_NAME ] );

				if ( ( empty( $file->get_products() ) && ! \TVA_Product::has_access() ) || ! is_user_logged_in() ) {
					$meta['primary_template']   = THRIVE_ERROR404_TEMPLATE;
					$meta['secondary_template'] = '';
					$meta['variable_template']  = '';
				}
			}

			if ( ! empty( $_GET[ \TVA_Protected_File::DOWNLOAD_RESOURCE_URL_QUERY_NAME ] ) && is_numeric( $_GET[ \TVA_Protected_File::DOWNLOAD_RESOURCE_URL_QUERY_NAME ] ) ) {
				$resource      = \TVA_Resource::one( (int) $_GET[ \TVA_Protected_File::DOWNLOAD_RESOURCE_URL_QUERY_NAME ] );
				$resource_post = get_post( $resource->id );

				if ( $resource_post instanceof \WP_Post && ! empty( $resource_post->post_parent ) ) {
					$post_parent = \TVA_Post::factory( get_post( $resource_post->post_parent ) );
					if ( $post_parent->get_the_post() instanceof \WP_Post ) {
						$has_access = tva_access_manager()->has_access_to_object( get_post( $resource_post->post_parent ) );

						if ( ! $has_access ) {
							$meta['primary_template']   = THRIVE_ERROR404_TEMPLATE;
							$meta['secondary_template'] = '';
							$meta['variable_template']  = '';
						}
					}
				}
			}

		}

		return $meta;
	}

	public static function file_download_redirect() {

		if ( ! empty( $_GET[ \TVA_Protected_File::DOWNLOAD_URL_QUERY_NAME ] ) && is_numeric( $_GET[ \TVA_Protected_File::DOWNLOAD_URL_QUERY_NAME ] ) ) {

			$file = new \TVA_Protected_File( (int) $_GET[ \TVA_Protected_File::DOWNLOAD_URL_QUERY_NAME ] );

			/**
			 * If the file is not part of any product we always show the 404 page
			 */
			if ( empty( $file->get_products() ) && ! \TVA_Product::has_access() ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( 404 );
				exit();
			}

			if ( ! $file->is_valid() ) {
				wp_redirect( $file->url );

				exit();
			}

			$file_with_path = $file->get_uploaded_file();
			$allow_download = is_readable( $file_with_path ) && tva_access_manager()->has_access_to_object( $file->get_post_object() );
			if ( ! $allow_download ) {
				$url = $file->url;
				/**
				 * If the user do not has permission to download the file and the "ret" query string is present, pass it to the file page
				 */
				if ( ! empty( $_GET['ret'] ) ) {
					$url = add_query_arg( [ 'ret' => $_GET['ret'] ], $url );
				}

				wp_redirect( $url );

				exit();
			}

			$is_admin_action = ! empty( $_GET['tva-admin-download'] ) && (int) $_GET['tva-admin-download'] === 1;

			Main::download( $file, ! $is_admin_action );
		}
	}

	/**
	 * File download as a resource
	 *
	 * @return void
	 */
	public static function file_resource_download_redirect() {
		if ( ! empty( $_GET[ \TVA_Protected_File::DOWNLOAD_RESOURCE_URL_QUERY_NAME ] ) && is_numeric( $_GET[ \TVA_Protected_File::DOWNLOAD_RESOURCE_URL_QUERY_NAME ] ) ) {
			$resource = \TVA_Resource::one( (int) $_GET[ \TVA_Protected_File::DOWNLOAD_RESOURCE_URL_QUERY_NAME ] );

			if ( is_array( $resource->config ) && ! empty( $resource->config['post']['post_type'] ) && $resource->config['post']['post_type'] === \TVA_Protected_File::POST_TYPE ) {
				$resource_post = get_post( $resource->id );

				if ( ! empty( $resource_post->post_parent ) ) {
					$post_parent = \TVA_Post::factory( get_post( $resource_post->post_parent ) );
					if ( $post_parent->get_the_post() instanceof \WP_Post ) {
						$has_access = tva_access_manager()->has_access_to_object( get_post( $resource_post->post_parent ) );

						if ( ! $has_access ) {
							global $wp_query;
							$wp_query->set_404();
							status_header( 404 );
							get_template_part( 404 );
							exit();
						}
					}
				}

				$file = new \TVA_Protected_File( (int) $resource->config['post']['id'] );

				if ( ! $file->is_valid() ) {
					wp_redirect( get_permalink( $resource->lesson_id ) );

					exit();
				}

				Main::download( $file );
			}
		}
	}

	/**
	 * Needed for rendering shortcodes in editor page
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public static function content_allowed_shortcodes_filter( $shortcodes = [] ) {

		if ( is_editor_page_raw( true ) ) {
			$shortcodes = array_merge(
				$shortcodes,
				Shortcodes::get()
			);
		}

		return $shortcodes;
	}
}
