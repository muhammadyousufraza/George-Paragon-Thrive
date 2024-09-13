<?php /* small 32px sidebar */ ?>
<div id="sidebar-right">
	<div class="links">
		<div class="upper">
			<a class="sidebar-item click" data-fn="blur" href="javascript:void(0)">
				<img alt="Thrive Architect" width="18" src="<?php echo esc_url( apply_filters( 'architect.branding', tve_editor_css( 'images/admin-bar-logo.png' ), 'logo_src' ) ); ?>"/>
			</a>
			<?php if ( tcb_editor()->can_add_elements() ) : ?>
				<a href="javascript:void(0)" class="sidebar-item green add-element" data-position="left" data-tooltip="<?php echo esc_attr__( 'Add Element', 'thrive-cb' ); ?>"
				   data-toggle="elements">
					<?php tcb_icon( 'plus-square-light' ); ?>
					<?php tcb_icon( 'plus-square-regular', false, 'sidebar', 'active' ); ?>
				</a>
			<?php endif; ?>

			<a href="javascript:void(0)" class="mouseenter mouseleave style-panel sidebar-item tcb-sidebar-icon-<?php echo esc_attr( tcb_editor()->get_sidebar_icon_availability( 'central-style' ) ); ?>" data-fn-mouseenter="toggleTooltip" data-fn-mouseleave="toggleTooltip" data-toggle="central_style_panel" data-tooltip="<?php echo esc_attr__( 'Central Style Panel', 'thrive-cb' ); ?>" data-position="left" data-tooltip-type="central-style-panel">
				<?php tcb_icon( 'central-style-panel' ); ?>
				<?php tcb_icon( 'central-style-panel', false, 'sidebar', 'active' ); ?>
			</a>

			<a href="javascript:void(0)"
			   class="mouseenter mouseleave click sidebar-item tcb-sidebar-icon-tooltip tcb-sidebar-icon-unavailable"
			   data-fn="toggleTooltip"
			   data-tooltip-type="conditional-display"
			   data-toggle="conditional-display">
				<?php tcb_icon( 'control-switch' ); ?>
			</a>

			<a href="javascript:void(0)"
			   class="mouseenter mouseleave click sidebar-item tcb-sidebar-icon-<?php echo esc_attr( tcb_editor()->get_sidebar_icon_availability( 'cloud-templates' ) ); ?>"
			   data-fn-mouseenter="toggleTooltip"
			   data-fn-mouseleave="toggleTooltip"
			   data-tooltip-type="cloud-templates"
			   data-position="left"
			   data-fn="open_templates_picker"
			   data-tooltip="<?php echo esc_attr( tcb_editor()->get_templates_tab_title() ); ?>">
				<?php tcb_icon( 'cloud-download-light' ); ?>
			</a>

			<?php if ( tcb_editor()->has_settings_tab() ) : ?>
				<a href="javascript:void(0)"
				   class="sidebar-item open-settings mouseenter mouseleave tcb-sidebar-icon-tooltip"
				   data-fn-mouseenter="toggleTooltip"
				   data-fn-mouseleave="toggleTooltip"
				   data-tooltip-type="sidebar-settings"
				   data-toggle="settings"
				   data-position="left">
					<?php tcb_icon( 'cog-light' ); ?>
					<?php tcb_icon( 'cog-regular', false, 'sidebar', 'active' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( apply_filters( 'tcb_post_type_has_meta_boxes', in_array( get_post_type(), array( 'post', 'page', 'product' ), true ) ) ): ?>
				<a href="javascript:void(0)" class="click sidebar-item has-new-label" data-fn="adminMetaBoxes" data-position="left" data-tooltip="<?php echo esc_attr__( 'WordPress settings', 'thrive-cb' ); ?>">
					<?php tcb_icon( 'wordpress' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( ! function_exists( 'thrive_ab' ) ) : ?>
				<a id="thrive-ab-create-test" data-position="left"
				   class="mouseenter mouseleave sidebar-item tcb-sidebar-icon-<?php echo esc_attr( tcb_editor()->get_sidebar_icon_availability( 'ab-test' ) ); ?>" data-fn-mouseenter="toggleTooltip" data-fn-mouseleave="toggleTooltip" data-tooltip-type="ab-test">
					<?php tcb_icon( 'test' ); ?>
				</a>
			<?php endif ?>

			<?php do_action( 'tcb_sidebar_extra_links' ); ?>
		</div>

		<!--	Help Corner	-->
		<div class="bottom">
			<a href="javascript:void(0)" data-position="left" class="sidebar-item click"
			   data-fn="openHelpCorner" data-tooltip="Help Corner">
				<?php tcb_icon( 'question-light' ) ?>
			</a>
		</div>
	</div>

	<div class="drawer" data-drawer="elements">
		<div class="header fill" id="el-search">
			<span class="text s-normal"><?php echo esc_html__( 'Add Element', 'thrive-cb' ); ?></span>
			<div class="s-links s-normal">
				<a href="javascript:void(0)" class="s-icon click search" data-fn="state" data-state="search"><?php tcb_icon( 'search-regular' ); ?></a>
				<a href="javascript:void(0)" class="s-icon click close" data-fn="hide_drawers"><?php tcb_icon( 'times-regular' ); ?></a>
			</div>
			<input autocomplete="off" type="text" name="s" placeholder="<?php echo esc_attr__( 'Search elements...', 'thrive-cb' ); ?>" class="s-search q">
			<a href="javascript:void(0)" class="x-icon click search s-search" data-fn="state" data-state="normal">
				<?php tcb_icon( 'times-light' ); ?>
			</a>
		</div>
		<div id="tve-promoted-elements"><?php tcb_template( 'elements/-list-promoted' ); ?></div>
		<div id="tve-elements" class="scrollbar"><?php tcb_template( 'elements/-sidebar-list' ); ?></div>
	</div>

	<div class="drawer central_style_panel" data-drawer="central_style_panel">
		<div class="header fill" id="el-search">
			<span class="text s-normal"><?php echo esc_html__( 'Style Editor', 'thrive-cb' ); ?></span>
			<div class="s-links s-normal">
				<a href="javascript:void(0)" class="s-icon click close" data-fn="hide_drawers"><?php tcb_icon( 'times-regular' ); ?></a>
			</div>
		</div>
		<?php tcb_template( 'central-style-panel', tcb_editor()->get_template_styles_data() ); ?>
	</div>

	<div class="drawer hide-scroll settings" data-drawer="settings">
		<div class="header" id="settings-search">
			<a href="javascript:void(0)" class="s-normal back-link">
				<?php tcb_icon( 'arrow-left-solid', false, 'sidebar', 's-normal b-icon' ); ?>
				<span class="text s-normal"><?php echo esc_html__( 'Settings', 'thrive-cb' ); ?></span>
			</a>
			<div class="s-links s-normal">
				<a href="javascript:void(0)" class="s-icon click search" data-fn="state" data-state="search"><?php tcb_icon( 'search-regular' ); ?></a>
				<a href="javascript:void(0)" class="s-icon click close" data-fn="hide_drawers"><?php tcb_icon( 'times-regular' ); ?></a>
			</div>
			<input autocomplete="off" type="text" name="s" placeholder="<?php echo esc_attr__( 'Search setting...', 'thrive-cb' ); ?>" class="s-search q">
			<a href="javascript:void(0)" class="x-icon click search s-search" data-fn="state" data-state="normal">
				<?php tcb_icon( 'times-light' ); ?>
			</a>
		</div>
		<div class="scroll-content">
			<?php tcb_template( 'settings' ); ?>
		</div>
	</div>

	<div class="drawer" data-drawer="conditional-display">
		<div class="header">
			<span class="text">
				<?php echo esc_attr__( 'Conditioned content', 'thrive-cb' ); ?>
			</span>
			<div class="s-links s-normal">
				<a href="javascript:void(0)" class="s-icon click close" data-fn="hide_drawers"><?php tcb_icon( 'times-regular' ); ?></a>
			</div>
		</div>
		<ul class="conditional-display-hierarchy"></ul>
	</div>

	<div class="tve-custom-code-wrapper full-width" style="display: none">
		<textarea id="tve-custom-css-code"></textarea>
		<div class="tve-css-buttons-wrapper">
			<div class="code-apply"><?php tcb_icon( 'check' ); ?></div>
			<div class="code-close"><?php tcb_icon( 'close2' ); ?></div>
		</div>
	</div>

	<div class="tve-editor-html-wrapper full-width" style="display: none">
		<textarea id="tve-custom-html-code"></textarea>
		<div class="tve-code-buttons-wrapper">
			<div class="code-button-check"><?php tcb_icon( 'check' ); ?></div>
			<div class="code-button-close"><?php tcb_icon( 'close2' ); ?></div>
		</div>
	</div>

</div>
