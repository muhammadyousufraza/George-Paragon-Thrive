/* global TCB_Post_Edit_Data */

import thriveLogo from './thrive-logo';

/**
 * Once an block is update try to update the preview in gutenberg too
 */
window.addEventListener(
	'storage',
	( storageEvent ) => {
		if (
			storageEvent.key &&
			storageEvent.key.includes( 'thrive_symbol' )
		) {
			const id = storageEvent.key.split( '-' )[ 1 ],
				iframes = document.getElementsByClassName(
					`architect-block-${id}`
				);

			/**
			 * in case of duplicate / copy-paste
			 */
			Array.prototype.forEach.call( iframes, ( iframe ) => {
				iframe.setAttribute(
					'src',
					`${iframe.getAttribute(
						'src'
					)}?tcb=${generateRandomString()}`
				);
			} );

			localStorage.removeItem( storageEvent.key );
		}
	},
	false
);

if ( wp.domReady && typeof wp.domReady === 'function' ) {
	wp.domReady( function () {
		wp.blocks.updateCategory( 'thrive', {icon: thriveLogo} );
	} );
}


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
	return `${link}${
		link.split( '?' )[ 1 ] ? '&' : '?'
	}tve_block_preview=1`;
}

export function getTerm( slug ) {
	return TCB_Post_Edit_Data.symbols_tax_terms.find(
		( term ) => term.slug === slug
	);
}

export function maxNrOfPosts() {
	return 50;
}
