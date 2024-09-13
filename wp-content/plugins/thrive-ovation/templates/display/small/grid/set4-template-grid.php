<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-set4-small-template tve_green">
	<?php foreach ( $testimonials as $testimonial ) : ?>
		<?php if ( ! empty( $testimonial ) ) : ?>
			<div class="tvo-item-col tvo-item-s12 tvo-item-m6 tvo-item-l6 ">
				<div class="tvo-testimonial-display-item">
					<div class="tvo-testimonial-message-box tvo-apply-background">
						<?php if ( ! empty( $config['show_title'] ) ) : ?>
							<?php if ( strlen( $testimonial['title'] ) > 0 ) {
								echo '<h4>';
								echo $testimonial['title'];
								echo '</h4>';
							} ?>
						<?php endif; ?>
						<div class="tvo-testimonial-content-wrapper">
							<div class="tvo-testimonial-quote"></div>
							<div class="tvo-relative tvo-testimonial-content">
								<p class="tvo-testimonial-text">
									<?php echo strip_tags( $testimonial['content'] ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="tvo-testimonial-info-wrapper clearfix">
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
