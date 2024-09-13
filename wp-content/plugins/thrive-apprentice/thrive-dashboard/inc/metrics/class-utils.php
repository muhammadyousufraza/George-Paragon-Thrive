<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Metrics;

use TVE_Dash_Product_Abstract;
use function tve_dash_api_remote_post;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Utils {
	const THRIVE_KEY = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

	/**
	 * @param string $url
	 * @param array  $data
	 *
	 * @return void
	 */
	public static function send_request( $url, array $data = [] ) {

		$url = add_query_arg( [
			'p' => static::calc_thrive_hash( $data ),
		], $url );

		tve_dash_api_remote_post( $url,
			[
				'body'    => json_encode( $data ),
				'headers' => [
					'Content-Type' => 'application/json',
				],
			]
		);
	}

	/**
	 * Whether we are on plugin screen
	 *
	 * @return bool
	 */
	public static function is_plugins_screen() {
		$screen = get_current_screen();

		return $screen && in_array( $screen->id, [ 'plugins', 'plugins-network' ] );
	}

	/**
	 * Calc the hash that should be sent on APIs requests
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function calc_thrive_hash( array $data ) {
		return md5( static::THRIVE_KEY . serialize( $data ) . static::THRIVE_KEY );
	}

	/**
	 * Hash a string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function hash_256( $string ) {
		if ( $string === null || static::is_hashed( $string ) ) {
			return $string;
		}

		return hash( 'sha256', $string );
	}

	/**
	 * Check if a string is hashed
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function is_hashed( $string ) {
		return strlen( $string ) === 64 && ctype_xdigit( $string );
	}

	/**
	 * Default options for string required params
	 *
	 * @return array
	 */
	public static function get_rest_string_arg_data() {
		return [
			'type'              => 'string',
			'required'          => true,
			'validate_callback' => static function ( $param ) {
				return ! empty( $param );
			},
		];
	}

	/**
	 * get a list of products with their localizations
	 *
	 * @return array
	 */
	public static function get_products() {
		$installed = tve_dash_get_products( false );
		$localized = [];
		foreach ( $installed as $key => $product ) {
			/**
			 * @var $product TVE_Dash_Product_Abstract
			 */
			$localized[ $key ] = $product->localize_data();
		}

		return $localized;
	}
}
