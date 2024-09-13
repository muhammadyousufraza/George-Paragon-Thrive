/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 10:56 AM
 */

var base = require( './base' ),
	BreadcrumbLink = require( '../models/breadcrumb-link' );
/**
 * Breadcrumb links collection
 */
module.exports = base.extend( {
	model: Backbone.Model.extend( {
		defaults: {
			hash: '',
			label: ''
		}
	} ),
	/**
	 * helper function allows adding items to the collection easier
	 *
	 * @param {string} route
	 * @param {string} label
	 */
	add_page: function ( route, label ) {
		var _model = new BreadcrumbLink( {
			hash: route,
			label: label
		} );
		return this.add( _model );
	}
} );
