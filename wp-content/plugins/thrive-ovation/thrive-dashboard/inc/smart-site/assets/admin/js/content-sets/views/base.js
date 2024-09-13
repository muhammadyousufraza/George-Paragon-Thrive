( function ( $ ) {
	module.exports = Backbone.View.extend( {
		events: {
			'click .click': '_call',
			'change .change': '_call',
			'input .input': '_call',
		},
		/**
		 * View Constructor
		 *
		 * @param {Object} options
		 */
		initialize( options ) {
			$.extend( true, this, options );

			if ( options && options.template ) {
				this.template = options.template;
			}

			this.afterInitialize( options );
		},
		/**
		 * Call method for specific events
		 *
		 * @param {Event} event
		 * @return {*} result from handler function
		 */
		_call( event ) {
			const _method = event.currentTarget.dataset.fn;

			if ( typeof this[ _method ] === 'function' ) {
				return this[ _method ].call( this, event, event.currentTarget );
			}
		},
		/**
		 * Appends the template's html into $el
		 *
		 * @return {Backbone.View} the caller view
		 */
		render() {
			if ( typeof this.template === 'string' ) {
				this.template = TVE_Dash.tpl( this.template );
			}

			if ( typeof this.template === 'function' ) {
				this.$el.html( this.template( {model: this.model} ) );
			}

			/**
			 * Used to do stuff after the template is applied.
			 *
			 * Ex: declare the view variables
			 */
			this.afterRender();

			return this;
		},
		/**
		 * Opens a new modal
		 *
		 * @param {Function} modalView
		 * @param {Object} params
		 */
		openModal( modalView, params = {} ) {

			if ( modalView.prototype instanceof TVE_Dash.views.Modal ) {
				params =
					{
						...{
							'max-width': 'calc(100% - 40px)',
							width: '850px',
							in_duration: 200,
							out_duration: 300,
							className: 'tvd-modal tva-modal-create',
							dismissible: true
						}, ...params
					};

				return TVE_Dash.modal( modalView, params );
			}
			// eslint-disable-next-line no-console
			console.warn( 'Invalid type of modal view' )
		},
		/**
		 * Completely destroy the view and un-delegate any events
		 */
		destroy() {
			this.stopListening();
			this.undelegateEvents();
			this.$el.removeData().off();

			return this;
		},
		/**
		 * Overridden in child views
		 */
		afterRender: $.noop,
		afterInitialize: $.noop,
	} );
} )( jQuery );
