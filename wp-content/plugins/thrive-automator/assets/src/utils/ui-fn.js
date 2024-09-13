import { detectBrowser } from "@/utils/data-fn";

/**
 * Prevent view to have overflow
 * @param hideOverflow
 */
export function toggleBodyOverflow( hideOverflow = true ) {
	document.body.classList.toggle( 'tap-overflow-hidden', hideOverflow );
}

/**
 * Toggle app loader
 * @param show
 */
export function toggleAppLoader( show = true ) {
	document.getElementsByClassName( 'tap-app-loader' )[ 0 ].classList.toggle( 'tap-loader-show', show );
}

/**
 * Folded wp sidebar
 */
export function foldWpMenu() {
	document.body.classList.add( 'folded' );
}

export function setBrowserClass() {
	document.body.classList.add( `tap-${detectBrowser()}-browser` );
}

/**
 * Toast messages
 * @param message
 * @param timeout
 */
export function tapToast( message = '', timeout = 3000 ) {
	const toast = document.getElementById( 'tap-toast-container' )
	if ( message ) {
		toast.textContent = message;
		toast.classList.add( 'show-toast' );
	}
	setTimeout( () => {
		toast.classList.remove( 'show-toast' );
	}, message ? timeout : 0 )
}


/**
 * Custom handling of select2 options in order to include images
 * @param option
 * @param extraData
 * @param theme
 * @returns {*|Window.jQuery|HTMLElement}
 */
export function select2Option( option, extraData = {}, theme = '' ) {
	let img = extraData.logo || extraData.image || option.image || '';
	if ( img ) {
		if ( /(https?:\/\/[^\s]+)/.test( img ) ) {
			img = `<img class="tap-select2-img" src="${img}" alt="">`;
		} else if ( ! img.includes( '<svg' ) ) {
			img = `<svg class="tap-icon"><use xlink:href="#${img}" /></svg>`;
		}
		img = `<span class="tap-opt-image tap-icon-wrapper">${img}</span>`;
	}

	return jQuery( `<span class="tap-select-option" data-option-id="${option.id}" data-theme="${theme}">${img}<span class="tap-opt-text">${option.text}</span></span>` );
}

/**
 * Custom search matcher for select2
 * It matches data that contains only a part of the search
 * @param params
 * @param data
 * @returns {null|*}
 */
export function select2Matcher( params, data ) {
	// If there are no search terms, return all of the data
	if ( ! params.term || params.term.trim() === '' ) {
		return data;
	}

	// Do not display the item if there is no 'text' property
	if ( typeof data.text === 'undefined' ) {
		return null;
	}

	const searchText = params.term.toLowerCase().replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ),
		splitWords = searchText.replace( /\s\s+/g, ' ' ).replaceAll( ' ', '|' ),
		regex = new RegExp( splitWords, 'g' ), optionText = data.text.toLowerCase();

	if ( optionText.includes( searchText ) || optionText.match( regex )?.length > 0 ) {
		return data;
	}

	// Return `null` if the term should not be displayed
	return null;
}

/**
 * Trigger custom keyboard event on an element
 * @param elements
 * @param eventType
 * @param keyCode
 */
export function triggerElementEvent( elements, eventType = 'keyup', keyCode = 13 ) {
	const newEvent = new Event( eventType, {
		bubbles: false,
		cancelable: true,
	} );

	if ( eventType.includes( 'key' ) ) {
		newEvent.keyCode = keyCode;
	}

	if ( elements?.length > 1 || elements instanceof NodeList ) {
		[ ...elements ].forEach( el => el.dispatchEvent( newEvent ) );
	} else {
		elements?.dispatchEvent( newEvent )
	}
}

/**
 * Get an array of fields previews
 * @param fieldsData
 * @param showUnused
 * @returns {*[]}
 */
export function getFieldsPreview( fieldsData, showUnused = false ) {
	let unsetFields = 0;
	const text = [],
		getPreviewText = fields => {
			fields.forEach( data => {
				if ( data.preview?.length ) {
					text.push( data.preview )
				} else {
					unsetFields ++;
				}
				if ( data.subfield && Object.keys( data.subfield )?.length ) {
					getPreviewText( Object.values( data.subfield ) );
				}
			} );
		};

	getPreviewText( Object.values( fieldsData ) );
	if ( showUnused && unsetFields ) {
		text.push( `<div class="tap-preview-unset">${unsetFields} more parameters</div>` )
	}
	return text;
}

/**
 * Fix string characters
 *
 * @param string
 * @returns {*}
 */
export function replaceStringSpecialChars( string ) {
	string = string.replace( /(&#8216;|&#8217;)/g, '\'' ).replace( /(&amp;)/g, '&' );// replace left-right single quote with normal quote
	return string.replace( /&#(\d+);/g, ( _match, dec ) => String.fromCharCode( dec ) );
}
