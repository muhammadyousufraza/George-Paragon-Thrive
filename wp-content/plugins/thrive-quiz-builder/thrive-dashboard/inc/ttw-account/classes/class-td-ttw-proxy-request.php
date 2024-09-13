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
 * Class TD_TTW_Proxy_Request
 * Decorator for TD_TTW_Request
 */
class TD_TTW_Proxy_Request {

	const URL = 'http://service-api.thrivethemes.com';

	const API_PASS = '!!@#ThriveIsTheBest123$$@#';

	const API_KEY = '@(#$*%)^SDFKNgjsdi870234521SADBNC#';

	protected $secret_key = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

	/** @var TD_TTW_Request */
	protected $request;

	/**
	 * TD_TTW_Proxy_Request constructor.
	 *
	 * @param TD_TTW_Request $request
	 */
	public function __construct( TD_TTW_Request $request ) {

		$this->request = $request;
	}

	/**
	 * Execute the request
	 *
	 * @param string $route
	 *
	 * @return array|WP_Error
	 */
	public function execute( $route ) {

		// Allow bypassing proxy server
		if ( defined( 'TPM_BYPASS_PROXY' ) && TPM_BYPASS_PROXY ) {
			return $this->request->execute();
		}

		$params = array(
			'body'    => $this->request->get_body(),
			'headers' => $this->request->get_headers(),
			'url'     => $this->request->get_url(),
			'pw'      => self::API_PASS,
		);

		$headers = array(
			'X-Thrive-Authenticate' => $this->_build_auth_string( $params ),
		);

		$args = array(
			'headers'   => $headers,
			'body'      => $params,
			'timeout'   => 30,
			'sslverify' => false,
		);

		$url = add_query_arg(
			array(
				'p' => $this->_calc_hash( $params ),
			),
			trim( $this->_get_url(), '/' ) . '/' . ltrim( $route, '/' )
		);

		return wp_remote_post( $url, $args );
	}

	/**
	 * @return string
	 */
	protected function _get_url() {

		if ( defined( 'TPM_DEBUG' ) && TPM_DEBUG === true && defined( 'TVE_CLOUD_URL' ) ) {
			return TVE_CLOUD_URL;
		}

		return self::URL;
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	protected function _calc_hash( $params ) {

		return md5( $this->secret_key . serialize( $params ) . $this->secret_key );
	}

	/**
	 * Create an auth string fro the request
	 *
	 * @param null $data
	 *
	 * @return string
	 */
	protected function _build_auth_string( $data = null ) {
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
