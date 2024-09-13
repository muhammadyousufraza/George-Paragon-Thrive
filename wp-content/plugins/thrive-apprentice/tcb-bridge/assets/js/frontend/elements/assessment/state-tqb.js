const State = require( './state' );

class StateTQB extends State {
	init () {
		this.$quizWrapper = this.$form.find( '.tqb-shortcode-wrapper' );
	}

	reset () {
		const Quiz = this.$quizWrapper.data( 'tcbQuiz' );

		if ( Quiz ) {
			Quiz.forceRestartQuiz();
		}
	}

	getAjaxData () {
		return this.config;
	}
}

module.exports = StateTQB;
