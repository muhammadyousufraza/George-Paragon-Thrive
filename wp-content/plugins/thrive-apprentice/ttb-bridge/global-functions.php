<?php

namespace TVA\TTB;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


/**
 * Get wizard instance
 *
 * @return Apprentice_Wizard
 */
function tva_wizard() {

	static $tva_wizard;
	if ( null === $tva_wizard ) {
		$tva_wizard = new Apprentice_Wizard();
	}

	return $tva_wizard;
}


/**
 * Returns Skin_Template instance
 *
 * @param int $id template ID
 *
 * @return Skin_Template
 */
function thrive_apprentice_template( $id = 0 ) {
	$is_ajax_rest = wp_doing_ajax() || \TCB_Utils::is_rest();

	if ( empty( $id ) ) {
		if ( ! empty( $_REQUEST['template_id'] ) && $is_ajax_rest ) {
			$id = (int) $_REQUEST['template_id'];
		} elseif ( thrive_wizard()->is_template_preview() ) {
			$id = thrive_wizard()->request( 'template_id' );
		}
	}

	return Skin_Template::instance_with_id( $id );
}
