module.exports = require( './base' ).extend( {
	template: 'form',
	addNewRuleTemplate: 'add-new-rule',
	addRuleView: null,
	afterInitialize() {
		this.listenTo( this.model.getRules(), 'add remove change', _.bind( this.rulesChanged, this ) );
		this.listenTo( this.model.getRules(), 'add change', this.animateToBottom );
	},
	afterRender() {
		this.$name = this.$( '#tvd-content-set-name' );
		this.$rulesWrapper = this.$( '#tvd-content-set-rules' );
		this.$addRuleWrapper = this.$( '#tvd-content-set-add-rule' );
		this.$inner = this.$( '.tvd-content-set-inner' );
		this.$saveButton = this.$( 'button' );

		this.renderRules();
		this.rulesChanged();

		this.$name.on( 'input', () => {
			if ( this.getTitle().length >= 1 ) {
				this.$name.removeClass( 'tva-cset-error' );
			}
		} );
	},
	renderRules() {
		this.$rulesWrapper.empty();

		this.model.getRules().each( this.renderRule, this );
	},
	renderRule( ruleModel ) {
		this.$rulesWrapper.append( ( new TD_SETS.Views.Rule( {
			model: ruleModel,
			collection: this.model.getRules(),
		} ).render().$el ) )
	},
	rulesChanged() {
		this.$addRuleWrapper.children().remove();

		if ( this.allowAddNewRule() ) {
			this.$addRuleWrapper.append( TVE_Dash.tpl( this.addNewRuleTemplate )() );
		}
	},
	animateToBottom: function () {
		this.$inner.animate( {
			scrollTop: this.$inner[ 0 ].scrollTop + 1000
		}, 150 );
	},
	addNewRule: function () {
		const model = new TD_SETS.Models.Rule( {} );

		this.model.getRules().add( model );

		this.renderRule( model );
	},
	allowAddNewRule() {
		let allow = true;
		this.model.getRules().each( ruleModel => {
			if ( ! ruleModel.isCompleted() ) {
				allow = false;
			}
		} );

		return allow;
	},
	getTitle() {
		return this.$name.val().trim();
	},
	saveContentSet() {
		const title = this.getTitle();

		this.$( '.tva-cset-error' ).removeClass( 'tva-cset-error' );

		this.$name.toggleClass( 'tva-cset-error', title.length === 0 );

		if ( title.length === 0 ) {
			setTimeout( () => {
				this.$inner.animate( {scrollTop: 0}, 150 );
			}, 200 );

			TVE_Dash.err( 'Please add a name' );
			return;
		}

		this.model.set( {'post_title': title}, {silent: true} );

		if ( ! this.model.isValid() ) {
			const changed = this.model.changedAttributes();
			if ( changed && changed.post_title ) {
				const previousAttributes = this.model.previousAttributes();

				this.model.set( {'post_title': previousAttributes.post_title}, {silent: true} );
			}

			TVE_Dash.err( this.model.getValidationError() );

			/**
			 * UI Validation:
			 *
			 * Highlight the empty fields
			 */
			this.$( 'input, select' ).each( ( index, elem ) => {

				if ( elem.tagName.toLowerCase() === 'input' && elem.classList.contains( 'select2-search__field' ) ) {
					const $select2 = jQuery( elem ).closest( '.tvd-content-set-rule-step' ).find( 'select' );

					if ( $select2.val().length === 0 ) {
						elem.classList.add( 'tva-cset-error' );
					}
				} else if ( ! elem.value ) {
					elem.classList.add( 'tva-cset-error' );
				}
			} );

			return;
		}
		this.$saveButton.addClass( 'disabled' );
		TVE_Dash.showLoader();

		this.model.save()
		    .done( response => {
			    this.collection.reset( response );
			    this.$saveButton.removeClass( 'disabled' );
			    TVE_Dash.hideLoader();
		    } )
		    .fail( r => {
			    if ( r.responseJSON && r.responseJSON.message ) {
				    TVE_Dash.err( 'Something went wrong: ' + r.responseJSON.message )
			    } else {
				    TVE_Dash.err( 'Something went wrong: ' + r.responseText )
			    }
			    this.$saveButton.removeClass( 'disabled' );
			    TVE_Dash.hideLoader();
		    } );
	}
} );
