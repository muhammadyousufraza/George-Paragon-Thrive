var reports = require( './views/report/report' ),
	variation_collection = require( './collections/variations' ),
	dashboard = require( './views/dashboard' ),
	test_model = require( './models/test' );

(function ( $ ) {

	module.exports = Backbone.Router.extend( {
		view: null,
		$el: $( '#tab-dashboard-wrapper' ),
		routes: {
			'dashboard(/:action)': 'dashboard',
			'test(/:id)': 'reports'
		},
		/**
		 * dashboard route callback
		 */
		dashboard: function ( action ) {
			if ( this.view ) {
				this.view.remove();
			}

			if ( typeof ThriveAB === 'undefined' ) {
				console.log( 'Thrive Optimize have not localized required data !' );
				return;
			}

			this.view = new dashboard( {
				el: this.$el,
				model: new Backbone.Model( ThriveAB.page ),
				collection: new variation_collection( ThriveAB.variations ),
				archived: new variation_collection( ThriveAB.archived ),
			} );

			if ( action === 'start-test' ) {
				this.view.$( '#thrive-ab-start-test' ).trigger( 'click' );
			}
			this.check_license();
		},
		/**
		 * reports route callback
		 */
		reports: function ( id ) {
			if ( this.view ) {
				this.view.remove();
			}

			var model = new test_model( id ? ThriveAB.running_test : ThriveAB.current_test );

			this.view = new reports( {
				el: this.$el,
				model: model
			} );
			this.check_license();
		},
		check_license: function(){
			if ( ThriveAB.license.gp && ThriveAB.license.show_lightbox) {
				TVE_Dash.modal( TVE_Dash.views.LicenseModal, {
					model: {
						title: 'Thrive Optimize',
						license_class: 'grace-period',
						product_class: 'tab',
						license_link: ThriveAB.license.link,
						grace_time: ThriveAB.license.grace_time

					},
					className: 'tvd-modal tvd-license-modal tvd-modal-grace-period',
					width: '950px',
					'max-width': '950px',
				} );
			} else if ( ThriveAB.license.exp && ! ThriveAB.license.gp ) {

				TVE_Dash.modal( TVE_Dash.views.LicenseModal, {
					model: {
						title: 'Thrive Optimize',
						license_class: 'expired',
						product_class: 'tab',
						license_link: ThriveAB.license.link
					},
					className: 'tvd-modal tvd-license-modal tvd-modal-expired',
					no_close: true,
					width: '950px',
					dismissible: false,
					'max-width': '950px',
				} );
				$( '#tab-admin-dashboard-wrapper' ).replaceWith( $('#tab-admin-dashboard-wrapper').clone() );
			}
		}
	} );

})( jQuery );
