module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/static-options',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;
	},
	afterInitialize() {
		this.listenTo( this.dataModel, 'change:selected_collection refresh-count', this.render );
	},
	onSelectAllCheckboxChange( event ) {
		this.dataModel.set( 'selected_all', event.target.checked ? 1 : 0 );
	},
} );
