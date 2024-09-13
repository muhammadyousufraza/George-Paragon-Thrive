<?php
$post_id        = get_the_ID();
$revisions      = wp_get_post_revisions( $post_id, ['numberposts' => 10] );
$first_revision = reset( $revisions );
?>
<h2 class="tcb-modal-title ml-0"><?php echo esc_html__( 'Revision Manager', 'thrive-cb' ) ?></h2>
<p class="tcb-modal-description mb-0"><?php echo esc_html__( 'Use the revision manager to restore your page to a previous version:', 'thrive-cb' ); ?></p>
<div id="tcb-revision-list"></div>
<div class="tcb-modal-footer tcb-modal-footer pl-0 pr-0 pt-0">
	<div>
		<?php if ( empty( $first_revision ) ) : ?>
			<?php echo esc_html__( 'The current post has no revisions!', 'thrive-cb' ); ?>
		<?php else : ?>
			<a href="<?php echo esc_url(add_query_arg( [ 'revision' => $first_revision->ID ], admin_url( 'revision.php' ) )); ?>"
			   class="tcb-modal-lnk blue"
			   target="_blank"><?php tcb_icon( 'revision' ); ?>&nbsp;<?php echo esc_html__( 'Show the default WordPress Revision Manager', 'thrive-cb' ); ?></a>
		<?php endif; ?>
	</div>
	<div>
		<button type="button" class="tcb-right tve-button medium green white-text tcb-modal-cancel"><?php echo esc_html__( 'Close', 'thrive-cb' ) ?></button>
	</div>
</div>
