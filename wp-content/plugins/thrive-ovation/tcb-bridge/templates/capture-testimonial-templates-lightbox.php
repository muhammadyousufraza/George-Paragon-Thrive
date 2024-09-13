<?php $templates = tvo_get_testimonial_templates( 'capture' ); ?>
<div class="tvo-frontend-modal">
	<span class="tcb-modal-title ml-0 mt-0">
		<?php echo __( 'Testimonial Capture Templates', 'thrive-ovation' ) ?>
	</span>
	<div class="tvo_capture_templates tvo-templates">
		<?php foreach ( $templates as $template ) : ?>
			<div class="tvo-template click" data-fn="select" data-value="<?php echo $template['file'] ?>">
				<div class="tvo-template-thumbnail click" style="background-image: url('<?php echo $template['thumbnail'] ?>');"></div>
				<div class="tvo-template-name">
					<?php echo $template['name'] ?>
				</div>
				<div class="selected"></div>
			</div>
		<?php endforeach ?>
	</div>
	<div class="tcb-modal-footer flex-end">
		<button class="tve-button green click tvd-right tvo-save-template white-text" data-fn="save">
			<?php echo __( 'Save', 'thrive-ovation' ); ?>
		</button>
	</div>
</div>
