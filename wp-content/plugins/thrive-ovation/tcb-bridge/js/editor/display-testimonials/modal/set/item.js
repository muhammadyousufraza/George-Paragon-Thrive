let escPressed = false;

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/set-item',
	tagName: 'li',
	className: 'set-item',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;

		this.listenTo( this.model, 'destroy', this.remove );
		this.listenTo( this.model, 'change:label', this.onLabelChange );
	},
	afterRender() {
		this.$el.attr( {
			'data-id': this.model.get( 'id' ),
			'data-type': this.model.getType(),
		} );
	},
	loadSet() {
		this.dataModel.initializeSetData( this.model )
		    .set( 'set_id', this.model.get( 'id' ), { silent: true } )
		    .load();
	},
	viewSet() {
		this.$el.parents( 'body' ).find( '.tvd-material-tooltip' ).removeClass( 'show' ); /* make sure we hide the tooltip */
		this.dataModel.viewSet( this.model );
	},
	editSet() {
		this.dataModel.editSet( this.model );
	},
	toggleDeleteSet() {
		this.$el.toggleClass( 'delete-overlay' );
	},
	deleteSet() {
		this.model.destroy( {
			success: () => TVE.page_message( 'Testimonial set deleted!' ),
			error: () => TVE.page_message( 'Testimonial set deletion failed.', true ),
		} );
	},
	/**
	 * @param  event
	 * @param  target
	 */
	showRename( event, target ) {
		const $label = TVE.$( target ).siblings( '.set-label' ),
			$labelContainer = $label.parent( '.set-name-container' );

		if ( $label.find( 'input' ).length === 0 ) {
			$label.html( `<input class="keydown blur" data-fn="renameSet" value="${this.model.get( 'label' )}">` ).find( 'input' ).focus();
		}

		/* hide the pen, show the checkmark */
		$labelContainer.addClass( 'label-edit-mode' );
	},
	renameSet( event ) {
		if ( event.type === 'keydown' ) {
			escPressed = ( event.keyCode === 27 );
			/* on enter press, we save, on anything else we skip the save */
			if ( event.keyCode !== 13 ) {
				/* on ESC press, we re render and exit edit mode. */
				event.keyCode === 27 && this.onLabelChange();

				/* when we don't press enter, we skip the save */
				return;
			}
		}

		/* in case we press ESC, keydown and blur will be both called, but we want to cancel the blur event */
		if ( escPressed ) {
			escPressed = false;
			this.onLabelChange();
			return;
		}

		const label = this.$( 'input' ).val().trim();

		/* if the name didn't change, return without doing a toast */
		if ( label === this.model.get( 'label' ) ) {
			this.onLabelChange();
			return;
		}

		if ( ! label || label.length === 0 ) {
			this.onLabelChange();
			return;
		}

		this.model
		    .set( 'label', label )
		    .save( null, {
			    wait: true,
			    success: () => TVE.page_message( 'Testimonial set renamed.' ),
			    error: () => TVE.page_message( 'Error on renaming the testimonial set.', true ),
		    } );
	},
	onLabelChange() {
		this.$( '.set-label' ).html( this.model.get( 'label' ) );
		this.$( '.label-edit-mode' ).removeClass( 'label-edit-mode' );
	},
} );
