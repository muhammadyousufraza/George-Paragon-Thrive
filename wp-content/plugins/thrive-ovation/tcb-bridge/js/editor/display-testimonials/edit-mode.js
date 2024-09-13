/**
 * Edit mode functions that extend/overwrite the post list edit mode functions
 *
 * @type {Object}
 */

const constants = require( './constants' ),
	editMode = TVE.$.extend( true, {}, TVE.PostList.EditMode, {
		/* overwrite the post list identifier */
		identifier: `.${ constants.THRIVE_DISPLAY_TESTIMONIALS_CLASS }`,
		/* overwrite the post list category */
		subElementCategory: 'Ovation Elements',
		/**
		 * Get extra elements that we want to display inside the course list
		 *
		 * @return {string[]}
		 */
		getExtraListElementsTags: () => [ 'divider', 'icon', 'social', 'styledlist', 'rating' ],
		/**
		 * @param {jQuery} $element
		 */
		enterEditMode( $element ) {
			this.beforeEditModeEnter( $element );

			TVE.main.EditMode.enter( false, {
				body_class: 'edit-mode-active display-testimonials-edit-mode', /* add an extra class so we can do some CSS targeting  */
				blur: true,
				element_selectable: false,
				show_overlay: true,
				show_default_message: true,
				can_insert_elements: true,
				states: [],
				callbacks: {
					before_exit: TVE.Views.Controls.DropPanel.close_all,
				},
			} );

			this.afterEditModeEnter();
		},
		/**
		 * General list actions done after entering edit mode
		 */
		afterEditModeEnter() {
			/* call the parent method */
			TVE.PostList.EditMode.afterEditModeEnter.call( this );

			/* make sure we hide all the other sub-elements except for the course-list ones */
			const filterFn = element => element.is_sub_element && element.category !== this.subElementCategory;

			TVE.main.sidebar_toggle_elements( _.map( _.filter( TVE.Elements, element => filterFn( element ) ), element => element.tag ), false );
		},
		/**
		 * @param {jQuery} $element
		 */
		exitEditMode( $element ) {
			this.beforeEditModeExit( $element );

			/* show the icons again */
			setTimeout( () => {
				TVE.ElementEditIcons.show_icons( $element );
			}, 50 );
		},
		isInternalDrag: () => typeof TVE.FLAGS.$dragged_element !== 'undefined' && ! TVE.FLAGS.$dragged_element.static_element,
		/**
		 * Nothing so far, overwrites the parent functionality
		 *
		 * @param  show
		 */
		toggleOnlyListElements( show ) {
		},
	} );

module.exports = editMode;
