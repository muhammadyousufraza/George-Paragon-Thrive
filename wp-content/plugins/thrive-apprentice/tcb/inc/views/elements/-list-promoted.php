<?php foreach ( tcb_editor()->elements->get_promoted() as $element ) : ?>
	<div class="tve-element tve-promoted-element" data-elem="<?php echo esc_attr( $element->tag() ) ?>" data-alternate="<?php echo esc_attr($element->alternate()) ?>" draggable="true">
		<div class="item">
			<span class="tve-e-icon"><?php tcb_icon( $element->icon() ); ?></span>
			<span class="tve-e-name"><?php echo esc_html( $element->name() ); ?></span>
		</div>
	</div>
<?php endforeach; ?>
