<div id="thrive-api-groups">
	<?php if ( ! empty( $data['groups'] ) ) : ?>
		<div class="tve-sp"></div>
		<h6><?php echo esc_html__( 'Choose your grouping:', 'thrive-dash' ) ?></h6>
		<div class="tve_lightbox_select_holder tve_lightbox_input_inline tve_lightbox_select_inline">
			<select class="tve-api-extra" id="thrive-api-groupin-select" name="mailchimp_groupin">
				<option <?php echo ! isset( $data['groupin'] ) ? 'selected="selected"' : ''; ?> value="0"><?php echo esc_html__( 'No Group', 'thrive-dash' ) ?></option>
				<?php foreach ( $data['groups'] as $groups ) : ?>
					<option <?php echo isset( $data['groupin'] ) && $data['groupin'] == $groups->id ? 'selected="selected"' : ''; ?> value="<?php echo esc_attr( $groups->id ) ?>"><?php echo esc_html( $groups->title ) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php foreach ( $data['groups'] as $groups ) : ?>
			<div class="tve-groups-wrapper tve-groups-select-<?php echo esc_attr( $groups->id ) ?>" <?php echo isset( $data['groupin'] ) && $data['groupin'] == $groups->id ? '' : 'style="display:none"'; ?>>
				<div class="tve-sp"></div>
				<h6><?php echo esc_html__( 'Choose your group:', 'thrive-dash' ) ?></h6>
				<?php if ( $groups->type == 'dropdown' || $groups->type == 'hidden' ) : ?>
					<div class="tve_lightbox_select_holder tve_lightbox_input_inline tve_lightbox_select_inline">
						<select class="thrive-api-group-select <?php echo isset( $data['groupin'] ) && $data['groupin'] == $groups->id ? 'tve-api-extra' : ''; ?>" name="mailchimp_group">
							<?php foreach ( $groups->groups as $group ) : ?>
								<option <?php echo isset( $data['group'] ) && $data['group'] == $group->id ? 'selected="selected"' : ''; ?> value="<?php echo esc_attr( $group->id ) ?>"><?php echo esc_html( $group->name ) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php else : ?>
					<?php foreach ( $groups->groups as $group ) : ?>
						<?php
						$selected_groups = array();
						if ( isset( $data['group'] ) ) {
							$selected_groups = explode( ',', $data['group'] );
						} ?>
						<input style="margin-top: -5px;" id="thrive-group-checkbox-<?php echo esc_attr( $group->id ); ?>" <?php echo in_array( $group->id, $selected_groups ) ? 'checked="checked"' : '' ?> name="mailchimp_group" class="thrive-api-group-select <?php echo isset( $data['groupin'] ) && $data['groupin'] == $groups->id ? 'tve-api-extra' : ''; ?>" type="<?php echo $groups->type == 'checkboxes' ? 'checkbox' : esc_attr( $groups->type ); ?>" name="<?php echo esc_attr( $groups->id ) ?>" value="<?php echo esc_attr( $group->id ) ?>">
						<label class="thrive-api-group-select" for="thrive-group-checkbox-<?php echo esc_attr( $group->id ) ?>">
							<?php echo esc_html( $group->name ); ?>
						</label>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<script type="text/javascript">
	(function ( $ ) {
		$('#thrive-api-groupin-select').change(function(e) {
			var $container = $('#thrive-api-groups'),
				element = 'tve-groups-select-' + e.target.value;

				$container.find('.tve-groups-wrapper').each(function () {
					var $this = $(this);
					$this.hide().find('input[type="radio"], input[type="checkbox"], select').removeClass('tve-api-extra');
					if($this.hasClass(element)) {
						$this.show().find('input[type="radio"], input[type="checkbox"], select').addClass('tve-api-extra')
					}
				})
			});
		})( jQuery );
	</script>


