module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/testimonial-item',
	tagName: 'li',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;

		this.listenTo( this.model, 'change:selected', this.render )
			.listenTo( this.model, 'remove', this.remove );
	},
	afterRender() {
		this.$el
			.toggleClass( 'is-selected', !! this.model.get( 'selected' ) && this.dataModel.isSelectMode() )
			.attr( 'data-id', this.model.get( 'ID' ) );
	},
	getTagList() {
		let tagString = '';

		this.model.get( 'tags' ).forEach( tag => {
			tagString += `<span class="testimonial-tag">${ tag }</span>`;
		} );

		return tagString;
	},
	checkboxChanged( event ) {
		this.model.set( 'selected', event.target.checked ? 1 : 0 );
	},
	isSelectMode() {
		return this.dataModel.isSelectMode();
	},
	isReorderMode() {
		return this.dataModel.isReorderMode();
	},
} );
