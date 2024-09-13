module.exports = require( './base' ).extend( {
	template: 'tva-c-s-pagination',
	afterInitialize() {
		this.listenTo( this.collection, 'reset', this.render );
		this.listenTo( this.collection, 'page-changed', this.render );
	},
	/**
	 * Toggle Pagination
	 * @param {boolean} status
	 */
	toggle( status ) {
		this.$el.toggle( !! status );

		return this;
	},
	render() {
		const pageInfo = this.collection.pageInfo();
		this.$el.html( pageInfo.totalPages > 1 ? TVE_Dash.tpl( this.template )( pageInfo ) : '' );
	},
	/**
	 * Callback in case prev button is clicked
	 */
	previousPage() {
		this.collection.prev().trigger( 'page-changed' );
	},
	/**
	 * Callback in case next button is clicked
	 */
	nextPage() {
		this.collection.next().trigger( 'page-changed' );
	},
} );
