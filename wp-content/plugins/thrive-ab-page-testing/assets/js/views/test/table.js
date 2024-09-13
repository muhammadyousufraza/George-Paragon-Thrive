var base = require( '../base' ),
	test_item = require( './item' ),
	change_automatic_winner_settings = require( '../../modals/automatic-winner-settings' ),
	test_items_collection = require( '../../collections/test-items' ),
	test_model = require( '../../models/test' ),
	goal = require( './goal' ),
	modal_goal = require( '../../modals/goal' );

module.exports = base.extend( {

	template: TVE_Dash.tpl( 'test/table' ),

	initialize: function ( attr ) {

		this.template = TVE_Dash.tpl( 'test/' + this.model.get( 'type' ) + '-table' );

		if ( attr.item_template_name ) {
			this.item_template_name = attr.item_template_name;
		}

		var raw_test = ThriveAB.current_test || ThriveAB.running_test;

		this.active_items = new test_items_collection( _.filter( raw_test.items, {active: '1'} ) );
		this.stopped_items = new test_items_collection( _.filter( raw_test.items, {active: '0'} ) );

		this.listenTo( this.stopped_items, 'add', function ( model ) {
			this.$stopped_item_list.append( this.render_item( model ).$el );
		} );

		this.listenTo( this.active_items, 'remove', function ( model ) {
			this.render_active_items();
		} );

		this.listenTo( this.model, 'change:auto_win_enabled', this.toggle_auto_win_text );

		base.prototype.initialize.apply( this, arguments );
	},

	render: function () {

		this.$el.html( this.template( {item: this.model} ) );
		this.toggle_auto_win_text( this.model );

		if ( this.model.get( 'status' ) === 'completed' ) {
			this.$( '.thrive-ab-test-footer' ).hide();
		}

		this.$item_list = this.$( '.thrive-ab-test-items' );
		this.$stopped_item_list = this.$( '.thrive-ab-test-stopped-items' );
		this.collection = this.model.get( 'items' );

		this.render_variations();
		this.render_goal_type();

		return this;
	},
	toggle_auto_win_text: function ( model ) {

		var text = model.get( 'auto_win_enabled' ) != 0 ? ThriveAB.t.auto_win_enabled : ThriveAB.t.auto_win_disabled;

		this.$( '#thrive-ab-auto-win-text' ).text( text );
	},
	render_variations: function () {
		this.render_active_items();
		this.render_stopped_items();

	},

	render_active_items: function () {
		this.$item_list.empty();

		this.active_items.each( function ( item ) {
			this.$item_list.append( this.render_item( item ).$el );
		}, this );

		return this;
	},

	render_stopped_items: function () {

		this.$stopped_item_list.empty();

		this.stopped_items.each( function ( item ) {
			this.$stopped_item_list.append( this.render_item( item ).$el );
		}, this );

		return this;
	},

	render_item: function ( item ) {

		var item_view = new test_item( {
			model: item,
			table_model: this.model,
			active_items: this.active_items,
			stopped_items: this.stopped_items
		} );

		if ( this.item_template_name ) {
			item_view.template = TVE_Dash.tpl( this.item_template_name );
		} else if ( item.get( 'is_control' ) ) {
			item_view.template = TVE_Dash.tpl( 'test/item/control-view-' + this.model.get( 'type' ) );
			item_view.className = 'tab-control-row';
		} else if ( parseInt( item.get( 'active' ) ) === 0 ) {
			item_view.template = TVE_Dash.tpl( 'test/item/view-stopped-' + this.model.get( 'type' ) );
		} else {
			item_view.template = TVE_Dash.tpl( 'test/item/view-' + this.model.get( 'type' ) );
		}

		return item_view.render();
	},

	render_goal_type: function () {

		new goal( {
			el: this.$( '#thrive-ab-conversion-goals' ),
			model: this.model
		} ).render();
	},

	/**
	 * Change Automatic Winner Settings
	 */
	change_automatic_winner_settings: function () {

		TVE_Dash.modal( change_automatic_winner_settings, {
			model: this.model,
			title: ThriveAB.t.automatic_winner_settings
		} );

		return false;
	},

	view_conversion_goal_details: function () {

		TVE_Dash.modal( modal_goal, {
			collection: new Backbone.Collection( Object.values( this.model.get( 'goal_pages' ) ) ),
			model: this.model,
			title: ''
		} );

		return false;
	}
} );
