<?php
$menus = tve_get_custom_menus();

$attributes = array(
	'menu_id'          => isset( $_POST['menu_id'] ) ? sanitize_text_field( $_POST['menu_id'] ) : ( ! empty( $menus[0] ) ? $menus[0]['id'] : 'custom' ),
	'uuid'             => isset( $_POST['uuid'] ) ? sanitize_text_field( $_POST['uuid'] ) : '',
	/* color is not used anymore. kept here for backwards compat */
	'color'            => isset( $_POST['colour'] ) ? sanitize_text_field( $_POST['colour'] ) : '',
	'dir'              => isset( $_POST['dir'] ) ? sanitize_text_field( $_POST['dir'] ) : 'tve_horizontal',
	'font_class'       => isset( $_POST['font_class'] ) ? sanitize_text_field( $_POST['font_class'] ) : '',
	'font_size'        => isset( $_POST['font_size'] ) ? sanitize_text_field( $_POST['font_size'] ) : '',
	'ul_attr'          => isset( $_POST['ul_attr'] ) ? sanitize_text_field( $_POST['ul_attr'] ) : '',
	'link_attr'        => isset( $_POST['link_attr'] ) ? sanitize_text_field( $_POST['link_attr'] ) : '',
	'top_link_attr'    => isset( $_POST['top_link_attr'] ) ? sanitize_text_field( $_POST['top_link_attr'] ) : '',
	'trigger_attr'     => isset( $_POST['trigger_attr'] ) ? sanitize_text_field( $_POST['trigger_attr'] ) : '',
	'primary'          => isset( $_POST['primary'] ) && ( sanitize_text_field( $_POST['primary'] ) == 'true' || sanitize_text_field( $_POST['primary'] ) == '1' ) ? 1 : '',
	'head_css'         => isset( $_POST['head_css'] ) ? $_POST['head_css'] : '', //phpcs:ignore
	'background_hover' => isset( $_POST['background_hover'] ) ? sanitize_text_field( $_POST['background_hover'] ) : '', //phpcs:ignore
	'main_hover'       => isset( $_POST['main_hover'] ) ? sanitize_text_field( $_POST['main_hover'] ) : '', //phpcs:ignore
	'child_hover'      => isset( $_POST['child_hover'] ) ? sanitize_text_field( $_POST['child_hover'] ) : '', //phpcs:ignore
	'dropdown_icon'    => isset( $_POST['dropdown_icon'] ) ? sanitize_text_field( $_POST['dropdown_icon'] ) : 'style_1',
	'mobile_icon'      => isset( $_POST['mobile_icon'] ) ? sanitize_text_field( $_POST['mobile_icon'] ) : '',
	'template'         => isset( $_POST['template'] ) ? sanitize_text_field( $_POST['template'] ) : 'first',
	'template_name'    => isset( $_POST['template_name'] ) ? sanitize_text_field( $_POST['template_name'] ) : 'Basic',
	'unlinked'         => isset( $_POST['unlinked'] ) ? $_POST['unlinked'] : new stdClass(), //phpcs:ignore
	'icon'             => isset( $_POST['icon'] ) ? $_POST['icon'] : new stdClass(), //phpcs:ignore
	'top_cls'          => isset( $_POST['top_cls'] ) ? $_POST['top_cls'] : new stdClass(), //phpcs:ignore
	'type'             => isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '',
	'layout'           => isset( $_POST['layout'] ) ? $_POST['layout'] : [ 'default' => 'grid' ], //phpcs:ignore
	'mega_desc'        => isset( $_POST['mega_desc'] ) ? $_POST['mega_desc'] : [], //phpcs:ignore
	'actions'          => isset( $_POST['actions'] ) ? $_POST['actions'] : new stdClass(), //phpcs:ignore
	'images'           => isset( $_POST['images'] ) ? $_POST['images'] : new stdClass(), //phpcs:ignore
	'img_settings'     => isset( $_POST['img_settings'] ) ? $_POST['img_settings'] : new stdClass(), //phpcs:ignore
	'logo'             => isset( $_POST['logo'] ) && $_POST['logo'] !== 'false' ? $_POST['logo'] : [], //phpcs:ignore
);

if ( ! $attributes['dropdown_icon'] && $attributes['dir'] === 'tve_vertical' ) {
	$icon_styles                 = tcb_elements()->element_factory( 'menu' )->get_icon_styles();
	$styles                      = array_keys( $icon_styles );
	$attributes['dropdown_icon'] = reset( $styles );
}

$attributes['font_class'] .= ( ! empty( $_POST['custom_class'] ) ? ' ' . sanitize_text_field( $_POST['custom_class'] ) : '' );
?>

<div class="thrive-shortcode-config" style="display: none !important"><?php echo '__CONFIG_widget_menu__' . json_encode( array_filter( $attributes ) ) . '__CONFIG_widget_menu__'; ?></div>
<?php echo tve_render_widget_menu( $attributes ); //phpcs:ignore ?>
