/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 11:00 AM
 */

/**
 * BreadcrumbLink model
 */
module.exports = Backbone.Model.extend( {
	defaults: {
		ID: '',
		hash: '',
		label: '',
		full_link: false
	},
	/**
	 * we pass only hash and label, and build the ID based on the label
	 *
	 * @param {object} att
	 */
	initialize: function ( att ) {
		if ( ! this.get( 'ID' ) ) {
			this.set( 'ID', att.label.split( ' ' ).join( '' ).toLowerCase() );
		}
		this.set( 'full_link', att.hash.match( /^http/ ) );
	},
	/**
	 *
	 * @returns {String}
	 */
	get_url: function () {
		return this.get( 'full_link' ) ? this.get( 'hash' ) : ('#' + this.get( 'hash' ));
	}
} );
