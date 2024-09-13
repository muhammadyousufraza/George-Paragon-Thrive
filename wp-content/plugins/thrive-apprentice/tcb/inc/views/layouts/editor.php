<?php
/**
 * The template for displaying the main editor page
 *
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?><!DOCTYPE html>
<html class="no-js" style="height: 100%;overflow:hidden">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php echo esc_html( get_the_title() . ' | ' . apply_filters( 'tcb_editor_title', __( 'Thrive Architect', 'thrive-cb' ) ) ); ?></title>
	<?php wp_head(); ?>
	<?php do_action( 'tcb_hook_editor_head' ); ?>
	<?php tve_load_global_variables(); ?>
	<?php echo tve_get_shared_styles( '', '300' ); ?>
	<script>
		var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
	</script>
</head>
<body class="tcb-editor-main preview-desktop <?php echo get_post_type() ?>" style="padding: 0;margin: 0;height: 100%;overflow:hidden;">
<div class="tcb-wrap-all" id="tve-main-frame">
	<div id="tve-page-loader" class="tve-open">
		<?php tcb_template( 'loading-spinner.php' ); ?>
	</div>
	<div id="sidebar-top">
		<div id="tcb-sidebar-top-left">
            <?php do_action( 'tve_top_buttons' ); ?>

            <?php if ( tcb_editor()->has_post_breadcrumb_option() ) : ?>
                <?php $post_breadcrumb_data = tcb_editor()->post_breadcrumb_data(); ?>
                <span id="tcb-post-option-breadcrumb" class="tcb-left pr-5 click tcb-active-element-breadcrumbs-item" data-index="" data-selector="<?php echo esc_attr( $post_breadcrumb_data['selector'] ); ?>" data-fn="postOptionsClicked">
				<span class="tcb-breadcrumb-name"><?php echo esc_html( $post_breadcrumb_data['label'] ); ?></span>
				<span class="tcb-icon"><?php tcb_icon( 'cog-regular' ); ?><?php tcb_icon( 'cog-solid' ); ?></span>
				<span class="cont"><i></i></span>
			</span>
            <?php endif; ?>

            <div id="tcb-top-nav-list" class="<?php echo in_array( get_post_type(), [ 'post', 'page' ] ) ? 'tve-has-post-options' : '' ?>"></div>
        </div>
		<!-- Here buttons/images/text will be added dynamically -->
        <div id="tcb-sidebar-top-right">
            <div class="button-group">

			</div>
        </div>
	</div>

	<div class="tcb-relative">
		<div id="tcb-right-drop-panels"></div>
	</div>

	<div class="fr-center-toolbar">
		<div id="main-fr-toolbar" style="display: none">
			<div class="fr-drag"><span></span><span class="r"></span></div>
		</div>
	</div>
	<?php tcb_editor()->render_menu(); ?>
	<?php do_action( 'tcb_editor_iframe_before' ); ?>
	<div id="tcb-frame-container">
		<?php $id = get_the_ID() === absint( $_GET['post'] ) ? get_the_ID() : absint( $_GET['post'] ); //MMM compatibility ?>
		<iframe tabindex="-1" id="tve-editor-frame" data-src="<?php echo esc_attr( tcb_get_editor_url( $id, false ) ); ?>"></iframe>
		<div class="top canvas-border"></div>
		<div class="right canvas-border"></div>
		<div class="bottom canvas-border"></div>
		<div class="left canvas-border"></div>
	</div>
	<?php do_action( 'tcb_editor_iframe_after' ); ?>
	<?php tcb_template( 'sidebar-bottom' ); ?>
	<?php tcb_template( 'sidebar-right' ); ?>
	<div id="main-icons">
		<?php include TVE_TCB_ROOT_PATH . 'editor/css/fonts/control-panel.svg'; ?>
		<svg id="tve-icon-picker" style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1"
			 xmlns="http://www.w3.org/2000/svg">
			<defs></defs>
		</svg>
	</div>
	<div id="inline-drop-panels"></div>
	<div class="fr-center-toolbar bottom" id="edit-mode-tool">
		<div id="tcb-edit-mode-button"></div>
	</div>
</div>
<?php wp_footer(); ?>
<?php do_action( 'admin_print_footer_scripts' ); ?>
<?php do_action( 'tcb_hook_editor_footer' ); ?>
<div style="display: none" id="tve-static-elements">
	<?php echo tcb_editor()->elements->layout(); //phpcs:ignore ?>
</div>
</body>
</html>
