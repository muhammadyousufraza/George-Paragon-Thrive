( function ( $ ) {

	const Constants = require( './constants' ),
		iconStyles = [ 'solid', 'outlined', 'duotone', 'brands', 'light' ],
		listZWS = [
			'\u200b', //ZERO_WIDTH_SPACE
			'\u200c', //ZERO_WIDTH_NON_JOINER
			'\u200d',//ZERO_WIDTH_JOINER
			'\u200e', //LEFT_TO_RIGHT_MARK
			'\u200f', //RIGHT_TO_LEFT_MARK
			'\ufeff', //ZERO_WIDTH_NON_BREAK
		];
	const utils = {},
		isProFA = () => {
			return !! window.FontAwesomeKitConfig && FontAwesomeKitConfig.license === 'pro';
		};

	utils.getStyles = () => {
		let styles = '';

		iconStyles.forEach( style => {
			let hasIcons = false;

			Object.keys( Constants ).forEach( pack => {
				if ( ! hasIcons ) {
					let packData = Constants[ pack ];
					hasIcons = !! packData[ style ].icons.length;
					if ( ! hasIcons && pack === 'fontawesome' && isProFA() ) {
						hasIcons = !! packData.pro[ style ].icons.length;
					}
				}
			} );

			if ( hasIcons ) {
				styles += `<div class="td-icon-pack click mt-5" data-fn="filterByStyle" data-style="${style}"><span>${style}</span></div>`
			}
		} );
		return styles;
	};

	utils.getPacks = () => {
		return Object.keys( Constants );
	};

	utils.encode = message => {
		if ( 'string' !== typeof message ) {
			throw new TypeError( 'Cannot encode ' + typeof message + 's!' );
		}

		if ( message.length === 0 ) {
			return '';
		}
		let encoded = '';

		for ( let i = 0; i < message.length; i++ ) {
			encoded += `${message.charAt( i )}${listZWS[ i % listZWS.length ]}`;
		}
		return encoded;
	};

	utils.getCategories = () => {
		let categories = '';
		utils.getPacks().forEach( pack => {
			categories += `<div class="td-icon-pack click mt-5" data-fn="filterByPack" data-pack="${pack}"><span>${pack === 'custom' ? 'Custom icons' : Constants[ pack ].getLabel()}</span></div>`
		} );

		return categories;
	};


	utils.getIconsByPack = ( pack = 'fontawesome' ) => {
		return utils.getAllIcons( utils.getPacks().filter( e => e !== pack ) )
	};

	utils.getAllIcons = ( excludedPack = [] ) => {
		let items = [];
		Object.keys( Constants ).forEach( pack => {
			let icons = Constants[ pack ];
			if ( ! excludedPack.includes( pack ) ) {
				Object.keys( icons.styleToPrefix ).forEach( iconStyle => {
					items = [ ...items, ...utils.getIconsByStyle( iconStyle, pack ) ];
				} );
			}
		} );

		return items;
	};

	utils.getIconsByStyle = ( iconStyle = 'solid', iconPack ) => {
		let items = [];

		let sourcePacks;
		if ( iconPack ) {
			sourcePacks = [ iconPack ];
		} else {
			sourcePacks = Object.keys( Constants )
		}

		sourcePacks.forEach( pack => {
			let packData = Constants[ pack ],
				icons = packData[ iconStyle ].icons;
			if ( pack === 'fontawesome' && isProFA() ) {
				icons = [ ...icons, ...packData.pro[ iconStyle ].icons ]
			}

			icons.forEach( icon => {
				items.push( `<div class="td-icon click tvd-build-svg ${icon}-${iconStyle}" data-filter="${icon}" title="${icon}" data-style="${iconStyle}" data-type="${pack}" data-fn="icon_click" data-tags="${packData.tags[ icon ] || ''}"><div class="td-icons-wrapper">${packData.render( packData.styleToPrefix[ iconStyle ], icon, iconStyle )}</div></div>` )
			} );
		} );

		return items;
	};

	utils.getIconsByTag = ( tag = 'brand', pack = 'material' ) => {
		let packIcons = utils.getIconsByPack( pack );

		packIcons = packIcons.filter( icon => $( icon ).attr( 'data-tags' ).includes( tag ) );

		return packIcons;
	};


	utils.setKit = ( src = '' ) => {
		if ( ! src ) {
			delete window.FontAwesomeKitConfig;
		}
		TVE_Dash_Const.tvd_fa_kit = src;
		$.ajax( {
			type: "post",
			dataType: "json",
			url: ajaxurl,
			data: {
				_wpnonce: TVE_Dash_Const.nonce,
				action: 'tve_dash_backend_ajax',
				route: 'saveFaKit',
				option_name: 'tvd_fa_kit',
				option_value: src
			}
		} )
			.success( () => {
				if ( src ) {
					TVE_Dash.success( TVE_Dash_Const.translations.ImportedKit )
				} else {
					TVE_Dash.success( TVE_Dash_Const.translations.RemovedKit )
				}
			} );
	};

	utils._loadScript = ( src, saveOption = true, onloadCallback = $.noop, failCallback = $.noop ) => {
		const script = document.createElement( 'script' ),
			onFail = ( callback = true ) => {
				delete window.FontAwesomeKitConfig;
				utils.setKit();
				if ( callback ) {
					failCallback();
				}
			};
		script.onload = function () {
			if ( saveOption ) {
				if ( isProFA() ) {
					utils.setKit( src );
				} else {
					onFail( false );
				}
			}
			onloadCallback();
		};
		script.onerror = onFail;
		script.src = src;
		script.crossOrigin = 'anonymous';

		document.head.appendChild( script )
	};
	module.exports = utils;

} )
( jQuery );
