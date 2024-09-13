const content = require( '../content' ),
	utils = require( '../utils' ),
	editMode = require( '../edit-mode' ),
	actions = {
		/**
		 * This is used to hide the Ovation group from the Froala dropdown when not in edit mode
		 *
		 * @param {jQuery} $popup
		 */
		'tcb.froala.before_shortcode_popup': $popup => {
			const $groupList = $popup.find( '#fr-dropdown-categories-list' );

			$groupList.find( 'option[value="Ovation"]' ).toggle( !! editMode.isInsideEditMode() );
		},
		/**
		 * Move data-css from anchor to shortcode for website link
		 *
		 * @param {Object} shortcode
		 */
		'tve.froala.inline.shortcodes.before.save': shortcode => {
			if ( shortcode.getAttribute( 'data-shortcode' ) === 'tvo_testimonial_website' ) {
				shortcode.setAttribute( 'data-attr-css', jQuery( shortcode ).find( 'a' ).attr( 'data-css' ) || '' );
			}
		},
	},
	filters = {
		/**
		 * This is used to display the shortcodes in the dynamic area from Froala
		 *
		 * @param {Array} shortcodesGroupOrderMap
		 * @return {Array} array containing the shortcodes group with Ovation added
		 */
		'tcb.inline_shortcodes.shortcode_group': shortcodesGroupOrderMap => {
			return [ ...shortcodesGroupOrderMap, 'Ovation' ];
		},

		/**
		 *  Before shortcode insertion we do some processing of the data which will later shape the shortcode element ( shortcodeData structure can be seen below )
		 *
		 *    shortcodeData = {
		 *		key: shortcodeKey,
		 *		extra_key: shortcodeExtraKey,
		 *		name: name,
		 *      shortcodeName: name,
		 *		class: SHORTCODE_CLASS,
		 *		content_class: SHORTCODE_CONTENT_CLASS,
		 *	    configOptions: [   |
		 *			{              |
		 *				key: '',   | used for inputs that require further configuration
		 *				value: '', | these will generate inputs inside the froala shortcode dropdown
		 *			}              |
		 *		]                  |
		 *		options: [         |
		 *			{              |
		 *				key: '',   | used for additional information passed  through the shortcode itself
		 *				value: '', | these don't do much but will be part of the final shortcode structure
		 *			}              |
		 *		]                  |
		 *	};
		 *
		 * @param {Array} shortcodeData
		 * @return {Array} shortcodeData
		 */
		'tcb.inline_shortcodes.insert': shortcodeData => {
			shortcodeData.options.push( { key: 'inline', value: 1 } );

			/* extra_key is specific to non-post-list shortcodes, so we avoid doing the next steps */
			if ( shortcodeData.extra_key ) {
				return shortcodeData;
			}

			const shortcodeName = utils.getShortcodeName( shortcodeData );

			if ( shortcodeName ) {
				shortcodeData.name = shortcodeName;
			}

			return shortcodeData;
		},
		/**
		 * This is used to alter the default value of the shortcode
		 * `
		 *
		 * @param  shortcodeValue
		 * @param  shortcodeData
		 * @return {*}
		 */
		'tcb.inline_shortcodes.shortcode_value': ( shortcodeValue, shortcodeData ) => {
			if ( shortcodeData.key.includes( 'tvo_testimonial' ) && shortcodeData.configOptions[ 0 ] && shortcodeData.configOptions[ 0 ].value ) {
				const actualValue = TVE.inner_$( TVE.displayTestimonials.testimonial_shortcodes[ utils.getArticleID() ][ shortcodeData.key ] )[ 0 ].innerText;
				if ( ! actualValue.length ) {
					shortcodeValue = shortcodeData.configOptions[ 0 ].value;
				}
			}

			return shortcodeValue;
		},
		/**
		 * After an inline shortcode is inserted check if a display testimonials sync is needed.
		 * Also check if we need to apply the date formatting filter.
		 *
		 * @param {jQuery} $element
		 * @param {Array}  shortcodeData
		 * @return {jQuery} $element
		 */
		'tcb.inline_shortcodes.afterInsert': ( $element, shortcodeData ) => {
			const websiteShortcode = 'tvo_testimonial_website',
				key = shortcodeData.key;

			if ( key.includes( 'tvo_testimonial' ) ) {
				if ( key === websiteShortcode ) {
					const $shortcodeContent = $element.find( '.thrive-shortcode-content' ),
						ID = utils.getArticleID( $element ),
						testimonialData = TVE.displayTestimonials.testimonial_shortcodes[ ID ],

						/* replace the dynamic content with data from the posts object */
						$newContent = TVE.inner_$( testimonialData[ key ] );

					if ( $newContent.length ) {
						$shortcodeContent.html( `<a href="#">${$newContent.text()}</a>` );
					}

					$newContent.remove();
				}

				content.checkForDisplayTestimonialsSync( $element.closest( TVE.identifier( 'article' ) ) );
			}
			return $element;
		},

		/**
		 * If the shortcode is a display testimonials shortcode, we display nothing when it's empty
		 * The default behaviour for froala inline shortcode is displaying the shortcode name in editor
		 *
		 * @param {string} defaultEmptyValue
		 * @param {jQuery} $element
		 * @return {string} defaultEmptyValue
		 */
		'tve.froala.shortcode.default_empty_value': ( defaultEmptyValue, $element ) => {
			const shortcode = $element.attr( 'data-shortcode' );

			if ( shortcode && shortcode.includes( 'tvo_testimonial' ) ) {
				defaultEmptyValue = '';
			}

			return defaultEmptyValue;
		},
	};

module.exports = { actions, filters };
