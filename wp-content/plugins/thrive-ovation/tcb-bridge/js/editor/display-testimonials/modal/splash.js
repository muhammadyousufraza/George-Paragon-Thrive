const SetListView = require( './set/list' );

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/pages/splash',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;
		this.setCollection = attr.setCollection;
		this.canSwitchToTemplates = attr.canSwitchToTemplates;

		this.listenTo( this.setCollection, 'destroy', () => {
			/* We do this, so we can eventually hide the container if it is empty */
			this.updateSetCounts();
		} );
	},
	afterRender() {
		if ( TVE.displayTestimonials.has_at_least_one_testimonial ) {
			this.updateSetCounts();

			this.$( '.thrive-display-testimonials-action-buttons' ).toggle( this.canSwitchToTemplates );
		} else { /* If we do not have any displayable testimonials */
			this.$( '.thrive-display-testimonials-content' ).hide();
			this.$( '.thrive-display-testimonials-no-testimonials' ).show();
		}
	},
	openStaticList() {
		this.dataModel.setPage( 'static' );
	},
	openDynamicList() {
		this.dataModel.setPage( 'dynamic' );
	},
	openExistingSet() {
		this.$( '.thrive-display-testimonials-splash-content' ).addClass( 'opened-set-container' );

		( new SetListView( {
			el: this.$( '.thrive-display-testimonials-set-list' )[ 0 ],
			collection: this.setCollection,
			dataModel: this.dataModel,
		} ) );
	},
	updateSetCounts() {
		const isCollectionEmpty = this.setCollection.length === 0;

		this.$( '.descriptive-text.set-text' ).toggleClass( 'tcb-hidden', isCollectionEmpty );
		this.$( '.thrive-display-testimonials-sets-container' ).toggleClass( 'empty', isCollectionEmpty );

		if ( ! isCollectionEmpty ) {
			[ 'static', 'dynamic' ].forEach( type => this.$( `.${type}-count` ).html( this.setCollection.where( { type } ).length ).removeClass( 'loading small' ) );
		}
	},
} );
