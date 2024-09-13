<?php $has_zip_archive = class_exists( 'ZipArchive', false ); ?>
<?php $post = get_post_type(); ?>
<div id="tcb-sidebar-settings">
	<?php if ( tcb_editor()->is_landing_page() ) : ?>
		<div class="tve-component s-item">
			<div class="dropdown-header">
				<div class="group-description s-name">
					<?php echo __( 'Global settings', 'thrive-cb' ); ?>
				</div>
			</div>
			<div class="dropdown-content pb-0">
				<div class="field-section s-setting" id="p-header">
					<label><?php echo esc_html__( 'Header', 'thrive-cb' ); ?></label>
					<a href="javascript:void(0)" class="style-input dots click" data-fn="addSection" data-type="header">
						<span class="value tcb-truncate" data-default="<?php echo esc_html__( '[no header added]', 'thrive-cb' ); ?>"><?php echo esc_html__( '[no header added]', 'thrive-cb' ); ?></span>
						<?php tcb_icon( 'pen-regular' ); ?>
					</a>
				</div>
				<div class="field-section s-setting" id="p-footer">
					<label><?php echo esc_html__( 'Footer', 'thrive-cb' ); ?></label>
					<a href="javascript:void(0)" class="style-input dots click" data-fn="addSection" data-type="footer">
						<span class="value tcb-truncate" data-default="<?php echo esc_html__( '[no footer added]', 'thrive-cb' ); ?>"><?php echo esc_html__( '[no footer added]', 'thrive-cb' ); ?></span>
						<?php tcb_icon( 'pen-regular' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="tve-component s-item">
			<div class="dropdown-header">
				<div class="group-description s-name">
					<?php echo __( 'Landing page settings', 'thrive-cb' ); ?>
				</div>
			</div>
			<div class="dropdown-content">
				<button class="click tcb-settings-modal-open-button s-item inside-button" data-fn="saveTemplateLP">
					<span class="s-name"><?php echo esc_html__( 'Save Landing Page', 'thrive-cb' ); ?></span>
					<?php tcb_icon( 'go-to' ); ?>
				</button>

				<hr class="mt-10 mb-10">

				<button class="click tcb-settings-modal-open-button s-item inside-button" data-fn="exportLP">
					<span class="s-name"><?php echo esc_html__( 'Export Landing Page', 'thrive-cb' ); ?></span>
					<?php tcb_icon( 'go-to' ); ?>
				</button>

				<hr class="mt-10 mb-10">

				<button class="click green-ghost-button p-5 w-100" data-fn="lpRevert">
					<?php tcb_icon( 'undo-regular' ); ?>
					<?php echo esc_html__( 'Revert to theme', 'thrive-cb' ); ?>
				</button>
			</div>
		</div>

		<hr class="mt-20" style="background: #dee8e8">
	<?php endif; ?>

	<?php do_action( 'tcb_right_sidebar_top_settings' ); ?>

	<div class="mt-20">
		<?php if ( tcb_editor()->can_use_landing_pages() ) : ?>
			<button class="<?php echo $has_zip_archive ? 'click' : 'disabled-children'; ?> tcb-settings-modal-open-button s-item" data-tooltip="<?php esc_attr_e( $has_zip_archive ? '' : __( 'The PHP ZipArchive extension must be enabled in order to use this functionality. Please contact your hosting provider.', 'thrive-cb' ) ); ?>"
					data-fn="importLP" data-position="top">
				<span class="s-name"><?php echo esc_html__( 'Import Landing Page', 'thrive-cb' ); ?></span>
				<?php tcb_icon( 'go-to' ); ?>
			</button>
		<?php endif; ?>

		<?php if ( tcb_editor()->allow_import_content() ) : ?>
			<button class="<?php echo $has_zip_archive ? 'click' : 'disabled-children'; ?> tcb-settings-modal-open-button s-item" data-fn="importContent" data-position="top" data-tooltip="<?php esc_attr_e( $has_zip_archive ? '' : __( 'The PHP ZipArchive extension must be enabled in order to use this functionality. Please contact your hosting provider.', 'thrive-cb' ) ); ?>">
				<span class="s-name"><?php echo esc_html__( 'Import Content', 'thrive-cb' ); ?></span>
				<?php tcb_icon( 'go-to' ); ?>
			</button>
		<?php endif; ?>

		<?php if ( tcb_editor()->allow_export_content() ) : ?>
			<button class="<?php echo $has_zip_archive ? 'click' : 'disabled-children'; ?> tcb-settings-modal-open-button s-item" data-fn="exportContent" data-position="top" data-tooltip="<?php esc_attr_e( $has_zip_archive ? '' : __( 'The PHP ZipArchive extension must be enabled in order to use this functionality. Please contact your hosting provider.', 'thrive-cb' ) ); ?>">
				<span class="s-name"><?php echo esc_html__( 'Export Content', 'thrive-cb' ); ?></span>
				<?php tcb_icon( 'go-to' ); ?>
			</button>
		<?php endif; ?>

		<?php if ( tcb_editor()->has_save_template_button() ) : ?>
			<button class="click tcb-settings-modal-open-button s-item" data-fn="saveTemplate">
				<span class="s-name"><?php echo esc_html__( 'Save as Template', 'thrive-cb' ); ?></span>
				<?php tcb_icon( 'go-to' ); ?>
			</button>
		<?php endif; ?>

		<?php do_action( 'tcb_right_sidebar_content_settings' ); ?>
	</div>

	<hr class="mt-20" style="background: #dee8e8">

	<div class="tve-component gray-component no-hover">
		<div class="dropdown-header">
			<div class="group-description">
				<?php echo __( 'Advanced settings', 'thrive-cb' ); ?>
			</div>
		</div>
		<div class="dropdown-content">
			<?php if ( tcb_editor()->can_use_page_events() ) : ?>
				<button class="click tcb-settings-modal-open-button s-item" data-fn="pageEvents">
					<span class="s-name"><?php echo esc_html__( 'Page Events', 'thrive-cb' ); ?></span>
					<?php tcb_icon( 'go-to' ); ?>
				</button>
			<?php endif ?>

			<div class="tve-component s-item">
				<div class="dropdown-header">
					<div class="group-description s-name">
						<?php echo __( 'Custom CSS & HTML', 'thrive-cb' ); ?>
					</div>
				</div>
				<div class="dropdown-content pb-0">

					<button class="click green-ghost-button p-5 w-100" data-fn="html">
						<?php echo esc_html__( 'View Page Source (HTML)', 'thrive-cb' ); ?>
					</button>

					<hr class="mt-10 mb-10">

					<button class="click green-ghost-button p-5 w-100 mb-10" data-fn="css">
						<?php echo esc_html__( 'Custom CSS', 'thrive-cb' ); ?>
					</button>

					<?php if ( tcb_editor()->is_landing_page() ) : ?>
						<hr class="mt-10">

						<button class="click green-ghost-button no-border p-0 w-100 flex-center" data-fn="focusScripts">
							<?php echo esc_html__( 'Custom Scripts', 'thrive-cb' ); ?>
							<?php tcb_icon( 'info-circle-solid', false, 'sidebar', 'grey-text ml-5', [
								'data-tooltip' => __( 'Custom Scripts are now more easily accessible in the left settings sidebar', 'thrive-cb' ),
								'data-side'    => 'top',
							] ); ?>
						</button>

						<hr class="mt-10 mb-10">

						<div class="tcb-strip-head-css-settings"></div>
					<?php endif ?>
				</div>
			</div>

			<div class="tve-component s-item">
				<div class="dropdown-header">
					<div class="group-description s-name">
						<?php echo __( 'Asset Optimization', 'thrive-cb' ); ?>
					</div>
				</div>
				<div class="dropdown-content">
					<div class="asset-optimization-settings">
						<?php $is_selected_gutenberg = get_post_meta( get_the_id(), \TCB\Lightspeed\Gutenberg::DISABLE_GUTENBERG, true ); ?>
						<label class="asset-optimization-label"><?php echo esc_html__( 'Gutenberg assets', 'thrive-cb' ); ?></label>
						<select name="<?php echo \TCB\Lightspeed\Gutenberg::DISABLE_GUTENBERG; ?>" data-fn="selectAssetOptimization" class="change">
							<option value="inherit" <?php selected( $is_selected_gutenberg, 'inherit' ); ?>><?php echo esc_html__( 'Inherit', 'thrive-cb' ); ?> </option>
							<option value="enable" <?php selected( $is_selected_gutenberg, '1' ); ?>><?php echo esc_html__( 'Enable', 'thrive-cb' ); ?> </option>
							<option value="disable" <?php selected( $is_selected_gutenberg, '0' ); ?>><?php echo esc_html__( 'Disable', 'thrive-cb' ); ?> </option>
						</select>
						<?php if ( \TCB\Integrations\WooCommerce\Main::active() ): ?>
							<?php $is_selected_woocommerce = get_post_meta( get_the_id(), \TCB\Lightspeed\Woocommerce::DISABLE_WOOCOMMERCE, true ); ?>
							<label class="asset-optimization-label"><?php echo esc_html__( 'WooCommerce assets', 'thrive-cb' ); ?></label>
							<select name="<?php echo TCB\Lightspeed\Woocommerce::DISABLE_WOOCOMMERCE; ?>" data-fn="selectAssetOptimization" class="change">
								<option value="inherit" <?php selected( $is_selected_woocommerce, 'inherit' ); ?>><?php echo esc_html__( 'Inherit', 'thrive-cb' ); ?> </option>
								<option value="enable" <?php selected( $is_selected_woocommerce, '1' ); ?>><?php echo esc_html__( 'Enable', 'thrive-cb' ); ?> </option>
								<option value="disable" <?php selected( $is_selected_woocommerce, '0' ); ?>><?php echo esc_html__( 'Disable', 'thrive-cb' ); ?> </option>
							</select>
						<?php endif; ?>
					</div>
					<div class="tve-grey-box p-5" style="font-size: 12px">
						<?php echo esc_html__( 'These optimization settings apply to WordPress core or 3rd party assets and may affect 3rd party plugin or theme functionality. Please read the documentation for each and thoroughly test your website after making any changes to them.', 'thrive-cb' ); ?>
					</div>
				</div>
			</div>

			<div class="tve-component s-item">
				<div class="dropdown-header">
					<div class="group-description s-name">
						<?php echo __( 'Save Reminders', 'thrive-cb' ); ?>
					</div>
				</div>
				<div class="dropdown-content pb-0">
					<div class="tcb-reminders-switch"></div>
				</div>
			</div>

			<?php do_action( 'tcb_right_sidebar_advanced_settings' ); ?>
		</div>
	</div>
</div>
