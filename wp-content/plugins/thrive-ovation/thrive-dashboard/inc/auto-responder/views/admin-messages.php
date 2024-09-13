<div class="iconmanager-messages">
	<?php if ( ! empty( $GLOBALS['thrive_list_api_message']['error'] ) ) : ?>
		<div class="clear" style="height: 10px"></div>
		<div class="error below-h2" style="margin-left: 0;">
			<p><?php echo strip_tags( $GLOBALS['thrive_list_api_message']['error'], '<a>' ); ?></p>
		</div>
	<?php endif ?>
	<?php if ( ! empty( $GLOBALS['thrive_list_api_message']['success'] ) ) : ?>
		<div class="clear" style="height: 10px"></div>
		<div class="updated below-h2" style="margin-left: 0;">
			<p><?php echo esc_html( $GLOBALS['thrive_list_api_message']['success'] ); ?></p>
			<?php if ( ! empty( $GLOBALS['thrive_list_api_message']['redirect'] ) ) : ?>
				<input type="hidden" id="tve-redirect-to" value="<?php echo esc_attr( $GLOBALS['thrive_list_api_message']['redirect'] ); ?>">
			<?php endif ?>
		</div>
	<?php endif ?>
</div>
