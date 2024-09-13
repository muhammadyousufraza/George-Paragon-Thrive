class Editor {

	static changeState( state = 'default' ) {
		if ( TVE.ActiveElement ) {
			TVE.state_manager.change( state );
		}
	}

	static getTemplateType() {
		const urlSearchParams = new URLSearchParams( window.location.search );

		return urlSearchParams.get( 'jstest' );
	}
}

module.exports = Editor;
