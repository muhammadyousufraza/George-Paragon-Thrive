const actions = {
		/**
		 * Open the 'second step' of the Display Testimonials modal
		 *
		 * @param {Object} modalInstance
		 */
		'tcb.cloud_templates_categories.second_step': modalInstance => {
			if ( modalInstance.collectionName === 'display_testimonials-templates' ) {
				modalInstance.applyTo.attr( 'data-cloud-template', modalInstance.selected );
				TVE.Components.display_testimonials.filterTestimonials( modalInstance, true );
			}
		},

		/**
		 * After importing a cloud template, we apply the carousel layout if needed
		 *
		 * @param {jQuery} $element
		 */
		'tcb_after_cloud_template_css_inserted': $element => {
			if ( $element.is( TVE.identifier( 'display_testimonials' ) ) ) {
				/* If we imported a display testimonial with a carousel, we need to reinitialize the carousel */
				if ( $element.attr( 'data-type' ) === 'carousel' ) {
					const $carouselElement = $element.find( '.tcb-carousel-container' );

					$carouselElement.attr( 'data-carousel-settings', decodeURI( $carouselElement.attr( 'data-carousel-settings' ) ) );
				}

				TVE.PostList.layout.applyLayout( $element );
			}
		},
	},
	filters = {
		'tcb.cloud_templates.change_main_wrapper_data_css': elements => [ ...elements, ...[ 'display_testimonials' ] ],
		/**
		 * Add the display testimonials to the list of modals with categories
		 *
		 * @param {Array} modalsWithCategories
		 * @return {Array} list of modals with categories
		 */
		'tcb.modals.modals_with_categories': modalsWithCategories => {
			if ( ! modalsWithCategories.find( modal => modal === 'display-testimonials' ) ) {
				modalsWithCategories.push( 'display-testimonials' );
			}

			return modalsWithCategories;
		},

		/**
		 * Add the display testimonials modal file to the list of modals with categories
		 *
		 * @param {Array} modalsList
		 * @return {Array} list of files for modals with categories
		 */
		'tcb.modals.modals_list': modalsList => {
			modalsList[ 'display-testimonials' ] = require( '../display-testimonials-cloud-modal' );
			return modalsList;
		},

		/**
		 * Use the categories modal for Display Testimonials Cloud Templates
		 *
		 * @param {string} modal    identifier of the modal
		 * @param {Object} $element element for which we open the modal
		 * @return {string} identifier for the modal to be opened
		 */
		'tve.cloud_templates.modal': ( modal, $element ) => {
			if ( $element.is( TVE.identifier( 'display_testimonials' ) ) ) {
				modal = 'display-testimonials';
			}

			return modal;
		},

		/**
		 * Whether the Cloud Templates Categories modal should have a 'second step'
		 *
		 * @param {boolean} hasTwoSteps   if the modal has 'two steps'
		 * @param {Object}  modalInstance
		 * @return {boolean} if the modal has 'two steps'
		 */
		'tcb.cloud_templates_categories.has_two_steps': ( hasTwoSteps, modalInstance ) => {
			if ( modalInstance.applyTo.hasClass( 'tcb-elem-placeholder' ) && modalInstance.collectionName === 'display_testimonials-templates' ) {
				hasTwoSteps = true;
			}

			return hasTwoSteps;
		},
	};

module.exports = { actions, filters };
