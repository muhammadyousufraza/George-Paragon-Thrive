<?php

/**
 * Class TVA_Level
 * Defines a level property for a course
 *
 * @property string name
 */
class TVA_Level extends TVA_Options_List {

	const COURSE_TERM_NAME = 'tva_level';

	/**
	 * TVA_Level constructor.
	 *
	 * @param int|array $data
	 */
	public function __construct( $data ) {

		if ( is_int( $data ) ) {
			$this->_init_from_db( $data );
		} else {
			parent::__construct( $data );
		}
	}

	/**
	 * Get the TVA_Level from id
	 *
	 * @param int $id
	 */
	protected function _init_from_db( $id ) {
		$levels = static::get_items( true );

		foreach ( $levels as $key => $level ) {
			if ( $level['id'] === $id ) {
				$this->_data = $level;
			}
		}
	}

	/**
	 * @return string
	 */
	static public function get_option_name() {
		return 'tva_difficulty_levels';
	}

	/**
	 * @param bool $as_array
	 *
	 * @return array[]|TVA_Level[]
	 */
	public static function get_items( $as_array = false ) {

		$items = parent::get_items( $as_array );

		return ! empty( $items ) ? $items : self::get_defaults( $as_array );
	}

	/**
	 * Get default levels
	 *
	 * @param bool $as_array
	 *
	 * @return array[]|TVA_Level[]
	 */
	public static function get_defaults( $as_array = true ) {
		$items = array(
			array(
				'ID'   => 0,
				'id'   => 0,
				'name' => 'None',
			),
			array(
				'ID'   => 1,
				'id'   => 1,
				'name' => 'Easy',
			),
			array(
				'ID'   => 2,
				'id'   => 2,
				'name' => 'Intermediate',
			),
			array(
				'ID'   => 3,
				'id'   => 3,
				'name' => 'Advanced',
			),
		);

		if ( $as_array ) {
			return $items;
		}

		return array_map( static function ( $item ) {
			return new TVA_Level( $item );
		}, $items );
	}

	/**
	 * Set name for TVA_Level
	 *
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Save the TVA_Level
	 *
	 * @return bool
	 */
	public function save() {
		$levels = static::get_items( true );

		if ( ! is_null( $this->id ) ) {
			$levels[ $this->id ] = $this->_data;
		} else {
			$id                = end( $levels )['id'] + 1;
			$this->_data['id'] = $id;
			$this->_data['ID'] = $id;
			array_push( $levels, $this->_data );
		}

		return update_option( $this->get_option_name(), $levels );
	}

	/**
	 * Delete the TVA_Level and reindex the rest
	 *
	 * @return array[]|bool|TVA_Level[]
	 */
	public function delete() {

		$levels = static::get_items( true );

		foreach ( $levels as $key => $level ) {
			if ( $level['id'] === $this->id ) {
				unset( $levels[ $key ] );
			}
		}

		$courses = TVA_Course_V2::get_items( [ 'levels' => [ $this->id ] ] );

		foreach ( $courses as $course ) {
			update_term_meta( $course->term_id, TVA_Level::COURSE_TERM_NAME, 0 );
		}

		$result = update_option( $this->get_option_name(), $levels );

		if ( $result ) {
			return $levels;
		}

		return false;
	}

}
