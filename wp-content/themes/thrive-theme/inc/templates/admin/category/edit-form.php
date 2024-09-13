<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$landing_page_id = thrive_category( $term->term_id )->get_redirect_page_id();
?>
<tr class="form-field">
	<th scope="row">
		<label for="<?php echo Thrive_Category::PAGE_REDIRECT; ?>"><?php echo __( 'Redirect Category to a Page', 'thrive-theme' ) ?></label>
	</th>
	<td>
		<select name="<?php echo Thrive_Category::PAGE_REDIRECT; ?>">
			<option value="0"><?php echo __( 'None', 'thrive-theme' ); ?></option>
			<?php foreach ( get_pages() as $page ): ?>
				<option value="<?php echo $page->ID; ?>" <?php echo ( $page->ID === (int) $landing_page_id ) ? 'selected' : ''; ?>><?php echo get_the_title( $page->ID ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php echo __( 'If set you can replace the WordPress category page with your own highly optimised landing page', 'thrive-theme' ); ?></p>
	</td>
</tr>
