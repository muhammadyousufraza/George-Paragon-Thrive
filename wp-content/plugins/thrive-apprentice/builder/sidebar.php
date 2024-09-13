<?php
/**
 * The template for displaying default sidebar
 *
 * @package thrive-theme
 */

if ( is_active_sidebar( THRIVE_DEFAULT_SIDEBAR ) ) {
	dynamic_sidebar( THRIVE_DEFAULT_SIDEBAR );
}

