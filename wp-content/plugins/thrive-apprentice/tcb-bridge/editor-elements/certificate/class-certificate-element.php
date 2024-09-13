<?php

namespace TVA\Architect\Certificate;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Certificate Element class
 */
class Certificate_Element extends \TCB_Cloud_Template_Element_Abstract {

	protected $_tag = 'certificate';

	public function hide() {
		return true;
	}

	public function name() {
		return __( 'Certificate', 'thrive-apprentice' );
	}

	public function identifier() {
		return '.tva-certificate';
	}

	public function category() {
		return $this->get_thrive_integrations_label();
	}

	public function is_placeholder() {
		return false;
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$content = '';

		ob_start();
		include \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/certificate.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function own_components() {
		$typography_cfg = [
			'css_prefix' => tcb_selection_root( false ) . ' .tva-certificate ',
		];
		$prefix_config  = tcb_selection_root() . ' ';

		return [
			'certificate'         => [
				'config' => [
					'Title'       => [
						'config'  => [
							'full-width' => true,
							'label'      => __( 'Certificate title', 'thrive-apprentice' ),
						],
						'extends' => 'LabelInput',
					],
					'Orientation' => [
						'hidden'  => \TVA\TTB\Check::is_end_user_site(), //Only show this on builder website
						'config'  => [
							'name'    => __( 'Orientation', 'thrive-apprentice' ),
							'options' => [
								'landscape' => __( 'Landscape', 'thrive-apprentice' ),
								'portrait'  => __( 'Portrait', 'thrive-apprentice' ),
							],
						],
						'extends' => 'Select',
					],
				],
			],
			'typography'          => [
				'disabled_controls' => [],
				'config'            => [
					'to'             => '> .tve-cb',
					'FontSize'       => $typography_cfg,
					'FontColor'      => $typography_cfg,
					'LineHeight'     => $typography_cfg,
					'FontFace'       => $typography_cfg,
					'ParagraphStyle' => [ 'hidden' => false ],
				],
			],
			'layout'              => [
				'hidden' => true,
			],
			'background'          => [
				'config' => [
					'ColorPicker' => [
						'css_prefix' => $prefix_config,
					],
					'PreviewList' => [
						'css_prefix' => $prefix_config,
					],
					'to'          => '>.tve-content-box-background',
				],
			],
			'borders'             => [
				'hidden' => true,
			],
			'shadow'              => [
				'config' => [
					'disabled_controls' => [ 'drop' ],
					'to'                => '>.tve-content-box-background',
				],
			],
			'decoration'          => [
				'hidden' => true,
			],
			'scroll'              => [
				'hidden' => true,
			],
			'conditional-display' => [
				'hidden' => true,
			],
		];
	}
}
