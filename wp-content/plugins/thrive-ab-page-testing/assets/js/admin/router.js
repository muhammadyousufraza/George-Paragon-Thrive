/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 9:51 AM
 */

var BreadcrumbsCollection = require( './collections/breadcrumbs' ),
	BreadcrumbsView = require( './views/breadcrumbs' ),
	DashboardView = require( './views/dashboard' );

(function ( $ ) {

	module.exports = Backbone.Router.extend( {
		view: null,
		$el: $( '#tab-admin-dashboard-wrapper' ),
		routes: {
			'dashboard': 'dashboard'
		},
		breadcrumbs: {
			col: null,
			view: null
		},
		/**
		 * init the breadcrumbs collection and view
		 */
		init_breadcrumbs: function () {
			this.breadcrumbs.col = new BreadcrumbsCollection();
			this.breadcrumbs.view = new BreadcrumbsView( {
				collection: this.breadcrumbs.col
			} )
		},
		/**
		 * set the current page - adds the structure to breadcrumbs and sets the new document title
		 *
		 * @param {string} section page hierarchy
		 * @param {string} label current page label
		 *
		 * @param {Array} [structure] optional the structure of the links that lead to the current page
		 */
		set_page: function ( section, label, structure ) {
			this.breadcrumbs.col.reset();
			structure = structure || {};
			/* Thrive Dashboard is always the first element */
			this.breadcrumbs.col.add_page( ThriveAbAdmin.dash_url, ThriveAbAdmin.t.Thrive_Dashboard, true );

			_.each( structure, _.bind( function ( item ) {
				this.breadcrumbs.col.add_page( item.route, item.label );
			}, this ) );
			/**
			 * last link - no need for route
			 */
			this.breadcrumbs.col.add_page( '', label );
			/* update the page title */
			var $title = $( 'head > title' );
			if ( ! this.original_title ) {
				this.original_title = $title.html();
			}
			$title.html( label + ' &lsaquo; ' + this.original_title );
		},
		/**
		 * dashboard route callback
		 */
		dashboard: function () {
			this.set_page( 'dashboard', ThriveAbAdmin.t.Dashboard );
			var self = this;
			TVE_Dash.showLoader();

			if ( this.view ) {
				this.view.remove();
			}

			jQuery.ajax( {
				cache: false,
				url: ThriveAbAdmin.ajax.url,
				method: 'POST',
				dataType: 'json',
				data: {
					route: 'testsforadmin',
					action: ThriveAbAdmin.ajax.action,
					custom: ThriveAbAdmin.ajax.controller_action,
					nonce: ThriveAbAdmin.ajax.nonce
				}
			} ).done( function ( response ) {
				self.view = new DashboardView( {
					running_tests: new Backbone.Collection( response.running_tests ),
					completed_tests: new Backbone.Collection( response.completed_tests ),
					dashboard_stats: response.dashboard_stats
				} );

				self.$el.html( self.view.render().$el );
				if ( ThriveAbAdmin.license.gp && ThriveAbAdmin.license.show_lightbox) {
					TVE_Dash.modal( TVE_Dash.views.LicenseModal, {
						model: {
							title: 'Thrive Optimize',
							license_class: 'grace-period',
							product_class: 'tab',
							license_link: ThriveAbAdmin.license.link,
							grace_time: ThriveAbAdmin.license.grace_time

						},
						className: 'tvd-modal tvd-license-modal tvd-modal-grace-period',
						width: '950px',
						'max-width': '950px',
					} );
				} else if ( ThriveAbAdmin.license.exp && ! ThriveAbAdmin.license.gp ) {

					TVE_Dash.modal( TVE_Dash.views.LicenseModal, {
						model: {
							title: 'Thrive Optimize',
							license_class: 'expired',
							product_class: 'tab',
							license_link: ThriveAbAdmin.license.link
						},
						className: 'tvd-modal tvd-license-modal tvd-modal-expired',
						no_close: true,
						width: '950px',
						dismissible: false,
						'max-width': '950px',
					} );
					$( '#tab-admin-dashboard-wrapper' ).replaceWith( $('#tab-admin-dashboard-wrapper').clone() );
				}
				TVE_Dash.hideLoader();
			} );
		}
	} );
})( jQuery );
