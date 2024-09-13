<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$args = array_merge( array(
	'css'       => '',
	'title'     => '',
	'id'        => '',
	'type'      => '',
	'value'     => '',
	'extra_cls' => '',
	'hide'      => false,
), $args );

$extra_attrs = '';
if ( $args['hide'] ) {
	$extra_attrs = 'style="display:none;"';
}

?>
<li class="tve-dynamic-dropdown-option tve_no_icons <?php echo $args['extra_cls']; ?>"
	data-selector='[data-css="<?php echo $args['css']; ?>"] .tve-dynamic-dropdown-option'
	data-type="<?php echo $args['type']; ?>"
	data-value="<?php echo $args['value']; ?>" <?php echo $extra_attrs; ?>>
	<div class="tve-input-option-text tcb-plain-text">
		<span contenteditable="false"><?php echo $args['title']; ?></span>
	</div>
</li>
