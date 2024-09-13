( function ( $ ) {
	var _instance = null,
		_utils = require( '../_utils' );

	module.exports = TVE.CloudTemplatesCategories.extend( {
		$$type: 'ultimatum_' + tve_ult_page_data.design_type,
		templateName: TVE.ucFirst( tve_ult_page_data.design_type === 'bar' ? 'ribbon' : tve_ult_page_data.design_type ),
		categoryId: 'countdown_type',
		before_open: function () {

			this.applyTo = TVE.Editor_Page.editor.children( '.thrv_wrapper' );
			if ( ! TVE.ActiveElement ) {
				TVE.ActiveElement = this.applyTo.addClass( 'edit_mode' );
			}

			if ( ! TVE.CONST[ this.collectionName ] ) {
				this.fetchTemplates( this.$$type );
			} else {
				this.$( '[data-item="Saved Templates"] .lp-group-count' ).text( tve_ult_page_data.saved_tpls.length )
			}

			this.resetFilters();

			if ( ! this.applyTo.hasClass( 'tcb-ct-placeholder' ) && this.applyTo.attr( 'tcb-template-name' ) ) {
				this.$warning.show().find( '.tcb-notification-content' ).html( TVE.t.warning_change_ct.replace( /%s/g, this.templateName ) );
			} else {
				this.$warning.hide();
			}

			this.selectDefault();
		},

		selectDefault: function () {
			this.filter( {}, this.$typesWrapper.find( '[data-item="Cloud Templates"]' ) );
		},

		on_success: function ( response ) {
			if ( response.error ) {
				return this.on_error( {responseJSON: response} );
			}

			this.collection.reset( response.templates );
			this.build_preview();

			/**
			 * Render Types
			 */
			this.types = {};

			_.each( response.templates, ( tpl ) => {
				const type = tpl.config[ `tve_tpl_${this.$$type}_meta` ][ this.categoryId ];

				if ( type && ! this.types[ type ] ) {
					this.types[ type ] = 0;
				}

				this.types[ type ] ++;
			} );

			this.$typesWrapper.empty()
				.append( this.group_item( {group: 'All Cloud Templates', counter: response.templates.length} ) )

			_.each( this.types, ( counter, groupKey ) => {
				this.$typesWrapper.append( this.group_item( {group: groupKey, counter: counter} ) )
			} );

			this.$typesWrapper.append( this.group_item( {group: 'Saved Templates', counter: tve_ult_page_data.saved_tpls.length} ) );

			this.selectDefault();
			TVE.CONST[ this.collectionName ] = response.templates;
		},
		/**
		 * Build the preview
		 *
		 * Called from reset collection
		 */
		build_preview: function () {
			var html = '',
				self = this;
			if ( this.filters.group.length > 0 ) {
				if ( this.filters.group[ 0 ].indexOf( 'Cloud' ) !== - 1 ) {
					this.collection.reset( TVE.CONST[ this.collectionName ] );
				} else if ( this.filters.group[ 0 ].indexOf( 'Countdown' ) !== - 1 ) {
					this.collection.reset( TVE.CONST[ this.collectionName ].filter( t => t.config[ `tve_tpl_${this.$$type}_meta` ][ this.categoryId ] === this.filters.group[ 0 ] ) );
				} else {
					this.collection.reset( tve_ult_page_data.saved_tpls );
				}
			}

			this.instance.collection.each( function ( model ) {
				html += self.item_template( {
					item: model.toJSON(),
					selected: '',
				} );

			} );

			if ( html.length ) {
				this.$content.html( html );

				var dynamicImageWidth = parseInt( this.$content.find( this.itemClass ).outerWidth( true ) );

				this.$content.find( 'img' ).each( function () {
					var imageRation = this.getAttribute( 'data-ratio' );
					if ( imageRation ) {
						$( this ).closest( '.cloud-template-item' ).css( 'height', dynamicImageWidth * parseFloat( imageRation ) );
					}
				} );

				this.applyMasonry();
			} else {
				this.$content.html( this.no_templates() );
			}

			this.on_finished();
		},
		/**
		 * Download a template
		 *
		 * @param id
		 */
		select_template: function ( id ) {
			this.selected = String( id );

			this.applyTo.addClass( 'tcb-block-placeholder tcb-elem-placeholder' ).empty();

			this.close();

			TVE.inner_$( 'html' ).stop().animate( {
				scrollTop: this.applyTo.offset().top - 50
			}, {duration: 500} );

			if ( this.selected.indexOf( 'tvu-tpl' ) !== - 1 ) {
				this.saveSavedTemplate();
			} else {
				this.save();

			}
		},
		saveSavedTemplate: function () {
			var self = this;
			_utils.tpl_ajax( {
				custom: 'get_saved_tpl',
				tpl_id: this.selected
			} ).done( function ( response ) {
				self.applyTo.replaceWith( $( response.content ) );
				TVE.Editor_Page.content_manager.insert_head_css( response.inline_css ).done( function () {

					TVE.Editor_Page.editor.find( '[data-css]' ).addBack( '[data-css]' ).each( function () {
						TVE.inner_$( this ).head_css_clone();
					} );

					TVE.main.overlay( true );
					TVE.Editor_Page.save( true );
				} )
			} )
		},
		alter_wrapper_attributes: function ( attributes, data ) {
			/**
			 * Force the proper active element
			 */
			if ( this.applyTo.length > 1 ) {
				this.applyTo = this.applyTo.filter( ':not(style)' );
				TVE.ClickedElement = TVE.ActiveElement = this.applyTo;
			}
			return {
				'tcb-template-name': data.name,
				'tcb-template-id': data.id,
				'tvu-tpl-id': 'cloud_' + data.type + '-' + data.id,
				'tcb-template-pack': data.pack,
				'data-keep-css_id': 1,
			}
		},
		afterTemplateCssApplied: function () {
			TVE.Editor_Page.editor.children( 'style' ).slice( 1 ).remove();
		}
	}, {
		/**
		 * "Singleton" implementation for modal instance
		 *
		 * @param el
		 */
		get_instance: function ( el ) {
			if ( ! _instance ) {
				_instance = new TVE_Ult_Int.DesignTemplates( {
					el: el
				} );
				_instance.$el.addClass( 'tcb-modal-cloud-templates-categories' )
			}

			return _instance;
		}
	} );
} )( jQuery );
