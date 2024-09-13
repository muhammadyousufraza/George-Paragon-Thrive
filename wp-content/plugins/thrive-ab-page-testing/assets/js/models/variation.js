var base = require( './base' );

module.exports = base.extend( {

	defaults: function () {

		return _.extend( base.prototype.defaults(), {
			is_control: false,
			traffic: 0
		} );
	},

	get_route: function () {

		return 'route=variations';
	}
} );
