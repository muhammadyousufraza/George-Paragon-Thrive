const {applyDefaultRules, initializeNotificationEditor, _updateMainFrameVars, updatePreviewLink, updateNotificationSidebarOptions} = require( './utils' );
module.exports = {
	'tcb-ready': () => {
		const $notificationWrapper = TVE.inner_$( TVE.identifier( 'notification' ) ),
			$notificationContent = $notificationWrapper.find( '.notifications-content' );

		$notificationWrapper.addClass( 'thrv_wrapper' );
		$notificationContent.removeClass( 'thrv_wrapper' );

		/* add the dataset of the default template when nothing else is set */
		if ( typeof $notificationWrapper.attr( 'data-ct' ) === 'undefined' ) {
			$notificationWrapper.attr( {
				'data-ct': 'notification-0',
				'data-ct-name': 'Default notification'
			} );
		}

		const displayedState = $notificationWrapper.attr( 'data-state' );

		/* Focus the notification element, open its Main Controls and update them */
		TVE.Editor_Page.focus_element( $notificationWrapper );

		initializeNotificationEditor( displayedState );
		_updateMainFrameVars( displayedState );

		updatePreviewLink();

		/* Only display a set of allowed elements that can be added inside the Notification element */
		const allowedElements = [ 'text', 'image', 'button', 'columns', 'contentbox', 'divider', 'icon', 'notification_message' ];

		const hiddenElements = Object.keys( TVE.Elements ).filter( element => ! allowedElements.includes( element ) );

		TVE.main.sidebar_toggle_elements( hiddenElements, false );

		if ( TVE.stylesheet.cssRules.length === 0 ) {
			applyDefaultRules( true )
		}
	},

	'tcb.element.focus': $element => {
		const isNotification = $element.is( TVE.identifier( 'notification' ) );
		if ( isNotification ) {
			/* Disable Margin Control for the Notification Element */
			TVE.Components.layout.disable_extra_controls( [ 'top', 'right', 'bottom', 'left' ].map( side => 'margin-' + side ) );

			/* Update available sidebar options for the Notification Element */
			updateNotificationSidebarOptions( TVE.$body.hasClass( 'edit-mode-active' ) );
		}

		TVE.Components.layout.$el.find( '.tve-control[data-prop="width"] .tve-input-um[data-value="%"]' ).toggle( ! isNotification );
		TVE.main.$( '.tve-active-element' ).addClass( 'no-states' );
	},

	'tcb.after-insert': $element => {
		if ( $element.is( '.thrv-notification_message' ) ) {
			$element.addClass( 'tcb-selector-no_save tcb-selector-no_clone' )
		}

		TVE.main.$( '.tve-active-element' ).addClass( 'no-states' );
	},

	/**
	 * @param  $element
	 */
	'tcb_after_cloud_template': $element => {
		if ( $element.is( TVE.identifier( 'notification' ) ) ) {
			$element.addClass( 'notification-edit-mode' );

			updatePreviewLink( $element.attr( 'data-state' ) );
			initializeNotificationEditor( $element.attr( 'data-state' ) );

			if ( $element.attr( 'data-ct' ) === 'notification--1' ) {
				applyDefaultRules( false );
			}
		}
	},
};
