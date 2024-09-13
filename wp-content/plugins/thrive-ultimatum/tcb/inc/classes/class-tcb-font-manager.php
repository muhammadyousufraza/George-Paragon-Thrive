<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Main class for handling the editor page related stuff
 *
 * Class TCB_Editor_Page
 */
class TCB_Font_Manager {
	/**
	 * Instance
	 *
	 * @var TCB_Font_Manager
	 */
	private static $instance;

	/**
	 * Singleton instance method
	 *
	 * @return TCB_Font_Manager
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return all fonts needed for font manager
	 *
	 * @return array
	 */
	public function all_fonts() {
		$fonts = array(
			'google' => array(
				'label'           => __( 'Google Fonts', 'thrive-cb' ),
				'fonts'           => [],
				'search_priority' => 99,
			),
			'safe'   => array(
				'label'           => __( 'Web Safe Fonts', 'thrive-cb' ),
				'fonts'           => static::safe_fonts(),
				'search_priority' => 1,
			),
			'custom' => array(
				'label'           => __( 'Custom Fonts', 'thrive-cb' ),
				'fonts'           => $this->custom_fonts(),
				'search_priority' => 1,
			),
		);

		/**
		 * Compatibility with the "Custom Fonts" plugin
		 */
		if ( class_exists( 'Bsf_Custom_Fonts_Taxonomy' ) ) {
			$bsf = [];

			if ( version_compare( BSF_CUSTOM_FONTS_VER, '2.0.0', '<' ) === true ) {
				foreach ( Bsf_Custom_Fonts_Taxonomy::get_fonts() as $font_face => $urls ) {
					$bsf [ $font_face ] = [
						'family'   => $font_face,
						'variants' => [],
						'subsets'  => '',
					];
				}
			} else if ( version_compare( BSF_CUSTOM_FONTS_VER, '2.0.0', '>=' ) === true ) {
				if ( class_exists( 'Bsf_Custom_Fonts_Render', false ) ) {
					$custom_fonts = Bsf_Custom_Fonts_Render::get_instance()->get_existing_font_posts();

					foreach ( $custom_fonts as $key => $font_id ) {
						$font_face          = get_the_title( $font_id );
						$bsf [ $font_id ] = [
							'family'   => $font_face,
							'variants' => [],
							'subsets'  => '',
						];
					}
				}
			}

			if ( $bsf ) {
				$fonts['custom_fonts_plugin'] = array(
					'label'           => __( 'Custom Fonts Plugin', 'thrive-cb' ),
					'fonts'           => $bsf,
					'search_priority' => 1,
				);
			}
		}

		return $fonts;
	}

	/**
	 * Return array of custom fonts
	 *
	 * @return array
	 */
	public function custom_fonts() {
		$custom_fonts   = json_decode( get_option( 'thrive_font_manager_options' ), true );
		$imported_fonts = Tve_Dash_Font_Import_Manager::getImportedFonts();

		if ( ! is_array( $custom_fonts ) ) {
			$custom_fonts = [];
		}

		$imported_keys = [];
		foreach ( $imported_fonts as $imp_font ) {
			$imported_keys[] = $imp_font['family'];
		}

		$return = [];
		foreach ( $custom_fonts as $font ) {
			$return[ $font['font_name'] ] = array(
				'family'         => $font['font_name'],
				'regular_weight' => intval( $font['font_style'] ),
				'class'          => $font['font_class'],
			);
		}

		return $return;
	}

	/**
	 * Return safe fonts array
	 *
	 * @return array
	 */
	public static function safe_fonts() {
		return array(
			array(
				'family'   => 'Georgia, serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Palatino Linotype, Book Antiqua, Palatino, serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Times New Roman, Times, serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Arial, Helvetica, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Arial Black, Gadget, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Comic Sans MS, cursive, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Impact, Charcoal, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Lucida Sans Unicode, Lucida Grande, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Tahoma, Geneva, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Trebuchet MS, Helvetica, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Verdana, Geneva, sans-serif',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Courier New, Courier, monospace',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
			array(
				'family'   => 'Lucida Console, Monaco, monospace',
				'variants' => [ 'regular', 'italic', '600' ],
				'subsets'  => [ 'latin' ],
			),
		);
	}
}
