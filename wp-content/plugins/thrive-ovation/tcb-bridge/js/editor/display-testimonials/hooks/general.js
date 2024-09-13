const content = require( '../content' ),
	editMode = require( '../edit-mode' ),
	constants = require( '../constants' ),
	Utils = require( '../utils' ),
	DisplayTestimonialSubElement = require( '../display-testimonials-sub-element' ),
	actions = {
		/**
		 * After the lazy load is done, we initialize the testimonial and set data
		 *
		 * @param {Object} response
		 */
		'tve.lazyload.done': response => {
			TVE.displayTestimonials.testimonial_shortcodes = response.testimonial_shortcodes ? response.testimonial_shortcodes : {};
			TVE.displayTestimonials.has_at_least_one_testimonial = response.has_at_least_one_testimonial;

			/* it's not worth loading the sets on demand, since the amount of data is kind of chill */
			TVE.displayTestimonials.sets = response.sets;
		},
		/**
		 * Include the Display Testimonials sub-elements renderers.
		 */
		'tcb-ready': () => {
			const ovationRenders = {
				display_testimonial_title: { key: 'tvo_testimonial_title', class: 'thrive-testimonial-title' },
				display_testimonial_content: { key: 'tvo_testimonial_content', class: 'thrive-testimonial-content' },
				display_testimonial_role: { key: 'tvo_testimonial_role', class: 'thrive-testimonial-role' },
				display_testimonial_author: { key: 'tvo_testimonial_author', class: 'thrive-testimonial-author' },
				display_testimonial_image: { key: 'tvo_testimonial_image', class: 'thrive-testimonial-image' },
				display_testimonial_website: { key: 'tvo_testimonial_website', class: 'thrive-testimonial-website' },
			};

			_.each( ovationRenders, ( attributes, renderName ) => {
				TVE.renderers[ renderName ] = new DisplayTestimonialSubElement( { key: attributes.key, className: attributes.class } );
			} );
		},
		/**
		 * Action triggered after an element has been added to the page ( all cases: by drag and drop or click )
		 * When we drop an element, we check if it's inside an article(testimonial), because if it is, we have to synchronize the whole display testimonials list
		 *
		 * @param {jQuery} $element
		 */
		'tcb.after-insert': $element => {
			if ( $element.length > 1 ) {
				$element = $element.first();
			}

			/* find the element to which this content belongs */
			const element = TVE.Elements[ TVE._type( $element ) ];

			if ( ! element ) {
				console.warn( 'Corresponding element not found inside TVE.Elements for:', $element );
				return;
			}

			/* find the article in which the content was inserted */
			const $article = $element.closest( TVE.identifier( 'article' ) );

			/* only do stuff if a parent article exists */
			if ( $article.length ) {
				/* sync the whole display testimonial ( except this article ) ( mirror the newly added data from this article to all the articles ) */
				content.checkForDisplayTestimonialsSync( $article );
			}

			/* this is done only if there's an element that extends the display testimonials and is using masonry in the page */
			setTimeout( () => {
				TVE.PostList.layout.masonryRedo();
			}, 100 );
		},
		/**
		 * When exiting edit mode, hide display testimonials elements and focus on the current element.
		 *
		 * @param {jQuery} $element
		 */
		'tcb.edit_mode.exit': $element => {
			if ( $element.is( TVE.identifier( 'display_testimonials' ) ) ) {
				editMode.exitEditMode( $element );
			}
		},
		/**
		 * If the element that was removed was from an article, we synchronize the templates.
		 *
		 * @param {jQuery} $element
		 */
		'tcb.element.remove': $element => {
			const identifier = TVE.identifier( 'display_testimonials' );
			if ( TVE.displayTestimonials.$articleToSync ) {
				content.checkForDisplayTestimonialsSync( TVE.displayTestimonials.$articleToSync );
				delete TVE.displayTestimonials.$articleToSync;
			}

			/* If the element is a Symbol, find the inside display testimonials */
			if ( $element.hasClass( 'thrv_symbol' ) ) {
				const $displayTestimonials = $element.find( identifier );

				$element = $displayTestimonials.length ? $displayTestimonials : $element;
			}

			if ( $element.is( identifier ) ) {
				/* also delete the Pagination element, if we find one or another for this display testimonials */
				TVE.PostList.utils.removeElementsWithPostList( $element );
			}

			TVE.PostList.layout.masonryRedo();
		},
		'tcb.insert_content_template': Utils.fetchExistingTestimonials,
		'tve.imported_content': Utils.fetchExistingTestimonials,
		'tcb.symbol_loaded': Utils.fetchExistingTestimonials,
		'theme.section.after_insert': ( data, section ) => {
			Utils.fetchExistingTestimonials( data.$element );
		},
	},
	filters = {
		/**
		 * @param  data
		 * @return {*&{testimonial_data: *}}
		 */
		'tcb.lazyload.data': data => {
			data[ 'testimonial-existing-ids' ] = Utils.getTestimonialIdsFromContent();

			return data;
		},
		/**
		 * Filter called before saving the post.
		 * Replace the display testimonials and its inner elements with shortcodes.
		 *
		 * @param {jQuery} $content
		 * @param {jQuery} $root
		 * @param {Object} options
		 * @return {jQuery} $content
		 */
		'tcb_filter_html_before_save': ( $content, $root, options ) => {
			/* get_clean_content() is also called when entering the Content Box style picker - we don't do anything in that scenario */
			if ( ! $root.is( TVE.identifier( 'contentbox' ) ) && ! options.render_post_list ) {
				content.setElementClasses( $content, TVE.identifier( 'article' ) );
				content.parseMainShortcodes( $content );
			}

			return $content;
		},

		/**
		 * If we're removing an element from an article, remember from where we want to remove it, because we might have to synchronize all of the templates.
		 *
		 * @param {boolean} allow
		 * @param {jQuery}  $element
		 * @return {boolean} allow
		 */
		'allow_remove': ( allow, $element ) => {
			const $article = $element[ 0 ].closest( TVE.identifier( 'article' ) ),
				$displayTestimonials = $element[ 0 ].closest( TVE.identifier( 'display_testimonials' ) );

			if ( $article.length && $displayTestimonials ) {
				TVE.displayTestimonials.$articleToSync = $article;
			}

			return allow;
		},

		/**
		 * Modifies the ListIdentifier
		 * Gets called from PostList Layout component
		 *
		 * @param {string} listIdentifier
		 * @param {jQuery} $element
		 * @return {string} listIdentifier
		 */
		'tcb.post_list.layout.getListIdentifier': ( listIdentifier, $element ) => {
			if ( $element && $element.closest( constants.IDENTIFIER ).length > 0 ) {
				listIdentifier = constants.IDENTIFIER;
			}

			return listIdentifier;
		},

		/**
		 * Do not allow elements to be dragged out of their original article.
		 *
		 * @param {boolean} allow
		 * @param {string}  $element
		 * @return {boolean} allow
		 */
		'allow_dragenter': ( allow, $element ) => {
			if ( editMode.isInsideEditMode() ) {
				/* check if the edit mode element exists and is a display testimonials */
				if ( TVE.main.EditMode.element() && TVE.main.EditMode.element().is( TVE.identifier( 'display_testimonials' ) ) ) {
					/* do not allow dragenter for article */
					if ( $element.is( TVE.identifier( 'article' ) ) ) {
						allow = false;
					} else if ( ! TVE.FLAGS.$dragged_element.static_element ) {
						/* if we're moving things in the articles, we should not be able to drag them outside their assigned article */
						const postId = TVE.FLAGS.$dragged_element.parents( TVE.identifier( 'article' ) ).attr( 'id' ),
							hoverId = $element.parents( TVE.identifier( 'article' ) ).attr( 'id' );

						allow = allow && ( postId === hoverId );
					}
				}
			}
			return allow;
		},
		/**
		 * Filter before the selection of the dropzone target. The editor scrolls to this after inserting an element with click
		 *
		 * @param  $target
		 * @return {*}
		 */
		'tcb.get_dropzone_target': $target => Utils.getDropzoneTarget( $target ),
		/**
		 * Filter before inserting an element inside a content area on click
		 *
		 * @param  $target
		 * @return {*}
		 */
		'tcb.change_target': $target => Utils.getDropzoneTarget( $target ),

		/**
		 *
		 * @param {InstanceType} _instance
		 * @param {jQuery}       $element
		 * @return {TVE.inner.window.TCB_Front.PostList} _instance
		 */
		'tcb.carousel_manager.element_instance.display_testimonials': ( _instance, $element ) => new TVE.inner.window.TCB_Front.PostList( $element, true ),

		'tcb.post_list.article_identifier': idenfifier => `${idenfifier}:not(.thrive-testimonial-wrapper)`,
		/**
		 * Add the display testimonials to the list of elements that need to be re-rendered when the device changes.
		 *
		 * @param {string} selectors
		 */
		'tcb.post_list.device_change_selectors': selectors => `${selectors}, ${TVE.identifier( 'display_testimonials' )}`,
		/**
		 *
		 * @param  identifier
		 * @return {`${string}, ${string}`}
		 */
		'theme.post_list.identifier': identifier => `${identifier}, ${TVE.identifier( 'display_testimonials' )}`,
	};

module.exports = { actions, filters };
