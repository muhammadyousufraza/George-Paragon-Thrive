import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import thriveLogo from './thrive-logo';

const createElement = wp.element.createElement;

wp.domReady( function() {
	wp.blocks.updateCategory( 'thrive', { icon: thriveLogo } );
} );

/**
 * Helper functions
 */

export function tveOuterHeight( el ) {
	if ( ! el ) {
		return 0;
	}
	let height = el.offsetHeight;
	const style = getComputedStyle( el );

	height += parseInt( style.marginTop ) + parseInt( style.marginBottom );
	return height;
}

export function generateRandomString( radix = 16 ) {
	return (
		new Date().getTime() + Math.floor( Math.random() * 100000 )
	).toString( radix );
}

export function getPreviewLink( link, quizID ) {
	return `${ link }${
		link.split( '?' )[ 1 ] ? '&' : '?'
	}quiz_id=${ quizID }`;
}

export function renderSelect( opts, props, label ) {
	return createElement(
		'div',
		{ class: 'tqb-block-select-wrapper' },

		createElement( SelectControl, {
			label: __( label, 'thrive-quiz-builder' ),
			options: opts,
			onChange( value ) {
				props.setAttributes( {
					selectedBlock: parseInt( value ),
				} );
			},
			value: props.attributes.selectedBlock,
		} )
	);
}
