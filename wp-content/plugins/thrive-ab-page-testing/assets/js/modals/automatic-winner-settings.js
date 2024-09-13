/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/14/2017
 * Time: 1:04 PM
 */
module.exports = TVE_Dash.views.Modal.extend( {
	template: TVE_Dash.tpl( 'modals/html-change-automatic-winner' ),
	events: {
		'click .tvd-modal-submit': 'submit'
	},
	afterInitialize: function ( args ) {
		TVE_Dash.views.Modal.prototype.afterInitialize.apply( this, arguments );

		this.listenTo( this.model, 'change:auto_win_enabled', this.toggle_settings_input );
	},

	afterRender: function () {
		TVE_Dash.views.Modal.prototype.afterRender.apply( this, arguments );
		TVE_Dash.data_binder( this );
		this.toggle_settings_input();
	},
	toggle_settings_input: function () {

		var _inputs = this.$( '#auto-win-settings input' ),
			_auto_win = this.model.get( 'auto_win_enabled' );

		this.$( '#auto-win-enabled' ).prop( 'checked', _auto_win == 1 );

		if ( _inputs.length ) {
			if ( _auto_win == 1 ) {
				_inputs.removeAttr( 'disabled' );
			} else {
				var defaults = this.model.defaults();
				delete defaults.id;
				this.model.set( defaults );
				_inputs.attr( 'disabled', 'disabled' );
			}
		}
	},
	submit: function () {

		if ( ! this.model.isValid( {step: 'winner_settings'} ) ) {
			return;
		}

		var save_options = {
			save_test_settings: true
		};

		this.model.save( save_options, {
			success: _.bind( function () {
				this.close();
			}, this ),
			error: _.bind( function () {
				this.close();
			}, this )
		} );
	}
} );
