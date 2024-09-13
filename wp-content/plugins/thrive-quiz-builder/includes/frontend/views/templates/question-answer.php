<?php if ( empty( $questions ) ) {
	return;
} ?>
<div class="tqb-question-wrapper tve_no_icons">
	<?php foreach ( $questions as $question ) : ?>
		<div class="tqb-question-container">

			<?php echo $media; ?>

			<div class="tqb-question-text tve_no_icons">
				<?php echo $question['text']; ?>
			</div>

			<?php if ( $question['description'] ) : ?>
				<div class="tqb-question-description tve_no_icons">
					<?php echo $question['description']; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! $media && $question['image'] ) : ?>
				<div class="tqb-question-image-container">
					<img
							src="<?php echo isset( $question['image']->sizes->large ) ? esc_url( $question['image']->sizes->large->url ) : esc_url( $question['image']->sizes->full->url ); ?>"
							alt="question-image">
				</div>
			<?php endif; ?>

		</div>
		<div
				class="tqb-answers-container tve_no_icons <?php if ( 2 === (int) $question['q_type'] ) : ?> tqb-answer-has-image <?php endif; ?>">
			<?php if ( ! empty( $question['answers'] ) && is_array( $question['answers'] ) ) : ?>
				<?php foreach ( $question['answers'] as $answer ) : ?>
					<div class="tqb-answer-inner-wrapper">
						<div class="tqb-answer-action">
							<?php if ( 2 === (int) $question['q_type'] ) : ?>
								<div class="tqb-answer-image-type">
									<div class="tqb-answer-image-container">
										<img src="<?php echo esc_url( $answer['image']->sizes->thumbnail->url ); ?>"
											 alt=""
											 class="tqb-answer-image">
									</div>
									<div class="tqb-answer-text-container">
										<div class="tqb-answer-text">
											<?php echo esc_html( $answer['text'] ); ?>
										</div>
									</div>
								</div>
							<?php elseif ( $answer['text'] ) : ?>
								<div class="tqb-answer-text-type">
									<div class="tqb-answer-text">
										<?php echo $answer['text']; ?>
										<span class="tqb-fancy-icon">
											<?php tqb_get_svg_icon( 'check' ); ?>
											<?php tqb_get_svg_icon( 'times' ); ?>
										</span>
									</div>
								</div>
							<?php else : ?>
								<div class="tqb-answer-oeq-type">
									<div class="tqb-answer-oeq">
										<?php echo esc_html__( 'Write your response here', 'thrive-quiz-builder' ); ?>
									</div>
								</div>
							<?php endif; ?>

						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="tqb-button-holder">
			<?php if ( ! empty( $has_next_button ) ) : ?>
				<div class="tqb-nav-button tqb-next-button tqb-disabled">
					<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
						<path fill-rule="nonzero"
							  d="M20 0c11.048 0 20 8.952 20 20s-8.952 20-20 20S0 31.048 0 20 8.952 0 20 0zm-2.33 11.58l6.088 5.84H9.032a1.93 1.93 0 0 0-1.935 1.935v1.29a1.93 1.93 0 0 0 1.935 1.936h14.726l-6.089 5.838c-.782.75-.798 2-.032 2.766l.887.88a1.927 1.927 0 0 0 2.734 0L31.96 21.37a1.927 1.927 0 0 0 0-2.734L21.258 7.927a1.927 1.927 0 0 0-2.734 0l-.887.88a1.947 1.947 0 0 0 .032 2.774z"/>
					</svg>
				</div>
			<?php endif; ?>
		</div>
		<?php break; ?>
	<?php endforeach; ?>
</div>
