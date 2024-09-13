<div class="tve-post-option-confirm tve-post-option-confirm-private tcb-hide">
	<div class="clearfix pb-10 tcb-post-options-modal-content flex-center">
		<?php if ( get_post_type() === 'post' ) : ?>
			<h2><?php echo esc_html__( 'Are you sure you want to privately publish this post?', 'thrive-cb' ) ?></h2>
		<?php else : ?>
			<h2><?php echo esc_html__( 'Are you sure you want to privately publish this page?', 'thrive-cb' ) ?></h2>
		<?php endif; ?>
	</div>

	<div class="tcb-modal-footer pl-0 pr-0">
		<button type="button" class="tcb-left tve-button text-only tve-cancel-change tcb-modal-cancel click" data-fn="cancelChanges">
			<?php echo esc_html__( 'Cancel', 'thrive-cb' ) ?>
		</button>
		<button type="button" class="tcb-right tve-button medium tcb-modal-save click" data-post-status="private" data-fn="setPostStatus">
			<?php echo esc_html__( 'Yes, privately publish', 'thrive-cb' ) ?>
		</button>
	</div>
</div>

<div class="tve-post-option-confirm tve-post-option-confirm-unpublish tcb-hide">
	<div class="clearfix pb-10 tcb-post-options-modal-content flex-center">
		<?php if ( get_post_type() === 'post' ) : ?>
			<h2><?php echo esc_html__( 'Are you sure you want to unpublish this post?', 'thrive-cb' ) ?></h2>
		<?php else : ?>
			<h2><?php echo esc_html__( 'Are you sure you want to unpublish this page?', 'thrive-cb' ) ?></h2>
		<?php endif; ?>
	</div>

	<div class="tcb-modal-footer pl-0 pr-0">
		<button type="button" class="tcb-left tve-button text-only tve-cancel-change tcb-modal-cancel click" data-fn="cancelChanges">
			<?php echo esc_html__( 'Cancel', 'thrive-cb' ) ?>
		</button>
		<button type="button" class="tcb-right tve-button medium tcb-modal-save click" data-post-status="draft" data-fn="setPostStatus">
			<?php echo esc_html__( 'Yes, unpublish', 'thrive-cb' ) ?>
		</button>
	</div>
</div>

<div class="tve-post-option-confirm tve-post-option-confirm-publish tcb-hide">
	<div class="clearfix pb-10 tcb-post-options-modal-content flex-center">
		<?php if ( get_post_type() === 'post' ) : ?>
			<h2><?php echo esc_html__( 'Are you sure you want to publish this post?', 'thrive-cb' ) ?></h2>
		<?php else : ?>
			<h2><?php echo esc_html__( 'Are you sure you want to publish this page?', 'thrive-cb' ) ?></h2>
		<?php endif; ?>
	</div>

	<div class="tcb-modal-footer pl-0 pr-0">
		<button type="button" class="tcb-left tve-button text-only tve-cancel-change tcb-modal-cancel click" data-fn="cancelChanges">
			<?php echo esc_html__( 'Cancel', 'thrive-cb' ) ?>
		</button>
		<button type="button" class="tcb-right tve-button medium tcb-modal-save click" data-post-status="publish" data-fn="setPostStatus">
			<?php echo esc_html__( 'Yes, publish', 'thrive-cb' ) ?>
		</button>
	</div>
</div>
