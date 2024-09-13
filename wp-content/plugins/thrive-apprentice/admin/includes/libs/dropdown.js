const Base = require( '../js/views/base' );

module.exports = Base.extend( {
	template: 'dropdown',
	afterInitialize ( options ) {

		this.items = options.items || []

		if ( typeof options.onSelect === 'function' ) {
			this.onSelect = options.onSelect;
		}

		this.render();

//		Base.prototype.initialize.call( this, options )

		this.$selectedValue = this.$( '.ttd-selected-value' )
		this.$items = this.$( '.ttd-dropdown-items' )

		this.setDefaultValue();
	},

	getItems () {
		return this.items
	},

	setItems ( items ) {
		this.items = items;

		return this;
	},

	onSelect ( event, button ) {
		this.$el.removeClass( 'open' )
		this.setValue( button.dataset.id )
		this.trigger( 'select', button.dataset.id )
	},

	toggle () {
		this.$el.toggleClass( 'open' )
	},

	open () {
		this.$el.addClass( 'open' )
	},

	close () {
		this.$el.removeClass( 'open' )
	},

	setValue ( id = 0 ) {
		this.selectedValue = id;
		this.$items.find( '.selected' ).removeClass( 'selected' );
		this.$items.find( `[data-id="${ id }"]` ).addClass( 'selected' );
		this.$selectedValue.html( this.getSelectedText() )
	},

	getValue () {
		return this.selectedValue;
	},

	getSelectedText () {
		const selectedOption = this.getItems().find( item => item.id === this.getValue() );

		return selectedOption ? selectedOption.text : ''
	},

	setDefaultValue () {
		let defaultItem = this.items.find( item => item.default );

		if ( ! defaultItem ) {
			defaultItem = this.items[ 0 ]
		}

		this.setValue( defaultItem.id );
	}

} )
