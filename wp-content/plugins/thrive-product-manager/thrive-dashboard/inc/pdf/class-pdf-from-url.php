<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * PDF from URL - generate
 *
 * Used in ThriveApprentice for certificate generation
 */
class TVD_PDF_From_URL {

	/**
	 * The name of the sub directory (relative to uploads) that is used to store PDF files
	 */
	const SUB_DIR = 'tve_pdfs';

	/**
	 * @var string
	 */
	private $url;

	/**
	 * Contains information about the PDF
	 * file_name, width, height
	 *
	 * @var array
	 */
	private $config;

	/**
	 * @param string $url
	 * @param array  $config
	 */
	public function __construct( $url, $config = [] ) {
		$this->url    = $url;
		$this->config = array_merge( [
			'file_name' => 'tve-pdf-file',
			'width'     => 1500,
			'height'    => 768,
		], $config );
	}

	/**
	 * Generates the actual PDF file.
	 * If something went wrong, returns an error message
	 *
	 * Called from thrive-apprentice when generating a certificate
	 *
	 * @return array|string[]
	 */
	public function generate() {
		$ttw_id = TD_TTW_Connection::get_instance()->is_connected() ? TD_TTW_Connection::get_instance()->ttw_id : 0;

		if ( empty( $ttw_id ) ) {
			return [
				'error' => 'PDF-API: Could not verify the user',
			];
		}

		if ( ! class_exists( 'TPM_License', false ) ) {
			return [
				'error' => 'PDF-API: Could not verify TPM',
			];
		}

		/**
		 * We send the license IDs to the PDF Generator website.
		 */
		$license_ids = array_filter( array_map( static function ( $license ) {
			if ( $license->has_tag( TVE_Dash_Product_LicenseManager::TVA_TAG ) ) {
				return $license->get_id();
			}

			return 0;
		}, TPM_License::get_saved_licenses() ) );


		$base      = static::get_upload_base();
		$file_name = $this->config['file_name'] . '.pdf';

		if ( is_readable( $base . $file_name ) ) {
			return [
				'url'  => static::get_upload_url() . $file_name,
				'file' => static::get_upload_base() . $file_name,
			];
		}

		if ( false === wp_mkdir_p( $base ) ) {
			return [
				'error' => 'PDF-API: Could not create the templates folder',
			];
		}

		$response = tve_dash_api_remote_post( static::get_endpoint(), [
			'headers' => [],
			'body'    => [
				'url'         => $this->url,
				'file_name'   => $this->config['file_name'],
				'width'       => $this->config['width'],
				'height'      => $this->config['height'],
				'home_url'    => home_url(),
				'ttw_id'      => $ttw_id,
				'ttw_license' => reset( $license_ids ),
			],
		] );

		$header = wp_remote_retrieve_header( $response, 'X-Thrive-File-Ok' );

		if ( $header !== 'ok' ) {
			return [
				'error'    => 'PDF-API: There was a problem creating the certificate. Status code:' . $response->get_error_code() . '. Error message:' . $response->get_error_message(),
				'response' => wp_remote_retrieve_body( $response ),
			];
		}

		$response = wp_remote_retrieve_body( $response );

		add_filter( 'upload_dir', [ __CLASS__, 'pdf_folder' ], PHP_INT_MAX );
		$is_uploaded = wp_upload_bits( $file_name, null, $response );
		remove_filter( 'upload_dir', [ __CLASS__, 'pdf_folder' ], PHP_INT_MAX );

		return $is_uploaded;
	}

	/**
	 * Callback for upload dir filter
	 * called from generate function
	 *
	 * @param array $upload
	 *
	 * @return array
	 */
	public static function pdf_folder( $upload ) {
		$sub_dir = '/' . static::SUB_DIR;

		$upload['path']   = $upload['basedir'] . $sub_dir;
		$upload['url']    = $upload['baseurl'] . $sub_dir;
		$upload['subdir'] = $sub_dir;

		return $upload;
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
	 * Computes the upload URL
	 *
	 * @return string
	 */
	public static function get_upload_url() {
		$upload = wp_upload_dir();

		return $upload['baseurl'] . '/' . static::SUB_DIR . '/';
	}

	/**
	 * Removes PDF files by prefix
	 *
	 * @param string $prefix
	 *
	 * @return void
	 */
	public static function delete_by_prefix( $prefix = '' ) {
		if ( empty( $prefix ) ) {
			return;
		}
		$base = static::get_upload_base();

		if ( is_dir( $base ) ) {
			$files = preg_grep( '~^' . $prefix . '.*\.pdf$~', scandir( $base ) );
			foreach ( $files as $file_name ) {
				if ( file_exists( $base . $file_name ) ) {
					@unlink( $base . $file_name );
				}
			}
		}
	}

	/**
	 * Computes the endpoint for PDF generation
	 *
	 * @return string
	 */
	private static function get_endpoint() {
		return tvd_get_service_endpoint() . '/pdf-from-url';
	}
}
