var base_view = require( '../base' ),
	goal_page = require( './goal_page' ),
	base_model = require( './../../models/base' ),
	page_search = require( '../../controls/page_search' );

module.exports = base_view.extend( {

	className: 'tvd-col tvd-s12',

	events: function () {
		return _.extend( base_view.prototype.events, {
			'click .thrive-ab-add-new-goal': 'add_goal_page_field'
		} );
	},

	initialize: function () {
		this.item_form_views = [];
	},

	render: function () {

		this.$el.html( this.template( {item: this.model} ) );

		this.render_goal_pages();

		return this;
	},

	render_goal_pages: function () {

		var goal_pages = this.model.get( 'goal_pages' ),
			form_view,
			self = this;
		if ( goal_pages ) {
			_.each( goal_pages, function ( element, index ) {
				form_view = new goal_page( {
					test: self.model,
					model: new base_model( element )
				} );

				self.item_form_views.push( form_view );

				self.$( '#item-forms' ).append( self.create_goal_page( form_view ) );
			} );
		} else {
			this.add_goal_page_field();
		}

	},
	add_goal_page_field: function () {

		var form_view = new goal_page( {
			test: this.model,
			model: new base_model()
		} );

		this.item_form_views.push( form_view );

		this.$( '#item-forms' ).append( this.create_goal_page( form_view ) );
	},
	create_goal_page: function ( input_view ) {

		return input_view.render().$el;
	}
} );
