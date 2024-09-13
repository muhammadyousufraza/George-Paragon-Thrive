<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

namespace TU\Automator;

use Thrive\Automator\Items\Email_Data;
use Thrive\Automator\Items\Form_Data;
use Thrive\Automator\Items\User_Data;
use function Thrive\Automator\tap_logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Start_Campaign extends \Thrive\Automator\Items\Action {
	private $campaign_id;
	private $has_lockdown;

	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'ultimatum/start_campaign';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return __( 'Start an Ultimatum Campaign', 'thrive-ult' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_description() {
		return __( 'Trigger an Ultimatum campaign for a user on the site', 'thrive-ult' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_image() {
		return 'tap-ultimatum-logo';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_app_id() {
		return Ultimatum_App::get_id();
	}

	/**
	 * @inheritDoc
	 */
	public static function get_required_action_fields() {
		return array( 'start_campaign_id' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_required_data_objects() {
		return array( Email_Data::get_id(), Form_Data::get_id(), User_Data::get_id() );
	}

	public function prepare_data( $data = array() ) {

		if ( ! empty( $data['extra_data'] ) ) {
			$data = $data['extra_data'];
		}
		$this->campaign_id  = $data['start_campaign_id']['value'];
		$this->has_lockdown = get_post_meta( $this->campaign_id, \TVE_Ult_Const::META_NAME_FOR_LOCKDOWN, true );
	}

	/**
	 * @inheritDoc
	 */
	public function do_action( $data ) {
		global $automation_data;

		/**
		 * Go through all the data objects and find the first one that has the email
		 */
		$email = '';
		$sets  = static::get_required_data_objects();
		$index = 0;

		while ( empty( $email ) && $index < count( $sets ) ) {
			if ( ! empty( $automation_data->get( $sets[ $index ] ) ) ) {
				$email = $automation_data->get( $sets[ $index ] )->get_provided_email();
			}
			$index ++;
		}

		tu_start_campaign( $this->campaign_id, $email );
	}

	/**
	 * Match all trigger that provice user/form data
	 *
	 * @param $trigger
	 *
	 * @return bool
	 */
	public static function is_compatible_with_trigger( $provided_data_objects ) {
		$action_data_keys = static::get_required_data_objects() ?: array();

		return count( array_intersect( $action_data_keys, $provided_data_objects ) ) > 0;
	}

	/**
	 * @inheritDoc
	 */
	public function can_run( $data ) {
		$valid = true;

		/**
		 * No need to check for sets if its a open campaign
		 */
		if ( $this->has_lockdown ) {
			$available_data = array();

			global $automation_data;
			foreach ( static::get_required_data_objects() as $key ) {
				if ( ! empty( $automation_data->get( $key ) ) && ! empty( $automation_data->get( $key )->get_provided_email() ) ) {
					$available_data[] = $key;
				}
			}

			if ( empty( $available_data ) ) {
				$valid = false;
				tap_logger()->register( [
					'key'         => static::get_id(),
					'id'          => 'data-not-provided-to-action',
					'message'     => 'Data object required by ' . static::class . ' action is not provided by trigger',
					'class-label' => tap_logger()->get_nice_class_name( static::class ),
				] );
			}
		}

		return $valid;
	}
}
