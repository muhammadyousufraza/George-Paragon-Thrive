const content = require( '../content' ),
	actions = {
		/**
		 * Triggered when we blur on a text element. If it's inside a display testimonials, do the sync
		 *
		 * @param {Object} event
		 * @param {Object} FE
		 */
		'tcb.froala.blur': ( event, FE ) => {
			/* exit if the active element is not an element that has a froala toolbar/editor */
			if ( typeof TVE.ActiveElement === 'undefined' || ! TVE.ActiveElement.is( TVE.TEXT_ALL ) ) {
				return;
			}

			content.checkForDisplayTestimonialsSync( FE.$el );
		},
		'tcb.image.change': () => content.checkForDisplayTestimonialsSync( TVE.ActiveElement ),
		'tcb.element.duplicate': $element => content.checkForDisplayTestimonialsSync( $element ),
		/**
		 * Action before the template article replaces the other articles during sync.
		 *
		 * @param {jQuery} $article
		 * @param {number} ID
		 */
		'tcb.testimonial_list.before_article_sync': ( $article, ID ) => {
			/* for each inline shortcode change the HTML with the dynamic data */
			$article.find( '.thrive-shortcode-content' ).each( function ( index, elem ) {
				const $element = TVE.inner_$( elem ),
					shortcode = $element.attr( 'data-shortcode' ),
					post = TVE.displayTestimonials.testimonial_shortcodes[ ID ],
					/* replace the dynamic content with data from the posts object */
					newContent = post ? post[ shortcode ] : '',
					$newContent = TVE.inner_$( newContent );
				if ( $newContent.length ) {
					let text = $newContent.text();

					/* If the shortcode is testimonial content we keep the html tags because content can be edited from TO Dashboard */
					if ( 'tvo_testimonial_content' === $element.attr( 'data-shortcode' ) ) {
						text = $newContent.html();
					}

					/* use default value when existing */
					if ( text.length === 0 && shortcode.includes( 'testimonial' ) && $element.attr( 'data-attr-default' ) ) {
						text = $element.attr( 'data-attr-default' );
					}

					/**
					 * Wrap the shortcode content when link option is enabled
					 */
					if ( 'tvo_testimonial_website' === $element.attr( 'data-shortcode' ) ) {
						$element.find( 'a' ).html( text )
						        .attr( 'href', text );
					} else {
						$element.html( text );
					}
				}
				$newContent.remove();
			} );
		},

		/**
		 * Apply styles again after the article has been synchronized.
		 *
		 * @param {jQuery} $displayTestimonials
		 */
		'tcb.display_testimonials_article.sync': $displayTestimonials => {
			/* apply layout only on masonry because the rest are pretty responsive */
			if ( [ 'masonry', 'carousel' ].includes( TVE.PostList.utils.readValue( $displayTestimonials, 'type' ) ) ) {
				setTimeout( () => {
					TVE.PostList.layout.applyLayout( $displayTestimonials );
				}, 100 );
			}
		},

		/**
		 * When we edit inside a display testimonials, we highlight elements that are in sync.
		 *
		 * @param {jQuery} $element
		 */
		'tcb.element.focus': $element => {
			const $article = $element.closest( TVE.identifier( 'article' ) );

			if ( $article.length ) {
				const selector = TVE.identifier( TVE._type( $element ) ),
					elementIndex = $article.find( selector ).index( $element );

				/* highlight similar elements from other articles that have the same index */
				if ( elementIndex !== -1 ) {
					const $displayTestimonials = $article.closest( TVE.identifier( 'display_testimonials' ) );

					$displayTestimonials.find( TVE.identifier( 'article' ) ).each( ( index, element ) => {
						TVE.inner_$( element ).find( selector ).eq( elementIndex ).addClass( TVE.PostList.constants.SIMILAR_EDIT_CLASS );
					} );
				}
			}
			/**
			 * Make sure that inline_shortcode is update for post-list inline shortcodes elements
			 * e.g tags/cat/title...
			 */
			if ( $element.is( '.tve-inline-post-list-shortcode' ) ) {
				const $shortcodeLink = $element.find( 'a' );

				TVE.froala.editor.$oel.find( '.thrive-shortcode-content' ).trigger( 'mouseup' );
				if ( $shortcodeLink.length ) {
					TVE.froala.force_focus( TVE.froala.editor.$oel.find( 'a' ), true );
				}
				$element.removeClass( 'tve-inline-post-list-shortcode' );
			}
		},

		/**
		 * Clear focus from the elements that we might have set the focus on with the 'tcb_similar_edit' class.
		 */
		'tcb.focus.clear': () => {
			TVE.inner.$body.find( `.${TVE.PostList.constants.SIMILAR_EDIT_CLASS}` ).removeClass( TVE.PostList.constants.SIMILAR_EDIT_CLASS );
		},
	},
	filters = {};

module.exports = { actions, filters };
