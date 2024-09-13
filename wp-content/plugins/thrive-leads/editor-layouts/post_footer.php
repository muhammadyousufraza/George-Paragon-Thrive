<?php global $variation;
if ( empty( $is_ajax_render ) ) : /** if AJAX-rendering the contents, we need to only output the html part, and do not include any of the custom css / fonts etc needed - used in the state manager */ ?>
	<?php do_action( 'get_header' ) ?><!DOCTYPE html>
	<!--[if IE 7]>
	<html class="ie ie7" <?php language_attributes(); ?>>
	<![endif]-->
	<!--[if IE 8]>
	<html class="ie ie8" <?php language_attributes(); ?>>
	<![endif]-->
	<!--[if !(IE 7) | !(IE 8)  ]><!-->
	<html <?php language_attributes(); ?>>
	<!--<![endif]-->
	<?php
	include plugin_dir_path( __FILE__ ) . 'head.php';
	$form_template           = empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ? '' : $variation[ TVE_LEADS_FIELD_TEMPLATE ];
	$state_manager_collapsed = ! empty( $_COOKIE['tve_leads_state_collapse'] );
	?>
	<body <?php body_class( ( $state_manager_collapsed ? 'tl-state-collapse ' : '' ) . 'tve_gray_bg ' . preg_replace( '#(.+?)\|(.+)#', '$2', $form_template ) ) ?>>
	<div class="cnt bSe">
	<article>
<?php endif ?>


<?php
$key       = '';
$hide_form = tve_leads_check_variation_visibility( $variation );
if ( ! empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
	list( $type, $key ) = explode( '|', $variation[ TVE_LEADS_FIELD_TEMPLATE ] );
	$key = preg_replace( '#_v(.+)$#', '', $key );
}
?>
	<div class="tve-leads-conversion-object">
		<div id="tve-leads-editor-replace">
			<?php include dirname( __FILE__ ) . '/_no_form.php'; ?>
			<div class="tve-leads-post-footer tve-leads-shortcode" <?php if ( $hide_form ): ?>style="display: none;"<?php endif; ?>>
				<div class="tl-style" id="tve_<?php echo $key ?>" data-state="<?php echo $variation['key'] ?>">
					<?php echo apply_filters( 'tve_editor_custom_content', null ) ?>
				</div>
				<?php echo apply_filters( 'tve_leads_variation_append_states', '', $variation ); ?>


				<div class="tve-leads-template-description" style="opacity: .6; margin-top: 240px;text-align: center; <?php if ( $hide_form ): ?>display: none;<?php endif; ?>">
					<h4><?php echo __( 'This is a Form Design type called "Post footer".', 'thrive-leads' ) ?></h4>
					<h4><?php echo __( 'It is an Opt-In form that appears at the end of a post.', 'thrive-leads' ) ?></h4>
					<h4><?php echo __( "Note that this form doesn't have any width settings. It will expand to the full width of the content area of your theme", 'thrive-leads' ) ?></h4>
				</div>
			</div>
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
	</body>
	</html>
<?php endif ?>
