( $ => {
	module.exports = class TCBSeoPlugin {
		/**
		 * Fetch Post Content from TCB
		 */
		fetchContent() {
			$.ajax( {
				url: ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					post_id: TCB_Post_Edit_Data.post_id,
					action: 'tve_get_seo_content'
				}
			} ).done( response => {
				const $content = $( `<div>${response.content}</div>` );
				/* Remove TTB headers and footers from SEO analysis */
				$content.find( 'header#thrive-header, footer#thrive-footer, aside#theme-sidebar-section' ).remove();

				this.isEditedWithTar = response.is_edited_with_tar;
				this.afterFetch( $content[ 0 ].outerHTML );
			} );
		}

		afterFetch() {
			throw Error( 'Class should implement the afterFetch function' );
		}
	}
} )( jQuery );

