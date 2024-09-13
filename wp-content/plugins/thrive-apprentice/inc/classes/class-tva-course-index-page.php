<?php

/**
 * Class TVA_Course_Index_Page
 * Wrapper course index post
 */
class TVA_Course_Index_Page {

	public function __construct() {
		$this->hooks();
	}

	protected function hooks() {

		/**
		 * Filter whether the Edit With TAr button should be displayed on admin bar
		 * - displayed the button for course overview post
		 */
		add_filter(
			'tcb_display_button_in_admin_bar',
			static function ( $display_button ) {
				$post_type = get_post_type();

				if ( TVA_Course_Overview_Post::POST_TYPE === $post_type ) {
					$display_button = true;
				}

				return $display_button;
			}
		);

		/**
		 * When the index page gets updated, we need to flush the rules in case the index page permalink is changed.
		 */
		add_action( 'post_updated', static function ( $post_id ) {
			if ( tva_get_settings_manager()->is_index_page( $post_id ) ) {
				flush_rewrite_rules();
			}

		} );
	}
}
