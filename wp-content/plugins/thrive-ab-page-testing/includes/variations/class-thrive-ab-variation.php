<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Variation extends Thrive_AB_Post {

	protected $_test_item;

	protected $_page;

	/**
	 * Already loaded lightbox which have LG Element
	 *
	 * @var array
	 */
	private static $tcb_lightbox_ids = array();

	public function get_data() {
		$data = array(
			'ID'           => $this->_post->ID,
			'post_title'   => $this->_post->post_title,
			'post_parent'  => $this->_post->post_parent,
			'preview_link' => $this->get_preview_url(),
			'edit_link'    => $this->get_editor_url(),
			'thumb_link'   => $this->get_thumb_link(),
			'is_control'   => $this->get_meta()->get( 'is_control' ),
			'traffic'      => $this->get_meta()->get( 'traffic' ),
			'has_form'     => $this->_has_form(),
		);

		return $data;
	}

	public function get_traffic() {
		return $this->get_meta()->get( 'traffic' );
	}

	public function get_test_item() {

		if ( ! $this->_test_item ) {
			$this->_test_item = new Thrive_AB_Test_Item();

			$this->_test_item->init_by_filters( array(
				'page_id'      => thrive_ab()->maybe_variation( $this->_post ) ? $this->_post->post_parent : $this->_post->ID,
				'variation_id' => $this->_post->ID,
			) );
		}

		return $this->_test_item;
	}

	/**
	 * Delete variation post with all its meta
	 * and if it's control then set the next variation in list as control
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function delete() {

		if ( $this->is_control() ) {
			throw new Exception( 'Control cannot be deleted' );
		}

		$deleted = wp_delete_post( $this->_post->ID, true );

		if ( $deleted === false || is_wp_error( $deleted ) ) {
			throw new Exception( __( 'Variation could not be deleted', 'thrive-ab-page-testing' ) );
		}

		return true;
	}

	/**
	 * Gets the preview thumb for current variation
	 * if the thumb is a print screen then applies a random version number to the url
	 * to prevent browser cache
	 *
	 * @return null|string
	 */
	public function get_thumb_link() {

		$args = array(
			'v' => rand( 1000, 10000 ),
		);

		$thumb = $this->_get_print_screen_url();
		if ( ! empty( $thumb ) ) {
			$thumb = add_query_arg( $args, $thumb );
		}
		$thumb = ! empty( $thumb ) ? $thumb : $this->_get_landing_page_thumb_url();
		$thumb = ! empty( $thumb ) ? $thumb : thrive_ab()->url( 'assets/images/default-variation.jpg' );

		return $thumb;
	}

	/**
	 * Check if the variation has any landing page template set and try to
	 * return template's thumb url
	 *
	 * @return null|string
	 */
	protected function _get_landing_page_thumb_url() {

		$url               = null;
		$landing_page_name = $this->get_meta()->get( 'tve_landing_page' );
		$upload            = wp_upload_dir();

		if ( $landing_page_name && defined( 'TVE_CLOUD_LP_FOLDER' ) ) {
			$thumb_file_path = trailingslashit( $upload['basedir'] ) . TVE_CLOUD_LP_FOLDER . '/templates/thumbnails/' . $landing_page_name . '.png';
		}

		if ( isset( $thumb_file_path ) && is_file( $thumb_file_path ) ) {
			$url = trailingslashit( $upload['baseurl'] ) . TVE_CLOUD_LP_FOLDER . '/templates/thumbnails/' . $landing_page_name . '.png';
		}

		return $url;
	}

	/**
	 * Gets the print screen file url if file exists
	 *
	 * @return null|string
	 */
	protected function _get_print_screen_url() {

		$url           = null;
		$wp_upload_dir = wp_get_upload_dir();
		$filename      = $this->ID . '.png';
		$file_path     = $wp_upload_dir['basedir'] . '/thrive-ab-page-testing/variations/' . $filename;

		if ( is_file( $file_path ) ) {
			$url = $wp_upload_dir['baseurl'] . '/thrive-ab-page-testing/variations/' . $filename;
		}

		return $url;
	}

	public function get_editor_url() {

		if ( $this->_page instanceof WP_Post && $this->_page->post_status === 'draft' ) {

			/**
			 * construct the url manually because WP doesn't see this post_status as draft or pending
			 *
			 * @see _get_page_link()
			 */
			$params = array(
				'tve'    => 'true',
				'action' => 'architect',
			);


			$link = add_query_arg( $params, get_edit_post_link( $this->_post->ID ) );

		} else {

			$link = tcb_get_editor_url( $this->_post->ID );
		}

		return $link;
	}

	public function get_preview_url() {

		$link = tcb_get_preview_url( $this->_post->ID );

		return $link;
	}

	public function is_control() {

		return (bool) $this->_post->is_control;
	}

	public function get_parent_ID() {

		return $this->_post->post_parent;
	}

	public function save( $model ) {

		$saved = wp_update_post( $model );

		return ! is_wp_error( $saved ) && $saved !== 0;
	}

	/**
	 * @return bool
	 */
	protected function _has_form() {

		/**
		 * TOP-108: Remove Subscription goal restriction
		 *
		 * For now, we removed this restriction.
		 */
		return true;

		$content = $this->_get_content();

		/**
		 * check if variation content has any LG ELement in content
		 */
		$has_form = strpos( $content, 'thrv_lead_generation_container' ) !== false;

		/**
		 * Check for Leads ThriveBox
		 */
		$has_form = $has_form || strpos( $content, '[thrive_2step id' ) !== false;
		$has_form = $has_form || strpos( $content, 'thrive_leads_2_step' ) !== false;

		/**
		 * check if variation has a Thrive Lightbox assigned and Thrive Lightbox has LG Element in content
		 */
		$has_form = $has_form || $this->_parse_events( $content );

		return $has_form;
	}

	/**
	 * @return string
	 */
	protected function _get_content() {

		return $this->get_meta()->get( 'tve_updated_post' );
	}

	/**
	 * @param string $content
	 *
	 * @return bool if action content html has LG element
	 */
	protected function _parse_events( $content ) {

		list( $start, $end ) = array(
			'__TCB_EVENT_',
			'_TNEVE_BCT__',
		);
		$event_pattern      = "#data-tcb-events=('|\"){$start}(.+?){$end}('|\")#";
		$triggers           = tve_get_event_triggers();
		$actions            = tve_get_event_actions();
		$registered_actions = array();

		if ( preg_match_all( $event_pattern, $content, $matches, PREG_OFFSET_CAPTURE ) !== false ) {

			foreach ( $matches[2] as $i => $data ) {
				$m = htmlspecialchars_decode( $data[0] ); // the actual matched regexp group
				if ( ! ( $_params = json_decode( $m, true ) ) ) {
					$_params = array();
				}
				if ( empty( $_params ) ) {
					continue;
				}

				foreach ( $_params as $event_config ) {
					if ( empty( $event_config['t'] ) || empty( $event_config['a'] ) || ! isset( $triggers[ $event_config['t'] ] ) || ! isset( $actions[ $event_config['a'] ] ) ) {
						continue;
					}
					$action                = clone $actions[ $event_config['a'] ];
					$registered_actions [] = array(
						'class'        => $action,
						'event_config' => $event_config,
					);
				}
			}
		}

		if ( ! empty( $registered_actions ) ) {
			foreach ( $registered_actions as $data ) {
				if ( ! empty( $data['class'] ) && $data['class'] instanceof TCB_Event_Action_Abstract ) {

					$lightbox_id = $data['event_config']['config']['l_id'];

					if ( in_array( $lightbox_id, self::$tcb_lightbox_ids ) ) {
						return true;
					}

					$content_html = $data['class']->applyContentFilter( $data['event_config'] );
					$has_form     = strpos( $content_html, 'thrv_lead_generation_container' ) !== false;

					if ( $has_form ) {
						self::$tcb_lightbox_ids[] = $lightbox_id;

						return true;
					}
				}
			}
		}

		return false;
	}

	public function copy_thumb_to( $new_variation_id ) {

		$new_filename = $new_variation_id . '.png';

		add_filter( 'upload_dir', array( 'Thrive_AB_Ajax', 'upload_dir' ) );

		$upload = wp_upload_dir();

		$editor = wp_get_image_editor( $upload['path'] . '/' . $this->ID . '.png' );

		if ( ! is_wp_error( $editor ) ) {
			$editor->save( $upload['path'] . '/' . $new_filename );
		}

		remove_filter( 'upload_dir', array( 'Thrive_AB_Ajax', 'upload_dir' ) );
	}

	/**
	 * Set the parent page for current variation for checking
	 *
	 * @param $page WP_Post
	 *
	 * @return $this
	 * @see get_editor_url()
	 *
	 */
	public function set_page( $page ) {
		if ( $page instanceof WP_Post && thrive_ab()->is_cpt_allowed( $page->post_type ) ) {
			$this->_page = $page;
		}

		return $this;
	}
}
