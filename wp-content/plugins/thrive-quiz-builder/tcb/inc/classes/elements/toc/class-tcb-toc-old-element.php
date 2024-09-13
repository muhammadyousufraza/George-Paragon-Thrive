<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Toc_Old_Element extends TCB_Element_Abstract {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Table of Contents', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'index,content,toc';
	}


	/**'
	 * @return string
	 */
	public function icon() {
		return 'table_contents';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.thrv_contents_table';
	}

	public function hide() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array(
			'toc_old'    => array(
				'config' => array(
					'HeaderColor'    => array(
						'config'  => array(
							'label' => __( 'Header', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
						'to'      => '.tve_ct_title',
					),
					'HeadBackground' => array(
						'config'  => array(
							'label' => __( 'Background', 'thrive-cb' ),
						),
						'to'      => '.tve_ct_title',
						'extends' => 'ColorPicker',
					),
					'Headings'       => array(
						'config'  => array(
							'name'   => __( 'Headings', 'thrive-cb' ),
							'inputs' => [
								[
									'name'  => 'h1',
									'label' => 'H1',
								],
								[
									'name'  => 'h2',
									'label' => 'H2',
								],
								[
									'name'  => 'h3',
									'label' => 'H3',
								],
								[
									'name'  => 'h4',
									'label' => 'H4',
								],
								[
									'name'  => 'h5',
									'label' => 'H5',
								],
								[
									'name'  => 'h6',
									'label' => 'H6',
								],
							],
						),
						'extends' => 'MultipleCheckbox',
					),
					'Columns'        => array(
						'config'  => array(
							'name'    => __( 'Columns', 'thrive-cb' ),
							'options' => [
								[
									'value' => '1',
									'name'  => '1',
								],
								[
									'value' => '2',
									'name'  => '2',
								],
								[
									'value' => '3',
									'name'  => '3',
								],
							],
						),
						'extends' => 'Select',
					),
					'Evenly'         => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Distribute Evenly', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Checkbox',
					),
					'MinWidth'       => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '0',
							'max'     => '2000',
							'label'   => __( 'Minimum Width', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'min-width',
						),
						'extends' => 'Slider',
					),
				),
			),
			'background' => [
				'config' => [
					'to' => '.tve_contents_table',
				],
			],
			'borders'    => [
				'config' => [
					'css_suffix' => ' .tve_contents_table',
					'Borders'    => [],
					'Corners'    => [],
				],
			],
			'animation'  => [
				'hidden' => true,
			],
			'typography' => [
				'config' => [
					'to'            => '.tve_ct_content',
					'FontColor'     => [
						'css_suffix' => ' .ct_column a',
					],
					'TextAlign'     => [
						'css_suffix' => ' .ct_column',
					],
					'FontSize'      => [
						'css_suffix' => ' .ct_column a',
					],
					'TextStyle'     => [
						'css_suffix' => ' .ct_column a',
					],
					'LineHeight'    => [
						'css_suffix' => ' .ct_column a',
					],
					'FontFace'      => [
						'css_suffix' => ' .ct_column a',
					],
					'LetterSpacing' => [
						'css_suffix' => ' .ct_column a',
					],
					'TextTransform' => [
						'css_suffix' => ' .ct_column a',
					],
				],
			],
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'inner' ],
				],
			],
			'scroll'     => [
				'hidden' => false,
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
}
