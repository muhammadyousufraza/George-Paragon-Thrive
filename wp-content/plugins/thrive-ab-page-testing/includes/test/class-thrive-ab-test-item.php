<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Test_Item extends Thrive_AB_Model {

	/**
	 * @var Thrive_AB_Test_Item
	 */
	protected $control;

	/**
	 * @return array
	 */
	public function get_data() {
		return array(
			'id'                  => $this->id,
			'variation_id'        => $this->variation_id,
			'test_id'             => $this->test_id,
			'thankyou_id'         => $this->thankyou_id,
			'page_id'             => $this->page_id,
			'title'               => $this->title,
			'is_control'          => (bool) $this->is_control,
			'active'              => $this->active,
			'is_winner'           => $this->is_winner,
			'impressions'         => $this->impressions,
			'unique_impressions'  => $this->unique_impressions,
			'stopped_date'        => date( 'j F Y', strtotime( $this->stopped_date ) ),
			'preview_link'        => $this->get_preview_link(),
			'editor_link'         => $this->get_editor_link(),
			'traffic'             => $this->get_traffic(),
			'conversions'         => $this->conversions,
			'revenue'             => $this->revenue,
			'revenue_visitor'     => $this->revenue_per_visitor(),
			'conversion_rate'     => $this->conversion_rate(),
			'improvement'         => $this->get_improvement(),
			'chance_to_beat_orig' => $this->get_chance_to_beat_original(),
		);
	}

	public function get_preview_link() {

		$url = '';

		/**@var $this ->>variation Thrive_AB_Variation */
		if ( $this->variation instanceof Thrive_AB_Variation ) {
			$url = $this->variation->get_preview_url();
		}

		return $url;
	}

	public function get_editor_link() {

		$url = '';

		/**@var $this ->>variation Thrive_AB_Variation */
		if ( $this->variation instanceof Thrive_AB_Variation ) {
			if ( current_user_can( 'edit_post', $this->variation->get_data()['ID'] ) ) {
				$url = $this->variation->get_editor_url();
			}
		}

		return $url;
	}

	public function get_traffic() {
		$traffic = '';

		/**@var $this ->>variation Thrive_AB_Variation */
		if ( $this->variation instanceof Thrive_AB_Variation ) {
			$traffic = (int) $this->variation->get_meta()->get( 'traffic' );
		}

		return $traffic;
	}

	/**
	 * @return mixed|null
	 */
	public function get_impressions() {
		return $this->unique_impressions;
	}

	/**
	 * @return mixed|null
	 */
	public function get_conversions() {
		return $this->conversions;
	}

	/**
	 * @return float
	 */
	public function get_chance_to_beat_original() {

		$chance = $this->chance( $this->get_control()->conversion_rate(), $this->get_control()->unique_impressions );

		if ( $chance === false ) {
			return 0.0;
		}

		return $chance;
	}

	/**
	 * Returns the improvement for current test item
	 *
	 * @return float
	 */
	public function get_improvement() {

		$control_conversion_rate = $this->get_control()->conversion_rate();

		if ( $control_conversion_rate == 0 ) {
			return 0;
		}

		return round( ( ( $this->conversion_rate() - $control_conversion_rate ) * 100 ) / $control_conversion_rate, 2 );
	}

	/**
	 * Calculates the revenue per visitor
	 *
	 * @return float
	 */
	public function revenue_per_visitor() {

		$this->unique_impressions = (int) $this->unique_impressions;
		$value                    = 0.0;

		if ( $this->unique_impressions !== 0 ) {
			$value = round( $this->revenue / $this->unique_impressions, 2 );
		}

		return $value;
	}

	/**
	 * Calculate conversion rate for current test item
	 *
	 * @return float
	 */
	public function conversion_rate() {

		$conversions     = (int) $this->conversions;
		$impressions     = (int) $this->unique_impressions;
		$conversion_rate = 0.0;

		if ( $conversions !== 0 && $impressions !== 0 ) {
			$conversion_rate = round( 100 * ( $conversions / $impressions ), 2 );
		}

		return $conversion_rate;
	}

	/**
	 * Calculate the chance of current variation to beat the original during a test
	 *
	 * @param float $control_conversion_rate
	 * @param float $control_unique_impressions
	 *
	 * @return string
	 */
	public function chance( $control_conversion_rate, $control_unique_impressions ) {

		if ( $this->unique_impressions == 0 || $control_unique_impressions == 0 ) {
			return false;
		}

		$variation_conversion_rate = $this->conversion_rate() / 100;
		$control_conversion_rate   = (float) $control_conversion_rate / 100;

		//standard deviation = sqrt((conversionRate*(1-conversionRate)/uniqueImpressions)
		$variation_standard_deviation = sqrt( ( $variation_conversion_rate * ( 1 - $variation_conversion_rate ) / $this->unique_impressions ) );
		$control_standard_deviation   = sqrt( ( $control_conversion_rate * ( 1 - $control_conversion_rate ) / $control_unique_impressions ) );

		if ( ( $variation_standard_deviation == 0 && $control_standard_deviation == 0 ) || ( is_nan( $variation_standard_deviation ) || is_nan( $control_standard_deviation ) ) ) {
			return false;
		}
		//z-score = (control_conversion_rate - variation_conversion_rate) / sqrt((controlStandardDeviation^2)+(variationStandardDeviation^2))
		$z_score = ( $control_conversion_rate - $variation_conversion_rate ) / sqrt( pow( $control_standard_deviation, 2 ) + pow( $variation_standard_deviation, 2 ) );

		if ( is_nan( $z_score ) ) {
			return false;
		}

		//Confidence_level (which is synonymous with “chance to beat original”)  = normdist(z-score)
		$confidence_level = $this->normal_distribution( $z_score );
		if ( $confidence_level === false ) {
			return false;
		}

		return number_format( round( ( 1 - $confidence_level ) * 100, 2 ), 2 );
	}

	/**
	 * Function that will generate a cumulative normal distribution and return the confidence level as a number between 0 and 1
	 *
	 * @param $x
	 *
	 * @return float
	 */
	protected function normal_distribution( $x ) {

		$b1 = 0.319381530;
		$b2 = - 0.356563782;
		$b3 = 1.781477937;
		$b4 = - 1.821255978;
		$b5 = 1.330274429;
		$p  = 0.2316419;
		$c  = 0.39894228;

		if ( $x >= 0.0 ) {
			if ( ( 1.0 + $p * $x ) == 0 ) {
				return false;
			}
			$t = 1.0 / ( 1.0 + $p * $x );

			return ( 1.0 - $c * exp( - $x * $x / 2.0 ) * $t * ( $t * ( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ) );
		}

		if ( ( 1.0 - $p * $x ) == 0 ) {
			return false;
		}

		$t = 1.0 / ( 1.0 - $p * $x );

		return ( $c * exp( - $x * $x / 2.0 ) * $t * ( $t * ( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ) );
	}

	/**
	 * @inheritdoc
	 */
	protected function _table_name() {

		return thrive_ab()->table_name( 'test_items' );
	}

	/**
	 * @inheritdoc
	 */
	protected function _get_default_data() {

		$defaults = array(
			'is_control'         => 0,
			'is_winner'          => 0,
			'impressions'        => 0,
			'unique_impressions' => 0,
			'conversions'        => 0,
			'revenue'            => 0.0,
			'active'             => true,
			'stopped_date'       => null,
		);

		return $defaults;
	}

	/**
	 * @inheritdoc
	 */
	protected function is_valid() {

		$is_valid = true;
		if ( ! ( $this->page_id ) ) {
			$is_valid = false;
		} elseif ( ! ( $this->variation_id ) ) {
			$is_valid = false;
		} elseif ( ! ( $this->test_id ) ) {
			$is_valid = false;
		} elseif ( ! ( $this->title ) ) {
			$is_valid = false;
		}

		return $is_valid;
	}

	public function get_total_conversions( $test_id ) {

		$sql    = 'SELECT SUM(conversions) as total_conversions FROM ' . $this->_table_name() . ' WHERE test_id = %d LIMIT 1';
		$params = array( $test_id );

		return $this->wpdb->get_row( $this->wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Init by filters
	 *
	 * @param array $filters
	 *
	 * @throws Exception
	 */
	public function init_by_filters( $filters = array() ) {
		if ( empty( $filters ) ) {
			throw new Exception( __( 'Invalid filters', 'thrive-ab-page-testing' ) );
		}

		$sql    = 'SELECT * FROM ' . $this->_table_name() . ' WHERE 1 ';
		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$sql       .= 'AND `id` = %d ';
			$params [] = $filters['id'];
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$sql       .= 'AND `page_id` = %d ';
			$params [] = $filters['page_id'];
		}

		if ( ! empty( $filters['variation_id'] ) ) {
			$sql       .= 'AND `variation_id` = %d ';
			$params [] = $filters['variation_id'];
		}

		if ( ! empty( $filters['test_id'] ) ) {
			$sql       .= 'AND `test_id` = %d ';
			$params [] = $filters['test_id'];
		}

		if ( ! empty( $filters['active'] ) ) {
			$sql       .= 'AND `active` = %d ';
			$params [] = $filters['active'];
		}

		$sql_prepared = $this->wpdb->prepare( $sql, $params );
		$result       = $this->wpdb->get_row( $sql_prepared, ARRAY_A );

		if ( ! empty( $result ) ) {
			$this->_data = $result;
		}
	}

	public function get_control() {

		if ( $this->control instanceof Thrive_AB_Test_Item ) {
			return $this->control;
		}

		$sql    = 'SELECT * FROM ' . $this->_table_name() . ' WHERE is_control = 1 AND test_id = %d LIMIT 1';
		$params = array( $this->test_id );

		$sql_prepared = $this->wpdb->prepare( $sql, $params );
		$result       = $this->wpdb->get_row( $sql_prepared, ARRAY_A );

		if ( ! empty( $result ) ) {
			$this->control = new Thrive_AB_Test_Item( $result );
		} else {
			$this->control = new Thrive_AB_Test_Item();
		}

		return $this->control;
	}

	/**
	 * Stops a test item
	 *
	 * @throws Exception
	 */
	public function stop() {

		$this->active       = 0;
		$this->stopped_date = date( 'Y-m-d H:i:s' );

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function _prepare_data() {

		$data = $this->_data;

		$save_data = array(
			'id'                 => null,
			'page_id'            => null,
			'variation_id'       => null,
			'test_id'            => null,
			'thankyou_id'        => null,
			'goal_pages'         => null,
			'title'              => null,
			'is_control'         => null,
			'is_winner'          => null,
			'impressions'        => null,
			'unique_impressions' => null,
			'conversions'        => null,
			'revenue'            => null,
			'active'             => null,
			'stopped_date'       => null,
		);
		$save_data = array_intersect_key( $data, $save_data );

		if ( empty( $save_data['id'] ) ) {
			unset( $save_data['id'] );
		}

		$save_data['is_control'] = (int) $save_data['is_control'];

		return $save_data;
	}
}
