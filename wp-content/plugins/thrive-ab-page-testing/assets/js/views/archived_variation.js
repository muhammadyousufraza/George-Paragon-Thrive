var base = require( './base' ),
	delete_modal = require( '../modals/delete' );

module.exports = base.extend( {

	className: 'tvd-col tvd-l3 tvd-m6',

	template: TVE_Dash.tpl( 'html-archived-variation-card' ),
	initialize: function ( args ) {
		base.prototype.initialize.apply( this, args );
		this.published_variations = args.published_variations;
		this.archived_variations = args.archived_variations;
	},

	delete: function () {

		TVE_Dash.modal( delete_modal, {
			submit: _.bind( function () {
				this.remove();
				this.model.destroy();
			}, this ),
			model: this.model,
			btn_yes_txt: ThriveAB.t.delete_title,
			btn_no_txt: ThriveAB.t.cancel,
			title: ThriveAB.t.delete_variation,
			description: TVE_Dash.sprintf( ThriveAB.t.about_to_delete, this.model.get( 'post_title' ) )
		} );

		return false;
	},

	restore: function () {
		var self = this;
		this.model.set( 'action', 'publish' );
		TVE_Dash.showLoader();
		this.model.save( null, {
			success: function ( model ) {
				model.collection = self.published_variations;
				self.published_variations.add( model );
				self.published_variations.equalize_traffic();
				self.published_variations.save_distributed_traffic();
				self.remove();
				self.archived_variations.remove( model );
				TVE_Dash.success( ThriveAB.t.variation_added, 1000 );
			},
			error: function () {
				TVE_Dash.err( ThriveAB.t.add_variation_error );
			},
			complete: function () {
				TVE_Dash.hideLoader();
			}
		} );
	},

	render: function () {
		this.$el.html( this.template( {item: this.model} ) );
	}

} );
