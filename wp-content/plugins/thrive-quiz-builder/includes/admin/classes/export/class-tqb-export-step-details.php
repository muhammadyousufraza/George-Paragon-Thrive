<?php

/**
 * Class TQB_Export_Step_Details
 * - prepares and Quiz Details to be exported
 */
class TQB_Export_Step_Details extends TQB_Export_Step_Abstract {

	protected $_name = 'details';

	/**
	 * Prepares quiz details
	 *
	 * @return bool
	 */
	protected function _prepare_data() {

		if ( empty( $this->quiz ) ) {
			return false;
		}

		$quiz_manager = new TQB_Quiz_Manager( $this->quiz->ID );

		$this->data['post_title']         = $this->quiz->post_title;
		$this->data['post_content']       = $this->quiz->post_content;
		$this->data['tpl']                = TQB_Post_meta::get_quiz_tpl_meta( $this->quiz->ID );
		$this->data['type']               = TQB_Post_meta::get_quiz_type_meta( $this->quiz->ID, true );
		$this->data['results']            = $quiz_manager->get_results();
		$this->data['highlight_settings'] = TQB_Post_meta::get_highlight_settings_meta( $this->quiz->ID );
		$this->data['feedback_settings']  = TQB_Post_meta::get_feedback_settings_meta( $this->quiz->ID );
		$this->data['progress_settings']  = tqb_progress_settings_instance( (int) $this->quiz->ID )->get();
		$this->data['scroll_settings']    = TQB_Post_meta::get_quiz_scroll_settings_meta( $this->quiz->ID );
		$this->data['style']              = TQB_Post_meta::get_quiz_style_meta( $this->quiz->ID );

		$this->data['video_options']       = get_post_meta( $this->quiz->ID, TQB_Post_meta::META_NAME_FOR_QUIZ_VIDEO_OPTIONS, true );
		$this->data['audio_options']       = get_post_meta( $this->quiz->ID, TQB_Post_meta::META_NAME_FOR_QUIZ_AUDIO_OPTIONS, true );
		$this->data['qna_templates']       = get_post_meta( $this->quiz->ID, 'tve_qna_templates', true );
		$this->data['custom_css']          = get_post_meta( $this->quiz->ID, 'tve_custom_css', true );
		$this->data['content_before_more'] = get_post_meta( $this->quiz->ID, 'tve_content_before_more', true );
		$this->data['post_constants']      = get_post_meta( $this->quiz->ID, '_tve_post_constants', true );
		$this->data['tve_globals']         = get_post_meta( $this->quiz->ID, 'tve_globals', true );
		$this->data['tve_updated_post']    = get_post_meta( $this->quiz->ID, 'tve_updated_post', true );

		$settings_keys = array(
			'tqb_quiz_video_style',
			'tge_display_weight',
			'tge_display_tags',
			'tge_display_feedback',
		);

		foreach ( $settings_keys as $key ) {
			$this->data['settings'][ $key ] = get_post_meta( $this->quiz->ID, $key, true );
		}

		return true;
	}
}
