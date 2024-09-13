/**
 * This file is included only when editing a TL form ( and only in the main frame )
 */
window.TL_Editor = window.TL_Editor || {};
window.TCB_AnimViews = TVE.Views.Components.AnimationViews;

window.TL_Editor.views = window.TL_Editor.views || {};
TVE.leads = TVE.leads || {};

/**
 * Modal for templates
 * Local and Cloud templates are listed in the same tab
 */
window.TL_Editor.views.ModalTemplates = TVE.modal.base.extend( {
	el: TVE.modal.get_element( 'tl-templates' ),
	initialize: function () {
		/* Items templates */
		TVE.modal.base.prototype.initialize.apply( this, arguments );
		this.$error = this.$( '.error-container' );

		this.$template_items = this.$( '.tl-set-list' );
		this.$saved_templates_checkbox = this.$( '.saved-templates-checkbox' );

		this.$warning = this.$( '.warning-ct-change' );
		this.$footer = this.$( '.tcb-modal-footer' );

		this.templateWasSelected = false;
	},

	after_initialize: function ( options ) {
		this.initClickHandler();
	},

	before_open: function () {
		/* Add the standard styling class*/
		this.$el.addClass( 'modal-w-sidebar' );

		/* Reset all variables */
		this.$template_items.empty();
		this.$warning.css( 'display', 'none' );
		this.$footer.css( 'display', 'none' );
		this.$( '.tl-category-filter.active' ).removeClass( 'active' );
		this.$( ".tl-category-filter[data-content='default']" ).addClass( 'active' );
		this.$( '.tve-c-modal-search-input' ).val( '' );

		this.$modalContent = this.$( '.tcb-modal-content' ).css( 'height', '100%' );

		/* Always render the default templates first */
		this.templatesType = 'default';
		this.renderTemplates();
	},

	/**
	 * Triggers when a filter from the sidebar is clicked
	 * @param event
	 */
	filterClick: function ( event ) {
		this.templatesType = event.currentTarget.getAttribute( 'data-content' );
		this.renderTemplates();
		this.$( '.tl-category-filter.active' ).removeClass( 'active' );
		event.currentTarget.classList.toggle( 'active' );
	},

	/**
	 * Triggers when the searchbar is used
	 */
	onSearch: function () {
		_.throttle( this.renderTemplates(), 420 );
	},

	/**
	 * Clear the searchbar
	 */
	clearSearch: function () {
		this.$( '.tve-c-modal-search-input' ).val( '' );
		this.renderTemplates();
	},

	/**
	 * Render the templates that have a certain type
	 */
	renderTemplates: function () {
		var self = this;
		this.$template_items.empty();

		/* Display checkbox only when viewing Saved Templates */
		this.$saved_templates_checkbox.css( 'display', 'none' );
		if ( this.templatesType === 'saved' ) {
			this.$saved_templates_checkbox.css( 'display', 'flex' );
		}

		this.getItems().then( function ( templates ) {
			self.setItems( self.filterTemplates( templates ) ).then( function () {
				self.applyMasonry();
			} )
		} );
	},

	/**
	 * Fetch the templates that have a certain type
	 * @returns {Promise<unknown>}
	 */
	getItems: function () {
		var self = this;
		return new Promise( function ( resolve ) {
			if ( self.templatesType === 'default' ) {
				self.setTemplates( TVE.CONST.tl_templates );
				resolve( self.templates );
			} else if ( self.templatesType === 'saved' ) {
				var data = {
					external_action: window.tve_leads_page_data.tpl_action,
					route: 'get_saved',
					current_template: self.$( '#tl-filter-current-templates' ).is( ':checked' ) ? 1 : 0,
					post_id: TVE.CONST.post_id,
					_key: window.tve_leads_page_data._key
				};

				TVE.main.overlay();
				TVE.ajax( 'save_post_external', 'post', data )
				   .fail( function () {
					   TVE.utils.message( 'Error fetching templates!', 'error' );
				   } ).done( function ( response ) {
					var success = response.success || response.main_page_content || false;
					if ( ! success ) {
						TVE.page_message( response.message, true );
						return TVE.main.overlay( 'close' );
					}
					self.setTemplates( response.templates );
					TVE.main.overlay( 'close' );
					resolve( self.templates )
				} )
			}
		} );
	},

	/**
	 * Filter templates by searchbar input
	 * @param templates
	 * @returns {*}
	 */
	filterTemplates: function ( templates ) {
		const searchFilter = this.$( '.tve-c-modal-search-input' ).val().trim().toLowerCase();
		return templates.filter( function ( item ) {
			var allow = true;
			if ( allow && searchFilter && ! item.attributes.name.toLowerCase().includes( searchFilter ) ) {
				allow = false;
			}
			return allow;
		} );
	},

	/**
	 * Append the templates
	 * @param templates
	 */
	setItems: function ( templates ) {
		var promises = [];
		var self = this,
			tpl = TVE.tpl( 'templates/item' );

		if ( TVE.CONST.tl_templates_error.error ) {
			this.$error.fadeIn( 200 ).html( TVE.tpl( 'inline-error' )( {error_message: TVE.CONST.tl_templates_error.error} ) );
		}
		if ( templates.length === 0 ) {
			self.$template_items.append( 'No templates found' );
		} else {
			templates.forEach( function ( item, index, list ) {
				const saved = ( self.templatesType === 'saved' );
				var thumb_sizes = item.get( 'thumb_sizes' );
				if ( saved && thumb_sizes && isNaN( parseInt( thumb_sizes.h ) ) ) {
					var newPromise = new Promise( function ( resolve ) {
						var currentImg = new Image();

						currentImg.src = item.get( 'thumbnail' );
						currentImg.onload = function () {
							item.set( {thumb_sizes: {w: currentImg.width, h: currentImg.height}} );
							self.$template_items.append( tpl( {item: item, saved: saved} ) )
							resolve()
						};
					} );
					promises.push( newPromise );
				} else {
					self.$template_items.append( tpl( {item: item, saved: saved} ) )
				}
			} )
		}

		return Promise.allSettled( promises );
	},

	/**
	 * Append the templates to a global variable
	 * @param templates
	 */
	setTemplates: function ( templates ) {
		this.templates = new Backbone.Collection( templates );
	},

	/**
	 * Triggers when a template is selected
	 * @param event
	 */
	selectTemplate: function ( event ) {
		var $currentTemplate = jQuery( event.currentTarget );
		this.$el.find( '.cloud-item.active' ).removeClass( 'active' );
		this.$el.find( '.modal-title-w-options.active' ).removeClass( 'active' );

		var $modalHeight = jQuery( '.tcb-modal-tl-templates' ).height();
		this.$modalContent.css( 'height', $modalHeight - 82 );

		$currentTemplate.addClass( 'active' );
		$currentTemplate.siblings().addClass( 'active' ); /* Select the template title as well */

		/* Display warning only when a template is set already */
		if ( window.tve_leads_page_data.has_content || this.templateWasSelected ) {
			this.$warning.css( 'display', 'block' );
		}

		this.$footer.css( 'display', 'block' );
	},

	/**
	 * Open the options tooltip
	 * @param event
	 */
	openOptionsTooltip: function ( event ) {
		this.closeOptionsTooltip();
		jQuery( event.target ).closest( '.modal-title-w-options' ).toggleClass( 'tooltip-open' );
		this.tooltipOpen = true;
	},

	/**
	 * Close the current options tooltip
	 */
	closeOptionsTooltip: function () {
		if ( this.tooltipOpen ) {
			this.$el.find( '.tooltip-open' ).removeClass( 'tooltip-open' );
			this.tooltipOpen = false;
		}
	},

	/**
	 * Close the options tooltip when clicking anywhere else
	 */
	initClickHandler: function () {
		var self = this;
		this.$el.on( 'mousedown', function ( event ) {
				const $target = jQuery( event.target );
				if ( self.tooltipOpen && $target.closest( '.modal-title-w-options, .tcb-dropdown-dots' ).length === 0 ) {
					self.closeOptionsTooltip();
				}
			}
		);
	},

	/**
	 * Apply masonry on the current template list
	 */
	applyMasonry: function () {
		const dynamicImageWidth = parseInt( this.$el.find( '.cloud-template-item' ).outerWidth( true ) );

		this.$el.find( 'img' ).each( function () {
			const imageRatio = jQuery( this )[ 0 ].getAttribute( 'data-ratio' );
			if ( imageRatio ) {
				jQuery( this ).parent().css( 'height', dynamicImageWidth * parseFloat( imageRatio ) );
			}
		} );

		if ( this.$template_items.data( 'masonry' ) ) {
			this.$template_items.data( 'masonry' ).destroy()
		}

		const self = this;

		setTimeout( function () {
			self.$template_items.masonry();
		}, 0 );
	},

	/**
	 * Display the delete confirmation view
	 *
	 * @param event
	 */
	deleteConfirmation: function ( event ) {
		var $templateItem = jQuery( event.currentTarget ).closest( '.cloud-template-item' );

		$templateItem.find( '.cb-template-wrapper' ).hide();
		var $deleteNotice = $templateItem.find( '.symbol-delete-notice' );

		$deleteNotice.css( 'display', 'flex' );
		this.closeOptionsTooltip();
	},

	/**
	 * Cancel a delete action and return to the default state
	 *
	 * @param event
	 */
	hideDelete: function ( event ) {
		var $templateItem = jQuery( event.currentTarget ).closest( '.cloud-item' );
		$templateItem.find( '.cb-template-wrapper' ).show();
		$templateItem.find( '.symbol-delete-notice' ).css( 'display', 'none' );
	},

	/**
	 * Delete a saved template
	 *
	 * @param event
	 */
	deleteSavedTemplate: function ( event ) {
		var $templateItem = jQuery( event.currentTarget ).closest( '.cloud-template-item' ),
			self = this,
			data = {
				external_action: window.tve_leads_page_data.tpl_action,
				route: 'delete',
				tpl: $templateItem.attr( 'data-id' ),
				post_id: TVE.CONST.post_id,
				_key: window.tve_leads_page_data._key
			};

		TVE.main.overlay();
		TVE.ajax( 'save_post_external', 'post', data ).done( function ( response ) {
			$templateItem.remove();
			self.applyMasonry();
			TVE.main.overlay( 'close' );
		} );
	},

	/**
	 * Apply the selected template
	 */
	applyTemplate: function () {
		this.templateWasSelected = true;
		var self = this,
			$template = this.$( '.cloud-item.active' );

		if ( $template.length <= 0 ) {
			return TVE.page_message( TVE.t.SelectTemplate, true, 5000 );
		}

		var id = $template.data( 'id' ),
			tpl_model = this.templates.findWhere( {id: id} );

		if ( ! ( tpl_model instanceof Backbone.Model ) ) {
			return TVE.page_message( 'Something is wrong here. Template model not found ', true );
		}

		var data = {
			tpl: tpl_model.get( 'key' ),
			external_action: window.tve_leads_page_data.tpl_action,
			post_id: TVE.CONST.post_id,
			_key: window.tve_leads_page_data._key,
			route: 'choose',
			cloud: tpl_model.get( 'cloud' ) || 0,
			multi_step: tpl_model.get( 'multi_step' ) || 0,
			form_type: tpl_model.get( 'form_type' ) || ''
		};

		TVE.main.overlay();

		if ( jQuery( '#tl-form-states' ).find( '.design-states' ).is( ':visible' ) ) {
			jQuery( '#tl-form-states' ).find( 'button.state-close' ).trigger( 'click' );
		}

		TVE.ajax( 'save_post_external', 'post', data )
		   .done( function ( response ) {
			   var success = response.success || response.main_page_content || false;
			   if ( ! success ) {
				   TVE.page_message( response.message, true );
				   return TVE.main.overlay( 'close' );
			   }
			   window.TL_Editor.state.insertResponse( response );

			   /**
			    * Fix form identifiers on template insert so we dont use the ones from cloud templates
			    */
			   TVE.Editor_Page.editor.find( TVE.identifier( 'lead_generation' ) ).each( function () {
				   TVE.regenerateFormIdentifier( TVE.inner_$( this ) );
			   } )

			   try {
				   /**
				    * Store the id of the variation to be available in TL Dashboard
				    */
				   localStorage.setItem( 'tve_add_content_variation', JSON.stringify( {
					   form_type_id: data.post_id,
					   variation_id: data._key
				   } ) );
			   } catch ( e ) {

			   }
			   self.close();
		   } );
	},
} );

/**
 * Modal for saving current template for later use
 */
window.TL_Editor.views.ModalTemplateSaving = TVE.modal.base.extend( {

	el: TVE.modal.get_element( 'tl-template-saving' ),

	after_initialize: function () {

		this.$el.addClass( 'medium' );
	},

	save: function () {

		var _name = this.$( 'input' ).val();

		if ( _name.length <= 0 ) {
			return TVE.page_message( TVE.t.tpl_name_required, true, 5000 );
		}

		var self = this,
			thumbnail = _name + TVE.CSS_Rule_Cache.generate_id( '' ),
			data = {
				external_action: window.tve_leads_page_data.tpl_action,
				route: 'save',
				post_id: TVE.CONST.post_id,
				_key: window.tve_leads_page_data._key,
				name: _name,
				thumbnail: thumbnail,
			};

		TVE.main.overlay();
		TVE.main.editor_settings.save( null, null, function () {
			TVE.ajax( 'save_post_external', 'post', data )
			   .done( function ( response ) {
				   var success = response.success || false;
				   if ( ! success ) {
					   TVE.page_message( response.message, true );
					   return TVE.main.overlay( 'close' );
				   }

				   window.TL_Editor.savePreview( thumbnail );
				   TVE.main.overlay( 'close' );
				   self.close();
				   TVE.page_message( response.message );
			   } );
		} );
		return this;
	}

} );

TVE.leads.LightboxStateAction = window.TCB_AnimViews.ThriveLightbox.extend( {
	reinit: function () {
		if ( ! this.options.actions[ this.key ] ) {
			this.$el.closest( '.action-item' ).hide();
		} else {
			this.$el.closest( '.action-item' ).show();
			this.list.set_items( this.options.actions[ this.key ].options );
		}
	},
	controls_init: function () {
		this.list = new TVE.Views.Controls.List( {
			el: this.$( '.state-list' )[ 0 ],
			items: this.options.actions[ this.key ].options
		} );
		this.event_trigger = 'click';
		this.$animation = this.$( '#lb-animation' );
		if ( TVE.CONST.options.animation.actions.tl_state_lightbox ) {
			this.$animation.show();
			_.each( TVE.CONST.options.animation.actions.tl_state_lightbox.animations, function ( v, k ) {
				this.$animation.append( '<option value="' + k + '">' + v + '</option>' );
			}, this );
		} else {
			this.$animation.hide();
		}
	},
	set_model: function ( model ) {
		this.model = typeof model !== 'undefined' ? model : new Backbone.Model( {'config': {}} );
		this.list.set_value( parseInt( this.model.get( 'config' ).s || 0 ) );
		this.$animation.val( this.model.get( 'config' ).anim || 'instant' );

		return this;
	},
	validate: function () {
		return this.list.get_value() ? true : TVE.page_message( TVE.t.state_missing, true );
	},
	apply_settings: function ( $element ) {
		if ( ! this.validate() ) {
			return false;
		}
		this.model.set( {
			a: this.key,
			t: this.event_trigger,
			config: {
				anim: this.$animation.val() || 'instant',
				s: this.list.get_value()
			}
		} );
		return true;
	}
} );
TVE.leads.StateSwitchAction = TVE.leads.LightboxStateAction.extend( {
	controls_init: function () {
		TVE.leads.LightboxStateAction.prototype.controls_init.apply( this, arguments );

		this.$animation.hide();
	}
} );

( function ( $ ) {

	TVE.add_filter( 'tve_form_submit_options', function ( options ) {

		if ( ! _.findWhere( options, {key: 'state'} ) ) {
			options.push( {
				key: 'state',
				label: window.tve_leads_page_data.L.switch_state,
				icon: 'state'
			} );
		}

		return options;
	} );

	/**
	 * document ready
	 */
	$( function () {
		var state_manager = new TVE.leads.StateManager( {
			el: $( '#tl-form-states' )[ 0 ]
		} );

		TVE.add_filter( 'editor_loaded_callback', window.TL_Editor.tcb_editor_page_loaded );

		TVE.add_filter( 'before_editor_events', window.TL_Editor.before_editor_loaded );

		/**
		 * hook into JS filters for TCB
		 */
		TVE.add_filter( 'tcb_insert_content_template', window.TL_Editor.pre_process_content_template );

		TVE.main.on( 'animation_update', function ( $element, event_manager ) {
			var config = event_manager.read( $element );
			$.each( config, function ( i, evt ) {
				var animation_type = evt.a;
				if ( animation_type !== 'thrive_leads_form_close' ) {
					var trigger_id = parseInt( evt.config.s ),
						trigger = evt.t,
						actions = TVE.Components.animation.options.actions;
					if ( trigger === 'click' ) {
						var action_lightbox = actions[ animation_type ].options,
							arr = [];
						if ( action_lightbox.length ) {
							$.each( action_lightbox, function ( i, opt ) {
								if ( opt.id === trigger_id ) {
									arr.push( opt.id );
								}
							} );
							if ( ! arr.length ) {
								event_manager.remove( $element, trigger );
							}
						} else {
							event_manager.remove( $element, trigger );
						}
					}
				}
			} );
		} );
	} );

	TVE.leads.StateManager = TVE.Views.Base.base_view.extend( {
		after_initialize: function () {
			this.dom = {
				btn: this.$( '.states-button-container' )
			};
			window.TL_Editor.state.fixed_height();
		},
		expand: function () {
			clearTimeout( this.hide_timeout );
			this.$( '.design-states' ).removeClass( 'hide-from-view' );
		},
		collapse: function ( e ) {
			this.hide_timeout = setTimeout( this.bind( function () {
				this.$( '.design-states' ).addClass( 'hide-from-view' );
			} ), 200 );
		},
		cancel_hide: function () {
			clearTimeout( this.hide_timeout );
		},
		toggle_add: function ( e ) {
			$( e.currentTarget ).toggleClass( 'tl-multistep-open' );
		},
		add: function ( e ) {
			var link = e.currentTarget;
			if ( link.getAttribute( 'data-subscribed' ) ) {
				alert( window.tve_leads_page_data.L.only_one_subscribed );
				return;
			}
			this.collapse();
			TVE.main.overlay();
			TVE.Editor_Page.save( false, function () {
				TVE.KEEP_OVERLAY = true;
				window.TL_Editor.state.ajax( {
					custom_action: 'add',
					state: link.getAttribute( 'data-state' )
				} ).done( function ( response ) {
					window.TL_Editor.state.insertResponse( response );
					TVE.Components.lead_generation.removeSettingsId( TVE.Editor_Page.editor ); //remove old LG settings id
				} );
			} ); // passed in callback function to skip the closing of overlay
		},
		select: function ( e ) {
			const variationId = e.currentTarget.getAttribute( 'data-id' ),
				$previewButton = TVE.$( '.preview-content' ),
				previewLink = $previewButton.attr( 'href' );
			this.collapse();
			TVE.main.overlay();
			TVE.Editor_Page.save( false, function () {
				TVE.KEEP_OVERLAY = true;
				window.TL_Editor.state.ajax( {
					custom_action: 'display',
					id: variationId
				} ).done( window.TL_Editor.state.insertResponse );
			} ); // passed in callback function to skip the closing of overlay
			/**
			 * Replace preview _key value with current state id so the preview is updated too
			 */
			$previewButton.attr( 'href', this.updateUrlParameter( previewLink, '_key', variationId ) );
		},
		/**
		 * Replace a specific url param with a new value
		 * @param url - current url
		 * @param param - param to be replaced
		 * @param value - new value
		 * @returns {String}
		 */
		updateUrlParameter: function ( url, param, value ) {
			var regex = new RegExp( '(' + param + '=)[^\&]+' );
			return url.replace( regex, '$1' + value );
		},
		visibility: function ( e ) {
			var $link = $( e.currentTarget );
			if ( ! $link.parents( 'li' ).hasClass( 'lightbox-step-active' ) || typeof $link.attr( 'data-visible' ) === 'undefined' ) {
				return;
			}
			TVE.main.overlay();
			this.collapse();
			window.TL_Editor.state.ajax( {
				custom_action: 'visibility',
				visible: $link.attr( 'data-visible' )
			} ).done( function ( response ) {
				TVE.page_message( response.message );
				window.TL_Editor.state.insertResponse( response );
			} );

			return false;
		},
		duplicate: function ( e, link ) {
			if ( link.getAttribute( 'data-state' ) === 'already_subscribed' ) {
				alert( window.tve_leads_page_data.L.only_one_subscribed );
				return;
			}
			this.collapse();
			TVE.main.overlay();
			TVE.Editor_Page.save( false, function () {
				TVE.KEEP_OVERLAY = true;
				window.TL_Editor.state.ajax( {
					custom_action: 'duplicate',
					id: link.getAttribute( 'data-id' )
				} ).done( function ( response ) {
					window.TL_Editor.state.insertResponse( response );
					TVE.Components.lead_generation.removeSettingsId( TVE.Editor_Page.editor ); //remove old LG settings id
				} );
			} );

			return false;
		},
		remove: function ( e, link ) {
			if ( ! confirm( window.tve_leads_page_data.L.confirm_state_delete ) ) {
				return false;
			}
			this.collapse();
			TVE.main.overlay();
			window.TL_Editor.state.ajax( {
				custom_action: 'delete',
				id: link.getAttribute( 'data-id' )
			} ).done( function ( response ) {
				TVE.page_message( window.tve_leads_page_data.L.state_deleted );
				window.TL_Editor.state.insertResponse( response );
			} );

			return false;
		}
	} );
	/**
	 * handles all user interactions related to form states
	 */
	window.TL_Editor.state = {
		fixed_height: function () {
			var _state_content = $( '.fix-height-states' );
			//Test is scrollbar() is a function. Should be loaded from Architect
			if ( typeof _state_content.scrollbar === 'function' ) {
				_state_content.scrollbar();
			} else {
				_state_content.css( 'overflow-y', 'auto' );
			}
		},
		insertResponse: function ( response ) {
			if ( ! response ) {
				TVE.page_message( 'Something went wrong', true );
			}

			if ( TVE.main && TVE.main.$cpanel && response.preview_link.length ) {
				TVE.main.$cpanel.find( '.preview-content' ).attr( 'href', decodeURIComponent( response.preview_link ) );
			}

			window.TL_Editor_Page.handle_state_response( response );
			$( '.design-states' ).replaceWith( response.state_bar );
			window.TL_Editor.state.fixed_height();

			if ( response.tve_path_params.tl_templates ) {
				modal_templates.setTemplates( TVE.CONST.tl_templates );
			}

			/**
			 * Any element configuration that needs updating
			 */
			if ( response.animation_options ) {
				TVE.Components.animation.options = response.animation_options;
				TVE.Components.animation.reinit();
				window.TL_Editor.FLAG_RE_RENDER_EVENTS = true;
			}

			const $totalStates = $( '.total_states' );
			if ( response.tve_leads_page_data.states.length >= 2 ) {
				$totalStates.show();
				$totalStates.html( response.tve_leads_page_data.states.length - 1 );
			} else {
				$totalStates.hide();
			}

			setTimeout( function () {
				TVE.main.overlay( 'close' );
			}, 1 );
		},
		ajax: function ( data ) {
			TVE.Editor_Page.blur();
			data._key = window.tve_leads_page_data._key;
			data.post_id = window.tve_leads_page_data.post_id;
			data.active_state = window.tve_leads_page_data._key;
			data.external_action = window.tve_leads_page_data.state_action;

			return TVE.ajax( 'save_post_external', 'post', data );
		}
	};

	window.TL_Editor.tcbEditorSetSelector = function () {
		/**
		 * lightbox state has the TL element as a parent of the editor
		 */
		if ( ! window.tve_leads_page_data.form_type.includes( 'lightbox' ) ) {
			const varData = _.findWhere( window.tve_leads_page_data.states, {key: window.tve_leads_page_data._key} );
			TVE.CONST.editor_selector = varData.form_state === 'lightbox' ? 'body' : '';
		}
	}

	window.TL_Editor.savePreview = function ( designName ) {
		var saveCallback = function ( imgData ) {

			var form = new FormData();
			if ( imgData ) {
				form.append( 'preview_file', imgData, designName + '.png' );
			}

			form.append( 'route', 'save_thumbnail' );
			form.append( 'custom', 'save_thumbnail' );
			form.append( 'action', window.tve_leads_page_data.tpl_action );
			form.append( 'security', window.tve_leads_page_data.security );
			form.append( '_key', window.tve_leads_page_data._key );
			form.append( 'post_id', window.tve_leads_page_data.post_id );
			form.append( 'file_name', designName );

			$.ajax( {
				type: 'POST',
				url: window.tve_leads_page_data.ajaxurl,
				data: form,
				processData: false,
				contentType: false,
			} );
		}
		TVE.Editor_Page.blur();
		TVE.Editor_Page.editor.find( '.on_hover' ).removeClass( 'on_hover' );
		TVE.inner_$( '.preview-cloned-content' ).remove();

		var $content = TVE.inner_$( '.thrv-leads-screen-filler, .thrv-leads-slide-in' );
		if ( $content.length === 0 ) {
			$content = TVE.Editor_Page.editor
		}
		var $contentToPreview = TVE.getContentToPreview( $content );
		TVE.generateElementPreview( $contentToPreview, saveCallback, {}, true );
	}

	/**
	 * Callback for 'editor_loaded_callback' filter thrown on DOMReady in TCB
	 */
	window.TL_Editor.tcb_editor_page_loaded = function () {
		TVE.StorageManager.unset( 'tl_design-' + window.tve_leads_page_data.post_id );

		window.TL_Editor.tcbEditorSetSelector();

		modal_templates = new window.TL_Editor.views.ModalTemplates();

		/**
		 * event listener for setting submit options on LG Element
		 * @param $el lg component element
		 * @param model Backbone.model
		 * @param option - submit option selected
		 */
		TVE.main.on( 'lgRenderOptionForm', function ( $el, model, option ) {
			if ( option !== 'state' ) {
				return;
			}
			var form_template = TVE.tpl( 'lead-generation/switch-states-form' ),
				$html = $( form_template() ),
				$select = $html.find( 'select' ),
				prefix = '__TCB_EVENT_[',
				suffix = ']_TNEVE_BCT__',
				updateState = function ( id ) {

					var event_action = 'tl_state_switch',
						state_id = parseInt( id );
					model.set( '_state', state_id );
					//decide if the state is a lightbox
					$( window.tve_leads_page_data.states ).each( function ( index, state ) {
						if ( parseInt( state_id ) === parseInt( state.key ) && state.form_state === 'lightbox' ) {
							event_action = 'tl_state_lightbox';
						}
					} );

					var event_config = {
						t: 'click',
						a: event_action,
						elementType: 'a',
						config: {
							s: state_id
						}
					};

					event_config = prefix + JSON.stringify( event_config ) + suffix;

					//write the event config html
					TVE.ActiveElement.find( '.tve-switch-state-trigger' ).remove();
					var $a = $( '<a href="javascript:void(0)" style="display: none;" class="tve-switch-state-trigger tve_evt_manager_listen tve_et_click"></a>' );
					$a.attr( 'data-tcb-events', event_config );
					TVE.Components.lead_generation.getWrapper( 'form' ).append( $a );
				};

			/**
			 * onChange set the state value on model to be written in HTML later on
			 */
			$select.on( 'change', function ( event ) {
				updateState( event.currentTarget.value );
			} );
			/**
			 * append states top select element
			 */
			$.each( window.tve_leads_page_data.states, function ( index, state ) {

				if ( ( parseInt( state.key ) === parseInt( window.tve_leads_page_data._key ) ) || state.form_state === 'already_subscribed' ) {
					return;
				}
				var $option = $( '<option value="' + state.key + '">' + state.state_name + '</option>' );

				$select.append( $option );
			} );
			if ( TVE.ActiveElement.find( '.tve-switch-state-trigger' ).length ) {
				var config = JSON.parse( TVE.ActiveElement.find( '.tve-switch-state-trigger' ).attr( 'data-tcb-events' ).replace( prefix, '' ).replace( suffix, '' ) ).config;
				$select.val( config.s );
				TVE.ActiveElement.data( 'lg' ).set( '_state', config.s );
			} else {
				updateState( $select.val() );
			}
			$el.find( '#lg-state' ).html( $html ).removeClass( 'tcb-hidden' );
		} );

		TVE.Components.lead_generation.on( 'tcb_lg_manage_submit_options', function ( modal, lead_generation_view ) {
			lead_generation_view._write._form_type = function () {

				//todo: make sure the input does not exist; unused for the moment
				TVE.Components.lead_generation.getWrapper( 'form' ).append( TVE.Components.lead_generation.generateHiddenInput( {
					name: '_form_type',
					value: window.tve_leads_page_data.form_type
				} ) );
			};
		} );

		/**
		 * Open templates modal - choose/change the template for the variation
		 */
		$( TVE.main ).on( 'tcb.open_templates_picker', function ( event ) {
			event.preventDefault();
			modal_templates.open( {} );

			return false;
		} );

		/**
		 * if variation has no content/template set
		 */
		if ( ! window.tve_leads_page_data.has_content ) {
			modal_templates.open( {
				dismissible: false
			} );
		}

		/**
		 * reset to default current content
		 */
		TVE.main.sidebar_extra.tl_template_reset = function () {

			if ( ! confirm( window.tve_leads_page_data.L.confirm_tpl_reset ) ) {
				return;
			}

			TVE.Editor_Page.blur();

			TVE.main.sidebar_extra.hide_drawers();

			var data = {
				_key: window.tve_leads_page_data._key,
				post_id: TVE.CONST.post_id,
				external_action: window.tve_leads_page_data.tpl_action,
				route: 'reset'
			};

			TVE.main.overlay();

			TVE.ajax( 'save_post_external', 'post', data )
			   .done( window.TL_Editor.state.insertResponse );
		};

		/**
		 * Save current template for later use
		 */
		TVE.main.sidebar_extra.tl_template_save = function () {
			if ( this.modal instanceof Backbone.View ) {
				return this.modal.open();
			}

			this.modal = new window.TL_Editor.views.ModalTemplateSaving();
			this.modal.open();
		};

		/**
		 * Do not open thrive lightboxes from links
		 */
		TVE.add_filter( 'link_search_lightbox', function () {
			return '';
		} );

		/**
		 * For lightboxes and 2 steps, we need to store some meta-data for the variation
		 */
		TVE.add_filter( 'tcb_save_post_data_before', function ( data ) {

			var sizes = {
				m: '600px',
				t: '1023px',
				d: '1366px'
			}

			data[ 'form-height' ] = {};
			var $container = TVE.Editor_Page.editor.find( '.tve_editor_main_content' ),
				initialWidth = TVE.main.$frame.width();

			if ( $container.length === 0 ) {
				$container = TVE.Editor_Page.editor;
			}

			/* fox for margin collapse */
			$container.css( 'border', '1px solid transparent' )

			/* save form height so we can prepare a placeholder for it. */
			for ( var device in sizes ) {
				TVE.main.$frame.width( sizes[ device ] );
				data[ 'form-height' ][ device ] = $container.outerHeight( true );
			}

			$container.css( 'border', '' )

			TVE.main.$frame.width( initialWidth );

			/**
			 * Only if a lightbox is being edited
			 */
			if ( TVE.inner_$( '.tve_p_lb_content' ).length ) {
				/**
				 * remove old attributes from the globals config for the lightbox
				 */
				var globals = TVE.CONST.tve_globals,
					$lb = TVE.inner_$( '.tve_p_lb_content' ),
					css;

				/**
				 * Content CSS attr
				 */
				if ( css = $lb.attr( 'data-css' ) ) {
					globals.content_css = css;
				}
			}

			return data;
		} );

		TVE.add_action( 'tve.save_post.success', function () {
			TVE.StorageManager.set( 'tl_design-' + window.tve_leads_page_data.post_id, true );
		} );

		TVE.add_action( 'component.update.layout.tl-slide-in', function ( component ) {
			component.disable_extra_controls( [ 'right', 'left' ].map( function ( side ) {
				return 'margin-' + side
			} ) );
		} );
	};

	/**
	 * Callback for 'before_editor_events' filter thrown on DOMReady in TCB
	 */
	window.TL_Editor.before_editor_loaded = function () {
		var EDITOR_INSTANCE = 1,
			TL_FORM_EVENTS = [
				'thrive_leads_form_close',
				'tl_state_lightbox',
				'tl_state_switch'
			];
		/**
		 * Add extra Event Manger options in the insert link functionality - froala
		 */
		TVE.add_filter( 'tcb_froala_config', function () {
			return {
				linkEventActions: {
					getHtml: function () {
						var opts = TVE.Components.animation.options.actions;
						var actions = {
							thrive_leads_form_close: opts.thrive_leads_form_close
						};
						if ( opts.tl_state_switch && opts.tl_state_switch.options.length ) {
							actions.tl_state_switch = opts.tl_state_switch;
						}
						if ( opts.tl_state_lightbox && opts.tl_state_lightbox.options.length ) {
							actions.tl_state_lightbox = opts.tl_state_lightbox;
						}

						return TVE.tpl( 'froala-leads-states' )( {
							actions: actions,
							current_id: ++ EDITOR_INSTANCE
						} );
					},
					bindEvents: function ( $popup ) {
						$popup.on( 'change', '.fr-extra-action', function ( e ) {
							$popup.find( '.tl-action-config' ).hide();
							if ( ! this.checked ) {
								$popup.find( '.fr-link-atts,.fr-link-url' ).show();
							} else {
								$popup.find( '.tl-action-opts-' + this.getAttribute( 'data-key' ) ).show();
								$popup.find( '.fr-link-atts,.fr-link-url' ).hide();
								$popup.find( '.fr-extra-action' ).not( this ).prop( 'checked', false );
							}
						} );
					},
					hasSelected: function ( $popup ) {
						return $popup.find( '.fr-extra-action:checked' ).length;
					},
					getEventConfig: function ( $popup ) {
						var event = {};
						event.a = $popup.find( '.fr-extra-action:checked' ).attr( 'data-key' );
						event.t = 'click';
						event.config = {
							s: $popup.find( '.tl-action-opts-' + event.a + ' select[name="s"]' ).val(),
							anim: $popup.find( '.tl-action-opts-' + event.a + ' select[name="a"]' ).val()
						};

						return event;
					},
					reset: function ( $popup ) {
						if ( window.TL_Editor.FLAG_RE_RENDER_EVENTS ) {
							/* re-render Thrive Leads action options inside froala link editing popup */
							$popup.find( '.tl-link-actions' ).replaceWith( this.getHtml() );

							delete window.TL_Editor.FLAG_RE_RENDER_EVENTS;
						}

						$popup.find( '.fr-extra-action' ).prop( 'checked', false );
						$popup.find( '.fr-link-atts,.fr-link-url' ).show();
						$popup.find( '.tl-action-config' ).hide();
					},
					updateFromLink: function ( $link, $popup ) {
						var leads_event_found = false;
						this.reset( $popup );

						if ( $link.hasClass( 'tve_evt_manager_listen' ) ) {
							var evt = TVE.EventManager.get( $link, 'click' );
							if ( evt && $.inArray( evt.a, TL_FORM_EVENTS ) !== - 1 ) {
								$popup.find( '.fr-extra-action[data-key="' + evt.a + '"]' ).prop( 'checked', true ).trigger( 'change' );
								$popup.find( '.tl-action-opts-' + evt.a + ' select[name="s"]' ).val( evt.config.s );
								leads_event_found = true;
								$popup.find( '.tl-action-opts-' + evt.a + ' select[name="a"]' ).val( evt.config.anim || 'instant' );
							}
						}

						return leads_event_found;
					}
				}
			};
		} );

		var CustomHTML = TVE.CustomHTML;

		TVE.CustomHTML = TVE.CustomHTML.extend( {
			append_extra_settings: function () {
				this.$( '.extra-settings' ).remove();
			},
			after_initialize: function () {
				CustomHTML.prototype.after_initialize.apply( this, arguments );
				this.$( '.tcb-modal-title' ).after( TVE.tpl( 'custom-html-options' )() );

				this.$lazy_load = this.$( '#custom-html-lazy-load' );
			},
			before_open: function () {
				CustomHTML.prototype.before_open.apply( this, arguments );
				this.$( '#tl-custom-html-opts' ).toggle( ! window.tve_leads_page_data.is_default_state );
			},
			is_lazy_load: function () {
				return ! window.tve_leads_page_data.is_default_state && this.$lazy_load.val() === 'lazy';
			},
			/**
			 * If "Lazy load" content is active, wrap everything in a <script type="text/template"> node
			 *
			 * @param {String} html
			 *
			 * @return {String} html
			 */
			prepare_content_for_save: function ( html ) {
				if ( this.is_lazy_load() ) {
					html = '<script type="text/template" style="display: none" class="tcb-lazyload-template">' + html + '</script>';
				}

				return html;
			},
			/**
			 * Check if html is a single <script type="text/template"> node. If yes, it means the script should be executed only when the state is displayed
			 *
			 * @return {String}
			 */
			prepare_content_for_load: function () {
				var $children = TVE.ActiveElement.children().first(),
					html;

				if ( $children.length === 1 && $children.is( 'script.tcb-lazyload-template[type="text/template"]' ) ) {
					this.$lazy_load.val( 'lazy' );
					html = $children.html();
				} else {
					html = TVE.ActiveElement.html();
				}

				return html;
			}
		} );
	};
} )( jQuery );
