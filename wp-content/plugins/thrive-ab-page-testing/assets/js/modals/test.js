var optins_settings = require( '../views/goals/optins-settings' ),
	monetary_settings = require( '../views/goals/monetary-settings' ),
	visits_settings = require( '../views/goals/visits-settings' );

module.exports = TVE_Dash.views.ModalSteps.extend( {

	className: 'tvd-modal tvd-modal-fixed-footer',

	template: TVE_Dash.tpl( 'modals/html-test' ),

	events: function () {

		return _.extend( TVE_Dash.views.ModalSteps.prototype.events, {

			'click .tvd-modal-submit': 'submit',
			'click .thrive-ab-goal': function ( event ) {

				if ( event.currentTarget.classList.contains( 'thrive-ab-disabled' ) ) {
					return false;
				}

				this.$( ".thrive-ab-goal" ).removeClass( "thrive-ab-selected" );
				event.currentTarget.classList.add( "thrive-ab-selected" );


				jQuery( '.tvd-modal-content' ).animate( {
					scrollTop: jQuery( "#thrive-ab-goal-settings" ).offset().top
				}, 500 );


				this.model.set( 'type', event.currentTarget.dataset.goal );
			}
		} );
	},

	initialize: function ( args ) {

		TVE_Dash.views.ModalSteps.prototype.initialize.apply( this, arguments );

		this.listenTo( this.model, 'change:type', this.render_goal_settings );

		this.listenTo( this.model, 'change:auto_win_enabled', this.toggle_settings_input );
	},

	afterRender: function () {

		TVE_Dash.views.ModalSteps.prototype.afterRender.apply( this, arguments );

		TVE_Dash.data_binder( this );

		this.toggle_settings_input();

		var has_forms = this.model.get( 'items' ).where( {has_form: false} ).length > 0;
	},

	gotoStep: function ( index ) {

		TVE_Dash.views.ModalSteps.prototype.gotoStep.apply( this, arguments );

		var _tabs = this.$step.find( '.tvd-tab' );

		if ( _tabs.length ) {
			_tabs.addClass( 'tvd-disabled' );
			jQuery( _tabs[ index ] ).removeClass( 'tvd-disabled' ).find( 'a' ).first().trigger( 'click' );
		}

		return this;
	},

	beforeNext: function () {

		return this.model.isValid( {
			step: this.currentStep
		} );
	},

	toggle_settings_input: function () {

		if ( this.model.get( 'auto_win_enabled' ) === true ) {
			this.$( '#auto-win-settings' ).show();
		} else {
			this.model.set( this.model.defaults() );
			this.$( '#auto-win-settings' ).hide();
		}

	},

	render_goal_settings: function () {

		var _options = {
			model: this.model
		};

		delete this.model.attributes.service;

		if ( this.goal_settings_view ) {
			this.goal_settings_view.remove();
		}

		switch ( this.model.get( 'type' ) ) {
			case 'monetary':
				this.goal_settings_view = new monetary_settings( _options );
				break;
			case 'visits':
				this.goal_settings_view = new visits_settings( _options );
				break;
			case 'optins':
				this.goal_settings_view = new optins_settings( _options );
				break;
		}

		this.$( '#thrive-ab-goal-settings' ).html( this.goal_settings_view.render().$el );
	},

	submit: function () {

		if ( ! this.model.isValid() ) {
			return false;
		}

		TVE_Dash.showLoader( true );

		this.model.save( null, {
			success: _.bind( function () {
				location.href = ThriveAB.page.edit_link;
			}, this ),
			error: _.bind( function () {
				TVE_Dash.hideLoader();
				TVE_Dash.err( 'Test could not be saved' );
			} )
		} );
	}
} );
