<div class="tcb-revision-row">
	<div>
		<#= item.author.avatar #>
	</div>
	<div class="fill">
		<?php echo esc_html__( 'Revision made by ', 'thrive-cb' ); ?>
		<strong>
			<#= item.author.name #>
		</strong>
		<br>
		<span class="tcb-revision-date-text"><#= item.dateShort #>&nbsp;(<#= item.timeAgo #>)</span>
	</div>
	<div>
		<a class="click tcb-modal-lnk"
		   data-fn="clicked"
		   href="<#= item.restoreUrl #>"><?php echo esc_html__( 'Restore Revision', 'thrive-cb' ) ?></a>
	</div>
</div>
