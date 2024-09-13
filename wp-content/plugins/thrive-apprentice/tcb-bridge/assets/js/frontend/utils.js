module.exports = {
	/**
	 * @param {Function} func
	 * @param {int} wait
	 * @param {boolean} immediate
	 * @return {function(): void}
	 */
	debounce ( func, wait, immediate ) {
		let timeout;
		return function () {
			const context = this, args = arguments;
			const later = function () {
				timeout = null;
				if ( ! immediate ) {
					func.apply( context, args );
				}
			};
			const callNow = immediate && ! timeout;
			clearTimeout( timeout );
			timeout = setTimeout( later, wait );
			if ( callNow ) {
				func.apply( context, args );
			}
		};
	},
	/**
	 * @see (php) TCB_Utils::restore_post_waf_content()
	 *
	 * @param {string} content
	 * @return {string} Prepare a POST field in order to not trigger a WordFence alert.
	 * Currently handles <svg> tags
	 */
	prepareWordfencePostContent ( content ) {
		return content.replace( /(<|<\/)svg/g, '$1_wafsvg_' )
	}
};
