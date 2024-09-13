<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_Product_List {

	const NAME = 'tpm_all_ttw_products';

	const CACHE_LIFE_TIME = WEEK_IN_SECONDS;

	protected $_products = array();

	public static $type_to_tag_dependencies = array(
		'skin' => array( 'ttb' ), // a skin has a dependency on TTB
	);

	protected static $_instance;

	private function __construct() {
	}

	public static function get_instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function render( $return = false ) {

		ob_start();
		include thrive_product_manager()->path( 'inc/templates/header.phtml' );
		include thrive_product_manager()->path( 'inc/templates/product/list.phtml' );
		$html = ob_get_clean();

		if ( $return === true ) {
			return $html;
		}

		echo $html;
	}

	protected function _get_products() {

		if ( empty( $this->_products ) ) {
			$this->_products = $this->_get_all_ttw_products();
		}

		foreach ( $this->_products as &$product ) {
			$instance          = $this->_product_factory( $product );
			$product['status'] = $instance->get_status();
			if ( isset( self::$type_to_tag_dependencies[ $product['type'] ] ) ) {
				$product['dependencies'] = self::$type_to_tag_dependencies[ $product['type'] ];
			}
		}

		return $this->_products;
	}

	public function get_products_array() {

		return array_values( $this->_get_products() );
	}

	/**
	 * @return array
	 */
	protected function _get_all_ttw_products() {

		if ( ! TPM_Connection::get_instance()->is_connected() ) {
			return array();
		}

		if ( Thrive_Product_Manager::CACHE_ENABLED && ( $products = tpm_get_transient( self::NAME ) ) !== false ) {

			return $products;
		}

		$connection = TPM_Connection::get_instance();

		$params = array(
			'user_id'     => $connection->ttw_id,
			'tpm_version' => Thrive_Product_Manager::V,
		);

		$request = new TPM_Request( '/api/v1/public/get_products', $params );
		$request->set_header( 'Authorization', $connection->ttw_salt );

		$proxy_request = new TPM_Proxy_Request( $request );
		$response      = $proxy_request->execute( '/tpm/proxy' );

		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body, true );

		$products = array();

		if ( is_array( $body ) && ! empty( $body['data'] ) ) {

			foreach ( $body['data'] as $product_data ) {
				$product                         = $this->_product_factory( $product_data );
				$products[ $product->get_tag() ] = $product->to_array();
			}
		}

		if ( ! empty( $products ) ) {
			tpm_set_transient( self::NAME, $products, self::CACHE_LIFE_TIME );
		}

		return $products;
	}

	/**
	 * @param $data
	 *
	 * @return TPM_Product_Plugin|TPM_Product_Theme|TPM_Product_Skin|TPM_Product
	 */
	protected function _product_factory( $data ) {

		if ( ! is_array( $data ) || empty( $data ) ) {
			$data = array();
		}

		$instance_name = 'TPM_Product';

		//defaults
		$name        = 'No name';
		$description = 'No description';
		$logo_url    = 'https://thrivethemes.com/wp-content/uploads/2016/10/thrive-themes-logo-home-2.png';
		$type        = '';
		$tag         = '';
		$api_slug    = '';
		$file        = '';

		extract( $data );

		if ( empty( $type ) || ! in_array( $type, array( 'plugin', 'theme', 'skin' ) ) ) {
			return new TPM_Product( $name, $description, $logo_url, $tag, $api_slug, $file );
		}

		$instance_name .= '_' . ucfirst( $type );

		if ( $tag === 'ttb' ) {
			$instance_name .= '_Builder';
		} elseif ( $tag === 'tap' ) {
			$instance_name .= '_Automator';
		}

		$instance = new $instance_name( $name, $description, $logo_url, $tag, $api_slug, $file );

		return $instance;
	}

	/**
	 * Create a TPM_Product instance based on $tag string
	 *
	 * @param string $tag
	 *
	 * @return TPM_Product|TPM_Product_Plugin|TPM_Product_Skin|TPM_Product_Theme
	 */
	public function get_product_instance( $tag ) {

		$ttw_products = $this->_get_products();

		$product_data = ! empty( $ttw_products[ $tag ] )
			? $ttw_products[ $tag ]
			: array(
				'tag' => $tag,
			);

		return $this->_product_factory( $product_data );
	}

	/**
	 * Deletes transient for TTW Products
	 * - TTW Product list will be fetched
	 */
	public function clear_cache() {

		tpm_delete_transient( self::NAME );
	}
}
