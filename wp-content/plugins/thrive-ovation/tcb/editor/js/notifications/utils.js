module.exports = {
	applyDefaultRules( shouldSave ) {
		const
			iconSize = 27,
			mobileIconSize = 24,
			defaultRules = {
				[ TVE.main.responsive.desktop.media ]: {
					'.notifications-content': {
						'box-shadow': 'rgba(0, 0, 0, 25%) 0px 4px 9px 0px',
						'margin': 0,
						'padding': 0,
						'width': '501px',
						'min-height': '20px',
						'border-radius': '2px',
						'border': '2px solid',
						'border-color': 'var(--notification-color)',
						'background-color': 'rgb(255, 255, 255)',
					},
					'.thrv-columns': {
						'margin': '0!important'
					},
					'.tcb-flex-row': {
						'padding': '0!important',
						'margin-left': '0px',
					},
					'.tcb-flex-col': {
						'padding-left': '0px'
					},
					'.thrv_icon': {
						'border': 'none',
						'border-radius': 0,
						'width': `${iconSize}px`,
						'height': `${iconSize}px`,
						'--tve-icon-size': `${iconSize}px`,
						'font-size': `${iconSize}px`,
						'margin': '0!important',
						'color': 'rgb(255, 255, 255)',
						'--tcb-local-color-icon': 'var(--notification-color)',
						'--tcb-local-color-var': 'var(--notification-color)',
						'background-image': 'linear-gradient(var(--tcb-local-color-icon), var(--tcb-local-color-icon))',
						'padding': '16px !important',
					},
					'.thrv-notification_message': {
						'margin': 0,
						'text-align': 'center',
						'font-size': '16px',
						'color': '#191f28'
					},
					'.tcb-flex-col:first-child': {
						'max-width': '12%'
					}
				},
				[ TVE.main.responsive.mobile.media ]: {
					'.notifications-content': {
						'width': 'unset',
						'min-width': '360px',
						'background-color': 'var(--notification-color) !important',
						'border': 'none',
						'border-radius': 0,
					},
					'.thrv-notification_message': {
						'min-width': 'auto',
						'color': 'rgb(255, 255, 255)',
						'text-align': 'left',
					},
					'.thrv_icon': {
						'width': `${mobileIconSize}px`,
						'height': `${mobileIconSize}px`,
						'--tve-icon-size': `${mobileIconSize}px`,
						'font-size': `${mobileIconSize}px`,
					},
					'.thrv-columns': {
						'width': '65%',
						'margin-left': 'auto !important',
						'margin-right': 'auto !important',
						'float': 'none',
					},
					'.tcb-flex-col:first-child': {
						'max-width': '27%'
					}
				}
			};

		/* when we open the template for the first time, we apply the default styles */
		[ 'success', 'warning', 'error' ].forEach( state => {
			TVE.FLAGS.notification_state = state;
			for ( const media in defaultRules ) {
				for ( const selector in defaultRules[ media ] ) {
					TVE.inner_$( selector ).head_css( defaultRules[ media ][ selector ], media )
				}
			}
		} );

		delete TVE.FLAGS.notification_state;
		/* and silently save so we can have the changes in preview */
		if ( shouldSave ) {
			TVE.Editor_Page.save( true, null, {}, true );
		}
	},

	initializeNotificationEditor( displayedState ) {
		/* Do not allow the content to be edited when it is not in edit mode */
		TVE.inner_$( `.notifications-content.notification-${displayedState}` ).children().addClass( 'tve-prevent-content-edit' );

		/* Initialize selectors */
		tve_notification.elements.forEach( elementKey => {
			const selector = TVE.identifier( elementKey );

			TVE.inner_$( selector ).each( ( index, element ) => {
				if ( elementKey === 'notification_message' ) {
					/* we want to drag the notification message */
					element.classList.add( 'tcb-selector-no_save', 'tcb-selector-no_clone' )
				} else {
					element.classList.add( 'tve_no_icons' );
				}
			} )
		} );
	},

	/**
	 * Add current notification color to the main frame for the controls to properly display the current color
	 *
	 * @param  state
	 * @private
	 */
	_updateMainFrameVars( state ) {
		const color = {
			'success': 'rgb(74, 178, 93)',
			'warning': 'rgb(243, 156, 15)',
			'error': 'rgb(214, 54, 56)'
		};
		TVE.changeCssVariableValue( '--notification-color', color[ state ] );
	},

	/**
	 * Add corresponding link for the preview button
	 *
	 * @param  state
	 */
	updatePreviewLink: ( state = 'success' ) => {
		const $previewButton = TVE.$( '.preview-content' );
		let previewLink = $previewButton.attr( 'href' );

		if ( previewLink.includes( 'notification-state' ) ) {
			previewLink = previewLink.split( '&notification-state=' )[ 0 ];
		}

		$previewButton.attr( 'href', previewLink.concat( `&notification-state=${state}` ) );
	},

	updateNotificationSidebarOptions( isEditMode ) {
		const hiddenComponents = '#tve-layout-component,#tve-background-component,#tve-borders-component,#tve-shadow-component,#tve-responsive-component,#tve-styles-templates-component';

		TVE.main.EditMode.$componentPanel.find( hiddenComponents ).toggle( isEditMode );
		TVE.main.EditMode.$componentPanel.find( '#tve-notification-component .non-edit-mode-controls' ).toggle( ! isEditMode );
		TVE.main.EditMode.$componentPanel.find( '#tve-notification-component .edit-mode-controls' ).toggle( isEditMode );
		TVE.main.EditMode.$componentPanel.find( '#tve-cloud-templates-component' ).toggle( ! isEditMode );
	},
};
