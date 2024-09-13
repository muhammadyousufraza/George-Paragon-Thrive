<div class="tcb-modals">
	<?php foreach ( $data['files'] as $file ) : $modal_id = 'tcb-modal-' . str_replace( [ '.php', '_' ], [ '', '-' ], basename( $file ) ); ?>
		<div id="<?php echo esc_attr( $modal_id ); ?>" class="tcb-modal <?php echo esc_attr( $modal_id ); ?>">
			<div class="tcb-modal-content">
				<?php include $file; ?>
			</div>
			<span class="click tcb-modal-close"><?php tcb_icon( 'modal-close' ); ?></span>
			<div class="tve-modal-loader tcb-hidden">
				<span class="click tcb-modal-refresh"><?php tcb_icon( 'refresh-icon' ); ?></span>
			</div>
		</div>
	<?php endforeach; ?>
</div>
