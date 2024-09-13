const utils = require( './utils' ),
	content = require( './content' ),
	editMode = require( './edit-mode' );
/**
 * Extends the post-list component ( from components/post-list/component.js ).
 */
module.exports = TVE.Views.Components.display_testimonials = TVE.Views.Components.post_list.extend( {
	after_init() {
		/* only to overwrite the function from Post List for now */
		this.$noResultsMessageTextArea = this.$( '#thrive-display-testimonials-no-results-message' );

		if ( ! this.displayTypeTooltip && ! TVE.CONST.dismissed_tooltips.includes( 'displayTypeDisplayTestimonials' ) ) {
			this.displayTypeTooltip = new TVE.Views.other.WarningTooltip( {
				template: TVE.tpl( 'util/carousel-display-type-tooltip' ),
				type: 'displayTypeDisplayTestimonials',
				className: 'tcb-tooltip-container tcb-display-type-tooltip',
				identifier: `${TVE.identifier( 'display_testimonials' )}${TVE.Models.CarouselManager.carouselSelector}`,
				control: this.controls.Type,
				afterApplyChange: () => {
					this.controls.Type.setActive( this.potentialDisplayType, true );
				},
			} );

			this.$el.find( '[data-view="Type"] .tve-btn-group' ).append( this.displayTypeTooltip.$el );
		}
	},
	controls_init( controls ) {
		/* call the controls_init from the parent ( the post-list component ) */
		TVE.Views.Components.post_list.prototype.controls_init.call( this, controls );

		editMode.toggleListElements( false, true );

		/* overwrites parent implementation */
		controls.NumberOfItems.input = TVE.$.noop;

		controls.NumberOfItems.change = function ( $element, control ) {
			const query = { ...TVE.PostList.utils.readQueryFromElement(), posts_per_page: control.value };

			utils.applyQuery( query, $element ).then( response => {
				utils.applyQueryCallback( response, $element );
			} );
		};

		controls.NumberOfItems.update = function ( $element ) {
			this.setValue( content.countItems( $element ) );
		};

		controls.MessageColor.update = function () {
			this.setValue( TVE.ActiveElement.attr( 'data-no_posts_text_color' ) );
		};
		controls.MessageColor.change = function ( color ) {
			color = color ? color.toString() : '';

			TVE.ActiveElement.attr( 'data-no_posts_text_color', color ).head_css( { color }, null, '::after' );
		};
	},
	/**
	 * Called before each component update
	 */
	before_update() {
		this.$noResultsMessageTextArea.val( TVE.ActiveElement.attr( 'data-no_posts_text' ) || '' );
	},
	after_update() {
		const isCarousel = this.controls.Type.getValue() === 'carousel',
			isFade = TVE.Components.carousel.controls.Fade.getValue();

		this.controls.HorizontalSpace.$el.toggleClass( 'tcb-disabled', isCarousel && isFade );
		this.controls.PaginationType.$el.toggleClass( 'tcb-disabled', isCarousel );
	},
	editMode() {
		editMode.enterEditMode( TVE.ActiveElement, this.getStates(), this.stateChange );
	},

	filterTestimonialsClicked() {
		this.filterTestimonials();
	},
	/**
	 * Open filter testimonial modal
	 *
	 * @param  cloudModalInstance
	 * @param  canSwitchToTemplates
	 */
	filterTestimonials( cloudModalInstance = null, canSwitchToTemplates = false ) {
		const Modal = require( './modal/main' );

		if ( typeof TVE.displayTestimonialsModal !== 'undefined' ) {
			TVE.displayTestimonialsModal.destroy();
		}

		TVE.displayTestimonialsModal = new Modal( {
			el: TVE.modal.get_element( 'display-testimonials' ),
			cloudModalInstance,
			canSwitchToTemplates,
		} );

		TVE.displayTestimonialsModal.open();
	},
	changeNoResultsMessage( event, dom ) {
		TVE.ActiveElement.attr( 'data-no_posts_text', dom.value );
	},
} );
