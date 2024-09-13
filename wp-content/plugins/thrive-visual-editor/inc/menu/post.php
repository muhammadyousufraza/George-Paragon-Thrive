<div id="tve-post-component" class="tve-component default-visible" data-view="PostOptions">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Status & Visibility', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="VisibilityOptions">
				<div class="control-grid pt-5 pb-25">
					<div class="label">
						<span class="tve-post-visibility-dialog-legend"><?php echo esc_html__( 'Visibility', 'thrive-cb' ); ?></span>
					</div>
					<div class="input tcb-text-right">
						<a href="javascript:void(0)" class="tve-show-visibility-options click" data-fn="showVisibilityOptions"></a>
					</div>
				</div>

				<hr>

				<div class="tve-post-visibility-options drop-panel panel-light tcb-hide mb-10">
					<span><?php echo esc_html__( 'Visibility settings', 'thrive-cb' ); ?></span>
					<div class="tve-post-visibility-option control-grid wrap mt-15">
						<div class="input">
							<input type="radio" name="post-visibility" class="change" data-fn="changeVisibility" id="tve-editor-post-public" value="public">
							<span class="checkmark"></span>
							<label for="tve-editor-post-public" class="tve-post-visibility-dialog-label"><?php echo esc_html__( 'Public', 'thrive-cb' ); ?></label>
						</div>
						<div class="label pl-20 pb-10">
							<span class="tve-post-visibility-dialog-info"><?php echo esc_html__( 'Visible to everyone.', 'thrive-cb' ); ?></span>
						</div>
					</div>
					<div class="tve-post-visibility-option control-grid wrap">
						<div class="input">
							<input type="radio" name="post-visibility" class="change" data-fn="changeVisibility" id="tve-editor-post-private" value="private">
							<span class="checkmark"></span>
							<label for="tve-editor-post-private" class="tve-post-visibility-dialog-label"><?php echo esc_html__( 'Private', 'thrive-cb' ); ?></label>
						</div>
						<div class="label pl-20 pb-10">
							<span class="tve-post-visibility-dialog-info"><?php echo esc_html__( 'Only visible to site admins and editors.', 'thrive-cb' ); ?></span>
						</div>
					</div>
					<div class="tve-post-visibility-option control-grid wrap">
						<div class="input">
							<input type="radio" name="post-visibility" class="change" data-fn="changeVisibility" id="tve-editor-post-password" value="password">
							<span class="checkmark"></span>
							<label for="tve-editor-post-password" class="tve-post-visibility-dialog-label"><?php echo esc_html__( 'Password Protected', 'thrive-cb' ); ?></label>
						</div>
						<div class="label pl-20 pb-10">
							<span class="tve-post-visibility-dialog-info"><?php echo esc_html__( 'Protected with a password you choose. Only those with the password can view this post.', 'thrive-cb' ); ?></span>
						</div>
						<input class="tcb-hide ml-20" id="tve-editor-post-password-input" type="text" placeholder="<?php echo esc_html__( 'Use a secure password', 'thrive-cb' ); ?>" value="">
					</div>

					<div class="control-grid no-space action-buttons">
						<div class="click tve-button drop-panel-action btn-cancel" data-fn="hideVisibilityOptions"><?php echo esc_html__( 'Cancel', 'thrive-cb' ) ?></div>
						<div class="click tve-button drop-panel-action btn-apply" data-fn="saveVisibility"><?php echo esc_html__( 'Apply', 'thrive-cb' ) ?></div>
					</div>

				</div>

				<div class="tve-control tve-publish-options tcb-hide tve-publish-options-unpublish" data-view="UnpublishOptions">
					<div class="control-grid pb-20">
						<span class="tve-post-visibility-dialog-info"><?php echo esc_html__( 'Publish status', 'thrive-cb' ); ?></span>
						<span class="button-group-name label blue tcb-text-right"><?php echo esc_html__( 'Published', 'thrive-cb' ); ?></span>
					</div>
					<div class="tve-modal btn click tve-button btn-text" data-fn="unpublish"><?php echo esc_html__( 'Switch to Draft', 'thrive-cb' ); ?></div>
				</div>

				<div class="tve-control tve-publish-options tcb-hide tve-publish-options-publish" data-view="PublishOptions">
					<div class="control-grid pb-20">
						<span class="tve-post-visibility-dialog-info"><?php echo esc_html__( 'Publish status', 'thrive-cb' ); ?></span>
						<span class="button-group-name label gray tcb-text-right"><?php echo esc_html__( 'Unpublished', 'thrive-cb' ); ?></span>
					</div>
					<div class="tve-btn click tve-button btn-text" data-fn="publish"><?php echo esc_html__( 'Publish', 'thrive-cb' ); ?></div>
				</div>
			</div>
		</div>
	</div>
</div>
