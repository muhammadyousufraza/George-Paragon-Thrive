<ul class="tvd-right">
	<li class="tvo-main-menu-button">
		<a href="javascript:void(0)" data-dropdown="display-testimonials" class="tvd-dropdown-button" data-activates="tvo-display-testimonials-dropdown"
		   data-beloworigin="true" data-hover="false" data-constrainwidth="false">
			<i class="tvo-icon-television"></i>
			<?php echo __( 'Display Testimonials', 'thrive-ovation' ); ?>
			<i class="tvd-icon-expanded tvd-no-margin-right"></i>
		</a>
		<ul id="tvo-display-testimonials-dropdown" class="tvd-dropdown-content">
			<li data-type="architect">
				<a data-source="<?php echo $is_thrive_visual_editor_active ? TVO_DISPLAY_USING_CONTENT_BUILDER_ACTIVE : TVO_DISPLAY_USING_CONTENT_BUILDER_INACTIVE ?>"
				   class="tvd-open-video"><?php echo __( 'Display using Thrive Architect', 'thrive-ovation' ); ?>
				</a>
			</li>
			<li data-type="shortcode">
				<a href="#shortcodes/display"
				   class="tvo-main-menu-link"><?php echo __( 'Display using WordPress Shortcodes', 'thrive-ovation' ); ?></a>
			</li>
			<!-- DISABLE THIS FOR NOW
			<li>
				<a data-source="<?php echo $is_thrive_leads_active ? TVO_DISPLAY_USING_LEADS_ACTIVE : TVO_DISPLAY_USING_LEADS_INACTIVE ?>"
				   class="tvd-open-video"><?php echo __( 'Display in Email Capture Form', 'thrive-ovation' ); ?></a>
			</li>
			-->
		</ul>
	</li>
	<li class="tvo-main-menu-button">
		<a href="javascript:void(0)" data-dropdown="capture-testimonials" class="tvd-dropdown-button" data-activates="tvo-capture-testimonials-dropdown"
		   data-beloworigin="true" data-hover="false" data-constrainwidth="false">
			<!--#shortcodes/capture-->
			<i class="tvo-icon-crosshairs"></i>
			<?php echo __( 'Capture Testimonials', 'thrive-ovation' ); ?>
			<i class="tvd-icon-expanded tvd-no-margin-right"></i>
		</a>
		<ul id="tvo-capture-testimonials-dropdown" class="tvd-dropdown-content">
			<li data-type="architect">
				<a class="tvd-open-video" data-source="<?php echo $is_thrive_visual_editor_active ? TVO_CAPTURE_USING_CONTENT_BUILDER_ACTIVE : TVO_CAPTURE_USING_CONTENT_BUILDER_ACTIVE ?>"><?php echo __( 'Capture using Thrive Architect', 'thrive-ovation' ); ?></a>
			</li>
			<li data-type="shortcode">
				<a href="#shortcodes/capture"
				   class="tvo-main-menu-link"><?php echo __( 'Capture using WordPress Shortcodes', 'thrive-ovation' ); ?></a>
			</li>
			<li data-type="leads">
				<a class="tvd-open-video" data-source="<?php echo $is_thrive_leads_active ? TVO_CAPTURE_USING_LEADS_ACTIVE : TVO_CAPTURE_USING_LEADS_ACTIVE ?>"><?php echo __( 'Capture using Thrive Leads', 'thrive-ovation' ); ?></a>
			</li>
			<li data-type="import">
				<a href="#socialimport" class="tvo-main-menu-link"><?php echo __( 'Import from Social Media', 'thrive-ovation' ); ?></a>
			</li>
		</ul>
	</li>
	<li>
		<a href="#settings">
			<i class="tvd-icon-settings"></i>
			<?php echo __( 'Settings', 'thrive-ovation' ); ?>
		</a>
	</li>
	<li>
		<a id="tvd-share-modal" class="tvd-modal-trigger" href="#tvd-modal1"
		   data-overlay_class="tvd-white-bg" data-opacity=".95">
			<span class="tvd-icon-heart"></span>
		</a>
	</li>
</ul>
<?php include TVE_DASH_PATH . '/templates/share.phtml'; ?>
