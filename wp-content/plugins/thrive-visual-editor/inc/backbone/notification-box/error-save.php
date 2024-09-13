<div class="tcb-notification">
	<div class="click tcb-close-notification" data-fn="close_notification_box"><?php tcb_icon( 'close2' ); ?></div>
	<div class="tcb-notification-icon tcb-notification-icon-error"><?php tcb_icon( 'close2' ); ?></div>
	<div class="tcb-notification-content">
		<div class="tcb-notification-title"><h3><?php echo esc_html__( 'Unable to complete action', 'thrive-cb' ); ?></h3></div>
		<div class="tcb-notification-message"><#= message.error_message #>
			<# if ( message.fixLink ) { #>
			<a href=" <#= message.fixLink #>" target="_blank"><?php echo esc_html__( 'How to fix', 'thrive-cb' );
				tcb_icon( 'external-link-alt-solid' ); ?></a>
			<# } #>
		</div>
		<# if ( message.error_code ) { #>
		<div class="tcb-notification-error-code"><?php echo esc_html__( 'Error code:', 'thrive-cb' ); ?> <#= message.error_code #>
			<# if (message.error_code===500 && message.error_content) {#>
			<div class="click tcb-notification-error-load-more" data-fn="notification_box_show_more"><span class="tve_error_show_more"><?php tcb_icon( 'plus-square-light' ); ?></span><span class="tve_error_show_less tcb-hidden"><?php tcb_icon( 'minus-square-light' ); ?></span></div>
			<blockquote class="tcb-notification-error-response tcb-hidden"> <#=message.error_content#></blockquote>
			<# } #>
		</div>
		<# } #>
	</div>
</div>
