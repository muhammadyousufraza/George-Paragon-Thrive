<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_SendLayer {
	protected $apiUrl = "https://console.sendlayer.com/api/v1";

	/**
	 * @var null|string
	 */
	protected $apiKey;

	/**
	 * @param string|null $apiKey
	 * @param string      $apiEndpoint
	 * @param string      $apiVersion
	 * @param bool        $ssl
	 */
	public function __construct( $apiKey = null, $apiEndpoint = 'https://console.sendlayer.com/api', $apiVersion = 'v1', $ssl = true ) {
		$this->apiKey = $apiKey;
		$this->apiUrl = $apiEndpoint . "/" . $apiVersion;
	}

	/**
	 *  This function allows the sending of a fully formed message
	 *
	 * @param array $postData
	 * @param array $postFiles
	 *
	 * @throws Exceptions\MissingRequiredMIMEParameters
	 */
	public function sendMessage( $postData, $postFiles = array() ) {
		if ( ! empty( $postData ) && is_array( $postData ) ) {
			$body = [
				"From"        => [
					"name"  => $postData["name"],
					"email" => $postData["from"],
				],
				"To"          => $postData["to"],
				"Subject"     => $postData["subject"],
				"ContentType" => "HTML",
				"HTMLContent" => $postData["html"],
			];

			if ( ! empty( $postData['tags'] ) ) {
				$body["Tags"] = $postData['tags'];
			}

			// Set the request headers
			$headers = [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->apiKey,
			];

			// Make the API request
			$response = wp_remote_post( $this->apiUrl . "/email", array(
				'headers' => $headers,
				'body'    => json_encode( $body ),
			) );


			// Check for errors
			if ( is_wp_error( $response ) ) {
				throw new \Exception( 'SendLayer API request error: ' . $response->get_error_message() );
			}

			// Check for a successful response
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( isset( $data["Errors"] ) ) {
				$errors = $data["Errors"];

				$message = "SendLayer API error. ";
				foreach ( $errors as $error ) {
					$message .= $error['Message'] . " ";
				}

				throw new \Exception( $message );
			}

			if ( isset( $response['response']['code'] ) && $response['response']['code'] !== 200 ) {
				if ( isset( $response['response']['message'] ) ) {
					throw new \Exception( 'SendLayer API error: ' . $response['response']['message'] );
				}
			}

			if ( isset( $response['response']['code'] ) && $response['response']['code'] === 200 ) {
				return $data;
			}
		}

		throw new \Exception( 'Provided data is not valid!' );
	}
}
