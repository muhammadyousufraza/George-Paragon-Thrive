<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Palette
 *
 * @package  TVA\TTB
 * @project  : thrive-apprentice
 */
class Palette {
	/**
	 * Use general singleton methods
	 */
	use \Thrive_Singleton;

	/**
	 * Palette Config Option Name
	 */
	const THEME_PALETTE_CONFIG = 'tva_palette_configuration';

	/**
	 * Master Variables Option Name
	 */
	const THEME_MASTER_VARIABLES = 'tva_master_variables';

	/**
	 * @var array
	 */
	private $colors;

	/**
	 * @var array
	 */
	public $master_hsl;

	/**
	 * @var bool Holds true if sharing color is enabled
	 */
	private $share_color = null;

	/**
	 * Thrive_Palette constructor.
	 */
	public function __construct() {
		$this->colors = get_option( static::THEME_PALETTE_CONFIG, [
			'v'       => 0,
			'palette' => [],
		] );

		$this->master_hsl = $this->get_master_hsl();

		add_action( 'theme_update_master_hsl', [ $this, 'trigger_theme_update_master_hsl' ] );
	}

	/**
	 * Getter for share_color. Instead of reading it in __construct(), only read it when needed
	 *
	 * @return bool
	 */
	public function get_share_color() {
		if ( $this->share_color === null ) {
			$this->share_color = \Thrive_Theme::is_active() && tva_get_setting( 'share_ttb_color' );
		}

		return $this->share_color;
	}

	/**
	 * Checks if the system has palettes
	 *
	 * @return bool
	 */
	public function has_palettes() {
		return ! empty( $this->colors['palette'] );
	}

	/**
	 * Check if the color configuration needs to be updated
	 *
	 * @param array $config
	 */
	public function maybe_update( $config = [] ) {

		if ( $this->colors['v'] === 0 ) {
			//First Time
			$this->colors = $config;
			$this->update_palette( $this->colors );
		} elseif ( (int) $this->colors['v'] < (int) $config['v'] && is_array( $config['palette'] ) ) {
			//Smart update
			$this->colors['v'] = $config['v'];

			foreach ( $config['palette'] as $color_id => $color_obj ) {
				if ( is_numeric( $color_id ) && is_array( $color_obj ) && empty( $this->colors['palette'][ $color_id ] ) ) {
					$this->colors['palette'][ $color_id ] = $color_obj;
				} elseif ( ! empty( $this->colors['palette'][ $color_id ] ) && ! $this->is_auxiliary_variable( $color_id ) ) {
					$this->colors['palette'][ $color_id ]['hsla_code'] = $color_obj['hsla_code'];
					$this->colors['palette'][ $color_id ]['hsla_vars'] = $color_obj['hsla_vars'];
				}
			}

			$this->update_palette( $this->colors );
		}
	}

	/**
	 * @return array
	 */
	public function get_palette() {
		return $this->colors['palette'];
	}

	/**
	 * Used for exporting the palettes
	 *
	 * @return array
	 */
	public function export_palette() {
		return get_option( static::THEME_PALETTE_CONFIG, [
			'v'       => 0,
			'palette' => [],
		] );
	}

	/**
	 * @param \Thrive_Skin $skin
	 */
	public function update_skin_colors( $skin ) {
		$config    = $skin->get_meta( \Thrive_Skin::SKIN_META_PALETTES_V2 );
		$active_id = $config['active_id'];

		$config['palettes'][ $active_id ]['modified_hsl'] = thrive_palettes()->get_master_hsl();

		$skin->set_meta( \Thrive_Skin::SKIN_META_PALETTES_V2, $config );
	}

	/**
	 * Called when a user updates the auxiliary variable
	 *
	 * @param int    $id
	 * @param string $color
	 */
	public function update_auxiliary_variable( $id, $color ) {
		if ( $this->is_auxiliary_variable( $id ) ) {
			$this->colors['palette'][ $id ]['color'] = $color;

			$this->update_palette( $this->colors );
		}
	}

	/**
	 * Returns the master variables for the theme
	 * Returns an HSL array
	 *
	 * @return array
	 */
	public function get_master_hsl() {
		if ( empty( $this->master_hsl ) ) {
			$this->master_hsl = get_option( static::THEME_MASTER_VARIABLES, [] );
		}

		return $this->master_hsl;
	}

	/**
	 * Updates the theme master variables
	 *
	 * @param array   $master_variables
	 * @param boolean $trigger_update_action
	 */
	public function update_master_hsl( $master_variables = [], $trigger_update_action = true ) {
		$this->master_hsl = $master_variables;

		update_option( static::THEME_MASTER_VARIABLES, $master_variables, 'no' );

		/**
		 * May be set to false from other plugins not to trigger the action
		 */
		if ( $trigger_update_action ) {
			$this->trigger_update_master_action( $master_variables );
		}
	}

	/**
	 * Deletes the site palette.
	 *
	 * It is called from the ThemeBuilder Website
	 */
	public function delete_palette() {
		delete_option( static::THEME_PALETTE_CONFIG );
	}

	/**
	 * Updates the theme palette configuration
	 *
	 * @param array $palette_configuration
	 */
	private function update_palette( $palette_configuration = [] ) {
		update_option( static::THEME_PALETTE_CONFIG, $palette_configuration, 'no' );
	}

	/**
	 * Checks if a variable is auxiliary variable
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	private function is_auxiliary_variable( $id ) {
		return ! empty( $this->colors['palette'][ $id ] ) && (int) $this->colors['palette'][ $id ]['id'] === $id && empty( $this->colors['palette'][ $id ]['hsla_code'] );
	}

	/**
	 * Called from the theme when the master variables are updated
	 *
	 * @param array $theme_master_variables
	 */
	public function trigger_theme_update_master_hsl( $theme_master_variables = [] ) {
		if ( $this->get_share_color() ) {
			$this->update_master_hsl( $theme_master_variables );
			$this->update_skin_colors( Main::requested_skin() );
		}
	}

	/**
	 * This should be left empty or the action should be overridden
	 */
	public function trigger_update_master_action( $master_variables = [] ) {
		if ( $this->get_share_color() && thrive_palettes()->has_palettes() ) {
			thrive_palettes()->update_master_hsl( $master_variables, false );
			thrive_palettes()->update_skin_colors( thrive_skin( 0, false ) );
		}
	}

	/**
	 * Reset the master HSL to their default value - ShapeShift blue
	 */
	public function reset_master_hsl() {
		$this->update_master_hsl( [
			'h' => 210,
			's' => 0.79,
			'l' => 0.55,
			'a' => 1,
		], false );
	}
}

/**
 * Returns an instance of apprentice palettes
 *
 * @return Palette
 */
function tva_palettes() {
	return Palette::instance();
}
