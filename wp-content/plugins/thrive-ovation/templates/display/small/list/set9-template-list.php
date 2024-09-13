<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-set9-small-template tve_green">
	<?php foreach ( $testimonials as $testimonial ) : ?>
		<?php if ( ! empty( $testimonial ) ) : ?>
			<div class="tvo-item-col tvo-item-s12 tvo-item-m12 tvo-item-l12 ">
				<div class="tvo-testimonial-display-item">
					<div class="tvo-testimonial-message-box tvo-apply-background">
						<?php if ( ! empty( $config['show_title'] ) ) : ?>
							<h4>
								<?php if ( strlen( $testimonial['title'] ) > 0 ) {
									echo $testimonial['title'];
								} ?>
							</h4>
						<?php endif; ?>
						<div class="tvo-relative tvo-testimonial-content">
							<p class="tvo-testimonial-text">
								<?php echo $testimonial['content']; ?>
							</p>
						</div>
					</div>
					<div class="tvo-testimonial-info-wrapper tvo-clearfix">
						<div class="tvo-testimonial-image-cover" style="background-image: url(<?php echo $testimonial['picture_url'] ?>)">
							<img src="<?php echo $testimonial['picture_url'] ?>"
								 class="tvo-testimonial-image tvo-dummy-image" alt="profile-pic">
						</div>
						<div class="tvo-testimonial-info tvo-info-background">
						<span class="tvo-testimonial-name">
							<?php echo $testimonial['name'] ?>
						</span>
							<br>
							<?php if ( ! empty( $config['show_role'] ) && strlen( $testimonial['role'] ) > 0 ) : ?>
								<span class="tvo-testimonial-role">
								<?php $role_wrap_before = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '<a href="' . $testimonial['website_url'] . '">';
								$role_wrap_after        = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '</a>';
								echo $role_wrap_before . $testimonial['role'] . $role_wrap_after; ?>
							</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach ?>
</div>
