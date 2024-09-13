<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/* we should run the plugins_loaded action and theme check only once */
if ( ! defined( 'TVE_EXTERNAL_ARCHITECT_ACTIONS' ) ) {
	add_action( 'after_setup_theme', function () {

		if ( defined( 'TVE_IN_ARCHITECT' ) && TVE_IN_ARCHITECT ) {
			/* the architect plugin is loaded so he will load all the functionality */
			return;
		}

		/* get all the architect versions from the other plugins that have architect included */
		$external_architect = apply_filters( 'tve_external_architect', [] );

		uksort( $external_architect, 'version_compare' );

		$latest_architect = end( $external_architect );

		if ( ! defined( 'TVE_EDITOR_URL' ) ) {
			define( 'TVE_EDITOR_URL', $latest_architect['url'] );
		}

		if ( ! defined( 'TVE_TCB_CORE_INCLUDED' ) ) {
			/* just to be save, include the core only if it wasn't included before */
			include_once $latest_architect['path'] . '/plugin-core.php';
		}
	}, 0 );

	define( 'TVE_EXTERNAL_ARCHITECT_ACTIONS', true );
}
/**
 * $current_architect_url allows using custom URLs when including this file. In turn, this allows having setups involving symlinks
 */
if ( ! isset( $current_architect_url ) ) {
	$current_architect_url = false;
}
/* register the path and url for the current version of architect */
add_filter( 'tve_external_architect', function ( $versions ) use ( $current_architect_url ) {

	$dir_path     = realpath( __DIR__ );
	$content_path = realpath( WP_CONTENT_DIR );

	if ( ! empty( $current_architect_url ) ) {
		$dir_url = $current_architect_url;
	} else {
		$dir_url = str_replace( $content_path, content_url(), $dir_path ) . '/';
		$dir_url = str_replace( '\\', '/', $dir_url );
	}

	$version = include $dir_path . '/version.php';

	$versions[ $version ] = [
		'path' => $dir_path,
		'url'  => $dir_url,
	];

	return $versions;
} );

