<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_License_Manager {

	const NAME = 'tpm_ttw_licenses';

	const CACHE_LIFE_TIME = 28800; //8 hours

	/**
	 * @var TPM_License_Manager
	 */
	protected static $_instance;

	/**
	 * Array of tags set from old licensing system
	 * Used for backwards compatibility
	 *
	 * @var array
	 */
	protected $_thrive_license;

	/**
	 * List of all licenses the user has on ttw website
	 *
	 * @var array
	 */
	protected $_ttw_licenses = array();

	protected $ttw_license_instances = array();

	private function __construct() {

		$this->_thrive_license = get_option( 'thrive_license', array() );
	}

	public static function get_instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Checks if there is a license saved/used for current site and has $product tag
	 *
	 * @param $product TPM_Product
	 *
	 * @return bool
	 */
	public function is_licensed( TPM_Product $product ) {

		$exists = false;

		/**
		 * @var  $license_id int
		 * @var  $license    TPM_License
		 */
		foreach ( TPM_License::get_saved_licenses() as $license_id => $license ) {
			if ( $license->has_tag( $product->get_tag() ) ) {
				$exists = true;
				break;
			}
		}

		return $exists;
	}

	/**
	 * Checks in TTW licenses if there is one which has $product tag
	 *
	 * @param TPM_Product $product
	 *
	 * @return bool
	 */
	public function is_purchased( TPM_Product $product ) {

		//check old licenses
		$thrive_license = get_option( 'thrive_license', array() );
		if ( in_array( 'all', $thrive_license, false ) || in_array( $product->get_tag(), $thrive_license, false ) ) {
			return true;
		}

		/** @var $license TPM_License */
		foreach ( $this->get_ttw_license_instances() as $id => $license ) {
			if ( $license->has_tag( $product->get_tag() ) ) {
				return true;
			}
		}
	}

	public function get_ttw_license_instances() {

		if ( ! empty( $this->ttw_license_instances ) ) {
			return $this->ttw_license_instances;
		}

		foreach ( $this->_get_ttw_licenses() as $license_id => $data ) {
			$instance                                   = new TPM_License( $license_id, $data['tags'], $data['usage']['used'], $data['usage']['max'] );
			$this->ttw_license_instances[ $license_id ] = $instance;
		}

		return $this->ttw_license_instances;
	}

	/**
	 * Based on current connection a request is made to TTW for assigned licenses
	 *
	 * @param TPM_Connection $connection
	 *
	 * @return array
	 */
	protected function _get_connection_licenses( TPM_Connection $connection ) {

		if ( ! $connection->is_connected() ) {
			return array();
		}

		$licenses = tpm_get_transient( self::NAME );

		if ( Thrive_Product_Manager::CACHE_ENABLED && $licenses !== false ) {

			return $licenses;
		}

		$params = array(
			'user_id' => $connection->ttw_id,
		);

		$route   = '/api/v1/public/get_licenses';
		$request = new TPM_Request( $route, $params );
		$request->set_header( 'Authorization', $connection->ttw_salt );

		$proxy_request = new TPM_Proxy_Request( $request );
		$response      = $proxy_request->execute( '/tpm/proxy' );

		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body, true );

		if ( ! is_array( $body ) || empty( $body['success'] ) ) {

			tpm_set_transient( self::NAME, array(), self::CACHE_LIFE_TIME );

			return array();
		}

		$licenses = $body['data'];

		//sort licenses so that the ones with 'all' tags will be 1st in list
		//so they have priority on usage
		uasort( $licenses, static function ( $license_a, $license_b ) {

			$a_tags = is_array( $license_a ) && ! empty( $license_a['tags'] ) && is_array( $license_a['tags'] ) ? $license_a['tags'] : array();
			$b_tags = is_array( $license_b ) && ! empty( $license_b['tags'] ) && is_array( $license_b['tags'] ) ? $license_b['tags'] : array();


			if ( in_array( 'all', $a_tags, true ) && in_array( 'all', $b_tags, true ) ) {
				return 0;
			}

			if ( false === in_array( 'all', $a_tags, true ) && in_array( 'all', $b_tags, true ) ) {
				return 1;
			}

			return - 1;
		} );

		tpm_set_transient( self::NAME, $licenses, self::CACHE_LIFE_TIME );

		return $licenses;
	}

	/**
	 * Searches in all licenses user has bought on TTW site
	 *
	 * @param TPM_Product $product
	 *
	 * @return int|null
	 */
	public function get_product_license( TPM_Product $product ) {

		/** @var TPM_License $license */
		foreach ( $this->get_ttw_license_instances() as $license ) {

			if ( $license->has_tag( $product->get_tag() ) && $license->get_used() < $license->get_max() ) {
				return $license->get_id();
			}
		}

		return null;
	}

	/**
	 * If $products have a license id assigned then
	 * - a request to TTW  is made to increase the usage of the license/licenses
	 *
	 * @param array $products tag
	 *
	 * @return array|bool
	 */
	public function activate_licenses( $products = array() ) {

		if ( empty( $products ) ) {
			return false;
		}

		$licenses_ids = array();
		$product_tags = array();

		/** @var TPM_Product $product */
		foreach ( $products as $product ) {
			$product_tags[ $product->get_tag() ] = false;

			$id = $product->get_license();

			if ( ! empty( $id ) ) {
				$licenses_ids[] = $id;
			}
		}

		$licenses_ids = array_filter( $licenses_ids );
		$licenses_ids = array_unique( $licenses_ids );

		if ( empty( $licenses_ids ) ) {
			return false;
		}

		$params  = array(
			'user_id'       => TPM_Connection::get_instance()->ttw_id,
			'user_site_url' => get_site_url(),
			'data'          => $licenses_ids,
		);
		$request = new TPM_Request( '/api/v1/public/license_uses', $params );
		$request->set_header( 'Authorization', TPM_Connection::get_instance()->ttw_salt );

		$proxy_request = new TPM_Proxy_Request( $request );
		$response      = $proxy_request->execute( '/tpm/proxy' );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );

		if ( ! is_array( $result ) || ! isset( $result['success'] ) || ! isset( $result['data'] ) || ! is_array( $result['data'] ) ) {
			return false;
		}

		$ttw_licenses = $this->_get_ttw_licenses();

		foreach ( $result['data'] as $license_id => $activated ) {

			if ( ! array_key_exists( $license_id, $ttw_licenses ) ) {
				continue;
			}

			$license          = $ttw_licenses[ $license_id ];
			$license_instance = new TPM_License( $license_id, $license['tags'] );

			if ( $activated === true ) {
				$license_instance->save();
				//prepare response
				foreach ( $product_tags as $tag => $value ) {
					if ( $license_instance->has_tag( $tag ) ) {
						$product_tags[ $tag ] = true;
					}
				}
			}
		}

		return $product_tags;
	}

	/**
	 * @return array
	 */
	protected function _get_ttw_licenses() {

		if ( empty( $this->_ttw_licenses ) ) {
			$this->_ttw_licenses = $this->_get_connection_licenses( TPM_Connection::get_instance() );
		}

		return $this->_ttw_licenses;
	}

	/**
	 * @param $license_id
	 *
	 * @return TPM_License|null;
	 */
	public function get_license_instance( $license_id ) {

		$license_id = (int) $license_id;

		if ( empty( $license_id ) ) {
			return null;
		}

		$license = null;
		$list    = TPM_License::get_saved_licenses();

		/** @var TPM_License $item */
		foreach ( $list as $item_id => $item ) {
			if ( $item->get_id() === $license_id ) {
				$license = $item;
				break;
			}
		}

		return $license;
	}

	public function license_deactivate( WP_REST_Request $request ) {

		$authorization = $request->get_param( 'Authorization' );
		$connection    = TPM_Connection::get_instance();
		$tpm_token     = $connection->decrypt( get_option( 'tpm_token', null ) );

		if ( $authorization !== $tpm_token ) {
			return array(
				'success' => false,
				'message' => 'No permission',
			);
		}

		$deactivated = true;
		$message     = 'License deactivated with success';
		$response    = array(
			'success' => $deactivated,
			'message' => $message,
		);

		$license_id = (int) $request->get_param( 'id' );

		if ( empty( $license_id ) ) {
			$response['success'] = false;
			$response['message'] = 'Invalid param license id ' . $request->get_param( 'id' );

			return $response;
		}

		$license = $this->get_license_instance( $license_id );

		if ( ! ( $license instanceof TPM_License ) ) {
			$response['success'] = true;
			$response['message'] = "Couldn't find any license with ID " . $request->get_param( 'id' );

			return $response;
		}

		if ( $license->delete() !== true ) {
			$response['success'] = false;
			$response['message'] = "Couldn't not deactivate license " . $request->get_param( 'id' );
		}

		TPM_Product_List::get_instance()->clear_cache();
		self::get_instance()->clear_cache();

		return $response;
	}

	public function clear_cache() {

		return tpm_delete_transient( self::NAME );
	}

	/**
	 * Deletes the local saved licenses
	 * - increments the usages for licenses by doing a request to TTW
	 */
	public function deactivate_all_licenses() {

		$licenses = TPM_License::get_saved_licenses();

		if ( empty( $licenses ) ) {
			return;
		}

		$connection = TPM_Connection::get_instance();

		//if user has disconnected TPM then try to use the backup connection saved at disconnecting
		if ( false === $connection->is_connected() ) {
			$connection->set_data( get_option( 'tpm_bk_connection', array() ) );
		}

		$params  = array(
			'user_id'       => $connection->ttw_id,
			'user_site_url' => get_site_url(),
			'direction'     => 'down',
			'data'          => array_keys( $licenses ),
		);
		$request = new TPM_Request( '/api/v1/public/license_uses', $params );
		$request->set_header( 'Authorization', $connection->ttw_salt );

		$proxy_request = new TPM_Proxy_Request( $request );
		$response      = $proxy_request->execute( '/tpm/proxy' );
		TPM_Log_Manager::get_instance()->set_message( var_export( $response, true ) )->log();
		delete_option( TPM_License::NAME );
	}
}
