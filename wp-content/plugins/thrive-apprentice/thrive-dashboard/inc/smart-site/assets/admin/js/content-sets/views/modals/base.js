( function ( $ ) {

	module.exports = TVE_Dash.views.Modal.extend( {
		template: '',
		events: {
			'click .click': '_call',
			'input .input': '_call',
			'keyup .keyup': '_call',
			'change .change': '_call',
		},

		/**
		 * Call method for specific events
		 *
		 * @param {Event} event
		 * @return {*} the result returned by the handler function
		 */
		_call( event ) {
			const _method = event.currentTarget.dataset.fn;

			if ( typeof this[ _method ] === 'function' ) {
				return this[ _method ].call( this, event, event.currentTarget );
			}
		},

		/**
		 * Called after the view has been render
		 *
		 * @return {this} fluent interface
		 */
		afterRender() {
			return this;
		},
	} );
} )( jQuery );
