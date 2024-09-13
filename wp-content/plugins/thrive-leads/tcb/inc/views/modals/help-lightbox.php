<?php
$items = array(
	array(
		'picture'     => tve_editor_url( 'editor/css/images/help-corner-lightbox/group-83.png' ),
		'picture-alt' => __( 'Change Template', 'thrive-cb' ),
		'text'        => array(
			__( 'Quickly add new elements or change the template', 'thrive-cb' ),
		),
		'class'       => 'change-template',

	),
	array(
		'picture'     => tve_editor_url( 'editor/css/images/help-corner-lightbox/group-84.png' ),
		'picture-alt' => __( 'Select any element', 'thrive-cb' ),
		'text'        => array(
			__( 'Select any element in the canvas and customize it\'s properties ', 'thrive-cb' ),
		),
		'class'       => 'select-element',
	),
	array(
		'picture'     => tve_editor_url( 'editor/css/images/help-corner-lightbox/group-29.png' ),
		'picture-alt' => __( 'Create multiple selections', 'thrive-cb' ),
		'text'        => array(
			__( 'Create multiple selections for moving/styling', 'thrive-cb' ),
		),
		'class'       => 'create-selections',
	),
);
?>
<div class="modal-help-corner">
	<div class="slider-help-corner">
		<?php foreach ( $items as $item ) : ?>
			<div class="slide-content">
				<div class="slide">
					<div class="slide-image">
						<img src="<?php echo esc_url( $item['picture'] ); ?>" alt="<?php echo esc_attr( $item['picture-alt'] ); ?>"
							 class="<?php echo esc_attr( $item['class'] ); ?> "/>
					</div>
					<div class="slide-text">
						<?php foreach ( $item['text'] as $text ) : ?>
							<p><?php echo esc_html( $text ); ?></p>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="help-modal-navigation">
		<div class="circle-arrow click" data-fn="prevSlide"><?php tcb_icon( 'long-arrow-right-light' ) ?></div>

		<div class="slider-dots-help">
			<span class="dot-content click active" data-fn="switchSlide" data-slide="0"></span>
			<span class="dot-content click" data-fn="switchSlide" data-slide="1"></span>
			<span class="dot-content click" data-fn="switchSlide" data-slide="2"></span>
		</div>

		<div class="circle-arrow click" data-fn="nextSlide"><?php tcb_icon( 'long-arrow-right-light' ) ?></div>
	</div>

	<div class="flex-row center-text">
		<button class="open-help-corner click" data-fn="openHelpCorner">
			<?php tcb_icon( 'help-corner' ) ?>
			<?php echo __( 'Help Corner', 'thrive-cb' ); ?>
		</button>
	</div>

</div>
