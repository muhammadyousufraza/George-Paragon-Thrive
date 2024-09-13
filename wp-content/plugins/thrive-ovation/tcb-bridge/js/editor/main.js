const TVE = window.TVE || {};
let isBlockType = false;
( function ( $ ) {
	const tvoPostTypes = [ 'tvo_display', 'tvo_capture', 'tvo_capture_post', 'tvo_display_post' ],
		isTvoPostType = ( postType = TVE.CONST.post.post_type ) => tvoPostTypes.includes( postType ),
		elementHasOvationConnection = $element => {
			let hasConnection = $element.is( '.thrv_lead_generation' );

			if ( hasConnection ) {
				try {
					const settings = JSON.parse( $element.attr( 'data-form-settings' ).replace( /__TCB_FORM__/g, '' ) );

					hasConnection = typeof settings?.apis?.ovation !== 'undefined';
				} catch ( e ) {
					hasConnection = false;
				}
			}

			return hasConnection;
		},
		toggleSidebarElements = () => {
			TVE.main.sidebar_toggle_elements( Object.keys( TVE.Elements ), false );

			TVE.main.sidebar_toggle_elements( [ 'text', 'image', 'button', 'columns', 'section', 'contentbox', 'logo', 'icon', 'divider' ], true );
		};

	/**
	 * On TCB Main Ready
	 */
	$( window ).on( 'tcb_main_ready', () => {
		/* entry point for the new logic of Display Testimonials */
		require( './display-testimonials/_includes' );

		if ( isTvoPostType() ) {
			isBlockType = true;
			TVE.StorageManager.unset( 'tvo_block-' + TVE.CONST.post.ID );
		}

		TVE.add_filter( 'tve.allowed.empty.posts.type', postTypes => [ ...postTypes, ...tvoPostTypes ] );

		TVE.main.on( 'device_change', function () {
			TVE.inner_$( '.thrlider-slider' ).each( function () {
				TVE.inner_$( this ).parents( '[id*="thrlider"]' ).thrlider( 'redraw' );
			} );
		} );

		$( TVE.main ).on( 'tve-api-options-ovation.tcb', function ( event, params ) {
			const data = TVE.get_inputs_value( params.$container, '.tve-api-extra' );
			data.preventDelete = TVE.ActiveElement?.attr( 'data-ct' )?.includes( 'capture_testimonials' );
			data.preventDeleteMessage = 'Thrive Ovation connection is mandatory for Ovation Capture forms';

			params.api.setConfig( data );
			params.api.set( data );
		} );

		TVE.add_filter( 'lg_hide_apis.lead_generation', ( hideSelect, api ) => {
			if ( api === 'ovation' ) {
				hideSelect = true;
			}

			return hideSelect;
		} );

		TVE.main.on( 'tcb_auth_login', function ( data ) {
			/* update the nonce after the user logs in back */
			TVO_Front.nonce = data.rest_nonce;
		} );

		TVE.add_action( 'tve.save_post.success', function () {
			TVE.StorageManager.set( 'tvo_block-' + TVE.CONST.post.ID, true );
		} );

		TVE.add_filter( 'editor_loaded_callback', function () {
			if ( isBlockType ) {
				toggleSidebarElements();

				TVE.Editor_Page.focus_element( TVE.Editor_Page.editor.children().first() );
			}
		} );

		TVE.add_filter( 'tve.lg_fields.mapping_fields_api', ( defaultApi, availableApis ) => {
			if ( 'ovation' in availableApis ) {
				defaultApi = 'ovation';
			}

			return defaultApi;
		} );

		TVE.add_filter( 'tve.lg_form.validated_apis', apis => [ ...apis, 'ovation' ] );

		TVE.add_filter( 'tve.cloud_templates.modal', ( modal, $element ) => {
			if ( elementHasOvationConnection( $element ) ) {
				modal = 'cloud-templates';
			}

			return modal;
		}, 11 );

		TVE.add_filter( `tve.cloud_templates.modal.options`, options => {
			if ( elementHasOvationConnection( options.element ) ) {
				options.type = 'capture_testimonials_v2';
			}

			return options;
		} );

		TVE.add_filter( 'tcb.lg_api_model.ovation', apis => {
			return apis.splice( apis.indexOf( 'ovation' ), 1 );
		} );

		TVE.add_filter( 'tve.lg_field.set_type', ( setType, type ) => {
			if ( setType && type === 'question' && TVE.ActiveElement && elementHasOvationConnection( TVE.ActiveElement ) ) {
				setType = false;
			}
			return setType;
		} );

		TVE.add_filter( 'tve.lg_fields.default_field_name', ( name, field, count ) => {
			if ( field === 'question' && TVE.ActiveElement && elementHasOvationConnection( TVE.ActiveElement ) ) {
				name = `Testimonial ${count > 1 ? count : ''}`.trim();
			}

			return name;
		} );

		TVE.add_filter( 'tcb.element_selected', $element => {
			if ( $element.is( TVE.identifier( 'capture_testimonials_v2' ) ) ) {
				$element.attr( 'data-element-name', 'Capture testimonial' );
			}
			return $element;
		}, 11 );

		TVE.add_filter( 'tcb.lg_field.stripped_name', strippedName => {
			if ( strippedName === 'question' && TVE.ActiveElement && elementHasOvationConnection( TVE.ActiveElement ) ) {
				strippedName = 'textarea';
			}

			return strippedName;
		} );

		/**
		 * Add Contact Form Elements for witch we should not display the delete button
		 */
		TVE.add_filter( 'selectors_no_icons', function ( selectors ) {
			if ( isTvoPostType() ) {
				selectors += `,${TVE.identifier( 'capture_testimonials_v2' )},${TVE.identifier( 'display_testimonials' )},.tcb-elem-placeholder`;
			}

			return selectors;
		} );

		TVE.add_filter( 'tcb.elem.type.info', ( type, $element ) => {
			if ( $element.is( TVE.identifier( 'capture_testimonials_v2' ) ) ) {
				type = 'capture_testimonials_v2';
			}

			return type;
		} );

		TVE.add_filter( 'tcb.cloud_template_download_params', params => {
			if ( params?.data?.type === 'display_testimonials' ) {
				params.data.query = TVE.PostList.utils.readQueryFromElement();
			}

			return params;
		} );

		TVE.add_filter( 'tcb.cloud_templates.element_name', name => {
			if ( TVE.ActiveElement?.is( TVE.identifier( 'capture_testimonials_v2' ) ) ) {
				name = 'Capture testimonial';
			}

			return name;
		} );

		TVE.add_filter( `tcb.cloud_template.display_testimonials.allow_cache`, '__return_false' );

		TVE.add_action( 'tcb_after_cloud_template_css_inserted', $element => {
			if ( elementHasOvationConnection( $element ) ) {
				$element.addClass( TVE.identifier( 'capture_testimonials_v2' ).replace( '.', '' ) );
			}
		} );

		TVE.add_action( 'tcb.edit_mode.after_exit', $element => {
			if ( isTvoPostType() && $element.is( `${TVE.identifier( 'capture_testimonials_v2' )},${TVE.identifier( 'display_testimonials' )}` ) ) {
				toggleSidebarElements();
			}
		} );

		TVE.add_filter( 'selectors_no_save', function ( selectors ) {
			selectors += ',.thrv_tvo_capture_testimonials, .thrv_tvo_display_testimonials';

			if ( isTvoPostType() ) {
				selectors += `,${TVE.identifier( 'capture_testimonials_v2' )},${TVE.identifier( 'display_testimonials' )}`;
			}

			return selectors;
		} );

		TVE.add_filter( 'tcb.form.input.labels', function ( labels ) {
			return {
				...labels, ...{
					role: 'Role',
					website_url: 'Website URL',
					title: 'Title ',
					question: 'Testimonial',
				},
			};
		} );

		TVE.add_filter( 'should_have_width_on_float', function ( list ) {
			list.push( 'thrv_tvo_capture_testimonials' );

			return list;
		} );

		TVE.TVO = {
			captureSettingsModal: {},
			captureTemplateModal: {},
			displaySettingsModal: {},
			displayTemplateModal: {},
			defaults: {
				display: {
					template: 'grid/default-template-grid',
					tags: [],
					testimonials: [],
					name: '',
					show_title: 1,
					show_role: 1,
					show_site: 0,
					type: 'display',
					max_testimonials: 0,
				},
				capture: {
					id: 0,
					template: 'default-template',
					type: 'capture',
					name_label: 'Full Name',
					title_label: 'Testimonial Title',
					email_label: 'Email',
					role_label: 'Role',
					website_url_label: 'Website URL',
					name_required: 1,
					title_required: 0,
					email_required: 0,
					role_required: 0,
					role_display: 0,
					title_display: 0,
					image_display: 1,
					reCaptcha_option: 0,
					on_success_option: 'message',
					on_success: 'Thanks for submitting your testimonial.',
					button_text: 'Submit',
					questions: [ 'What was your experience with our product like?' ],
					placeholders: [ '' ],
					questions_required: [ 1 ],
					tags: '',
					color_class: '',
				},
			}, config: {
				key: '__CONFIG_tvo_shortcode__', update( $element, key, value ) {
					const _data = this.get();
					_data[ key ] = value;
					this.save( _data );
				}, save( $config, data ) {
					if ( ! $config.hasClass( 'thrive-shortcode-config' ) ) {
						$config = $config.find( '.thrive-shortcode-config' );
					}

					if ( $config.length === 0 ) {
						return false;
					}

					$config.html( this.key + JSON.stringify( data ).replace( /\\"/g, '_tve_quote_' ) + this.key );
				}, get( $config ) {
					if ( ! $config.hasClass( 'thrive-shortcode-config' ) ) {
						$config = $config.find( '.thrive-shortcode-config' );
					}

					if ( $config.length === 0 ) {
						return false;
					}

					const replace = new RegExp( this.key, 'g' );

					return JSON.parse( $config.html().replace( /_tveutf8_/g, '\\u' ).replace( replace, '' ).replace( /\\\'/g, '\'' ).replace( /_tve_quote_/g, '\\"' ) );
				},
			},
			/**
			 * Return wrapper for a specific element inside another one.
			 * If the element does not exists it get created as a div child.
			 *
			 * @param  $element
			 * @param  selector
			 * @return {*}
			 */
			getWrapper( $element, selector ) {
				let $wrapper = $element.find( selector );

				if ( $wrapper.length === 0 ) {
					$wrapper = $( '<div>' ).attr( selector[ 0 ] === '.' ? 'class' : 'id', selector.substring( 1 ) ).appendTo( $element );
				}

				return $wrapper;
			},
			/**
			 * Get html template based on the shortcode config
			 *
			 * @param  $modal
			 * @param  config
			 */
			ajaxRender( $modal, config ) {
				$modal.$el.addClass( 'loading' );

				const self = this;

				$.ajax( {
					headers: {
						'X-WP-Nonce': TVO_Front.nonce,
					}, cache: false, data: {
						config,
					}, method: 'POST', url: TVO_Front.routes.shortcodes + '/render',
				} ).done( function ( response ) {
					self.updateElement( TVE.ActiveElement, response, $modal );
					TVE.ActiveElement.find( '[id*="thrlider"]' ).thrlider( 'redraw' );
				} ).fail( function ( response ) {
					console.error( response );
				} );
			},
			/**
			 * Update the element html and config
			 *
			 * @param  $element
			 * @param  data
			 * @param  $modal
			 */
			updateElement( $element, data, $modal ) {
				//save active element custom_css if we need to update them
				let styles = {};
				if ( $element.attr( 'data-update-styles' ) ) {
					styles = $.extend( {}, TVE.TVO.config.get( $element.find( '.thrive-shortcode-config' ) ).custom_css );
					$element.removeAttr( 'data-update-styles' );

					//we need to update the config by adding the styles
					data.config.custom_css = styles;
				}

				/* remove the placeholder and empty the element so we can add a new one */
				$element.removeClass( 'tcb-elem-placeholder' );
				$element.empty();

				/* save the config and hide it */
				TVE.TVO.config.save( TVE.TVO.getWrapper( $element, '.thrive-shortcode-config' ).hide(), data.config );

				TVE.TVO.getWrapper( $element, '.thrive-shortcode-html' ).html( data.html );

				/* add the previous styles to the $element's sub-elements*/
				for ( const selector in styles ) {
					$element.find( selector ).attr( 'data-css', styles[ selector ] );
				}

				/* update the new config */
				$element.data( 'config', data.config );

				setTimeout( function () {
					/* update the current component */
					$modal.component && $modal.component.update();
					/* reposition the icons if needed */
					TVE.Editor_Page.reposition_icons();
				}, 500 );

				$modal.close();
				$modal.$el.removeClass( 'loading' );
				TVE.Editor_Page.focus_element( $element );
			},
		};

		TVE.Views.Components.ovation_capture = TVE.Views.Base.component.extend( {
			controls_init( controls ) {
				this.template_modal = new TVE.TVO.captureTemplateModal( {
					el: TVE.modal.get_element( 'capture-testimonial-templates-lightbox' ),
				} );

				this.settings_modal = new TVE.TVO.captureSettingsModal( {
					el: TVE.modal.get_element( 'capture-form-settings-lightbox' ),
				} );

				controls.ButtonColor.getButtonStyle = function () {
					let style = '',
						template = TVE.ActiveElement.data( 'config' ).template;

					switch ( template ) {
						case 'default-template':
							style = 'background';
							break;

						case 'set1-template':
							style = 'background';
							break;
						case 'set2-template':
							style = 'border-color';
							break;
					}

					return style;
				};

				controls.ButtonColor.update = function () {
					this.setValue( this.applyTo().css( this.getButtonStyle() ) );
				};

				controls.ButtonColor.input = function ( color ) {
					const css = {},
						style = this.getButtonStyle(),
						$config = TVE.ActiveElement.find( '.thrive-shortcode-config' ),
						config = TVE.TVO.config.get( $config );

					css[ style ] = color + '!important';
					if ( style === 'border-color' ) {
						css.color = color + '!important';
					}

					this.applyTo().head_css( css );

					config.custom_css = config.custom_css || {};
					config.custom_css[ this.model.to ] = this.applyTo().data( 'css' );
					/* save the custom css id so we can apply it on display */
					TVE.TVO.config.save( $config, config );
				};
			}, placeholder_action( $element ) {
				$element.data( 'config', TVE.TVO.defaults.capture );

				this.template_modal.open( { template: TVE.TVO.defaults.capture.template, component: this } );
			}, before_update() {
				const $element = TVE.ActiveElement,
					config = TVE.TVO.config.get( $element ) || TVE.TVO.defaults.capture;

				$element.data( 'config', config );
			}, change_template() {
				const config = TVE.ActiveElement.data( 'config' );

				this.template_modal.open( { template: config.template, component: this } );
			}, form_settings() {
				const config = TVE.ActiveElement.data( 'config' );

				this.settings_modal.open( { config, component: this } );
			},

		} );

		TVE.TVO.captureTemplateModal = TVE.modal.base.extend( {
			before_open( options ) {
				/* if we have an template, mark it as selected */
				if ( options.template && options.template.length > 0 ) {
					this.$( '.tvo-template' ).each( function () {
						this.dataset.value === options.template ? this.classList.add( 'current' ) : this.classList.remove( 'current' );
					} );
				}

				this.component = options.component;
			}, save() {
				const config = TVE.ActiveElement.data( 'config' );

				config.template = this.$( '.tvo-template.current' ).data( 'value' );

				TVE.TVO.ajaxRender( this, config );
			}, select( event, dom ) {
				/* lol, just having some fun with js */
				dom.parentNode.childNodes.forEach( function ( element ) {
					element.nodeType !== Node.TEXT_NODE && element.classList.remove( 'current' );
				} );

				dom.classList.add( 'current' );
			},
		} );

		TVE.TVO.captureSettingsModal = TVE.modal.base.extend( {
			before_open( options ) {
				const config = options.config || {};

				/* apply settings for the shortcode from the config */
				this.applySettings( config );

				this.component = options.component;
			}, save() {
				TVE.TVO.ajaxRender( this, this.readSettings( TVE.ActiveElement.data( 'config' ) ) );
			}, applySettings( settings ) {
				this.$( '.tvo_config_field' ).each( function () {
					if ( this.type === 'checkbox' ) {
						this.checked = parseInt( settings[ this.name ] ) === 1;
					} else {
						this.value = settings[ this.name ];
					}
				} );

				this.$( '.tvo-question' ).remove();
				/* re-add all the questions and mark the first one as default */
				settings.questions.forEach( function ( question, index ) {
					$( '<div>', {
						class: 'tvo-row tvo-collapse tvo-question' + ( index === 0 ? ' tvo-default-question' : '' ), html: TVO_Front.tpl( 'new-question' )( { index, question, config: settings } ),
					} ).insertBefore( '.tvo-add-question' );
				} );

				this.$( '.tvo-all-tags' ).val( settings.tags ).trigger( 'change' );
			}, readSettings( settings ) {
				this.$( '.tvo_config_field' ).each( function () {
					settings[ this.name ] = ( this.type === 'checkbox' ? ( this.checked ? 1 : 0 ) : this.value );
				} );

				settings.tags = this.$( '.tvo-all-tags' ).val();

				settings.questions = [];
				settings.placeholders = [];
				settings.questions_required = [];

				this.$( '.tvo-question' ).each( function ( index ) {
					const $this = $( this );
					settings.questions[ index ] = $this.find( '.tvo-question-input' ).val();
					settings.placeholders[ index ] = $this.find( '.tvo-placeholder-input' ).val();
					settings.questions_required[ index ] = $this.find( '.tvo-required' ).is( ':checked' ) ? 1 : 0;
				} );

				return settings;
			},
		} );

		TVE.Views.Components.ovation_display = TVE.Views.Base.component.extend( {
			controls_init( controls ) {
				const self = this;

				this.template_modal = new TVE.TVO.displayTemplateModal( {
					el: TVE.modal.get_element( 'display-testimonial-templates-lightbox' ),
				} );

				this.settings_modal = new TVE.modal.base( {
					el: TVE.modal.get_element( 'display-settings-lightbox' ),
				} );

				/* all the controls are color pickers so we treat them all the same */
				_.each( controls, function ( control ) {
					control.update = function () {
						this.setValue( this.applyTo().css( control.model.config.style ) );
					};

					control.input = function ( color ) {
						const css = {},
							$config = TVE.ActiveElement.find( '.thrive-shortcode-config' ),
							config = TVE.TVO.config.get( $config );

						/* on some rare occasions we have a border so we have to treat it in a special way. */
						if ( control.model.config.style.indexOf( 'border' ) !== -1 ) {
							css.border = '1px solid';
						}

						css[ control.model.config.style ] = color + '!important';
						this.applyTo().head_css( css, false, '', false, TVE.CONST.global_css_prefix + ' ' );

						config.custom_css = config.custom_css || {};
						config.custom_css[ control.model.to ] = this.applyTo().first().data( 'css' );
						/* save the custom css id so we can apply it on display */
						TVE.TVO.config.save( $config, config );
					};
				} );

				$( document ).on( 'tvo.get.shortcode', function ( event, config ) {
					const currentConfig = TVE.ActiveElement.data( 'config' );

					/* we keep the template from the current config, not the saved one. */
					config.template = currentConfig.template;

					/* remove the id because we will use the config to render the template */
					config.id && delete config.id;

					TVE.TVO.ajaxRender( self.settings_modal, _.extend( currentConfig, config ) );
				} );

				$( document ).on( 'tvo.save.shortcode', function ( event, config ) {
					let $element = TVE.ActiveElement,
						currentConfig = $element.data( 'config' );

					currentConfig = _.extend( currentConfig, config );

					self.settings_modal.$el.addClass( 'loading' );

					$.ajax( {
						headers: {
							'X-WP-Nonce': TVO_Front.nonce,
						}, cache: false, url: TVO_Front.routes.shortcodes, type: 'POST', data: {
							name: currentConfig.name, type: 'display', config: currentConfig, content: '', html: 1,
						},
					} ).done( function ( response ) {
						TVE.TVO.updateElement( $element, response, self.settings_modal );
					} );
				} );
			}, placeholder_action() {
				TVE.ActiveElement.data( 'config', TVE.TVO.defaults.display );

				this.template_modal.open( { init: true, template: TVE.TVO.defaults.display.template, component: this } );
			}, after_update() {
				const $element = TVE.ActiveElement,
					config = TVE.TVO.config.get( $element ) || TVE.TVO.defaults.display;

				TVO_Front.active_config = config;

				$element.data( 'config', config );

				/* not all templates can be fully customized => we display only controls that can change something for the current element */
				_.each( this.controls, function ( control ) {
					setTimeout( function () {
						if ( control.applyTo().length === 0 ) {
							control.hide();
						} else {
							control.show();
						}
					}, 10 );
				} );
			}, display_settings( event, dom, route ) {
				this.settings_modal.open();

				Backbone.history.stop();
				Backbone.history.start();

				route = route ? route : '#pre-select';

				TVO_Front.router.navigate( route, { trigger: true } );
			}, change_template() {
				const config = TVE.ActiveElement.data( 'config' );

				this.template_modal.open( { template: config.template, component: this } );
			},
		} );

		TVE.TVO.displayTemplateModal = TVE.modal.base.extend( {
			before_open( options ) {
				if ( options.template && options.template.length > 0 ) {
					this.$( '.tvo-template' ).each( function () {
						this.dataset.value === options.template ? this.classList.add( 'current' ) : this.classList.remove( 'current' );
					} );
				}

				this.init = options && options.init;
				this.component = options && options.component;
			}, save() {
				const config = TVE.ActiveElement.data( 'config' );

				//when we pick the template, the ajax should send an empty custom_css for the config
				if ( config.custom_css ) {
					config.custom_css = {};
				}

				config.template = this.$( '.tvo-template.current' ).data( 'value' );

				if ( this.init ) {
					/* on the first time, after we chose the template we go to the settings page */
					TVE.ActiveElement.data( 'config', config );
					this.close();
					this.component.display_settings( {}, {}, '#start' );
				} else {
					TVE.TVO.ajaxRender( this, config );
				}
			}, select( event, dom ) {
				/* lol, just having some fun with js */
				dom.parentNode.childNodes.forEach( function ( element ) {
					element.nodeType !== Node.TEXT_NODE && element.classList.remove( 'current' );
				} );

				dom.classList.add( 'current' );
			},
		} );
	} );
}( jQuery ) );
