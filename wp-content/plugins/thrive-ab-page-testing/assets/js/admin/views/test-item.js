/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 4:55 PM
 */

var delete_test_modal = require( './../../modals/delete' );

module.exports = Backbone.View.extend( {
	template: '',
	tagName: 'tr',
	events: {
		'click .tab-delete-test': 'delete_test'
	},
	initialize: function ( attr ) {
		this.template = attr.template;
	},
	render: function () {
		this.$el.html( this.template( {model: this.model} ) );

		return this;
	},
	delete_test: function () {
		if ( this.model.get( 'status' ) !== 'completed' ) {
			return;
		}

		TVE_Dash.modal( delete_test_modal, {
			submit: _.bind( function () {
				TVE_Dash.showLoader();

				var self = this;

				jQuery.ajax( {
					cache: false,
					url: ThriveAbAdmin.ajax.url,
					method: 'POST',
					dataType: 'json',
					data: {
						id: this.model.get( 'id' ),
						page_id: this.model.get( 'page_id' ),
						route: 'deletecompletedtestadmin',
						action: ThriveAbAdmin.ajax.action,
						custom: ThriveAbAdmin.ajax.controller_action,
						nonce: ThriveAbAdmin.ajax.nonce
					}
				} ).done( function ( response ) {

					if ( response.success ) {
						self.collection.remove( self.model );
						TVE_Dash.success( response.text );
					} else {
						TVE_Dash.err( response.text );
					}

					TVE_Dash.hideLoader();
				} );

			}, this ),
			title: '',
			description: TVE_Dash.sprintf( ThriveAbAdmin.t.about_to_delete_variation, ['<strong>' + this.model.get( 'title' ) + '</strong>'] ),
			btn_yes_txt: ThriveAbAdmin.t.yes,
			btn_no_txt: ThriveAbAdmin.t.no,
			'max-width': '20%',
			width: '20%'
		} );
	}
} );
