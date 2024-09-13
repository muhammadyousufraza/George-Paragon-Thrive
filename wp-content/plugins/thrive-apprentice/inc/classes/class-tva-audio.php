<?php

/**
 * Class TVA_Audio
 * - handles embed code for data provided
 */
class TVA_Audio extends TVA_Media {
	/**
	 * Calculates embed code for soundcloud
	 * - called dynamically from parent class
	 *
	 * @return string
	 * @see get_embed_code
	 *
	 */
	protected function _soundcloud_embed_code() {
		$data    = $this->_data;
		$url     = $data['source'];
		$api_url = 'http://soundcloud.com/oembed';
		$args    = array(
			'url'      => $url,
			'autoplay' => false,
			'format'   => 'json',
		);

		$api_url .= '?';
		foreach ( $args as $k => $param ) {
			$api_url .= $k . '=' . $param . '&';
		}

		$api_url  = rtrim( $api_url, '?& ' );
		$response = tve_dash_api_remote_get( $api_url );

		if ( $response instanceof WP_Error ) {
			return '';
		}

		$status = $response['response']['code'];
		if ( $status != 200 && $status != 204 ) {
			return '';
		}

		$data = @json_decode( $response['body'], true );

		return $data['html'];
	}
}
