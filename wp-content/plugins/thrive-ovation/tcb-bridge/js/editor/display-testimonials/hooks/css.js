const content = require( '../content' ),
	utils = require( '../utils' ),
	actions = {
		/**
		 * if we're planning to add some css inside an *article* and it's for the *first time*,
		 * then we sync the templates so all of them receive the data-css. Also sync only when we're not *resizing*
		 *
		 * @param {string} selector
		 */
		'tcb.write_css': selector => {
			selector = selector.replace( TVE.PostList.utils.pseudoSelectorsRegex(), '' );

			/* stop if we're not resizing and the display testimonials is not included in the selector; add a filter for further conditional logic from the theme */
			if ( ! TVE.FLAGS.drag && selector.includes( TVE.identifier( 'display_testimonials' ) ) ) {
				const $element = TVE.inner.$document.find( selector ),
					$article = $element.closest( TVE.identifier( 'article' ) );

				/* if the element is inside article */
				if ( $article.length ) {
					const $parent = TVE.state_manager.get_parent();
					let shouldSync = false;

					/* content boxes have the data-css on parent */
					if ( $parent && TVE.inner.$body.find( '[data-css="' + $parent.attr( 'data-css' ) + '"]' ).length === 1 ) {
						shouldSync = true;
					}

					/* if the cssId does not exist in the other articles */
					if ( shouldSync || TVE.inner.$body.find( '[data-css="' + $element.attr( 'data-css' ) + '"]' ).length === 1 ) {
						/* if the element is an article, there's no need to sync */
						if ( ! $element.is( $article ) ) {
							content.checkForDisplayTestimonialsSync( $article );
						}
					}
				}
			}
		},
	},
	filters = {
		/**
		 * The dynamic images from the hover state need some 'help' with getting the default css variable (only when nothing was set before), so we add the value here
		 *
		 * @param {string}         cssValue
		 * @param {string | Array} rules
		 * @param {string}         selector
		 * @return {string}        cssValue
		 */
		'tcb.css.fallback_value': ( cssValue, rules, selector ) => {
			if ( ! cssValue && TVE.state_manager.is_hover() && selector && rules === 'background-image' ) {
				const isInsideTestimonialList = selector.includes( TVE.identifier( 'display_testimonials' ) ) ||
				                                ( selector.includes( TVE.identifier( 'article' ) ) && utils.isInsideTestimonialsList() );

				if ( isInsideTestimonialList ) {
					const dynamicBackgroundUrlPrefix = TVE.CONST.dynamic_background_url_prefix,
						dynamicCss = TVE.inner_$( selector ).css( `${TVE.CONST.cssVarPrefix}background-image` );

					if ( dynamicCss && dynamicCss.includes( dynamicBackgroundUrlPrefix ) ) {
						cssValue = dynamicCss.replace( 'var$', 'var' );
					}
				}
			}

			return cssValue;
		},
		/**
		 * Used for changing the element prefix
		 *
		 * @param {string} prefix
		 * @param {jQuery} $element
		 * @return {string} prefix
		 */
		'tcb.post_list.prefix': ( prefix, $element ) => {
			if ( $element.is( TVE.identifier( 'display_testimonials' ) ) ) {
				prefix = '.thrive-display-testimonials';
			}
			return prefix;
		},

		/**
		 * Filter for when a new css id is generated.
		 *
		 * @param {string} cssId
		 * @return {string} css_id
		 */
		'tcb.head_css_new_id': cssId => {
			const $element = TVE.inner.$body.find( '[data-css="' + cssId + '"]' ),
				$article = $element.closest( TVE.identifier( 'article' ) );

			/* when we generate a new data css, and it's inside an article, we sync the template so all the articles get the new id */
			/* but we make sure we don't sync the display testimonials when doing a drag action because it will mess everything up */
			if ( $element.length === 1 && $article.length && ! TVE.FLAGS.drag ) {
				content.checkForDisplayTestimonialsSync( $article );
			}

			return cssId;
		},
		/**
		 * Generate a prefix for the hover selector.
		 *
		 * @param {string} prefix
		 * @param {jQuery} currentElement
		 */
		'hover_prefix_selector': ( prefix, currentElement ) => {
			const $element = TVE.inner_$( currentElement );

			/* this is only needed when adding a content box background */
			if ( $element.hasClass( 'tve-content-box-background' ) ) {
				/* need to add the testimonial list + the data css of the testimonial list to the prefix */
				const $displayTestimonials = $element.parents( TVE.identifier( 'display_testimonials' ) );

				if ( $displayTestimonials.length ) {
					/* When editing elements inside the testimonial list, we add the testimonial list selector to the prefix so it won't apply to elements outside this testimonial-list. */
					prefix = utils.getDisplayTestimonialsSelector( $displayTestimonials ).trim();
				}
			}

			return prefix;
		},

		/**
		 * Change the prefix for the head_css function depending on what element you're editing.
		 *
		 * @param {string} prefix
		 * @param {jQuery} $element
		 * @return {string} prefix
		 */
		'tcb_head_css_prefix': ( prefix, $element ) => {
			const $displayTestimonials = $element.parents( TVE.identifier( 'display_testimonials' ) );

			/* When editing elements inside the display testimonials, we add the display testimonials selector to the prefix so it won't apply to elements outside this post-list. */
			if ( $displayTestimonials.length ) {
				const displayTestimonialsSelector = utils.getDisplayTestimonialsSelector( $displayTestimonials ),
					contentBoxSelector = '.thrv-content-box';

				/* if the prefix contains the content box selector or the element is a content box, then we insert the display testimonials selector between that and the main body class */
				if ( prefix.indexOf( contentBoxSelector ) === -1 && ! $element.is( '.tve-cb' ) ) {
					/* I'm not sure all the time what is the right order, so I just check to make sure :( */
					prefix = TVE.inner.$body.find( prefix + displayTestimonialsSelector ).length === 0 ? displayTestimonialsSelector + ' ' + prefix : prefix + ' ' + displayTestimonialsSelector;
					prefix = prefix.replace( /([ ]{2,})/g, ' ' );
				} else {
					/* if there's a content box identifier in the selector, make sure it is after the display testimonials prefix */
					prefix = prefix.replace( contentBoxSelector + ' ', '' ) + displayTestimonialsSelector + contentBoxSelector + ' ';
				}

				prefix = prefix.trimStart();

				/* make sure the base selector (usually = '#tve_editor') is the first thing in the prefix */
				if ( prefix.indexOf( TVE.PostList.baseSelector ) !== -1 ) {
					prefix = TVE.PostList.baseSelector + ' ' + prefix.replace( TVE.PostList.baseSelector, '' );
				}
			}

			return prefix;
		},

		/**
		 * In some cases, we want to save some rules anyway, despite them not being applied to the content.
		 * For instance, the display testimonials can load more articles without being refreshed, and they use css such as :nth-child(4),
		 * which wouldn't apply if there are 3 articles on save, but would be needed after load more is clicked.
		 * Relevant use case of things going wrong: SUPP-8483
		 *
		 * @param {boolean} whitelist
		 * @param {string}  selector
		 * @return {boolean} whitelist
		 */
		'tcb.css.save.whitelist_rule': ( whitelist, selector ) => {
			if ( selector.includes( TVE.identifier( 'display_testimonials' ) ) && selector.includes( ':nth-child' ) ) {
				/* remove the :nth-child part so we can check if the content is still in the page */
				selector = selector.replace( /:not\((:nth-child\(.*\))\)/g, '' );
				selector = selector.replace( /(:nth-child\(.*\))/g, '' );

				if ( TVE.inner.$body.find( selector ).length ) {
					whitelist = true;
				}
			}

			return whitelist;
		},

		/**
		 * Decide if we regenerate the data-css id or not.
		 * When we're inside an article, we don't regenerate it because we want the changes to apply to all articles.
		 *
		 * @param {boolean} regenerate
		 * @param {string}  cssId
		 * @return {boolean} regenerate
		 */
		'regenerate_css_id': ( regenerate, cssId ) => {
			const $element = TVE.inner.$body.find( '[data-css="' + cssId + '"]' );

			if ( $element.length ) {
				const $article = ( $element.length === 1 ? $element : $element.first() ).closest( TVE.identifier( 'article' ) );

				/* if we are inside an article, and that article has only one element with that data-css, do not regenerate the css */
				if ( $article.length && $article.find( '[data-css="' + cssId + '"]' ).length === 1 ) {
					regenerate = false;
				} else {
					/* if we have more than two identical data css in one article, this means that we've cloned an element so we regenerate the id */
				}
			}

			return regenerate;
		},
	};

module.exports = { actions, filters };
