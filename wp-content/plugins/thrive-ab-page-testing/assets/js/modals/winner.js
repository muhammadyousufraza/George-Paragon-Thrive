var test_table = require( './../views/test/table' ),
	variation_winner_modal = require( './variation-winner' );

module.exports = TVE_Dash.views.Modal.extend( {

	template: TVE_Dash.tpl( 'modals/html-winner' ),

	afterInitialize: function () {

		this.model.on( 'winner_selected', function ( test, item ) {
			this.close();
			TVE_Dash.modal( variation_winner_modal, {
				model: item,
				title: '',
				no_close: true,
				dismissible: false
			} );
		}, this );

		return this;
	},

	afterRender: function () {

		var item_template_name = 'modals/winner/test/item/' + this.model.get( 'type' );

		var test_view = new test_table( {
			model: this.model,
			collection: this.collection,
			item_template_name: item_template_name
		} );

		test_view.template = TVE_Dash.tpl( 'modals/winner/test/' + this.model.get( 'type' ) );

		this.$( '#test-view' ).html( test_view.render().$el );
	}
} );
