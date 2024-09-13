const TagsSelect = require( './tags-select' ),
	Select = require( '../select' );

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/filters',
	search: _.debounce( function ( event ) {
		this.model.set( 'search', event.target.value );
	}, 200 ),
	afterInitialize() {
		( new Select( {
			el: this.$( '.display-testimonials-select.title-filter' )[ 0 ],
			model: new Backbone.Model( {
				list: [
					{ label: 'With a title', value: 1 },
					{ label: 'Without a title', value: 0 },
					{ label: 'Any', value: '' },
				],
				placeholderText: 'Title filter',
				selected: this.model.get( 'title' ),
			} ),
			onSelect: value => this.model.set( 'title', value ),
		} ) );

		( new Select( {
			el: this.$( '.display-testimonials-select.image-filter' )[ 0 ],
			model: new Backbone.Model( {
				list: [
					{ label: 'With an image', value: 1 },
					{ label: 'Without an image', value: 0 },
					{ label: 'Any', value: '' },
				],
				placeholderText: 'Image filter',
				selected: this.model.get( 'image' ),
			} ),
			onSelect: value => this.model.set( 'image', value ),
		} ) );

		TagsSelect.initSelect( this.$( 'select' ), values => this.model.set( 'tags', values ) );
	},
	onSelect( event ) {
		this.model.set( event.target.dataset.type, event.target.value );
	},
	onCheckboxChange( event ) {
		const wordCount = this.model.get( 'word_count' ) || {};

		wordCount.enabled = event.target.checked ? 1 : 0;

		this.model.set( 'word_count', wordCount, { silent: true } );

		/* trigger 'change' separately in order to have full control over it ( change may not be triggered if nested data is changed ) */
		if ( wordCount.from || wordCount.to ) {
			this.model.trigger( 'change', this.model );
		}
	},
	onInputChange: _.throttle( function( event ) {
		const type = event.target.dataset.type,
			wordCount = this.model.get( 'word_count' ) || {};
		let value = parseFloat( event.target.value );

		if ( ( value < 0 ) || ( value > 1000 ) || ! Number.isInteger( value ) ) {
			value = 0;

			TVE.$( event.target ).val( parseInt( value ) );
		}

		wordCount[ type ] = value;

		this.model.set( 'word_count', wordCount, { silent: true } );

		/* trigger 'change' separately in order to have full control over it ( change may not be triggered if nested data is changed ) */
		if ( wordCount.enabled ) {
			this.model.trigger( 'change', this.model );
		}
	}, 1000 ),
} );
