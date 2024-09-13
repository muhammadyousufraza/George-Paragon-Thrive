( $ => {
	/**
	 * Displays toast message from storage, it is used when the user is redirected after login
	 */
	if ( window.sessionStorage ) {
		$( window ).on( 'tcb_after_dom_ready', () => {
			const message = sessionStorage.getItem( 'tcb_toast_message' );

			if ( message ) {
				tcbToast( sessionStorage.getItem( 'tcb_toast_message' ), false );
				sessionStorage.removeItem( 'tcb_toast_message' );
			}
		} );
	}

	/**
	 * Displays toast message
	 *
	 * @param {string}   message  - message to display
	 * @param {Boolean}  error    - whether the message is an error or not
	 * @param {Function} callback - callback function to be called after the message is closed
	 */
	function tcbToast( message, error, callback ) {
		/* Also allow "message" objects */
		if ( typeof message !== 'string' ) {
			message = message.message || message.error || message.success;
		}
		if ( ! error ) {
			error = false;
		}
		TCB_Front.notificationElement.toggle( message, error ? 'error' : 'success', callback );
	}
} )( typeof ThriveGlobal === 'undefined' ? jQuery : ThriveGlobal.$j );
