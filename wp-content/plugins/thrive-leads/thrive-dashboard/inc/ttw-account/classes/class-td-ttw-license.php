<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * @property int     id
 * @property int     status
 * @property string  name
 * @property string  state
 * @property string  expiration
 * @property string  refund_date
 * @property array   tags
 * @property boolean can_update
 * @property int     grace_period_in_days
 * @property boolean in_grace_period
 * @property boolean complementary
 *
 * Representation of a single user license
 * Class TD_TTW_License
 */
class TD_TTW_License {

	use TD_Magic_Methods;

	const MEMBERSHIP_TAG = 'all';

	const REFUNDED_STATUS = 3;

	private $_expected_fields = [
		'id',
		'status',
		'name',
		'state',
		'tags',
		'expiration',
		'refund_date',
		'can_update',
		'mm_product_id',
		'grace_period_in_days',
		'in_grace_period',
		'complementary',
	];

	public function __construct( $data ) {

		foreach ( $this->_expected_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$this->_data[ $field ] = $data[ $field ];
			}
		}
	}

	/**
	 * Check if the license is active
	 *
	 * @return bool
	 */
	public function is_active() {

		return in_array(
			       (int) $this->status,
			       array(
				       1, // active
				       9, // pending cancellation
			       ),
			       true
		       ) || $this->complementary === true;
	}

	/**
	 * Check if the license is expired
	 *
	 * @return bool
	 */
	public function is_expired() {
		try {
			return new DateTime( 'now' ) > $this->get_expiration_date();
		} catch ( Exception $e ) {
			return true;
		}
	}

	/**
	 * @return DateTime
	 * @throws Exception
	 */
	public function get_expiration_date() {
		return new DateTime( $this->expiration );
	}

	/**
	 * @return DateTime
	 * @throws Exception
	 */
	public function get_grace_period_date() {
		return $this->get_expiration_date()->add( new DateInterval( 'P' . (int) $this->grace_period_in_days . 'D' ) );
	}

	/**
	 * Checks if a license is expired and expiration date + grace period in days in the future
	 *
	 * @return bool
	 */
	public function is_in_grace_period() {

		if ( $this->is_active() ) {
			return false;
		}

		try {
			$date = $this->get_expiration_date();
			$date->add( new DateInterval( 'P' . (int) $this->grace_period_in_days . 'D' ) );

			return new DateTime( 'now' ) < $date;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function is_out_of_grace_period() {
		return $this->is_expired() && ! $this->is_in_grace_period();
	}

	/**
	 * @return string
	 */
	public function get_name() {

		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_expiration() {

		return $this->expiration;
	}

	/**
	 * Check if the user has access to updates on this license
	 *
	 * @return bool
	 */
	public function can_update() {

		return true === $this->can_update;
	}

	/**
	 * @return bool
	 */
	public function is_membership() {
		return in_array( self::MEMBERSHIP_TAG, $this->tags, true );
	}

	/**
	 * @return DateInterval
	 * @throws Exception
	 */
	public function get_remaining_grace_period() {
		try {
			return ( new DateTime( 'now' ) )->diff( $this->get_grace_period_date() );
		} catch ( Exception $e ) {
			return new DateInterval( 'P0D' );
		}
	}

	/**
	 * @return string
	 */
	public function get_product_name() {

		if ( $this->is_membership() ) {
			return 'membership';
		}

		return implode( ',', $this->tags );
	}

	public function get_state() {
		return $this->state;
	}

	public function is_refunded() {
		return self::REFUNDED_STATUS === (int) $this->status;
	}

	public function get_refunded_date() {
		return $this->refund_date;
	}

	/**
	 * Checks if tags list contains the $tag
	 *
	 * @param $tag
	 *
	 * @return bool
	 */
	public function has_tag( $tag ) {
		return in_array( $tag, $this->tags, true );
	}

	/**
	 * License data
	 *
	 * @return array|mixed
	 */
	public function get_data() {
		return $this->_data;
	}
}
