var base_view = require( './base' ),
	variation_view = require( './variation' ),
	archived_variation_view = require( './archived_variation' ),
	test_model = require( '../models/test' ),
	variation_model = require( '../models/variation' ),
	modal_test = require( '../modals/test' );

module.exports = base_view.extend( {

	template: TVE_Dash.tpl( 'dashboard/dashboard' ),

	initialize: function ( args ) {
		this.archived = args.archived;

		base_view.prototype.initialize.apply( this, arguments );

		this.listenTo( this.collection, 'add', function ( model ) {
			this.render_variation( model );
			this.render_action_button();
			this.toggle_traffic_control();
		} );
		this.listenTo( this.collection, 'remove', function ( model ) {
			this.render_action_button();
			this.toggle_traffic_control();
		} );

		this.listenTo( this.archived, 'add', function ( model ) {
			this.render_archived_variation( model );
		} );
	},

	render_action_button: function () {
		this.$( '#thrive-ab-start-test' ).toggleClass( 'top-hide-action', this.collection.length < 2 );
		this.$( '.thrive-ab-display-archived-container' ).toggleClass( 'hide', this.archived.length < 1 );
	},

	render: function () {

		this.$el.html( this.template() );
		this.$list = this.$( '#thrive-ab-card-list' );
		this.$archived_list = this.$( '#thrive-ab-card-list-archived' );

		this.render_action_button();
		this.collection.each( function ( item, index, list ) {
			this.render_variation( item );
		}, this );

		this.archived.each( function ( item, index, list ) {
			this.render_archived_variation( item );
		}, this );
	},

	render_archived_variation: function ( item ) {
		var _view = new archived_variation_view( {
			model: item,
			archived_variations: this.archived,
			published_variations: this.collection
		} );
		this.$archived_list.append( _view.$el );
	},

	render_variation: function ( item ) {

		var _view = new variation_view( {
			model: item,
			archived_variations: this.archived,
			published_variations: this.collection
		} );

		var $add_new_card = this.$list.find( '> .tvd-col' ).last();
		$add_new_card.remove();

		this.$list.append( _view.$el );
		this.$list.append( $add_new_card );
	},

	display_archived: function () {
		this.$archived_list.toggle();
		this.$( '.thrive-ab-display-archived' ).toggleClass( 'active' );
	},

	add_new_variation: function () {

		var _new_traffic = parseInt( 100 / ( this.collection.length + 1 ) ),
			_model = new variation_model( {
				post_parent: this.model.get( 'ID' ),
				post_title: TVE_Dash.sprintf( ThriveAB.t.variation_no, this.collection.length + 1 ),
				traffic: _new_traffic
			} );

		TVE_Dash.showLoader();

		_model.save( null, {
			success: _.bind( function ( model ) {
				this.collection.add( model );
				model.collection.distribute_traffic( model );
				model.collection.save_distributed_traffic();
				TVE_Dash.success( ThriveAB.t.variation_added, 1000 );
			}, this ),
			error: function () {
				TVE_Dash.err( ThriveAB.t.add_variation_error );
			},
			complete: _.bind( function () {
				TVE_Dash.hideLoader();
			}, this )
		} );
	},

	start_test: function () {

		var new_test_model = new test_model( {
			page_id: ThriveAB.page.ID,
			items: this.collection
		} );

		TVE_Dash.modal( modal_test, {
			model: new_test_model,
			'max-width': '80%'
		} );

		return false;
	},

	toggle_traffic_control: function () {
		if ( this.collection.length === 1 ) {
			this.$( '.thrive-ab-card-footer input' ).attr( 'disabled', 'disabled' );
		} else {
			this.$( '.thrive-ab-card-footer input' ).removeAttr( 'disabled' );
		}
	},
	equalize_traffic: function () {
		this.collection.equalize_traffic();
		this.collection.save_distributed_traffic();

		return false;
	}
} );
