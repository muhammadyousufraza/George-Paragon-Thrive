<div id="tve-lpfonts-component" class="tve-component default-visible">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Typography', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<?php if ( tcb_editor()->is_landing_page() ) : ?>
				<div class="typography-message-container">
					<section class="typography-component-section">
						<span class="message-topography "><?php echo esc_html__( 'Landing page typography is now more easily accessible in the Central Style Panel', 'thrive-cb' ); ?></span>
					</section>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
