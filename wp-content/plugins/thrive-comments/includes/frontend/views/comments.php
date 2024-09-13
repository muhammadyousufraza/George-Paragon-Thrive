<div id="comments" class="comments-compat-<?php echo wp_get_theme()->stylesheet; ?>">
	<div id="thrive-comments" class="clearfix">
		<div class="tcm-dot-loader">
			<span class="inner1"></span>
			<span class="inner2"></span>
			<span class="inner3"></span>
		</div>
		<div class="thrive-comments-content">
			<div class="tcm-comments-filter"></div>
			<div class="tcm-comments-create" id="respond"></div>
			<div class="tcm-comments-list">
				<?php
				wp_list_comments();
				?>
			</div>
			<div class="tcm-lazy-comments"></div>
		</div>
	</div>
</div>
