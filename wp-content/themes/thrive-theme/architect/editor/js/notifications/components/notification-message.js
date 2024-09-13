const typography = require( '../../main/views/components/typography' );

module.exports = typography.extend( {
	getTargetElement() {
		return TVE.ActiveElement.add( TVE.ActiveElement.find( '.tve_editable' ) ).not( '.tcb-el-group' );
	},
	controls_init( controls ) {
		typography.prototype.controls_init.apply( this, Array.from( arguments ) );

		controls.LineSpacing.getElement = function () {
			return TVE.ActiveElement;
		};
		controls.LineSpacing.readValues = function () {
			const $element = this.getElement();

			return {
				top: $element.css( 'padding-top' ),
				bottom: $element.css( 'padding-bottom' )
			};
		};
		controls.LineSpacing.writeStyle = function ( css ) {
			this.applyElementCss( css, TVE.ActiveElement, '', '' );
		};

		TVE.add_action( 'tcb.typography.notification_message.clear_formatting', () => {
			TVE.ActiveElement[ 0 ].style.setProperty( 'padding-top', '0px', 'important' );
			TVE.ActiveElement[ 0 ].style.setProperty( 'padding-bottom', '0px', 'important' );
		} );
	},
	lineSpacingControl() {
		return require( '../../main/views/controls/text/line-spacing' );
	},
} );
