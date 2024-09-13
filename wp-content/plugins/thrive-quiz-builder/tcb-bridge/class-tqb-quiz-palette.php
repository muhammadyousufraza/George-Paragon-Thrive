<?php

/**
 * Handles quiz colors palettes.
 *
 * Class TQB_Quiz_Palettes
 */
class TQB_Quiz_Palettes {

	/**
	 * @var int quiz style id
	 */
	private $_quiz_style;

	public function __construct( $id = 0 ) {

		$this->_quiz_style = $id;
	}

	/**
	 * @return array
	 */
	public function get_palettes() {

		$path = dirname( __FILE__ ) . "/palettes/style_{$this->_quiz_style}.php";

		if ( file_exists( $path ) ) {
			include tqb()->plugin_path( "tcb-bridge/palettes/style_{$this->_quiz_style}.php" );

			return $palettes;
		}

		return array();
	}

	/**
	 * @return string|array
	 */
	public function get_pg_palettes() {

		if ( ! $this->has_pg_palettes() ) {
			return '';
		}

		include tqb()->plugin_path( "tcb-bridge/palettes/pg-bar.php" );

		return $palettes;
	}

	/**
	 * @return bool
	 */
	public function has_pg_palettes() {

		return 5 !== (int) $this->_quiz_style;
	}

	/**
	 * @param array $data
	 *
	 * @return string
	 */
	public function get_palettes_as_string( $data = array() ) {
		$pattern = '__CONFIG_colors_palette__';

		if ( empty( $data ) ) {
			$data = $this->get_palettes();
		}

		return $pattern . json_encode( $data ) . $pattern;
	}
}
