const TagItemView = require( './item' );

module.exports = TVE.Views.Base.base_view.extend( {
	beforeInitialize( attr ) {
		this.collection = attr.collection;
	},
	afterRender() {
		this.$el.empty();

		this.collection.each( item => this.$el.append( ( new TagItemView( { model: item } ) ).$el ) );
	},
} );
