import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import thriveLogo from './thrive-logo';

const createElement = wp.element.createElement;

wp.domReady( function() {
	wp.blocks.updateCategory( 'thrive', { icon: thriveLogo } );
} );

/**
 * Once an block is update try to update the preview in gutenberg too
 */
window.addEventListener(
	'storage',
	( storageEvent ) => {
		if ( storageEvent.key && storageEvent.key.includes( 'tvu_design' ) ) {
			const design = storageEvent.key.split( '-' )[ 1 ],
				iframes = document.getElementsByClassName(
					`tvu-block-${ design }`
				);
			Array.prototype.forEach.call( iframes, ( iframe ) => {
				iframe.setAttribute(
					'src',
					`${ iframe.getAttribute(
						'src'
					) }?tvu=${ generateRandomString() }`
				);
			} );
			localStorage.removeItem( storageEvent.key );
		}
	},
	false
);

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

export function getPreviewLink( link ) {
	return `${ link }${
		link.split( '?' )[ 1 ] ? '&' : '?'
	}tve_block_preview=1`;
}

export function renderCampaignSelect( opts, props, label = '' ) {
	return createElement(
		'div',
		{
			class: 'tvu-block-select-wrapper',
		},
		createElement( SelectControl, {
			label: __( label, 'thrive-ult' ),
			options: opts,
			onChange( value ) {
				props.setAttributes( {
					selectedCampaign: parseInt( value ),
				} );
			},
			value: props.attributes.selectedCampaign,
		} )
	);
}

export function renderDesignSelect( opts, props, label = '' ) {
	return createElement(
		'div',
		{
			class: 'tvu-block-select-wrapper',
		},
		createElement( SelectControl, {
			label: __( label, 'thrive-ult' ),
			options: opts,
			onChange( value ) {
				props.setAttributes( {
					selectedDesign: parseInt( value ),
				} );
			},
			value: props.attributes.selectedDesign,
		} )
	);
}
