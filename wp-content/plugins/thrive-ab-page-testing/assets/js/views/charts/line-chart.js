/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/5/2017
 * Time: 10:56 AM
 */

module.exports = Backbone.Model.extend( {
	defaults: function () {
		return {
			id: '',
			title: '',
			renderTo: '',
			type: 'line',
			suffix: '',
			data: []
		};
	},
	initialize: function () {
		var title = this.get( 'title' ),
			type = this.get( 'type' ),
			renderTo = this.get( 'renderTo' );
		this.chart = this.dochart( title, type, renderTo );
	},
	empty: function () {
		while ( this.chart.series.length > 0 ) {
			this.chart.series[0].remove( true );
		}
	},
	redraw: function () {
		var title = this.get( 'title' ),
			data = this.get( 'data' ),
			x_axis = this.get( 'x_axis' ),
			y_axis = this.get( 'y_axis' ),
			ids = [],
			x_axis_length = this.get( 'x_axis' ).length;

		//add series or update data if it already exists
		for ( var i in data ) {
			ids.push( data[i].id );
			var series = this.chart.get( data[i].id );
			if ( ! series ) {
				this.chart.addSeries( data[i], false, false )
			} else {
				series.setData( data[i].data );
			}
		}
		//delete old series
		for ( i = 0; i < this.chart.series.length; i ++ ) {
			if ( ids.indexOf( this.chart.series[i].options.id ) < 0 ) {
				this.chart.series[i].remove( false );
				i --;
			}
		}

		this.chart.get( 'time_interval' ).setCategories( x_axis );
		this.chart.xAxis[0].update( {
			tickInterval: x_axis_length > 13 ? Math.ceil( x_axis_length / 13 ) : 1
		} );

		this.chart.setTitle( {text: title} );
		if ( this.chart.yAxis[0].axisTitle ) {
			this.chart.yAxis[0].axisTitle.attr( {
				text: y_axis
			} );
		}
		this.chart.redraw();
		this.chart.hideLoading();
	},
	showLoading: function () {
		this.chart.showLoading();
	},
	hideLoading: function () {
		this.chart.hideLoading();
	},
	dochart: function ( title, type, renderTo ) {
		var self = this;

		return new Highcharts.Chart( {
			chart: {
				type: type,
				renderTo: renderTo,
				style: {
					fontFamily: 'Open Sans,sans-serif'
				}
			},
			colors: ThriveAB.chart_colors,
			yAxis: {
				allowDecimals: false,
				title: {
					text: 'Engagements'
				},
				min: 0
			},
			xAxis: {
				id: 'time_interval'
			},
			credits: {
				enabled: false
			},
			title: {
				text: title
			},
			tooltip: {
				shared: false,
				useHTML: true,
				formatter: function () {
					if ( this.series.type == 'scatter' ) {
						/* We don't display tooltips for the scatter graph */
						return false;
					} else {
						return this.x + '<br/>' +
						       this.series.name + ': ' + '<b>' + this.y + '</b>' + self.get( 'suffix' );
					}
				}
			},
			plotOptions: {
				series: {
					dataLabels: {
						shape: 'callout',
						backgroundColor: 'rgba(0, 0, 0, 0.75)',
						style: {
							color: '#FFFFFF',
							textShadow: 'none'
						}
					},
					events: {
						legendItemClick: function () {
							if ( this.type == 'scatter' ) {
								/* The labels are not hidden by clicking on the legend so we have to do it manually */
								if ( this.visible ) {
									jQuery( '.highcharts-data-labels' ).hide();
								} else {
									jQuery( '.highcharts-data-labels' ).show();
								}
							}
						}
					}
				}
			}
		} );
	}
} );
