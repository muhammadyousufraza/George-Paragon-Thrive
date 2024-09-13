class State {
	constructor ( args ) {
		this.$form = args.form;
		this.assessmentID = args.assessmentID;
		this.config = args.config;

		this.init();
	}

	getStateKey () {
		return this.$form.attr( 'data-type' );
	}

	/**
	 * Allow child classes to inject data into constructor
	 */
	init () {
	}

	validate () {
		return true;
	}

	onSuccess ( response ) {
		this.$form.trigger( 'tcb.change_state', {
			...response,
			state: 'confirmation'
		} );
		TCB_Front.Hooks.doAction( 'tva.assessment.submit_success' );
	}

	onFail ( response ) {
	}

	getAjaxData () {
		return {};
	}

	beforeSend () {

	}

	getSubmitErrorMessage () {
		return 'TODO: ERROR HANDLING';
	}

	reset () {
	}
}

module.exports = State;
