( function ( $, tcb ) {

	const FormManager = require( './assessment/form-manager' );
	let $nextButton;

	module.exports = {
		collapseClass: 'tve-state-expanded',
		init () {
			if ( ! tcb.Utils.isEditorPage() ) {
				$( '.tva-assessment' ).tveAssessment();

				tcb.Hooks.addFilter( 'tqb.stop_render_quiz_page', this.shouldStopRenderQuizPage );

				if ( window.ThriveAppFront && ThriveAppFront.assessment_submitted && ! parseInt( ThriveAppFront.assessment_submitted ) ) {
					$nextButton = $( '[data-shortcode-id="next_lesson"][data-dynamic-link="tva_dynamic_actions_link"]' ).closest( '.thrv-button' );
					$nextButton.addClass( 'tar-disabled' );
					tcb.Hooks.addAction( 'tva.assessment.submit_success', () => {
						if ( $nextButton.length ) {
							$nextButton.removeClass( 'tar-disabled' );
						}
					} );
				}
			}
		},
		/**
		 * @param {boolean} shouldStop
		 * @param {Backbone.Model} quizModel
		 * @param {jQuery} $wrapper
		 *
		 * @return {boolean} Decides if the quiz should stop render the page
		 */
		shouldStopRenderQuizPage ( shouldStop, quizModel, $wrapper ) {

			if ( $wrapper.closest( '.tva-assessment' ).length > 0 && quizModel.get( 'page_type' ) === 'results' ) {
				shouldStop = true;

				const ElementForm = $wrapper.closest( '.tva-assessment' ).data( 'tcbAssessment' );

				ElementForm.activeState.config = {
					value: quizModel.get( 'user_id' ),
					quiz_id: quizModel.get( 'quiz_id' ),
					user_unique: quizModel.get( 'user_unique' ),
				};

				ElementForm.sendRequest( $( '' ) )
			}

			return shouldStop;
		},
		/**
		 * Expand / Collapse the result item depending on the state
		 *
		 * @param {jQuery} $resultItem
		 * @param {string} method - jQuery method (toggleClass|addClass|removeClass)
		 */
		toggleItem ( $resultItem, method = 'toggleClass' ) {
			$resultItem[ method ]( this.collapseClass ).siblings()[ $resultItem.hasClass( this.collapseClass ) ? 'slideUp' : 'slideDown' ]();
		},
	};

	$.fn.tveAssessment = function () {
		return this.each( ( _index, element ) => {
			const $element = $( element );

			if ( ! $element.data( 'tcbAssessment' ) ) {
				$element.data( 'tcbAssessment', ( new FormManager( $element ) ).bindEvents() );
			}
		} );
	}

} )( ThriveGlobal.$j, TCB_Front );
