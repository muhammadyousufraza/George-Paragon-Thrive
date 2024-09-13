/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/29/2017
 * Time: 11:42 AM
 */

var base_view = require( '../base' ),
	line_chart = require( '../charts/line-chart' );

module.exports = base_view.extend( {
	current_interval: 'day',
	type: 'conversion_rate',
	render_to: '',
	initialize: function ( options ) {
		base_view.prototype.initialize.apply( this, arguments );
		this.render_to = options.render_to;

		this.update_chart();
	},
	draw_chart: function ( render_to ) {
		this.chart = new line_chart( {
			title: '',
			type: 'spline',
			data: [],
			renderTo: render_to
		} );
		this.chart.showLoading();
	},
	interval_changed: function ( type, interval ) {
		var self = this;

		if ( interval === this.current_interval && type === this.type ) {
			return;
		}

		this.current_interval = interval;
		this.type = type;

		if ( typeof this.chart !== 'undefined' ) {
			this.chart.showLoading();
		}

		this.model.fetch( {
			data: jQuery.param( {
				type: type,
				interval: interval
			} ),
			success: function () {
				self.update_chart();
			}
		} );
	},
	update_chart: function () {
		if ( typeof this.chart === 'undefined' ) {
			this.draw_chart( this.render_to );
		}
		this.chart.set( 'data', this.model.get( 'data' ) );
		this.chart.set( 'title', '' );
		this.chart.set( 'x_axis', this.model.get( 'x_axis' ) );
		this.chart.set( 'y_axis', this.model.get( 'y_axis' ) );

		this.chart.redraw();

		this.update_description();

	},
	update_description: function () {
		this.$( '#thrive-ab-chart-title' ).html( this.model.get( 'title' ) );
		this.$( '#thrive-ab-chart-total-value' ).html( this.model.get( 'total_over_time' ) + ' ' + this.model.get( 'test_type_txt' ) );
	}
} );
