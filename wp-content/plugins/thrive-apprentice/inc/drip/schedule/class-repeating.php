<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Schedule;

class Repeating extends Non_Repeating {
	/**
	 * Get the next occurrence after $from_date.
	 *
	 * @param \DateTimeInterface|string|null $from_date if null, it will default to WordPress's current time
	 *
	 * @return \DateTimeInterface|null null if no event occurs after $from_date
	 */
	public function get_next_occurrence( $from_date = null ) {
		$from_date  = static::get_datetime( $from_date );
		$event_date = static::get_datetime( $from_date->format( 'Y-m-d H:i:s' ) );

		switch ( $this->interval ) {
			case 'days':
				/**
				 * Event should be repeated daily only on weekends or weekdays
				 *
				 * format( 'w' ) -> 0 = Sunday, 6 = Saturday
				 */
				if ( $this->interval_number === 'weekday' ) {
					for ( $i = 1; $i <= $this->interval_counter; $i ++ ) {
						$number_of_days = $from_date->format( 'w' ) === '5' ? 3 : 1;

						$event_date->add( \DateInterval::createFromDateString( $number_of_days . 'days' ) );

						$from_date = $event_date;
					}
					break;
				} else if ( $this->interval_number === 'weekend' ) {
					for ( $i = 1; $i <= $this->interval_counter; $i ++ ) {
						$number_of_days = $from_date->format( 'w' ) === '6' ? 1 : 6 - $from_date->format( 'w' );

						$event_date->add( \DateInterval::createFromDateString( $number_of_days . 'days' ) );

						$from_date = $event_date;
					}
					break;
				}
			case 'weeks':
			case 'months':
			default:
				$event_date = parent::get_next_occurrence( $from_date );
		}

		return $event_date;
	}

	/**
	 * Get the options for `daily` repeat
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_daily_options() {
		$values = [];
		foreach ( range( 1, 5 ) as $item ) {
			$values [] = [ 'value' => (string) $item, 'label' => (string) $item ];
		}

		return array_merge( $values, [
			[ 'value' => 'weekday', 'label' => __( 'Only on weekdays', 'thrive-apprentice' ) ],
			[ 'value' => 'weekend', 'label' => __( 'Only over the weekend', 'thrive-apprentice' ) ],
		] );
	}

	/**
	 * Get available options for the "Interval" dropdown
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_interval_options() {
		return [
			[ 'value' => 'days', 'label' => __( 'Daily', 'thrive-apprentice' ) ],
			[ 'value' => 'weeks', 'label' => __( 'Weekly', 'thrive-apprentice' ) ],
			[ 'value' => 'months', 'label' => __( 'Monthly', 'thrive-apprentice' ) ],
		];
	}
}
