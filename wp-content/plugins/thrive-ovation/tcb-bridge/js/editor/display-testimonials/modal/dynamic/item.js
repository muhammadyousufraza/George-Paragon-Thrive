module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/tag-item',
	tagName: 'li',
	tagCheckboxChanged( event ) {
		let checkbox;

		if ( event.target.type === 'checkbox' ) {
			checkbox = event.target;
		} else {
			checkbox = event.target.closest( 'li' ).getElementsByTagName( 'input' );
			/* when the row is clicked, manually activate the checkbox click event */
			checkbox[ 0 ].click();
			return;
		}

		this.model.set( 'selected', checkbox.checked ? 1 : 0 );
	},
} );
