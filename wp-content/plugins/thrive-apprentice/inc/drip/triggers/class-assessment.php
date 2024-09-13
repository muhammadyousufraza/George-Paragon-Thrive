<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Trigger;

use TVA\Assessments\TVA_User_Assessment;

class Assessment extends Base {

	/**
	 * Unlock condition names
	 */
	const UNLOCK_WHEN_PASS   = 'pass';
	const UNLOCK_WHEN_SUBMIT = 'submit';

	/**
	 * ID of the assessment to have a certain status
	 *
	 * @var int
	 */
	protected $object_id = 0;

	/**
	 * Status of the assessment needed for the content to unlock
	 *
	 * @var string
	 */
	protected $when = '';

	/**
	 * Check that the selected assessment has the required status to unlock content
	 *
	 * @param int $product_id TA product
	 * @param int $post_id    campaign id
	 *
	 * @return boolean
	 */
	public function is_valid( $product_id, $post_id ) {
		$args = array(
			'author'      => get_current_user_id(),
			'post_type'   => TVA_User_Assessment::POST_TYPE,
			'post_parent' => (int) $this->object_id,
			'post_status' => 'any',
		);

		$user_submissions = get_posts( $args );
		if ( count( $user_submissions ) > 0 ) {
			if ( $this->when === static::UNLOCK_WHEN_SUBMIT ) {
				return true;
			}

			$unlock_when = $this->when === static::UNLOCK_WHEN_PASS ? \TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED : \TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED;
			foreach ( $user_submissions as $user_submission ) {
				$submission = new TVA_User_Assessment( $user_submission );
				if ( $unlock_when === $submission->status ) {
					return true;
				}
			}
		}

		return false;
	}
}
