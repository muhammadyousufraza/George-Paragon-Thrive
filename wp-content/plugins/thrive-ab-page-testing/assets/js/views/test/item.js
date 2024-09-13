var base = require( './../base' ),
	stop_variation_modal = require( './../../modals/delete' ),
	range = require( '../../controls/range' ),
	variation_winner_modal = require( './../../modals/variation-winner' );

module.exports = base.extend( {

	template: TVE_Dash.tpl( 'test/item/view' ),
	className: '',

	initialize: function ( attrs ) {
		this.active_items = attrs.active_items;
		this.stopped_items = attrs.stopped_items;
		this.table_model = attrs.table_model;
	},

	render: function () {

		this.$el.html( this.template( {
			item: this.model,
			table_model: this.table_model
		} ) );

		if ( this.className.length > 0 ) {
			this.$el.attr( 'class', this.className );
		}

		if ( parseInt( this.model.get( 'active' ) ) !== 0 ) {
			new range( {
				el: this.$( '.thrive-ab-test-item-traffic' ),
				/**
				 * because the active_items is a collection with active items too
				 * model.item is from whole collection of test items
				 */
				model: this.active_items.findWhere( {id: this.model.get( 'id' )} )
			} );
		}

		return this;
	},

	set_as_winner: function ( model, callback ) {

		if ( ! (model instanceof Backbone.Model) ) {
			model = this.model;
		}

		TVE_Dash.showLoader( true );

		ThriveAB.ajax.do( 'set_winner', 'post', model.toJSON() )
		        .done( _.bind( function () {

			        if ( typeof callback === 'function' ) {
				        callback( model );
			        } else {
				        this.table_model.trigger( 'winner_selected', this.table_model, model );
				        TVE_Dash.hideLoader();
			        }

		        }, this ) );

		return false;
	},

	stop_variation: function () {

		TVE_Dash.modal( stop_variation_modal, {
			submit: _.bind( function () {
				TVE_Dash.showLoader();

				this.model.set( 'stop_test_item', true );

				this.model.save( null, {
					wait: true,
					/**
					 * tell server to stop the item and return the item model to update the backbone model
					 */
					success: _.bind( function ( model ) {

						/**
						 * split the removed traffic and save it
						 */
						this.active_items.split_traffic( model, model.previousAttributes().traffic );
						this.active_items.save_distributed_traffic();

						/**
						 * remove vew from active items list
						 */
						this.remove();

						/**
						 * add to stopped items to be rendered
						 */
						this.stopped_items.add( model );

						/**
						 * remove it from active collection items
						 * so splitting traffic will not take into consideration this item/model
						 */
						this.active_items.remove( model );

						var index = ThriveAB.current_test.items.findIndex( function ( element ) {
							return element.id == model.get( 'id' );
						} );
						if ( index >= 0 ) {
							ThriveAB.current_test.items[index].active = 0;
						}

						if ( this.active_items.length === 1 ) {
							this.set_as_winner( this.active_items.first(), function ( model ) {
								TVE_Dash.hideLoader();
								TVE_Dash.modal( variation_winner_modal, {
										model: model,
										title: '',
										no_close: true,
										dismissible: false
									}
								);
							} );
						}

					}, this ),
					error: _.bind( function () {
						TVE_Dash.hideLoader();
						TVE_Dash.err( ThriveAB.t.variation_status_not_changed );
					}, this )
				} );
			}, this ),
			title: '',
			description: TVE_Dash.sprintf( ThriveAB.t.about_to_stop_variation, this.model.get( 'title' ) ),
			btn_yes_txt: ThriveAB.t.stop,
			btn_no_txt: ThriveAB.t.keep_it_running,
			'max-width': '20%',
			width: '20%'
		} );
	}
} );
