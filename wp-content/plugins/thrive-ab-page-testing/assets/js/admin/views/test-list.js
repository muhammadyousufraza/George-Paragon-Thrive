/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 1:31 PM
 */

var TestItemView = require( './test-item' );

module.exports = Backbone.View.extend( {
	template: '',
	events: {},
	initialize: function ( attr ) {
		this.template_item = attr.template_item;
		this.template_no_item = attr.template_no_item;
		this.template_no_search_item = attr.template_no_search_item;
		this.collection = attr.collection;
	},
	render: function ( collection, c_source ) {
		var c = this.collection;

		this.$el.empty();

		if ( typeof collection !== 'undefined' ) {
			c = new Backbone.Collection( collection );
		}

		if ( c.length === 0 ) {
			var _no_results_template = this.template_no_item();

			if ( c_source === 'search_by' ) {
				_no_results_template = this.template_no_search_item();
			}

			this.$el.html( _no_results_template );
		} else {
			c.each( this.renderOne, this );
		}

		return this;
	},
	renderOne: function ( item ) {
		var view = new TestItemView( {
			template: this.template_item,
			collection: this.collection,
			model: item
		} );

		this.$el.append( view.render().$el );
	}
} );
