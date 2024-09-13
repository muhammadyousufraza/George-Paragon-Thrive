module.exports = TVE_Dash.views.Modal.extend( {

	events: {
		'click .tvd-modal-submit': function () {
			TVE_Dash.showLoader( true );
			location.reload();
		}
	},

	template: TVE_Dash.tpl( 'modals/variation-winner' ),

	afterInitialize: function () {

		this.model.set( 'label', TVE_Dash.sprintf( ThriveAB.t.variation_winner, this.model.get( 'title' ) ) );

		/**
		 * add custom class to modal
		 */
		this.$el.addClass( 'thrive-ab-set-winner' );
	}

} );
