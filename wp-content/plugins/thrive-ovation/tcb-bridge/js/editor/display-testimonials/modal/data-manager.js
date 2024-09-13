const Utils = require( './utils' ),
	cachedResults = {};

class DataManager {
	/**
	 * @param {Array}  values
	 * @param {number} itemsPerPage
	 * @param {Object} filters
	 * @param {number} page
	 * @return {Promise}
	 */
	static getTestimonials( values = [], itemsPerPage, filters = {}, page = 1 ) {
		let alreadyGotEverything, key;

		if ( values && values.length ) {
			/* check if we already have all the testimonials that are required */
			alreadyGotEverything = values.every( value => typeof cachedResults[ value ] !== 'undefined' );
		} else {
			key = values && values.length ? JSON.stringify( values ) : `${page}-${itemsPerPage}-${JSON.stringify( filters )}`;
		}

		return new Promise( resolve => {
			if ( typeof alreadyGotEverything !== 'undefined' && alreadyGotEverything ) {
				resolve( Utils.deepCopy( {
					items: values.map( value => cachedResults[ value ] ),
				} ) );
			} else if ( typeof key !== 'undefined' && typeof cachedResults[ key ] !== 'undefined' ) {
				resolve( Utils.deepCopy( cachedResults[ key ] ) );
			} else {
				TVE.$.ajax( {
					url: TVE.CONST.routes.base + '/display-testimonials/testimonials',
					data: {
						items_per_page: itemsPerPage,
						values, filters, page,
					},
					headers: {
						'X-WP-Nonce': TVE.CONST.rest_nonce,
					},
				} ).done( response => {
					cachedResults[ key ] = response;

					if ( response.items && response.items.length ) {
						/* also cache each ID separately in case we need to return only certain values */
						response.items.forEach( item => cachedResults[ item.ID ] = item );
					}

					resolve( Utils.deepCopy( response ) );
				} );
			}
		} );
	}

	/**
	 * @param {Object} filters
	 * @return {Promise}
	 */
	static getTestimonialCount( filters = {} ) {
		const key = JSON.stringify( filters );

		return new Promise( resolve => {
			if ( typeof cachedResults[ key ] !== 'undefined' ) {
				resolve( cachedResults[ key ] );
			} else {
				TVE.$.ajax( {
					url: TVE.CONST.routes.base + '/display-testimonials/testimonials-count',
					data: { filters },
					headers: {
						'X-WP-Nonce': TVE.CONST.rest_nonce,
					},
				} ).done( response => {
					cachedResults[ key ] = response;
					resolve( response );
				} );
			}
		} );
	}

	static getTags() {
		return new Promise( resolve => {
			if ( typeof TVE.displayTestimonials.allTags !== 'undefined' ) {
				resolve( Utils.deepCopy( TVE.displayTestimonials.allTags ) );
			} else {
				TVE.$.ajax( {
					url: TVE.CONST.routes.base + '/display-testimonials/tags',
					headers: {
						'X-WP-Nonce': TVE.CONST.rest_nonce,
					},
				} ).done( response => {
					TVE.displayTestimonials.allTags = response;
					resolve( Utils.deepCopy( TVE.displayTestimonials.allTags ) );
				} );
			}
		} );
	}

	static getSets() {
		return TVE.displayTestimonials.sets;
	}

	static updateSetCache( sets ) {
		TVE.displayTestimonials.sets = sets;
	}
}

module.exports = DataManager;
