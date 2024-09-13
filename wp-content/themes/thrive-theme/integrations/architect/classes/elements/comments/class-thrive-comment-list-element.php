<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comment_List_Element
 */
class Thrive_Comment_List_Element extends Thrive_Theme_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comments List', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-list';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {

		$post_id = url_to_postid( thrive_template()->url() );
		$content = '';

		if ( $post_id ) {
			$comments = get_comments( [ 'post_id' => $post_id ] );

			ob_start();

			wp_list_comments( [ 'style' => 'ol', 'short_ping' => true ], $comments );

			$content = ob_get_contents();
			ob_end_clean();
		}

		return TCB_Utils::wrap_content( $content, 'ol', '', 'comment-list' );
	}

	/**
	 * This element is a shortcode
	 *
	 * @return bool
	 */
	public function is_shortcode() {
		return true;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public static function shortcode() {
		return 'thrive_comment_list';
	}

	/**
	 * This element has no icons
	 *
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}

	/**
	 * This element has a selector
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}

	public function own_components() {
		$components = parent::own_components();

		$components['thrive_comments_list'] = [
			'config' => [
				'ReplySpacing' => [
					'config'  => [
						'default' => '20',
						'min'     => '1',
						'max'     => '200',
						'label'   => __( 'Reply Spacing', 'thrive-theme' ),
						'um'      => [ 'px' ],
						'css'     => 'margin-left',
					],
					'to'      => 'ol.children',
					'extends' => 'Slider',
				],
			],
		];

		return $components;
	}
}

return new Thrive_Comment_List_Element( 'thrive_comment_list' );

