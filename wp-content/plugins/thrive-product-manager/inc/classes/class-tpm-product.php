<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_Product {

	const AVAILABLE   = 'available'; //product has been purchased by the user and there is a license for it
	const INSTALLED   = 'installed'; //product is downloaded in its folder
	const ACTIVATED   = 'activated'; //product is downloaded in its folder and WP knows it as activated
	const READY       = 'ready'; //product is installed/activated/licensed and ready to be used
	const TO_INSTALL  = 'to_install'; //product is not downloaded in its folder
	const TO_ACTIVATE = 'to_activate'; //product is downloaded in its folder but not activated
	const TO_LICENSE  = 'to_license'; //product has to be licensed

	protected $name;
	protected $description;
	protected $logo_url;
	protected $tag;
	protected $file;
	protected $api_slug;

	protected $status;

	protected $_license_id;

	public function __construct( $name, $description, $logo_url, $tag, $api_slug, $file ) {

		$this->name        = $name;
		$this->description = $description;
		$this->logo_url    = $logo_url;
		$this->tag         = $tag;
		$this->api_slug    = $api_slug;
		$this->file        = $file;
	}

	public function to_array() {

		$data = array(
			'name'        => $this->name,
			'description' => empty( $this->description ) ? $this->name : $this->description,
			'logo_url'    => $this->logo_url,
			'tag'         => $this->tag,
			'api_slug'    => $this->api_slug,
			'file'        => $this->file,
			'hidden'      => false,
		);

		return $data;
	}

	/**
	 * Checks if its tag exists somewhere in DB and
	 *
	 * @return bool
	 */
	public function is_licensed() {

		$thrive_license = get_option( 'thrive_license', array() );

		$backwards = in_array( $this->get_tag(), $thrive_license ) || in_array( 'all', $thrive_license );

		return $backwards || TPM_License_Manager::get_instance()->is_licensed( $this );
	}

	/**
	 * Product is installed and activated physically on WP site
	 *
	 * @return bool
	 */
	public function is_activated() {

		return false;
	}

	/**
	 * @return bool
	 */
	public function is_installed() {

		return false;
	}

	/**
	 * Checks in TTW licenses if there is one which has $product tag
	 *
	 * @return bool
	 */
	public function is_purchased() {

		return TPM_License_Manager::get_instance()->is_purchased( $this );
	}

	/**
	 * We care only for statuses which are relate to TTW license
	 * - available (not purchased)
	 * - purchased
	 * - licensed
	 *
	 * @return string
	 */
	public function get_status() {

		if ( ! empty( $this->status ) ) {
			return $this->status;
		}

		if ( ! $this->is_purchased() ) {
			return $this->status = self::AVAILABLE;
		}

		if ( ! $this->is_installed() ) {
			return $this->status = self::TO_INSTALL;
		}

		if ( ! $this->is_activated() ) {
			return $this->status = self::TO_ACTIVATE;
		}

		if ( ! $this->is_licensed() ) {
			return $this->status = self::TO_LICENSE;
		}

		return $this->status = self::READY;
	}

	public function get_tag() {

		return $this->tag;
	}

	public function get_name() {

		return $this->name;
	}

	/**
	 * @param $credentials array
	 *
	 * @return bool|WP_Error
	 */
	public function install( $credentials ) {

		return new WP_Error( 'empty_product_install', 'This product cannot be installed' );
	}

	public function activate() {
		return false;
	}

	public function search_license() {

		$license_id = TPM_License_Manager::get_instance()->get_product_license( $this );

		$this->_license_id = $license_id;
	}

	public function set_license( $id ) {

		$this->_license_id = (int) $id;
	}

	public function get_license() {

		return $this->_license_id;
	}

	protected function _get_hash( $data ) {
		$key = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

		return md5( $key . serialize( $data ) . $key );
	}

	/**
	 * Change the response before sending it
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function before_response( $data ) {
		return $data;
	}

	/**
	 * Get a comprehensive response message based on a status
	 *
	 * @param string $status
	 *
	 * @return array
	 */
	public function get_response_status( $status ) {
		switch ( $status ) {
			case self::READY:
				$data['status']  = self::READY;
				$data['message'] = sprintf( '%s is now ready to use', $this->get_name() );
				break;
			case self::INSTALLED:
				$data['status']  = self::INSTALLED;
				$data['message'] = sprintf( '%s is now installed successfully', $this->get_name() );
				break;
			default:
				$data = array();
				break;
		}

		return $data;
	}

}
