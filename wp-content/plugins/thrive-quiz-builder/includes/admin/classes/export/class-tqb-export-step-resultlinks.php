<?php

/**
 * Handle Result Links export
 *
 * Class TQB_Export_Step_Resultlinks
 */
class TQB_Export_Step_Resultlinks extends TQB_Export_Step_Abstract {

	protected $_name = 'resultlinks';

	/**
	 * Prepare instance data
	 *
	 * @return bool|true
	 */
	protected function _prepare_data() {

		$quiz_structure = new TQB_Structure_Manager( $this->quiz->ID );
		$structure      = $quiz_structure->get_quiz_structure_meta();

		if ( empty( $structure ) ) {
			return true;
		}

		/** @var $results_page TQB_Results_Page */
		$results_page = TQB_Structure_Manager::make_page( $structure['results'] )->to_json();

		if ( true !== $results_page instanceof WP_Post ) {
			return true;
		}

		$links = ! empty( $results_page->links ) ? $results_page->links : array();

		foreach ( $links as $key => $link ) {

			unset( $link->id );

			/**
			 * If the link refers to a local post then we have to export the post too but maybe we'll take care of this later on with next improvements.
			 */
			if ( 'local' === $link->type ) {
				$link->link       = '';
				$link->post_title = '';
				$link->post_id    = null;
				$link->status     = 'invalid';
			}

			$links[ $key ] = $link;
		}

		$this->data['result_links']     = $links;
		$this->data['result_type']      = $results_page->type;
		$this->data['display_message']  = get_post_meta( $results_page->ID, 'tqb_redirect_display_message', true );
		$this->data['forward_results']  = get_post_meta( $results_page->ID, 'tqb_redirect_forward_results', true );
		$this->data['redirect_message'] = get_post_meta( $results_page->ID, 'tqb_redirect_message', true );

		return true;
	}
}
