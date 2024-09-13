<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Trigger;

class Course_Content extends Base {
	/**
	 * Array of all selected course content
	 *
	 * @var string
	 */
	protected $objects;

	/**
	 * Check that all selected objects are completed in order to unlock content
	 *
	 * @param int $product_id TA product
	 * @param int $post_id    campaign id
	 *
	 * @return boolean
	 */
	public function is_valid( $product_id, $post_id ) {
		$content         = (array) $this->objects;
		$customer        = new \TVA_Customer( get_current_user_id() );
		$learned_lessons = $customer->get_learned_lessons();
		$valid           = array_fill( 0, count( $content ), false );
		$index           = 0;

		foreach ( $content as $object ) {
			$type      = $object['type'];
			$object_id = $object['id'];

			if ( in_array( $type, array( 'lesson', 'module' ) ) ) {
				$post = get_post( $object_id );
				if ( empty( $post ) ) {
					$valid[ $index ] = true;
				} else {
					$tva_post = \TVA_Post::factory( get_post( $object_id ) );

					$course = $tva_post->get_course_v2();
					if ( empty( $course ) || ! ( $course instanceof \TVA_Course_V2 ) ) {
						$valid[ $index ] = true;
					} else {
						$valid[ $index ] = ( ! $tva_post->is_published() ) || ( ! $course->is_published() ) || $tva_post->is_completed();
					}
				}
			} elseif ( $type === 'course' ) {
				$tva_course = new \TVA_Course_V2( (int) $object_id );
				$completed  = isset( $learned_lessons[ $object_id ] ) ? count( $learned_lessons[ $object_id ] ) : 0;
				$total      = $tva_course->get_published_lessons_count();

				$valid[ $index ] = ( ! $tva_course->is_published() ) || ( $completed === $total );
			}

			$index ++;
		}

		return array_product( $valid ) === 1;

	}
}
