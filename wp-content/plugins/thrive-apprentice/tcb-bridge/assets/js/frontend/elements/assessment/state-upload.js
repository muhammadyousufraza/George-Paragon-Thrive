const State = require( './state' );

class StateUpload extends State {
	init () {
		this.uploader = new TCB_Front.FileUpload( this.$form, {
			url: `${ tve_frontend_options.routes.assessments }/file-upload`,
			uploadExtraOptions: {
				headers: {
					'X-WP-Nonce': tve_frontend_options.nonce
				}
			}
		} );

		this.uploader.errorTemplates = { //Override the error messages from file-upload.js
			...this.errorTemplates,
			upload_progress: this.config.error_messages.upload_progress,
			file_required: this.config.error_messages.file_required,
			file_size: this.config.error_messages.file_size,
			file_extension: this.config.error_messages.file_extension,
			max_files: this.config.error_messages.max_files
		};

		//Send also the assessment ID and post_id when we upload the file
		this.uploader.uploader.setOption( 'multipart_params', {
			assessment_id: this.assessmentID,
			post_id: Number( tve_frontend_options.post_id ),
		} )
	}

	/**
	 * Removes all files from plUpload
	 */
	reset () {
		this.uploader.uploader.splice();
	}

	validate () {
		const isValid = this.uploader.isValid();

		if ( isValid === true ) {
			return true;
		} else if ( typeof isValid === 'string' ) {
			TCB_Front.toast( isValid, 1 );
		}

		return false;
	}

	getAjaxData () {
		const files = Object.values( this.uploader.files );

		return {
			value: files.map( file => this.$form.find( `#file-${ file.id }` ).val() ),
		};
	}

	/**
	 * Cancel the error message from the state and let them come from validate function
	 *
	 * @return {string} Submit error message
	 */
	getSubmitErrorMessage () {
		return '';
	}
}

module.exports = StateUpload;
