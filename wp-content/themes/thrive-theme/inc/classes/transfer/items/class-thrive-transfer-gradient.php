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
 * Class Thrive_Transfer_Gradient
 */
class Thrive_Transfer_Gradient extends Thrive_Transfer_Base {

	/**
	 * Global gradient regular expression
	 */
	const GRADIENT_REGEX = '/--tcb-gradient-(\d+)\)/';

	/**
	 * Global gradient option name from the db
	 */
	const GRADIENT_OPTION = 'thrv_global_gradients';

	/**
	 * Json filename where to keep the data for the gradients
	 *
	 * @var string
	 */
	public static $file = 'gradients.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	public $tag = 'gradient';

	/**
	 * Get global gradients used in css content
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function read( &$content ) {

		if ( preg_match_all( self::GRADIENT_REGEX, $content, $matches ) && Thrive_Utils::is_end_user_site() ) {

			$global_gradients = get_option( self::GRADIENT_OPTION, [] );

			foreach ( $matches[1] as $gradient_id ) {
				foreach ( $global_gradients as $existing_gradient ) {
					if ( $existing_gradient['id'] == $gradient_id ) {
						$this->items[ $gradient_id ] = $existing_gradient;

						$content = str_replace( '(' . Thrive_Transfer_Utils::GRADIENT_PREFIX . $gradient_id . ')', '(' . Thrive_Transfer_Utils::GRADIENT_PREFIX . md5( $gradient_id ) . ')', $content );
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Add global gradients to archive data
	 */
	public function add() {
		$gradients = $this->archive_data['gradient'];

		foreach ( $this->items as $id => $item ) {
			if ( empty( $gradients[ $id ] ) ) {
				$gradients[ $id ] = $item;
			}
		}

		$this->archive_data['gradient'] = $gradients;
	}

	/**
	 * Import global gradients from an array parameter pulled from the archive
	 */
	public function import() {
		$imported_gradients_map = [];

		if ( ! empty( $this->data ) ) {
			$global_gradients = get_option( self::GRADIENT_OPTION, [] );
			if ( ! is_array( $global_gradients ) ) {
				$global_gradients = [];
			}
			foreach ( $this->data as $key => $imported_gradient ) {
				$exists = false;

				foreach ( $global_gradients as $existing_gradient ) {
					if ( ! empty( $existing_gradient['gradient'] ) && $existing_gradient['gradient'] === $imported_gradient['gradient'] ) {
						$imported_gradients_map[ $key ] = $existing_gradient['id'];
						$exists                         = true;
					}
				}
				if ( ! $exists ) {
					$imported_gradients_map[ $key ] = $imported_gradient['id'] = count( $global_gradients );

					$global_gradients[] = $imported_gradient;
				}
			}
			update_option( self::GRADIENT_OPTION, $global_gradients );
		}

		$this->archive_data[ $this->tag ] = $imported_gradients_map;
	}
}
