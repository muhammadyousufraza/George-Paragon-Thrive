<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class fro Thrive Automator
 * - which extends and overwrites the Thrive Plugin
 * - TAP is a free product of TTW
 */
class TPM_Product_Plugin_Automator extends TPM_Product_Plugin {

	/**
	 * @return true because this product offered by TTW as free
	 */
	public function is_purchased() {
		return true;
	}

	/**
	 * @return true because this product offered by TTW as free
	 */
	public function is_licensed() {
		return true;
	}
}
