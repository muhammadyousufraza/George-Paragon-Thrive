const State = require( './state' );
const StateUpload = require( './state-upload' );
const StateYoutubeLink = require( './state-youtube-link' );
const StateExternalLink = require( './state-external-link' );
const StateResults = require( './state-results' );
const StateTQB = require( './state-tqb' );

const submitButtonSelector = '.tve-form-button-submit';

class FormManager {
	constructor ( $wrapper ) {
		const attrID = Number( $wrapper.attr( 'data-assessment-id' ) );

		this.formType = ! $wrapper.attr( 'data-type' ) || ! [ 'auto', 'submit', 'results' ].includes( $wrapper.attr( 'data-type' ) ) ? 'auto' : $wrapper.attr( 'data-type' );
		this.$wrapper = $wrapper;
		this.assessmentID = ( isNaN( attrID ) || attrID === - 1 ) ? Number( tve_frontend_options.post_id ) : attrID;
		this.config = TCB_Front.Utils.unserialize( TCB_Front.Base64.decode( this.$wrapper.find( 'input[name="config"]' ).val() ) );

		if ( ! this.config.error_messages || Object.keys( this.config.error_messages ).length === 0 ) {
			this.config.error_messages = this.getDefaultErrors();
		}

		this.initStates();

		if ( [ 'auto', 'submit' ].includes( this.formType ) ) {
			/**
			 * Allow switchState on init only for states with forms
			 */
			this.switchState( this.getDefaultState(), false );
		}

		this.$wrapper.find( '.tva-assessment-type[data-type]' ).on( 'tcb.change_state', ( _e, args ) => {

			if ( args.result_content && args.result_content.length > 0 ) {
				this.$wrapper.find( '.tva-assessment-type[data-type="results"]' ).html( args.result_content );
				//Re-bind events for result state
				this.states.results.init();
				this.bindEvents( this.$wrapper.find( '.tva-assessment-type[data-type="results"]' ) );
			}

			this.switchState( args.state );
		} );

		ThriveGlobal.$j( window ).on( 'hashchange', () => {
			const state = this.getStateFromHash();
			if ( state ) {
				this.switchState( state );
			}
		} );
	}

	/**
	 * @return {Object} Error messages
	 */
	getDefaultErrors () {
		return {
			file_extension: 'Sorry, {fileextension} files are not allowed',
			file_size: '{file} exceeds the maximum file size of {filelimit}',
			file_required: 'At least one file is required',
			upload_progress: 'File upload in progress. Please wait for the upload to finish and try again.',
			link_invalid: 'Sorry, that URL is not valid',
			youtube_link_invalid: 'Sorry, that URL is not a valid youtube video link',
			max_files: 'Sorry, the maximum number of files is {maxfiles}',
		};
	}

	initStates () {
		this.states = {};
		this.$wrapper.find( '.tva-assessment-type[data-type]' ).each( ( _index, el ) => this.states[ el.dataset.type ] = this.stateFactory( el.dataset.type ) );
	}

	switchState ( state, withHideLogic = true ) {
		if ( withHideLogic ) {
			this.$wrapper.find( '.tva-assessment-type' ).addClass( 'tcb-permanently-hidden' );
			this.$wrapper.find( `.tva-assessment-type[data-type="${ state }"]` ).removeClass( 'tcb-permanently-hidden' );
		}

		this.activeState = this.states[ state ];
	}

	onSwitchState ( event ) {
		const $target = this.$wrapper.find( event.currentTarget );

		const state = this.getStateToSwitch( $target.data( 'shortcode-id' ) );

		if ( state ) {
			event.preventDefault();

			this.switchState( state );
			return false;
		}

		return true;
	}

	getStateToSwitch ( id ) {
		let state = null;

		if ( id === 'back_to_submit' ) {
			state = this.$wrapper.attr( 'data-default-type' );

			//Reset the state before switching to the default state
			this.states[ state ].reset();
		}

		if ( id === 'results' ) {
			//TODO: refactor this
			state = 'results';
		}

		return state;
	}

	/**
	 * @return {string | null} Get a possible form state from URL hash (#tcb-state--results)
	 */
	getStateFromHash () {
		if ( window.location.hash ) {
			const possibleState = window.location.hash.replace( '#tcb-state--', '' );

			if ( [ 'results', 'confirmation' ].includes( possibleState ) && this.$wrapper.find( `.tva-assessment-type[data-type="${ possibleState }"]` ).length > 0 ) {
				return possibleState;
			}
		}

		return null;
	}

	getDefaultState () {
		return this.$wrapper.attr( 'data-default-type' );
	}

	stateFactory ( state ) {
		const args = {
			assessmentID: this.assessmentID,
			form: this.$wrapper.find( `.tva-assessment-type[data-type="${ state }"]` ),
			config: this.config
		};

		let instance;

		switch ( state ) {
			case 'upload':
				instance = new StateUpload( args );
				break;
			case 'youtube_link':
				instance = new StateYoutubeLink( args );
				break;
			case 'external_link':
				instance = new StateExternalLink( args );
				break;
			case 'results':
				instance = new StateResults( args );
				break;
			case 'tqb':
				instance = new StateTQB( args );
				break;
			default:
				instance = new State( args );
				break;
		}

		return instance;
	}

	onSubmit ( e ) {
		e.preventDefault();
		e.stopPropagation();

		if ( ! this.activeState.validate() ) {
			const errorMessage = this.activeState.getSubmitErrorMessage();

			if ( errorMessage && errorMessage.length > 0 ) {
				TCB_Front.toast( errorMessage, 1 );
			}

			return;
		}

		this.sendRequest( jQuery( e.currentTarget ) );
	}

	sendRequest ( $submitButton ) {
		const stateInstance = this.activeState;

		stateInstance.beforeSend();

		$submitButton.addClass( 'tar-disabled' );

		TCB_Front.Utils.restAjax( {
					 route: tve_frontend_options.routes.assessments,
					 type: 'POST',
					 data: this.getAjaxData(),
				 } )
				 .success( response => {
					 if ( this.config.submit_action === 'default' ) {
						 if ( parseInt( this.config.show_success ) && this.config.success_message.length ) {
							 TCB_Front.Utils.toast( this.config.success_message );
						 }
						 stateInstance.onSuccess( response );
						 document.location.reload();
					 } else if ( this.config.submit_action === 'redirect' && this.config.redirect_url.length ) {
						 document.location.href = TCB_Front.Utils.addHttp( this.config.redirect_url );
					 }
				 } ).fail( response => stateInstance.onFail( response ) )
				 .always( () => {
					 $submitButton.removeClass( 'tar-disabled' );
				 } );
	}

	getAjaxData () {
		const data = {
				type: this.activeState.getStateKey(),
				post_id: Number( tve_frontend_options.post_id ),
				assessment_id: this.assessmentID,
				...this.activeState.getAjaxData()
			},
			resultTemplateSelector = `.tcb-assessment-result-template[data-identifier="${ this.$wrapper.attr( 'data-css' ) }"]`;

		if ( ThriveGlobal.$j( resultTemplateSelector ).length > 0 ) {
			data.result_content = ThriveGlobal.$j( resultTemplateSelector ).html();
		}

		return data
	}

	bindEvents ( $root = this.$wrapper ) {

		if ( ! $root ) {
			$root = this.$wrapper;
		}

		/**
		 * Hides dynamic links based on form types
		 */
		switch ( this.formType ) {
			case 'submit':
				$root.find( '[data-shortcode-id="results"]' ).remove();
				break;
			case 'results':
				$root.find( '[data-shortcode-id="back_to_submit"]' ).remove();
				break;
		}

		$root.find( submitButtonSelector ).off( 'click' ).on( 'click', e => {
			this.onSubmit( e );
		} );

		$root.find( '.tve-dynamic-link[data-dynamic-link="tva_assessment_dynamic_link"]' ).off( 'click' ).on( 'click', e => this.onSwitchState( e ) );

		return this;
	}
}

module.exports = FormManager;
