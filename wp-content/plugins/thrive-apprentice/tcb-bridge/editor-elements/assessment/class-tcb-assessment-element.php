<?php

namespace TVA\Architect\Assessment;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * TAR Element Class
 */
class TCB_Assessment_Element extends \TCB_Cloud_Template_Element_Abstract {

	/**
	 * Element tag
	 *
	 * @var string
	 */
	protected $_tag = 'assessment';

	/**
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_action( 'tcb_before_get_content_template', array( $this, 'before_content_template' ), 10, 2 );
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Assessment', 'thrive-apprentice' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'assessment, assignment';
	}

	/**
	 * Decide when to hide the TCB_Course_Element
	 *
	 * @return bool
	 */
	public function hide() {

		if ( \Thrive_Utils::is_theme_template() ) {
			/**
			 * We need to hide the element if we are in TTB context and the template is not an assessment template
			 */
			return ! tva_is_apprentice_template() || ! \TVA\TTB\thrive_apprentice_template()->is_assessment();
		}

		return parent::hide();
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return Main::IDENTIFIER;
	}

	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'assessment-submit';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tva_is_apprentice_template() ? \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_elements_category() : $this->get_thrive_integrations_label();
	}

	public function html() {
		$content = '';

		ob_start();
		include \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/assessment.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Before the assessment content template gets applied modify the post_content to include the active course ID
	 *
	 * @param \WP_Post $post
	 * @param array    $meta
	 */
	public function before_content_template( $post, $meta ) {

		if ( is_array( $meta ) && $meta['type'] === $this->_tag && ! empty( $_REQUEST['assessment_id'] ) && is_numeric( $_REQUEST['assessment_id'] ) ) {
			$assessment_id = (int) $_REQUEST['assessment_id'];

			$replace_string = "[tva_assessment assessment-id='$assessment_id' ";

			$post->post_content = str_replace( '[tva_assessment ', $replace_string, $post->post_content );
		}
	}

	/**
	 * Element components
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'assessment' => [
				'config' => [
					'Palettes'         => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'AssessmentSelect' => [
						'config'  => [
							'label'             => __( 'Choose assessment', 'thrive-apprentice' ),
							'auto_detect_label' => __( 'Auto detect current assessment', 'thrive-apprentice' ),
						],
						'extends' => 'CourseItem',
					],
					'FormType'         => [
						'config'  => [
							'name'    => __( 'Default state', 'thrive-apprentice' ),
							'options' => [
								Main::STATE_AUTO    => __( 'Auto', 'thrive-apprentice' ),
								Main::STATE_SUBMIT  => __( 'Submit', 'thrive-apprentice' ),
								Main::STATE_RESULTS => __( 'Results', 'thrive-apprentice' ),
							],
						],
						'extends' => 'Select',
					],
					'Align'            => [
						'config'  => [
							'name'       => __( 'Size and Alignment', 'thrive-apprentice' ),
							'full-width' => true,
							'buttons'    => [
								[
									'icon'    => 'a_left',
									'value'   => 'left',
									'tooltip' => __( 'Align Left', 'thrive-apprentice' ),
								],
								[
									'icon'    => 'a_center',
									'value'   => 'center',
									'default' => true,
									'tooltip' => __( 'Align Center', 'thrive-apprentice' ),
								],
								[
									'icon'    => 'a_right',
									'value'   => 'right',
									'tooltip' => __( 'Align Right', 'thrive-apprentice' ),
								],
								[
									'text'    => 'FULL',
									'value'   => 'full',
									'tooltip' => __( 'Full Width', 'thrive-apprentice' ),
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'FormWidth'        => [
						'config'  => [
							'default' => '400',
							'min'     => '10',
							'max'     => '1080',
							'label'   => __( 'Form width', 'thrive-apprentice' ),
							'um'      => [ '%', 'px' ],
							'css'     => 'max-width',
						],
						'extends' => 'Slider',
					],
				],
			],
			'typography' => [
				'hidden' => true,
			],
			'animation'  => [
				'hidden' => true,
			],
		];
	}
}
