module.exports = require( './base' ).extend( {
	className: 'tvd-content-set-item',
	template: 'item',
	/**
	 * Edit a content set callback
	 *
	 * Called from the UI
	 */
	edit: function () {
		this.openModal( TD_SETS.Views.Modals.Edit, {
			model: this.model,
			collection: this.collection,
			width: '810px',
			className: 'tvd-modal tvd-content-set-edit'
		} );
	},

	/**
	 * Deletes a content set callback
	 *
	 * Called from the UI
	 */
	delete: function () {

		const confirmView = new TD_SETS.Views.Confirm( {
			template: TVE_Dash.tpl( 'item-delete-confirmation' ),
			className: 'tvd-content-set-delete-confirmation',
			confirm: () => {
				this.model.destroy( {
					wait: true,
					success: ( model, response ) => {
						this.destroy().remove();
					}
				} );
			},
			cancel() {
				this.remove();
			}
		} );

		this.$el.append( confirmView.render().$el );
	}
} );
