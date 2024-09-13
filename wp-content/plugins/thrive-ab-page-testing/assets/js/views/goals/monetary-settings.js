var settings = require( './settings' );

( function ( $ ) {

	module.exports = settings.extend( {

		template: TVE_Dash.tpl( 'goals/monetary-settings' ),

		events: function () {

			var parent_settings = settings.prototype.events.apply( this, arguments );

			return _.extend( parent_settings, {
				'change #thrive-ab-monetary-services': 'on_service_change'
			} );

		},

		render: function () {

			this.$el.html( this.template( {item: this.model} ) );

			this.$( '.thrive-ab-monetary-service' ).hide();

			this.init_services();

			this.render_goal_pages();

			return this;
		},

		on_service_change: function ( event ) {

			var $services = this.$( '.thrive-ab-monetary-service' ),
				service = event.currentTarget.value;

			$services.hide();

			this.model.set( 'service', service );

			if ( service.length <= 0 ) {
				return;
			}

			$services.closest( '#' + service ).css( 'display', 'block' );
		},

		init_services: function () {

			if ( typeof ThriveAB.monetary_services === 'undefined' || ThriveAB.monetary_services.length <= 0 ) {
				return;
			}

			var $dropdown = this.$( '#thrive-ab-monetary-services' ),
				services = Object.keys( ThriveAB.monetary_services );

			_.each( ThriveAB.monetary_services, function ( service, slug ) {
				var $option = $( '<option/>' )
					.attr( 'value', slug )
					.text( typeof service.label ? service.label : slug );
				$dropdown.append( $option );
			}, this );

			if ( services.length === 1 ) {
				$dropdown.val( services[ 0 ] ).change();
				$dropdown.parents( '.tvd-row' ).first().hide();
			}
		}
	} );

} )( jQuery );
