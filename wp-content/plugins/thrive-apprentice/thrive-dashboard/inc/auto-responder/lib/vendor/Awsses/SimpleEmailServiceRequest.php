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
 * SimpleEmailServiceRequest PHP class
 *
 * @link    https://github.com/daniel-zahariev/php-aws-ses
 * @package AmazonSimpleEmailService
 */
final class Thrive_Dash_Api_Awsses_SimpleEmailServiceRequest {

	private $ses, $verb, $parameters = array();
	public               $response;

	/**
	 * For Signature V4 https://docs.aws.amazon.com/general/latest/gr/sigv4_signing.html
	 */
	const SERVICE   = 'email';
	const DOMAIN    = 'amazonaws.com';
	const ALGORITHM = 'AWS4-HMAC-SHA256';

	private $_aws_key;
	private $_aws_secret;
	private $_region;

	private $_host;
	private $_endpoint;

	private $_amz_date;
	private $_date;

	private $_method;
	private $_body;

	private $_headers;
	private $_query_parameters;

	/**
	 * Constructor
	 *
	 * @param string $ses  The SimpleEmailService object making this request
	 * @param string $verb HTTP verb
	 *
	 * @return void
	 */
	function __construct( $ses, $verb, $region = 'eu-west-1' ) {
		$this->ses      = $ses;
		$this->verb     = $verb;
		$this->response = (object) array( 'body' => '', 'code' => 0, 'error' => false );

		/**
		 * Signature V4
		 */
		$this->_amz_date = gmdate( 'Ymd\THis\Z' );
		$this->_date     = gmdate( 'Ymd' );

		$this->_aws_key    = $this->ses->getAccessKey();
		$this->_aws_secret = $this->ses->getSecretKey();
		$this->_region     = $region; // Used in vendor class as well
		$this->_method     = $verb;

		$this->_host     = self::SERVICE . '.' . $this->_region . '.' . self::DOMAIN;
		$this->_endpoint = 'https://' . self::SERVICE . '.' . $this->_region . '.' . self::DOMAIN;
	}

	/**
	 * Set request parameter
	 *
	 * @param string  $key     Key
	 * @param string  $value   Value
	 * @param boolean $replace Whether to replace the key if it already exists (default true)
	 *
	 * @return Thrive_Dash_Api_Awsses_SimpleEmailServiceRequest $this
	 */
	public function setParameter( $key, $value, $replace = true ) {
		if ( ! $replace && isset( $this->parameters[ $key ] ) ) {
			$temp                     = (array) ( $this->parameters[ $key ] );
			$temp[]                   = $value;
			$this->parameters[ $key ] = $temp;
		} else {
			$this->parameters[ $key ] = $value;
		}

		return $this;
	}

	/**
	 * Build one of the f*!#ing keys for v4 signature
	 *
	 * @return string
	 */
	private function _generateSignatureKey() {

		$date_h    = hash_hmac( 'sha256', $this->_date, 'AWS4' . $this->_aws_secret, true );
		$region_h  = hash_hmac( 'sha256', $this->_region, $date_h, true );
		$service_h = hash_hmac( 'sha256', self::SERVICE, $region_h, true );
		$signing_h = hash_hmac( 'sha256', 'aws4_request', $service_h, true );

		return $signing_h;
	}

	/**
	 * Create headers & body for v4 signature
	 */
	private function _generateDataSignature() {

		$parameters     = $this->parameters;
		$this->_headers = array();
		$canonical_uri  = '/';
		ksort( $parameters );
		$query_parameters  = '';
		$canonical_headers = '';
		$signed_headers    = '';
		$qp                = http_build_query( $parameters, '', '&', PHP_QUERY_RFC3986 );

		if ( $parameters['Action'] == "SendRawEmail" || $parameters['Action'] == "SendEmail" ) {
			$this->_body                    = $qp;
			$canonical_headers              .= 'content-type:' . 'application/x-www-form-urlencoded' . "\n";
			$signed_headers                 .= 'content-type;';
			$this->_headers['Content-Type'] = 'application/x-www-form-urlencoded';
		} else {
			$query_parameters = $qp;
		}

		$canonical_headers .= 'host:' . $this->_host . "\n" . 'x-amz-date:' . $this->_amz_date . "\n";
		$signed_headers    .= 'host;x-amz-date';
		$payload_hash      = hash( 'sha256', $this->_body );

		// task 1 https://docs.aws.amazon.com/general/latest/gr/sigv4-create-canonical-request.html
		$canonical_request = $this->_method . "\n" . $canonical_uri . "\n" . $query_parameters . "\n" . $canonical_headers . "\n" . $signed_headers . "\n" . $payload_hash;

		// task 2 https://docs.aws.amazon.com/general/latest/gr/sigv4-create-string-to-sign.html
		$credential_scope = $this->_date . '/' . $this->_region . '/' . self::SERVICE . '/aws4_request';
		$string_to_sign   = self::ALGORITHM . "\n" . $this->_amz_date . "\n" . $credential_scope . "\n" . hash( 'sha256', $canonical_request );

		// task 3 https://docs.aws.amazon.com/general/latest/gr/sigv4-calculate-signature.html
		$signing_key = $this->_generateSignatureKey();

		// task 4 https://docs.aws.amazon.com/general/latest/gr/sigv4-add-signature-to-request.html
		$signature                       = hash_hmac( 'sha256', $string_to_sign, $signing_key );
		$this->_headers['Authorization'] = self::ALGORITHM . ' Credential=' . $this->_aws_key . '/' . $credential_scope . ', SignedHeaders=' . $signed_headers . ', Signature=' . $signature;
		$this->_headers['x-amz-date']    = $this->_amz_date;
		$this->_query_parameters         = $query_parameters;
	}

	/**
	 * Get the response
	 *
	 * @return WP_Error|mixed
	 */
	public function getResponse() {

		$params = array();
		foreach ( $this->parameters as $var => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $v ) {
					$params[] = $var . '=' . $this->customUrlEncode( $v );
				}
			} else {
				$params[] = $var . '=' . $this->customUrlEncode( $value );
			}
		}

		sort( $params, SORT_STRING );

		$url = 'https://' . $this->ses->getHost() . '/';
		$this->_generateDataSignature();

		switch ( $this->verb ) {
			case 'GET':
				$fn = 'tve_dash_api_remote_get';
				break;
			default:
				$fn = 'tve_dash_api_remote_post';
				break;
		}

		$this->response = $fn( $url, array(
			'body'      => $this->_body,
			'headers'   => $this->_headers,
			'timeout'   => 15,
			'sslverify' => false,
		) );


		return $this->response;
	}

	/**
	 * Contributed by afx114
	 * URL encode the parameters as per http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/index.html?Query_QueryAuth.html
	 * PHP's rawurlencode() follows RFC 1738, not RFC 3986 as required by Amazon. The only difference is the tilde (~), so convert it back after rawurlencode
	 * See: http://www.morganney.com/blog/API/AWS-Product-Advertising-API-Requires-a-Signed-Request.php
	 *
	 * @param string $var String to encode
	 *
	 * @return string
	 */
	private function customUrlEncode( $var ) {
		return str_replace( '%7E', '~', rawurlencode( $var ) );
	}
}
