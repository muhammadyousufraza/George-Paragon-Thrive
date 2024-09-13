<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TVE_Dash_Product_LicenseManager {
	const LICENSE_OPTION_NAME = 'thrive_license';
	const TCB_TAG             = 'tcb';
	const TL_TAG              = 'tl';
	const TCW_TAG             = 'tcw';
	const ALL_TAG             = 'all';
	const TU_TAG              = 'tu';
	const THO_TAG             = 'tho';
	const TVO_TAG             = 'tvo';
	const TQB_TAG             = 'tqb';
	const TCM_TAG             = 'tcm';
	const TVA_TAG             = 'tva';
	const TAB_TAG             = 'tab';
	const TPM_TAG             = 'tpm';

	const TAG_FOCUS     = 'focusblog';
	const TAG_LUXE      = 'luxe';
	const TAG_IGNITION  = 'ignition';
	const TAG_MINUS     = 'minus';
	const TAG_SQUARED   = 'squared';
	const TAG_VOICE     = 'voice';
	const TAG_PERFORMAG = 'performag';
	const TAG_PRESSIVE  = 'pressive';
	const TAG_STORIED   = 'storied';
	const TAG_RISE      = 'rise';

	protected $secret_key = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

	protected static $instance;

	protected $license_data;
	protected $accepted_tags = array();

	protected function __construct() {
		$this->license_data = get_option( static::LICENSE_OPTION_NAME, array() );
		$reflection         = new ReflectionClass( $this );

		$constants = $reflection->getConstants();

		$this->accepted_tags = array();

		foreach ( $constants as $name => $value ) {
			if ( strpos( $name, 'TAG' ) !== false ) {
				$this->accepted_tags [] = $value;
			}
		}
	}

	public static function getInstance() {
		if ( empty( static::$instance ) ) {
			static::$instance = new TVE_Dash_Product_LicenseManager();
		}

		return static::$instance;
	}

	/**
	 * whether or not $tag is a theme tag
	 * used in backwards-compatible license check
	 *
	 * @param string $tag
	 *
	 * @return array
	 */
	public static function isThemeTag( $tag ) {
		return in_array( $tag, array(
			static::TAG_FOCUS,
			static::TAG_IGNITION,
			static::TAG_LUXE,
			static::TAG_MINUS,
			static::TAG_PERFORMAG,
			static::TAG_PRESSIVE,
			static::TAG_RISE,
			static::TAG_SQUARED,
			static::TAG_STORIED,
			static::TAG_VOICE,
		) );
	}

	/**
	 * Checks license options
	 *
	 * @param string|TVE_Dash_Product_Abstract $item
	 *
	 * @return bool
	 */
	public function itemActivated( $item ) {

		if ( $item instanceof TVE_Dash_Product_Abstract ) {
			$item = $item->get_tag();
		}

		if ( function_exists( 'thrive_product_manager' ) ) {
			return thrive_product_manager()->itemActivated( $item, $this );
		}

		if ( $this->isThemeTag( $item ) ) {
			$licensed = get_option( 'thrive_license_status', false ) === 'ACTIVE';
		} else {
			switch ( $item ) {
				case static::TCB_TAG:
					$licensed = get_option( 'tve_license_status', false ) === 'ACTIVE';
					break;
				case static::TL_TAG:
					$licensed = get_option( 'tve_leads_license_status', false ) === 'ACTIVE';
					break;
				case static::TCW_TAG:
					$licensed = get_option( 'tcw_license_status', false ) === 'ACTIVE';
					break;
				default:
					$licensed = false;
					break;
			}
		}

		if ( $licensed === false ) {
			$licensed = $this->checkData( $item );
		}

		return $licensed;
	}

	/**
	 * @param $item TVE_Dash_Product_Abstract|string
	 *
	 * @return bool
	 */
	protected function checkData( $item = null ) {
		if ( empty( $this->license_data ) || null === $item ) {
			return false;
		}

		if ( in_array( static::ALL_TAG, $this->license_data ) ) {
			return true;
		}

		if ( $item instanceof TVE_Dash_Product_Abstract ) {
			$tag = $item->get_tag();
		} elseif ( is_string( $item ) ) {
			$tag = $item;
		}

		return isset( $tag ) ? in_array( $tag, $this->license_data ) : false;
	}

	public function checkLicense( $email, $key, $tag = false ) {
		$api_url = "http://service-api.thrivethemes.com/license";


		$body = array(
			'email'   => $email,
			'license' => $key,
		);

		if ( $tag !== false ) {
			$body['tag'] = $tag;
		}

		$api_url = add_query_arg( array(
			'p' => $this->calc_hash( $body ),
		), $api_url );

		$_args = array(
			'sslverify' => false,
			'timeout'   => 120,
			'headers'   => array(
				'User-Agent' => 'WordPress',
			),
			'body'      => $body,
		);

		$licenseValid = wp_remote_post( $api_url, $_args );

		$response = array();

		if ( is_wp_error( $licenseValid ) ) {
			/** @var WP_Error $licenseValid */
			/** Couldn't connect to the API URL - possible because wp_remote_get failed for whatever reason.  Maybe CURL not activated on server, for instance */
			$response['success'] = 0;
			$response['reason']  = sprintf( __( "An error occurred while connecting to the license server. Error: %s. Please login to thrivethemes.com, report this error message on the forums and we'll get this sorted for you", 'thrive-dash' ), $licenseValid->get_error_message() );

			return $response;
		}

		$response = @json_decode( $licenseValid['body'], true );

		if ( empty( $response ) ) {
			$response            = new stdClass();
			$response['success'] = 0;
			$response['reason']  = sprintf( __( "An error occurred while receiving the license status. The response was: %s. Please login to thrivethemes.com, report this error message on the forums and we'll get this sorted for you.", 'thrive-dash' ), $licenseValid['body'] );

			return $response;
		}

		return $response;
	}

	protected function calc_hash( $data ) {
		return md5( $this->secret_key . serialize( $data ) . $this->secret_key );
	}

	public function activateProducts( &$response ) {
		//check success flag
		if ( empty( $response['success'] ) ) {
			$response['success'] = 0;
			$response['reason']  = __( 'Invalid response!', 'thrive-dash' );

			return null;
		}

		//check if products is not empty and is array
		if ( empty( $response['products'] ) || ! is_array( $response['products'] ) ) {
			$response['success'] = 0;
			$response['reason']  = __( 'Invalid products returned from server!', 'thrive-dash' );

			return null;
		}

		foreach ( $response['products'] as $product_tag ) {
			if ( ! in_array( $product_tag, $this->accepted_tags ) ) {
				$response['success'] = 0;
				$response['reason']  = __( 'Products returned from server are not accepted for licensing!', 'thrive-dash' );

				return null;
			}
		}

		//do the logic and update the option in DB
		if ( in_array( static::ALL_TAG, $response['products'] ) ) {
			update_option( static::LICENSE_OPTION_NAME, array( static::ALL_TAG ) );
		} else {
			$existing = get_option( static::LICENSE_OPTION_NAME, array() );
			update_option( static::LICENSE_OPTION_NAME, array_unique( array_merge( $existing, $response['products'] ) ) );
		}

		return $response;
	}

	/**
	 * Based on $text_domain returns a tag associated
	 *
	 * @param string $text_domain
	 *
	 * @return string
	 */
	public static function get_product_tag( $text_domain ) {
		$mapping = array(
			'thrive-cb'              => static::TCB_TAG,
			'thrive-leads'           => static::TL_TAG,
			'thrive-clever-widgets'  => static::TCW_TAG,
			'thrive-ult'             => static::TU_TAG,
			'thrive-headline'        => static::THO_TAG,
			'thrive-ovation'         => static::TVO_TAG,
			'thrive-quiz-builder'    => static::TQB_TAG,
			'thrive-comments'        => static::TCM_TAG,
			'thrive-apprentice'      => static::TVA_TAG,
			'thrive-optimize'        => static::TAB_TAG,
			'thrive-product-manager' => static::TPM_TAG,
		);

		return $mapping[ $text_domain ] ?? '';
	}
}
