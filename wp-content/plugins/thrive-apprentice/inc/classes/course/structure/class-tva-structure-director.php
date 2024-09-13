<?php

namespace TVA\Course\Structure;

use TVA\Course\Structure\Builder\TVA_Course_Level_Structure_Builder;
use TVA\Course\Structure\Builder\TVA_Course_Structure_Builder;

class TVA_Structure_Director {

	/**
	 * @var TVA_Course_Structure_Builder
	 */
	private $builder;

	/**
	 * @param $builder TVA_Course_Structure_Builder
	 */
	public function __construct( $builder ) {
		$this->builder = $builder;
	}

	public function build_structure() {

		$this->builder->reset();

		foreach ( $this->builder->get_posts() as $post ) {
			$this->builder->add_post( $post );
		}
	}
}
