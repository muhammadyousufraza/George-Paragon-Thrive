module.exports = require( './base' ).extend( {
	template: 'list',
	noItemsTemplate: 'tva-c-s-no-items',
	noSearchItemsTemplate: 'tva-c-s-no-search-items',
	pagination: null,
	itemViews: {},
	afterInitialize() {
		this.listenTo( this.collection, 'reset', _.bind( this.renderList, this ) );
		this.listenTo( this.collection, 'page-changed', _.bind( this.renderList, this ) );
		this.listenTo( this.collection, 'remove', _.bind( () => {

			const pageInfo = this.collection.pageInfo();
			if ( pageInfo.currentPage > 1 && this.collection.paginated().toJSON().length === 0 ) {
				this.collection.prev();
			}

			this.renderList();
			this.pagination.render();
		}, this ) );
	},
	afterRender() {
		this.$list = this.$( '.tvd-content-sets-list' );
		this.$pagination = this.$( '.tvd-content-sets-list-pagination' );

		this.renderList();
		this.renderPagination();
	},
	renderPagination() {
		this.pagination = new TD_SETS.Views.Pagination( {
			el: this.$pagination,
			collection: this.collection,
		} );

		this.pagination.render();
	},
	filterSets( event, dom ) {
		if ( dom.value.length ) {
			this.collection.applyFilters( 'search', dom.value );
		} else {
			this.collection.resetFilters();
		}

		this.renderList();
		this.pagination.toggle( ! this.collection.hasFilters() ).render();
	},
	renderList() {
		this.$list.empty();
		this.itemViews = {};

		if ( this.collection.length === 0 ) {
			return this.$list.html( TVE_Dash.tpl( this.noItemsTemplate ) );
		}

		const paginatedCollection = this.collection.paginated();

		if ( this.collection.hasFilters() && paginatedCollection.size() === 0 ) {
			return this.$list.html( TVE_Dash.tpl( this.noSearchItemsTemplate ) );
		}

		paginatedCollection.forEach( this.renderItem, this );
	},
	renderItem( model ) {
		const view = new TD_SETS.Views.Item( {
			model: model,
			collection: this.collection,
		} );

		this.$list.append( view.render().$el );

		this.itemViews[ model.get( 'ID' ) ] = view;
	},
	addSet() {
		this.openModal( TD_SETS.Views.Modals.Edit, {
			model: new TD_SETS.Models.Set( {} ),
			collection: this.collection,
			width: '810px',
			className: 'tvd-modal tvd-content-set-edit'
		} );
	}
} );
