const TCBSeoPlugin = require( './tcb-seo-plugin' );

module.exports = class TCBRankMathPlugin extends TCBSeoPlugin {
	/**
	 * Init the custom fields
	 */
	init() {
		this.content = '';
		this.hooks();
		this.fetchContent();
	}

	/**
	 * Hook into Rank Math App eco-system
	 */
	hooks() {
		if ( window.wp && wp.hooks ) {
			wp.hooks.addFilter( 'rank_math_content', 'rank-math', content => {
				return this.content ? this.content : content;
			}, 11 );
		}
	}

	/**
	 * Fetch Post Content from TCB
	 *
	 * @param  fetchedContent
	 */
	sendContent( fetchedContent ) {
		if ( ! this.isEditedWithTar ) {
			this.content = fetchedContent;
		}
		if ( typeof window.rankMathEditor !== 'undefined' ) {
			rankMathEditor.refresh( 'content' );
		}
	}

	afterFetch( response ) {
		this.sendContent( response );
	}
};
