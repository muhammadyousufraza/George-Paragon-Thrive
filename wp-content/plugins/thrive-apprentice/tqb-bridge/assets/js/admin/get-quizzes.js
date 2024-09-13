const fetchQuizzes = () => {
	return new Promise( ( resolve, reject ) => {
		wp.apiRequest( {
			  url: TVA.Utils.buildUrl( TVA.routes.admin, {}, 'get_quizzes' ),
		  } )
		  .done( response => {
			  resolve( response );
		  } )
		  .fail( error => reject( error ) );
	} );
};

module.exports = async () => {
	if ( typeof TVA.quizzes !== 'undefined' ) {
		return TVA.quizzes;
	}

	TVA.quizzes = await fetchQuizzes();

	return TVA.quizzes;
};
