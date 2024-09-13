var base_view = require( '../base' );

module.exports = base_view.extend( {
	template: TVE_Dash.tpl( 'goals/optins-settings' ),

	render: function () {

		this.$el.html( this.template() );

		return this;
	}
} );
