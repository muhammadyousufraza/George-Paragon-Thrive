<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/4/2017
 * Time: 11:56 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Commentsfacebook_Element
 */
class TCB_Commentsfacebook_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Facebook Comments', 'thrive-cb' );
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
		return 'facebook_comments';
	}

	/**
	 * Facebook Comments element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_facebook_comments'; // Compatibility with TCB 1.5
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'commentsfacebook' => array(
				'config' => array(
					'moderators'     => array(
						'config'  => array(
							'top_text'        => __( 'Add Facebook user ID for the people that you will like to moderate the comments.', 'thrive-cb' ),
							'add_button_text' => __( 'Add New Moderator', 'thrive-cb' ),
							'list_label'      => 'ID',
							'remove_title'    => __( 'Remove Moderator', 'thrive-cb' ),
							'list_items'      => [],
						),
						'extends' => 'InputMultiple',
					),
					'URL'            => array(
						'config'  => array(
							'full-width'  => true,
							'label'       => __( 'URL', 'thrive-cb' ),
							'placeholder' => 'http://',
						),
						'extends' => 'LabelInput',
					),
					'nr_of_comments' => array(
						'config'  => array(
							'default' => '20',
							'min'     => '1',
							'max'     => '200',
							'label'   => __( 'Number of comments', 'thrive-cb' ),
							'um'      => [],
						),
						'extends' => 'Slider',
					),
					'color_scheme'   => array(
						'config'  => array(
							'name'    => __( 'Color Scheme', 'thrive-cb' ),
							'options' => [
								[
									'value' => 'light',
									'name'  => 'Light',
								],
								[
									'value' => 'dark',
									'name'  => 'Dark',
								],
							],
						),
						'extends' => 'Select',
					),
					'order_by'       => array(
						'config'  => array(
							'name'    => __( 'Order By', 'thrive-cb' ),
							'options' => [
								[
									'value' => 'social',
									'name'  => 'Social Popularity',
								],
								[
									'value' => 'time',
									'name'  => 'Oldest First',
								],
								[
									'value' => 'reverse_time',
									'name'  => 'Newest first',
								],
							],
						),
						'extends' => 'Select',
					),
				),
			),
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'background'       => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'layout'           => [ 'disabled_controls' => [ 'Height', 'Width', 'Alignment', 'Overflow', 'ScrollStyle' ] ],
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
				'url'  => 'facebook_comments',
				'link' => 'https://help.thrivethemes.com/en/articles/4425808-how-to-add-facebook-disqus-comments-in-thrive-architect',
			],
		];
	}
}
