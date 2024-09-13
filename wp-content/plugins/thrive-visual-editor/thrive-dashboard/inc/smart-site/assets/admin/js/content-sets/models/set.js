const RulesCollection = require( '../collection/rules' );

module.exports = require( './base' ).extend( {
	idAttribute: 'ID',
	allowEmptyRules: false,
	defaults() {
		return {
			post_title: '',
			post_content: new RulesCollection(),
		}
	},
	initialize( options = {} ) {
		if ( options.post_content instanceof Backbone.Collection ) {
			options.post_content = jQuery.extend( true, [], options.post_content.toJSON() );
		}
		if ( options.post_content ) {
			this.set( 'post_content', new RulesCollection( options.post_content ) );
		}
	},
	parse( data ) {
		if ( data.post_content instanceof Backbone.Collection ) {
			data.post_content = jQuery.extend( true, [], data.post_content.toJSON() );
		}

		if ( data.post_content ) {
			/**
			 * This is not optimal but gets the job done
			 */
			this.get( 'post_content' ).reset( data.post_content );
			data = [];
		}
		return data;
	},
	url( path = '', params = {} ) {
		return this.buildUrl( [
			TD_SETS.routes.base,
			this.get( 'ID' ),
			path
		].filter( i => i ).join( '/' ), params );
	},
	getRules() {
		return this.get( 'post_content' );
	},
	/**
	 * Validates current model
	 *
	 * @param {Object} data
	 * @return {undefined|Array} result
	 */
	validate( data = {} ) {
		const errors = [];
		if ( ! data.post_title ) {
			errors.push( this.validation_error( 'post_title', 'Title is empty!' ) );
		}

		if ( this.allowEmptyRules === false && ! data.post_content.isCompleted() ) {
			errors.push( this.validation_error( 'post_content', 'You must add some content' ) );
		}

		if ( errors.length ) {
			return errors;
		}
	},
} );
