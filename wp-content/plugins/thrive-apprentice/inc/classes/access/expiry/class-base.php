<?php

namespace TVA\Access\Expiry;

use TVA\Drip\Schedule\Utils;
use TVA\Product;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Base {

	use Utils;

	/**
	 * @var string
	 */
	const CONDITION = '';

	/**
	 * @var string
	 */
	const EVENT = '';

	/**
	 * Reminder Event
	 * It is necessary different events for different expiry types because this events have different parameters
	 *
	 * @var string
	 */
	const EVENT_REMINDER = '';

	/**
	 * Meta key that is stored on product when it is schedule to expire
	 */
	const PRODUCT_EXPIRY_META_KEY = 'tva_access_expiry_specific_time';

	/**
	 * @var Product|null Product
	 */
	protected $product;

	/**
	 * @param Product $product
	 */
	public function __construct( $product ) {
		$this->product = $product;
	}

	/**
	 * Returns the user meta
	 *
	 * Has the dynamic product ID inside its name
	 *
	 * @return string
	 */
	protected function get_product_user_meta_key() {
		return 'tva_product_' . $this->product->get_id() . '_access_expiry';
	}


	/**
	 * Returns meta key name
	 *
	 * @param int $product_id
	 *
	 * @return string
	 */
	public static function get_meta_key_name( $product_id ) {
		return 'tva_product_' . $product_id . '_access_expiry';
	}

	/**
	 * Expiry factory
	 *
	 * @param Product $product
	 * @param string  $condition
	 *
	 * @return $this
	 */
	public static function factory( $product, $condition = null ) {

		/**
		 * If condition is null, we take the active condition
		 */
		if ( empty( $condition ) && $product->has_access_expiry() ) {
			$condition = $product->get_access_expiry()['expiry']['cond'];
		}

		switch ( $condition ) {
			case After_Purchase::CONDITION:
				$class_name = 'After_Purchase';
				break;
			case Specific_Time::CONDITION:
				$class_name = 'Specific_Time';
				break;
			default:
				$class_name = 'Base';
				break;
		}

		$class = __NAMESPACE__ . '\\' . $class_name;

		return new $class( $product );
	}

	/**
	 * @param int     $user_id
	 * @param Product $product
	 *
	 * @return string
	 */
	private static function get_expiry_time_from_source( $user_id, $product ) {
		$meta = get_user_meta( $user_id, static::get_meta_key_name( $product->get_id() ), true );

		if ( empty( $meta ) ) {
			$meta = get_term_meta( $product->get_id(), static::PRODUCT_EXPIRY_META_KEY, true );
		}

		return $meta;
	}

	/**
	 * Returns true if product access has expired
	 *
	 * @param int     $user_id
	 * @param Product $product
	 *
	 * @return bool
	 */
	public static function access_has_expired( $user_id, $product ) {
		if ( ! ( $product instanceof Product ) || empty( $user_id ) || ! $product->has_access_expiry() ) {
			return false;
		}

		$meta = static::get_expiry_time_from_source( $user_id, $product );

		if ( empty( $meta ) ) {
			return false;
		}

		$dateTime = static::get_datetime( $meta );

		return static::is_past( $dateTime );
	}

	public static function is_about_to_expire( $user_id, $product ) {
		if ( ! ( $product instanceof Product ) || empty( $user_id ) || ! $product->has_access_expiry() ) {
			return false;
		}

		if ( static::access_has_expired( $user_id, $product ) ) {
			return false;
		}

		$meta = static::get_expiry_time_from_source( $user_id, $product );

		if ( empty( $meta ) ) {
			return false;
		}

		$expire_threshold = current_datetime()->add( new \DateInterval( "P14D" ) );

		return static::is_past( $meta, $expire_threshold );
	}

	/**
	 * @param $user_id
	 * @param $product
	 *
	 * @return float|int
	 */
	public static function get_days_until_expiration( $user_id, $product ) {
		if ( ! static::is_about_to_expire( $user_id, $product ) ) {
			return 0;
		}

		$meta = static::get_expiry_time_from_source( $user_id, $product );

		if ( empty( $meta ) ) {
			return 0;
		}

		$date_diff = abs( strtotime( $meta ) - time() );

		return round( $date_diff / ( 60 * 60 * 24 ) );
	}

	/**
	 * Returns true if product access has expired
	 *
	 * @param int     $user_id
	 * @param Product $product
	 *
	 * @return bool
	 */
	public static function access_expired_should_redirect( $user_id, $product ) {
		if ( ! static::access_has_expired( $user_id, $product ) ) {
			return false;
		}

		return $product->has_access_expiry_redirect();
	}

	/**
	 * @param int   $state
	 * @param mixed $condition
	 *
	 * @return void
	 */
	public function toggle( $state, $condition ) {
		if ( (int) $state === 1 ) {
			$this->add( $condition );
		} else {
			$this->remove( $condition );
		}
	}

	/**
	 * Triggered only when reminder has been modified
	 * Overridden in child classes
	 *
	 * @param $expiry
	 * @param $old_reminder
	 * @param $new_reminder
	 *
	 * @return void
	 */
	public function reminder_modified( $expiry, $old_reminder, $new_reminder ) {
	}

	public function add( $condition ) {
	}

	public function remove( $condition ) {
	}

	/**
	 * Adds reminder
	 *
	 * @param \DateTimeInterface|string $datetime
	 * @param array                     $reminder
	 * @param array                     $event_args
	 *
	 * @return $this
	 */
	public function add_reminder( $datetime, $reminder, $event_args = [] ) {
		if ( ! $this->has_reminder( $reminder ) ) {
			return $this;
		}

		if ( ! $datetime instanceof \DateTimeInterface ) {
			$datetime = static::get_datetime( $datetime );
		}

		$reminder_datetime = $this->get_reminder_datetime( $datetime, $reminder );

		$this->schedule_event( $reminder_datetime, $event_args, static::EVENT_REMINDER );

		return $this;
	}

	/**
	 * Removes reminder
	 *
	 * @param \DateTimeInterface|string $datetime
	 * @param array                     $reminder
	 * @param array                     $event_args
	 *
	 * @return $this
	 */
	public function remove_reminder( $datetime, $reminder, $event_args = [] ) {
		if ( ! $datetime instanceof \DateTimeInterface ) {
			$datetime = static::get_datetime( $datetime );
		}

		$reminder_datetime = $this->get_reminder_datetime( $datetime, $reminder );

		$this->unschedule_event( $reminder_datetime, $event_args, static::EVENT_REMINDER );

		return $this;
	}

	/**
	 * Schedules the event
	 *
	 * @param \DateTimeInterface $date
	 * @param array              $event_args
	 * @param string|null        $event
	 *
	 * @return void
	 */
	protected function schedule_event( $date, $event_args = [], $event = null ) {
		$args = $this->get_event_args( $event_args );

		if ( empty( $event ) ) {
			$event = static::EVENT;
		}

		if ( $this->is_event_scheduled( $event, $args ) ) {
			/**
			 * This means that a hook is already registered with these arguments
			 */
			return;
		}

		wp_schedule_single_event( $date->getTimestamp(), $event, $args );
	}

	/**
	 * @param \DateTimeInterface $date
	 * @param array              $event_args
	 * @param string|null        $event
	 *
	 * @return void
	 */
	public function unschedule_event( $date, $event_args = [], $event = null ) {
		if ( empty( $event ) ) {
			$event = static::EVENT;
		}

		wp_unschedule_event( $date->getTimestamp(), $event, $this->get_event_args( $event_args ) );
	}

	public function clear_user_meta() {
		delete_metadata( 'user', 0, $this->get_product_user_meta_key(), '', true );
	}

	/**
	 * Returns true if there is an event scheduled for the campaign parameters
	 *
	 * @param string $event
	 * @param array  $args
	 *
	 * @return bool
	 */
	public function is_event_scheduled( $event = null, $args = [] ) {
		if ( empty( $event ) ) {
			$event = static::EVENT;
		}

		return wp_get_scheduled_event( $event, $args ) !== false;
	}

	/**
	 * @param \DateTimeInterface $event_datetime
	 * @param array              $reminder
	 *
	 * @return \DateTimeInterface
	 */
	public function get_reminder_datetime( $event_datetime, $reminder ) {
		$unit   = strtoupper( $reminder['unit'] );
		$number = (int) $reminder['number'];


		return $event_datetime->sub( new \DateInterval( "P$number$unit" ) );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	private function get_event_args( $args = [] ) {
		return array_merge( [ (int) $this->product->get_id() ], $args );
	}

	/**
	 * Validate reminder config
	 *
	 * @param array $reminder
	 *
	 * @return bool
	 */
	private function has_reminder( $reminder ) {
		if ( empty( $reminder ) || empty( $reminder['enabled'] ) || empty( $reminder['number'] ) || empty( $reminder['unit'] ) ) {
			return false;
		}

		return true;
	}
}
