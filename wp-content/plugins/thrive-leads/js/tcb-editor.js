/**
 * This file is included each time the TCB editor is opened for a piece of content - Loaded in the main frame
 */
window.TL_Editor = window.TL_Editor || {}
window.TCB_AnimViews = TVE.Views.Components.AnimationViews;
window.TL_Editor.views = window.TL_Editor.views || {};

/**
 * TL Components Views
 * @type {{}}
 */
window.TL_Editor.views.components = window.TL_Editor.views.components || {};

/**
 * Backbone Panel View used to display all TL Shortcodes list when
 * user adds TL Shortcode Element in content
 */
window.TL_Editor.views.ShortcodesPanel = TVE.Views.InlinePanel.extend( {

	template: TVE.tpl( 'inline/content-templates' ),

	after_initialize: function () {

		this.$( '.drop-panel' ).addClass( 'small-pad tcb-autocomplete' );
		this.autocomplete();
	},

	autocomplete: function () {

		var self = this;

		this.$input = this.$( '.tcb-search' ).autocomplete( {
			minLength: 0,
			appendTo: this.$( '.popup-content' ),

			source: function ( request, response ) {

				var results = jQuery.ui.autocomplete.filter( TVE.CONST.tl_shortcodes, request.term );
				response( results.slice( 0, 5 ) );
			},

			select: function ( e, ui ) {

				self.render_html( ui.item.id );
				e.stopPropagation();

				return false;
			}
		} );

		this.$input.data( 'ui-autocomplete' )._renderItem = function ( ul, item ) {

			ul.addClass( 'tcb-suggest' );
			var r = new RegExp( this.term, 'i' ),
				li = jQuery( '<li></li>' )
					.data( 'item.autocomplete', item )
					.append( '<a href="#" class="tcb-truncate">' + item.label.replace( r, '<span class="highlight">' + this.term + '</span>' ) + '</a>' )
					.appendTo( ul );

			return li;
		};
	},

	/**
	 * Do an ajax call on which request TL hooks into
	 * render_tl_shortcode is the filter implemented
	 *
	 * @param shortcode_id
	 */
	render_html: function ( shortcode_id ) {

		var action = 'save_post_external',
			type = 'post',
			dataType = 'json',
			data = {
				shortcode_id: shortcode_id,
				external_action: 'render_tl_shortcode'
			};

		TVE.ActiveElement.addClass( 'tcb-el-loading' );

		TVE.ajax( action, type, data, dataType )
		   .done( function ( response ) {
			   var $element = TVE.drag.insert( response.html, TVE.ActiveElement, false, true );
			   TVE.Editor_Page.focus_element( $element );
			   TVE.inner.window.TCB_Front.handleIframes( $element, true );
			   TVE.ActiveElement.removeClass( 'tcb-el-loading' );
		   } );
	},

	onOpen: function () {

		this.$input.val( '' ).focus().autocomplete( 'search' );
	}
} );

/**
 * TL Shortcode View Component
 */
TVE.Views.Components.tl_shortcode = TVE.Views.Base.component.extend( {

	before_update: function () {

		var type = TVE.ActiveElement.find( '.tve-leads-conversion-object' ).data( 'tl-type' );
		var id = null;

		if ( type ) {
			id = parseInt( type.replace( 'shortcode_', '' ) );
		}

		this.list.set_value( id );
	},

	controls_init: function () {

		var self = this;

		this.placeholder_panel = new window.TL_Editor.views.ShortcodesPanel( {
			component: this,
			minWidth: 300,
			no_buttons: true
		} );

		this.list = new TVE.Views.Controls.List( {
			el: this.$( '#tl_shortcodes_list' )[ 0 ],
			items: TVE.CONST.tl_shortcodes
		} );

		this.list.on( 'change', function ( id ) {
			self.placeholder_panel.render_html( id );
		} );
	},

	placeholder_action: function () {

		this.placeholder_panel.open( TVE.ActiveElement, TVE.ActiveElement.find( '.tcb-inline-placeholder-action' ) )
	}
} );

window.TL_Editor.views.ThriveBoxAction = TCB_AnimViews.ThriveLightbox.extend( {
	controls_init: function () {
		this.list = new TVE.Views.Controls.List( {
			el: this.$( '.state-list' )[ 0 ],
			items: this.options.actions[ this.key ].options
		} );
		this.event_trigger = 'click';
		this.$animation = this.$( '#lb-animation' ).hide();
	},
	apply_settings: function ( $element ) {
		if ( ! this.validate() ) {
			return false;
		}
		this.model.set( {
			a: this.key,
			t: this.parent_tab.$( '#a-popup-trigger' ).val(),
			config: {
				l_id: this.list.get_value()
			}
		} );
		return true;
	}
} );

/**
 * jQuery Context
 */
( function ( $ ) {

	/**
	 * Used to set/write Asset Delivery Options on LG Elements
	 *
	 * This is called in LeadGenerationView context
	 *
	 * @returns {write_asset_delivery}
	 */
	function write_asset_delivery() {

		var self = this,
			asset_delivery_settings = this.model.get( 'asset_delivery' );

		if ( parseInt( asset_delivery_settings._asset_option ) === 1 ) {

			$.each( asset_delivery_settings, function ( prop, value ) {
				var $input = self.$( '#' + prop );
				if ( $input.length > 0 ) {
					$input.val( value );
				} else {
					var input = TVE.Components.lead_generation.generateHiddenInput( {
						name: prop,
						value: value
					} );
					TVE.Components.lead_generation.getWrapper( 'form' ).append( input );
				}
			} );

			return this;
		}

		this.$( "input[name*='asset']" ).remove();
	}

	/**
	 * Used to read Asset Delivery Settings on LG Element
	 *
	 * @returns {{}}
	 */
	function read_asset_delivery() {

		var config = {};

		this.$( "input[name*='asset']" ).each( function () {
			config[ this.getAttribute( 'name' ) ] = this.value;
		} );

		this.model.set( 'asset_delivery', $.extend( {
			_asset_group: 0,
			_asset_option: 0
		}, config ) );

		return config;
	}

	/**
	 * Asset Delivery Control injected on Lead Generation Component
	 *
	 * Enable or Disable the Assets Delivery
	 */
	TVE.Views.Controls.AssetDelivery = TVE.Views.Controls.Switch.extend( {

		after_initialize: function () {
			TVE.add_filter( 'lg_api_save.' + this.component.key, this.update.bind( this ) );
			TVE.add_filter( 'lg_custom_html_save.' + this.component.key, this.update.bind( this ) );
		},

		/**
		 * Update the Asset Delivery Control when user clicks on the LG Element
		 */
		update: function () {
			var lgComponent = this.component;

			/**
			 * if the asset delivery reader is not set then set one
			 */
			if ( lgComponent.leadGenerationView._read.asset_delivery === undefined ) {
				lgComponent.leadGenerationView._read.asset_delivery = read_asset_delivery; //set the reader function for asset_delivery
			}

			/**
			 * read the Asset Delivery options
			 */
			lgComponent.leadGenerationView.read( 'asset_delivery' );

			if ( ! lgComponent.leadGenerationModel.get( 'form_fields' ).hasOwnProperty( 'email' ) ) {
				this.input( TVE.ActiveElement, {checked: 0} )
				this.disable();
			} else {
				this.enable();
			}
			var is_checked = lgComponent.leadGenerationModel.get( 'asset_delivery' )._asset_option;
			this.setChecked( is_checked );
			this.$el.parent().find( '#tve-leads-asset-controls' ).toggleClass( 'tcb-hidden', ! is_checked );
		},

		/**
		 * Set the Asset Delivery Settings inside LG Element based on what user sets/change in this control
		 *
		 * @param $element
		 * @param dom
		 */
		input: function ( $element, dom ) {

			var lgComponent = this.component;

			/**
			 * Sets the writer for Asset Delivery Options
			 */
			if ( lgComponent.leadGenerationView._write.asset_delivery === undefined ) {
				lgComponent.leadGenerationView._write.asset_delivery = write_asset_delivery;
			}

			var asset_delivery_settings = lgComponent.leadGenerationModel.get( 'asset_delivery' );

			asset_delivery_settings = jQuery.extend( asset_delivery_settings, {
				_asset_option: dom.checked ? 1 : 0
			} );

			this.$el.parent().find( '#tve-leads-asset-controls' ).toggleClass( 'tcb-hidden', ! dom.checked );

			lgComponent.leadGenerationModel.set( 'asset_delivery', asset_delivery_settings );
			lgComponent.leadGenerationView.write( 'asset_delivery' );

			if ( dom.checked ) {
				/**
				 * We need to trigger the change event on AssetGroup Select when enabling the asset delivery
				 */
				this.$el.parent().find( '[data-view="AssetGroup"] select' ).trigger( 'change' );
			}
		}
	} );

	/**
	 * Asset Delivery Group, visible when the Asset Delivery is Enabled
	 * Used to set the Asset Delivery Group on LG Element
	 */
	TVE.Views.Controls.AssetGroup = TVE.Views.Controls.Select.extend( {

		after_initialize: function () {
			TVE.add_filter( 'lg_consent_apis.' + this.component.key, _.bind( function ( apis ) {

				if ( this.component.has_asset_delivery() ) {
					apis.push( {
						key: 'asset',
						label: this.model.config.consent_label.replace( '{asset_name}', this.get_selected_label() )
					} );
				}
				return apis;
			}, this ) );
		},

		update: function () {

			var lgComponent = this.component,
				_group = lgComponent.leadGenerationModel.get( 'asset_delivery' )._asset_group;

			/**
			 * set the writer callback for asset delivery options/settings
			 */
			if ( lgComponent.leadGenerationView._write.asset_delivery === undefined ) {
				lgComponent.leadGenerationView._write.asset_delivery = write_asset_delivery;
			}

			/**
			 * if the AD Group is not set yet,
			 * then get the 1st option from select and set it
			 */
			if ( _group === 0 ) {
				_group = this.$select.find( 'option' ).first().val();
				lgComponent.leadGenerationModel.get( 'asset_delivery' )._asset_group = _group;
				lgComponent.leadGenerationView.write( 'asset_delivery' );
			}

			this.setValue( _group );
		},

		input: function ( $element, dom ) {

			var lgComponent = this.component,
				_asset_delivery = lgComponent.leadGenerationModel.get( 'asset_delivery' );

			_asset_delivery._asset_group = dom.value;

			lgComponent.leadGenerationModel.set( {
				asset_delivery: _asset_delivery,
				write: 'asset_delivery'
			} );
		}
	} );

	/**
	 * DOM READY !!! :)
	 */
	$( function () {

		TVE.add_filter( 'tcb_page_event_actions', function ( actions, LightboxBase ) {
			var ThriveBox = LightboxBase.extend( {
				template: TVE.tpl( 'page-events/thrive-box' ),
				get: function () {
					return TVE.Components.animation.options.actions.thrive_leads_2_step.options;
				}
			} );
			delete actions.thrive_leads_2_step.disabled;
			actions.thrive_leads_2_step.view = ( new ThriveBox() );

			return actions;
		} );

		TVE.add_filter( 'tve_custom_html_code', function ( $form ) {

			if ( typeof tve_leads_page_data !== 'undefined' ) {
				var data = {
					'group_id': tve_leads_page_data.group_id,
					'form_type_id': tve_leads_page_data.post_id,
					'variation_id': tve_leads_page_data._key
				};

				$( '<div>', {
					html: '__CONFIG_tve_leads_additional_fields_filters__' + JSON.stringify( data ) + '__CONFIG_tve_leads_additional_fields_filters__'
				} ).hide().appendTo( $form );
			}
		} );

		TVE.add_filter( 'should_have_width_on_float', function ( list ) {
			list.push( 'thrive_leads_shortcode' );

			return list;
		} );

		TVE.add_action( 'tcb.symbol.after_save', function ( $element ) {
			$element.find( '.tve-tl-anim' ).removeClass( 'tve-tl-anim' );
		} );
	} );

} )( jQuery );
