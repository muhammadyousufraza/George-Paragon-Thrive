module.exports = TVE.Views.Base.component.extend( {
	after_init() {
		/* backwards compatibility stuff */
		TVE.Editor_Page.editor.find( '.animated' ).removeClass( 'animated' );
	},
	controls_init( controls ) {
		let startAnimation, displayAnimation, endAnimation;

		/* Display Position Control  */
		controls.DisplayPosition.input = function ( $element, dom ) {
			const positionAttribute = dom.getAttribute( 'data-value' ),
				verticalPosition = positionAttribute.split( '-' )[ 0 ];
			let horizontalPosition = positionAttribute.split( '-' )[ 1 ];

			if ( TVE.main.device === 'mobile' ) {
				horizontalPosition = $element.attr( 'data-position' ).split( '-' )[ 1 ];
			}

			$element.attr( 'data-position', verticalPosition.concat( '-', horizontalPosition ) );

			updateVerticalSpacing( $element );
			updateHorizontalSpacing( $element );
		};

		controls.DisplayPosition.update = function ( $element ) {
			let positionAttribute = $element.attr( 'data-position' );

			this.$( '.items-9' ).removeClass( 'mobile' );
			this.$( '.active' ).removeClass( 'active' );

			if ( TVE.main.device === 'mobile' ) {
				positionAttribute = positionAttribute.split( '-' )[ 0 ].concat( '-center' );
				this.$( '.items-9' ).addClass( 'mobile' );
			}

			this.$( `[data-value=${positionAttribute}]` ).addClass( 'active' );
		};

		/* Vertical Spacing Control  */
		controls.VerticalSpacing.input = function ( $element, dom ) {
			/* Only allow numerical values */
			if ( isNaN( dom.value ) ) {
				controls.VerticalSpacing.setValue( 0 );
			}

			const verticalPosition = $element.attr( 'data-position' ).split( '-' )[ 0 ];
			$element.head_css( {[ verticalPosition ]: dom.value + 'px'}, false, `${TVE.identifier( 'notification' )}[data-position*="${verticalPosition}"]`, true, '' );
		};

		controls.VerticalSpacing.update = function ( $element ) {
			updateVerticalSpacing( $element );
		};

		/* Horizontal Spacing Control  */
		controls.HorizontalSpacing.input = function ( $element, dom ) {
			/* Only allow numerical values */
			if ( isNaN( dom.value ) ) {
				controls.VerticalSpacing.setValue( 0 );
			}

			const horizontalPosition = $element.attr( 'data-position' ).split( '-' )[ 1 ];
			$element.head_css( {[ horizontalPosition ]: dom.value + 'px'}, false, `${TVE.identifier( 'notification' )}[data-position*="${horizontalPosition}"]`, true, '' );
		};

		controls.HorizontalSpacing.update = function ( $element ) {
			updateHorizontalSpacing( $element );
		};

		/* Animation Direction Control  */
		controls.AnimationDirection.input = function ( $element, dom ) {
			$element.attr( 'data-animation', dom.value );
			$element.toggleClass( 'tcb-animated', dom.value !== 'none' );

			animateNotification( $element );
		};

		controls.AnimationDirection.update = function ( $element ) {
			this.setValue( $element.attr( 'data-animation' ) );
		};

		/* Animation Time Control  */
		controls.AnimationTime.input = function ( $element, dom ) {
			$element.attr( 'data-timer', dom.value * 1000 );
		};

		controls.AnimationTime.change = function ( $element ) {
			animateNotification( $element, true );
		};

		controls.AnimationTime.update = function ( $element ) {
			let timerValue = $element.attr( 'data-timer' );

			if ( timerValue < 0 ) {
				$element.attr( 'data-timer', 3000 );
			} else if ( timerValue > 10 ) {
				timerValue = timerValue / 1000;
			}
			this.setValue( timerValue );
		};

		controls.VerticalPosition.applyStyles = function ( $element, dom ) {
			const state = $element.attr( 'data-state' );
			$element.find( `.notifications-content.notification-${state}` ).css( 'justify-content', dom.getAttribute( 'data-value' ) );
		};

		controls.VerticalPosition.update = function ( $element ) {
			const state = $element.attr( 'data-state' );
			let verticalPosition = $element.find( `.notifications-content.notification-${state}` ).css( 'justify-content' );
			verticalPosition = verticalPosition === 'normal' ? 'flex-start' : verticalPosition;

			this.setActive( verticalPosition );
		};

		controls.MaximumWidth.input = function ( $element, dom ) {
			const state = $element.attr( 'data-state' );
			this.applyElementCss( {'max-width': dom.value + 'px'}, $element.find( `.notifications-content.notification-${state}` ), '', '' )
		};

		controls.MaximumWidth.update = function ( $element ) {
			const state = $element.attr( 'data-state' );

			let maxWidth = $element.find( `.notifications-content.notification-${state}` ).css( 'max-width' ).split( 'px' )[ 0 ];
			const width = $element.find( `.notifications-content.notification-${state}` ).css( 'width' ).split( 'px' )[ 0 ];

			if ( maxWidth === 'none' ) {
				maxWidth = width;
				$element.find( `.notifications-content.notification-${state}` ).head_css( {'max-width': width + 'px'} );
			}
			this.setValue( maxWidth );
		};

		controls.MinimumHeight.input = function ( $element, dom ) {
			const state = $element.attr( 'data-state' );
			this.applyElementCss( {'min-height': dom.value + 'px'}, $element.find( `.notifications-content.notification-${state}` ), '', '' )
		};

		controls.MinimumHeight.update = function ( $element ) {
			const state = $element.attr( 'data-state' );
			this.setValue( $element.find( `.notifications-content.notification-${state}` ).css( 'min-height' ).split( 'px' )[ 0 ] );
		};

		/**
		 * Animate the Notification in the editor (slide-out is only triggered by changing the AnimationTime value)
		 *
		 * @param  $element
		 * @param  animationCanStop
		 */
		function animateNotification( $element, animationCanStop = false ) {
			clearTimeouts();
			setAnimation( $element );

			const timer = $element.attr( 'data-timer' ),
				animation = $element.attr( 'data-animation' );

			$element.hide();
			$element.addClass( 'editor-preview' );

			/* Only allow animations to be stopped when they are triggered from the AnimationTime */
			if ( animationCanStop ) {
				TVE.inner_$( 'html, body' ).on( 'mousedown.notification', () => {
					stopNotificationAnimation( $element );
				} );

				TVE.$( 'html, body' ).on( 'mousedown.notification_main', () => {
					stopNotificationAnimation( $element );
				} );
			}

			startAnimation = setTimeout( () => {
				/* Slide in */
				$element.show();
				$element.removeAttr( 'data-animation' );

				if ( animationCanStop ) {
					/* Slide out */
					displayAnimation = setTimeout( () => {
						if ( animation !== 'none' ) {
							setAnimation( $element );
						} else {
							$element.hide();
						}
						stopNotificationAnimation( $element );
					}, timer );
				} else {
					setTimeout( () => {
						$element.removeClass( 'editor-preview' );
						setAnimation( $element );
					}, 1000 )
				}
			}, 300 );
		}

		/**
		 * End the current Notification animation
		 *
		 * @param  $element
		 */
		function stopNotificationAnimation( $element ) {
			clearTimeouts();

			endAnimation = setTimeout( () => {
				$element.removeClass( 'editor-preview' );

				if ( typeof $element.attr( 'data-animation' ) === 'undefined' ) {
					setAnimation( $element );
				}

				$element.show();
				TVE.inner_$( 'html, body' ).off( 'mousedown.notification' );
				TVE.inner_$( 'html, body' ).off( 'mousedown.notification_main' );
			}, 500 );
		}

		/**
		 * Stop previous animations by clearing the timeouts
		 */
		function clearTimeouts() {
			/* Stop previous animations by clearing the timeouts */
			[ startAnimation, displayAnimation, endAnimation ].forEach( timeout => {
				if ( typeof timeout !== 'undefined' ) {
					clearTimeout( timeout );
				}
			} );
		}

		/**
		 * Set animation on the Notification Element
		 *
		 * @param  $element
		 */
		function setAnimation( $element ) {
			const animation = controls.AnimationDirection.$el.find( '.tve-select' )[ 0 ].value;

			if ( animation !== 'none' ) {
				$element.attr( 'data-animation', animation );
			}
		}

		/**
		 * @param  $element
		 */
		function updateVerticalSpacing( $element ) {
			const position = $element.attr( 'data-position' ).split( '-' ),
				verticalPosition = position[ 0 ];

			/* Vertical Spacing Control Update */
			if ( [ 'top', 'bottom' ].includes( verticalPosition ) ) {
				controls.VerticalSpacing.$el.show();
				controls.VerticalSpacing.$el.find( '.input-label' ).text( `${verticalPosition} spacing` );
				controls.VerticalSpacing.setValue( $element.css( `${verticalPosition}` ).split( 'px' )[ 0 ] );
			} else {
				controls.VerticalSpacing.hide();
			}
		}

		/**
		 * @param  $element
		 */
		function updateHorizontalSpacing( $element ) {
			const position = $element.attr( 'data-position' ).split( '-' ),
				horizontalPosition = position[ 1 ];

			/* Horizontal Spacing Control Update */
			if ( ( [ 'left', 'right' ].includes( horizontalPosition ) ) && ( TVE.main.device !== 'mobile' ) ) {
				controls.HorizontalSpacing.$el.show();
				controls.HorizontalSpacing.setValue( $element.css( `${horizontalPosition}` ).split( 'px' )[ 0 ] );
			} else {
				controls.HorizontalSpacing.hide();
			}
		}
	},

	editNotifications() {
		const $notificationWrapper = TVE.inner_$( TVE.identifier( 'notification' ) ),
			currentState = $notificationWrapper.attr( 'data-state' ),
			utils = require( '../utils' );

		TVE.main.sidebar_extra.$( '.sidebar-item.add-element' ).show();
		$notificationWrapper.find( '.tve-prevent-content-edit' ).removeClass( 'tve-prevent-content-edit' );

		/* Update available sidebar options for the Notification Element */
		utils.updateNotificationSidebarOptions( true );

		TVE.main.EditMode.enter( $notificationWrapper, {
			show_default_message: true,
			can_insert_elements: true,
			view_label: 'Editing Notification',
			element_selectable: true,
			restore_state: false,
			states: [
				{
					label: 'Success',
					value: 'success',
					default: currentState === 'success',
				},
				{
					label: 'Warning',
					value: 'warning',
					default: currentState === 'warning',
				},
				{
					label: 'Error',
					value: 'error',
					default: currentState === 'error',
				}
			],
			callbacks: {
				exit: () => {
					/* Restore editor settings */
					const state = $notificationWrapper.attr( 'data-state' );

					$notificationWrapper.addClass( 'tve_no_icons' );

					TVE.main.sidebar_extra.$( '.sidebar-item.add-element' ).hide();
					TVE.inner_$( `.notifications-content.notification-${state}` ).children().addClass( 'tve-prevent-content-edit' );

					/* Set focus on the notification element */
					TVE.Editor_Page.focus_element( $notificationWrapper );

					/* Update available sidebar options for the Notification Element */
					utils.updateNotificationSidebarOptions( false );

					/* Add corresponding link for the preview button */
					utils.updatePreviewLink( state );
				},
				state_change: state => {
					$notificationWrapper.attr( 'data-state', state );

					TVE.Components.notification.controls.VerticalPosition.update( $notificationWrapper );

					TVE.Editor_Page.focus_element( $notificationWrapper );

					utils._updateMainFrameVars( state );
				}
			}
		} );
	},
} );
