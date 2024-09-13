/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 5:24 PM
 */

module.exports = Backbone.View.extend( {
	template: TVE_Dash.tpl( 'pagination' ),
	events: {
		'click a.page': 'changePage',
		'change .tab-items-per-page': 'changeItemPerPage'
	},
	currentPage: 1,
	pageCount: 1,
	itemsPerPage: 10,
	total_items: 0,
	collection: null,
	params: null,
	view: null,
	initialize: function ( options ) {
		this.collection = options.collection;
		this.view = options.view;
	},
	changeItemPerPage: function ( event ) {
		this.itemsPerPage = jQuery( event.target ).val();
		this.changePage( null, {page: 1} );
	},
	changePage: function ( event, args ) {
		var data = {
			itemsPerPage: this.itemsPerPage
		};

		/* Set the current page of the pagination. This can be changed by clicking on a page or by just calling this method with params */
		if ( event && typeof event.currentTarget !== 'undefined' ) {
			data.page = jQuery( event.currentTarget ).attr( 'value' );
		} else if ( args && typeof args.page !== 'undefined' ) {
			data.page = parseInt( args.page );
		} else {
			data.page = this.currentPage;
		}

		/* just to make sure */
		if ( data.page < 1 ) {
			data.page = 1;
		}

		/* Parse args sent to pagination */
		if ( typeof args !== 'undefined' ) {

			/* When "per page"  filter changes, those values are not sent so we save them in the view so we can have them for later */
			if ( typeof args.search_by !== 'undefined' ) {
				this.search_by = args.search_by;
			}
		}

		/* In case we've saved this before */
		data.search_by = this.search_by ? this.search_by.toLowerCase() : '';

		if ( typeof this.view != 'undefined' && this.view != null ) {

			/* Prepare params for pagination render */
			this.updateParams( data.page, this.collection.length );

			var currentCollection = this.collection.clone(),
				from = (this.currentPage - 1) * this.itemsPerPage,
				collectionSlice,
				removeIds = [],
				c_source = '';

			if ( typeof currentCollection.comparator !== 'undefined' ) {
				currentCollection.sort();
			}

			if ( data.search_by ) {

				currentCollection.each( function ( model ) {
					var title = model.get( 'title' ).toLowerCase(),
						goal_pages = JSON.stringify( model.get( 'goal_pages' ) ).toLowerCase(),
						page_title = model.get( 'page_title' ).toLowerCase();
					if ( title.indexOf( data.search_by ) === - 1 && goal_pages.indexOf( data.search_by ) === - 1 && page_title.indexOf( data.search_by ) === - 1 ) {
						removeIds.push( model );
					}
				} );

				for ( var i in removeIds ) {
					currentCollection.remove( removeIds[i] );
				}
				c_source = 'search_by';
			}

			collectionSlice = currentCollection.chain().rest( from ).first( this.itemsPerPage ).value();

			/* render sliced view collection */
			this.view.render( collectionSlice, c_source );
			/* render pagination */
			this.render();
		}

		return false;
	},
	updateParams: function ( page, total ) {
		this.currentPage = page;
		this.total_items = total;
		this.pageCount = Math.ceil( this.total_items / this.itemsPerPage );
	},
	setupParams: function ( page ) {
		this.currentPage = page;
		this.total_items = this.collection.length;
		this.pageCount = Math.ceil( this.total_items / this.itemsPerPage );
	},
	render: function () {
		this.$el.html( this.template( {
			currentPage: parseInt( this.currentPage ),
			pageCount: parseInt( this.pageCount ),
			total_items: parseInt( this.total_items ),
			itemsPerPage: parseInt( this.itemsPerPage )
		} ) );

		TVE_Dash.materialize( this.$el );

		return this;
	}
} );
