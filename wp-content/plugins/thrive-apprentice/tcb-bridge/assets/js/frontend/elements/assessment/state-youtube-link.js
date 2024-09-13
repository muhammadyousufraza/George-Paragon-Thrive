const State = require( './state' );

class StateYoutubeLink extends State {

	init () {
		this.$input = this.$form.find( 'input' );
		this.$videoContainer = this.$form.find( '.tva_responsive_video_container' );
		this.$videoPlaceholder = this.$form.find( '.tva-video-preview-no-video' );

		this.$input.off( 'change' ).on( 'change', e => {
			this.displayPreview( e );
		} );
	}

	reset () {
		this.$input.val( '' ).trigger( 'change' );
	}

	getURL () {
		return this.$input.val().trim();
	}

	validate () {
		const inputValue = this.getURL();

		return ! ( inputValue.length === 0 || ! TCB_Front.isValidUrl( inputValue ) || ! this.validURL( inputValue ) );


	}

	displayPreview () {
		let $iframe = this.$videoContainer.find( 'iframe' );

		if ( $iframe.length === 0 ) {
			this.$videoContainer.append( '<iframe frameborder="0" allowfullscreen loading="lazy"></iframe>' );

			$iframe = this.$videoContainer.find( 'iframe' );
		}

		const isValid = this.validate();

		if ( isValid ) {
			$iframe.attr( 'src', `https://www.youtube.com/embed/${ this.validURL( this.getURL() ) }` )
		}

		$iframe[ isValid ? 'show' : 'hide' ]();

		this.$videoPlaceholder[ isValid ? 'hide' : 'show' ]();
	}

	validURL ( url ) {
		const p = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
		const matches = url.match( p );

		return !! matches ? matches[ 1 ] : false;
	}

	getAjaxData () {
		return {
			value: this.getURL(),
		};
	}

	/**
	 * @return {string} Submit error message
	 */
	getSubmitErrorMessage () {
		return this.config.error_messages.youtube_link_invalid;
	}
}

module.exports = StateYoutubeLink;
