const State = require( './state' );

class StateResults extends State {
	init () {
		const $resultList = this.$form.find( '.tva-assessment-result-list' );

		$resultList.find( '.tva-assessment-result-state-header' ).click( event => {
			if ( event.target.tagName !== 'A' ) {
				TCB_Front.assessment.toggleItem( ThriveGlobal.$j( event.currentTarget ) );
			}
		} );
	}
}

module.exports = StateResults;
