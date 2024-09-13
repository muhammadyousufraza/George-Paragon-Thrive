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

use TVA\Architect\Assessment\Main;
use TVA_Assessment;

class Base {

	protected $assessment_id;

	public static $assessment_types = [
		Main::TYPE_UPLOAD,
		Main::TYPE_QUIZ,
		Main::TYPE_YOUTUBE_LINK,
		Main::TYPE_EXTERNAL_LINK,
	];

	protected static $meta_keys = [];

	public function __construct( $data ) {
	}

	/**
	 * @param array|string $data
	 *
	 * @return Base
	 */
	public static function factory( $data ) {
		$type = '';
		if ( is_array( $data ) && ! empty( $data['assessment_type'] ) ) {
			$type = $data['assessment_type'];
		} elseif ( is_string( $data ) ) {
			$type = $data;
		}

		if ( ! in_array( $type, static::$assessment_types ) ) {
			return new static( [] );
		}

		$class = static::get_type_class( $type );

		return new $class( $data );
	}

	private static function get_type_class( $type ) {
		$type  = explode( '_', $type );
		$type  = array_map( 'ucfirst', $type );
		$class = __NAMESPACE__ . '\\' . join( '', $type );

		return class_exists( $class ) ? $class : __CLASS__;
	}

	public function save() {
		if ( empty( $this->assessment_id ) ) {
			return false;
		}

		/**
		 * Clean the previously meta that was set before adding new meta
		 */
		$this->clean();
		foreach ( static::$meta_keys as $key ) {
			if ( isset( $this->{$key} ) ) {
				update_post_meta( $this->assessment_id, static::get_meta_key_name( $key ), $this->{$key} );
			}
		}

		return true;
	}

	public function get_details() {
		$metas = [];

		if ( ! empty( $this->assessment_id ) ) {
			foreach ( static::$meta_keys as $key ) {
				$metas[ $key ] = get_post_meta( $this->assessment_id, static::get_meta_key_name( $key ), true );
			}
		}

		return $metas;
	}


	/**
	 * @param TVA_Assessment $assessment
	 *
	 * @return array
	 */
	public static function get_assessment_details( $assessment ) {
		$type_instance = static::factory( $assessment->get_type() );

		return $type_instance->set_assessment_id( $assessment->ID )->get_details();
	}

	/**
	 * @param int $assessment_id
	 *
	 * @return $this
	 */
	public function set_assessment_id( $assessment_id ) {
		$this->assessment_id = $assessment_id;

		return $this;
	}

	/**
	 * Computes the meta key name
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private static function get_meta_key_name( $key ) {
		return 'tva_' . $key;
	}

	/**
	 * Cleans the meta that was previously saved on assessment before inserting new one
	 *
	 * @return void
	 */
	private function clean() {
		$meta_keys = [];

		foreach ( static::$assessment_types as $type ) {
			/**
			 * @var Base $class
			 */
			$class = static::get_type_class( $type );

			$meta_keys = array_merge( $meta_keys, $class::$meta_keys );
		}

		foreach ( $meta_keys as $meta_key ) {
			delete_post_meta( $this->assessment_id, static::get_meta_key_name( $meta_key ) );
		}
	}
}
