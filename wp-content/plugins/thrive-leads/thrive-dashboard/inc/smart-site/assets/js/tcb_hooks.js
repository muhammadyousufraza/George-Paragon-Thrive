var TVD_SS = TVD_SS || {};
// phpcs:disable
( function ( $ ) {
	var userDataWithLink = [ 'username', 'first_name', 'last_name' ];

	if ( typeof TVE !== 'undefined' ) {
		TVE.add_filter( 'tcb.inline_shortcodes.insert', tvdShapeShortcode );
		TVE.add_filter( 'tve.froala.shortcode.init', tvdBackwardsCompat );
		TVE.add_filter( 'tve.shortcode.options.html', tvdShortcodeHtmlOptions );
		TVE.add_action( 'tcb.froala.after_shortcode_select', tvdShortcodeSelect );
		TVE.add_filter( 'tcb.inline_shortcodes.shortcode_value', tvdBeforeInsert );
	}

	/**
	 * Change the content on insert of inline-shortcode
	 * @param content - to be inserted
	 * @param shortcodeData
	 * @returns {string}
	 */
	function tvdBeforeInsert( content, shortcodeData ) {
		/* wrap the content so it behaves like a link */
		if ( shortcodeData.key === 'thrv_dynamic_data_user' && userDataWithLink.includes( shortcodeData.extra_key ) ) {
			const link = shortcodeData.configOptions.find( opt => opt.key === 'link' );
			if ( link && link.value === '1' ) {
				content = '<a href="#">' + content + '</a>'
			}
		} else if ( shortcodeData.key === 'thrive_global_fields' ) {
			if ( ! content.includes( 'thrive-shortcode-notice' ) ) {
				const call = shortcodeData.configOptions.find( opt => opt.key === 'enable_call' );
				const mail = shortcodeData.configOptions.find( opt => opt.key === 'enable_email' );
				if ( call && call.value === '1' ) {
					content = `<a href="tel:${content}">${content}</a>`
				} else if ( mail && mail.value === '1' ) {
					content = `<a href="mailto:${content}">${content}</a>`
				}
			}
		}
		return content;
	}

	/**
	 * Change the select template for Global fields
	 * @param html
	 * @param key
	 * @returns {*}
	 */
	function tvdShortcodeHtmlOptions( html, key ) {
		if ( key === 'Global fields' ) {
			html = TVE.tpl( 'inline/shortcodes/global-fields-options' )( {
				shortcodes: TVE.CONST.inline_shortcodes[ key ]
			} );
		}

		return html;
	}

	/**
	 * Custom handling for global fields inline shortcodes select
	 * @param $FE
	 * @param data
	 */
	function tvdShortcodeSelect( $FE, data ) {
		if ( data && data.selectedData ) {
			var selectedData = data.selectedData;

			if ( selectedData.key === 'thrive_global_fields' ) {
				var selectValue;
				if ( ! selectedData.configOptions ) {
					selectValue = $FE.find( '#fr-dropdown-shortcode-list' ).find( 'option:selected' ).attr( 'data-field-value' );
				} else {
					selectValue = selectedData.configOptions.find( function ( object ) {
						return object.key === 'id';
					} ).value;
					/* Set the proper value for the first select on shortcode is already inserted */
					$FE.find( `#fr-dropdown-shortcode-list option[data-field-value="${selectValue}"]` ).prop( 'selected', true );
				}
				/* hide the actual shortcode select but set the new value to make sure that shortcode is properly generated */
				$FE.find( '#fr-dropdown-list-id' ).val( selectValue ).trigger( 'change' ).hide();
				$FE.find( 'label[for="fr-dropdown-list-id"]' ).hide();
			}
		}
	}

	/**
	 * Replace old username, first_name, last_name shortcode with the new ones and make sure the options set are kept
	 * @param {HTMLElement} shortcode
	 * @returns {HTMLElement}
	 */
	function tvdBackwardsCompat( shortcode ) {
		var shortcodeName = shortcode.getAttribute( 'data-shortcode' ),
			compatShortcode,
			cfg = {
				key: 'thrv_dynamic_data_user'
			};
		if ( [ 'tcb_username_field', 'tcb_first_name_field', 'tcb_last_name_field' ].includes( shortcodeName ) ) {
			shortcode.setAttribute( 'data-attr-link', shortcode.getAttribute( 'data-attr-link_to_profile' ) );
			shortcode.setAttribute( 'data-attr-default', shortcode.getAttribute( 'data-attr-text_not_logged' ) );
			shortcode.removeAttribute( 'data-attr-link_to_profile' );
			shortcode.removeAttribute( 'data-attr-text_not_logged' );
			switch ( shortcodeName ) {
				case 'tcb_username_field':
					cfg.extra_key = 'username';
					break;
				case 'tcb_first_name_field':
					cfg.extra_key = 'first_name';
					break;
				case 'tcb_last_name_field':
					cfg.extra_key = 'last_name';
					break;
				default:
					break;
			}
			shortcode.setAttribute( 'data-attr-id', cfg.extra_key );
			shortcode.setAttribute( 'data-extra_key', cfg.extra_key );

			compatShortcode = TVE.inlineShortcodeFn.getShortcodeByValue( cfg );

			if ( compatShortcode ) {
				shortcode.setAttribute( 'data-shortcode', compatShortcode.config.value );
				shortcode.setAttribute( 'data-shortcode-name', compatShortcode.config.option );
				shortcode.innerText = compatShortcode.config.input.id.real_data[ cfg.extra_key ];
			}
		}

		return shortcode;
	}


	/**
	 * Before shortcode insertion we do some processing of the data which will later shape the shortcode element ( shortcodeData structure can be seen bellow )
	 *
	 * @param shortcodeData
	 * @returns {*}
	 */
	//	shortcodeData = {
	//		key: shortcode_key,
	//		extra_key: shortcode_extra_key,
	//		name: name,
	//      shortcodeName: name,
	//		class: SHORTCODE_CLASS,
	//		content_class: SHORTCODE_CONTENT_CLASS,
	//	    configOptions: [        )
	//			{                   )
	//				key: '',        )        used for inputs that require further configuration
	//				value: '',      )        these will generate inputs inside the froala shortcode dropdown
	//			}                   )
	//		]                       )
	//		options: [                  ]
	//			{                       ]
	//				key: '',            ]   used for additional information passed  through the shortcode itself
	//				value: '',          ]   these don't do much but will b part of the final shortcode structure
	//			}                       ]
	//		]                           ]
	//	};
	function tvdShapeShortcode( shortcodeData ) {
		var shortcode, name, shortcodeName;

		_.each( TVE.CONST.inline_shortcodes, function ( group, group_name ) {
			if ( ! shortcode ) {
				shortcode = group.find( function ( item ) {
					return shortcodeData.extra_key && item.extra_param === shortcodeData.extra_key;
				} );
			}
		} );
		if ( shortcode ) {

			shortcodeName = shortcode.input.id.value[ shortcodeData.configOptions.find( function ( item ) {
				return item.key === 'id';
			} ).value ];

			if ( shortcodeName ) {
				shortcodeData.shortcodeName = '[' + shortcodeData.shortcodeName + '] ' + shortcodeName;
				shortcodeData.name = shortcodeData.shortcodeName;
			}

			name = shortcode.input.id.real_data[ shortcodeData.configOptions.find( function ( item ) {
				return item.key === 'id';
			} ).value ];

			if ( name ) {
				shortcodeData.name = name;
			}

			var multiline = shortcodeData.configOptions.find( function ( item ) {
				return item.key === 'multiline';
			} );
			if ( multiline && multiline.value ) {
				shortcodeData.name = shortcodeData.name.split( ',' ).join( '<br/>' );
			}

			var defaultData = shortcodeData.configOptions.filter( opt => opt.key === 'default' )[ 0 ];
			if ( defaultData && defaultData.value &&
			     ( shortcodeData.key === 'thrv_dynamic_data_request' /* url querystrings cookie ...*/
			       || ( shortcodeData.key === 'thrv_dynamic_data_user' && shortcodeData.name.toLowerCase().includes( 'wordpress' ) ) ) /* user data not defined*/
			) {
				shortcodeData.name = defaultData.value;

			}
		}

		return shortcodeData;
	}
} )( jQuery );
