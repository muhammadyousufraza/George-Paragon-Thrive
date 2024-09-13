<?php

namespace TVA\Reporting\Events;

use TVA\Drip\Campaign;
use TVA\Product;
use TVA\Reporting\EventFields\Campaign_Id;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Product_Id;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class Drip_Unlocked_For_User extends Event {
	use Report;

	public static function key(): string {
		return 'tva_drip_unlocked_for_user';
	}

	public static function label(): string {
		return __( 'Drip unlocked for user', 'thrive-apprentice' );
	}

	public static function get_extra_int_field_1(): string {
		return Course_Id::class;
	}

	public static function get_extra_int_field_2(): string {
		return Campaign_Id::class;
	}

	public static function get_extra_float_field() {
		return Product_Id::class;
	}

	public static function register_action() {

		add_action( 'tva_drip_content_unlocked_for_specific_user', static function ( $user, $post, $product_term ) {
			/**
			 * @var \WP_User $user
			 * @var \WP_Post $post
			 * @var \WP_Term $product_term
			 */
			$tva_post = \TVA_Post::factory( $post );

			if ( ! $tva_post instanceof \TVA_Post ) {
				return;
			}

			$product = new Product( $product_term );
			$course  = $tva_post->get_course_v2();

			if ( ! $course instanceof \TVA_Course_V2 ) {
				return;
			}

			$campaign = $product instanceof Product ? $product->get_drip_campaign_for_course( $course ) : null;
			$event    = new static( [
				'item_id'     => $post->ID,
				'user_id'     => $user->ID,
				'post_id'     => $post->ID,
				'course_id'   => $course->get_id(),
				'campaign_id' => $campaign instanceof Campaign ? $campaign->ID : 0,
				'product_id'  => $product->get_id(),
			] );

			$event->log();
		}, 10, 3 );
	}

	/**
	 * Event description - used for user timeline
	 *
	 * @return string|false
	 */
	public function get_event_description() {
		$course = new \TVA_Course_V2( (int) $this->get_field( 'course_id' )->get_value() );

		if ( ! $course instanceof \TVA_Course_V2 ) {
			return false;
		}

		$item         = $this->get_field( 'item_id' )->get_title();
		$course_title = $this->get_field( 'course_id' )->get_title();
		$post         = get_post( $this->get_field( 'item_id' )->get_value() );
		$tva_post     = \TVA_Post::factory( $post );
		$type         = '';

		if ( $tva_post instanceof \TVA_Module ) {
			$type = __( 'module', 'thrive-apprentice' );
		} elseif ( $tva_post instanceof \TVA_Lesson ) {
			$type = __( 'lesson', 'thrive-apprentice' );
		}

		return ' unlocked ' . $type . ' "' . $item . '" in the course "' . $course_title . '".';
	}
}
