window.TVE_Dash = window.TVE_Dash || {};
TVE_Dash.views = TVE_Dash.views || {};
( function ( $ ) {
	require( './materialize/leanModal' );
	window.Vel = require( './materialize/velocity.min' );
	require( './materialize/global' );
	require( './materialize/forms' );
	require( './util/util' );
	/**
	 * base class for modals
	 *
	 * backbone view wrapped over a modal div from materialize
	 */
	TVE_Dash.views.Modal = Backbone.View.extend( {
		className: 'tvd-modal',
		id: 'tvd-modal-base',
		/**
		 * .tvd-modal-content div reference
		 */
		$_content: '',
		/* this will be populated with data coming from params when instantiating the view / modal */
		data: {},

		/**
		 * Array of stored child views - to remove them when this view is destroyed
		 */
		$$childViews: [],

		/**
		 * Get the main modal action button. Usually, this is the "Save" button.
		 * Overwrite this if custom behaviour is needed
		 *
		 * @return {JQuery}
		 */
		getModalActionButton() {
			return this.$( '.tvd-modal-save' ).filter( ':visible' );
		},

		/**
		 * used to setup this.data field - it will set all constructor arguments into this.data so that they are available
		 *
		 * @param {Object} args
		 */
		initialize( args ) {

			this.beforeInitialize( args );

			this.data = args;
			const self = this;

			_.each( args, function ( v, k ) {

				if ( typeof self[ k ] === 'undefined' ) {
					self[ k ] = v;
				}
			} );

			this.afterInitialize( args );
		},
		/**
		 * we should not override this, use afterRender() instead
		 * this does not open the modal, it just renders it and appends it to the body tag
		 *
		 * @return {TVE_Dash.views.Modal}
		 */
		render() {
			this.$el.empty();
			this.$el.appendTo( 'body' );
			// make the whole data object available to the template
			this.data = _.extend( {
				model: this.model
			}, this.data );

			if ( typeof this.template === 'string' ) {
				this.template = TVE_Dash.tpl( this.template );
			}

			this.$el.html( this.template( this.data ) );

			if ( this.data[ 'max-width' ] ) {
				/* max-width should always be sent in % */
				this.$el.css( 'max-width', this.data[ 'max-width' ] );
			} else {
				this.$el.css( 'max-width', '' );
			}

			this.$el.css( 'max-height', this.data[ 'max-height' ] || '' );

			if ( this.data.no_close ) {
				this.$el.find( '.tvd-modal-close' ).remove();
			} else if ( ! this.$el.children( '.tvd-modal-close' ).length ) {
				this.$el.append( '<a href="javascript:void(0)" class="tvd-modal-action tvd-modal-close tvd-modal-close-x"><i class="tvd-icon-close2"></i></a>' );
			}

			if ( this.data.title ) {
				let $title = this.$el.find( 'h3.tvd-modal-title' );
				if ( ! $title.length ) {
					$title = $( '<h3 class="tvd-modal-title"></h3>' );
					this.$el.find( '.tvd-modal-content' ).prepend( $title );
				}
				$title.html( this.data.title );
			}

			if ( this.data.width ) {
				this.$el.css( 'width', this.data.width );
			}
			if ( this.data.height ) {
				this.$el.css( 'height', this.data.height + 'px' );
			}
			this.$_content = this.$el.find( '.tvd-modal-content' );

			this.afterRender();

			/**
			 * when the autocomplete opens, we need to scroll the .tvd-modal-content so that the results are visible
			 */
			this.$el.on( 'autocompleteopen', _.bind( this.autocomplete_scroll, this ) );

			this.$el.on( 'tvdpickeropen', _.bind( this.pickadate_scroll, this ) );

			return this;
		},
		/**
		 * called when a jquery autocomplete widget is opened inside a modal
		 * scrolls the modal frame to contain as much of the autocomplete element as possible
		 *
		 * @param event
		 * @param ui
		 */
		autocomplete_scroll( event ) {
			const $input = $( event.target ),
				$ul = $input.parent().find( 'ul.ui-autocomplete' ),
				ui_height = $input.outerHeight() + $ul.outerHeight(),
				ui_top = $input.offset().top,
				c_top = this.$_content.offset().top,
				delta_scroll = ui_top - c_top + this.$_content.scrollTop() - 25;

			if ( ui_height + ui_top > this.$_content.outerHeight() + c_top ) {
				this.$_content.animate( {
					scrollTop: delta_scroll
				} );
			}
		},
		/**
		 * called when a datepicker (pickadate) is opened inside this modal
		 * it scroll the modal frame so that the date picker is fully visible
		 *
		 * @param {Object} event jQuery event instance
		 * @param {Object} P Picker instance
		 */
		pickadate_scroll( event, P ) {
			const $input = $( event.target ),
				$picker = P.$root,
				picker_height = $picker.outerHeight( true ) + 40,
				picker_top = $input.offset().top - picker_height / 2,
				c_top = this.$_content.offset().top;

			if ( picker_top + picker_height > this.$_content.outerHeight() + c_top ) {
				this.$_content.animate( {
					scrollTop: picker_height + picker_top - c_top - this.$_content.outerHeight() + this.$_content.scrollTop() - 25
				} );
			} else if ( picker_top < c_top ) {
				this.$_content.animate( {
					scrollTop: this.$_content.scrollTop() - ( c_top - picker_top )
				} );
			}
		},
		/**
		 * auto-focus the first input and bind the ENTER event
		 *
		 * @param {Object} [$root] optional, jquery wrapper over a DOM element
		 */
		input_focus( $root ) {
			$root = $root || this.$el;

			const $actionButton = this.getModalActionButton();
			const $textInputs = $root.find( 'input:not(.tvd-no-focus,:checkbox,:radio,:hidden)' ).filter( ':visible' );

			/**
			 * find the first focusable text input (or textarea) or action button
			 */
			const $autoFocus = $textInputs.add( this.$( 'textarea' ) ).add( $actionButton ).filter( ':visible' ).not( '.tvd-no-focus' ).first();

			if ( $autoFocus.length ) {
				requestAnimationFrame( () => $autoFocus.first().focus().select() );
			}

			/**
			 * Setup keyup listeners on the text inputs
			 *
			 * @type {*|number|bigint|T|T|undefined}
			 */
			$textInputs.not( '.tvd-skip-modal-save' ).off( 'keyup.tvd-save' ).on( 'keyup.tvd-save', function ( e ) {
				if ( e.which === 13 ) {
					/**
					 * `requestAnimationFrame` so that any other `change` events are processed first and model validation can be correctly performed
					 */
					requestAnimationFrame( () => {
						if ( $actionButton.css( 'pointer-events' ) !== 'none' ) {
							$actionButton.trigger( 'click' );
						}
					} );

					return false;
				}
				return true;
			} );
		},
		/**
		 * triggered immediately after the rendering is completed (after the template is added to DOM) but before showing the modal
		 * override this to populate extra stuff in the view
		 */
		afterRender() {
			return this;
		},
		/**
		 * open the modal
		 * options for the modal can be sent when instantiating the view
		 *
		 * @return {TVE_Dash.Modal}
		 */
		open() {
			const self = this;

			const options = _.extend( {
				in_duration: 200,
				out_duration: 300,
				dismissible: true,
				beforeClose() {
					self.beforeClose();
				},
				ready() {
					self.onOpen.call( self );
					/**
					 * allow also 'afterOpen' callback
					 */
					if ( typeof self.afterOpen === 'function' ) {
						self.afterOpen.call( self );
					}
					if ( ! self.noMaterialize ) {
						TVE_Dash.materialize( self.$el );
					}
					self.input_focus();

					if ( typeof self.afterMaterialize === 'function' ) {
						self.afterMaterialize.call( self );
					}
				},
				complete() {
					delete TVE_Dash.opened_modal_view;
					self.$el.css( 'width', '' ).css( 'height', '' );
					self.destroy().remove();
					self.onClose.call( self );
					/**
					 * allow also 'afterClose' callback
					 */
					if ( typeof self.afterClose === 'function' ) {
						self.afterClose.call( self );
					}
				}
			}, this.data );

			this.modal_options = options;

			this.$el.openModal( options );

			TVE_Dash.opened_modal_view = this;

			return this;
		},
		close() {
			this.$el.closeModal( this.modal_options );
			return this;
		},
		/**
		 * triggered after the modal has been opened
		 */
		onOpen() {
		},
		/**
		 * triggered after the modal has been closed and the html has been removed
		 */
		onClose() {
		},
		/**
		 * triggered before the modal has been closed and the html has been removed
		 */
		beforeClose() {
		},
		/**
		 * show a preloader over this modal
		 */
		showLoader() {
			let _loader = this.$( '.tvd-modal-preloader-wrapper' );
			if ( ! _loader.length ) {
				_loader = $( TVE_Dash.tpl( 'modal-loader', {} ) );
				this.$el.append( _loader );
			}

			_loader.find( '.tvd-modal-preloader' ).css( {
				top: ( this.$el.outerHeight() / 2 ) + 'px'
			} );

			/* we do this to avoid calling the loader multiple times */
			requestAnimationFrame( () => {
				if ( ! this.isLoading ) {
					_loader.fadeIn( 300 );
					this.isLoading = true;
				}
			} );

			this.$el.addClass( 'tvd-modal-disable' );

			return this;
		},
		/**
		 * hide the preloader
		 */
		hideLoader() {
			this.$el.removeClass( 'tvd-modal-disable' );
			requestAnimationFrame( () => this.$( '.tvd-modal-preloader-wrapper' ).fadeOut( 300 ) );
			this.isLoading = false;

			return this;
		},
		/**
		 * set the loading state on a button
		 *
		 * @param $btn
		 * @return {*}
		 */
		btnLoading( $btn ) {
			return jQuery( $btn ).addClass( 'tvd-disabled' ).prop( 'disabled', true );
		},
		beforeInitialize() {
			return this;
		},
		afterInitialize() {
			return this;
		},
		/**
		 * Add one or more child view reference. Can receive any number of arguments
		 *
		 * @param {Backbone.View|Backbone.View[]} views
		 *
		 * @return {Backbone.View} the caller view
		 */
		addChild( ...views ) {
			if ( Array.isArray( views[ 0 ] ) ) {
				views = views[ 0 ];
			}
			this.$$childViews = this.$$childViews.concat( views );

			return this;
		},
		/**
		 * Completely destroy the view and un-delegate any events
		 */
		destroy() {
			this.stopListening();
			this.undelegateEvents();
			this.$el.removeData().off();

			this.$$childViews.filter( i => i ).forEach( view => {
				if ( typeof view?.destroy === 'function' ) {
					view.destroy();
				}
			} );

			return this;
		}
	} );

	$( document ).ready( function () {
		if ( ! TVE_Dash.views.VideoModal ) {
			TVE_Dash.views.VideoModal = TVE_Dash.views.Modal.extend( {
				template: TVE_Dash.tpl( 'modal-video-modal' ),
				className: 'tvd-modal video-container',

				initialize( options ) {
					this.source = options.source;
				},

				afterRender() {
					this.$el.find( 'iframe' ).attr( 'src', `https://www.youtube.com/embed/${this.source}` );
				},

			} );

			$( 'body' ).on( 'click', '.tvd-open-video', function ( e ) {
				let $target = $( e.target );
				if ( ! $target.hasClass( 'tvd-open-video' ) ) {
					$target = $target.parents( '.tvd-open-video' );
				}
				TVE_Dash.modal( TVE_Dash.views.VideoModal, {
					'max-width': '80%',
					source: $target.attr( 'data-source' ),
				} );
			} );
		}


	} );
} )( jQuery );
