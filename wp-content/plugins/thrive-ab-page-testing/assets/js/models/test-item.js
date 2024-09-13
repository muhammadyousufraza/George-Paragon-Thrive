var base = require( './base' );

module.exports = base.extend( {

	idAttribute: 'id',

	defaults: function () {
		return {
			id: '',
			revenue: 0
		};
	},
	get_route: function () {

		return 'route=testitem';

	},
	validate: function ( attrs, options ) {

		var _errors = [];

		if ( options.test instanceof Backbone.Model ) {

			var test_type = options.test.get( 'type' ),
				validator_callback = 'validate_' + test_type;

			if ( test_type && typeof this[validator_callback] === 'function' ) {
				_errors = this[validator_callback]( attrs, options );
			}
		}

		return _errors.length ? _errors : undefined;
	},

	validate_monetary: function ( attrs, options ) {

		var _errors = [];

		if ( isNaN( parseFloat( attrs.revenue ) ) ) {
			_errors.push( this.validation_error( 'revenue', 'invalid revenue' ) );
		}

		return _errors;
	},

	validate_visits: function ( attrs, options ) {

		var _errors = [];

		return _errors;
	}
} );
