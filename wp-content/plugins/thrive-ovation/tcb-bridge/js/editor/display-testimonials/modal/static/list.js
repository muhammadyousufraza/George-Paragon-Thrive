const ItemView = require( './item' );

module.exports = TVE.Views.Base.base_view.extend( {
	beforeInitialize( attr ) {
		this.displayedCollection = attr.collection;
		this.dataModel = attr.dataModel;

		this.listenTo( this.displayedCollection, 'reset remove', this.render );
	},
	afterRender() {
		if ( ! this.dataModel.isSelectMode() ) {
			this.displayedCollection.sort();
		}

		this.renderItems();
	},
	renderItems() {
		this.$el.empty();

		const isReorderMode = this.dataModel.isReorderMode();

		this.displayedCollection.each( item => {
			if ( ! isReorderMode || !! item.get( 'selected' ) ) {
				this.$el.append( ( new ItemView( { model: item, dataModel: this.dataModel } ) ).$el );
			}
		} );

		if ( isReorderMode && ! this.dataModel.isViewMode() ) {
			this.$el.sortable( {
				axis: 'y',
				handle: '.action-col',
				start( event, ui ) {
					ui.item.addClass( 'is-dragged' );
				},

				stop( event, ui ) {
					ui.item.removeClass( 'is-dragged' );
				},
				update: () => {
					/**
					 * When we manually moved the items, reset the ordering to manual.
					 */
					if ( this.dataModel.getOrderingOptions( 'type' ) !== 'manual' ) {
						this.dataModel.setOrderingOption( 'type', 'manual' );
					}

					this.sortItems( this.$el.sortable( 'toArray', { attribute: 'data-id' } ) );
				},
			} );
		}
	},
	sortItems( reorderedIds, reRenderList = false ) {
		if ( ! reorderedIds ) {
			return;
		}

		const selectedCollection = this.dataModel.getSelectedCollection();
		let order = 0;

		reorderedIds.forEach( id => selectedCollection.get( id ).set( 'order', order++ ) );

		selectedCollection.sort();

		this.updateDisplayedCollection();

		this.dataModel.set( 'maximum_order', order );

		if ( reRenderList ) {
			this.renderItems();
		}
	},
	removeFromSelection( event ) {
		this.removeItem( event.target.closest( 'li' ).dataset.id );
	},
	removeItem( id ) {
		const selectedCollection = this.dataModel.getSelectedCollection();

		selectedCollection.remove( selectedCollection.get( id ) );

		this.dataModel.trigger( 'change:selected_collection' );

		this.updateDisplayedCollection();
	},
	updateDisplayedCollection() {
		this.displayedCollection = this.dataModel.getSelectedCollection().clone();

		if ( this.dataModel.isReorderMode() && this.displayedCollection.length === 0 ) {
			const modal = this.$el.parents( '#thrive-display-testimonials.static' );
			this.$el.hide();
			modal.find( '.deleted-testimonials' ).show();
			modal.find( 'button[data-fn="openSetPage"], button[data-fn="load"], button[data-fn="updateSet"]' ).tcbAddClass( 'tcb-disabled' );
		}
	},
} );
