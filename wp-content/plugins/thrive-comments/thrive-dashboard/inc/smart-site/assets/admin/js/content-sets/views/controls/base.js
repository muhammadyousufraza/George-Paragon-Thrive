module.exports = require( '../base' ).extend( {
	template: '',
	className: 'tvd-content-set-rule-step',
	afterInitialize( options ) {
		this.ruleModel = options.ruleModel;
		this.step = options.step;
	},
	/**
	 * Checks if the step is completed
	 *
	 * @return {boolean}
	 */
	hasStoredValue() {
		return this.getStoredValue().length > 0;
	},
	getStoredValue() {
		return this.ruleModel.get( this.step );
	},
	/**
	 * Should be extended in other classes
	 * @param value
	 * @return {boolean}
	 */
	isValid( value ) {
		return value && value.trim().length > 0;
	},
	change( event, dom ) {
		if ( this.isValid( dom.value ) ) {
			this.ruleModel.trigger( 'control-changed', this.step, this.processValue( dom.value ) );
		}
	},
	/**
	 * Process the value before storing into the model
	 *
	 * @param {string|Array} value
	 *
	 * @return {string|Array}
	 */
	processValue( value ) {
		return value;
	}
} );
