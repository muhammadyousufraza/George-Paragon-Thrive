<div class="thrv_wrapper tqb-question-wrapper tcb-local-vars-root tve-only-inner-drop">
	<div class="thrive-colors-palette-config" style="display: none !important">
		<?php echo esc_html( $colors ); ?>
	</div>
	<div class="thrv_wrapper tqb-progress-container tcb-hidden">
		<div class="tqb-progress-box tqb-progress-style-<?php echo esc_attr( $quiz_style ); ?>">
			<div class="thrv_wrapper tqb-progress-label">
				<span>33% <?php echo esc_html( $progress_settings['label_text'] ); ?></span>
			</div>
			<div class="tqb-progress tcb-local-vars-root <?php echo $palettes->has_pg_palettes() ? 'tcb-local-vars-root' : '' ?>">
				<div class="thrive-colors-palette-config" style="display: none !important">
					<?php if ( $palettes->has_pg_palettes() ): ?>
						<?php echo esc_html( $palettes->get_palettes_as_string( $palettes->get_pg_palettes() ) ); ?>
					<?php endif; ?>
				</div>
				<div class="tqb-progress-completed"></div>
				<div class="tqb-next-item"></div>
				<div class="tqb-remaining-progress"></div>
			</div>
		</div>
	</div>
	<div class="thrv_wrapper tqb-question-container">
		<div class="tqb-question-media"></div>

		<div class="thrv_wrapper tqb-question-text">
			<span>Question text</span>
		</div>

		<div class="thrv_wrapper tqb-question-description">
			<span>Question description</span>
		</div>
	</div>
	<div class="thrv_wrapper tqb-answers-container tve-no-dropzone">
		<div class="tqb-answer-inner-wrapper tqb-editor-answer-wrapper">
			<div class="tqb-answer-content">
				<div class="tqb-answer-feedback tve-state-expanded tqb-hide <?php echo $is_write_wrong ? 'tqb-answer-feedback-right' : ''; ?>">
					<div class="tqb-feedback-inner">
						<div class="tqb-feedback-text <?php echo $is_write_wrong ? 'tqb-feedback-right' : ''; ?>">
							<?php echo $is_write_wrong ? 'Positive feedback' : 'Answer feedback'; ?>
						</div>
					</div>
				</div>

				<div class="tqb-answer-action">
					<div class="tqb-answer-text-type">
						<div class="tqb-fancy-container">
								<span class="tqb-answer-icon tqb-fancy-icon">
									<div class="thrv_wrapper tqb-icon tqb-right-icon tqb_v2 tqb-check">
										<svg class="tqb-check tcb-icon" viewBox="0 0 512 512" data-id="icon-check-solid" data-name="">
											<path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
										</svg>
									</div>
									<div class="thrv_wrapper tqb-icon tqb-wrong-icon tqb_v2 tqb-times">
										<svg class="tqb-times tcb-icon" viewBox="0 0 352 512" data-id="icon-times-solid" data-name="">
											<path d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path>
										</svg>
									</div>
								</span>
						</div>
						<div class="tqb-answer-text">
							<span class="tqb-span-text">
								Answer 1
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tqb-answer-inner-wrapper tqb-editor-answer-wrapper">
			<div class="tqb-answer-content">
				<div class="tqb-answer-feedback tve-state-expanded tqb-hide <?php echo $is_write_wrong ? 'tqb-answer-feedback-wrong ' : ''; ?>">
					<div class="tqb-feedback-inner">
						<div class="tqb-feedback-text <?php echo $is_write_wrong ? 'tqb-feedback-wrong' : ''; ?>">
							<?php echo $is_write_wrong ? 'Negative feedback' : 'Answer feedback'; ?>
						</div>
					</div>
				</div>

				<div class="tqb-answer-action">
					<div class="tqb-answer-text-type">
						<div class="tqb-fancy-container">
								<span class="tqb-answer-icon tqb-fancy-icon">
									<div class="thrv_wrapper tqb-icon tqb-right-icon tqb_v2 tqb-check">
										<svg class="tqb-check tcb-icon" viewBox="0 0 512 512" data-id="icon-check-solid" data-name="">
											<path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
										</svg>
									</div>
									<div class="thrv_wrapper tqb-icon tqb-wrong-icon tqb_v2 tqb-times">
										<svg class="tqb-times tcb-icon" viewBox="0 0 352 512" data-id="icon-times-solid" data-name="">
											<path d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path>
										</svg>
									</div>
								</span>
						</div>
						<div class="tqb-answer-text">
							<span class="tqb-span-text">
								Answer 2
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tqb-answer-inner-wrapper tqb-image-answer tqb-hide <?php echo $is_write_wrong ? 'tqb-image-answer-right tqb-right' : ''; ?>"<?php echo $is_write_wrong ? ' data-answer-prefix=".tqb-right"' : ''; ?>>
			<div class="tqb-answer-content">
				<div class="tqb-answer-action">
					<div class="tqb-answer-image-type">
						<div class="tqb-answer-image-container"></div>
						<div class="tqb-answer-text-container">
							<div class="tqb-fancy-container">
								<span class="tqb-answer-icon tqb-fancy-icon">
									<div class="thrv_wrapper tqb-icon tqb-right-icon tqb_v2 tqb-check">
										<svg class="tqb-check tcb-icon" viewBox="0 0 512 512" data-id="icon-check-solid" data-name="">
											<path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
										</svg>
									</div>
									<div class="thrv_wrapper tqb-icon tqb-wrong-icon tqb_v2 tqb-times">
										<svg class="tqb-times tcb-icon" viewBox="0 0 352 512" data-id="icon-times-solid" data-name="">
											<path d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path>
										</svg>
									</div>
								</span>
							</div>
							<div class="tqb-answer-text"><#= item.get('text') #></div>
						</div>
					</div>
				</div>
				<div class="tqb-answer-feedback tqb-hide <?php echo $is_write_wrong ? 'tqb-answer-feedback-right ' : ''; ?>">
					<div class="tqb-feedback-inner">
						<div class="tqb-feedback-text <?php echo $is_write_wrong ? 'tqb-feedback-right' : ''; ?>"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="tqb-answer-inner-wrapper tqb-image-answer tqb-hide <?php echo $is_write_wrong ? 'tqb-image-answer-wrong tqb-wrong' : ''; ?>"<?php echo $is_write_wrong ? ' data-answer-prefix=".tqb-wrong"' : ''; ?>>
			<div class="tqb-answer-content">
				<div class="tqb-answer-action">
					<div class="tqb-answer-image-type">
						<div class="tqb-answer-image-container">
						</div>
						<div class="tqb-answer-text-container">
							<div class="tqb-fancy-container">
								<span class="tqb-answer-icon tqb-fancy-icon">
									<div class="thrv_wrapper tqb-icon tqb-right-icon tqb_v2 tqb-check">
										<svg class="tqb-check tcb-icon" viewBox="0 0 512 512" data-id="icon-check-solid" data-name="">
											<path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
										</svg>
									</div>
									<div class="thrv_wrapper tqb-icon tqb-wrong-icon tqb_v2 tqb-times">
										<svg class="tqb-times tcb-icon" viewBox="0 0 352 512" data-id="icon-times-solid" data-name="">
											<path d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path>
										</svg>
									</div>
								</span>
							</div>
							<div class="tqb-answer-text"><#= item.get('text') #></div>
						</div>
					</div>
				</div>
				<div class="tqb-answer-feedback tqb-hide <?php echo $is_write_wrong ? 'tqb-answer-feedback-wrong ' : ''; ?>">
					<div class="tqb-feedback-inner">
						<div class="tqb-feedback-text <?php echo $is_write_wrong ? 'tqb-feedback-wrong' : ''; ?>"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="thrv_wrapper tqb-answer-inner-wrapper tqb-open-ended-wrapper tqb-open-answer tqb-hide">
			<div class="tqb-answer-content">
				<div class="tqb-answer-action tqb-open-ended-wrapper">
					<div class="tqb-answer-open-type">
						<textarea name="" id="tqb-open-type-answer" rows="<#= settings.get('form_size') #>" placeholder="<#= question.get('settings').get('placeholder')#>"></textarea>
						<p class="tqb-answer-status tqb-gray-text"><?php echo esc_html__( 'Characters: ', 'thrive-quiz-builder' ) ?><#= settings.get_max_value() #></p>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="tqb-button-holder">
		<div class="thrv_wrapper tqb-nav-button tqb-prev-button thrv_icon tcb-icon-display tqb-prev-icon">
			<svg class="tcb-icon" viewBox="0 0 512 512">
				<path d="M256 504C119 504 8 393 8 256S119 8 256 8s248 111 248 248-111 248-248 248zm28.9-143.6L209.4 288H392c13.3 0 24-10.7 24-24v-16c0-13.3-10.7-24-24-24H209.4l75.5-72.4c9.7-9.3 9.9-24.8.4-34.3l-11-10.9c-9.4-9.4-24.6-9.4-33.9 0L107.7 239c-9.4 9.4-9.4 24.6 0 33.9l132.7 132.7c9.4 9.4 24.6 9.4 33.9 0l11-10.9c9.5-9.5 9.3-25-.4-34.3z"/>
			</svg>
		</div>
		<div class="thrv_wrapper tqb-nav-button tqb-next-button thrv_icon tcb-icon-display tqb-next-icon">
			<svg class="tcb-icon" viewBox="0 0 512 512" data-id="icon-arrow-circle-right-solid">
				<path d="M256 8c137 0 248 111 248 248S393 504 256 504 8 393 8 256 119 8 256 8zm-28.9 143.6l75.5 72.4H120c-13.3 0-24 10.7-24 24v16c0 13.3 10.7 24 24 24h182.6l-75.5 72.4c-9.7 9.3-9.9 24.8-.4 34.3l11 10.9c9.4 9.4 24.6 9.4 33.9 0L404.3 273c9.4-9.4 9.4-24.6 0-33.9L271.6 106.3c-9.4-9.4-24.6-9.4-33.9 0l-11 10.9c-9.5 9.6-9.3 25.1.4 34.4z"></path>
			</svg>
		</div>
		<div class="tqb-nav-button tqb-check-button tqb-hide tqb-disabled">
			<a href="javascript:void(0)">
				<?php tqb_get_svg_icon( 'check-circle_solid' ); ?>
			</a>
		</div>
		<div class="tqb-nav-button tqb-finish-button tqb-hide tqb-disabled">
			<a href="javascript:void(0)">
				<?php tqb_get_svg_icon( 'finish-q' ); ?>
			</a>
		</div>
	</div>
</div>
