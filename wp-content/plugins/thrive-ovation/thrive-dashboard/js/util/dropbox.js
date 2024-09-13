( function ( $ ) {
	/**
	 * Connects with Dropbox "Dropins" functionality
	 *
	 * @type {*|{}}
	 */
	window.Dropbox = window.Dropbox || {};

	const D = window.Dropbox;
	module.exports = {
		/**
		 * Loads the dropbox api dropin script
		 * @private
		 */
		_load() {
			if ( ! D.__loaded ) {
				D.__loaded = true;
				$( '<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js"></script>' ).appendTo( 'head' );
			}
		},
		/**
		 * Initialize with an application key
		 *
		 * @param appKey
		 */
		init( appKey ) {
			D.appKey = appKey;
			this._load();
		},
		/**
		 * Render a "Choose" button
		 *
		 * @param {JQuery} $container
		 * @param {Object} options
		 * @param {String} selectedFile the currently selected file
		 * @param {Function} onSelected callback to be applied when a file has been selected
		 *
		 * @return {Object}
		 */
		chooser( $container, options = {}, selectedFile = null, onSelected = null ) {
			if ( ! D.__loaded ) {
				return;
			}

			if ( $container.data( 'dropbox-init' ) ) {
				return $container.data( 'dropbox-init' );
			}

			const $button = $( D.createChooseButton( $.extend( {}, {
				linkType: 'preview',
				extensions: [ '.nonexisting' ], // only allow folder selection
				folderselect: true,
				success: files => {
					chooser.setButtonText( files[ 0 ].name );
					if ( typeof onSelected === 'function' ) {
						onSelected( files );
					}
				},
			}, options ) ) );
			$container.append( $button );

			const chooser = {
				$button,
				setButtonText( text ) {
					$button.toggleClass( 'dropbox-dropin-success', !! text );
					const icon = $button.children()[ 0 ].outerHTML;
					$button.html( icon + ( text || 'Choose from Dropbox' ) );
				}
			};

			if ( selectedFile ) {
				chooser.setButtonText( selectedFile );
			}

			$container.data( 'dropbox-init', chooser );

			return chooser;
		}
	};
} )( jQuery );
