<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/* delete this option, as it was saved incorrectly before this version. This will be rebuilt when users select a menu template */
delete_option( 'tve_menu_item_templates' );
