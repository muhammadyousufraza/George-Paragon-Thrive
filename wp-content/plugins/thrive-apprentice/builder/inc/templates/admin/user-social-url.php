<h3> <?php echo __( 'Thrive Social URL Settings', 'thrive-theme' ); ?> </h3>
<table class="form-table" aria-label="Social URL Table">
	<?php $social_urls = get_the_author_meta( THRIVE_SOCIAL_OPTION_NAME, $user->ID ); ?>
	<?php foreach ( Thrive_Defaults::social_labels() as $key => $value ) { ?>
		<tr>
			<th scope="row"><label for=<?php echo $key; ?>> <?php echo $value; ?></label></th>
			<td>
				<input
						type="text"
						name="<?php echo $key; ?>"
						id="<?php echo $key; ?>"
						value="<?php echo isset( $social_urls[ $key ] ) ? $social_urls[ $key ] : ''; ?>"
						class="regular-text"
						placeholder="<?php echo __( 'Add your site URL here.', 'thrive-theme' ); ?>"/>
				<br/>
			</td>
		</tr>
	<?php } ?>
</table>
