/**
 * @description Base View
 * @extends Backbone.View
 */
module.exports = Backbone.View.extend( {

	events: {
		'click .click': '_call',
		'input .input': '_call',
		'change .change': '_call',
		'mousedown .mousedown': '_call',
		'mouseenter .mouseenter': '_call',
		'mouseup .mouseup': '_call',
		'keyup .keyup-enter': '_keyup_enter'
	},

	initialize: function () {

		this.render();
	},

	_call: function ( e ) {
		/**
		 * Do not allow actions on disabled controls
		 */
		if ( e.currentTarget.disabled || e.currentTarget.classList.contains( 'tve-disabled' ) ) {
			return false;
		}

		var m = e.currentTarget.getAttribute( 'data-fn-' + e.type ) || e.currentTarget.getAttribute( 'data-fn' );

		if ( m && m === '__return_false' ) {
			e.stopPropagation();
			e.preventDefault();
			return false;
		}

		if ( typeof this[m] === 'function' ) {
			return this[m].call( this, e, e.currentTarget );
		}

		/**
		 * call external function on the base TVE object
		 */
		if ( m && m.indexOf( 'f:' ) === 0 ) {
			var fn = TVE, parts = m.split( ':' )[1].split( '.' ), context = window;
			while ( fn && parts.length ) {
				context = fn;
				fn = fn[parts.shift()];
			}
			if ( typeof fn === 'function' ) {
				return fn.call( context, e );
			}
		}
	},

	render: function () {
	}

} );
