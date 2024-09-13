<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$post_type = get_post_type();
$object    = get_post_type_object( $post_type );
$name      = $object->labels->singular_name;
?>

<div id="tve-post-type-template-settings-component" class="tve-component" data-view="PostTypeTemplateSettings">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo Thrive_Utils::get_post_type_name() . ' ' . __( 'Template Settings', 'thrive-theme' ) ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content mb-5">
		<div class="mb-5">
			<?php echo sprintf( __( 'Currently Active Template', 'thrive-theme' ) ); ?>
		</div>
		<div class="thrive-page-active-template"></div>
		<div class="mt-20 mb-5">
			<?php echo __( 'Load Template', 'thrive-theme' ) ?>
		</div>
		<div class="tve-search-filter-container"></div>
		<div class="thrive-templates-container"></div>
		<?php if ( $post_type === 'post' ) { ?>
			<?php echo Thrive_Utils::return_part( '/integrations/architect/views/backbone/theme-main/template-editor-notice.php' ); ?>
		<?php } ?>
		<div class="thrive-no-post-type-templates" style="display: none">
			<div class="center-lg mb-5"><strong><?php echo sprintf( __( 'No %s Templates Found', 'thrive-theme' ), $name ); ?></strong></div>
			<div class="mb-5"><?php echo sprintf( __( "We're displaying a list of post templates above because no '%s' templates have been found.", 'thrive-theme' ), $name ); ?></div>
			<div><?php echo sprintf( __( "If you'd like to design a specific template for your '%s' content, you can do so from %s.", 'thrive-theme' ),
					$name,
					'<a target="_blank" href="' . admin_url( 'admin.php?page=' . THRIVE_MENU_SLUG . '#templates' ) . '">template editor</a>.'
				); ?></div>
		</div>
	</div>
</div>
