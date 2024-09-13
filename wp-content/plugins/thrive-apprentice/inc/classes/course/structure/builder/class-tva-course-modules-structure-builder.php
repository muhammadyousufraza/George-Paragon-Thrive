<?php

namespace TVA\Course\Structure\Builder;

use TVA_Const;
use TVA_Course_V2;
use TVA_Manager;
use TVA_Post;

class TVA_Course_Modules_Structure_Builder extends TVA_Course_Structure_Builder {

	/**
	 * Fetch all modules from DB at course level
	 *
	 * @return TVA_Post[]
	 */
	public function get_posts() {

		$tva_posts = [];

		$args = array(
			'posts_per_page' => - 1,
			'post_status'    => TVA_Post::$accepted_statuses,
			'post_type'      => array(
				TVA_Const::LESSON_POST_TYPE,
				TVA_Const::CHAPTER_POST_TYPE,
				TVA_Const::ASSESSMENT_POST_TYPE,
			),
			'meta_key'       => array( 'tva_lesson_order', 'tva_chapter_order' ),
			'post_parent'    => $this->parent_id,
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);

		$posts = TVA_Manager::get_posts_from_cache( $args );

		foreach ( $posts as $post ) {
			$tva_posts[] = TVA_Post::factory( $post );
		}

		return $tva_posts;
	}
}
