<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use TVA\Architect\Assessment\Main;
use TVA\Assessments\Grading\Base as Grading_Base;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Assessments\Types\Base as Type_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVA_Assessment
 *
 * @property string cover_image
 * @property string comment_status
 */
class TVA_Assessment extends TVA_Post {

	/**
	 * Wizard ID present in Skin Wizard screen
	 */
	const WIZARD_ID = 'assessment';

	/**
	 * Used in save method from tva-post.php
	 *
	 * @var array
	 */
	protected $_defaults = [
		'post_type' => TVA_Const::ASSESSMENT_POST_TYPE,
	];

	public static $types = [
		Main::TYPE_UPLOAD        => 'Upload',
		Main::TYPE_QUIZ          => 'Quiz',
		Main::TYPE_YOUTUBE_LINK  => 'Youtube',
		Main::TYPE_EXTERNAL_LINK => 'External Link',
	];

	protected $grading;

	protected $type_class;

	/**
	 * Meta names, without prefix, supported by a assessment model
	 *
	 * @var string[]
	 */
	private $_meta_names = [
		'assessment_type',
		'cover_image',
		'freemium',
	];

	/**
	 * Which type of lesson: text/video/etc
	 *
	 * @return string
	 */
	public function get_type() {

		if ( empty( $this->_type ) ) {
			$this->_type = get_post_meta( $this->_post->ID, 'tva_assessment_type', true );
		}

		return $this->_type;
	}

	/**
	 * Returns the assessment quiz
	 * Is applicable only for assessment quiz type
	 *
	 * @return int
	 */
	public function get_quiz() {
		return (int) get_post_meta( $this->_post->ID, 'tva_quiz_id', true );
	}

	/**
	 * Extends the parent save with
	 * - saving post meta
	 *
	 * @return true
	 * @throws Exception
	 */
	public function save() {

		parent::save();

		if ( $this->grading instanceof Grading_Base ) {
			$this->grading->set_assessment_id( $this->ID );
			$this->grading->save();
		}

		if ( $this->type_class instanceof Type_Base ) {
			$this->type_class->set_assessment_id( $this->ID );
			$this->type_class->save();
		}

		foreach ( $this->_meta_names as $meta_name ) {
			if ( isset( $this->_data[ $meta_name ] ) ) {
				update_post_meta( $this->ID, 'tva_' . $meta_name, $this->_data[ $meta_name ] );
			}
		}

		return true;
	}

	/**
	 * Once deleted, delete all the user assessments
	 *
	 * @return void
	 */
	protected function _delete_children() {
		$children = get_posts(
			[
				'post_type'      => TVA_User_Assessment::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'post_parent'    => $this->_post->ID,
				'fields'         => 'ids',
			] );

		foreach ( $children as $child ) {
			wp_delete_post( $child, true );
		}
	}

	public function set_order( $order ) {

		$set = false;

		$this->_data['order'] = $order;

		if ( true === $this->_post instanceof WP_Post ) {
			update_post_meta( $this->ID, 'tva_lesson_order', $order );
			$set = true;
		}

		return $set;
	}

	public function get_order() {

		$order = '';

		if ( true === $this->_post instanceof WP_Post ) {
			$order = $this->_post->{'tva_lesson_order'};
		}

		return $order;
	}

	/**
	 * @param Grading_Base $grading
	 *
	 * @return void
	 */
	public function set_grading( $grading ) {
		if ( false === ( $grading instanceof Grading_Base ) ) {
			return;
		}

		$grading->set_assessment_id( $this->ID );
		$this->grading = $grading;
	}

	public function get_grading() {
		return $this->grading;
	}

	/**
	 * @param Grading_Base $grading
	 *
	 * @return void
	 */
	public function set_type_class( $class ) {
		if ( false === ( $class instanceof Type_Base ) ) {
			return;
		}

		$class->set_assessment_id( $this->ID );
		$this->type_class = $class;
	}

	public function get_inbox_details() {
		return array_merge(
			[
				'course_id'       => $this->get_course_v2()->get_id(),
				'assessment_type' => $this->get_type(),
				'post_title'      => $this->_post->post_title,
				'ID'              => $this->_post->ID,
				'publish_date'    => $this->_post->post_date,
			],
			Grading_Base::get_assessment_grading_details( $this->ID )
		);
	}

	public function __get( $key ) {

		if ( in_array( $key, $this->_meta_names, true ) ) {
			return $this->_post->{'tva_' . $key};
		}

		return parent::__get( $key );
	}

	/**
	 * Serialize specific lesson data
	 * - rather than parent wp post
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {

		$course = $this->get_course_v2();

		return array_merge(
			parent::jsonSerialize(),
			[
				'course_id'       => $course instanceof TVA_Course_V2 ? $course->get_id() : null,
				'assessment_type' => $this->get_type(),
				'has_tcb_content' => ! ! $this->get_tcb_content(),
				'cover_image'     => $this->cover_image,
				'freemium'        => $this->get_freemium(),
				'has_submission'  => TVA_User_Assessment::has_submissions( [ 'post_parent' => $this->ID ] ),
			],
			Grading_Base::get_assessment_grading_details( $this->ID ),
			Type_Base::get_assessment_details( $this )
		);
	}

	/**
	 * Returns true if the user has a submission
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function is_completed( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		$counter = (int) get_user_meta( $user_id, TVA_User_Assessment::SUBMISSION_COUNTER_META . $this->ID, true ) ?: 0;

		return $counter > 0;
	}

	/**
	 * Duplicates TVA_Assessment
	 *
	 * @param int    $post_parent
	 * @param string $name
	 *
	 * @return TVA_Assessment
	 * @throws Exception
	 */
	public function duplicate( $post_parent = 0, $name = '' ) {

		$new_assessment = new TVA_Assessment(
			[
				'post_parent'    => (int) $post_parent,
				'post_excerpt'   => $this->post_excerpt,
				'order'          => $this->get_order() + 1,
				'post_title'     => $name ? $name : $this->post_title,
				'cover_image'    => $this->cover_image,
				'comment_status' => $this->comment_status,
				'freemium'       => $this->get_freemium(),
			]
		);

		$new_assessment->save();
		$new_assessment->assign_to_course( $this->get_course_v2()->get_id() );

		foreach ( get_post_meta( $this->ID ) as $meta_key => $post_meta_item ) {
			if ( isset( $post_meta_item[0] ) ) {
				$data = thrive_safe_unserialize( $post_meta_item[0] );
				update_post_meta( $new_assessment->ID, $meta_key, $data );
			}
		}

		Grading_Base::handle_assessment_clone( $this->ID, $new_assessment->ID );

		$post_name = wp_unique_post_slug( $this->post_name, $new_assessment->ID, 'publish', TVA_Const::ASSESSMENT_POST_TYPE, $post_parent );
		wp_update_post(
			[
				'ID'        => $new_assessment->ID,
				'post_name' => $post_name,
			]
		);

		$new_assessment->_post->post_name = $post_name;

		return $new_assessment;
	}

	/**
	 * Returns true if there is an assessment in content
	 *
	 * @return bool
	 */
	public function has_assessment_in_content() {
		return ! empty( (int) get_post_meta( $this->ID, 'tva_assessment_in_content', true ) );
	}

	/**
	 * Toggle assessment element in content
	 *
	 * @param integer $in_content
	 *
	 * @return void
	 */
	public function toggle_assessment_in_content( $in_content ) {
		update_post_meta( $this->ID, 'tva_assessment_in_content', (int) $in_content );
	}

	/**
	 * Only text lessons have cover image
	 * - try to get image from parent and course
	 *
	 * @return string
	 */
	public function inherit_cover_image() {

		$image = parent::inherit_cover_image();

		//try to get it from parent
		if ( empty( $image ) ) {
			$image = $this->get_parent()->inherit_cover_image();
		}

		//try to get  it from course
		if ( empty( $image ) ) {
			$image = $this->get_course_v2()->cover_image;
		}

		return $image;
	}
}
