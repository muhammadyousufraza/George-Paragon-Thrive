module.exports = require( './base' ).extend( {
	/**
	 * Removes the view from DO because user doesn't confirm his action
	 * - defined in HTML
	 */
	cancel() {
		this.remove();
	},
	/**
	 * This should be overwritten or extended when this view is initialized
	 * - defined in HTML
	 */
	confirm() {
		this.remove();
	}
} );
