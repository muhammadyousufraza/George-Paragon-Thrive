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
<?php $symbol_id = get_the_ID(); ?>
<?php $symbol_title = get_the_title(); ?>
<?php

$is_gutenberg_preview = isset( $_GET['tve_block_preview'] );
$content              = TCB_Symbol_Template::render_content( [], true );

/**
 * on gutenberg preview display a placeholder
 */
if ( $is_gutenberg_preview ) {
	/**
	 * since its the frontend page check for empty content without default styles
	 */
	$symbol_content = TCB_Symbol_Template::content( $symbol_id );
	if ( empty( $symbol_content ) ) {
		$content = tcb_template( 'elements/no-symbol-content.php', [], true );
	}
}

if ( empty( $content ) ) {
	$content = tcb_template( 'elements/block.php', [], true );
}


?>
<?php $symbol_vars = TCB_Symbol_Template::get_edit_symbol_vars(); ?>
<?php $type = ucfirst( $symbol_vars['type'] ); ?>
<?php $type = preg_replace( '/-/', ' ', $type ); ?>
<?php $data_attr = TCB_Symbol_Template::data_attr( $symbol_id ); ?>
<?php TCB_Symbol_Template::body_open(); ?>
<div class="tve-leads-conversion-object">
	<div id="tve-leads-editor-replace">
		<div class="tve-symbol-container">
			<div class="tve_flt" id="tve_flt">
				<div class="symbol-extra-info">
					<p class="sym-l"><?php echo esc_html__( "Currently Editing {$type} \"{$symbol_title}\"" ); ?></p>
					<p class="sym-r"><?php echo sprintf( esc_html__( "Note that this {$type} doesn't have any width settings. %sIt will expand to the full width of the content area of your theme." ), '<br>' ); ?></p>
				</div>
				<div id="tve_editor" class="tve_editable thrv_symbol thrv_symbol_empty <?php echo esc_attr( $symbol_vars['css_class'] ); ?> thrv_symbol_<?php echo esc_attr( $symbol_id ); ?>" data-content="<?php echo esc_html__( "Add {$type} Content Here" ); ?>"<?php echo $data_attr; ?>><?php echo $content; ?></div> <?php // phpcs:ignore ?>
			</div>
		</div>
	</div>
</div>
<?php TCB_Symbol_Template::body_close(); ?>
