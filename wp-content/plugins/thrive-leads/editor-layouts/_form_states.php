<?php
$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
if ( ! $is_ajax && ! is_editor_page_raw() ) {
	return;
}
global $variation; // this is the main variation (variation parent)
if ( ! isset( $current_variation ) ) {
	$current_variation = $variation; // this is the variation being edited now
}
/**
 * Shows a bar at the bottom of the page having all of the states defined for this form
 */
$states             = tve_leads_get_form_related_states( $variation );
$total_states       = count( $states );
$parent_form_type   = tve_leads_get_form_type_from_variation( $variation, true );
$already_subscribed = false; ?>
<?php if ( empty( $do_not_wrap ) ) : ?>
	<div class="tl-form-states-container" id="tl-form-states">
<?php endif ?>
	<div class="design-states hide-from-view mouseenter mouseleave" data-fn-mouseenter="cancel_hide" data-fn-mouseleave="collapse">
		<span class="title"><?php echo __( 'Current States', 'thrive-leads' ) ?></span>
		<button data-fn="collapse" title="<?php echo __( 'Close', 'thrive-leads' ) ?>" class="click state-close btn-icon">
			<?php tcb_icon( 'close2' ) ?>
		</button>
		<ul class="state-steps fix-height-states">
			<?php foreach ( $states as $index => $v ) :
				if ( $v['form_state'] == 'already_subscribed' ) {
					$already_subscribed = true;
				} ?>
				<li data-fn="select" data-id="<?php echo $v['key'] ?>" class="click<?php echo $v['key'] == $current_variation['key'] ? ' lightbox-step-active' : '' ?>">
					<?php if ( $v['form_state'] == 'already_subscribed' ) : ?>
						<div style="left: 7px;top: 7px;position: absolute;">
							<button data-fn="visibility"
									data-id="<?php echo $v['key'] ?>"
									data-state="<?php echo $v['form_state'] ?>"
									data-visible="<?php echo tve_leads_check_variation_visibility( $v ) ? 0 : 1; ?>"
									title="<?php echo __( 'This is the content that displays when a visitor has already subscribed to the form. Click the icon if you would prefer to simply hide the form completely to already subscribed visitors!', 'thrive-leads' ) ?>"
									class="lightbox-step-visibility click btn-icon">

								<?php tcb_icon( tve_leads_check_variation_visibility( $v ) ? 'no-preview' : 'preview2' ) ?>
							</button>
						</div>
					<?php else : ?>
						<button data-fn="duplicate"
								data-id="<?php echo $v['key'] ?>"
								data-state="<?php echo $v['form_state'] ?>"
								title="<?php echo __( 'Duplicate state', 'thrive-leads' ) ?>"
								class="state-clone click btn-icon"><?php tcb_icon( 'duplicate' ) ?></button>
					<?php endif; ?>
					<?php if ( $index > 0 ) : ?>
						<button
								data-fn="remove"
								data-id="<?php echo $v['key'] ?>"
								title="<?php echo __( 'Delete state', 'thrive-leads' ) ?>"
								class="state-delete btn-icon click"><?php tcb_icon( 'trash' ) ?></button>
					<?php endif ?>

					<span class="lightbox-step-name"><?php echo $v['state_name'] ?></span>
				</li>
			<?php endforeach ?>
		</ul>
		<ul class="state-steps">
			<li data-fn="toggle_add"
				class="state-add click">
				<?php tcb_icon( 'plus' ) ?>
				<?php echo __( 'Add new state', 'thrive-leads' ) ?>
				<span class="lightbox-step-add-menu">
						<ul>
							<li><a class="click" data-fn="add" data-state="default"
								   href="javascript:void(0)"><?php echo __( 'New State', 'thrive-leads' ) ?></a></li>
							<li><a class="click" <?php echo ( $already_subscribed ) ? 'data-subscribed="1" ' : '' ?>data-fn="add" data-state="already_subscribed"
								   href="javascript:void(0)"><?php echo __( 'Already Subscribed', 'thrive-leads' ) ?></a></li>
							<?php if ( $parent_form_type != 'lightbox' && $parent_form_type != 'screen_filler' ) : ?>
								<li><a class="click" data-fn="add" data-state="lightbox"
									   href="javascript:void(0)"><?php echo __( 'Lightbox', 'thrive-leads' ) ?></a></li>
							<?php endif ?>
						</ul>
					</span>
			</li>
		</ul>
	</div>
<?php if ( empty( $do_not_wrap ) ) : ?>
	<div class="states-button-container mouseenter mouseleave" data-fn-mouseenter="expand" data-fn-mouseleave="collapse">
		<button class="states-expand">+</button>
		<span class="total_states" style="<?php echo $total_states <= 1 ? 'display: none;' : '' ?>"><?php echo $total_states - 1 ?></span>
	</div>
	</div>
<?php endif ?>