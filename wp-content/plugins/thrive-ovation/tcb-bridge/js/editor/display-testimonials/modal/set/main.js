const ListView = require( './list' ),
	SetModel = require( '../models/set' );

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/pages/set',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;
		this.setCollection = attr.setCollection;
	},
	afterInitialize() {
		const isCollectionEmpty = this.setCollection.length === 0;

		this.$( '.descriptive-text.existing-sets' ).toggleClass( 'tcb-hidden', isCollectionEmpty );
		this.$( '.thrive-display-testimonials-sets-container' ).toggleClass( 'empty', isCollectionEmpty );

		if ( ! isCollectionEmpty ) {
			( new ListView( {
				el: this.$( '.thrive-display-testimonials-set-list' )[ 0 ],
				collection: this.setCollection,
				dataModel: this.dataModel,
			} ) );
		}
	},
	onInputChange( event ) {
		const label = event.target.value;

		this.dataModel.set( 'label', label );
	},
	saveNewSet() {
		const newSet = new SetModel( this.getDataToSave() );

		newSet.save( null, {
			wait: true,
			success: set => {
				set.unset( 'ids' );

				this.setCollection.add( set );
				this.clear();

				TVE.page_message( 'Testimonial set added.' );

				this.dataModel.viewSet( set );
			},
			error: () => TVE.page_message( 'Error on adding the testimonial set.', true ),
		} );
	},
	overwriteSet( event ) {
		const id = event.target.closest( 'li' ).dataset.id,
			setToOverwrite = this.setCollection.get( id ),
			setData = this.getDataToSave();

		setData.label = setToOverwrite.get( 'label' );

		setToOverwrite.save( setData, {
			wait: true,
			success: () => {
				TVE.page_message( 'Testimonial set overwritten.' );

				this.clear();

				this.dataModel.viewSet( setToOverwrite );
			},
			error: () => TVE.page_message( 'Error on overwriting the testimonial set.', true ),
		} );
	},
	getDataToSave() {
		const type = this.dataModel.get( 'type' ),
			isStatic = ( type === 'static' );

		return {
			label: this.dataModel.get( 'label' ),
			type,
			[ isStatic ? 'testimonials' : 'tags' ]: this.dataModel.getItemIds(),
			ordering: this.dataModel.getOrderingOptions(),
		};
	},
	goBack() {
		this.clear();

		this.dataModel
		    .set( 'preselected_ids', this.dataModel.getItemIds() )
		    .setPage( this.dataModel.get( 'type' ), this.dataModel.getSetId() ); /* if we were currently editing a set, memorize the set ID for caching purposes */
	},
	clear() {
		this.dataModel.unset( 'label' );

		this.remove();
	},
} );
