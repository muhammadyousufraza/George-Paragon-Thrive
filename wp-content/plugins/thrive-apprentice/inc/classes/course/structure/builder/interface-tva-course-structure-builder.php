<?php

namespace TVA\Course\Structure\Builder;

use TVA_Post;

interface TVA_Course_Structure_Builder_Interface {

	public function reset();

	public function get_structure();

	/**
	 * @param TVA_Post $post
	 *
	 * @return mixed
	 */
	public function add_post( $post );

	/**
	 * This method has to be implemented by the child builder classes
	 * - modules builder has to fetch from DB specific posts
	 * - chapters builder has to fetch from DB specific posts
	 * - ...
	 *
	 * @return TVA_Post[]
	 */
	public function get_posts();
}
