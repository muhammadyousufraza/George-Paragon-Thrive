<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/24/2019
 * Time: 15:02
 */

/**
 * Class TVA_Term_Model
 *
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property array  $membership_ids
 * @property array  $bundle_ids
 * @property array  $rules
 */
class TVA_Term_Model extends TVA_Model {

	const TAXONOMY = TVA_Const::COURSE_TAXONOMY;

	/**
	 * @var
	 */
	public $term_id;

	/**
	 * PHP 8.2 Fixes
	 */
	public $name;
	public $description;
	public $slug;
	public $term_group;
	public $term_taxonomy_id;
	public $taxonomy;
	public $parent;
	public $count;
	public $filter;
	public $membership_ids;
	public $bundle_ids;
	public $rules;

	/**
	 * TVA_Term_Model constructor.
	 *
	 * @param WP_Term|array $data
	 */
	public function __construct( $data ) {

		$this->data = $data;

		foreach ( $this->get_public_fields() as $field ) {
			if ( $data instanceof WP_Term || $data instanceof self ) {
				$this->$field = isset( $data->$field ) ? $data->$field : '';
			} elseif ( is_array( $data ) ) {
				$this->$field = isset( $data[ $field ] ) ? $data[ $field ] : '';
			}
		}

		$this->_set_protection_fields();

		return $this;
	}

	/**
	 * @return array|mixed
	 */
	public function get_public_fields() {
		return array(
			'term_id',
			'name',
			'slug',
			'term_group',
			'term_taxonomy_id',
			'taxonomy',
			'description',
			'parent',
			'count',
			'filter',
		);
	}

	/**
	 * Return all extra fields for this term
	 *
	 * @return array
	 */
	public function get_extra_fields() {
		return array(
			'cover_image',
			'order',
			'level',
			'logged_in',
			'message',
			'roles',
			'topic',
			'author',
			'status',
			'description',
			'label',
			'label_name',
			'label_color',
			'excluded',
			'membership_ids',
			'bundle_ids',
			'video_status',
			'term_media',
			'comment_status',
		);
	}

	private function _set_extra_fields() {
		foreach ( $this->get_extra_fields() as $field ) {
			$this->$field = isset( $this->data[ $field ] ) ? $this->data[ $field ] : '';
		}
	}

	private function _save_extra_fields() {
		foreach ( $this->get_extra_fields() as $field ) {
			update_term_meta( $this->term_id, 'tva_' . $field, $this->data[ $field ] );
		}
	}

	private function _set_protection_fields() {

		$this->membership_ids = isset( $this->membership_ids )
			? $this->membership_ids
			: get_term_meta( $this->term_id, 'tva_membership_ids', true );

		$this->bundle_ids = isset( $this->bundle_ids )
			? $this->bundle_ids
			: get_term_meta( $this->term_id, 'tva_bundle_ids', true );

		$this->rules = isset( $this->rules )
			? $this->rules
			: array_filter( (array) get_term_meta( $this->term_id, 'tva_rules', true ) );

		foreach ( $this->rules as $i => $rule ) {
			if ( ! isset( $rule['integration'] ) ) {
				unset( $this->rules[ $i ] );
			}
		}
		$this->rules = array_values( $this->rules );
	}

	/**
	 * Check if current instance is protected by sendowl
	 *
	 * @return bool
	 */
	public function is_protected_by_sendowl() {

		$is_protected = count( $this->get_rules_by_integration( 'sendowl_product' ) );
		$is_protected = $is_protected || count( $this->get_rules_by_integration( 'sendowl_bundle' ) );

		$is_protected = $is_protected ||
						is_array( $this->membership_ids ) &&
						array_key_exists( 'sendowl', $this->membership_ids ) &&
						! empty( $this->membership_ids['sendowl'] )
						||
						is_array( $this->bundle_ids ) &&
						array_key_exists( 'sendowl', $this->bundle_ids ) &&
						! empty( $this->bundle_ids['sendowl'] );

		return $is_protected;
	}

	/**
	 * Checks if the courses has any WordPress rules set
	 *
	 * @return bool
	 */
	public function is_protected_by_wp() {

		return count( $this->get_rules_by_integration( 'wordpress' ) ) > 0;
	}

	public function get_rules_by_integration( $integration ) {

		if ( empty( $this->rules ) ) {
			return array();
		}

		return array_filter(
			$this->rules,
			function ( $rule ) use ( $integration ) {
				return ! empty( $rule['integration'] ) && $rule['integration'] === $integration;
			}
		);
	}

	/**
	 * @return array|mixed
	 */
	public function get_sendowl_products_ids() {
		return isset( $this->membership_ids['sendowl'] ) ? $this->membership_ids['sendowl'] : array();
	}

	/**
	 * @return array|mixed
	 */
	public function get_sendowl_bundles_ids() {
		return isset( $this->bundle_ids['sendowl'] ) ? $this->bundle_ids['sendowl'] : array();
	}

	/**
	 * From sendowl rules fetches sendowl IDs and returns them
	 *
	 * @return array
	 */
	public function get_all_sendowl_protection_ids() {

		$ids = array();

		foreach ( $this->rules as $rule ) {
			if ( false === in_array( $rule['integration'], [ 'sendowl_bundle', 'sendowl_product' ], true ) ) {
				continue;
			}
			foreach ( $rule['items'] as $item ) {
				$ids[] = $item['id'];
			}
		}

		return $ids;
	}

	/**
	 * @return mixed
	 */
	public function get_id() {
		return $this->term_id;
	}

	public function save() {
		$this->term_id
			? $this->_update()
			: $this->_create();

		if ( ! empty( $this->errors ) ) {
			return $this->errors[0];
		}

		return $this->prepare_response();
	}

	private function _create() {
		$result = wp_insert_term(
			$this->name,
			TVA_Const::COURSE_TAXONOMY,
			array(
				'description' => $this->description,
				'slug'        => $this->slug,
			)
		);

		if ( $result instanceof WP_Error ) {
			$this->errors[] = $result;

			return;
		}

		$this->term_id = $result['term_id'];
		$this->_set_extra_fields();
		$this->_save_extra_fields();
	}

	private function _update() {
		$result = wp_update_term(
			$this->term_id,
			TVA_Const::COURSE_TAXONOMY,
			array(
				'description' => $this->description,
				'slug'        => $this->slug,
			)
		);

		$this->_set_extra_fields();
	}

	public function prepare_response() {
		$response = $this->to_array();

		foreach ( $this->get_extra_fields() as $field ) {
			$response[ $field ] = $this->$field;
		}

		return $response;
	}
}
