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

<div class="tvd-breadcrumb login-nav">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_section' ) ); ?>" class="tvd-breadcrumb-back">
		<?php echo esc_html__( 'Thrive Dashboard', 'thrive-dash' ); ?>
	</a>
	<span>
		<?php echo esc_html__( 'WordPress Login Screen Branding', 'thrive-dash' ); ?>
	</span>
</div>

<div class="tvd-wrap">
	<div class="tvd-login-preview">
		<iframe title="<?php echo esc_html__( 'Login preview', 'thrive-dash' ); ?>" src="<?php echo esc_url( $design_enabled ? $preview_url : $default_url ); ?>" data-preview-url="<?php echo esc_url( $preview_url ) ?>" data-default-url="<?php echo esc_url( $default_url ); ?>"></iframe>
	</div>
	<div class="tvd-actions">
		<h2>
			<?php echo esc_html__( 'Add your own brand to the default WordPress login screen', 'thrive-dash' ); ?>
		</h2>
		<p>
			<?php echo esc_html__( 'Visually design the WordPress login screen.  This screen is used when users wish to login to the site, set a new password and similar flows.', 'thrive-dash' ); ?>
		</p>
		<p>
			<?php echo esc_html__( 'Activating this feature will mean taking over the default WordPress design.', 'thrive-dash' ); ?>
			<a class="learn-more-link" href="http://help.thrivethemes.com/en/articles/4519097-how-to-use-the-wordpress-login-screen-branding-feature">
				<?php echo esc_html__( 'Learn more', 'thrive-dash' ); ?>
			</a>
		</p>
		<div class="tvd-wrap space-between">
			<div class="tvd-switch">
				<label>
					<?php echo esc_html__( 'ACTIVE', 'thrive-dash' ); ?>
					<input type="checkbox" class="tvd-toggle-input" <?php checked( true, $design_enabled ); ?>>
					<span class="tvd-lever"></span>
				</label>
			</div>
			<div class="tvd-wrap tvd-template-links" style="visibility: <?php echo $design_enabled ? 'visible' : 'hidden'; ?>;">
				<a target="_blank" href="<?php echo esc_url( $preview_url ); ?>">
					<span class="tvd-icon-eye"></span>
					<?php echo esc_html__( 'View', 'thrive-dash' ); ?>
				</a>
				<span class="tvd-sep"></span>
				<a target="_blank" href="<?php echo esc_url( $edit_url ); ?>">
					<span class="tvd-icon-architect"></span>
					<?php echo esc_html__( 'Edit with Thrive Architect', 'thrive-dash' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
<div id="tvd-back-td" class="tvd-col tvd-m6">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_section' ) ); ?>" class="tvd-waves-effect tvd-waves-light tvd-btn-small tvd-btn-gray">
		<?php echo esc_html__( 'Back To Dashboard', 'thrive-dash' ); ?>
	</a>
</div>

<script>
	( $ => {
		const $iframe = $( '.tvd-login-preview iframe' ),
			$links = $( '.tvd-template-links' );

		$( '.tvd-toggle-input' ).on( 'click', ( event ) => {
			const enabled = event.currentTarget.checked ? 1 : 0

			$links.css( 'visibility', enabled ? 'visible' : 'hidden' );

			$.ajax( {
				url: ajaxurl,
				dataType: 'json',
				type: 'POST',
				data: {
					_wpnonce: TVE_Dash_Const.nonce,
					action: TVE_Dash_Const.actions.backend_ajax,
					route: TVE_Dash_Const.routes.settings,
					field: 'tvd_enable_login_design',
					value: enabled
				}
			} ).always( response => {
				TVE_Dash.success( 'Setting saved successfully!' );

				$iframe.attr( 'src', $iframe.attr( 'data-' + ( enabled ? 'preview' : 'default' ) + '-url' ) );
			} );
		} );
	} )( jQuery )
</script>
