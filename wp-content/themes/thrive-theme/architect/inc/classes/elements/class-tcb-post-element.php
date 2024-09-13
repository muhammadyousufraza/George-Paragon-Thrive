<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Post_Element extends TCB_Element_Abstract {

	/**
	 * @return string
	 */
	public function name() {
		$name = get_post_type() === 'post' ? __( 'Post', 'thrive-cb' ) : __( 'Page', 'thrive-cb' );

		/**
		 * Change post element name
		 */
		return apply_filters( 'tcb_post_element_name', $name );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tve-post-options-element';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Returns the class main option
	 *
	 * Used also in:
	 * inc/classes/elements/class-tcb-post-element.php
	 * inc/classes/elements/class-tcb-landing-page-element.php
	 *
	 * @return array
	 */
	protected function post_main_option() {
		return array(
			'post' => array(
				'config' => array(
					'VisibilityOptions' => array(
						'config' => array(
							'label'   => __( 'Visibility', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'public',
									'name'  => __( 'Public', 'thrive-cb' ),
								),
								array(
									'value' => 'private',
									'name'  => __( 'Private', 'thrive-cb' ),
								),
								array(
									'value' => 'password',
									'name'  => __( 'Password protected', 'thrive-cb' ),
								),
							),
						),
					),
					'PublishOptions'    => [],
					'UnpublishOptions'  => [],
				),
			),
		);
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$post_config = [
			'typography'       => [
				'hidden' => true,
			],
			'layout'           => [
				'hidden' => true,
			],
			'borders'          => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'background'       => [
				'hidden' => true,
			],
			'shadow'           => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
		];

		$post_config = $this->post_main_option() + $post_config;

		/* filter in order to add more components to this in the Theme Builder */

		return apply_filters( 'tcb_post_element_extend_config', $post_config );
	}
}
