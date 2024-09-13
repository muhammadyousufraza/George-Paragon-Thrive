const Select = require( './select' );

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/order-options',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;
		this.list = attr.typeList;
		this.onTypeSelect = attr.onTypeSelect;
		this.onDirectionSelect = attr.onDirectionSelect;
		this.placeholderText = attr.placeholderText;

		/* When the ordering is changed to manual indirectly, we need to update the type select */
		this.listenTo( this.dataModel, 'change:ordering', model => {
			if ( model.isStatic() ) {
				const type = model.getOrderingOptions( 'type' );

				if ( type === 'manual' ) {
					this.typeSelect.setValue( type );
					this.onTypeSelect( type );
				}
			}
		} );
	},
	afterRender() {
		this.typeSelect = new Select( {
			el: this.$( '.type-select' ),
			model: new Backbone.Model( {
				list: this.list,
				placeholderText: this.placeholderText ? this.placeholderText : '',
				selected: this.dataModel.getOrderingOptions( 'type' ),
				className: 'no-search small',
			} ),
			onSelect: value => {
				this.dataModel.setOrderingOption( 'type', value );

				if ( typeof this.onTypeSelect === 'function' ) {
					this.onTypeSelect( value );
				}
			},
		} );

		( new Select( {
			el: this.$( '.direction-select' ),
			model: new Backbone.Model( {
				list: [
					{ label: 'Ascending', value: 'ASC' },
					{ label: 'Descending', value: 'DESC' },
				],
				placeholderText: 'Sort',
				selected: this.dataModel.getOrderingOptions( 'direction' ),
				className: 'no-search small',
			} ),
			onSelect: value => {
				this.dataModel.setOrderingOption( 'direction', value );

				if ( typeof this.onDirectionSelect === 'function' ) {
					this.onDirectionSelect( value );
				}
			},
		} ) );

		/**
		 * Disable/enable the offset input: if pagination is active, we must disable it, and if there's no pagination, we enable it
		 * Reason: offset and pagination are not compatible by default
		 */
		const paginationValue = TVE.ActiveElement.attr( 'data-pagination-type' ),
			hasPagination = typeof paginationValue !== 'undefined' && paginationValue !== 'none',
			$offsetContainer = this.$( '.input-container.offset' ),
			$offsetInput = $offsetContainer.find( '.starting-input' );

		$offsetContainer.toggleClass( 'disabled-container', hasPagination );
		$offsetInput.toggleClass( 'tcb-disabled', hasPagination );

		if ( hasPagination ) {
			$offsetContainer.attr( 'data-tooltip', 'Not available while pagination is enabled' );
		} else {
			$offsetContainer.removeAttr( 'data-tooltip' );
		}
	},
	onOrderInputChange( event ) {
		const type = event.target.dataset.type,
			minimumAcceptedValue = type === 'offset' ? 0 : 1;
		let value = parseFloat( event.target.value );

		/* validation */
		if ( ( value < minimumAcceptedValue ) || ( value > 1000 ) || ! Number.isInteger( value ) ) {
			value = this.dataModel.getOrderingOptions( 'number_of_items' );

			TVE.$( event.target ).val( parseInt( value ) );
		}

		this.dataModel.setOrderingOption( type, value );
	},
	toggleVisibility( type ) {
		this.$( '.direction-select, .display-input, .starting-input, .order-text' ).toggleClass( 'tcb-hidden', type === 'manual' );
	},
} );
