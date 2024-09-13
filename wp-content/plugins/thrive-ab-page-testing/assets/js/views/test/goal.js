var base = require( '../base' );

module.exports = base.extend( {
	initialize: function () {
		this.template = TVE_Dash.tpl( 'test/item/goal/' + this.model.get( 'type' ) );
	},
	render: function () {

		this.$el.html( this.template( {} ) );

		return this;
	}
} );
