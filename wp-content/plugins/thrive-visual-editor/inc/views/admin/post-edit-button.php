<?php
$license_warning_class='';
if ( TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tcb' ) ) {
	$license_warning_class = 'tve-license-warning';
}elseif ( ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tcb' ) ) {
	$license_warning_class = 'tve-license-warning-red';
}
if ( ! empty( $data['show_migrate_button'] ) ) :
	?>
	<br>
	<div class="postbox" style="text-align: center;">
		<div class="inside thrive-architect">
			<?php echo esc_html__( 'You can upgrade this post / page to Thrive Architect. Upgrading the content will disable the default WP editor for this post and activate the Thrive Architect editor for it. This will allow you to have your content (text and images) saved if you want to disable Thrive Architect for this post. You will not lose any content during this action - all of your current WP editor content will get saved as a "WordPress Content" element and appended to the end of the Thrive Architect content', 'thrive-cb' ); ?>
			<br><br>
			<a class="thrive-architect-edit-link <?php echo $license_warning_class; ?>" href="javascript:void(0)" data-edit="<?php echo esc_attr( $data['edit_url'] ); ?>" id="tcb2-migrate-post">
				<div class="thrive-architect-admin-icon-holder">
					<div class="thrive-architect-admin-icon"></div>
				</div>
				<div class="thrive-architect-admin-text">
					<?php echo esc_html__( 'Upgrade to Thrive Architect', 'thrive-cb' ); ?>
				</div>
			</a>
			<br/>
		</div>
	</div>
	<br>
<?php endif; ?>
<?php tve_enqueue_style( 'tve_architect_edit_links', tve_editor_css( 'thrive-architect-edit-links.css' ) ); ?>
<br/>
<?php if ( ! $data['landing_page'] && ! empty( $data['tcb_enabled'] ) ) : ?>
	<div class="postbox" style="text-align: center;">
		<div class="inside thrive-architect">
			<p>
				<?php echo esc_html__( 'You are currently using Thrive Architect to edit this content.', 'thrive-cb' ); ?>

			</p>
			<p class="bottom-spacing">
				<?php echo esc_html__( 'You can continue editing with Thrive Architect or return to the default WordPress editor', 'thrive-cb' ); ?>
			</p>
			<br>
			<a class="thrive-architect-edit-link tcb-enable-editor <?php echo $license_warning_class; ?>" data-id="<?php echo esc_attr( $data['post_id'] ); ?>" href="<?php echo esc_url( $data['edit_url'] ); ?>"
			   id="thrive_preview_button" target="_blank">
				<div class="thrive-architect-admin-icon-holder">
					<div class="thrive-architect-admin-icon"></div>
				</div>
				<div class="thrive-architect-admin-text">
					<?php echo esc_html__( 'Launch Thrive Architect', 'thrive-cb' ); ?>
				</div>
			</a>
			<p>
				<?php echo esc_html__( 'or', 'thrive-cb' ); ?>
			</p>
			<a href="javascript:void(0)" class="tcb-disable" data-id="<?php echo esc_attr( $data['post_id'] ); ?>"
			   id="tcb2-show-wp-editor"><?php echo esc_html__( 'Return to the WP editor', 'thrive-cb' ); ?></a>
		</div>
	</div>
	<div class="tcb-flags">
		<input disabled="disabled" type="hidden" name="tcb_disable_editor" id="tcb_disable_editor"
			   value="<?php echo esc_attr( wp_create_nonce( 'tcb_disable_editor' ) ); ?>">
	</div>
<?php elseif ( ! empty( $data['landing_page'] ) ) : ?>
	<div class="postbox" style="text-align: center;">
		<div class="inside thrive-architect">
			<p>
				<?php echo esc_html__( 'You are currently using a Thrive Architect landing page to display this piece of content.', 'thrive-cb' ); ?>
			</p>
			<p class="bottom-spacing">
				<?php echo esc_html__( 'You can continue editing with Thrive Architect or revert to your theme template', 'thrive-cb' ); ?>
			</p>
			<br>
			<a class="thrive-architect-edit-link tcb-enable-editor <?php echo $license_warning_class; ?>" data-id="<?php echo esc_attr( $data['post_id'] ); ?>" href="<?php echo esc_url( $data['edit_url'] ); ?>"
			   id="thrive_preview_button" target="_blank">
				<div class="thrive-architect-admin-icon-holder">
					<div class="thrive-architect-admin-icon"></div>
				</div>
				<div class="thrive-architect-admin-text">
					<?php echo esc_html__( 'Launch Thrive Architect', 'thrive-cb' ); ?>
				</div>
			</a>
			<p>
				<?php echo esc_html__( 'or', 'thrive-cb' ); ?>
			</p>
			<a href="javascript:void(0)" class="button tcb-revert" data-nonce="<?php esc_attr_e( $data['nonce'] ); ?>"><?php echo esc_html__( 'Revert to theme template', 'thrive-cb' ); ?></a>
		</div>
	</div>
<?php else : ?>
	<a class="thrive-architect-edit-link tcb-enable-editor <?php echo $license_warning_class; ?>" data-id="<?php echo esc_attr( $data['post_id'] ); ?>" href="<?php echo esc_url( $data['edit_url'] ); ?>"
	   id="thrive_preview_button" target="_blank">
		<div class="thrive-architect-admin-icon-holder">
			<div class="thrive-architect-admin-icon"></div>
		</div>
		<div class="thrive-architect-admin-text">
			<?php echo esc_html__( 'Launch Thrive Architect', 'thrive-cb' ); ?>
		</div>
	</a>
<?php endif; ?>
