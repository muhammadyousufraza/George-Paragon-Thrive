/**
 * General Archive Modal
 */
module.exports = TVE_Dash.views.Modal.extend( {

	template: TVE_Dash.tpl( 'modals/html-archive' ),

	events: {
		'click .tvd-modal-submit': 'submit'
	},

	initialize: function ( args ) {

		TVE_Dash.views.Modal.prototype.initialize.apply( this, arguments );
		this.$el.addClass( 'tvd-red' );
	},

	render: function () {

		TVE_Dash.views.Modal.prototype.render.apply( this, arguments );
		this.$( '.tvd-modal-close' ).addClass( 'tvd-white-text' );

		return this;
	},

	submit: function () {

		if ( typeof this.data['submit'] !== 'function' ) {
			throw new Error( 'Submit data not implemented' );
		}
		this.data.submit.apply( this, arguments );

		this.close();
	}
} );
