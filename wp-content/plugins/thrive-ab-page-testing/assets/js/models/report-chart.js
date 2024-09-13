/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/7/2017
 * Time: 9:57 AM
 */

var base = require( './base' );

module.exports = base.extend( {
	defaults: function () {
		return _.extend( base.prototype.defaults(), {
			ID: 0,
			title: '',
			x_axis: [],
			y_axis: ''
		} );
	},
	get_route: function () {

		return 'route=report';
	}
} );
