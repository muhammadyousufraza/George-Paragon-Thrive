<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<# if( typeof fromSkin !== 'undefined' && fromSkin ) { #>
<?php

tcb_icon( 'ttb-skin-colored', false, 'sidebar', 'click mouseleave mouseover', [
	'data-fn'           => 'toggleTooltip',
	'data-tooltip-type' => 'skin',
] );
?>
<# } #>
