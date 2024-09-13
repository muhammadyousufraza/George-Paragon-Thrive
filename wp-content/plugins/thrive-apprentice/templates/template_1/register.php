<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

do_action( 'tva_register' );
?>

<?php include_once( TVA_Const::plugin_path( 'templates/header.php' ) ) ?>

<?php

//Remove ACF custom fields from default register form
global $acf_instances;

if ( ! empty( $acf_instances ) && is_object( $acf_instances['ACF_Form_User'] ) ) {
	remove_action( 'register_form', [ $acf_instances['ACF_Form_User'], 'render_register' ] );
}

do_action( 'register_form' );
?>

<?php include_once( TVA_Const::plugin_path( 'templates/footer.php' ) ) ?>
