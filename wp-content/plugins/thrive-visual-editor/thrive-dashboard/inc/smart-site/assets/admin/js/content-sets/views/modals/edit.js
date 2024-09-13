module.exports = require( './base' ).extend( {
	template: 'modals/edit',
	afterInitialize() {
		this.listenTo( this.collection, 'reset', _.bind( this.close, this ) );
	},
	afterRender() {
		this.$_content = this.$el;

		this.$( '.tvd-content-set-form' ).html( ( new TD_SETS.Views.Form( {
			model: this.model,
			collection: this.collection
		} ) ).render().$el );
	},
} );
