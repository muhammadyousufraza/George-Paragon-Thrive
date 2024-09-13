<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once 'class-tcb-post-element.php';

/**
 * Class TCB_Landing_Page_Element
 */
class TCB_Landing_page_Element extends TCB_Post_Element {

	/**
	 * Post Component Main Option
	 *
	 * @var array
	 */
	private $post_components = [];

	/**
	 * TCB_Landing_page_Element constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		$this->post_components = parent::post_main_option();
	}

	/**
	 * This is only available when editing landing pages
	 *
	 * @return bool
	 */
	public function is_available() {
		return tcb_post()->is_landing_page();
	}

	public function name() {
		return __( 'Landing Page', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * These settings apply directly on <body>, on landing pages
	 *
	 * @return string
	 */
	public function identifier() {
		return 'body.tve_lp';
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
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Override this from the parent
	 *
	 * @return array
	 */
	public function own_components() {
		return [];
	}

	protected function general_components() {
		$lp = array(
			'landing_page'     => array(
				'config' => array(
					'ContentMaxWidth'  => array(
						'to'      => '#tve_editor',
						'config'  => array(
							'default' => '1080',
							'min'     => '100',
							'max'     => '2400',
							'label'   => __( 'Content Maximum Width', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'ContentWidth'     => array(
						'to'      => '#tve_editor',
						'config'  => array(
							'default' => '1080',
							'min'     => '100',
							'max'     => '2400',
							'label'   => __( 'Layout Maximum Width', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'ContentFullWidth' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Layout covers entire screen width', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
				),
			),
			'lpfonts'          => [ 'order' => 90 ],
			'background'       => [
				'order'             => 110,
				'config'            => [
					'ColorPicker'       => [
						'config' => [
							'icon'      => true,
							'important' => true,
						],
					],
					'PreviewFilterList' => [
						'config' => [
							'sortable'    => false,
							'extra_class' => 'tcb-preview-list-white',
						],
					],
					'PreviewList'       => [
						'config' => [
							'sortable' => true,
						],
					],
				],
				'disabled_controls' => [
					'video',
				],
			],
			'lp-advanced'      => [],
			'scripts_settings' => [ 'order' => 752 ],
		);

		$lp_config = array_merge( $this->post_components + $lp, $this->group_component() );

		return apply_filters( 'tcb_lp_element_extend_config', $lp_config ); /* filter the config in order to extend this in TTB */
	}
}
