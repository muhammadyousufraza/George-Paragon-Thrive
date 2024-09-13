( $ => {
	const TCBSeoPlugin = require( './tcb-seo-plugin' );

	module.exports = class TCBYoastPlugin extends TCBSeoPlugin {
		init() {
			YoastSEO.app.registerPlugin( 'tcbYoastPlugin', {status: 'loading'} );

			this.fetchContent();
		}

		sendContent( fetchedContent ) {
			YoastSEO.app.pluginReady( 'tcbYoastPlugin' );

			/**
			 * @param  modification {string}    The name of the filter
			 * @param  callable     {function}  The callable
			 * @param  pluginName   {string}    The plugin that is registering the modification.
			 * @param  priority     {number}    (optional) Used to specify the order in which the callables
			 *                      associated with a particular filter are called. Lower numbers
			 *                      correspond with earlier execution.
			 */
			YoastSEO.app.registerModification( 'content', content => this.parseTCBContent( content, fetchedContent ), 'tcbYoastPlugin', 5 );
		}

		parseTCBContent( content, architectContent ) {
			/* Remove empty tags because yoast kind fails on parse here */
			if ( architectContent ) {
				const contentSelector = '.tcb-style-wrap',
					$content = $( `<div>${architectContent}</div>` ).find( contentSelector );

				$content.find( '*:empty:not(img,input,br)' ).remove();

				architectContent = $content.html();
			}

			return architectContent ? architectContent : content;
		}

		afterFetch( response ) {
			this.sendContent( response );
		}
	}
} )( jQuery );

