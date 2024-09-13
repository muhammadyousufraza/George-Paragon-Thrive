<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Trigger;

class Video_Progress extends Base {

	/**
	 * ID of the video lesson to be completely watched
	 *
	 * @var int
	 */
	protected $object_id = 0;

	/**
	 * Check that the selected video lesson has been watched completely
	 *
	 * @param int $product_id TA product
	 * @param int $post_id    campaign id
	 *
	 * @return boolean
	 */
	public function is_valid( $product_id, $post_id ) {

		return apply_filters( 'tva_can_be_marked_as_completed', true, [ 'video_progress' => true ], $this->object_id );
	}
}
