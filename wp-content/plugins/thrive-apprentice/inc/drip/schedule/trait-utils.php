<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Schedule;

trait Utils {

	/**
	 * Get a datetime object from $datetime with WordPress's timezone setting, or the current datetime
	 *
	 * @param \DateTimeInterface|string $datetime
	 *
	 * @return \DateTime|\DateTimeInterface
	 */
	public static function get_datetime( $datetime = null ) {
		if ( $datetime === null ) {
			return current_datetime();
		}

		if ( $datetime instanceof \DateTimeInterface ) {
			return $datetime;
		}

		$instance = date_create( $datetime, wp_timezone() );

		/**
		 * Weird bug - the timezone does not get set when passing a @timestamp as parameter
		 */
		if ( strpos( $datetime, '@' ) === 0 ) {
			$instance->setTimezone( wp_timezone() );
		}

		return $instance;
	}

	/**
	 * Checks whether $test_date is before $reference_date
	 *
	 * @param \DateTimeInterface|string $test_date
	 * @param \DateTimeInterface|string $reference_date
	 *
	 * @return bool
	 */
	public static function is_past( $test_date, $reference_date = null ) {
		$reference_date = $reference_date ?: current_datetime();
		$reference_date = static::get_datetime( $reference_date );
		$test_date      = static::get_datetime( $test_date );

		return $test_date < $reference_date;
	}

	/**
	 * Returns a new DateTime object storing a date that's $months after $datetime.
	 * Takes into account overflows. e.g. 2021-01-30 + 1 month = 2021-02-28 ( uses the maximum available date if overflow occurs )
	 *
	 * @param \DateTimeInterface $datetime
	 * @param int                $months
	 *
	 * @return \DateTime
	 */
	protected static function add_months( \DateTimeInterface $datetime, $months ) {
		$month = (int) $datetime->format( 'n' ) + (int) $months;
		$year  = $datetime->format( 'Y' );
		$date  = $datetime->format( 'j' );
		if ( $month > 12 ) {
			$year  += floor( $month / 12 );
			$month = $month % 12;
		}
		/* treat special cases - if the corresponding month does not have enough days, use the maximum available */
		$date = static::ensure_date_available( "{$year}-{$month}", $date );

		return static::get_datetime( "{$year}-{$month}-{$date} " . $datetime->format( 'H:i:s' ) );
	}

	/**
	 * Returns a new DateTime object that contains the same properties as datetime, except for $date (day of month).
	 * Takes into account overflows ( if last day of month is lower than $date, $date will become last day of month)
	 *
	 * @param \DateTimeInterface $datetime
	 * @param int                $date
	 *
	 * @return \DateTime
	 */
	protected static function set_date( \DateTimeInterface $datetime, $date ) {

		$date = (int) $date;

		/* treat special cases - if the corresponding month does not have enough days, use the maximum available */
		$date = static::ensure_date_available( $datetime, $date );

		return static::get_datetime( $datetime->format( "Y-m-{$date} H:i:s" ) );
	}

	/**
	 * Ensure $day_of_month can be used with the month from $datetime. If not, use the maximum day of month available
	 *
	 * @param \DateTimeInterface|string $date
	 * @param string|int                $day_of_month
	 *
	 * @return string
	 */
	protected static function ensure_date_available( $date, $day_of_month ) {
		if ( $date instanceof \DateTime ) {
			$date = $date->format( 'Y-m' );
		}
		$temp          = static::get_datetime( $date );
		$days_in_month = $temp->format( 't' );

		return min( $day_of_month, $days_in_month );
	}

	/**
	 * Get the english weekday name based on day index
	 *
	 * @param string $weekday_index
	 *
	 * @return string
	 */
	public static function get_weekday_name( $weekday_index ) {
		$days = [
			'0' => 'sunday',
			'1' => 'monday',
			'2' => 'tuesday',
			'3' => 'wednesday',
			'4' => 'thursday',
			'5' => 'friday',
			'6' => 'saturday',
		];

		return isset( $days[ $weekday_index ] ) ? $days[ $weekday_index ] : $days['0'];
	}

	/**
	 * Get a ready-to-use list of weekdays
	 *
	 * @param bool $include_any whether to include the "Any" option
	 * @param bool $assoc       if true, get an assoc. array with index => label
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_weekdays( $include_any = false, $assoc = false ) {
		$days = [];
		if ( $include_any ) {
			$days [] = [ 'value' => '-1', 'label' => __( 'Any', 'thrive-apprentice' ) ];
		}

		$days[] = [ 'value' => '0', 'label' => __( 'Sunday', 'thrive-apprentice' ) ];
		$days[] = [ 'value' => '1', 'label' => __( 'Monday', 'thrive-apprentice' ) ];
		$days[] = [ 'value' => '2', 'label' => __( 'Tuesday', 'thrive-apprentice' ) ];
		$days[] = [ 'value' => '3', 'label' => __( 'Wednesday', 'thrive-apprentice' ) ];
		$days[] = [ 'value' => '4', 'label' => __( 'Thursday', 'thrive-apprentice' ) ];
		$days[] = [ 'value' => '5', 'label' => __( 'Friday', 'thrive-apprentice' ) ];
		$days[] = [ 'value' => '6', 'label' => __( 'Saturday', 'thrive-apprentice' ) ];

		return $assoc ? array_combine( array_column( $days, 'value' ), array_column( $days, 'label' ) ) : $days;
	}

	/**
	 * Get a ready-to-use list of monthly schedule options
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_monthly_schedule_options() {
		return [
			[ 'value' => 'any', 'label' => __( 'Any', 'thrive-apprentice' ) ],
			[ 'value' => 'date', 'label' => __( 'On a specific date (e.g day 21)', 'thrive-apprentice' ) ],
			[ 'value' => 'day', 'label' => __( 'On a specific day of the month (e.g first Monday in the month)', 'thrive-apprentice' ) ],
		];
	}

	/**
	 * Get a ready-to-use list of numeral orders for weekdays in a month.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_monthly_order_options() {
		return [
			[ 'value' => 'first', 'label' => __( 'First', 'thrive-apprentice' ) ],
			[ 'value' => 'second', 'label' => __( 'Second', 'thrive-apprentice' ) ],
			[ 'value' => 'third', 'label' => __( 'Third', 'thrive-apprentice' ) ],
			[ 'value' => 'fourth', 'label' => __( 'Fourth', 'thrive-apprentice' ) ],
			[ 'value' => 'last', 'label' => __( 'Last', 'thrive-apprentice' ) ],
		];
	}
}
