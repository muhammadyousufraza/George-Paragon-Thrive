const utils = require( './utils' );

module.exports = TVE.renderers.baseModel.extend( {
	getClassName() {
		return this.attributes.className || '';
	},
	/**
	 * Render Display Testimonials Inline Shortcodes Elements
	 *
	 * @param {jQuery} $target - where the html will be inserted
	 * @return {string} - html to be inserted
	 */
	render_default( $target ) {
		const blockElementMapping = {
				/* testimonial title should always use a heading tag */
				tvo_testimonial_title: '<h2></h2>',
				tvo_testimonial_content: '<div class="tcb-plain-text"></div>',
				tvo_testimonial_website: '<div class="thrv_wrapper thrv_text_element"></div><div class="tcb-plain-text"></div></div>',
				tvo_testimonial_role: '<div class="tcb-plain-text"></div>',
				tvo_testimonial_author: '<div class="tcb-plain-text"></div>',
				tvo_testimonial_image: '<div class="thrv_wrapper tve_image_caption tcb-dynamic-field-source tve-draggable tve-droppable" data-dynamic="featured"></div>',
			},
			$block = TVE.inner_$( blockElementMapping[ this.key ] || '<p></p>' ),
			articleID = utils.getArticleID( $target ),
			attr = {
				'data-shortcode': this.key,
			};
		let newContent = TVE.displayTestimonials.testimonial_shortcodes[ articleID ][ this.key ];

		switch ( this.key ) {
			case 'tvo_testimonial_content':
				newContent = TVE.$( newContent ).html();
				break;
			case 'tvo_testimonial_title':
			case 'tvo_testimonial_role':
			case 'tvo_testimonial_author':
				newContent = TVE.$( newContent ).text();
				break;
			case 'tvo_testimonial_website':
				newContent = TVE.$( newContent ).text();
				attr[ 'data-attr-link' ] = 1;
				break;
			case 'tvo_testimonial_image':
				const imageSrc = TVE.Components.image.controls.ImagePicker.picker.clean_url( TVE.displayTestimonials.testimonial_shortcodes[ articleID ].image );
				newContent = `<span class="tve_image_frame"><img class="tve_image" src="${imageSrc}" loading="lazy" data-d-f="featured" /></span>`;
				break;
			default:
				break;
		}

		/* For the image, we do not need to wrap the text elements */
		if ( this.key === 'tvo_testimonial_image' ) {
			$block.html( newContent );

			/* We need the active element for the image url callback */
			TVE.ActiveElement = TVE.ClickedElement = $block;

			return $block[ 0 ].outerHTML;
		}

		/* For website, we need to wrap it inside and a tag */
		if ( this.key === 'tvo_testimonial_website' ) {
			const $shortcodeContent = TVE.$( `<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-option-inline="1"><a href="${newContent}" class="tve-froala fr-basic" style="outline: none;">${newContent}</a></span>` ).attr( attr );

			$block.html( `<span class="thrive-inline-shortcode" contenteditable="false">${$shortcodeContent[ 0 ].outerHTML}</span>` );
		} else {
			const $shortcodeContent = TVE.$( `<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-option-inline="1">${newContent}</span>` ).attr( attr );

			$block.html( `<span class="thrive-inline-shortcode" contenteditable="false">${$shortcodeContent[ 0 ].outerHTML}</span>` );
		}

		return `<div class="${this.getClassName()} thrv_wrapper">${$block[ 0 ].outerHTML}</div>`;
	},

} );
