<div id="tvo_capture_form_settings" class="tvo-frontend-modal">
	<h2>
		<?php echo __( 'Form Settings', 'thrive-ovation' ) ?>
	</h2>
	<hr class="tve_lightbox_line">

	<table>
		<thead>
		<tr>
			<th><?php echo __( 'Display', 'thrive-ovation' ) ?></th>
			<th><?php echo __( 'Field', 'thrive-ovation' ) ?></th>
			<th><?php echo __( 'Label', 'thrive-ovation' ) ?></th>
			<th><?php echo __( 'Required', 'thrive-ovation' ) ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><?php echo __( 'Always', 'thrive-ovation' ) ?></td>
			<td><?php echo __( 'Name', 'thrive-ovation' ) ?></td>
			<td><input class="tvo_config_field tve_lightbox_input" name="name_label" value="<?php echo __( 'Name', 'thrive-ovation' ); ?>"></td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="name_required" id="name_required">
					<label for="name_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td><?php echo __( 'Always', 'thrive-ovation' ) ?></td>
			<td><?php echo __( 'Email', 'thrive-ovation' ) ?></td>
			<td>
				<input class="tvo_config_field tve_lightbox_input" name="email_label" value="<?php echo __( 'Email', 'thrive-ovation' ) ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="email_required" id="email_required">
					<label for="email_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="role_display" id="role_display">
					<label for="role_display"></label>
				</div>
			</td>
			<td>
				<label for="role_display"><?php echo __( 'Role/Description', 'thrive-ovation' ) ?></label>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input" name="role_label" value="<?php echo __( 'Role', 'thrive-ovation' ) ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="role_required" id="role_required">
					<label for="role_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="website_url_display" id="website_url_display">
					<label for="website_url_display"></label>
				</div>
			</td>
			<td>
				<?php echo __( 'Website URL', 'thrive-ovation' ) ?>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input tvo_website_url" type="text" name="website_url_label" value="<?php echo __( 'Website URL', 'thrive-ovation' ); ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="website_url_required" id="website_url_required">
					<label for="website_url_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="title_display" id="title_display">
					<label for="title_display"></label>
				</div>
			</td>
			<td>
				<?php echo __( 'Title', 'thrive-ovation' ) ?>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input" type="text" name="title_label">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="title_required" id="title_required">
					<label for="title_required"></label>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<div id="tvo-questions-list">

		<div class="tvo-add-question">
			<div class="tvo-card tvo-card-xsmall">
				<div class="tvo-card-content">
					<span class="tvo-f-icon-add tvo-add-testimonial"></span>
					<?php echo __( 'Add New Question', 'thrive-ovation' ) ?>
				</div>
			</div>
		</div>
	</div>
	<br>
	<h5>
		<?php echo __( 'Other settings', 'thrive-ovation' ) ?>
	</h5>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icon-image tvo-margin-right-small"></span>
				<?php echo __( 'Include image field', 'thrive-ovation' ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6">
			<div class="tve_lightbox_input_holder tve_lightbox_no_label">
				<label class="tvo_switch">
					<input id="tvo_dispaly_image" name="image_display" class="tvo_config_field" type="checkbox">
					<span></span>
				</label>
			</div>
		</div>
	</div>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icon-check tvo-text-green tvo-margin-right-small"></span>
				<?php echo __( 'On successful message', 'thrive-ovation' ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6 pl-0">
			<div class="tve_lightbox_select_holder">
				<select class="tvo_config_field" name="on_success_option" data-message="<?php echo __( 'Thanks for submitting your testimonial.', 'thrive-ovation' ); ?>" data-redirect="http://">
					<option value="message"><?php echo __( 'Show success message', 'thrive-ovation' ) ?></option>
					<option value="redirect"><?php echo __( 'Redirect to url', 'thrive-ovation' ) ?></option>
				</select>
			</div>
			<div>
				<input class="tvo_config_field tve_lightbox_input" type="text" name="on_success"
					   value="<?php echo __( 'Thanks for submitting your testimonial.', 'thrive-ovation' ); ?>">
			</div>
		</div>
	</div>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icon-chat tvo-text-blue tvo-margin-right-small"></span>
				<?php echo __( 'Submit button text', 'thrive-ovation' ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6">
			<input class="tvo_config_field tve_lightbox_input" type="text" name="button_text" value="<?php echo __( 'Submit', 'thrive-ovation' ); ?>">
		</div>
	</div>
	<br>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icoan-tags tvo-orange-text tvo-margin-right-small"></span>
				<?php echo __( 'Add tags (optional)', 'thrive-ovation' ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6">
			<select class="tvo_config_field tve_lightbox_input tvo-all-tags" type="text" name="tags"></select>
		</div>
	</div>

	<?php
	$captcha_api       = Thrive_Dash_List_Manager::credentials( 'recaptcha' );
	$captcha_available = ! empty( $captcha_api['site_key'] );
	?>

	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s12">
			<p>
				<input class="tvo_config_field" type="checkbox" name="reCaptcha_option" id="tvo_activate_reCaptcha" <?php echo $captcha_available ? '' : ' disabled'; ?>>
				<?php echo __( 'Activate reCaptcha ', 'thrive-ovation' ) ?> </p>

			<label for="reCaptcha_option">
				<?php if ( ! $captcha_available ) : ?>
					<a target="_blank" href= <?php echo admin_url( 'admin.php?page=tve_dash_api_connect' ) ?>> <?php echo __( 'Requires integration with Google ReCaptcha', 'thrive-ovation' ) ?> </a>
				<?php else : ?>
					<p> <?php echo __( 'Please note that only one reCaptcha may be active on a page!', 'thrive-ovation' ) ?></p>
				<?php endif; ?>
			</label>
		</div>
	</div>

	<button class="tve-button green tve_editor_button tve_editor_button_success click tvd-right" data-fn="save">
		<?php echo __( 'Save', 'thrive-ovation' ); ?>
	</button>
</div>

<script type="text/javascript">
	window.addEventListener( 'load', function () {
		jQuery( '#tvo_capture_form_settings' ).find( '.tvo-all-tags' ).select2( {
			multiple: true,
			tags: true,
			data: TVO_Front.all_tags
		} ).on( 'select2:select', function ( e ) {
			if ( e.params.data.text != e.params.data.id ) {
				return;
			}

			var $select = jQuery( this );

			jQuery.ajax( {
				headers: {
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				},
				cache: false,
				url: '<?php echo tvo_get_route_url( 'tags' ); ?>',
				type: 'POST',
				data: {'name': e.params.data.text}
			} ).done( function ( response ) {
				TVO_Front.all_tags.push( {
					id: response.tag.term_id,
					text: response.tag.name
				} );
				$select.find( 'option[value="' + e.params.data.text + '"]' ).remove();
				$select.append( '<option value="' + response.tag.term_id + '" selected>' + response.tag.name + '</option>' ).trigger( 'change' );
			} );
		} );

		jQuery( document ).on( 'click', '.tvo-add-question', function ( e ) {

			var $target = jQuery( e.target );

			if ( ! $target.hasClass( 'tvo-add-question' ) ) {
				$target = $target.parents( '.tvo-add-question' );
			}

			var $q = jQuery( '<div>', {
				class: 'tvo-row tvo-collapse tvo-question',
				html: $target.siblings( '.tvo-default-question' ).html()
			} ).insertBefore( $target );

			$q.find( '.tvo-question-input' ).val( 'Enter your question here' );
			$q.find( '.tvo-placeholder-input' ).attr( 'placeholder', 'Enter your placeholder text here' ).val( '' );
			$q.find( '.tvo-show-placeholder' ).show();
			$q.find( '.tvo-placeholder' ).hide();

			tvo_question_number();
		} );

		jQuery( document ).on( 'click', '.tvo-remove-question', function () {
			jQuery( this ).parents( '.tvo-question' ).remove();
			tvo_question_number();
		} ).on( 'click', '.tvo-show-placeholder', function () {
			var $this = jQuery( this );
			$this.parents( '.tvo-question' ).find( '.tvo-placeholder' ).show();
			$this.hide();
		} ).on( 'click', '.tvo-remove-placeholder', function () {
			var $q = jQuery( this ).parents( '.tvo-question' );
			$q.find( '.tvo-placeholder' ).hide();
			$q.find( '.tvo-show-placeholder' ).show();
			$q.find( '.tvo-placeholder-input' ).val( '' );
		} );

		jQuery( '.tvo_config_field[name="on_success_option"]' ).change( function () {
			jQuery( '.tvo_config_field[name="on_success"]' ).val( this.getAttribute( 'data-' + this.value ) );
		} );

		function tvo_question_number() {
			jQuery( document ).find('.tvo-question').each( function ( index ) {
				var $this = jQuery( this );
				$this.find( '.tvo-q-label' ).html( 'Question ' + (index + 1) );
				$this.find( '.tvo-p-label' ).html( 'Placeholder ' + (index + 1) );
				$this.find( '.tvo-required' ).attr( 'id', 'tvo_question_req_' + index );
				$this.find( 'label' ).attr( 'for', 'tvo_question_req_' + index );
			} );
		}
	} );
</script>
