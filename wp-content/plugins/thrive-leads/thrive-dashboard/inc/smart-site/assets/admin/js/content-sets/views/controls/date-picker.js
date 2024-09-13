module.exports = require( './base' ).extend( {
	template: 'tvd-c-s-date-picker',
	afterRender() {
		this.$input = this.$( '.tvd-date-picker' );

		this.$input.pickadate( {
			firstDay: 1,
			format: 'dd-mmm-yyyy',
			formatSubmit: 'yyyy-mm-dd',
			hiddenName: true,
		} );

		this.picker = this.$input.pickadate( 'picker' );

		if ( this.hasStoredValue() ) {
			this.$input.val( this.processFormat() );
		}
	},
	processFormat() {
		const date = new Date( this.getStoredValue() ),
			mm = parseInt( date.getMonth() ) + 1,
			yr = date.getFullYear(),
			month = mm < 10 ? '0' + mm : mm,
			day = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();

		return day + '-' + month + '-' + yr;
	},
	processValue( value ) {
		value = this.picker.get( 'select', 'yyyy-mm-dd' );

		return value;
	}
} );
