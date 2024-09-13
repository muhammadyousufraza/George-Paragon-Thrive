<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_License {

	const NAME = 'tpm_licenses';

	/** @var integer */
	protected $id = 0;

	/** @var array */
	protected $tags = array();

	/** @var int */
	protected $used = 0;

	/** @var int */
	protected $max = 0;

	public function __construct( $id, $tags, $used = 0, $max = 0 ) {

		$this->id   = (int) $id;
		$this->used = (int) $used;
		$this->max  = (int) $max;

		if ( is_array( $tags ) ) {
			$this->tags = $tags;
		}
	}

	/**
	 * @param $tag string
	 *
	 * @return bool
	 */
	public function has_tag( $tag ) {

		return in_array( $tag, $this->tags ) || in_array( 'all', $this->tags );
	}

	public function get_id() {

		return $this->id;
	}

	public function get_max() {

		return (int) $this->max;
	}

	public function get_used() {

		return (int) $this->used;
	}

	public function save() {

		$current_licenses              = get_option( self::NAME, array() );
		$current_licenses[ $this->id ] = $this->tags;

		update_option( self::NAME, $current_licenses );

		return true;
	}

	public function delete() {

		$current_licenses = get_option( self::NAME, array() );

		if ( isset( $current_licenses[ $this->id ] ) ) {
			unset( $current_licenses[ $this->id ] );
		}

		update_option( self::NAME, $current_licenses );

		return true;
	}

	/**
	 * Fetches a list of licenses which are used on current site
	 * - each license may have more tags
	 *
	 * @return TPM_License[]
	 */
	public static function get_saved_licenses() {

		$licenses = array();

		foreach ( get_option( self::NAME, array() ) as $license_id => $tags ) {
			$licenses[ $license_id ] = new self( $license_id, $tags );
		}

		return $licenses;
	}

	/**
	 * Checks of max is strict greater than used
	 *
	 * @return bool
	 */
	public function has_usages() {
		return $this->get_max() > $this->get_used();
	}
}
