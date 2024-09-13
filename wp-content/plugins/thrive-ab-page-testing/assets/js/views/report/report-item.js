/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/27/2017
 * Time: 2:31 PM
 */

var base_view = require( '../base' );

module.exports = base_view.extend( {

	template: TVE_Dash.tpl( 'report/report-item' ),

	tagName: 'tr',

	initialize: function () {
	},

	render: function () {

		this.$el.html( this.template( {model: this.model} ) );

		return this;
	}
} );
