<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Assessments\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Tqb extends Base {

	protected $quiz_id;

	protected static $meta_keys = [
		'quiz_id',
	];

	public function __construct( $data ) {
		parent::__construct( $data );

		if ( ! empty( $data['quiz_id'] ) ) {
			$this->quiz_id = $data['quiz_id'];
		}
	}
}

