const State = require( './state' );

class StateExternalLink extends State {

	init () {
		this.$input = this.$form.find( 'input' );
	}

	reset () {
		this.$input.val( '' );
	}


	getURL () {
		return this.$input.val().trim();
	}

	validate () {
		const inputValue = this.getURL();

		return ! ( inputValue.length === 0 || ! TCB_Front.isValidUrl( inputValue ) );
	}

	getAjaxData () {
		return {
			value: this.getURL(),
		};
	}

	/**
	 * @return {string} Submit error message
	 */
	getSubmitErrorMessage () {
		return this.config.error_messages.link_invalid;
	}
}

module.exports = StateExternalLink;
