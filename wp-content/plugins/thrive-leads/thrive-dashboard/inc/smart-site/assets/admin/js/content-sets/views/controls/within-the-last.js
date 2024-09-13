module.exports = require( './base' ).extend( {
	template: 'tvd-c-s-within-the-last',
	afterRender() {
		this.$input = this.$( 'input' );
		this.$select = this.$( 'select' );

		if ( this.hasStoredValue() ) {
			const value = this.getStoredValue().split( ' ' );

			this.$input.val( value[ 0 ].trim() );
			this.$select.val( value[ 1 ].trim() );
		} else {
			this.$input.val( 0 );
			this.$select.val( this.$select.find( 'option' ).first().attr( 'value' ) );
		}

		this.$select.select2();
		this.$select.data( 'select2' ).$container.addClass( 'mt-10 tvd-content-set-select-simple' );
		this.$select.data( 'select2' ).$dropdown.addClass( 'tvd-content-set-select-dropdown' );
	},
	isValid() {
		const inputValue = this.$input.val();

		if ( isNaN( inputValue ) || parseInt( inputValue ) < 0 ) {
			this.$input.val( 1 );

			return false;
		}

		return Number.isInteger( parseInt( this.$input.val() ) ) && this.$select.val().length > 0;
	},
	change( event, dom ) {
		if ( this.isValid( dom.value ) ) {
			const obj = {};

			obj[ this.step ] = this.processValue( dom.value );

			this.ruleModel.set( obj );
		}
	},
	processValue( value ) {
		value = `${this.$input.val()} ${this.$select.val()}`;

		return value;
	}
} );
