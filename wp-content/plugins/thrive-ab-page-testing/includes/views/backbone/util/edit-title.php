<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<input id="tcb-admin-edit-text" data-bind="value" type="text" value="<#= item.get('post_title') #>">
<label for="tcb-admin-edit-text" data-error="<?php echo sprintf( __( 'Title is required', 'thrive-cb' ) ) ?>"  class="tvd-active">&nbsp;</label>
