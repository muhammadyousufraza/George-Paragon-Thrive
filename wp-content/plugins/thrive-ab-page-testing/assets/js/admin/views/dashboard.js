/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 2:17 PM
 */
var TestListView = require( './test-list' ),
	TestPagination = require( './test-pagination' );

module.exports = Backbone.View.extend( {
	template: TVE_Dash.tpl( 'dashboard' ),
	events: {
		'keyup .tab-running-search-input': 'search_tests',
		'keyup .tab-completed-search-input': 'search_tests'
	},
	running_test_pagination: null,
	completed_test_pagination: null,
	initialize: function ( args ) {
		this.running_tests = args.running_tests;
		this.completed_tests = args.completed_tests;
		this.dashboard_stats = args.dashboard_stats;

		this.listenTo( this.completed_tests, 'remove', function () {
			this.completed_test_pagination.changePage();
		}, this );

	},
	render: function () {
		this.$el.html( this.template( {stats: this.dashboard_stats} ) );

		this.render_running_tests();
		this.render_completed_tests();

		return this;
	},

	render_running_tests: function () {

		var running_test_list = new TestListView( {
			template_item: TVE_Dash.tpl( 'running-test-item' ),
			template_no_item: TVE_Dash.tpl( 'running-test-no-item' ),
			template_no_search_item: TVE_Dash.tpl( 'running-test-no-search-item' ),
			el: this.$el.find( '.tab-running-test-items-list' ),
			collection: this.running_tests
		} );

		this.running_test_pagination = new TestPagination( {
			collection: this.running_tests,
			view: running_test_list,
			el: this.$el.find( '.tab-running-pagination' )
		} );

		this.running_test_pagination.changePage();
	},

	render_completed_tests: function () {
		var completed_test_list = new TestListView( {
			template_item: TVE_Dash.tpl( 'completed-test-item' ),
			template_no_item: TVE_Dash.tpl( 'completed-test-no-item' ),
			template_no_search_item: TVE_Dash.tpl( 'completed-test-no-search-item' ),
			el: this.$el.find( '.tab-completed-test-items-list' ),
			collection: this.completed_tests
		} );

		this.completed_test_pagination = new TestPagination( {
			collection: this.completed_tests,
			view: completed_test_list,
			el: this.$el.find( '.tab-completed-pagination' )
		} );

		this.completed_test_pagination.changePage();
	},
	search_tests: function ( e ) {

		var search = jQuery( e.target ).val(),
			pagination;

		if ( e.currentTarget.className.indexOf( 'running' ) !== - 1 ) {
			pagination = this.running_test_pagination;
		} else if ( e.currentTarget.className.indexOf( 'completed' ) !== - 1 ) {
			pagination = this.completed_test_pagination;
		} else {
			return;
		}

		pagination.changePage( null, {
			page: 1,
			search_by: search
		} );
	}
} );
