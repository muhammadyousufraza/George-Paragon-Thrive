<div id="tve-layout-component" class="tve-component" data-view="Layout"></div>

<div id="tve-responsive-component" class="tve-component" data-view="Responsive">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Responsive', 'thrive-cb' ) ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control no-space" data-view="Devices" data-extends="ButtonGroup" data-required="1" data-name="<?php _e( 'Visible on', 'thrive-cb' ); ?>"
			 data-checkbox="true"></div>
		<hr>
		<div class="tve-control" data-extends="Switch" data-view="ShowAllHidden" data-label="<?php _e( 'Show all hidden elements', 'thrive-cb' ); ?>"></div>
	</div>
</div>

<div id="tve-conditional-display-component" class="tve-component" data-view="ConditionalDisplay">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Conditional Display', 'thrive-cb' ) ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="display-order">
			<?php echo esc_html__( 'Display order', 'thrive-cb' ) ?>
		</div>
		<div class="display-list">
			<div class="custom-display-list"></div>
			<div class="default-display-list"></div>
		</div>
		<button class="tve-button green ghost long click mt-10" data-fn="addDisplay">
			<?php tcb_icon( 'plus-regular' ); ?>
			<?php echo esc_html__( 'Add display', 'thrive-cb' ) ?>
		</button>
		<div class="tve-advanced-controls mt-20 hide-states">
			<div class="dropdown-header with-info" data-prop="advanced">
				<div class="dropdown-info">
					<span class="mr-10"><?php echo esc_html__( 'Advanced', 'thrive-cb' ); ?></span>
					<span class="click tve-lazy-load-info" data-fn="openTooltip"><?php tcb_icon( 'info-circle-solid' ); ?></span>
				</div>
			</div>

			<div class="dropdown-content pt-0">
				<div class="hide-states">
					<div class="tve-control no-space" data-view="LazyLoad"></div>
					<div class="tve-cond-lazy-load-settings">
						<div class="tve-control no-space" data-view="UniformHeights"></div>
						<div class="tve-control full-width" data-view="InheritBackground"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="tve-carousel-component" class="tve-component" data-view="Carousel">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Carousel Options', 'thrive-cb' ) ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control sep-bottom mb-10 pb-0" data-view="MovementSettings"></div>
		<div class="tve-control" data-view="SlidesToScroll"></div>
		<div class="tve-control" data-view="SlidesToShow"></div>
		<div class="tve-control" data-view="Autoplay"></div>
		<div class="tve-autoplay-controls sep-bottom">
			<div class="tve-control" data-view="AutoplaySpeed"></div>
			<div class="tve-control" data-view="PauseOn"></div>
		</div>
		<div class="tve-control" data-view="CenterMode"></div>
		<div class="tve-control" data-view="CenterPadding"></div>
		<div class="tve-control" data-view="AdaptiveHeight"></div>
		<div class="tve-control" data-view="UniformSlidesHeight"></div>
		<div class="tve-control" data-view="VerticalPosition"></div>
		<div class="tve-control" data-view="Fade"></div>
		<div class="tve-control" data-view="FadeImageWidth"></div>
	</div>
</div>

<div id="tve-styles-templates-component" class="tve-component" data-view="StylesTemplates">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'HTML Attributes', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-input="class" data-key="Class" data-extends="LabelInput" data-label="<?php echo esc_html__( 'Class', 'thrive-cb' ); ?>"></div>
		<div class="tve-control" data-input="id" data-key="ID" data-extends="LabelInput" data-label="<?php echo esc_html__( 'ID', 'thrive-cb' ); ?>"></div>
	</div>
</div>

<div id="tve-background-component" class="tve-component" data-view="Background">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo esc_html__( 'Background Style', 'thrive-cb' ); ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="gradient-layers"></div>
		<div class="tve-control" data-key="PreviewFilterList" data-view="PreviewList"></div>
		<div class="tve-control bg-icons" data-view="PreviewList"></div>
		<div class="v-sep"></div>
		<div class="tve-control" data-view="ColorPicker" data-show-gradient="0"></div>
		<div class="tve-control video-bg white" data-label="<?php echo esc_attr__( 'Video background', 'thrive-cb' ); ?>" data-key="video" data-initializer="video"></div>
	</div>
</div>

<div id="tve-typography-component" class="tve-component" data-view="Typography">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo esc_html__( 'Typography', 'thrive-cb' ); ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control hide-states" data-view="ParagraphStyle"></div>
		<div class="tve-control hide-states" data-key="ParagraphStylePicker" data-initializer="paragraphStylePicker"></div>
		<div class="tve-control hide-states" data-view="FontFace"></div>
		<div class="tve-control" data-view="FontColor"></div>
		<div class="tve-control hide-states" data-view="TextTransform"></div>
		<div class="tve-control hide-states" data-view="TextAlign"></div>
		<div class="tve-control" data-view="TextStyle"></div>
		<div class="btn-group-light typography-button-toggle-controls">
			<div class="tve-control hide-states" data-view="ToggleControls"></div>

			<div class="tve-control hide-states tcb-typography-toggle-element tcb-typography-font-size" data-view="FontSize"></div>
			<div class="tve-control hide-states tcb-typography-toggle-element tcb-typography-line-height" data-view="LineHeight"></div>
			<div class="tve-control hide-states tcb-typography-toggle-element tcb-typography-letter-spacing pb-10" data-view="LetterSpacing"></div>
		</div>
		<hr>
		<div class="tcb-text-center clear-formatting mt-10">
			<span class="click tcb-text-uppercase clear-format custom-icon" data-fn="clear_formatting">
				<?php echo esc_html__( 'Clear element formatting', 'thrive-cb' ); ?>
			</span>
		</div>

		<div class="tve-advanced-controls hide-states">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo esc_html__( 'Advanced', 'thrive-cb' ); ?>
				</span>
			</div>

			<div class="dropdown-content pt-0">
				<div class="hide-states">
					<div class="tve-control" data-view="Slider" data-key="p_spacing"></div>
					<div class="tve-control" data-view="Slider" data-key="h1_spacing"></div>
					<div class="tve-control" data-view="Slider" data-key="h2_spacing"></div>
					<div class="tve-control" data-view="Slider" data-key="h3_spacing"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="tve-borders-component" class="tve-component" data-view="Borders">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Borders & Corners', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">

			<div class="tve-control" data-view="Borders"></div>
			<hr>
			<div class="tve-control" data-view="Corners"></div>

		</div>
	</div>
</div>

<div id="tve-animation-component" class="tve-component" data-view="Animation">
	<?php $tabs = tcb_get_editor_actions(); ?>
	<div class="animation-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php esc_html_e( 'Animation & Action', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-animation control-grid no-space button-group-holder">
				<div class="group-label label">
					<?php esc_html_e( 'Add new:', 'thrive-cb' ) ?>
				</div>
				<div class="tve-btn-group grey input" id="tcb-anim-buttons">
					<?php foreach ( $tabs as $key => $tab ) : ?>
						<div class="btn-inline tve-btn click anim-<?php echo esc_attr( $key ) ?>" data-fn="tab_click"
							 data-value="<?php echo esc_attr( $key ) ?>"
							 data-tooltip="<?php echo esc_attr( $tab['title'] ) ?>" data-side="top">
							<?php tcb_icon( $tab['icon'] ) ?>
						</div>
					<?php endforeach ?>
				</div>
			</div>
			<div class="actions-holder">
				<?php foreach ( $tabs as $key => $tab ) : ?>
					<div class="action-tab action-<?php echo esc_attr( $key ) ?>" style="display: none"
						 data-tab="<?php echo esc_attr( $key ) ?>">
						<?php /* special case for links */ ?>
						<?php if ( $key == 'link' || isset( $tab['instance'] ) ) : ?>
							<div class="action-settings"
								 data-action="<?php echo isset( $tab['instance'] ) ? esc_attr( $tab['instance']->get_key() ) : 'link' ?>"
								 data-view="<?php echo isset( $tab['instance'] ) ? esc_attr( $tab['instance']->get_editor_js_view() ) : 'Link' ?>">
								<?php isset( $tab['instance'] ) ? $tab['instance']->render_editor_settings() : tcb_template( 'actions/link' ) ?>
							</div>
						<?php elseif ( isset( $tab['actions'] ) ) : ?>
							<?php if ( $key === 'popup' ) : ?>
								<div class="control-grid animation-popup-trigger">
									<label for="a-popup-trigger"><?php echo esc_html__( 'Trigger', 'thrive-cb' ) ?></label>
									<select id="a-popup-trigger">
										<option value="click" selected><?php echo esc_html__( 'Click', 'thrive-cb' ) ?></option>
										<option
												value="tve-viewport"><?php echo esc_html__( 'Comes into viewport', 'thrive-cb' ) ?></option>
									</select>
								</div>
							<?php endif ?>
							<hr class="animation-popup-trigger">
							<div class="action-collection">
								<?php
								$auto_select = count( $tab['actions'] ) == 1 ? 'checked="checked"' : '';
								foreach ( $tab['actions'] as $action ) : ?>
									<div class="action-item">
										<label class="tcb-radio">
											<input
													name="action_group_<?php echo esc_attr( $key ) ?>" <?php echo $auto_select ?>
													type="radio"
													class="action-chooser change"
													data-fn="action_select"
													value="<?php echo esc_attr( $action['instance']->get_key() ) ?>">
											<span><?php echo esc_html( $action['instance']->getName() ) ?></span>
										</label>
										<div class="action-settings" style="display: none"
											 data-action="<?php echo esc_attr( $action['instance']->get_key() ) ?>"
											 data-view="<?php echo esc_attr( $action['instance']->get_editor_js_view() ) ?>">
											<?php $action['instance']->render_editor_settings() ?>
										</div>
									</div>
								<?php endforeach ?>
							</div>
						<?php endif ?>
					</div>
				<?php endforeach ?>
			</div>
			<div id="tcb-anim-list" class="tcb-relative"></div>
		</div>
	</div>
</div>

<div id="tve-shadow-component" class="tve-component" data-view="Shadow">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Shadow', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-shadow no-space" id="tcb-shadow-buttons"></div>
			<div id="tcb-text-shadow-list" class="tcb-relative no-space tcb-preview-list" data-shadow-type="text-shadow"></div>
			<div id="tcb-box-shadow-list" class="tcb-relative no-space tcb-preview-list" data-shadow-type="box-shadow"></div>
		</div>
	</div>

</div>

<div id="tve-cloud-templates-component" data-key="cloud_templates" class="tve-component dynamic-component"
	 style="order: 5;" data-view="CloudTemplates">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo esc_html__( 'Template Options', 'thrive-cb' ); ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<span class="template-name">
			<?php tcb_icon( 'template-picker' ); ?>
			<div class="tve-control" data-view="ModalPicker"></div>
		</span>
		<?php tcb_icon( 'external-link-square-light' ); ?>
	</div>
</div>

<div id="tve-multiple-select-mode-component" class="tve-component" data-view="MultipleSelectMode">
	<div class="dropdown-header open" data-prop="docked">
		<div class="group-description"><?php echo esc_html__( 'Currently selected', 'thrive-cb' ); ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-multiple-select">
			<div class="tve-control fill no-space" data-view="selectedElementsType"></div>

			<div class="tve-multi-select-notice tve-same-type info-text dark-orange mt-5" data-type="all" style="display: none">
				<?php echo esc_html__( 'Your selection is a combination of item types. Only elements of the same type can be styled together. Please choose the items type', 'thrive-cb' ); ?>
			</div>

			<div class="tve-multi-select-notice tve-different-type info-text yellow mt-5" data-type="element" data-type="all" style="display:none">
				<strong>
					<?php echo esc_html__( 'Selected type is ', 'thrive-cb' ); ?>
					<span class="tve-multiple-select-element-type-notice">
						<?php echo esc_html__( 'Text', 'thrive-cb' ); ?>
					</span>
				</strong>
				<br>
				<?php echo esc_html__( 'Please select items of this type only', 'thrive-cb' ); ?>
			</div>
		</div>
		<div class="tcb-text-center sep-top exit-group-styling">
			<a href="javascript:void(0);" class="click clear-format"
			   data-fn="exit_multiple_selected_mode"><?php echo esc_html__( 'Exit Group Styling', 'thrive-cb' ); ?></a>
		</div>
	</div>
</div>

<div id="tve-group-component" class="tve-component" data-view="Group">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo esc_html__( 'Currently styling', 'thrive-cb' ); ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="control-grid">
			<div class="tve-control fill no-space" data-view="preview"></div>
			<div class="tve-control pl-10" data-view="ButtonToggle"></div>
		</div>
		<div class="tcb-text-center sep-top exit-group-styling">
			<a href="javascript:void(0);" class="click clear-format"
			   data-fn="close_group_options"><?php tcb_icon( 'exit-to-app' ); ?><?php echo esc_html__( 'Exit Group Styling', 'thrive-cb' ); ?></a>
		</div>

	</div>
</div>

<div id="tve-scroll-component" class="tve-component" data-view="Scroll">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo esc_html__( 'Scroll Behavior', 'thrive-cb' ); ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control no-space" data-extends="ButtonGroup" data-key="toggle" data-name="<?php esc_attr_e( 'Behavior on Scroll', 'thrive-cb' ); ?>"></div>

		<div class="tve-control padding-top-10" data-extends="ButtonGroup" data-key="Devices" data-required="1" data-name="<?php esc_attr_e( 'Visible on', 'thrive-cb' ); ?>" data-checkbox="true"></div>
		<div class="if-parallax">
			<div class="tve-control" data-key="ParallaxEffectsControl" data-initializer="initParallaxEffects"></div>
			<div id="add-parallax-effect" class="tve-button click" data-fn="addNewParallax"><?php echo esc_html__( 'Add New Effect', 'thrive-cb' ); ?></div>
			<div class="tve-control mt-5" data-key="ParallaxPreview" data-extends="Switch" data-label="<?php esc_attr_e( 'Parallax Preview', 'thrive-cb' ); ?>"></div>
		</div>
		<div class="if-sticky">
			<div class="tve-control mb-10" data-extends="ButtonGroup" data-key="stickyPosition" data-name="<?php esc_attr_e( 'Stick to screen', 'thrive-cb' ); ?>"></div>
			<div class="tve-control" data-extends="Slider" data-key="top"
				 data-label="<?php echo esc_html__( 'Distance from the top of the screen', 'thrive-cb' ); ?>"></div>
			<div class="tve-control no-space" data-extends="Select" data-key="until" data-label="<?php echo esc_html__( 'Sticky until', 'thrive-cb' ); ?>"></div>
			<div class="tve-control no-space if-until-element padding-top-10" data-extends="LabelInput" data-key="element_id"
				 data-label="<?php echo esc_html__( 'Element ID', 'thrive-cb' ); ?>"></div>
			<div class="info-text if-until-element"><?php echo esc_html__( 'You can set the ID of an element from the "HTML Attributes" section', 'thrive-cb' ); ?></div>
			<div class="tve-control mt-10" data-extends="Switch" data-key="switchOnScroll" data-label="<?php echo esc_html__( 'Switch on scroll', 'thrive-cb' ); ?>"></div>
			<div class="tve-control no-space" data-extends="Slider" data-key="switchAfter" data-label="<?php echo esc_html__( 'Switch after', 'thrive-cb' ); ?>"></div>
		</div>
	</div>
</div>

<div id="tve-shared-styles-component" class="tve-component" data-view="SharedStyles">
	<div class="dropdown-header" data-prop="docked">
		<div><?php echo esc_html__( 'Style Options', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="control-grid no-space">
			<span class="tcb-style-icon">
				<?php tcb_icon( 'paint-brush-regular' ); ?>
			</span>

			<div class="label tve-control no-space" data-key="preview" data-truncate="false" data-initializer="preview_style_control"></div>

			<div class="shared-styles-actions shared-styles-icon" data-state="0">
				<div class="tve-control" data-key="global_style" data-initializer="global_style_control"></div>
			</div>

			<div class="shared-styles-actions tcb-hide" data-state="1">
				<a href="javascript:void(0)" class="click" data-fn="edit_style" data-side="top" data-tooltip="<?php echo esc_attr__( 'Edit Global Style', 'thrive-cb' ); ?>">
					<?php tcb_icon( 'edit' ); ?>
				</a>

				<a href="javascript:void(0)" class="click" data-fn="dom_unlink_style" data-side="top" data-tooltip="<?php echo esc_attr__( 'Unlink Global Style', 'thrive-cb' ); ?>">
					<?php tcb_icon( 'unlink-regular' ); ?>
				</a>

				<a href="javascript:void(0)" class="click" data-fn="toggle_more_options">
					<?php tcb_icon( 'ellipsis-v-regular' ); ?>
				</a>
			</div>

			<div class="control-grid no-space shared-styles-actions tcb-hide" data-state="2">
				<span class="global-edit-warning">
					<span><?php echo esc_html__( 'You are editing the Global Style', 'thrive-cb' ); ?>: </span>
					<strong><span class="shared-styles-name"></span></strong>.
					<span> <?php echo esc_html__( 'The changes you make on it will affect all elements which use this style.', 'thrive-cb' ); ?></span>
				</span>
			</div>

			<span class="tcb-style-icon trigger">
				<?php tcb_icon( 'arrow-down' ); ?>
			</span>

			<ul class="shared-styles-drop-content tcb-hide">
				<li class="click" data-fn="rename">
					<a href="javascript:void(0)"><?php echo esc_html__( 'Rename', 'thrive-cb' ) ?></a>
				</li>
				<li class="click" data-fn="delete">
					<a href="javascript:void(0)"><?php echo esc_html__( 'Delete', 'thrive-cb' ) ?></a>
				</li>
			</ul>
		</div>
	</div>

	<div class="control-grid pt-5 no-space shared-styles-actions tcb-hide" data-state="1">
		<span class="global-edit-warning">
			<?php echo esc_html__( 'This element has a Global Style applied: some of its properties are not editable at the instance level (e.g. Typography, Background, Borders etc). You can modify these by clicking on the pencil icon above.', 'thrive-cb' ); ?>
		</span>
	</div>
</div>
