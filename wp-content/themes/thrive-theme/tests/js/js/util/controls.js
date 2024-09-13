/** utility functions for controls */
class Controls {
	/**
	 * Applies to button group
	 * @param controlView
	 * @param values
	 */
	static setActive( controlView, values ) {
		if ( ! Array.isArray( values ) ) {
			values = [ values ];
		}

		controlView.clearActive();

		let control;

		values.forEach( val => {
			control = controlView.$( `[data-value="${val}"]` ).click()[ 0 ];
		} );

		controlView.change( controlView.applyTo(), control );
	}

	/**
	 * Read in head_css for the element
	 * @param control
	 * @param prop
	 * @returns {*|void}
	 */
	static head_css( control, prop ) {
		return control.applyTo().head_css( prop, false, control.config.css_suffix, false, control.config.css_prefix );
	}

	/**
	 * get control instance from component
	 * component can be in the form of 'typography.FontFace' or 'typography/FontFace'
	 * @param component
	 * @param {String} [control]
	 * @returns {*}
	 */
	static get( component, control ) {

		if ( component.match( /(.+)[\.|\/](.+)/ ) ) {
			component = RegExp.$1;
			control = RegExp.$2;
		}

		const $control = TVE.Components[ component ].controls[ control ];

		/* ensure all callbacks are triggered */
		if ( typeof $control.onShow === 'function' ) {
			$control.onShow();
			if ( $control.favorites && $control.favorites.on_color_show ) {
				$control.favorites.on_color_show();
			}
		}
		return $control;
	}

	static background_panel( panel ) {
		return TVE.Components.background.tabs.data.buttons[ panel ].panel;
	}

	static open_panel( tabs, section ) {
		tabs.$el.find( `[data-panel="${section}"]` ).trigger( 'click' );

		return tabs.data.buttons[ section ].panel;
	}

	static get background() {
		return {

			color: ( color ) => Controls.get( 'background.ColorPicker' ).setValue( color, true, true ),

			add_solid_layer: ( color ) => {
				const panel = Controls.open_panel( TVE.Components.background.tabs, 'solid' );
				panel.color.setValue( color, true );
				panel.onApply();

				return panel;
			}
		};
	}

	static get border() {
		const border = Controls.get( 'borders.Borders' );

		return {
			color: ( color ) => border.controls.Color.setValue( color, true, true ),
			width: ( width ) => border.controls.Width.setValue( parseInt( width ), true, true ),
			style: ( style ) => border.controls.Style.setValue( style, true, true ),
			side: function ( side ) {
				border.$( side ? '.tve-border-side[data-value="' + side + '"]' : '.default' ).trigger( 'click' );
				return this;
			}
		}
	}

	static get corner() {
		const border = Controls.get( 'borders.Borders' ),
			corner = Controls.get( 'borders.Corners' );

		return {
			side: function ( side ) {
				border.$( side ? `.tve-corner[data-value="${side}"]` : '.default' ).trigger( 'click' );
				return this;
			},
			size: ( value ) => corner.controls.BorderRadius.setValue( parseInt( value ), true, true )

		}
	}
}

module.exports = Controls;
