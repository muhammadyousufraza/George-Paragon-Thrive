<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<?php /* Handle here also the link from the theme template -> tar and also tar -> theme template*/ ?>
<?php if ( Thrive_Utils::is_theme_template() ) : ?>
	<a href="javascript:void(0)" class="sidebar-item click" data-fn="resetTemplate" data-position="left" data-tooltip="<?php echo __( 'Reset template', 'thrive-theme' ); ?>">
		<?php tcb_icon( 'template-reset', false, 'sidebar', '' ); ?>
	</a>
	<?php if ( apply_filters( 'thrive_theme_allow_architect_switch', thrive_template()->is_singular() ) ) : ?>
		<a href="<?php echo add_query_arg( 'from_theme', true, tcb_get_editor_url( url_to_postid( thrive_template()->url( true ) ) ) ) ?>" class="click sidebar-item mouseenter mouseleave tcb-sidebar-icon-redirect tar-redirect" data-fn="switchClickedFromTheme" data-fn-mouseenter="toggleTooltip" data-fn-mouseleave="toggleTooltip" data-tooltip-type="tar-redirect">
			<?php tcb_icon( 'tar', false, 'sidebar', '' ); ?>
		</a>
	<?php endif; ?>
<?php elseif ( apply_filters( 'thrive_theme_allow_page_edit', ! tve_post_is_landing_page( get_the_ID() ) ) ) : ?>
	<a href="" class="sidebar-item click mouseenter mouseleave tcb-sidebar-icon-redirect theme-template-redirect" data-fn="switchClickedFromArchitect" data-fn-mouseenter="toggleTooltip" data-fn-mouseleave="toggleTooltip" data-tooltip-type="theme-redirect">
		<?php tcb_icon( apply_filters( 'thrive_theme_sidebar_icon_redirect', 'ttb' ), false, 'sidebar', '' ); ?>
	</a>
<?php endif; ?>
