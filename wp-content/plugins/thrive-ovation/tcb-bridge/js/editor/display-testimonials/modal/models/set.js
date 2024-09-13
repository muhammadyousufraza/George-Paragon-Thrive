const Utils = require( '../utils' );

module.exports = Backbone.Model.extend( {
	idAttribute: 'id',
	urlRoot: TVE.CONST.routes.base + '/display-testimonials/sets',
	getType() {
		return this.get( 'type' );
	},
	getItems() {
		const items = this.get( this.isStatic() ? 'testimonials' : 'tags' );

		return items ? items.map( item => parseInt( item ) ) : [];
	},
	setItems( items ) {
		this.set( this.isStatic() ? 'testimonials' : 'tags', items );

		return this;
	},
	getOrdering() {
		return this.get( 'ordering' ) ? this.get( 'ordering' ) : Utils.getDefaultOrderingOptions( this.isStatic() );
	},
	isStatic() {
		return this.getType() === 'static';
	},
	getCount() {
		return this.get( 'count' ) ? this.get( 'count' ) : 0;
	},
	sync( method, model, options ) {
		options = options || {};

		options.cache = false;

		const beforeSend = options.beforeSend;

		options.beforeSend = function ( xhr ) {
			/* add rest nonce for backend security */
			xhr.setRequestHeader( 'X-WP-Nonce', TVE.CONST.rest_nonce );

			if ( typeof beforeSend === 'function' ) {
				return beforeSend.apply( this, arguments );
			}
		};

		return Backbone.sync( method, model, options );
	},
} );
