/**
 * @param {jQuery} $
 * @param {Object} TCB_Front
 * @return {{initCourseLists: initCourseLists, class: CourseList}}
 */
module.exports = ( $, TCB_Front ) => {
	/**
	 * Initialize all the Display Testimonials
	 */
	const initDisplayTestimonials = () => {
		TCB_Front.$document.ready( function () {
			function tvaDisplayTestimonialsBootstrap() {
				$( '.thrive-display-testimonials' ).each( ( index, element ) => {
					const displayTestimonialsInstance = new DisplayTestimonials( $( element ) );

					displayTestimonialsInstance.renderPagination();
				} );
			}

			setTimeout( tvaDisplayTestimonialsBootstrap );
		} );
	};

	class DisplayTestimonials extends TCB_Front.PostList {
		/**
		 * @param {jQuery} $displayTestimonials
		 */
		constructor( $displayTestimonials ) {
			super( $displayTestimonials );
		}

		/**
		 * @return {DisplayTestimonials}
		 */
		editorInit() {
			TCB_Front.PostList.prototype.editorInit.call( this );

			return this;
		}

		/**
		 * @return {DisplayTestimonials}
		 */
		frontendInit() {
			if ( typeof tvo_display_testimonials_list === 'undefined' ) {
				console.warn( 'Error initializing the display testimonials parameters' );
			} else {
				this.listArgs = tvo_display_testimonials_list.find( displayTestimonials => {
					return this.$element.is( displayTestimonials.identifier );
				} );

				if ( this.listArgs ) {
					this.content = $( `.thrive-display-testimonials-template[data-identifier="${this.listArgs.template}"]` ).html();
				}

				this.frontendMarkupChanges();
			}

			return this;
		}

		/**
		 * Handles the frontend markup changes
		 */
		frontendMarkupChanges() {
			/**
			 * For the empty inline shortcodes we hide the elements and the wrapper if the wrapper contains only that element
			 */
			this.$element.find( '.thrive-shortcode-content:empty' ).each( ( index, element ) => {
				const $text = $( element ).closest( 'p,h2,div.tcb-plain-text' );

				if ( $text.height() === 0 ) {
					$text.hide();
				}

				const $textWrapper = $text.closest( '.thrv_wrapper' );
				if ( $textWrapper.height() === 0 ) {
					$textWrapper.hide();
				}
			} );

			/* we need to treat the website separately because it has a slightly different structure */
			this.$element.find( '.thrive-testimonial-website a:empty' ).each( ( index, element ) => {
				const $text = $( element ).closest( '.thrv_text_element' );

				if ( $text.height() === 0 ) {
					$text.hide();
				}

				const $textWrapper = $text.closest( '.thrive-testimonial-website' );

				if ( $textWrapper.height() === 0 ) {
					$textWrapper.hide();
				}
			} );
		}

		/* Retrieve the pagination instance and render it if it exists */
		renderPagination() {
			this.pagination = this.getPaginationInstance();

			if ( this.pagination ) {
				if ( ! tve_frontend_options.is_editor_page ) {
					this.pagination.addLoadListeners();
				}

				this.pagination.render();
			}

			return this;
		}

		/**
		 * Do an ajax request to get new testimonials.
		 *
		 * @param {Function} callback
		 * @param {Object}   args
		 */
		getItems( callback, args = this.listArgs ) {
			TCB_Front.Utils.restAjax( {
				route: tve_frontend_options.routes.testimonials + '/html',
				data: {
					content: this.content, args: args.query,
					has_pagination: this.pagination ? 1 : 0,
				},
			} ).done( response => {
				if ( typeof callback === 'function' ) {
					callback( response );
				}

				this.initItems();

				/* re-initialize the animation events after loading the new posts */
				TCB_Front.event_triggers( this.$element );
			} ).fail( () => {
				console.warn( 'There was an error and the content could not be loaded.' );
			} );
		}

		/**
		 * @param  ajaxResponse
		 * @param  replace
		 */
		insertItems( ajaxResponse, replace = false ) {
			if ( ajaxResponse.count && ajaxResponse.testimonials ) {
				const $postHtml = this.$element.clone().empty();

				for ( const testimonial in ajaxResponse.testimonials ) {
					if ( ajaxResponse.testimonials.hasOwnProperty( testimonial ) ) {
						$postHtml.append( ajaxResponse.testimonials[ testimonial ] );
					}
				}

				if ( replace ) {
					this.$element.replaceWith( $postHtml );

					/* since we replaced the element, we have to look for it again.
					 * if this is a data-css identifier, modify the identifier so we can search for it
					 */
					if ( this.identifier.includes( 'tve-u-' ) ) {
						this.$element = $( `.thrive-display-testimonials[data-css=${this.identifier}]` );
					} else {
						this.$element = $( this.identifier );
					}

					$( window ).trigger( 'tcb_post_list_after_item_insert' );
				} else if ( this.isCarousel() && this.carousel ) {
					this.$element.find( '.slick-track' ).append( $postHtml.html() );
					this.carousel.initCarousel( true );
				} else {
					this.$element.append( $postHtml.html() );
					/* re-do the masonry */
					this.masonryRedo();
				}

				/* Always update the total post count on instance because it might be altered after a filter */
				if ( ajaxResponse.total_post_count ) {
					this.listArgs.attr.total_post_count = ajaxResponse.total_post_count;
				}
			}
		}
	}

	return {
		'class': DisplayTestimonials,
		initDisplayTestimonials,
	};
};
