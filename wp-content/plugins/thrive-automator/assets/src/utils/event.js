/**
 * Generic event handler for the app
 */
class Event {
	constructor() {
		this.events = {};
		this.filters = {};
		this.actions = {};
		this.activeFilters = [];
		this.activeActions = [];
	}

	//events
	on( eventName, fn ) {
		this.events[ eventName ] = this.events[ eventName ] || [];
		this.events[ eventName ].push( fn );
	}

	off( eventName, fn ) {
		if ( this.events[ eventName ] ) {
			const index = this.events[ eventName ].findIndex( callback => callback === fn )
			this.events[ eventName ].splice( index, 1 );
		}
	}

	trigger( eventName, data ) {
		if ( this.events[ eventName ] ) {
			this.events[ eventName ].forEach( function ( fn ) {
				fn( data );
			} );
		}
	}

	//hooks
	/**
	 * @param {String} filterTag
	 * @param {Function|String} callback
	 * @param {int} [priority]
	 */
	addFilter( filterTag, callback, priority = 10 ) {
		this.filters[ filterTag ] = this.filters[ filterTag ] || [];
		this.filters[ filterTag ].push( {
			fn: callback,
			priority: parseInt( priority )
		} );
		return this;
	}

	/**
	 * @param {String} actionTag
	 * @param {Function|String} callback
	 * @param {int} [priority]
	 */
	addAction( actionTag, callback, priority = 10 ) {
		this.actions[ actionTag ] = this.actions[ actionTag ] || [];
		this.actions[ actionTag ].push( {
			fn: callback,
			priority: parseInt( priority )
		} );

		return this;
	}

	/**
	 * @param {String} tag
	 * @returns {Boolean}
	 */
	hasFilter( tag ) {
		return this.filters?.[ tag ]?.length;
	}

	/**
	 * @param {String} tag
	 * @returns {Boolean}
	 */
	hasAction( tag ) {
		return this.actions?.[ tag ]?.length;
	}

	/**
	 * Applies all registered filters and returns the filtered value
	 * @param tag
	 * @param value
	 * @returns {*|null}
	 */
	applyFilters( tag, value ) {
		if ( arguments.length < 1 ) {
			return typeof value !== 'undefined' ? value : null;
		}
		if ( this.hasFilter( tag ) ) {
			this.activeFilters.push( tag );

			const callbacks = this.filters[ tag ].filter( data => !! data.fn ).sort( function ( a, b ) {
				return a.priority - b.priority;
			} );
			let params = [];

			if ( arguments.length > 2 ) {
				params = Array.prototype.slice.call( arguments, 2 );
			}

			callbacks.forEach( data => {
				if ( data.fn === '__return_true' ) {
					value = true;
				} else if ( data.fn === '__return_false' ) {
					value = false;
				} else {
					value = data.fn.apply( null, [ value, ...[ params ] ] );
				}
			} )

			this.activeFilters.pop();
		}
		return value;
	}

	/**
	 * Does all registered actions
	 * @param tag
	 */
	doAction( tag ) {
		if ( this.hasAction( tag ) ) {
			const params = arguments.length > 1 ? Array.prototype.slice.call( arguments, 1 ) : [],
				callbacks = this.actions[ tag ].filter( data => !! data.fn ).sort( function ( a, b ) {
					return a.priority - b.priority;
				} );
			this.activeActions.push( tag );

			callbacks.forEach( data => {
				data.fn.apply( null, params );
			} );

			this.activeActions.pop();
		}
	}

	/**
	 * Remove an action
	 * @param tag
	 * @param callback
	 * @param priority
	 * @returns {Event}
	 */
	removeAction( tag, callback, priority = 10 ) {
		if ( this.hasAction( tag ) ) {
			if ( typeof callback === 'undefined' ) {
				delete this.actions[ tag ];
				return this;
			}
			this.actions = this.actions.filter( data => ! ( data?.priority === priority && data?.fn === callback ) );
		}

		return this;
	}

	/**
	 * Remove a filter
	 * @param tag
	 * @param callback
	 * @param priority
	 * @returns {Event}
	 */
	removeFilter( tag, callback, priority = 10 ) {
		if ( this.hasFilter( tag ) ) {
			if ( typeof callback === 'undefined' ) {
				delete this.filters[ tag ];
				return this;
			}
			this.filters = this.filters.filter( data => ! ( data?.priority === priority && data?.fn === callback ) );
		}

		return this;
	}

	/**
	 * Checks if the currently performed action is identical to hook
	 * @param tag
	 * @returns {boolean | undefined}
	 */
	doingAction( tag ) {
		return this.activeActions?.includes( tag )
	}

	/**
	 * Checks if the currently performed filter is identical to hook
	 * @param tag
	 * @returns {boolean | undefined}
	 */
	doingFilter( tag ) {
		return this.activeFilters?.includes( tag )
	}
}

export default new Event();
