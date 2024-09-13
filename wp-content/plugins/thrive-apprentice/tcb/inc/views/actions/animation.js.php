<?php echo 'function(trigger, action, config) {'; ?>
var $element = jQuery( this ),
	$at = $element.closest( '.tcb-col, .thrv_wrapper' );
if ( $at.length === 0 ) {
	$at = $element;
}
if ( ! config.loop && $at.data( 'a-done' ) ) {
	return;
}
$at.data( 'a-done', 1 );
const hadAnimStart = $at.hasClass( 'tve_anim_start' );
$at.removeClass( function ( i, cls ) {
	return cls.split( ' ' ).filter( function ( item ) {
		return item.indexOf( 'tve_anim_' ) === 0;
	} ).join( ' ' );
} ).addClass( 'tve_anim_' + config.anim ).removeClass( 'tve_anim_start' );
if ( config.loop ) {
	setTimeout( function () {
		$at.addClass( 'tve_anim_start' );
	}, hadAnimStart ? 50 : 0 );
	if ( trigger === 'mouseover' ) {
		$element.one( 'mouseleave', function () {
			$at.removeClass( 'tve_anim_start' );
		} );
	}
	if ( trigger === 'tve-viewport' ) {
		$element.one( 'tve-viewport-leave', function () {
			/**
			 * double check for viewport
			 * animation in animation triggers weird behaviors
			 */
			if ( ! TCB_Front.isInViewport( $element ) ) {
				$at.removeClass( 'tve_anim_start' );
			}
		} );
	}
} else {
	setTimeout( function () {
		$at.addClass( 'tve_anim_start' );
	}, 50 );
}
return false;
<?php echo '}'; ?>
