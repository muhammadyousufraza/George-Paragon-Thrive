<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/3/2017
 * Time: 1:42 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Commentsdisqus_Element
 */
class TCB_Commentsdisqus_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Disqus Comments', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'social';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'disqus_comments';
	}

	/**
	 * Disqus Comments element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_disqus_comments'; // Compatibility with TCB 1.5
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'commentsdisqus' => array(
				'config' => array(
					'ForumName' => array(
						'config'  => array(
							'full-width' => true,
							'label'      => __( 'Forum Name', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'URL'       => array(
						'config'  => array(
							'full-width'  => true,
							'label'       => __( 'URL', 'thrive-cb' ),
							'placeholder' => __( 'http://', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
				),
			),
			'typography'     => [ 'hidden' => true ],
			'animation'      => [ 'hidden' => true ],
			'background'     => [ 'hidden' => true ],
			'shadow'         => [ 'hidden' => true ],
			'layout'         => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'Width',
					'Height',
					'Alignment',
				],
			],
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'disqus_comments',
				'link' => 'https://help.thrivethemes.com/en/articles/4425808-how-to-add-facebook-disqus-comments-in-thrive-architect',
			],
		];
	}
}
