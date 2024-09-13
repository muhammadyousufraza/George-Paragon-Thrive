const SetView = require( './item' );

module.exports = TVE.Views.Base.base_view.extend( {
	beforeInitialize( attr ) {
		this.collection = attr.collection;
		this.dataModel = attr.dataModel;
	},
	afterRender() {
		this.$el.empty();

		this.collection.each( item => this.$el.append( ( new SetView( { model: item, dataModel: this.dataModel } ) ).$el ) );
	},
} );
