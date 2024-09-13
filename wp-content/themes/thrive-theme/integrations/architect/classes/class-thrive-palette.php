<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Palette
 *
 * Class overridden in thrive-apprentice
 */
class Thrive_Palette {
	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Palette Config Option Name
	 */
	const THEME_PALETTE_CONFIG = 'thrive_theme_palette_configuration';

	/**
	 * Master Variables Option Name
	 */
	const THEME_MASTER_VARIABLES = 'thrive_theme_master_variables';

	/**
	 * @var array
	 */
	private $colors;

	/**
	 * @var array
	 */
	public $master_hsl;

	/**
	 * Thrive_Palette constructor.
	 */
	public function __construct() {
		$this->colors = get_option( static::THEME_PALETTE_CONFIG, [
			'v'       => 0,
			'palette' => [],
		] );

		$this->master_hsl = $this->get_master_hsl();
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

			if ( (int) $this->colors['v'] === 1 ) {
				/**
				 * When V = 1 it means that is a fresh install of TTB and no skin is active.
				 * In this case we override the palette with what comes from cloud
				 */
				$this->colors['palette'] = $config['palette'];
			} else {
				//Smart update
				foreach ( $config['palette'] as $color_id => $color_obj ) {
					if ( is_numeric( $color_id ) && is_array( $color_obj ) && empty( $this->colors['palette'][ $color_id ] ) ) {
						$this->colors['palette'][ $color_id ] = $color_obj;
					} elseif ( ! empty( $this->colors['palette'][ $color_id ] ) && ! $this->is_auxiliary_variable( $color_id ) ) {
						$this->colors['palette'][ $color_id ]['hsla_code'] = $color_obj['hsla_code'];
						$this->colors['palette'][ $color_id ]['hsla_vars'] = $color_obj['hsla_vars'];
					}
				}
			}

			$this->colors['v'] = $config['v'];

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
	 * @param Thrive_Skin $skin
	 */
	public function update_skin_colors( $skin ) {
		$config    = $skin->get_meta( Thrive_Skin::SKIN_META_PALETTES_V2 );
		$active_id = $config['active_id'];

		$config['palettes'][ $active_id ]['modified_hsl'] = thrive_palettes()->get_master_hsl();

		$skin->set_meta( Thrive_Skin::SKIN_META_PALETTES_V2, $config );
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
	 * Triggers the update theme master variables action
	 *
	 * Allows other classes that extend this class to override the action that is being triggered
	 *
	 * @param array $master_variables
	 */
	public function trigger_update_master_action( $master_variables = [] ) {
		do_action( 'theme_update_master_hsl', $master_variables );
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
}

/**
 * Returns the thrive palettes instance
 *
 * @return Thrive_Palette
 */
function thrive_palettes() {
	return Thrive_Palette::instance();
}
