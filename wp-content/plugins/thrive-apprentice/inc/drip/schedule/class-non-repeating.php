<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Schedule;

class Non_Repeating extends Base {
	/**
	 * Number of $intervals that must pass between 2 events.
	 * Usually a number between 1 and 5.
	 * For daily repeats, this can also have the values 'weekday' or 'weekend'
	 *
	 * @see Repeating::get_daily_options()
	 *
	 * @var string
	 */
	protected $interval_number = '1';

	/**
	 * Interval counter works in combination with weekdays and weekends interval number
	 *
	 * @see Repeating
	 *
	 * @var int
	 */
	protected $interval_counter = 1;

	/**
	 * Name of the interval. can be hours/days/weeks/months.
	 *
	 * @var string
	 */
	protected $interval = 'weeks';

	/**
	 * Index of the weekday.
	 *
	 * @see Utils::get_weekdays()
	 *
	 * @var string
	 */
	protected $weekday = '-1';

	/**
	 * Type of monthly schedule.
	 *
	 * @see Utils::get_monthly_schedule_options()
	 *
	 * @var string
	 */
	protected $monthly_type = 'any';

	/**
	 * Applicable for monthly_type = 'date'. Which date of the month should this occur.
	 * Range from 1 - 31
	 *
	 * @var string
	 */
	protected $day_of_month = '1';

	/**
	 * Applicable for monthly_type = 'day'. Holds the order numeral as a string.
	 *
	 * @see Utils::get_monthly_order_options()
	 *
	 * @var string
	 */
	protected $monthly_day_order = 'first';

	/**
	 * Get the next occurrence after $from_date.
	 *
	 * @param \DateTimeInterface|string|null $from_date if null, it will default to WordPress's current time
	 *
	 * @return \DateTimeInterface|null null if no event occurs after $from_date
	 */
	public function get_next_occurrence( $from_date = null ) {
		$from_date = static::get_datetime( $from_date );

		/* start from $from_date and figure out the date at which the event should happen */
		$event_date = static::get_datetime( $from_date->format( 'Y-m-d H:i:s' ) . ' +' . $this->interval_number . ' ' . $this->interval );

		switch ( $this->interval ) {
			case 'months':
				if ( $this->monthly_type === 'date' ) {
					/*
					 * the event should occur on day X of the calculated month
					 * e.g. $from_date is a date in feb, and $this->interval_number = 1, $this->day_of_month = 23
					 * ===> event should occur on March 23rd
					 */
					$event_date = static::add_months( $from_date, (int) $this->interval_number );
					$event_date = static::set_date( $event_date, $this->day_of_month );

				} elseif ( $this->monthly_type === 'day' ) {
					/* event should occur in the first/second/third etc {WEEKDAY} of that month */
					$event_date = static::add_months( $from_date, (int) $this->interval_number );

					/* e.g. "first Sunday of 2021-10" */
					$event_date->modify( $this->monthly_day_order . ' ' . ucfirst( static::get_weekday_name( $this->weekday ) ) . ' of ' . $event_date->format( 'Y-m H:i:s' ) );
				}
				break;
			case 'weeks':
				/* special case: specific weekday */
				if ( $this->weekday !== '-1' ) {
					/*
					 * the event should occur on day X of the calculated week
					 * e.g. $from_date is a date in week no. 30 of a year, and $this->interval_number = 1, $this->weekday = 1 ( Monday )
					 * ===> event should occur in week no. 31 of the year, on Monday
					 */
					$event_weekday = $event_date->format( 'w' ); // 0 = Sunday, 6 = Saturday

					/* add the difference in days between the needed day_of_week and $event_weekday */
					/* we need to account for Sunday having index "0" */
					$target        = $this->weekday === '0' ? '7' : $this->weekday;
					$event_weekday = $event_weekday === '0' ? '7' : $event_weekday;
					$diff          = \DateInterval::createFromDateString( ( $target - $event_weekday ) . ' days' );
					$event_date->add( $diff );
				}
				break;
			case 'days':
			case 'hours':
			default:
				break;
		}

		return $event_date;
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
			[ 'value' => 'hours', 'label' => __( 'Hour(s)', 'thrive-apprentice' ) ],
			[ 'value' => 'days', 'label' => __( 'Day(s)', 'thrive-apprentice' ) ],
			[ 'value' => 'weeks', 'label' => __( 'Week(s)', 'thrive-apprentice' ) ],
			[ 'value' => 'months', 'label' => __( 'Month(s)', 'thrive-apprentice' ) ],
		];
	}
}
