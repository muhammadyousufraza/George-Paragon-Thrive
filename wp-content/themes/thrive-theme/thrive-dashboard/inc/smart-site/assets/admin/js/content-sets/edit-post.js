( function ( $ ) {

	/**
	 * On document ready init app
	 */
	$( tvdInitApp );

	/**
	 * Init App
	 */
	function tvdInitApp() {

		TD.matches = new Backbone.Collection( TD.matches || [] );
		TD.sets = new Backbone.Collection( TD.sets || [] );

		new AppView( {
			el: $( '#tvd-contents-sets' ),
		} );
	}

	/**
	 * Main App View
	 */
	const AppView = Backbone.View.extend( {
		events: {
			/**
			 * @param {Event} event
			 */
			'click .removeMatchedTag': event => {
				const ID = parseInt( event.currentTarget.parentNode.getAttribute( 'data-id' ) ),
					matchedModel = TD.matches.findWhere( {id: ID} );

				if ( matchedModel ) {
					TD.matches.remove( matchedModel );
				}
			},
		},
		/**
		 * @param {Object} options
		 */
		initialize( options ) {

			$.extend( this, true, options );

			this.listenTo( TD.matches, 'add remove', this.renderMatchedTags );

			this.$autocomplete = this.$( '#tvd-content-sets-autocomplete' );
			this.$matchedWrapper = this.$( '#tvd-matched-content-sets' );

			this.renderMatchedTags();
		},
		renderMatchedTags() {
			this.$matchedWrapper.empty();

			/**
			 * Array needed to send data to backend
			 *
			 * @type {string[]}
			 */
			const matchedIDs = [];

			TD.matches.each( matchedModel => {
				this.$matchedWrapper.append( `<div data-id="${matchedModel.get( 'id' )}"><span>${matchedModel.get( 'text' )}</span><span class="removeMatchedTag"></span></div>` )

				matchedIDs.push( matchedModel.get( 'id' ) );
			} );

			this.bindAutocomplete();

			if ( matchedIDs.length ) {
				this.$matchedWrapper.append( `<input type="hidden" name="tvd_matched_content_sets_id" value="${matchedIDs.toString()}" />` );
			}
		},
		_escape( value ) {
			return value.replace( /[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&" );
		},
		bindAutocomplete() {
			if ( this.$autocomplete.data( 'autocomplete' ) ) {
				this.$autocomplete.autocomplete( 'destroy' );
				this.$autocomplete.removeData( 'autocomplete' );
			}

			this.$autocomplete.autocomplete( {
				appendTo: this.$autocomplete.parent(),
				minLength: 0,
				classes: {
					'ui-autocomplete': 'tvd-content-sets-dropdown',
				},
				source: ( request, response ) => {
					const regex = new RegExp( this._escape( request.term ), 'i' ),
						setsJSON = TD.sets.toJSON().filter( elem => typeof TD.matches.findWhere( {id: elem.id} ) === 'undefined' ),
						recs = request.term.trim().length === 0 ? setsJSON : $.grep( setsJSON, function ( set ) {
							return regex.test( set.text )
						} );

					response( $.map( recs, function ( set ) {
						return {
							label: set.text,
							key: set.id
						};
					} ) );
				},
				select: ( event, ui ) => {
					this.$autocomplete.val( '' );

					const matchedModel = TD.sets.findWhere( {id: ui.item.key} );

					if ( matchedModel ) {
						TD.matches.add( matchedModel.clone() );
					}

					return false;
				}
			} )
		}
	} );

} )( jQuery );
