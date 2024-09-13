module.exports = require( './base' ).extend( {
	template: 'rule',
	className: 'tvd-content-set-rule',
	hiddenPostTypes: [ 'tvd_blog_page', 'tvd_search_result_page' ],
	afterInitialize() {
		this.listenTo( this.model, 'control-changed', _.bind( this.stepChanged, this ) )
		this.listenTo( this.collection, 'add', _.bind( this.allowDelete, this ) );
		this.listenTo( this.collection, 'remove', _.bind( this.allowDelete, this ) );
	},

	clearSteps() {
		if ( this.stepInstances ) {
			this.stepInstances.forEach( inst => {
				inst.off( 'all' );
				inst.destroy();
			} );
		}
		this.stepInstances = [];
	},
	afterRender() {
		this.$ruleWrapper = this.$( '.tvd-rule-holder' ).empty();
		this.$deleteRuleWrapper = this.$( '.tvd-content-set-delete-rule-wrapper' );

		this.clearSteps();

		if ( this.model.isEmpty() ) {
			this.computeNextStep();
		} else {
			this.model.getSteps().forEach( step => {
				if ( this.model.get( step ).length > 0 ) {
					this.addStep( step );
				}
			} );
			this.computeNextStep();
		}

		this.allowDelete();
	},
	stepChanged( stepChanged, stepChangedValue ) {
		this.clearSteps();
		this.$ruleWrapper.empty();

		const removeStepFromIndex = this.getStepIndex( stepChanged ) + 1;

		if ( this.getStepsLength() !== removeStepFromIndex ) {

			for ( let i = removeStepFromIndex; i < this.getStepsLength(); i ++ ) {
				const step = this.model.getSteps()[ i ];

				if ( this.model.get( step ).toString().length ) {
					const obj = {};
					obj[ step ] = '';
					this.model.set( obj, {silent: true} );
				}
			}
		}

		this.model.set( this.getRuleModelParams( stepChanged, stepChangedValue ) );

		this.model.getSteps().forEach( step => {
			if ( this.model.get( step ).length > 0 ) {
				this.addStep( step );
			}
		} );

		this.computeNextStep( 'field' === stepChanged && this.getNextStep().length );
	},
	/**
	 * Do some particular things here
	 * Based on the step, return the proper params
	 *
	 * @param {string} step
	 * @param {string} value
	 * @return {Object}
	 */
	getRuleModelParams( step, value ) {
		const params = {};
		params[ step ] = value;

		if ( step === 'content' ) {
			if ( [ 'tvd_search_result_page', 'tvd_blog_page' ].includes( params.content ) ) {
				params.content_type = params.content;
				params.field = - 1;
			} else if ( params.content === 'archive' ) {
				params.content_type = params.content;
			} else {
				params.content_type = TD_SETS.post_types.includes( value ) ? 'post' : 'term';
			}
		}

		if ( step === 'field' ) {
			if ( params.field === 'title' && this.model.get( 'content_type' ) === 'archive' ) {
				params.content_type = 'term';
				params.content = 'category';
			}

			if ( params.field === 'author' && [ 'archive', 'term' ].includes( this.model.get( 'content_type' ) ) ) {
				params.content_type = 'archive';
				params.content = 'archive';
			}
		}

		return params
	},
	/**
	 * Computes the next step in building the rule
	 *
	 * @param {boolean} triggerChange
	 */
	computeNextStep( triggerChange = false ) {
		const nextStep = this.getNextStep();

		if ( nextStep.length ) {
			this.addStep( this.getNextStep(), triggerChange );
		}
	},
	getStepIndex( step ) {
		return this.model.getSteps().indexOf( step );
	},
	getStepsLength() {
		return this.model.getSteps().length;
	},
	/**
	 * Returns the next step in the rule
	 *
	 * @return {string}
	 */
	getNextStep() {
		let nextStep = '';

		if ( this.forceCompleteSteps() ) {
			return nextStep;
		}

		this.model.getSteps().some( step => {
			if ( this.model.get( step ).length === 0 ) {
				nextStep = step;
				return true;
			}
		} );

		return nextStep;
	},

	/**
	 * Force complete steps
	 * Ex: when the field dropdown has "ALL" in it
	 *
	 * @return {boolean}
	 */
	forceCompleteSteps() {
		return ( this.model.get( 'field' ) && parseInt( this.model.get( 'field' ) ) === - 1 ) || this.hiddenPostTypes.includes( this.model.get( 'content' ) );
	},

	/**
	 * Add a new step in the rule
	 *
	 * @param {string} step
	 * @param {boolean} triggerChange
	 */
	addStep( step, triggerChange = false ) {
		const View = this.getStepView( step );
		const instance = new View( {
			ruleModel: this.model,
			step,
			model: new Backbone.Model( {
				options: this.getStepOptions( step ),
				trigger_change: triggerChange,
			} )
		} );
		if ( step === 'content' && this.model.get( 'content' ) === 'category' ) {
			instance.getStoredValue = function () {
				if ( this.ruleModel.get( 'content_type' ) === 'term' && this.ruleModel.get( 'content' ) === 'category' ) {
					return 'archive';
				}

				return this.ruleModel.get( this.step );
			}
		}

		this.stepInstances.push( instance );

		this.$ruleWrapper.append( instance.render().$el );
	},

	/**
	 * Do some particular things
	 *
	 * @param {string} step
	 *
	 * @return {*}
	 */
	getStepOptions( step ) {

		let options = TD_SETS.options.general[ step ];

		if ( step === 'field' && Array.isArray( TD_SETS.options.exceptions[ this.model.get( 'content' ) ] ) ) {
			options = TD_SETS.options.exceptions[ this.model.get( 'content' ) ];
		} else if ( step === 'operator' && Array.isArray( TD_SETS.options.exceptions[ this.model.get( 'field' ) ] ) ) {
			options = TD_SETS.options.exceptions[ this.model.get( 'field' ) ];
		}

		return options;
	},
	/**
	 * Returns the Step View
	 *
	 * @param {string} step
	 *
	 * @return {Backbone.View}
	 */
	getStepView( step ) {
		let View = require( './base' );

		switch ( step ) {
			case 'content':
			case 'field':
			case 'operator':
				View = require( `./controls/select` );
				break;
			case 'value':
				if ( this.model.get( 'field' ) === TD_SETS.fields.published_date ) {
					if ( this.model.get( 'operator' ) === TD_SETS.operators.within_last ) {
						View = require( `./controls/within-the-last` );
					} else {
						View = require( `./controls/date-picker` );
					}
				} else {
					View = require( `./controls/select-multiple` );
				}
			default:
				break;
		}

		return View;
	},
	allowDelete() {
		this.$deleteRuleWrapper.toggle( this.collection.length > 1 );
	},
	deleteRule() {
		const confirmView = new TD_SETS.Views.Confirm( {
			template: TVE_Dash.tpl( 'rule-delete-confirmation' ),
			className: 'tvd-content-set-delete-confirmation',
			confirm: () => {
				this.model.destroy();
				this.destroy().remove();
			},
			cancel() {
				this.remove();
			}
		} );

		this.$el.append( confirmView.render().$el );
	}
} );
