var base_view = require( '../base' ),
	page_search = require( './../../controls/page_search' );

module.exports = base_view.extend( {

	className: 'test-item-form tvd-col tvd-s12',

	template: TVE_Dash.tpl( 'goals/page' ),

	type: null,
	events: {
		'click .thrive-ab-remove-page': 'remove_page'
	},
	initialize: function ( args ) {

		if ( args.test && args.test instanceof Backbone.Model ) {
			this.test = args.test
		}
		this.model.set('type', this.test.get('type'));
		this.page_search_view = new page_search.view( {
			model: this.model,
			goal_pages: this.test.get('goal_pages')
		} );

		this.page_search_view.test = this.test;

		this.goal_pages = this.test.get( 'goal_pages' );
		if ( ! this.goal_pages ) {
			this.goal_pages = {};
		}

		this.listenTo( this.model, 'change:revenue', this.onRevenueChange );
		this.listenTo( this.model, 'change:post_id', this.onPostChange );

	},
	onPostChange: function () {
		if ( this.model.get( 'post_id' ) != null ) {
			this.goal_pages[this.model.get( 'post_id' )] = {
				post_id: this.model.get( 'post_id' ),
				post_title: this.model.get( 'post_title' ),
				revenue: this.model.get( 'revenue' )
			};

			this.test.set( 'goal_pages', this.goal_pages );
		}
	},
	onRevenueChange: function () {
		if ( this.model.get( 'post_id' ) ) {
			this.onPostChange();
		}
	},
	render: function () {
		this.$el.html( this.template( {item: this.page_search_view.model, test: this.test} ) );

		setTimeout( _.bind( function () {
			TVE_Dash.materialize( this.$el );
		}, this ), 0 );

		TVE_Dash.data_binder( this );

		this.$( '.page-search' ).html( this.page_search_view.render().$el );

		return this;
	},
	remove_page: function () {
		delete this.goal_pages[this.model.get( 'post_id' )];
		this.test.set( 'goal_pages', this.goal_pages );
		this.$el.unbind();
		this.remove();
	}
} );
