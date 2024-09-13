/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/27/2017
 * Time: 10:20 AM
 */

var base_view = require( '../base' ),
	report_item_view = require( './report-item' ),
	report_chart = require( './report-chart' ),
	chart_model = require( './../../models/report-chart' ),
	test_table_view = require( './../test/table' ),
	winner_modal = require( './../../modals/winner' );

module.exports = base_view.extend( {

	template: TVE_Dash.tpl( 'report/report' ),

	initialize: function () {
		base_view.prototype.initialize.apply( this, arguments );
	},

	render: function () {
		this.$el.html( this.template( {
			model: this.model,
			edit_page_link: ThriveAB.page.edit_link
		} ) );

		this.$( 'select' ).select2();

		var table_view = new test_table_view( {
			el: this.$( '#thrive-ab-test' ),
			model: this.model
		} );

		this.do_chart();
	},
	update_chart: function ( event, dom ) {
		var type = this.$( '.tab-graph-type' ).val(),
			interval = this.$( '.tab-graph-interval' ).val();
		this.report_chart.interval_changed( type, interval );
	},
	do_chart: function () {
		this.report_chart = new report_chart( {
			el: this.$el,
			model: new chart_model( ThriveAB.test_chart ),
			render_to: 'tab-test-chart'
		} );
	},
	stop_test: function () {

		TVE_Dash.modal( winner_modal, {
			model: this.model,
			collection: this.collection,
			'max-width': '80%',
			width: '80%',
			title: ThriveAB.t.choose_winner
		} );

	}
} );
