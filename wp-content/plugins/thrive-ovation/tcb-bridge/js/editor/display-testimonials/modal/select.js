/**
 * Based on select.js from conditional-display, with some creative differences
 */
module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/select',
	beforeInitialize( attr ) {
		/* bind the on select function if we receive one */
		if ( typeof attr.onSelect === 'function' ) {
			this.onSelect = attr.onSelect.bind( this );
		}

		TVE.add_action( 'tvo.display_testimonial.modal_clicked', event => {
			/* if the user clicks outside the select and the select is open, we close it */
			if ( this.$( event.target ).length === 0 && event.target.getAttribute( 'data-fn' ) !== 'toggleSetSelectorDropdown' ) {
				this.close();
			}
		} );

		this.listenTo( this.model, 'change:list', this.render );
	},
	afterRender() {
		this.$defaultValue = this.$( '.default-value' );

		const $list = this.$( 'ul' );

		_.each( this.model.get( 'list' ), item => {
			const classes = `${ item.disabled ? 'disabled' : 'click' }${ item.isSubOption ? ' sub-option' : '' }`;

			$list.append( `<li class="${ classes }" data-fn="setValue" data-value="${ item.value }">${ item.beforeContent ? item.beforeContent + ' ' : '' }${ item.label }</li>` );
		} );

		const selected = this.model.get( 'selected' );

		if ( typeof selected !== 'undefined' && selected ) {
			this.setValue( selected );
		} else if ( this.model.get( 'placeholderText' ).length ) {
			this.setPlaceholder();
		}
	},
	setPlaceholder() {
		this.$el.addClass( 'is-placeholder' );

		this.$defaultValue.removeAttr( 'data-value' ).html( this.model.get( 'placeholderText' ) );
	},
	/**
	 * Hide/Show select
	 */
	selectToggle() {
		if ( ! this.isOpen() ) {
			/* hide all the other selects when we open this one */
			this.$el.closest( '.tcb-modal-content' ).find( '.tvo-display-testimonials-select' ).removeClass( 'active' );
		}

		this.$el.toggleClass( 'active' );
	},
	/**
	 * Set value, either from a click event or directly by string value
	 *
	 * @param {string | Object} event
	 */
	setValue( event ) {
		this.$el.removeClass( 'is-placeholder' );

		let selectedValue = '';
		if ( typeof event === 'string' || typeof event === 'number' ) {
			selectedValue = event;
		} else {
			const target = event.currentTarget;

			/* if the target is already active, we don't have to do anything */
			if ( target.classList.contains( 'active' ) ) {
				this.close();
				return;
			}

			selectedValue = target.dataset.value;
		}
		/* get text name from the value */
		const label = this.$( 'ul li' ).removeClass( 'active' ).filter( `[data-value="${ selectedValue }"]` ).addClass( 'active' ).text();

		/* unset all active and make the current one active and also set it as default */
		this.$el.removeClass( 'active' ).find( '.default-value' ).attr( 'data-value', selectedValue ).html( label );

		if ( typeof event === 'object' ) {
			/* if the select was done by a click event, also trigger the on select function */
			this.onSelect( selectedValue, label, event );
		}
	},
	getValue() {
		return this.$defaultValue.attr( 'data-value' );
	},
	open() {
		this.$el.addClass( 'active' );
	},
	close() {
		this.$el.removeClass( 'active' );
	},
	isOpen() {
		return this.$el.hasClass( 'active' );
	},
	onSelect() {
		/* nothing by default */
	},
	setList( items ) {
		this.model.set( 'list', items )
			/* make sure this always triggers change */
			.trigger( 'change:list' );
	},
} );
