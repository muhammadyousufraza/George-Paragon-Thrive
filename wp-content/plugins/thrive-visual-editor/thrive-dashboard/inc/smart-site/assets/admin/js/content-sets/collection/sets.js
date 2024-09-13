module.exports = Backbone.Collection.extend( {
	model: require( '../models/set' ),

	/**
	 * @property {number} offset offset
	 */
	offset: 0,
	/**
	 * @property {number} of items per page
	 */
	limit: 8,

	/**
	 * @property {number} of current page
	 */
	currentPage: 1,

	/**
	 * Filter - map of key => value pairs to be sent to the server for filtering
	 */
	_filter: {},

	/**
	 * Checks if the collection has filters
	 *
	 * @return {boolean} has filters
	 */
	hasFilters() {
		return Object.keys( this._filter ).length > 0;
	},

	/**
	 * Reset all filters
	 *
	 * @return {Backbone.Collection} this
	 */
	resetFilters() {
		this.currentPage = 1;
		this.offset = 0;
		this._filter = {};

		return this;
	},

	applyFilters( key, value ) {
		this.currentPage = 1;
		this.offset = 0;
		this._filter[ key ] = value;

		return this;
	},

	/**
	 * Increment the current page and calculates the new offset
	 *
	 * @return {Backbone.Collection} this
	 */
	next() {
		this.currentPage ++;
		this.offset = ( this.currentPage - 1 ) * this.limit;

		return this;
	},
	/**
	 * Decrement the current page and calculates the new offset
	 *
	 * @return {Backbone.Collection} this
	 */
	prev() {
		this.currentPage --;
		this.offset = ( this.currentPage - 1 ) * this.limit;

		return this;
	},
	/**
	 * Based on current items returns them to be used outside:
	 * - used on pagination template
	 *
	 * @return {{next: boolean, prev: boolean, totalPages: number, page: *, currentPage: *}} page information
	 */
	pageInfo() {
		const total = this.length;

		return {
			currentPage: this.currentPage,
			totalPages: Math.ceil( total / this.limit ),
			page: this.currentPage,
			prev: this.offset > 0,
			next: ( this.currentPage * this.limit ) < total,
		}
	},
	paginated() {
		const page = this.currentPage - 1,
			removeIds = [];
		let collection = this.clone();

		if ( this.hasFilters() ) {
			if ( this._filter.search && this._filter.search.length > 0 ) {

				collection.each( model => {
					const title = model.get( 'post_title' ).toLowerCase();
					if ( title.indexOf( this._filter.search ) === - 1 ) {
						removeIds.push( model );
					}
				} );

				for ( let i in removeIds ) {
					collection.remove( removeIds[ i ] );
				}
			}
		}

		collection = _( collection.rest( this.limit * page ) );
		collection = _( collection.first( this.limit ) );

		return collection;
	}
} );