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
 * Class TCB_LP_Palettes - helper class that handles the Palettes login for Landing Pages
 */
class TCB_LP_Palettes {
	const LP_PALETTES                = 'thrv_lp_template_palettes_v2';
	const LP_PALETTES_CONFIG         = 'thrv_lp_template_palettes_config_v2';
	const SKIN_COLOR_VARIABLE_PREFIX = '--tcb-skin-color-';

	/**
	 * Landing Pade ID
	 *
	 * @var int
	 */
	public $lp_id;

	/**
	 * Palettes specific to Skin
	 *
	 * @var array
	 */
	public $skin_palettes = [];
	/**
	 * Config for Palettes specific to Skin
	 *
	 * @var array
	 */
	public $skin_palettes_config = [];

	public function __construct( $landing_page_id, $skin_palettes, $skin_palettes_config ) {
		if ( empty( $landing_page_id ) ) {
			$landing_page_id = get_the_ID();
		}

		$this->lp_id                = $landing_page_id;
		$this->skin_palettes        = $this->get_smart_lp_palettes_v2( $skin_palettes );
		$this->skin_palettes_config = $this->get_smart_lp_palettes_config_v2( $skin_palettes_config );
	}

	/**
	 * Update the palette for this landing page
	 *
	 * @param $palettes_v2
	 */
	public function update_lp_palettes_v2( $palettes_v2 ) {
		update_post_meta( $this->lp_id, static::LP_PALETTES, $palettes_v2 );

		$this->skin_palettes = $palettes_v2;
	}

	/**
	 * Get the palettes for the current LP from meta
	 *
	 * @param null $post_id
	 *
	 * @return mixed
	 */
	public function get_smart_lp_palettes_v2( $default_palettes = [] ) {
		$palettes = get_post_meta( $this->lp_id, static::LP_PALETTES, true );

		if ( empty( $palettes ) ) {
			$palettes = $default_palettes;
		}

		return $palettes;
	}

	/**
	 * Update the palette config for this landing page
	 *
	 * @param $palettes_config_v2
	 */
	public function update_lp_palettes_config_v2( $palettes_config_v2 ) {
		update_post_meta( $this->lp_id, static::LP_PALETTES_CONFIG, $palettes_config_v2 );

		$this->skin_palettes_config = $palettes_config_v2;
	}

	/**
	 * Get the palettes config from meta
	 *
	 * @param null $post_id
	 *
	 * @return mixed
	 */
	public function get_smart_lp_palettes_config_v2( $default_palettes_config = [] ) {
		$palettes_config = get_post_meta( $this->lp_id, static::LP_PALETTES_CONFIG, true );

		if ( empty( $palettes_config ) ) {
			$palettes_config = $default_palettes_config;
		}

		return $palettes_config;
	}

	/**
	 * Get the palettes colors from the config
	 *
	 * @return array|mixed
	 */
	public function tcb_get_palettes_from_config() {
		return ! empty( $this->skin_palettes_config['palette'] ) ? $this->skin_palettes_config['palette'] : [];
	}

	/**
	 * Update the auxiliary variable in the palettes config
	 *
	 * @param $id
	 * @param $color
	 */
	public function update_auxiliary_variable( $id, $color ) {
		if ( $this->is_auxiliary_variable( $id ) ) {
			$this->skin_palettes_config['palette'][ $id ]['color'] = $color;

			$this->update_lp_palettes_config_v2( $this->skin_palettes_config );
		}
	}

	/**
	 * Check if the color variable is auxiliary
	 * A color is auxiliary if it's not the primary/master variable
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function is_auxiliary_variable( $id ) {
		return ! empty( $this->skin_palettes_config['palette'][ $id ] ) && (int) $this->skin_palettes_config['palette'][ $id ]['id'] === $id && empty( $this->skin_palettes_config['palette'][ $id ]['hsla_code'] );
	}

	/**
	 * Update the master variable in the config
	 *
	 * @param array $master_variables
	 * @param       $active_id
	 */
	public function update_master_hsl( $master_variables = [], $active_id = 0 ) {
		$this->skin_palettes['palettes'][ $active_id ]['modified_hsl'] = $master_variables;

		$this->update_lp_palettes_v2( $this->skin_palettes );
	}

	/**
	 * Updates the variables in config with a given hsl color
	 *
	 * @param $hsl
	 */
	public function update_variables_in_config( $hsl ) {
		/* Update the config */
		foreach ( $this->skin_palettes_config['palette'] as $color_id => $color_obj ) {
			if ( is_numeric( $color_id ) && is_array( $color_obj ) && empty( $this->skin_palettes_config ['palette'][ $color_id ] ) ) {
				$this->skin_palettes_config ['palette'][ $color_id ] = $color_obj;
			}
		}

		$this->update_lp_palettes_config_v2( $this->skin_palettes_config );
	}

	/**
	 * Returns ready to print variables from Palettes config
	 *
	 * @return string
	 */
	public function get_variables_for_css() {
		$data = '';

		if ( ! empty( $this->skin_palettes_config ) && is_array( $this->skin_palettes_config ) ) {
			$palette = $this->skin_palettes_config ['palette'];

			foreach ( $palette as $variable ) {

				$color_name = static::SKIN_COLOR_VARIABLE_PREFIX . $variable['id'];

				if ( ! empty( $variable['hsla_code'] ) && ! empty( $variable['hsla_vars'] ) && is_array( $variable['hsla_vars'] ) ) {
					$data .= $color_name . ':' . $variable['hsla_code'] . ';';

					foreach ( $variable['hsla_vars'] as $var => $css_variable ) {
						$data .= $color_name . '-' . $var . ':' . $css_variable . ';';
					}
				} else {
					$data .= $color_name . ':' . $variable['color'] . ';';

					if ( function_exists( 'tve_rgb2hsl' ) && function_exists( 'tve_print_color_hsl' ) ) {
						$data .= tve_print_color_hsl( $color_name, tve_rgb2hsl( $variable['color'] ) );
					}
				}
			}
		}
		if ( function_exists( 'tve_prepare_master_variable' ) ) {
			$palettes                = $this->get_smart_lp_palettes_v2();
			$active_id               = (int) $palettes['active_id'];
			$master_variable         = $palettes['palettes'][ $active_id ]['modified_hsl'];
			$general_master_variable = tve_prepare_master_variable( [ 'hsl' => $master_variable ] );
			$theme_master_variable   = str_replace( '--tcb-main-master', '--tcb-theme-main-master', $general_master_variable );

			$data .= $general_master_variable;
			$data .= $theme_master_variable;
		}

		return $data;
	}
}


