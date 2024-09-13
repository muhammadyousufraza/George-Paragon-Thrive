const DataManager = require( '../data-manager' ),
	TagListView = require( './list' ),
	TagCollection = Backbone.Collection.extend( {
		model: Backbone.Model,
	} ),
	Nav = require( '../nav' ),
	OrderOptions = require( '../order-options' ),
	Utils = require( '../utils' );

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/pages/dynamic',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;
		this.setCollection = attr.setCollection;

		this.dataModel.set( 'type', 'dynamic' );

		this.listenTo( this.dataModel, 'change:set_status', this.render );
	},
	afterRender() {
		this.collection = new TagCollection( [] );

		this.listenTo( this.collection, 'change:selected', () => {
			this.dataModel.set( 'tags', this.getSelectedTagIds() );
			this.updateCounter();
		} );

		this.$tagList = this.$( '.thrive-display-testimonials-tags-list' );

		Utils.toggleLoading( this.$tagList );

		DataManager.getTags().then( tags => {
			if ( tags.length ) {
				const selectedTags = this.dataModel.getItemIds();

				/* check the tags that should be selected */
				if ( selectedTags.length ) {
					tags.forEach( tag => tag.selected = selectedTags.includes( parseInt( tag.value ) ) ? 1 : 0 );
				}

				this.collection.reset( tags );

				( new TagListView( {
					el: this.$tagList[ 0 ],
					collection: this.collection,
				} ) );

				this.updateCounter();
			} else {
				this.$tagList.find( '.no-tags-message' ).show();
			}

			Utils.toggleLoading( this.$tagList, false );
		} );

		this.initializeNav();
		this.initializeOrderingOptions();
		this.toggleActionButtonsAvailability();
	},
	updateCounter() {
		const selectedTags = this.dataModel.getItemIds();

		this.toggleListLoader();

		DataManager.getTestimonialCount( { tags: selectedTags.toString() } ).then( count => {
			this.$( '.dynamic-number' ).text( count );
			this.dataModel.set( 'dynamic-set-item-count', count );
			this.toggleListLoader( false );
			this.toggleActionButtonsAvailability( count === 0 );
		} );
	},
	initializeNav() {
		( new Nav( {
			el: this.$( '.thrive-display-testimonials-set-selector.dynamic' )[ 0 ],
			dataModel: this.dataModel,
			selectedValue: this.dataModel.getSetId() ? this.dataModel.getSetId() : 'dynamic',
			selectCallback: () => this.clear(),
			setCollection: this.setCollection,
		} ) );
	},
	initializeOrderingOptions() {
		if ( this.orderOptionsView ) {
			this.orderOptionsView.remove();
		}

		this.orderOptionsView = new OrderOptions( {
			el: this.$( '.dynamic-order' )[ 0 ],
			dataModel: this.dataModel,
			typeList: [
				{ label: 'Date Published', value: 'date' },
				{ label: 'Date Modified', value: 'modified' },
				{ label: 'Title', value: 'title' },
				{ label: 'Author', value: 'author' },
				{ label: 'Random', value: 'rand' },
			],
			placeholderText: 'Arrange results',
		} );
	},
	load() {
		if ( this.isValid() ) {
			this.dataModel.load();
		}
	},
	goBackToSplash() {
		this.clear();
		this.dataModel.setPage( 'splash' );
	},
	clear() {
		this.dataModel.clearData();
		this.remove();

		if ( this.orderOptionsView ) {
			this.orderOptionsView.remove();
		}
	},
	getSelectedTagIds() {
		return this.collection.filter( item => item.get( 'selected' ) === 1 ).map( tag => parseInt( tag.get( 'value' ) ) );
	},
	isValid() {
		return this.dataModel.getItemIds().length > 0;
	},
	switchToEditMode() {
		this.dataModel.set( 'set_status', 'edit' );
	},
	openSetPage() {
		if ( ! this.isValid() ) {
			return;
		}

		this.remove();

		this.dataModel.setPage( 'set', this.dataModel.getSetId() ); /* if we were currently editing a set, memorize the set ID for caching purposes */
	},
	updateSet() {
		this.setCollection.get( this.dataModel.getSetId() )
		    .set( 'count', this.dataModel.get( 'dynamic-set-item-count' ) )
		    .setItems( this.getSelectedTagIds() )
		    .save( null, {
			    wait: true,
			    success: () => {
				    TVE.page_message( 'Testimonial set updated.' );

				    this.dataModel.set( 'set_status', 'view' );
			    },
			    error: () => TVE.page_message( 'Error on updating the testimonial set.', true ),
		    } );
	},
	toggleListLoader( loading = true ) {
		Utils.toggleLoadingSpinner( this.$( '.dynamic-counter-container' ), '.dynamic-counter', '', loading );
	},
	toggleActionButtonsAvailability( disableButtons = false ) {
		const selectedCollectionIds = this.dataModel.getItemIds(),
			shouldBeHidden = selectedCollectionIds && selectedCollectionIds.length === 0;

		this.$( '[data-fn="openSetPage"], [data-fn="load"]' ).toggleClass( 'tcb-disabled', shouldBeHidden || disableButtons );
		this.$( '.dynamic-counter' ).toggle( ! shouldBeHidden );
	},
} );
