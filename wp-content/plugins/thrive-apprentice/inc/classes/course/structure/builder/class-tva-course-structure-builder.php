<?php

namespace TVA\Course\Structure\Builder;

use TVA\Course\Structure\TVA_Course_Structure;

abstract class TVA_Course_Structure_Builder implements TVA_Course_Structure_Builder_Interface {

	/**
	 * @var TVA_Course_Structure
	 */
	protected $structure;

	/**
	 * @var int
	 */
	protected $parent_id;

	public function __construct( $parent_id ) {
		$this->structure = new TVA_Course_Structure();
		$this->parent_id = $parent_id;
	}

	public function reset() {
		$this->structure = new TVA_Course_Structure();
	}

	public function add_post( $post ) {
		$this->structure->add( $post );
	}

	public function get_structure() {
		$structure = $this->structure;
		$this->reset();

		return $structure;
	}
}
