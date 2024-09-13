const constants = require( './constants' ),
	content = {

		/**
		 * Check if the $element is inside a display testimonials item. If it is, do a display testimonials sync.
		 *
		 * @param {jQuery}  $element
		 * @param {boolean} shouldSync
		 * @return {boolean} should sync
		 */
		checkForDisplayTestimonialsSync( $element, shouldSync = true ) {
			const $testimonial = $element.closest( '.thrive-testimonial-wrapper' ),
				canSync = $testimonial.length > 0 && ! content.isSyncing();

			if ( canSync && shouldSync ) {
				TVE.displayTestimonials.SYNC = true;
				content.syncDisplayTestimonial( $testimonial );
				TVE.displayTestimonials.SYNC = false;
			}

			return canSync;
		},

		/**
		 * Checks if the display testimonials is syncing
		 *
		 * @return {Object} is syncing
		 */
		isSyncing() {
			return TVE.displayTestimonials.FLAGS.SYNC;
		},
		/**
		 * Copy the content of the new article to the given article.
		 *
		 * @param {HTMLElement} article
		 * @param {jQuery}      $newArticle
		 * @param {boolean}     syncNewArticle
		 */
		syncArticle( article, $newArticle, syncNewArticle = false ) {
			/* skip the 'new' article and the sample articles */
			if ( $newArticle.is( article ) && ! syncNewArticle ) {
				return;
			}

			const $current = TVE.inner_$( article ),
				/* get a copy so we don't mess up the original article */
				$clone = $newArticle.clone(),
				ID = parseInt( article.dataset.id );

			/* if for some reason, this article doesn't have an ID, we can't really sync info */
			if ( ID ) {
				const testimonial = TVE.displayTestimonials.testimonial_shortcodes[ ID ];

				if ( testimonial ) {
					content.syncDynamicImages( $clone, testimonial.image );
				}

				/* action for content changes on the new article */
				TVE.do_action( 'tcb.testimonial_list.before_article_sync', $clone, ID );
			}

			/* make sure we don't clone the overlay when in hover mode */
			$clone.find( '.tve-element-overlay' ).removeClass( 'tve-element-overlay' );
			$clone.find( '.edit_mode' ).removeClass( 'edit_mode' );

			/* do not copy the dynamic style node - preserve each article's own style node */
			$clone.find( 'style.tvo-display-testimonials-dynamic-variables' ).replaceWith( $current.find( 'style.tvo-display-testimonials-dynamic-variables' ) );

			/* replace the current layout with the updated clone */
			$current.html( $clone.html() );

			TVE.Editor_Page.content_manager.cleanup_froala( $current );

			$clone.remove();
		},
		syncDisplayTestimonial( $newArticle, $testimonialList = null, posts = TVE.displayTestimonials.testimonial_shortcodes || [] ) {
			/* clean template content */
			$newArticle.find( '.tve-dropped' ).removeClass( 'tve-dropped' );

			/* figure out which $post_list we have to synchronize */
			if ( ! $testimonialList ) {
				$testimonialList = $newArticle.parents( TVE.identifier( 'display_testimonials' ) );
			}

			TVE.froala.disable( $newArticle );

			/* if a list of posts has been sent, it means that we have to replace the whole display testimonials with new elements */
			if ( posts.length ) {
				content.generateDisplayTestimonials( $testimonialList, $newArticle, posts );
			}

			/* now we go through all the posts/articles and we update the dynamic/shortcode information */
			$testimonialList.find( TVE.identifier( 'article' ) ).each( ( index, article ) => {
				content.syncArticle( article, $newArticle, false, posts );
			} );

			/* rebind drag/resize listeners after templates change */
			TVE.drag.editorActions();

			TVE.do_action( 'tcb.display_testimonials_article.sync', $testimonialList );
		},
		/**
		 * Replace the articles from the display testimonials with the ones received from the Query Builder ajax.
		 *
		 * @param {jQuery} $testimonialList
		 * @param {jQuery} $newArticle
		 * @param {jQuery} testimonials
		 */
		generateDisplayTestimonials( $testimonialList, $newArticle, testimonials ) {
			/* first we empty the testimonial list */
			$testimonialList.empty();

			const elementTag = $newArticle.prop( 'tagName' ).toLowerCase();

			testimonials.forEach( testimonial => {
				const attr = {
					id: 'post-' + testimonial.ID,
					'data-id': testimonial.ID,
					class: 'thrive-testimonial-wrapper post-wrapper thrv_wrapper',
					'data-selector': '.post-wrapper',
				};

				/* and append post/article elements to the display testimonials for each id */
				$testimonialList.append(
					TVE.inner_$( `<${elementTag}>`, attr ).append( TVE.inner_$( testimonial.tvo_testimonial_dynamic_variables ) ),
				);
				/* we will later synchronize the content of them */
			} );
		},

		/**
		 * Find all the display testimonials in the page and convert them into shortcodes.
		 * We only save the first article.
		 *
		 * @param {jQuery} $content
		 */
		parseMainShortcodes( $content ) {
			$content.find( TVE.identifier( 'display_testimonials' ) ).each( ( index, element ) => {
				const $testimonialList = $content.find( element ),
					$firstArticle = $testimonialList.find( TVE.identifier( 'article' ) ).first(),
					shortcodeTag = constants.THRIVE_DISPLAY_TESTIMONIALS_SHORTCODE;
				/* add the dynamic style inside a separate shortcode */
				let shortcodeContent = `[tvo_testimonial_dynamic_variables][/tvo_testimonial_dynamic_variables]`,
					attr = '';

				/* wrap the pagination for this display testimonials into a shortcode */
				TVE.PostList.content.parsePaginationShortcode( $testimonialList, $content );

				/* remove the style tag after saving the rules in the shortcode */
				$firstArticle.find( 'style.tvo-display-testimonials-dynamic-variables' ).remove();

				TVE.PostList.dynamicImage.saveDynamicImageSources( $firstArticle );

				/* Replace Testimonial Image TAG with the shortcode */
				$testimonialList.find( '.tcb-dynamic-field-source img' ).each( ( _index, domElem ) => {
					const $el = TVE.inner_$( domElem ),
						$par = $el.parent().parent(),
						extraAttrs = ` class="${domElem.getAttribute( 'class' ) || ''}" css="${domElem.getAttribute( 'data-css' ) || ''}"`;
					$par.attr( 'data-tcb-events', $el.attr( 'data-tcb-events' ) );
					domElem.outerHTML = `[tvo_testimonial_image type="${domElem.getAttribute( 'data-d-f' )}"${extraAttrs}]`;
				} );

				/* Save the display testimonials id */
				if ( $testimonialList.attr( 'id' ) ) {
					attr = `id='${$testimonialList.attr( 'id' )}' `;
				}

				const excludedAttr = [ 'data-shortcode', 'data-selector', 'data-class', 'data-id' ],
					testimonialListAttr = TVE.PostList.utils.elementAttributes( $testimonialList, excludedAttr, true, true );
				const $carouselContainer = $testimonialList.find( '.tcb-carousel-container' );

				/* Add the data-carousel-settings attr to the display testimonials Attributes */
				if ( $carouselContainer.length > 0 ) {
					TVE.Models.CarouselManager.prepareForPostListSave( $carouselContainer, testimonialListAttr );
				}
				/* add the display testimonials dataset to the shortcode */
				_.each( testimonialListAttr, ( value, key ) => {
					if ( typeof value === 'number' || value.includes( '"' ) ) {
						value = `'${value}'`;
					} else {
						value = `"${value}"`;
					}

					attr += `${key}=${value} `;
				} );

				/* add responsive classes when needed*/
				const classes = TVE.Editor_Page.getResponsiveClasses( $testimonialList );

				attr += classes ? ` class='${classes.join( ' ' )}' ` : '';

				if ( $firstArticle.length ) {
					TVE.do_action( 'tcb.post_list.before_save', $firstArticle );

					shortcodeContent += $firstArticle.html();

					/* get each data attribute from the article except what's excluded; encode shortcode brackets ( replace '[' with '|{|' ) */
					const elementAttr = TVE.PostList.utils.elementAttributes( $firstArticle, [ 'data-id', 'data-selector' ], true, true );

					/* save attributes specific to the article */
					_.each( elementAttr, ( value, key ) => {
						/* prefix with 'article-' so we know it's for the article */
						attr += `article-${key}='${value}' `;
					} );
				}

				/* wrap the content in the post-list shortcode tags and add the attributes */
				$testimonialList.replaceWith( `[${shortcodeTag} ${attr}]${shortcodeContent}[/${shortcodeTag}]` );
			} );
		},

		/**
		 * Detect elements that have events on them so we can save the custom classes and the event datasets.
		 *
		 * @param {jQuery} $content
		 * @param {string} itemIdentifier
		 */
		setElementClasses( $content, itemIdentifier ) {
			const classList = [
				`${itemIdentifier}.tve_evt_manager_listen`,
				`.${constants.THRIVE_DISPLAY_TESTIMONIALS_CLASS}.tve_evt_manager_listen`,
			];

			$content.find( classList.join( ', ' ) ).each( ( index, element ) => {
				element.setAttribute( 'data-class', TVE.PostList.utils.getEventClasses( element.className ) );

				/* encode [ and ] so the shortcodes won't read them in a wrong way. */
				if ( element.getAttribute( 'data-tcb-events' ) ) {
					element.setAttribute( 'data-tcb-events', element.getAttribute( 'data-tcb-events' ).replace( /\[/g, '|{|' ).replace( /\]/g, '|}|' ) );
				}
			} );
		},
		/**
		 * Sync Display Testimonials Image
		 *
		 * @param {jQuery} $content
		 * @param {string} url
		 */
		syncDynamicImages( $content, url ) {
			$content.find( `${TVE.identifier( 'image' )}[data-dynamic]` ).each( ( index, image ) => {
				const $imageElement = TVE.inner_$( image ),
					$img = $imageElement.find( '.tve_image' ),
					imageType = $imageElement.attr( 'data-dynamic' );

				$img.attr( {
					src: TVE.Components.image.controls.ImagePicker.picker.clean_url( url ),
					'data-d-f': imageType,
				} );
			} );
		},
		countItems( $content ) {
			return $content.find( '.thrive-testimonial-wrapper:not(.slick-cloned)' ).length;
		},
		replaceShortcodeWithData( shortcode, $html, ID ) {
			if ( shortcode && ID && TVE.displayTestimonials.testimonial_shortcodes && TVE.displayTestimonials.testimonial_shortcodes[ ID ] && TVE.displayTestimonials.testimonial_shortcodes[ ID ][ shortcode ] ) {
				/* Get the html we want to replace the element with. Add a filter here in case there are specific implementations. */
				const newHtml = TVE.displayTestimonials.testimonial_shortcodes[ ID ][ shortcode ];

				/* jQuerify the new html, then use its inner HTML (without the wrapper) to replace the shortcode */
				const $newEl = TVE.inner_$( newHtml );

				$html.html( $newEl.html() );

				$newEl.remove();
			}
		},
	};

module.exports = content;

