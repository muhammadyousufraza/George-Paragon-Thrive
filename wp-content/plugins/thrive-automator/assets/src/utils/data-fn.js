import { dbIcon, regexPatterns } from "@/utils/constants";

const allowedDataKeys = [ 'conditions', 'extra_data', 'name', 'value', 'unit', 'image', 'logo', 'webhook_id' ];

/**
 * Generate uniq id based on current time
 * @param radix
 * @returns {string}
 */
export function generateRandomString( radix = 16 ) {
	return ( new Date().getTime() + Math.floor( Math.random() * 100000 ) ).toString( radix );
}

/**
 * Whether the subfield values are valid or not
 *
 * @param fields
 * @returns {boolean}
 */
export function subfieldsAreValid( fields ) {
	let isValid = true;
	const validateData = data => {
		const keys = Object.keys( data );
		let i = 0;

		while ( isValid && i < keys.length ) {
			if ( data[ keys[ i ] ].validation ) {
				isValid = data[ keys[ i ] ].validation.isValid;
				if ( data[ keys[ i ] ].subfield ) {
					validateData( data[ keys[ i ] ].subfield )
				}
			}
			i ++;
		}
	};

	validateData( fields );

	return isValid;
}

/**
 * Validate field values based on list of validations
 * @param value
 * @param validator
 * @returns {{isValid: boolean, message: (string)}}
 */
export function validateValue( value, validator = [] ) {
	let isValid = true, message = '';

	if ( validator.length ) {
		let i = 0;

		if ( typeof value === 'string' ) {
			value = value.trim();
		}
		const emailRegex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
			urlRegex = /(http(s)?):\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,30}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g,
			passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/;


		while ( isValid && i < validator.length ) {
			switch ( validator[ i ] ) {
				case 'required':
					if ( ! String( value ).length ) {
						isValid = false;
						message = Array.isArray( value ) ? 'Invalid. Please select at least one item' : 'Value can not be empty';
					}
					break;
				case 'boolean':
					if ( typeof value !== 'boolean' ) {
						isValid = false;
						message = 'Not a boolean value';
					}
					break;
				case 'date':
					if ( ! value.getTime() ) {
						isValid = false;
						message = 'Invalid date';
					}
					break;
				case 'email':
					if ( ! emailRegex.test( String( value ).toLowerCase() ) ) {
						isValid = false;
						message = 'Invalid email format';
					}
					break;
				case 'url':
					if ( ! urlRegex.test( String( value ).toLowerCase() ) ) {
						isValid = false;
						message = 'Invalid link format';
					}
					break;
				case 'key_value_pair':
					if ( ! validateKeyPair( value ) ) {
						isValid = false;
						message = 'Empty fields not allowed.';
					}
					if ( isValid && Array.isArray( value ) && value?.some( pair => /\s/g.test( pair.key ) ) ) {
						isValid = false;
						message = 'No spaces allowed';
					}
					if ( isValid && Array.isArray( value ) && value?.some( pair => pair.key && ! ( validateDataKey( pair.key ).isValid ) ) ) {
						isValid = false;
						message = 'No special characters allowed';
					}
					break;
				case 'http_headers':
					if ( Array.isArray( value ) && value?.some( pair => pair.key && pair.key.match( /[a-zA-Z0-9$-]+/g )?.[ 0 ] !== pair.key ) ) {
						isValid = false;
						message = 'No special characters allowed';
					}
					break;
				case 'password':
					if ( ! passRegex.test( String( value ) ) ) {
						isValid = false;
						message = 'Password must contain at least 8 characters, one lowercase letter, one uppercase letter, one number and one special character';
					}
					break;
				default:
					break;
			}
			i ++;
		}
	}
	return {
		isValid,
		message: isValid ? '' : message
	};
}

export function getAutomationMappedData( automationSteps = [] ) {
	const mappedData = [];
	//before the save we need to map the triggers, delays and actions to unique ids need while running the automation
	automationSteps.forEach( step => {
		let stepContent = TAPAdmin._.cloneDeep( step );

		stepContent.id = stepContent.id || generateRandomString( 32 );

		if ( 'filters' !== step.type ) {
			stepContent.data = mapData( [ 'actions', 'delay' ].includes( stepContent.type ) ? [ stepContent.data ] : stepContent.data );
		}

		stepContent = TAPAdmin._.omit( stepContent, [ 'saved', 'matching_actions', 'data_objects' ] );

		mappedData.push( stepContent )
	} );

	return mappedData;
}

/**
 * Map triggers & actions to uniq ids
 * @param data
 * @returns {{}}
 */
export function mapData( data = [] ) {
	const mapped = {};
	data.forEach( obj => {
		let newID = generateRandomString();

		while ( Object.keys( mapped ).includes( newID ) ) {
			newID = generateRandomString();
		}

		/**
		 * Store in the db only the necessary things
		 * @type {{app_name: *, extra_data: ({}|{}), key}}
		 */
		mapped[ newID ] = {
			key: obj.id, app_id: obj.app_id,
		};

		allowedDataKeys.forEach( key => {
			if ( obj[ key ] ) {
				mapped[ newID ][ key ] = obj[ key ]
			}
		} )

	} );
	return mapped;
}

export function ucFirst( string ) {
	return string ? string.charAt( 0 ).toUpperCase() + string.slice( 1 ) : '';
}

/**
 * Get camelCase name
 * @param string
 * @returns {*|string}
 */
export function getComponentName( string ) {
	return string && typeof string === 'string' ? string.replaceAll( '_', ' ' ).split( ' ' ).map( part => ucFirst( part ) ).join( '' ).replaceAll( ' ', '' ) : '';
}

/**
 * Set/replace url params
 * @param url
 * @param param
 * @param value
 * @returns {string|*}
 */
export function setUrlParam( url, param, value ) {
	const urlObj = new URL( url );
	if ( Array.isArray( value ) ) {
		value.forEach( ( val, index ) => urlObj.searchParams.append( `${param}[${index}]`, val ) );
	} else {
		urlObj.searchParams.set( param, value );
	}
	return urlObj.toString();
}

/**
 * Init an object with fields & their validation
 * @param data
 */
export function initFields( data ) {
	const extraData = {};

	Object.keys( data ).forEach( key => {
		extraData[ data[ key ].id ] = {validation: validateValue( '', data[ key ].validators || [] )}
	} );

	return extraData;
}

/**
 * Replace field preview with actual data
 * @param previewString
 * @param value
 * @returns {*}
 */
export function prettyPreview( previewString, value ) {
	if ( previewString?.length ) {
		const valueLength = Array.isArray( value ) ? value.length : String( value ).length,
			isDynamic = field => String( field ).includes( 'tap-dynamic' ) || regexPatterns.shortcode.test( field );

		if ( valueLength >= 1 ) {
			if ( Array.isArray( value ) ) {
				value = value.map( field => {
					if ( TAPAdmin._.isObject( field ) ) {
						field = JSON.stringify( field )
					}

					return field ? `<div class="tap-preview-value ${isDynamic( field ) ? 'tap-flex' : ''}">${isDynamic( field ) ? dbIcon : ''}${field}</div>` : '';
				} ).join( '' )
				previewString = previewString.replaceAll( '$$value', `<div class="tap-preview-array">${value}</div>` )
			} else {
				previewString = previewString.replaceAll( '$$value', `<div class="tap-preview-value ${isDynamic( value ) ? 'tap-flex' : ''}">${isDynamic( value ) ? dbIcon : ''}${value}</div>` );
			}

			previewString = previewString.replaceAll( '$$length', `<div class="tap-preview-value">${valueLength}</div>` );
		} else {
			previewString = '';
		}
	}


	return previewString;
}

/**
 * Handle multilevel array keys
 * @param key
 * @param type
 * @returns {*}
 */
export function getKeyNicePreview( key, type ) {
	let label = type;
	if ( key && TAPAdmin._.isString( key ) ) {
		if ( key.includes( '[' ) ) {
			label = key.split( '[' ).map( part => {
				part = part.replace( ']', '' ).replace( /[-_]/g, ' ' );
				return ucFirst( part )
			} ).join( ' ' );
		} else {
			label = `${key.replace( /[-_]/g, ' ' ).split( ' ' ).map( part => ucFirst( part ) ).join( ' ' )}`
		}
	}
	return label;
}

export function validateKeyPair( value ) {
	return Array.isArray( value ) && ! value?.some( pair => !! String( pair.value )?.length ^ !! String( pair.key )?.length )
}

/**
 * Validate editor field key input
 * @param key
 * @param regex
 * @returns {*}
 */
export function validateDataKey( key, regex = '[a-zA-Z0-9-_]+(\\[(.*?)\\])*$' ) {
	let isValid = false,
		message = '';
	if ( key ) {
		if ( /\s/g.test( key ) ) {
			message = 'No spaces allowed';
		} else {
			regex = new RegExp( regex, 'g' )
			if ( ! /^[a-zA-Z]/.test( key ) || key.match( regex )?.[ 0 ] !== key ) {
				//just in case we have numbers as keys
				if ( parseInt( key ) == key ) {
					message = '';
					isValid = true;
				} else {
					message = 'No special characters allowed';
				}
			} else {
				isValid = true;
			}
		}
	} else {
		message = 'No key';
	}
	return {isValid, message};
}

export function getItemById( object, id ) {
	let item;
	const getItem = data => {
		Object.keys( data || {} ).forEach( key => {
			if ( data[ key ]?.id && data[ key ].id === id ) {
				item = data[ key ];
			} else if ( Object.keys( data[ key ]?.items || {} )?.length ) {
				getItem( data[ key ].items );
			}
		} )
	};

	getItem( object );
	return item;
}

/**
 * Get data object key and field ID from a string
 * @param value
 * @param dataObjects
 * @returns {{field: string, appID: string}}
 */
export function getFieldInfo( value, dataObjects ) {
	let appID = '',
		dataObjectIndex = 0,
		field = '';

	while ( ! appID && dataObjectIndex < dataObjects.length ) {
		if ( value.startsWith( dataObjects[ dataObjectIndex ] ) ) {
			appID = dataObjects[ dataObjectIndex ];
			field = value.replace( `${appID}/`, '' );
		}
		dataObjectIndex ++;
	}

	return {
		appID,
		field
	};
}

/**
 * Function to detect current browser
 */
export function detectBrowser() {
	const ua = navigator.userAgent;
	let browser = 'unknown';

	if ( /Firefox[\/\s](\d+\.\d+)/.test( ua ) ) {
		browser = 'firefox';
	} else if ( /MSIE (\d+\.\d+);/.test( ua ) ) {
		browser = 'ie';
	} else if ( /Chrome[\/\s](\d+\.\d+)/.test( ua ) ) {
		browser = 'chrome';
	} else if ( /Safari[\/\s](\d+\.\d+)/.test( ua ) ) {
		browser = 'safari';
	} else if ( /Opera[\/\s](\d+\.\d+)/.test( ua ) ) {
		browser = 'opera';
	} else if ( /Edge[\/\s](\d+\.\d+)/.test( ua ) || /Edg[\/\s](\d+\.\d+)/.test( ua ) ) {
		browser = 'edge';
	} else if ( /iPad|iPhone|iPod/.test( ua ) && ! window.MSStream ) {
		browser = 'ios';
	}

	return browser;
}

