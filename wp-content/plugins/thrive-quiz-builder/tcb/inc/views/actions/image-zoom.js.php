<?php echo 'function(trigger,action,config){' ?>
var $target = jQuery( this ),
	offset = $target.offset(),
	target_w = $target.outerWidth(),
	target_h = $target.outerHeight(),
	$element = $target,
	sameImage = ( config.url && $element.attr( 'src' ) && $element.attr( 'src' ) === config.url ) || ( config.id && $element.attr( 'data-id' ) && $element.attr( 'data-id' ) == config.id );
if ( config.id ) {
	$fullSize = jQuery( "#tcb-image-zoom-" + config.id + " img" );
	if ( $fullSize.length ) {
		$element = $fullSize;
	}
}
/* If it is the same img but set from Anim&Action work like Open full size image on click*/
if ( ! sameImage || ( sameImage && config.sizeChanged ) ) {
	if ( config.id ) {
		$element = jQuery( "#tcb-image-zoom-" + config.id + " img" )
	} else if ( $element.find( "img" ).length ) {
		$element = $element.find( "img" )
	}
}
$element = $element.first();

var imageSrc = $element.attr( "data-opt-src" ) || $element.attr( "src" ),
	imgAlt = $target.attr( 'alt' ) || '',
	$lightbox = jQuery( '#tve_zoom_lightbox' ),
	$overlay = jQuery( '#tve_zoom_overlay' ),
	windowWidth = window.innerWidth,
	windowHeight = window.innerHeight,
	img_size = $element.data( "tve-zoom-clone" ),
	resizeScale = windowWidth < 600 ? 0.8 : 0.9;

if ( imageSrc.indexOf( 'data:image' ) !== - 1 && $element.attr( 'data-src' ) ) {
	imageSrc = $element.attr( 'data-src' );
}

/**
 * Force lazy load of the image
 */
if ( window.lazySizes ) {
	lazySizes.loader.unveil( $element[ 0 ] );
}

if ( typeof img_size === 'undefined' ) {
	var $clone = $element.clone()
	                     .css( {
		                     position: "absolute",
		                     width: "",
		                     height: "",
		                     left: "-8000px",
		                     top: "-8000px"
	                     } ).removeAttr( "width height" );
	$clone.appendTo( "body" );
	/**
	 * `.one()` ensures this will not get executed multiple times.
	 */
	$clone.one( 'load', function () {
		var $parent = $element.parent(),
			height = parseFloat( $element.attr( 'data-init-height' ) ) || parseFloat( $element.attr( 'height' ) || $element.height() ),
			width = parseFloat( $element.attr( 'data-init-width' ) ) || parseFloat( $element.attr( 'width' ) || $element.width() );

		/**
		 * If we cant get the size try to make the parent visible until we get img props
		 */
		if ( ! ( height && width ) ) {
			$parent.css( {display: 'block', visibility: 'hidden'} );
			height = $element.height();
			width = $element.width();
			$parent.css( {display: 'none', visibility: ''} );
		}

		img_size = {
			"originalWidth": width,
			"width": width,
			"originalHeight": height,
			"height": height
		};

		if ( img_size.originalWidth > windowWidth * resizeScale || img_size.originalHeight > windowHeight * resizeScale ) {
			var widthPercent = img_size.originalWidth / windowWidth,
				heightPercent = img_size.originalHeight / windowHeight;

			img_size.width = ( ( widthPercent > heightPercent ) ? ( windowWidth * resizeScale ) : ( windowHeight * resizeScale * ( img_size.originalWidth / img_size.originalHeight ) ) );
			img_size.height = ( ( widthPercent > heightPercent ) ? ( windowWidth * resizeScale * ( img_size.originalHeight / img_size.originalWidth ) ) : ( windowHeight * resizeScale ) );

			img_size.width += 30;
			img_size.height += 30;
		}

		$element.data( "tve-zoom-clone", img_size );

		show_lightbox();
	} );
	/**
	 * Firefox doesnt trigger load event for the clone when is open full size image
	 */
	if ( TCB_Front.browser.mozilla && ( sameImage || typeof sameImage === 'undefined' ) ) {
		$clone.trigger( 'load' );
	} else if ( imageSrc.includes( '.optimole.com/' ) ) {
		/**
		 * Optimole w/ lazy-load will actually trigger loading of this image URL earlier.
		 * Image is already loaded at this point. Just need to trigger the load event manually
		 */
		$clone.trigger( 'load' );
	} else {
		/**
		 * Finally, some failsafe mechanism, trigger the load event with a delay. There have been cases reported where it does not "always" work.
		 */
		setTimeout( function () {
			$clone.trigger( 'load' );
		}, 500 );
	}
} else {
	show_lightbox();
}


function show_lightbox() {

	if ( $lightbox.length ) {
		$lightbox.show();
	} else {
		$lightbox = jQuery( "<div id='tve_zoom_lightbox'><div class='tve_close_lb thrv-svg-icon'>" + TCB_Front.icons.get('cross') + "</div><div id='tve_zoom_image_content'></div></div>" )
			.appendTo( 'body' );
		$overlay = jQuery( "<div id='tve_zoom_overlay'></div>" ).hide()
		                                                        .appendTo( 'body' );
		var tve_close_lb = function () {
			$lightbox.hide();
			$overlay.hide();
		};
		/* set listeners for closing the lightbox */
		jQuery( document ).on( "click", ".tve_close_lb", tve_close_lb );
		jQuery( document ).on( "click", "#tve_zoom_overlay", tve_close_lb );
		jQuery( document ).on( "keyup", function ( e ) {
			if ( e.keyCode == 27 ) {
				tve_close_lb();
			}
		} );

		jQuery( window ).resize( function () {
			var _sizes = $lightbox.data( "data-sizes" ),
				windowWidth = window.innerWidth,
				windowHeight = window.innerHeight,
				resizeScale = windowWidth < 600 ? 0.8 : 0.9;

			if ( _sizes.originalWidth > windowWidth * resizeScale || _sizes.originalHeight > windowHeight * resizeScale ) {
				var widthPercent = _sizes.originalWidth / windowWidth,
					heightPercent = _sizes.originalHeight / windowHeight;

				_sizes.width = ( ( widthPercent > heightPercent ) ? ( windowWidth * resizeScale ) : ( windowHeight * resizeScale * ( _sizes.originalWidth / _sizes.originalHeight ) ) );
				_sizes.height = ( ( widthPercent > heightPercent ) ? ( windowWidth * resizeScale * ( _sizes.originalHeight / _sizes.originalWidth ) ) : ( windowHeight * resizeScale ) );
			}

			$lightbox.width( _sizes.width );


			$lightbox.css( "margin-left", - ( _sizes.width + 30 ) / 2 );
			$lightbox.css( "margin-top", - ( _sizes.height + 30 ) / 2 );
		} );
	}

	$lightbox.data( "data-sizes", img_size );

	jQuery( "#tve_zoom_image_content" ).html( "<img src='" + imageSrc + "' alt='" + imgAlt + "'/>" );

	$lightbox.css( {
		left: offset.left + target_w / 2,
		top: offset.top + target_h / 2,
		marginLeft: 0,
		marginTop: 0,
		width: 0,
		opacity: 0
	} ).animate( {
		opacity: 1,
		left: '50%',
		top: '50%',
		marginLeft: - ( img_size.width + 30 ) / 2,
		marginTop: - ( img_size.height + 30 ) / 2,
		width: img_size.width
	}, 150 );
	$overlay.fadeIn( 150 );
}

<?php echo 'return false;}';
