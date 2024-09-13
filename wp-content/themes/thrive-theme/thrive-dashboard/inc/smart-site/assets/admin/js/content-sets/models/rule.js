module.exports = require( './base' ).extend( {
	idAttribute: 'ID',
	defaults() {
		return {
			content_type: '', //post or course
			content: '',
			field: '',
			operator: '',
			value: [],
			content_label: {
				singular: '',
				plural: '',
			}
		}
	},
	orderSteps: [ 'content', 'field', 'operator', 'value' ],
	getSteps() {
		return this.orderSteps;
	},
	getValues() {
		let _return = [];

		if ( Array.isArray( this.get( 'value' ) ) ) {
			this.get( 'value' ).forEach( val => {
				if ( typeof val === 'object' && typeof val.id !== 'undefined' ) {
					_return.push( val.id );
				}
			} );
		}

		return _return;
	},
	/**
	 * Checks if all the attributes of the model are filled
	 *
	 * @return {boolean}
	 */
	isCompleted() {
		let isCompleted = true

		this.orderSteps.some( step => {

			if ( this.get( 'field' ) && parseInt( this.get( 'field' ) ) === - 1 ) {
				/**
				 * if field === -1 return true.
				 */
				return true;
			}

			if ( this.get( step ).length === 0 ) {
				isCompleted = false;
				return true;
			}
		} );

		return isCompleted;
	},
	/**
	 * Checks if all the attributes of the model are empty
	 *
	 * @return {boolean}
	 */
	isEmpty() {
		let empty = true

		this.orderSteps.some( step => {
			if ( this.get( step ).length > 0 ) {
				empty = false;
				return true;
			}
		} );
		return empty;
	},
	/**
	 * Updates the rule info from DB.
	 * mainly this is for content_label rule key
	 *
	 * @returns {Promise<unknown>}
	 */
	normalize() {
		return new Promise( resolve => {
			wp.apiRequest(
				{
					type: 'POST',
					url: `${TD_SETS.routes.base}/normalize-rule`,
					data: this.toJSON(),
				}
			).always( data => resolve( data ) );

		} );
	}
} );
