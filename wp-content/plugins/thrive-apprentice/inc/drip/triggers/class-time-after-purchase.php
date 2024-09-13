<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Trigger;

use TVA\Drip\Schedule\Utils;

/**
 * Class Time_After_Purchase
 *
 * @package TVA\Drip\Trigger
 */
class Time_After_Purchase extends Base {

	use Utils;

	/**
	 * Trigger Name
	 */
	const NAME = 'purchase';

	/**
	 * Event name
	 */
	const EVENT = 'tva_campaign_purchase_schedule';

	/**
	 * User Meta Key
	 */
	const USER_META_KEY = 'tva_purchase_schedule_post_%s_campaign_%s_completed';

	/**
	 * @var \TVA\Drip\Schedule\Non_Repeating
	 */
	protected $schedule = null;

	/**
	 * Returns true if the trigger is valid
	 *
	 * The trigger is valid only if the user has purchased the product the course belongs to & time passed after the purchase according to the trigger settings
	 *
	 * @param int $product_id
	 * @param int $post_id
	 *
	 * @return boolean
	 */
	public function is_valid( $product_id, $post_id ) {
		$user  = $this->get_tva_user();
		$order = $user->has_bought( $product_id );

		if ( ! $order instanceof \TVA_Order ) {

			if ( \TVA_SendOwl::is_connected() ) {

				$tva_term    = new \TVA_Term_Model( get_term( $product_id ) );
				$order_found = false;

				if ( $tva_term->is_protected_by_sendowl() ) {
					foreach ( $tva_term->get_all_sendowl_protection_ids() as $protection_id ) {
						$order = $user->has_bought( $protection_id );
						if ( $order instanceof \TVA_Order ) {
							$order_found = true;
							break;
						}
					}
				}
				if ( ! $order_found ) {
					return false;
				}
			} else {
				return false;
			}
		}

		$date_after_trigger_settings = $this->schedule->get_next_occurrence( static::get_datetime( $order->get_created_at() ) );

		return $date_after_trigger_settings && $date_after_trigger_settings < current_datetime();
	}

	/**
	 * Get the DateTime when user purchased the product
	 *
	 * @param array $args
	 *
	 * @return \DateTime|\DateTimeImmutable|null
	 */
	protected function _compute_original_event_date( $args ) {
		$user  = new \TVA_User( $args['user_id'] );
		$order = $user->has_bought( $args['product_id'] );

		if ( empty( $order ) ) {
			return null;
		}

		return static::get_datetime( $order->get_created_at() );
	}
}
