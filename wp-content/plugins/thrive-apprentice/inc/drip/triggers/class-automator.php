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
class Automator extends Base {

	use Utils;

	/**
	 * Trigger Name
	 */
	const NAME = 'automator';

	/**
	 * Returns true if the trigger is valid
	 *
	 * The trigger is valid only if the content has been unlocked globally or for a specific user
	 *
	 * @param int $product_id
	 * @param int $post_id
	 *
	 * @return boolean
	 */
	public function is_valid( $product_id, $post_id ) {
		$is_valid = ! empty( get_post_meta( $post_id, 'tva_drip_content_unlocked_for_everyone', true ) ) || in_array( $post_id, $this->campaign->get_customer()->get_drip_content_unlocked() );

		if ( $is_valid ) {
			switch ( $this->campaign->trigger ) {
				case 'datetime':
					$is_valid = static::is_past( $this->campaign->unlock_date );
					break;
				case 'purchase':
					$is_valid = $this->get_tva_user()->has_bought( $product_id ) instanceof \TVA_Order;
					break;
				case 'first-lesson':
					$is_valid = ! empty( $this->get_customer()->get_begin_course_timestamp( $this->get_course()->get_id() ) );
					break;
			}
		}


		return $is_valid;
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
