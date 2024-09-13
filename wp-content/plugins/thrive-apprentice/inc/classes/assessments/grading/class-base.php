<?php

namespace TVA\Assessments\Grading;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Base {
	const PASS_FAIL_METHOD  = 'pass_fail';
	const PERCENTAGE_METHOD = 'percentage';
	const SCORE_METHOD      = 'score';
	const CATEGORY_METHOD   = 'category';

	/** @var string $grading_method */
	protected $grading_method;

	/** @var bool $grading_manually_mark */
	protected $grading_manually_mark = 1;

	/** @var int|string $grading_passing_value */
	protected $grading_passing_value;

	/** @var int $assessment_id */
	protected $assessment_id;

	public static $grading_methods = [
		self::PASS_FAIL_METHOD  => 'Pass/Fail',
		self::PERCENTAGE_METHOD => 'Percentage',
		self::SCORE_METHOD      => 'Score',
		self::CATEGORY_METHOD   => 'Category',
	];

	private $meta_keys = [
		'grading_method',
		'grading_passing_value',
		'grading_manually_mark',
	];

	public function __construct( $data ) {
		if ( ! empty( $data['grading_method'] ) && in_array( $data['grading_method'], array_keys( static::$grading_methods ) ) ) {
			$this->grading_method = $data['grading_method'];
		}

		if ( ! empty( $data['grading_passing_value'] ) ) {
			$this->grading_passing_value = $data['grading_passing_value'];
		}

		if ( isset( $data['grading_manually_mark'] ) ) {
			$this->grading_manually_mark = (int) $data['grading_manually_mark'];
		}
	}

	/**
	 * Factory method
	 *
	 * @param array|string $data
	 *
	 * @return static
	 */
	public static function factory( $data ) {
		$method = '';
		if ( is_array( $data ) && ! empty( $data['grading_method'] ) ) {
			$method = $data['grading_method'];
		} elseif ( is_string( $data ) ) {
			$method = $data;
		}

		if ( ! in_array( $method, array_keys( static::$grading_methods ) ) ) {
			return new static( [] );
		}

		$grading_class = static::get_grading_class( $method );

		return new $grading_class( $data );
	}

	private static function get_grading_class( $grading_method ) {
		$grading_method = explode( '_', $grading_method );
		$grading_method = array_map( 'ucfirst', $grading_method );

		return __NAMESPACE__ . '\\' . join( '', $grading_method );
	}

	/**
	 * @param int $id assessment id
	 *
	 * @return $this
	 */
	public function set_assessment_id( $id ) {

		$id = (int) $id;

		if ( $id > 0 ) {
			$this->assessment_id = (int) $id;
		}

		return $this;
	}

	/**
	 * To be overwritten by the child classes
	 * - which might have additional data
	 *
	 * @return array
	 */
	public function get_additional_meta_keys() {
		return [];
	}

	private function get_meta_keys() {
		return array_merge( $this->meta_keys, $this->get_additional_meta_keys() );
	}

	/**
	 * @return bool
	 */
	public function save() {

		if ( empty( $this->assessment_id ) ) {
			return false;
		}

		foreach ( $this->get_meta_keys() as $key ) {
			if ( isset( $this->{$key} ) ) {
				update_post_meta( $this->assessment_id, 'tva_' . $key, $this->{$key} );
			}
		}

		return true;
	}

	public function get_grading_details() {
		$metas = [];
		if ( ! empty( $this->assessment_id ) ) {
			foreach ( $this->get_meta_keys() as $key ) {
				$metas[ $key ] = get_post_meta( $this->assessment_id, 'tva_' . $key, true );
			}
		}

		return $metas;
	}


	/**
	 * Get the grading details for the assessment
	 *
	 * @param int $assessment_id
	 *
	 * @return mixed
	 */
	public static function get_assessment_grading_details( $assessment_id ) {
		$grading_instance = static::factory( get_post_meta( $assessment_id, 'tva_grading_method', true ) );

		return $grading_instance->set_assessment_id( $assessment_id )->get_grading_details();
	}

	/**
	 * Checks if the $value is passing the grading
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function passed( $value ) {
		return false;
	}

	/**
	 * Returns the grade value
	 *
	 * @param string $grade
	 *
	 * @return string
	 */
	public function get_value( $grade ) {

		if ( in_array( $grade, [ PassFail::PASSING_GRADE, PassFail::FAILING_GRADE ] ) ) {
			$name = $grade === PassFail::PASSING_GRADE ? 'assessments_pass' : 'assessments_fail';
			$grade = tcb_tva_dynamic_actions()->get_course_structure_label( $name, 'singular' );
		}

		return $grade;
	}

	/**
	 * Returns the set passing grade
	 *
	 * @return int|mixed|string
	 */
	public function get_passing_grade() {
		return $this->grading_passing_value;
	}

	/**
	 * @param $original_id
	 * @param $clone_id
	 *
	 * @return void
	 */
	public static function handle_assessment_clone( $original_id, $clone_id ) {
		$grading_instance = static::factory( get_post_meta( $original_id, 'tva_grading_method', true ) );
		$grading_instance->set_assessment_id( $original_id );
		$clone_instance = static::factory( get_post_meta( $clone_id, 'tva_grading_method', true ) );
		$clone_instance->set_assessment_id( $clone_id );

		$grading_instance->after_clone( $clone_instance );
	}

	/**
	 * Clone additional data
	 *
	 * @param $clone_instance
	 *
	 * @return void
	 */
	public function after_clone( $clone_instance ) {

	}
}
