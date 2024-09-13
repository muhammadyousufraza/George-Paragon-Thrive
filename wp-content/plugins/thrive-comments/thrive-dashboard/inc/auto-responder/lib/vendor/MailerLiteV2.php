<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLiteV2 {

	/**
	 * @var null | string
	 */
	protected $api_key;

	/**
	 * @var Thrive_Dash_Api_MailerLiteV2_RestClient
	 */
	protected $rest_client;

	/**
	 * Thrive_Dash_Api_MailerLite constructor.
	 *
	 * @param null $api_key
	 *
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function __construct( $api_key = null ) {
		if ( is_null( $api_key ) ) {
			throw new Thrive_Dash_Api_MailerLite_MailerLiteSdkException( 'API key is not provided' );
		}

		$this->api_key = $api_key;

		$this->rest_client = new Thrive_Dash_Api_MailerLiteV2_RestClient(
			$this->get_base_url(),
			$api_key
		);
	}
	/**
	 * @return Thrive_Dash_Api_MailerLiteV2_Groups
	 */
	public function groups() {
		return new Thrive_Dash_Api_MailerLiteV2_Groups( $this->rest_client );
	}

	/**
	 * @return Thrive_Dash_Api_MailerLiteV2_Fields
	 */
	public function fields() {
		return new Thrive_Dash_Api_MailerLiteV2_Fields( $this->rest_client );
	}

	/**
	 * @return Thrive_Dash_Api_MailerLiteV2_Subscribers
	 */
	public function subscribers() {
		return new Thrive_Dash_Api_MailerLiteV2_Subscribers( $this->rest_client );
	}

	/**
	 * @return Thrive_Dash_Api_MailerLiteV2_Campaigns
	 */
	public function campaigns() {
		return new Thrive_Dash_Api_MailerLiteV2_Campaigns( $this->rest_client );
	}

	/**
	 * @return string
	 */
	public function get_base_url() {
		return Thrive_Dash_Api_MailerLiteV2_ApiConstants::BASE_URL;
	}

}