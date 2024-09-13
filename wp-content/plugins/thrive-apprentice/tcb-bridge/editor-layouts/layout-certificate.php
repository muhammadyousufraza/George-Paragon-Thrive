<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$id      = get_the_ID();
$title   = get_the_title();
$content = TCB_Symbol_Template::render_content( array(), true );
TCB_Symbol_Template::body_open();
?>
	<div class="tve-symbol-container">
		<div class="tve_flt" id="tve_flt">
			<?php if ( is_editor_page_raw() ) : ?>
				<div class="symbol-extra-info">
					<p class="sym-l"><?php echo esc_html__( "Currently Editing \"{$title}\"" ); ?></p>
					<p class="sym-r"><?php echo esc_html__( 'Certificate templates have a fixed width and height suitable for saving to PDF and printing on standard paper.' ); ?></p>
				</div>
			<?php endif; ?>
			<div id="tve_editor" data-content="<?php echo esc_html__( 'Add Content Here' ); ?>"><?php echo $content; ?></div>
		</div>
	</div>
<?php
TCB_Symbol_Template::body_close();
