<?php
/**
 * Created by PhpStorm.
 * User: Andrei
 * Date: 4/29/2019
 * Time: 2:02 PM
 */

/**
 * Class TVA_Product_Model
 *
 * @property string $id
 * @property string $name
 * @property string $type
 * @property string $price
 * @property string $instant_buy_url
 * @property array  $protected_terms
 */
class TVA_Product_Model extends TVA_Model {

	protected $id_key = 'id';

	/**
	 * @var string
	 */
	protected $_key = 'product';

	/**
	 * PHP 8.2 Fixes
	 *
	 * This needs to be public because it is accessed from class-tva-produtcs-collection
	 */
	public $id;
	public $name;
	public $price;
	public $instant_buy_url;
	public $protected_terms;
	public $type;



	public function init() {
		$this->normalize_data( $this->data );
		$this->set_public_fields();
		$this->set_extra_fields();
	}

	/**
	 * @return array
	 */
	public function get_public_fields() {
		return array(
			'id',
			'name',
			'price',
			'instant_buy_url',
		);
	}

	public function get_extra_fields() {
		return array(
			'protected_terms',
			'type',
		);
	}

	public function set_extra_fields() {
		$this->protected_terms = isset( $this->data['protected_terms'] ) ? $this->data['protected_terms'] : array();
		$this->type            = isset( $this->data['type'] ) ? $this->data['type'] : $this->_key; // used to identify what type is current item
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function normalize_data( $data ) {
		if ( $data instanceof self ) {
			$this->data = $data->to_array();

			return $this->data;
		}

		return isset( $data[ $this->_key ] ) ? $data[ $this->_key ] : $data;
	}

	/**
	 * @return $this
	 */
	public function set_protected_terms() {
		$protected_items = TVA_Terms_Collection::make(
			tva_term_query()->get_protected_items()
		)->get_sendowl_protected_items()->get_items();

		foreach ( $protected_items as $item ) {
			/** @var TVA_Term_Model $item */
			$pids = $item->get_sendowl_products_ids();

			if ( in_array( $this->id, $pids ) ) {
				$this->add_protected_term( $item->get_id() );
			}
		}

		return $this;
	}

	/**
	 * Prepare the instance to be saved
	 *
	 * @return array
	 */
	public function to_db() {
		return $this->to_array();
	}

	/**
	 * Add a new term to protected items list
	 *
	 * @param $term_id
	 */
	public function add_protected_term( $term_id ) {
		if ( ! in_array( $term_id, $this->protected_terms ) ) {
			$this->protected_terms[] = $term_id;
		}

		$this->protected_terms = array_values( $this->protected_terms );
	}

	/**
	 * Remove a term from protected items list
	 *
	 * @param $term_id
	 */
	public function remove_protected_term( $term_id ) {
		foreach ( $this->protected_terms as $key => $term ) {
			if ( $term_id === $term ) {
				unset( $this->protected_terms[ $key ] );
			}
		}

		$this->protected_terms = array_values( $this->protected_terms );
	}

	public function get_key() {
		return $this->_key;
	}
}
