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
 * Class TD_TTW_User_Licenses
 * @static TD_TTW_User_Licenses get_instance
 * @property string status
 */
class TD_TTW_User_Licenses {

	use TD_Magic_Methods;

	use TD_Singleton;

	use TD_TTW_Utils;

	const NAME = 'td_ttw_licenses_details';

	const RECHECK_KEY = 'td_recheck_license';

	const TTB_TAG = 'ttb';

	const CACHE_LIFE_TIME = 28800; //8 hours

	const CACHE_LIFE_TIME_SHORT = 28800; //30 minutes should be but for the moment we keep it 8 hours

	private $_licenses_instances = array();

	/**
	 * @var TD_TTW_License[]
	 */
	private $_active_licenses = array();
	/**
	 * @var TD_TTW_License[]
	 */
	private $_in_grace_period_licenses = array();
	/**
	 * @var TD_TTW_License[]
	 */
	private $_all_license_instances = array();

	/**
	 * @var array|mixed - transient value before deleting the transient
	 */
	private $_tr_licenses;

	private function __construct() {

		$tr_licenses = array();
		add_filter( 'option__thrive_tr_' . self::NAME, function ( $option_value ) use ( &$tr_licenses ) {
			$tr_licenses = $option_value['value'];

			return $option_value;
		} );

		$transient          = thrive_get_transient( self::NAME );
		$this->_tr_licenses = $tr_licenses;
		$this->_data        = $transient === false ? array() : $transient;

		$this->_init_licenses_instances();

		if ( ! empty( $_REQUEST[ self::RECHECK_KEY ] ) ) {
			$this->recheck_license();
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			die;
		}
	}

	private function _init_licenses_instances() {

		$this->_all_license_instances = array();

		foreach ( (array) $this->_data as $item ) {

			$license = new TD_TTW_License( $item );

			$this->_all_license_instances[] = $license;

			if ( $license->is_active() ) {
				$this->_push( $license, 'active' );
			} else if ( $license->is_in_grace_period() ) {
				$this->_push( $license, 'in_grace_period' );
			} else {
				$this->_push( $license, 'expired' );
			}

			if ( ! empty( $item['tags'] ) && is_array( $item['tags'] ) ) {
				foreach ( $item['tags'] as $tag ) {
					/**
					 * There might be a cases where user has purchased the same license multiple times; e.g. Suit with tag: all
					 * TTW serves them all but those which can_update are first in the list
					 * So that, the last ones in the list which cannot_update do not overwrite those which can_update()
					 */
					if ( empty( $this->_licenses_instances[ $tag ] ) ) {
						$this->_licenses_instances[ $tag ] = new TD_TTW_License( $item );
					}
				}
			}
		}

		//membership license should be first in the list
		usort( $this->_all_license_instances, static function ( $a, $b ) {
			if ( $a->is_membership() && $b->is_membership() ) {
				return 0;
			}

			return $a->is_membership() ? - 1 : 1;
		} );
	}

	/**
	 * Push the license into a list
	 *
	 * @param TD_TTW_License $license
	 * @param string         $list - expired, active, in_grace_period
	 *
	 * @return void
	 */
	private function _push( TD_TTW_License $license, string $list ) {

		$allowed_lists = array( 'expired', 'active', 'in_grace_period' );

		if ( ! in_array( $list, $allowed_lists, true ) ) {
			$list = 'expired';
		}
		$arr = $this->{'_' . $list . '_licenses'};
		foreach ( $license->tags as $tag ) {
			$arr[ $tag ] = $license;
		}
		$this->{'_' . $list . '_licenses'} = $arr;
	}

	/**
	 * Check if the membership license is active
	 *
	 * @return bool
	 */
	public function is_membership_active() {

		return $this->get_membership() && $this->get_membership()->is_active();
	}

	/**
	 * Get available licenses
	 *
	 * @return TD_TTW_License[]
	 */
	public function get() {

		return $this->_licenses_instances;
	}

	/**
	 * Returns all licenses
	 *
	 * @return TD_TTW_License[]
	 */
	public function get_all(): array {
		return $this->_all_license_instances;
	}

	/**
	 * Get all licenses that are expired or in grace period
	 *
	 * @return array
	 */
	public function get_inactive(): array {
		return array_filter( $this->_all_license_instances, static function ( $license ) {
			/** @var $license TD_TTW_License */
			return $license->is_expired() || $license->is_in_grace_period();
		} );
	}

	/**
	 * Check if a license exists by tag
	 *
	 * @param $tag
	 *
	 * @return bool
	 */
	public function has_license( $tag ): bool {

		return isset( $this->_licenses_instances[ $tag ] );
	}

	/**
	 * Check if there is any membership license
	 *
	 * @return bool
	 */
	public function has_membership(): bool {

		return $this->has_license( TD_TTW_License::MEMBERSHIP_TAG );
	}

	/**
	 * Returns a license which has 'all' in tags list
	 *
	 * @return TD_TTW_License|null
	 */
	public function get_membership() {

		return $this->get_license( TD_TTW_License::MEMBERSHIP_TAG );
	}

	/**
	 * Checks if active licenses array has a [all] tag license
	 * @return TD_TTW_License|null
	 */
	public function get_active_membership_license() {

		if ( ! empty( $this->_active_licenses['all'] ) && $this->_active_licenses['all'] instanceof TD_TTW_License ) {
			return $this->_active_licenses['all'];
		}

		return null;
	}


	/**
	 * Checks if in grace period licenses array has a [all] tag license
	 * @return TD_TTW_License|null
	 */
	public function get_in_grace_period_membership() {

		if ( ! empty( $this->_in_grace_period_licenses['all'] ) && $this->_in_grace_period_licenses['all'] instanceof TD_TTW_License ) {
			return $this->_in_grace_period_licenses['all'];
		}

		return null;
	}

	/**
	 * Get license instance based on a tag
	 *
	 * @param string $tag
	 *
	 * @return TD_TTW_License|null
	 */
	public function get_license( $tag ) {

		$license = null;

		if ( isset( $this->_licenses_instances[ $tag ] ) ) {
			$license = $this->_licenses_instances[ $tag ];
		}

		return $license;
	}

	/**
	 * Get license details
	 *
	 * @return array
	 */
	public function get_licenses_details() {

		if ( ! TD_TTW_Connection::get_instance()->is_connected() ) {
			return array();
		}

		$licenses_details = $this->_get_connection_licenses( TD_TTW_Connection::get_instance() );

		$this->_data = $licenses_details;

		$this->_init_licenses_instances();

		return $licenses_details;
	}

	/**
	 * Recheck license details
	 */
	public function recheck_license() {

		thrive_delete_transient( self::NAME );
		remove_query_arg( self::RECHECK_KEY );

		$this->get_licenses_details();

		if ( $this->has_membership() && $this->is_membership_active() ) {
			add_action( 'admin_notices', array( $this, 'success_notice' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'fail_notice' ) );
		}
	}

	public function success_notice() {

		TD_TTW_Messages_Manager::render( 'success-notice' );
	}

	public function fail_notice() {

		TD_TTW_Messages_Manager::render( 'expired-notice' );
	}

	/**
	 * Get recheck license url
	 *
	 * @return string
	 */
	public function get_recheck_url( $file = 'plugins.php' ) {

		if ( isset( $_REQUEST['page'] ) && sanitize_text_field( $_REQUEST['page'] ) === TD_TTW_Update_Manager::NAME ) {
			$url = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		} else {
			$url = admin_url( $file );
		}

		return add_query_arg(
			array(
				TD_TTW_User_Licenses::RECHECK_KEY => 1,
			),
			$url
		);
	}

	/**
	 * Render licenses screen
	 *
	 * @param false $return
	 *
	 * @return false|string
	 */
	public function render( $return = false ) {

		ob_start();
		include $this->path( 'templates/header.phtml' );
		include $this->path( 'templates/licences/list.phtml' );
		include $this->path( 'templates/debugger.phtml' );
		$html = ob_get_clean();

		if ( true === $return ) {
			return $html;
		}

		echo $html; //phpcs:ignore
	}

	/**
	 * Based on current connection a request is made to TTW for assigned licenses
	 *
	 * @param TD_TTW_Connection $connection
	 *
	 * @return array
	 */
	protected function _get_connection_licenses( TD_TTW_Connection $connection ) {

		if ( ! $connection->is_connected() ) {
			return array();
		}

		$licenses = thrive_get_transient( self::NAME );
		/* some sanity checks : there are cases when this is an array containing a single empty array. this IF identifies and corrects that case */
		if ( is_array( $licenses ) && ! empty( $licenses ) && empty( array_filter( $licenses ) ) ) {
			// force a re-fetch
			$licenses = false;
		}

		if ( false !== $licenses ) {

			return $licenses;
		}

		$params = array(
			'user_id'       => $connection->ttw_id,
			'user_site_url' => get_site_url(),
		);

		$route   = '/api/v1/public/get_licenses_details';
		$request = new TD_TTW_Request( $route, $params );
		$request->set_header( 'Authorization', $connection->ttw_salt );

		$proxy_request = new TD_TTW_Proxy_Request( $request );
		$response      = $proxy_request->execute( '/tpm/proxy' );

		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body, true );

		$response_status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_status_code ) {
			$error_message = isset( $body['message'] ) ? $body['message'] : 'It looks like there has been an error while fetching your ThriveThemes license details.';
			thrive_set_transient( 'td_ttw_connection_error', $error_message, self::CACHE_LIFE_TIME );
		}

		$cache_time = self::CACHE_LIFE_TIME;

		/**
		 * 200, //success
		 * 400, //bad request
		 * 401, //unauthorized
		 * 403, //forbidden rate limiter
		 */
		if ( $response_status_code >= 403 && ! empty( $this->_tr_licenses ) ) {
			$cache_time = self::CACHE_LIFE_TIME_SHORT;
			$body       = array(
				'success' => true,
				'data'    => $this->_tr_licenses,
			);
		}

		if ( ! is_array( $body ) || empty( $body['success'] ) ) {
			thrive_set_transient( self::NAME, array(), $cache_time );

			return array();
		}

		$licenses_details = $body['data'];

		thrive_set_transient( self::NAME, $licenses_details, $cache_time );
		thrive_delete_transient( 'td_ttw_connection_error' );

		return $licenses_details;
	}

	/**
	 * Check if there is any TTB license that allows updates - memberships are not included here
	 *
	 * @return bool
	 */
	public function can_update_ttb() {

		return $this->get_license( self::TTB_TAG ) && $this->get_license( self::TTB_TAG )->can_update();
	}

	public function get_active_license( $tag ) {

		$license = false;

		foreach ( $this->_active_licenses as $active_license ) {
			if ( $active_license->has_tag( $tag ) ) {
				$license = $active_license;
				break;
			}
		}

		return $license;
	}

	/**
	 * Checks is there is a license that allows user to user the product
	 * - firstly it looks for a membership active license
	 * - secondly it looks for a specific plugin active license
	 *
	 * @param string $tag plugin tag
	 *
	 * @return bool - plugin has/has not active license (will check membership tag also)
	 */
	public function has_active_license( string $tag ) {

		$has      = false;
		$licenses = thrive_get_transient( self::NAME );
		if ( empty( $licenses ) && ! is_array( $licenses ) ) {
			return true;
		}
		$active_membership = $this->get_active_membership_license();
		if ( $active_membership ) {
			$has = true;
		}

		if ( ! $has ) {
			foreach ( $this->_active_licenses as $license ) {
				if ( $license->has_tag( $tag ) ) {
					$has = true;
					break;
				}
			}
		}

		return $has;
	}

	/**
	 * Check if a plugin tag has a license which is in grace period
	 *
	 * @param string $tag //plugin representation for which we check license
	 *
	 * @return bool - plugin is/is not in grace period (will check membership tag also)
	 */
	public function is_in_grace_period( string $tag ) {

		$is = false;

		if ( ! $this->has_active_license( $tag ) ) {
			$in_grace_period_membership = $this->get_in_grace_period_membership();
			if ( $in_grace_period_membership ) {
				return true;
			}

			foreach ( $this->_in_grace_period_licenses as $license ) {
				if ( $license->has_tag( $tag ) && $license->is_in_grace_period() ) {
					$is = true;
					break;
				}
			}
		}

		return $is;
	}

	public function show_gp_lightbox( string $tag ) {
		$transient = 'tve_license_warning_lightbox_' . $tag;

		return empty( get_transient( $transient ) );
	}

	/**
	 * Check if a plugin tag has a license which is in grace period
	 * and calculate the number of days left in grace period
	 *
	 * @param string $tag
	 *
	 * @return int - number of days left in grace period
	 *               -1 if there is no license in grace period
	 */
	public function get_grace_period_left( string $tag ) {

		if ( ! $this->is_in_grace_period( $tag ) ) {
			return 0;
		}

		try {

			$membership = $this->get_in_grace_period_membership();
			$single     = ! empty( $this->_in_grace_period_licenses[ $tag ] ) ? $this->_in_grace_period_licenses[ $tag ] : null;

			$membership_days = 0;
			$single_days     = 0;

			if ( $membership ) {
				$membership_days = (int) $membership->get_remaining_grace_period()->format( '%a' ) + 1;
			}

			if ( $single ) {
				$single_days = (int) $single->get_remaining_grace_period()->format( '%a' ) + 1;
			}

			$days = max( $membership_days, $single_days );

		} catch ( Exception $e ) {
			$days = 0;
		}

		return $days;
	}
}
