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
 * Handles update related messages across wp-admin
 *
 * Class TD_TTW_Messages_Manager
 */
class TD_TTW_Messages_Manager {

	use TD_Singleton;

	use TD_TTW_Utils;

	private function __construct() {

		/**  @var TD_TTW_User_Licenses $licenses */
		$licenses = TD_TTW_User_Licenses::get_instance();

		if ( $licenses->get_membership() && ! $licenses->get_membership()->can_update() ) {
			//add_action( 'admin_notices', array( __CLASS__, 'inactive_membership' ) );
		}
	}

	/**
	 * Render a message from templates/messages/ directory
	 * First param should be template name
	 * Second whether or not to echo/return the output
	 * Third an array with needed vars in template
	 *
	 * @return false|string
	 */
	public static function render() {

		$args     = func_get_args();
		$template = ! empty( $args[0] ) ? $args[0] : null;

		if ( empty( $template ) ) {
			return false;
		}

		$action = ! empty( $args[1] ) && 1 === (int) $args[1] ? 'return' : 'echo';
		$vars   = ! empty( $args[2] ) && is_array( $args[2] ) ? $args[2] : array();

		/**
		 * Prepare variables names for template file
		 * $key => var name
		 * $value => var value
		 */
		foreach ( $vars as $key => $value ) {
			$$key = $value;
		}

		ob_start();

		include self::path( 'templates/messages/' . $template . '.phtml' );

		$html = ob_get_clean();

		if ( 'return' === $action ) {
			return $html;
		}

		echo $html; //phpcs:ignore
	}

	/**
	 * Show license related notices in wp dash
	 */
	public static function inactive_membership() {
		/**  @var TD_TTW_User_Licenses $licenses */
		$licenses   = TD_TTW_User_Licenses::get_instance();
		$membership = $licenses->get_membership();

		if ( ! $membership ) {
			return;
		}

		if ( TD_TTW_Connection::get_instance()->is_connected() ) {
			$tpl = 'expired-connected';
		} else {
			$tpl = 'expired-disconnected';
		}

		self::render(
			$tpl,
			false,
			array(
				'membership_name' => $membership->get_name(),
			)
		);
	}

	/**
	 * Get plugin update message
	 *
	 * @param stdClass $state
	 * @param array    $plugin_data
	 *
	 * @return string|null
	 *      - `` empty string means: no custom message is returned so let the UpdateChecker do its logic
	 *      - `null` means that no license was fond for the plugin and no update should be provided by the UpdateChecker
	 */
	public static function get_update_message( $state, $plugin_data ) {

		$message       = '';
		$template      = null;
		$template_data = array(
			'state'       => $state,
			'plugin_data' => $plugin_data,
			'recheck_url' => TD_TTW_User_Licenses::get_instance()->get_recheck_url(),
		);

		$plugin_tag = TVE_Dash_Product_LicenseManager::get_product_tag( $plugin_data['TextDomain'] );
		if ( TVE_Dash_Product_LicenseManager::TPM_TAG === $plugin_tag ) {
			return $message;
		}

		$is_connected = TD_TTW_Connection::get_instance()->is_connected();

		if ( false === $is_connected ) {
			$template = 'plugin/disconnected';
		} else {
			/** @var TD_TTW_User_Licenses $licenses */
			$licenses   = TD_TTW_User_Licenses::get_instance();
			$membership = $licenses->get_membership();
			$license    = $licenses->get_license( $plugin_tag );

			if ( ( $membership && $membership->can_update() ) || ( $license && $license->can_update() ) ) {
				return $message;
			}

			if ( $membership && false === $membership->can_update() ) {
				$template = 'plugin/membership-expired';
			} elseif ( null === $license && null === $membership ) {
				$template = 'plugin/no-license-found';
			} elseif ( $license && false === $license->can_update() ) {
				$template = 'plugin/license-expired';
			}
		}

		$error = thrive_get_transient( 'td_ttw_connection_error' );
		if ( ! empty( $error ) ) {
			$template                       = 'error';
			$template_data['error_message'] = $error;
		}

		if ( $template ) {
			$message = static::render(
				$template,
				true,
				$template_data
			);
		}

		return $message;
	}
}

TD_TTW_Messages_Manager::get_instance();
