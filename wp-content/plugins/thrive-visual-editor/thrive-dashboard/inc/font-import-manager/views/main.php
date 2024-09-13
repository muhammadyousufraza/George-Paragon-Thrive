<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/** @var $this Tve_Dash_Font_Import_Manager_View */
?>

<?php if ( $this->messages ) : ?>
	<?php $this->render( 'messages' ) ?>
<?php endif; ?>

<table class="options_table">
	<tr>
		<td class="thrive_options_branding" colspan="2">
			<img class="thrive_admin_logo" src="<?php echo esc_url( TVE_DASH_URL . '/css/images/thrive-logo.png' ); ?>" alt="">
		</td>
	</tr>
</table>

<div class="font-manager-import-settings" style="width: auto;">
	<h3><?php echo esc_html__( "Font Import Manager", 'thrive-dash' ) ?></h3>

	<p><?php echo wp_kses_post( sprintf( __( "Thrive Themes integrates with %s so that you can upload your own font files for use in your web site.", 'thrive-dash' ), '<a target="_blank" href="//www.fontsquirrel.com/">Font Squirrel</a>' ) ); ?></p>

	<h4><?php echo esc_html__( "Follow these steps to import custom fonts into your site:", 'thrive-dash' ) ?></h4>
	<ol>
		<li><?php echo wp_kses_post( sprintf( __( "Download one or more fonts from one of the many font libraries on the web. These files should be ttf or otf format. One such font library is: %s", 'thrive-dash' ), '<a target="_blank" href="//dafont.com">www.dafont.com</a>' ) ); ?></li>
		<li><?php echo wp_kses_post( sprintf( __( "Once downloaded to your computer, you can then upload each font to the Font Squirrel generator tool here: %s", 'thrive-dash' ), '<a target="_blank" href="//www.fontsquirrel.com/tools/webfont-generator">www.fontsquirrel.com/tools/webfont-generator</a>' ) ); ?></li>
		<li><?php echo esc_html__( "Once all your font files are uploaded, you can download the .zip file that is produced to your computer", 'thrive-dash' ) ?></li>
		<li><?php echo esc_html__( 'Upload this file to your site using the "Upload" button below and then click the "Save and Generate Fonts" button', 'thrive-dash' ) ?></li>
		<li><?php echo esc_html__( "Once generated, your fonts will immediately become accessible from the font manager", 'thrive-dash' ) ?></li>
	</ol>

	<h3><?php echo esc_html__( "Import Fonts", 'thrive-dash' ) ?></h3>

	<?php $this->render( 'form' ); ?>

	<h3><?php echo esc_html__( "Your Custom Fonts", 'thrive-dash' ) ?></h3>

	<?php if ( $this->font_pack && $this->font_pack['font_families'] ) : ?>
		<div class="tvd-row">
			<h4 class="tvd-col tvd-m3"><?php echo esc_html__( "Name", 'thrive-dash' ) ?></h4>
			<div class="tvd-col tvd-m9"><?php echo esc_html__( "Preview", 'thrive-dash' ) ?></div>
		</div>
		<?php foreach ( $this->font_pack['font_families'] as $family ) : ?>
			<div class="tvd-row">
				<h4 class="tvd-col tvd-m3">
					<p><?php echo esc_html( $family ) ?></p>
				</h4>
				<div class="tvd-col tvd-m9 tve-dash-font-preview" style="font-family: '<?php echo esc_attr( $family ); ?>'">
					Grumpy wizards make toxic brew for the evil Queen and Jack.
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p><?php echo esc_html__( "No custom fonts added", 'thrive-dash' ) ?></p>
	<?php endif; ?>
	<a class="tvd-waves-effect tvd-waves-light tvd-btn-small tvd-btn-gray" href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_font_manager' ) ); ?>">
		<?php echo esc_html__( "Return to Font Manager", 'thrive-dash' ) ?>
	</a>
	<div class="clear"></div>
</div>
