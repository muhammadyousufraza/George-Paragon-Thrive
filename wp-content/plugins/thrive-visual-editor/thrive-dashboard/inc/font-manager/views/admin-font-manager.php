<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
 include TVE_DASH_PATH . '/templates/header.phtml'; ?>
<div class="tvd-v-spacer vs-2"></div>
<div class="font-manager-settings">
	<h3><?php echo esc_html__( "Custom Font Manager", 'thrive-dash' ); ?></h3>
	<?php if ( 1 === 2 ) : ?>
		<p>
			<?php echo esc_html__( "By default, Thrive Themes integrates with Google Fonts. This allows you to choose from 600+ fonts for use in your content. However, you can also use the blue import font button below to import your own fonts files using a service called Font Squirrel" ); ?>
			<a href="https://thrivethemes.com/tkb_item/how-to-use-the-font-import-manager/" target="_blank"><?php echo esc_html__( "Learn more about how to import your own fonts", 'thrive' ) ?></a>
		</p>
		<div class="tvd-row">
			<a class="tvd-waves-effect tvd-waves-light tvd-btn-small tvd-btn-blue" href="<?php echo esc_url( admin_url( "admin.php?page=tve_dash_font_import_manager" ) ) ?>">
				<?php echo esc_html__( "Import custom font manager", 'thrive-dash' ) ?>
			</a>
		</div>
	<?php endif; ?>
	<div>
		<div class="tvd-row">
			<h3 class="tvd-col tvd-m2 tvd-s1">
				<?php echo esc_html__( "Font name", 'thrive-dash' ) ?>
			</h3>
			<h3 class="tvd-col tvd-m2 tvd-s1">
				<?php echo esc_html__( "Size", 'thrive-dash' ) ?>
			</h3>
			<h3 class="tvd-col tvd-m2 tvd-s1">
				<?php echo esc_html__( "Color", 'thrive-dash' ) ?>
			</h3>
			<h3 class="tvd-col tvd-m6 tvd-s1">
				<?php echo esc_html__( "CSS Class Name", 'thrive-dash' ) ?>
			</h3>
		</div>

		<?php foreach ( $font_options as $font ): ?>
			<div class="tvd-row">
				<div class="tvd-col tvd-m2 tvd-s1"><?php echo esc_html( $font['font_name'] ); ?></div>
				<div class="tvd-col tvd-m2 tvd-s1"><?php echo esc_html( $font['font_size'] ); ?></div>
				<div class="tvd-col tvd-m2 tvd-s1">
					<span class="tvd-fm-color" style="background-color: <?php echo esc_attr( $font['font_color'] ); ?>;">&nbsp;</span>
					<?php echo empty( $font['font_color'] ) ? esc_html__( 'white', 'thrive-dash' ) : esc_html( $font['font_color'] ); ?>
				</div>
				<div class="tvd-col tvd-m2 tvd-s1">
					<input type="text" readonly value="<?php echo esc_attr( $font['font_class'] ); ?>">
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="tvd-row" style="margin-top: 5px;">
		<div class="tvd-col tvd-m6">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_section' ) ); ?>" class="tvd-waves-effect tvd-waves-light tvd-btn-small tvd-btn-gray">
				<?php echo esc_html__( "Back To Dashboard", 'thrive-dash' ); ?>
			</a>
		</div>
		<div class="tvd-col tvd-m6">
			<input type="hidden" value="<?php echo esc_attr( $font_id ); ?>" id='new-font-id'/>
			<a style="margin-right: 5px;" class="tvd-waves-effect tvd-waves-light tvd-btn-small tvd-btn-blue tvd-right" id="thrive-update-posts" href="javascript:void(0)">
				<i style="display: none;" class="tvd-icon-spinner mdi-pulse"></i> <?php echo esc_html__( "Update Posts", 'thrive-dash' ); ?>
			</a>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function () {
		jQuery( '#thrive-update-posts' ).click( function () {
			var loading = jQuery( this ).find( 'i' );
			loading.show();
			jQuery.post( 'admin-ajax.php?action=tve_dash_font_manager_update_posts_fonts' + '&_wpnonce=' + TVE_Dash_Const.nonce, function ( response ) {
				loading.hide();
			} );
		} );
	} );
</script>
