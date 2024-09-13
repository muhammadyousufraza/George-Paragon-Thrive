<?php global $variation;
if ( empty( $is_ajax_render ) ) : /** if AJAX-rendering the contents, we need to only output the html part, and do not include any of the custom css / fonts etc needed - used in the state manager */ ?>
	<?php do_action( 'get_header' ) ?><!DOCTYPE html>
	<!--[if IE 7]>
	<html class="ie ie7" <?php language_attributes(); ?> style="margin-top: 0 !important;height:100%">
	<![endif]-->
	<!--[if IE 8]>
	<html class="ie ie8" <?php language_attributes(); ?> style="margin-top: 0 !important;height:100%">
	<![endif]-->
	<!--[if !(IE 7) | !(IE 8)  ]><!-->
	<html <?php language_attributes(); ?> style="margin-top: 0 !important;height:100%">
	<!--<![endif]-->
	<?php include plugin_dir_path( __FILE__ ) . 'head.php' ?>
	<?php $state_manager_collapsed = ! empty( $_COOKIE['tve_leads_state_collapse'] ); ?>
	<body <?php body_class( 'tve-l-open tve-o-hidden tve-lightbox-page' . ( $state_manager_collapsed ? ' tl-state-collapse' : '' ) ) ?>>
	<div class="cnt bSe">
	<article>
<?php endif; ?>
<?php
$hide_form = tve_leads_check_variation_visibility( $variation );
$config    = tve_leads_lightbox_globals( $variation );
list( $type, $_key ) = array( '', '' );
if ( ! empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
	list( $type, $_key ) = explode( '|', $variation[ TVE_LEADS_FIELD_TEMPLATE ] );
} ?>
	<div class="tve-leads-conversion-object">
		<div id="tve-leads-editor-replace" class="tl-states-root">
			<?php include dirname( __FILE__ ) . '/_no_form.php'; ?>
			<?php
			if ( ! empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) :
				list( $type, $key ) = explode( '|', $variation[ TVE_LEADS_FIELD_TEMPLATE ] );
				$key = preg_replace( '#_v(.+)$#', '', $key );
				?>
				<div class="tl-style" id="tve_<?php echo $key ?>" data-state="<?php echo $variation['key'] ?>" <?php if ( $hide_form ): ?>style="display: none;"<?php endif; ?>>
					<div class="tve-leads-lightbox tve_post_lightbox tve_<?php echo $_key ?>">
						<div class="tve_p_lb_background tl-lb-target tve-scroll <?php if ( is_editor_page() )
							echo 'tve_lb_open' ?>" id="open-me">
							<div class="tve_p_lb_overlay main-lb-overlay"
								 style="<?php echo $config['overlay']['css'] ?>"<?php echo $config['overlay']['custom_color'] ?>></div>
							<div class="tve_p_lb_content tve_editable<?php echo $config['content']['class'] ?>"
								 style="<?php echo $config['content']['css'] ?>"<?php echo $config['content']['custom_color'] ?>>
								<div class="tve_p_lb_inner" id="tve-p-scroller" style="<?php echo $config['inner']['css'] ?>">
									<?php echo apply_filters( 'tve_editor_custom_content', null ) ?>
								</div>
								<?php if ( ! tve_leads_is_v2_template( $_key ) ) : ?>
									<a href="javascript:void(0)" class="tve_p_lb_close<?php echo $config['close']['class'] ?>"
									   style="<?php echo $config['close']['css'] ?>"<?php echo $config['close']['custom_color'] ?>
									   title="Close">x</a>
								<?php endif ?>
							</div>
							<div class="tve-spacer"></div>
						</div>
					</div>
				</div>
				<?php echo apply_filters( 'tve_leads_variation_append_states', '', $variation ); ?>
			<?php endif ?>
		</div>
	</div>

<?php if ( empty( $is_ajax_render ) ) : ?>
	</article>
	</div>
	<div id="tve_page_loader" class="tve_page_loader">
		<div class="tve_loader_inner"><img src="<?php echo tve_editor_css() ?>/images/loader.gif" alt=""/></div>
	</div>

<?php do_action( 'get_footer' ) ?>
<?php wp_footer() ?>

<?php if ( ! is_editor_page() ) : ?>
	<script type="text/javascript">
		jQuery( document ).ready( function () {
			/* trigger lightbox opening */
			window.TL_Front && TL_Front.open_lightbox( jQuery( '#open-me' ) );
		} );
	</script>
<?php else : ?>
	<script type="text/javascript">
		jQuery( document ).ready( function () {
			jQuery( '.tve_p_lb_control' ).removeClass( 'tve_editable' );
		} );
	</script>
<?php endif ?>
	</body>
	</html>
<?php endif ?>