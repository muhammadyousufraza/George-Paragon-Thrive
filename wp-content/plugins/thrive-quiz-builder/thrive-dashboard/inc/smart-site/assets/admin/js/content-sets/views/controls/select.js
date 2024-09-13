module.exports = require( './base' ).extend( {
	template: 'tvd-c-s-select',
	afterRender() {
		this.$select = this.$( 'select' );

		this.model.get( 'options' ).forEach( option => {
			const opt = `<option value="${option.value}"${option.disabled ? ' disabled selected' : ''}>${option.label}</option>`;
			this.$select.append( opt );
		} );

		if ( this.hasStoredValue() ) {
			this.setSelected( this.getStoredValue() )
		}

		if ( this.model.get( 'trigger_change' ) ) {
			this.setSelected( this.getFirstOptionValue() );

			setTimeout( () => {
				this.triggerChange();
			} )
		}

		this.$select.select2( {
			//Disable the search input for simple select
			minimumResultsForSearch: - 1,
			width: '100%'
		} );
		this.$select.data( 'select2' ).$container.addClass( 'tvd-content-set-select-simple' );
		this.$select.data( 'select2' ).$dropdown.addClass( 'tvd-content-set-select-dropdown' );
	},
	setSelected( selected ) {
		if ( selected ) {
			this.$select.find( `option[value="${selected}"]` ).attr( 'selected', 'selected' );
		}
	},
	/**
	 * Returns the first non empty value
	 *
	 * @return {*}
	 */
	getFirstOptionValue() {
		return this.$select.find( `option:eq(1)` ).val();
	},

	/**
	 * Triggers the change on the control element
	 */
	triggerChange() {
		this.$select.trigger( 'change' );
	},

	/**
	 * A <select>ed value is always valid
	 *
	 * @return {boolean}
	 */
	isValid() {
		return true;
	}
} );
