<?php

/**
 * Class TVA_Video
 * - handles embed code for data provided
 *
 * @property string type
 * @property string source
 * @property array  options
 */
class TVA_Media implements JsonSerializable {

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * TVA_Video constructor.
	 *
	 * @param $data
	 */
	public function __construct( $data ) {
		$this->_data = $data;
	}

	/**
	 * Magic get
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {

		$value = null;

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		}

		return $value;
	}

	/**
	 * Magic set
	 * - pushes a new item in $_data at $key index with specified $value
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function __set( $key, $value ) {
		$this->_data[ $key ] = $value;
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->_data;
	}

	/**
	 * Gets a ready made string of embed code ready to be shown in HTML
	 *
	 * @return string
	 */
	public function get_embed_code() {

		$type        = empty( $this->_data['type'] ) ? '' : $this->_data['type'];
		$method_name = "_{$type}_embed_code";

		if ( method_exists( $this, $method_name ) ) {
			return $this->$method_name();
		}

		return html_entity_decode( $this->_data['source'] );
	}
}
