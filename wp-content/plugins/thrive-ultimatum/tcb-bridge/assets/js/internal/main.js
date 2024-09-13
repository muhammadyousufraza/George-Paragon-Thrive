/**
 * Created by Ovidiu on 7/21/2017.
 */
var TVE_Ult_Int = window.TVE_Ult_Int = TVE_Ult_Int || {},
	withCloudTpls = false,
	EDITOR_INSTANCE = 1,
	TU_FORM_EVENTS = [ 'tve_ult_close' ],
	countdownSet = require( './countdown-sets-constants' );

( function ( $ ) {

	/**
	 * On TCB Main Ready
	 */
	$( window ).on( 'tcb_main_ready', function () {
		withCloudTpls = Boolean( parseInt( tve_ult_page_data.with_cloud_tpl ) )

		if ( withCloudTpls ) {
			TVE_Ult_Int.DesignTemplates = require( './modals/design-cloud-templates' );
		} else {
			TVE_Ult_Int.DesignTemplates = require( './modals/design-templates' );
		}
		TVE_Ult_Int.DesignReset = require( './modals/design-reset' );
		TVE_Ult_Int.DesignSave = require( './modals/design-save' );
		TVE_Ult_Int.AddEditState = require( './modals/add-edit-state' );

		const _states = require( './states' );
		TVE_Ult_Int.States = new _states( {
			el: jQuery( '#tu-form-states' )[ 0 ]
		} );

		TVE_Ult_Int.savePreview = function ( designName = tve_ult_page_data.design_id, savePreviewCallback = null ) {
			const saveCallback = function ( imgData ) {

				const form = new FormData();
				if ( imgData ) {
					form.append( 'preview_file', imgData, designName + '.png' );
				}

				form.append( 'custom', 'save_thumbnail' );
				form.append( 'action', tve_ult_page_data.tpl_action );
				form.append( 'design_id', tve_ult_page_data.design_id );
				form.append( 'post_id', tve_ult_page_data.post_id );
				form.append( 'security', tve_ult_page_data.security );
				form.append( 'file_name', designName );

				$.ajax( {
					type: 'POST',
					url: tve_ult_page_data.ajaxurl,
					data: form,
					processData: false,
					contentType: false,
				} ).done( data => {
					if ( typeof savePreviewCallback === 'function' ) {
						savePreviewCallback( JSON.parse( data ) );
					}
				} );
			}
			TVE.Editor_Page.blur();
			TVE.Editor_Page.editor.find( '.on_hover' ).removeClass( 'on_hover' );
			TVE.generateElementPreview( TVE.Editor_Page.editor, saveCallback, {}, true );
		}

		function open_templates_modal() {
			var modal = withCloudTpls ? 'design-cloud-templates' : 'design-templates',
				designTemplatesModal = TVE_Ult_Int.DesignTemplates.get_instance( TVE.modal.get_element( modal ) );
			designTemplatesModal.open( {
				top: '5%',
				css: {
					width: '80%',
					left: '10%'
				},
				dismissible: ( tve_ult_page_data.has_content )
			} );
		}

		TVE.add_filter( 'editor_loaded_callback', function () {
			$( TVE.main ).on( 'tcb.open_templates_picker', function ( event ) {
				event.preventDefault();
				open_templates_modal();
			} );

			TVE.main.sidebar_extra.tve_ult_save_template = function () {
				var designSaveModal = TVE_Ult_Int.DesignSave.get_instance( TVE.modal.get_element( 'design-save' ) );
				TVE.main.sidebar_extra.hide_drawers();
				designSaveModal.open( {
					top: '20%'
				} );

				return false;
			};

			TVE.main.sidebar_extra.tve_ult_reset_template = function () {
				TVE.Editor_Page.blur();
				var designResetsModal = TVE_Ult_Int.DesignReset.get_instance( TVE.modal.get_element( 'design-reset' ) );
				designResetsModal.open( {
					top: '20%'
				} );

				return false;
			};

			/**
			 * Open Template Chooser if the variation is empty
			 */
			if ( ! tve_ult_page_data.has_content ) {
				if ( withCloudTpls ) {
					TVE.Editor_Page.editor.append( tve_ult_page_data.placeholder );
				}
				open_templates_modal();
			}

			/**
			 * Backwards Compatibility:
			 * Adds thrv-inline-text class to countdown elements that doesn't have it on caption class
			 */
			TVE.inner_$( '.thrv_countdown_timer .t-caption:not(.thrv-inline-text)' ).each( function () {
				jQuery( this ).addClass( 'thrv-inline-text' );
			} );

			TVE.StorageManager.unset( 'tvu_design-' + tve_ult_page_data.design_id );
		} );

		/**
		 * The countdown color form TU sets is now editable
		 */
		TVE.add_action( 'tcb.element.focus', function ( $element ) {
			if ( $element.hasClass( 'thrv_countdown_timer' ) && tve_ult_page_data.tpl_action ) {
				//get the templates set
				var setID = TVE.inner_$( '.tl-style' ).attr( 'id' );

				TVE.Components.countdown_old.controls.Color.config.css_suffix = '';

				if ( countdownSet[ setID ] ) {
					TVE.Components.countdown_old.controls.Color.config.config.style_default_color = countdownSet[ setID ];
				}
			}
		} );

		/**
		 * Mark all ajax requests that are sent from the current editor page with a special parameter that applies to Thrive Ultimatum
		 */
		TVE.add_filter( 'tcb.editor.default_ajax_config', function ( params ) {
			params.data.ultimatum_editor_page = 1;

			return params;
		} );

		TVE.add_filter( 'tve.countdown.state', function ( states ) {
			if ( TVE.CONST.post_type === 'tve_ult_campaign' ) {
				states = [];
			}
			return states;
		} );

		if ( withCloudTpls ) {
			TVE.add_action( 'tcb_after_cloud_template_css_inserted', function ( $content ) {
				if ( $content.attr( 'tvu-tpl-id' ) && $content.attr( 'tvu-tpl-id' ).indexOf( `ultimatum_${tve_ult_page_data.design_type}` ) !== - 1 ) {
					TVE.Editor_Page.save( true );
				}
			} );

			TVE.add_filter( 'tcb_save_post_data_before', function ( data ) {
				var $ultDesign = TVE.Editor_Page.editor.find( `[tvu-tpl-id*="ultimatum_${tve_ult_page_data.design_type}"]` );

				if ( $ultDesign.length ) {
					data.tve_ult_tpl = `${$ultDesign.first().attr( 'tvu-tpl-id' )}`;
				}

				if ( TVE.CONST.post_type === 'tve_ult_campaign' ) {
					var sizes = {
						m: '600px',
						t: '1023px',
						d: '1366px'
					}

					data[ 'form-height' ] = {};
					var $container = TVE.Editor_Page.editor.find( '.tve-editor-main-content' ),
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
				}

				return data;
			} )

			TVE.add_action( 'tve.save_post.success', function () {
				TVE.StorageManager.set( 'tvu_design-' + tve_ult_page_data.design_id, true );
				TVE_Ult_Int.savePreview();
			} );

			TVE.add_filter( 'tcb.elem.type', ( type, $element ) => {

				if ( type === 'icon' && $element.tcbHasClass( 'tve-ult-bar-close-editable' ) ) {
					type = 'ultimatum_close';
				}

				return type;
			} );

			TVE.add_action( 'tcb.element.click', function ( event, data ) {
				if ( event.currentTarget.classList.contains( 'tve-ult-cloud-placeholder' ) ) {
					open_templates_modal();
				}
			} );
		}

		/**
		 * Ultimatum froala control
		 */
		TVE.add_filter( 'tcb_froala_config', function () {
			return {
				linkEventActions: {
					getHtml: function () {
						var opts = TVE.Components.animation.options.actions;
						var actions = {
							tve_ult_close: opts.tve_ult_close
						};
						return TVE.tpl( 'froala-ult' )( {
							actions: actions,
							current_id: ++ EDITOR_INSTANCE
						} );
					},
					bindEvents: function ( $popup ) {
						$popup.on( 'change', '.fr-extra-action', function ( e ) {
							$popup.find( '.ult-action-config' ).hide();
							if ( ! this.checked ) {
								$popup.find( '.fr-link-atts,.fr-link-url,.tve-top-tabs' ).show();
							} else {
								$popup.find( '.ult-action-opts-' + this.getAttribute( 'data-key' ) ).show();
								$popup.find( '.fr-link-atts,.fr-link-url,.tve-top-tabs' ).hide();
								$popup.find( '.fr-extra-action' ).not( this ).prop( 'checked', false );
							}
						} );
					},
					hasSelected: function ( $popup ) {
						return $popup.find( '.fr-extra-action:checked' ).length;
					},
					getEventConfig: function ( $popup ) {
						var key = $popup.find( '.fr-extra-action:checked' ).attr( 'data-key' );
						return {
							a: key,
							t: 'click',
							config: {
								s: $popup.find( '.ult-action-opts-' + key + ' select[name="s"]' ).val(),
								anim: $popup.find( '.ult-action-opts-' + key + ' select[name="a"]' ).val()
							}
						}
					},
					reset: function ( $popup ) {
						$popup.find( '.fr-extra-action' ).prop( 'checked', false );
						$popup.find( '.fr-link-atts,.fr-link-url,.tve-top-tabs' ).show();
						$popup.find( '.ult-action-config' ).hide();
					},
					updateFromLink: function ( $link, $popup ) {
						var ult_event = false;
						this.reset( $popup );

						if ( $link.hasClass( 'tve_evt_manager_listen' ) ) {
							var evt = TVE.EventManager.get( $link, 'click' );
							if ( evt && TU_FORM_EVENTS.includes( evt.a ) ) {
								$popup.find( '.fr-extra-action[data-key="' + evt.a + '"]' ).prop( 'checked', true ).trigger( 'change' );
								$popup.find( '.ult-action-opts-' + evt.a + ' select[name="s"]' ).val( evt.config.s );
								ult_event = true;
								$popup.find( '.ult-action-opts-' + evt.a + ' select[name="a"]' ).val( evt.config.anim || 'instant' );
							}
						}

						return ult_event;
					}
				}
			};

		} );

	} );
} )( jQuery );
