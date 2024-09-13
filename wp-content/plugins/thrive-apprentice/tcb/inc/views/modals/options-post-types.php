<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

/* define a list of post types that the user can choose from */
$all_post_types = apply_filters( 'tve_link_autocomplete_post_types', get_post_types( [
	'public'  => true,
	'show_ui' => true,
] ) );

$blacklist = apply_filters( 'tve_post_types_blacklist', [ 'tcb_lightbox', TCB_Symbols_Post_Type::SYMBOL_POST_TYPE ] );
$saved     = maybe_unserialize( get_option( 'tve_hyperlink_settings', apply_filters( 'tve_link_autocomplete_default_post_types', [ 'post', 'page' ] ) ) ); // by default, show posts and pages
if ( is_string( $saved ) ) {
	$saved = [ $saved ];
}

$all_post_types = array_diff( $all_post_types, $blacklist ); ?>
<h2 class="tcb-modal-title"><?php esc_html_e( 'Thrive Hyperlink Settings', 'thrive-cb' ) ?></h2>
<p class="tcb-modal-description"><?php echo esc_html__( 'Select the content to be included in search results.', 'thrive-cb' ) ?></p>
<div class="inline-checkboxes row">
	<?php foreach ( $all_post_types as $i => $post_type ) : $info = get_post_type_object( $post_type ) ?>
		<div class="col col-xs-4">
			<label for="tcb-post-type-<?php echo esc_attr( $i ) ?>" class="tcb-checkbox tcb-truncate" title="<?php echo esc_attr( $info->labels->menu_name ) ?>">
				<input type="checkbox" class="post-type" name="post_types[]" id="tcb-post-type-<?php echo esc_attr( $i ); ?>"<?php checked( in_array( $post_type, $saved ) ) ?>
					   value="<?php echo esc_attr( $post_type ) ?>">
				<span><?php echo esc_html( $info->labels->menu_name ); ?></span>
			</label>
		</div>
	<?php endforeach ?>
</div>

<div class="tcb-modal-footer pt-40">
	<button type="button" class="tcb-modal-save tcb-right tve-button medium green"><?php echo esc_html__( 'Continue', 'thrive-cb' ) ?></button>
</div>
