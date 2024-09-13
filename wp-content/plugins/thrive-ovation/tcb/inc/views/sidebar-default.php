<div id="left-editor-sidebar">
	<div>
		<span class="summary">
			<?php echo sprintf( esc_html__( 'Select or add an element on the %s canvas in order to activate this sidebar.', 'thrive-cb' ), '<br>' ); ?>
		</span>

		<?php if ( tcb_editor()->has_templates_tab() ) : ?>
			<img src="<?php echo esc_url( tve_editor_css( 'images/sidebar-blank-tpl.png' ) ); ?>" width="207" height="328"
				 srcset="<?php echo esc_url( tve_editor_css( 'images/sidebar-blank-tpl@2x.png' ) ); ?> 2x">
		<?php else : ?>
			<img src="<?php echo esc_url( tve_editor_css( 'images/sidebar-blank.png' ) ); ?>" width="193" height="326"
				 srcset="<?php echo esc_url( tve_editor_css( 'images/sidebar-blank@2x.png' ) ); ?> 2x">
		<?php endif; ?>
	</div>

	<div class="tcb-sidebar-more-features click" data-fn="openHelpCornerLightbox">
		<div>
			<p><?php echo __( 'Curious about other cool features?', 'thrive-cb' ); ?></p>
			<button class="click text-button text-green"><?php echo __( 'More Features', 'thrive-cb' ); ?></button>
		</div>
		<img class="help-corner-default click" src="<?php echo esc_url( tve_editor_css( 'images/loud-voice.svg' ) ); ?>"/>
	</div>
</div>
