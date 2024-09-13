<?php

namespace TVA\Course\Structure\Builder;

use TVA_Course_V2;
use TVA_Manager;
use TVA_Post;
use TVA_Const;

class TVA_Course_Level_Structure_Builder extends TVA_Course_Structure_Builder {

	private $course;

	/**
	 * TVA_Course_Level_Structure_Builder constructor.
	 *
	 * @param TVA_Course_V2 $course
	 */
	public function __construct( $course ) {
		parent::__construct( $course->term_id );
		$this->course = $course;
	}

	/**
	 * Fetch all posts from DB at course level
	 *
	 * @return TVA_Post[]
	 */
	public function get_posts() {

		$items     = [];
		$tva_posts = [];

		if ( true === $this->course instanceof TVA_Course_V2 ) {

			$args = array(
				'posts_per_page' => - 1,
				'post_status'    => TVA_Post::$accepted_statuses,
				'post_type'      => array(
					TVA_Const::LESSON_POST_TYPE,
					TVA_Const::CHAPTER_POST_TYPE,
					TVA_Const::MODULE_POST_TYPE,
					TVA_Const::ASSESSMENT_POST_TYPE,
				),
				'meta_key'       => array( 'tva_lesson_order', 'tva_module_order', 'tva_chapter_order' ),
				'post_parent'    => 0,
				'tax_query'      => array(
					array(
						'taxonomy' => TVA_Const::COURSE_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => array( $this->course->term_id ),
						'operator' => 'IN',
					),
				),
				'orderby'        => 'meta_value_num', //because tva_order_item is int
				'order'          => 'ASC',
			);

			$items = TVA_Manager::get_posts_from_cache( $args );
		}

		foreach ( $items as $post ) {
			$tva_posts[] = TVA_Post::factory( $post );
		}

		return $tva_posts;
	}
}
