const content = require( '../content' ),
	editMode = require( '../edit-mode' ),
	utils = require( '../utils' ),
	constants = require( '../constants' ),
	dynamicImageElementCallback = imageType => {
		const testimonial = TVE.displayTestimonials.testimonial_shortcodes[ utils.getArticleID() ];

		if ( testimonial ) {
			TVE.ActiveElement.attr( {
				'data-dynamic': imageType,
			} ).find( '.tve_image' ).attr( {
				src: TVE.Components.image.controls.ImagePicker.picker.clean_url( testimonial.image ),
				'data-d-f': imageType,
			} ).removeAttr( 'srcset' );

			content.checkForDisplayTestimonialsSync( TVE.ActiveElement.closest( TVE.identifier( 'article' ) ) );
		}
	},
	actions = {},
	filters = {

		/**
		 * Retuns the dynamic image url for the testimonial
		 *
		 * @param {string} url
		 * @return {string} url to be used for the dynamic image
		 */
		'tcb.dynamic_image.change_url': url => {
			if ( editMode.isInsideEditMode() ) {
				url = `var(${TVE.CONST.dynamic_background_url_prefix}testimonial-image)`;
			}

			return url;
		},

		/**
		 * Show the dynamic image option inside the background component
		 *
		 * @param {boolean} show
		 *
		 * @return {boolean} show tab
		 */
		'tcb.dynamic_image.show_tab': show => {
			if ( editMode.isInsideEditMode() ) {
				show = true;
			}

			return show;
		},

		/**
		 * Called from the image picker lightbox when "Use Dynamic Image" button is pressed
		 *
		 * @param {Object} dynamicSourceObj
		 * @return {Object} returns the course list item dynamic image
		 */
		'tcb.image_picker.dynamic_source': dynamicSourceObj => {
			if ( editMode.isInsideEditMode() ) {
				const testimonial = TVE.displayTestimonials.testimonial_shortcodes[ utils.getArticleID() ];

				if ( testimonial ) {
					TVE.ActiveElement.attr( { 'data-dynamic': 'featured' } );
					dynamicSourceObj.url = TVE.Components.image.controls.ImagePicker.picker.clean_url( testimonial.image );
				}
			}

			return dynamicSourceObj;
		},

		/**
		 * Rename 'Featured Image' into 'Testimonial Image'
		 *
		 * @param {Object} options
		 * @return {Object} options
		 */
		'tcb.dynamic_image.options': options => {
			if ( editMode.isInsideEditMode() ) {
				options.featured.label = constants.thrive_display_testimonial_image_label;
			}

			return options;
		},

		/**
		 * Overrides the callback for dynamic image if the user has a dynamic image inside the course list
		 *
		 * @param {Function} callback
		 *
		 * @return {Function} Callback Function
		 */
		'tcb.dynamic_image_input.callback': callback => {
			if ( editMode.isInsideEditMode() ) {
				callback = dynamicImageElementCallback;
			}

			return callback;
		},

		/**
		 * Parse the mainDropdownValues and returns a filtered list
		 *
		 * @param {Object} mainDropdownValues
		 * @param {string} elementType
		 * @return {Object} returns a filtered list of mainDropdownValues needed for Display Testimonials element
		 */
		'tcb.custom-fields.main-dropdown-values': ( mainDropdownValues, elementType ) => {
			if ( editMode.isInsideEditMode() && elementType === 'image' ) {
				mainDropdownValues = _.pick( mainDropdownValues, [ '', 'featured' ] );

				/* Override the label names */
				mainDropdownValues.featured = constants.thrive_display_testimonial_image_label;
			}
			return mainDropdownValues;
		},

		/**
		 * Hide the featured image size select inside the Display Testimonials edit mode
		 *
		 * @param {boolean} show
		 * @return {boolean} show
		 */
		'tcb.dynamic_image.show_sizes_select': show => {
			if ( editMode.isInsideEditMode() ) {
				show = false;
			}

			return show;
		},
	};

module.exports = { actions, filters };
