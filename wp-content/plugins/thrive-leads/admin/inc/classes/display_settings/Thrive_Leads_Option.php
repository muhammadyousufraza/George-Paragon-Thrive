<?php

/**
 * Class Option Model
 */
class Thrive_Leads_Option {
	/* These data members are public just for the serialization(json_encode/json_decode) */
	public $id;
	public $label;
	public $isChecked = false;

	/**
	 * Used to filter options by filters
	 * Used to render a specific template
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * @param mixed $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param boolean $isChecked
	 */
	public function setIsChecked( $isChecked ) {
		$this->isChecked = $isChecked;
	}

	/**
	 * @return boolean
	 */
	public function getIsChecked() {
		return $this->isChecked;
	}

	/**
	 * @param mixed $label
	 */
	public function setLabel( $label ) {
		$charset_utf8 = defined( 'DB_CHARSET' ) && strpos( DB_CHARSET, 'utf8' ) !== false;
		/* for non utf8 we need to encode the label otherwise the page will break*/
		if ( ! $charset_utf8 ) {
			$label = utf8_encode( $label );
		}
		$this->label = $label;
	}

	/**
	 * @return mixed
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $type
	 */
	public function setType( $type ) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}
