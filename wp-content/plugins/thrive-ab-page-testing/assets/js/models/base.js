(function ( $ ) {
	module.exports = Backbone.Model.extend( {

		idAttribute: 'ID',

		defaults: function () {
			return {
				ID: ''
			}
		},

		url: function () {

			var url = ThriveAB.ajax.get_url( this.get_action() + '&' + this.get_route() );

			if ( $.isNumeric( this.get( 'ID' ) ) ) {
				url += '&ID=' + this.get( 'ID' );
			}

			return url;
		},
		get_action: function () {

			return 'action=' + ThriveAB.ajax.controller_action;
		},
		get_route: function () {

			return 'route=no_route';
		},

		validation_error: function ( field, message, callback ) {

			return {
				field: field,
				message: message,
				callback: callback
			};
		}
	} );
})( jQuery );
