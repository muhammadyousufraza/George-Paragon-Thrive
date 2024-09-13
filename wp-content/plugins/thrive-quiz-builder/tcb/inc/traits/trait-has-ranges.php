<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TCB\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

trait Has_Ranges {
	/**
	 * @param $ranges
	 * @param $new_start
	 * @param $new_end
	 *
	 * @return array
	 */
	public static function add_range( $ranges, $new_start, $new_end ) {
		$normalized_ranges = [];

		static::add_range_to_normalized_array( $ranges, $normalized_ranges, [ 'start' => $new_start, 'end' => $new_end ] );

		return $normalized_ranges;
	}

	/**
	 * @param $ranges
	 *
	 * @return int
	 */
	public static function get_duration( $ranges ) {
		$total_duration = 0;

		foreach ( $ranges as $range ) {
			$total_duration += ( $range['end'] - $range['start'] );
		}

		return $total_duration;
	}

	/**
	 * Adds a new range to the array, while making sure that the array stays normalized (ordered and non-overlapping ranges)
	 * When it finds an overlapping interval, it merges it with the new interval and then continues to merge the next ranges, if possible.
	 * After merging, calls itself recursively on the remaining ranges. (in theory it can only recurse once, but it's still more readable)
	 *
	 * @param $ranges
	 * @param $normalized_ranges
	 * @param $range_to_add
	 *
	 * @return void
	 */
	private static function add_range_to_normalized_array( $ranges, &$normalized_ranges, $range_to_add ) {
		if ( empty( $ranges ) ) {
			$normalized_ranges[] = $range_to_add;

			return;
		}

		$added = false;

		foreach ( $ranges as $index => $range ) {
			if ( static::is_before_range( $range_to_add, $range ) ) {
				/* if there's no overlap and the end of the inserted range < the start of the current range, push it before the current range */
				if ( ! $added ) {
					$normalized_ranges[] = $range_to_add;
					$added               = true;
				}
				$normalized_ranges[] = $range;
			} else if ( static::is_range_overlap( $range_to_add, $range ) ) {
				/* if we detect an overlap, merge all the possible ranges ahead */
				$next_ranges = \array_slice( $ranges, $index );

				static::merge_overlapping_ranges( $next_ranges, $range_to_add );
				/* do recursion on the remaining array */
				static::add_range_to_normalized_array( $next_ranges, $normalized_ranges, $range_to_add );
				break;
			} else {
				/* if the new range > current range, don't add it yet (unless we reached the end) */
				$normalized_ranges[] = $range;

				if ( $range === end( $ranges ) ) {
					/* if this is the last element, add the new range to the end of the array */
					$normalized_ranges[] = $range_to_add;
				}
			}
		}
	}

	/**
	 * @param $ranges
	 * @param $new_range
	 *
	 * @return void
	 */
	private static function merge_overlapping_ranges( &$ranges, &$new_range ) {
		foreach ( $ranges as $index => $range ) {
			if ( $new_range['end'] >= $range['start'] ) {
				/* if we detect an overlap, 'update' the current range */
				$new_range['start'] = min( $new_range['start'], $range['start'] );
				$new_range['end']   = max( $new_range['end'], $range['end'] );
				unset( $ranges[ $index ] );
			} else {
				/* if there's no more overlapping ranges, it's time to stop the merge and continue the iteration through the remaining ranges */
				break;
			}
		}
	}

	/**
	 * @param $range_to_add
	 * @param $range
	 *
	 * @return bool
	 */
	private static function is_before_range( $range_to_add, $range ) {
		return static::compare_ranges( $range_to_add, $range ) === - 1;
	}

	/**
	 * @param $range_to_add
	 * @param $range
	 *
	 * @return bool
	 */
	private static function is_range_overlap( $range_to_add, $range ) {
		return static::compare_ranges( $range_to_add, $range ) === 0;
	}

	/**
	 * @param $first_interval
	 * @param $second_interval
	 *
	 * @return int
	 */
	private static function compare_ranges( $first_interval, $second_interval ) {
		if ( $first_interval['end'] < $second_interval['start'] ) {
			return - 1;
		} else if ( $first_interval['start'] <= $second_interval['end'] ) {
			return 0;
		} else {
			return 1;
		}
	}
}
