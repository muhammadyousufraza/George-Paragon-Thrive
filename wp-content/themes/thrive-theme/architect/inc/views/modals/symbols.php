<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<h2 class="tcb-modal-title"><?php echo esc_html__( 'Choose symbol', 'thrive-cb' ) ?></h2>

<span class="click tcb-modal-close"><?php tcb_icon( 'modal-close' ) ?></span>

<div class="status tpl-ajax-status"><?php echo esc_html__( 'Fetching data', 'thrive-cb' ); ?>...</div>
<div class="error-container"></div>

<div class="tvd-input-field">
	<input type="text" class="search-symbols" id="tcm-symbol-search" name="tcm-symbol-search" placeholder="<?php echo esc_html__( 'Search symbols...', 'thrive-cb' ); ?>">
	<span class="search"></span>
</div>

<div style="background: #f0f3f3 url(<?php echo esc_url( tve_editor_css( 'images/symbol_animation_02.gif' ) ); ?>) center no-repeat;" class="tve-symbols-wrapper">
	<div class="text-no-symbols">
		<?php echo esc_html__( "Oups! We couldn't find anything called " ) ?><span class="search-word"></span><?php echo esc_html__( '. Maybe search for something else ?' ); ?>
	</div>
	<div class="symbols-container modal-symbols-templates" id="symbols-template"></div>
</div>

<div class="tcb-modal-footer">
	<button type="button" disabled="false" class="tcb-right tve-button medium green tcb-modal-save">
		<?php echo esc_html__( 'Choose Symbol', 'thrive-cb' ) ?>
	</button>
</div>
