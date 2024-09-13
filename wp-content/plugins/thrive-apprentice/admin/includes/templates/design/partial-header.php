<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$index_page = tva_get_settings_manager()->factory( 'index_page' )->get_value();
?>

<h1 class="tva-options-heading click" data-fn="modifySettingsView" data-state="design-legacy">
	<i><?php tva_get_svg_icon( 'long-arrow-left_light' ); ?></i>
	<?php echo __( 'Template settings', 'thrive-apprentice' ); ?>
</h1>

<div class="tva-flex tva-options-main-buttons">
	<button class="click tva-opts-btn left" <?php echo ( $config['disable_submenu_item'] !== 'advanced' ) ? 'data-fn="modifySettingsView" data-state="design-legacy/advanced"' : 'data-active="1"' ?>>
		<?php echo __( 'Course overview', 'thrive-apprentice' ); ?>
	</button>
	<# if(!!TVA.indexPageModel.get('value') === false){ #>
		<button class="tva-opts-btn right tvd-tooltipped" style="opacity: .4" data-position="right" data-tooltip="<?php echo esc_attr( __( 'You need to define a course page in order to style the Course Layout', 'thrive-apprentice' ) ); ?>">
			<?php echo __( 'Course layout', 'thrive-apprentice' ); ?>
		</button>
	<# }else{ #>
		<button class="click tva-opts-btn right" <?php echo ( $config['disable_submenu_item'] !== 'index' ) ? 'data-fn="modifySettingsView" data-state="design-legacy/index"' : 'data-active="1"' ?>>
			<?php echo __( 'Course layout', 'thrive-apprentice' ); ?>
		</button>
	<# } #>
</div>
