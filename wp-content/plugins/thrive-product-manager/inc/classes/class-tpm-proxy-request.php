<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TPM_Proxy_Request
 * Decorator for TPM_Request
 */
class TPM_Proxy_Request {

	const URL      = 'http://service-api.thrivethemes.com';
	const API_PASS = '!!@#ThriveIsTheBest123$$@#';
	const API_KEY  = '@(#$*%)^SDFKNgjsdi870234521SADBNC#';

	protected $secret_key = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

	/** @var TPM_Request */
	protected $request;

	public function __construct( TPM_Request $request ) {

		$this->request = $request;
	}

	public function execute( $route ) {

		// Allow bypassing proxy server
		if ( defined( 'TPM_BYPASS_PROXY' ) && TPM_BYPASS_PROXY ) {
			return $this->request->execute();
		}

		$params['body']    = $this->request->get_body();
		$params['headers'] = $this->request->get_headers();
		$params['url']     = $this->request->get_url();
		$params['pw']      = self::API_PASS;

		$headers = array(
			'X-Thrive-Authenticate' => $this->_buildAuthString( $params ),
		);

		$args = array(
			'headers'   => $headers,
			'body'      => $params,
			'timeout'   => 30,
			'sslverify' => false,
		);

		$url = add_query_arg( array(
			'p' => $this->_calc_hash( $params ),
		), trim( $this->_get_url(), '/' ) . '/' . ltrim( $route, '/' ) );

		return wp_remote_post( $url, $args );
	}

	protected function _get_url() {

		if ( defined( 'TPM_DEBUG' ) && TPM_DEBUG === true && defined( 'TVE_CLOUD_URL' ) ) {
			return TVE_CLOUD_URL;
		}

		return self::URL;
	}

	protected function _calc_hash( $params ) {

		return md5( $this->secret_key . serialize( $params ) . $this->secret_key );
	}

	protected function _buildAuthString( $data = null ) {
		$string = '';

		foreach ( $data as $field => $value ) {
			if ( is_array( $value ) ) {
				$value = serialize( $value );
			}
			$string .= $field . '=' . $value;
			$string .= '|' . self::API_KEY . '|';
		}

		return md5( $string );
	}
}
