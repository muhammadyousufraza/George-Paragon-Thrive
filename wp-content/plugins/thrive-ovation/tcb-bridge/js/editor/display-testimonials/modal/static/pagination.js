const Constants = require( '../constants' );

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/pagination',
	beforeInitialize( attr ) {
		this.currentPage = 1;
		this.totalItems = attr.totalItems;
		this.itemsPerPage = attr.itemsPerPage;
		this.lastPage = Math.ceil( this.totalItems / this.itemsPerPage );
		this.dataModel = attr.dataModel;

		this.listenTo( this.dataModel, 'page-changed', this.render );
	},
	afterRender() {
		this.$( '[data-fn="openPrevPage"]' ).toggle( this.currentPage > 1 );
		this.$( '[data-fn="openNextPage"]' ).toggle( this.currentPage < this.lastPage );

		this.$( '[data-fn="openFirstPage"]:not(.first-page-arrow)' ).toggle( this.currentPage > 2 );
		this.$( '[data-fn="openLastPage"]:not(.last-page-arrow)' ).toggle( this.currentPage < this.lastPage - 1 );

		this.$( '.pagination-left-dots' ).toggle( this.currentPage > 3 );
		this.$( '.pagination-right-dots' ).toggle( this.currentPage < this.lastPage - 2 );
	},
	openPrevPage() {
		this.setPage( this.currentPage - 1 );
	},
	openNextPage() {
		this.setPage( this.currentPage + 1 );
	},
	openFirstPage() {
		this.setPage( 1 );
	},
	openLastPage() {
		this.setPage( this.lastPage );
	},
	setPage( page ) {
		this.currentPage = page;
		this.triggerPageChange( page );
		return this;
	},
	triggerPageChange( page ) {
		this.dataModel.trigger( 'page-changed', page );
	},
	onInputChange: _.debounce( function ( event ) {
		let value = parseFloat( event.target.value );

		/* validation */
		if ( ( value < 0 ) || ( value > 1000 ) || ! Number.isInteger( value ) ) {
			value = Constants.ITEMS_PER_PAGE;

			TVE.$( event.target ).val( parseInt( value ) );
		}

		this.dataModel.trigger( 'items-per-page-changed', value );
	}, 500 ),
} );
