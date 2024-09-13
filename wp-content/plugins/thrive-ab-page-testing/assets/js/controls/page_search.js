/**
 * Autocomplete input
 */
var base_view = Backbone.View,
	base_model = require( './../models/base' );

var page_model = base_model.extend( {

	defaults: function () {
		return {
			post_id: '',
			post_title: ''
		};
	},

	validate: function ( attrs ) {

		var _errors = [];

		if ( ! attrs.post_title || ! attrs.post_id ) {
			_errors.push( this.validation_error( 'post_title', 'Page not selected' ) );
		}

		return _errors.length ? _errors : undefined;
	}
} );

var view = base_view.extend( {

	className: 'tvd-input-field thrive-ab-page-search',

	template: TVE_Dash.tpl( 'util/page-search' ),

	initialize: function ( attrs ) {

		if ( ! attrs || ! attrs.model ) {
			this.model = new page_model();
		}
		this.goal_pages = attrs.goal_pages;

		this.on( 'select', this.select );
		this.on( 'save-page', this.save_page );

	},

	render: function () {

		this.$el.html( this.template( {item: this.model} ) );

		TVE_Dash.data_binder( this );

		this.$autocomplete = this.$( '.page-search' );
		this.$autocomplete.on( 'input', _.bind( function () {
			this.model.set( {
				post_id: null
			} );
			this.$( '.thrive-ab-edit-page' ).attr( 'href', 'javascript:void(0)' ).addClass( 'tvd-btn-flat-secondary' );
			this.$( '.thrive-ab-preview-page' ).attr( 'href', 'javascript:void(0)' ).addClass( 'tvd-btn-flat-secondary' );
		}, this ) );

		this.bind();

		return this;
	},
	select: function ( item ) {
		this.$( '.thrive-ab-edit-page' ).attr( 'href', item.edit_url ).removeClass( 'tvd-btn-flat-secondary' ).show();
		this.$( '.thrive-ab-preview-page' ).attr( 'href', item.url ).removeClass( 'tvd-btn-flat-secondary' ).show();
	},
	save_page: function ( item ) {

		var self = this,
			_options = {
				title: item.title
			};

		TVE_Dash.showLoader();

		ThriveAB.ajax.do( 'add_new_page', 'post', _options ).done( function ( response ) {
			self.model.set( {
				post_id: response.post.ID,
				post_title: response.post.post_title
			} );
			self.select( {
				edit_url: response.post.edit_url,
				url: response.post.guid
			} );
			TVE_Dash.hideLoader();
		} );
	},
	bind: function () {
		var self = this,
			last,
			cache;

		this.$autocomplete.autocomplete( {
			appendTo: this.$el,
			minLength: 2,
			delay: 300,
			source: function ( request, response ) {
				var exclude_ids;
				if ( self.goal_pages ) {
					exclude_ids = Object.keys( self.goal_pages );
					exclude_ids.push( ThriveAB.page.ID );
				} else {
					exclude_ids = [ThriveAB.page.ID];
				}
				var _options = {
					q: request.term,
					exclude_id: exclude_ids
				};

				ThriveAB.ajax.do( 'post_search', 'post', _options )
				        .done( function ( data ) {
					        if ( true || data.length === 0 ) {
						        data.push( {
							        title: request.term,
							        label: request.term,
							        type: 'Create New Page',
							        value: request.term
						        } );
					        }

					        cache = data;
					        response( data );
				        } );

				last = request.term;
			},
			select: function ( event, ui ) {
				self.$autocomplete.val( ui.item.title );

				self.model.set( {
					post_id: ui.item.id,
					post_title: ui.item.title
				} );

				if ( ui.item.id ) {
					self.trigger( 'select', ui.item );
				}

				return false;
			}
		} );
		this.$autocomplete.data( 'ui-autocomplete' )._renderItem = function ( ul, item ) {
			var $li = jQuery( '<li/>' );

			if ( typeof item.id === 'undefined' ) {
				$li.addClass( 'ui-not-found' );
				$li.click( function () {
					self.trigger( 'save-page', item );
				} );
			}

			var _html;

			if ( item.type === 'Create New Page' ) {
				_html = '<span class="post-name" style="width: 60%">' + item.label + '</span><span class="post-type" style="width: 40%">' + item.type + '</span>';
			} else {
				_html = '<span class="post-name">' + item.label + '</span><span class="post-type">' + item.type + '</span>';
			}

			$li.append( _html ).appendTo( ul );

			return $li;
		};
	}
} );

module.exports = {
	model: page_model,
	view: view
};
