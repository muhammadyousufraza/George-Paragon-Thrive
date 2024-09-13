const content = require( './content' ),
	utils = {
		/**
		 * Gets the testimonials for the given query and calls the callback
		 *
		 * @param {Object} query
		 * @param {jQuery} $element
		 */
		applyQuery: ( query, $element = TVE.ActiveElement ) => {
			return new Promise( resolve => {
				const noPostsText = $element.attr( 'data-no_posts_text' ) || query.no_posts_text;

				/* replace double quotes with single ones so we can safely save it in element attribute */
				$element.addClass( 'tve-loading' )
				        .attr( 'data-query', JSON.stringify( query ).replace( /"/g, '\'' ) )
				        .attr( 'data-no_posts_text', noPostsText );

				utils.fetchTestimonials( query, $element, response => {
					$element.removeClass( 'tve-loading' );
					resolve( response );
				} );
			} );
		},
		/**
		 * @param {Object}   query
		 * @param {jQuery}   $element
		 * @param {Function} callback
		 */
		fetchTestimonials: ( query, $element = TVE.ActiveElement, callback = null ) => {
			TVE.$.ajax( {
				url: TVE.CONST.routes.base + '/testimonials/html',
				headers: {
					'X-WP-Nonce': TVE.CONST.rest_nonce,
				},
				/* This is POST instead of GET on purpose (see the explanation in TVE.PostList.utils.fetchPosts) */
				method: 'POST',
				data: {
					args: query,
					has_pagination: $element.attr( 'data-pagination-type' ) === 'none' ? 0 : 1,
				},
				success( response ) {
					/* add the newly retrieved testimonials to the localized object */
					TVE.displayTestimonials.testimonial_shortcodes = Object.assign( TVE.displayTestimonials.testimonial_shortcodes, response.testimonials );

					if ( typeof callback === 'function' ) {
						callback( response );
					}
				},
				// eslint-disable-next-line no-console
				error: console.warn,
			} );
		},
		fetchExistingTestimonials: ( $content = TVE.inner.$body ) => {
			utils.fetchTestimonials( {
				'post__in': utils.getTestimonialIdsFromContent( $content ),
				'posts_per_page': -1,
			}, $content );
		},
		getTestimonialIdsFromContent: ( $content = TVE.inner.$body ) => {
			return _.uniq( $content.find( '.thrive-testimonial-wrapper' ).map( ( index, element ) => parseInt( element.dataset.id ) ) );
		},
		/**
		 * Callback after applying the query. Syncs the testimonial list and updates some controls after fetching new testimonials.
		 *
		 * @param {Object} response
		 * @param {jQuery} $element
		 */
		applyQueryCallback: ( response, $element = TVE.ActiveElement ) => {
			const testimonials = [];

			_.each( response.testimonials, ( testimonial, key ) => {
				testimonials[ parseInt( testimonial.order ) ] = testimonial;
			} );

			/* if there are no testimonials, we add a class that will hide the testimonials inside and just display a text */
			$element.toggleClass( 'empty-list', testimonials.length === 0 );

			if ( testimonials.length ) {
				/* send the exact display testimonials that we want to render, so we can redraw it */
				content.syncDisplayTestimonial( $element.find( TVE.identifier( 'article' ) ).first(), $element, testimonials );

				/* We need to update the control of number of items because it can be changed from the modal */
				TVE.Components.display_testimonials.controls.NumberOfItems.update( $element );
				/* Update the no results message in the component */
				TVE.Components.display_testimonials.$noResultsMessageTextArea.val( $element.attr( 'data-no_posts_text' ) );
			}
		},
		/**
		 * This is called only the first time the element is added.
		 *
		 * @param  template
		 * @param  query
		 * @return {Promise<unknown>}
		 */
		fetchCloudTemplate: ( template, query ) => {
			return new Promise( resolve => {
				TVE.ActiveElement.addClass( 'tve-loading' );

				TVE.$.ajax( {
					url: `${TVE.CONST.routes.base}/testimonials/cloud`,
					headers: {
						'X-WP-Nonce': TVE.CONST.rest_nonce,
					},
					/* This is POST instead of GET on purpose (see the explanation in TVE.PostList.utils.fetchPosts) */
					method: 'POST',
					data: { template, query },
					success( response ) {
						/* fetch the testimonial shortcode data for the testimonials that we have loaded */
						utils.fetchExistingTestimonials( TVE.inner_$( response.content ) );

						const modal = TVE.modal_open( 'display-testimonials', 'currentInstance' );

						modal.applyTo = TVE.ActiveElement;
						modal.apply_cloud_template( response );

						resolve( response );
					},
					// eslint-disable-next-line no-console
					error: console.warn,
				} )
				;
			} );
		},
		/**
		 * Render the actual value of the shortcode after it was inserted in the editor instead of using the standard name ( 'new testimonial 2' instead of 'Title', etc )
		 *
		 * @param {Object} shortcodeData
		 * @return {string} shortcode string
		 */
		getShortcodeName: shortcodeData => {
			let shortcodeName;
			const $element = TVE.ActiveElement,
				key = shortcodeData.key;

			/* all the other shortcodes ( mostly ovation list shortcodes ) are processed here */
			const ID = utils.getArticleID( $element ),
				testimonialData = TVE.displayTestimonials.testimonial_shortcodes[ ID ];

			if ( ! _.isEmpty( testimonialData ) ) {
				const newContent = testimonialData[ key ],
					$newContent = TVE.inner_$( newContent );

				if ( $newContent ) {
					shortcodeName = $newContent.text();
				}
				$newContent.remove();
			}

			return shortcodeName;
		},

		/**
		 * Returns the id of the article which contains the $element sent as a parameter.
		 *
		 * @param {jQuery} $element
		 * @return {number} post id
		 */
		getArticleID( $element = TVE.ActiveElement ) {
			return parseInt( $element.closest( TVE.identifier( 'article' ) ).attr( 'data-id' ) ) || 0;
		},
		/**
		 * Compute the query based on the types and items from modal
		 *
		 * @param {Array}   items    - testimonials or tags
		 * @param {boolean} isStatic - true is static, if it's static items are testimonials IDs, if it's dynamic items are tags
		 * @param {Object}  ordering
		 * @param {number}  setId
		 */
		computeQuery( items = [], isStatic = true, ordering = {}, setId ) {
			const queryFromElement = TVE.PostList.utils.readQueryFromElement(),
				attrToDelete = [ 'post__in', 'tax_query', 'set_id', 'orderby', 'order', 'offset' ],
				$element = TVE.ActiveElement,
				hasPagination = $element.attr( 'data-pagination-type' ) && $element.attr( 'data-pagination-type' ) !== 'none';

			/* when pagination is active, we don't want to remove the posts_per_page parameter because it might still be used afterwards */
			if ( ! hasPagination ) {
				attrToDelete.push( 'posts_per_page' );
			}

			/* delete the old attributes */
			attrToDelete.forEach( key => {
				delete queryFromElement[ key ];
			} );

			/* if a set is active, all we have to do is attach the ID and return it */
			if ( typeof setId !== 'undefined' ) {
				queryFromElement.set_id = setId;
				return queryFromElement;
			}

			if ( isStatic ) {
				queryFromElement.post__in = items;
				queryFromElement.orderby = ordering.type === 'manual' ? 'post__in' : 'date';
			} else {
				queryFromElement.tax_query = [
					{
						taxonomy: 'tvo_tags',
						field: 'term_id',
						terms: items,
					},
				];
				queryFromElement.orderby = ordering.type;
			}

			/* Regardless of the type, add the ordering */
			queryFromElement.order = ordering.direction;

			if ( ordering.type === 'manual' ) {
				/* if the pagination is active, don't mess with the posts_per_page value, otherwise, use the length of the array as the item count */
				if ( ! hasPagination ) {
					queryFromElement.posts_per_page = queryFromElement.post__in.length;
				}
			} else {
				queryFromElement.posts_per_page = ordering.number_of_items;
				if ( ! hasPagination ) {
					queryFromElement.offset = ordering.offset;
				}
			}

			return queryFromElement;
		},
		parseQuery( query ) {
			const type = query.post__in ? 'static' : 'dynamic',
				items = type === 'static' ? query.post__in : query.tax_query[ 0 ].terms,
				ordering = {
					type: query.orderby === 'post__in' ? 'manual' : query.orderby, /* this doesn't exist after reloading */
					direction: query.order,
					number_of_items: query.posts_per_page, /* this doesn't exist after reloading */
					offset: query.offset ? query.offset : 0,
				};

			return { type, ordering, items };
		},
		/**
		 * Callback for the 'tcb.get_dropzone_target' and 'tcb.change_target' filters.
		 * If we are in the testimonial list edit mode, return the last child of the first testimonial.
		 *
		 * @param  $target
		 * @return {*}
		 */
		getDropzoneTarget: $target => {
			/* check if the element was clicked in the sidebar */
			if ( typeof TVE.FLAGS.$dragged_element !== 'undefined' ) {
				return $target;
			}

			if ( TVE.main.EditMode.in_edit() ) {
				if ( TVE.main.EditMode.element() && TVE.main.EditMode.element().is( TVE.identifier( 'display_testimonials' ) ) ) {
					const $firstTestimonial = TVE.main.EditMode.element().find( '.thrive-testimonial-wrapper' ).first();

					$target = $firstTestimonial.length ? $firstTestimonial.children().last() : $target;
				}
			}

			return $target;
		},
		isInsideTestimonialsList: ( $element = TVE.ActiveElement ) => {
			return $element.closest( TVE.identifier( 'display_testimonials' ) ).length > 0;
		},
		/**
		 * Get the display testimonials selector for the given  display testimonials / element inside the display testimonials
		 *
		 * @param {jQuery} $element
		 * @return {string} selector
		 */
		getDisplayTestimonialsSelector( $element ) {
			const $displayTestimonials = $element.closest( TVE.identifier( 'display_testimonials' ) );
			let displayTestimonialsSelector;

			/* this is the main display testimonials element from the page */
			if ( $displayTestimonials.attr( 'data-selector' ) ) {
				displayTestimonialsSelector = $displayTestimonials.attr( 'data-selector' );
			} else {
				const dataCssID = TVE.CSS_Rule_Cache.uniq_id( $displayTestimonials );

				/* We can have more than one post-list on the page, so we use the data-css to identify them. */
				displayTestimonialsSelector = '[data-css="' + dataCssID + '"]' + TVE.identifier( 'display_testimonials' );
			}

			return displayTestimonialsSelector + ' ';
		},
	};

module.exports = utils;
