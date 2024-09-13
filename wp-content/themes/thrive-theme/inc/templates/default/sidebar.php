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

[tcb_search_form]

<div class="thrv_wrapper thrv_text_element theme-style" data-tag="h6">
	<h6><?php echo __( 'Categories', 'thrive-theme' ); ?></h6>
</div>
<div class="thrv_wrapper theme-sidebar-divider thrv-divider" data-style="tve_sep-1" data-thickness="1" data-color="rgb(10, 10, 10)">
	<hr class="tve_sep tve_sep-1">
</div>
[thrive_dynamic_list icon='icon-angle-right-light' type='categories']

<div class="thrv_wrapper thrv_text_element theme-style" data-tag="h6">
	<h6><?php echo __( 'Recent Posts', 'thrive-theme' ); ?></h6>
</div>
<div class="thrv_wrapper theme-sidebar-divider thrv-divider" data-style="tve_sep-1" data-thickness="1" data-color="rgb(10, 10, 10)">
	<hr class="tve_sep tve_sep-1">
</div>
[thrive_dynamic_list type='post']
