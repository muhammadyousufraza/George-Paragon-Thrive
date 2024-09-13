<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Content_Element
 */
class TCB_Post_Content_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		/**
		 * Used In TA Visual Builder
		 *
		 * @param string $post_content
		 */
		return apply_filters( 'tcb_post_content_element_title', __( 'Post Content', 'thrive-cb' ) );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-content';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-post-content';
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_content';
	}

	/**
	 * Element category
	 *
	 * Calls a filter that allows other plugins to modify the category.
	 * Used in Thrive Apprentice Plugin - Visual Builder
	 *
	 * @return string
	 */
	public function category() {
		return apply_filters( 'tcb_post_content_element_category', parent::category() );
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$default_attr = [
			'data-size'      => 'words',
			'data-read_more' => TCB_Post_List_Content::$default_read_more,
		];

		return TCB_Utils::wrap_content( '', 'section', '', 'tcb-post-content' . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS, $default_attr );
	}

	/**
	 * Add suffixes for post content elements
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$elements = [ ' p', ' a', ' ul', ' ul > li', ' ol', ' ol > li', ' h1', ' h2', ' h3', ' h4', ' h5', ' h6', ' blockquote > p', ' pre' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = $elements;
			}
		}

		$components['typography']['config']['css_suffix'] = $elements;
		$components['layout']['disabled_controls']        = [];

		$components['post_content'] = array(
			'order'  => 1,
			'config' => array(
				'ContentSize'  => array(
					'config'  => array(
						'name'    => __( 'Content', 'thrive-cb' ),
						'buttons' => [
							[
								'icon'  => '',
								'text'  => 'Full',
								'value' => 'content',
							],
							[
								'icon'  => '',
								'text'  => 'Excerpt',
								'value' => 'excerpt',
							],
							[
								'icon'    => '',
								'text'    => 'Words',
								'value'   => 'words',
								'default' => true,
							],
						],
					),
					'extends' => 'ButtonGroup',
				),
				'WordsTrim'    => array(
					'config'  => array(
						'name'      => __( 'Word Count', 'thrive-cb' ),
						'default'   => 12,
						'maxlength' => 2,
						'min'       => 1,
					),
					'extends' => 'Input',
				),
				'ReadMoreText' => array(
					'config'  => array(
						'label'   => __( 'Read More Text', 'thrive-cb' ),
						'default' => '...',
					),
					'extends' => 'LabelInput',
				),
			),
		);

		return $components;
	}
}
