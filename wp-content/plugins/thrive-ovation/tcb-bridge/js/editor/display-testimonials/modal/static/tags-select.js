/* select2 adapters needed to change the functionality of the dropdown */
const Utils = TVE.$.fn.select2.amd.require( 'select2/utils' ),
	Dropdown = TVE.$.fn.select2.amd.require( 'select2/dropdown' ),
	DropdownSearch = TVE.$.fn.select2.amd.require( 'select2/dropdown/search' ),
	AttachBody = TVE.$.fn.select2.amd.require( 'select2/dropdown/attachBody' );

const dropdownAndSearch = Utils.Decorate( Dropdown, DropdownSearch ),
	/* this adds a search bar inside the dropdown */
	dropdownAdapter = Utils.Decorate( dropdownAndSearch, AttachBody );

const select2Utils = TVE.Select2Utils,
	placeholderText = 'Search tags',
	TagsSelect = {
		/**+
		 * @param $select
		 * @param onSelect
		 */
		initSelect( $select, onSelect ) {
			this.$select = $select;

			const options = {
				...select2Utils.getDefaultOptions( {
					placeholder: 'All tags',
					dropdownAdapter,
					onOpen: () => {
						TVE.$( 'body > .select2-container--open' ).addClass( 'tvo-tags-select' )
						   .find( 'input' ).attr( 'placeholder', placeholderText );

						this.$select.parent().find( '.select2-search__field' ).attr( 'disabled', 1 );
					},
				} ),
			};

			options.ajax = {
				...options.ajax,
				...{
					url: TVE.CONST.routes.base + '/display-testimonials/tags',
					data: params => ( {
						search: params.term,
					} ),
					processResults: results => {
						const selectOptions = [];

						results.forEach( data => {
							selectOptions.push( {
								id: data.value,
								value: data.value,
								text: data.label,
							} );
						} );

						return { results: selectOptions };
					},
				},
			};

			select2Utils.initSelect2( this.$select, options, event => this.selectCallback( event, onSelect ) );
		},
		/**
		 * @param  event
		 * @param  onSelect
		 */
		selectCallback( event, onSelect ) {
			/* this has to be jQuery because `target.value` gives a different result than `$target.val()` */
			const $selectContainer = TVE.$( event.target ),
				values = $selectContainer.val() ? $selectContainer.val().toString() : '';

			TagsSelect.refreshSelect2Visuals( values, TVE.$( event.target ) );

			onSelect( values );
		},
		/**
		 * @param  values
		 * @param  $select
		 */
		refreshSelect2Visuals( values, $select = this.$select ) {
			const $container = $select.parent(),
				$list = $container.find( '.select2-selection__rendered' ),
				truncatedList = [];

			$container.toggleClass( 'select-has-selection', values.length > 0 );

			const getItems = ( visibleOnly = false ) => $list.children( `.select2-selection__choice${ visibleOnly ? ':visible' : '' }` ),
				getItemWidth = () => getItems( true )
					.toArray().map( element => TVE.$( element ).outerWidth( true ) )
					.reduce( ( total, itemWidth ) => total += itemWidth, 0 );

			/* start by showing all the items, then if the width exceeds the maximum allowed width, hide the items at the end and move them to the tooltip */
			getItems().show();

			const maximumItemWidth = $list.width() -
			                         35 - /* limit dot width */
			                         30; /* dropdown arrow and paddings */

			while ( getItemWidth() > maximumItemWidth ) {
				const $lastVisibleItem = getItems( true ).last();

				$lastVisibleItem.hide();
				truncatedList.push( $lastVisibleItem.text().replace( '×', '' ) );
			}

			if ( truncatedList.length > 0 ) {
				this.initializeTruncatedTooltip( truncatedList, $list );
			}
		},
		/**
		 * Some black magic to truncate overflowing results and display a dot + tooltip instead.
		 *
		 * @param  truncatedList
		 * @param  $list
		 */
		initializeTruncatedTooltip( truncatedList, $list ) {
			/* remove previous left-overs */
			$list.find( '.truncated-dot' ).remove();
			TVE.$( '.select2-truncated-values-tooltip' ).remove();

			const $truncatedDot = TVE.$( `<li class="truncated-dot">+${ truncatedList.length }</li>` ),
				$tooltip = TVE.$( '<ul class="select2-truncated-values-tooltip"></ul>' );

			$list.append( $truncatedDot );

			$truncatedDot.on( 'mouseover', () => $tooltip.css( 'visibility', 'visible' ) )
			             .on( 'mouseleave', () => $tooltip.css( 'visibility', 'hidden' ) );

			truncatedList.forEach( item => $tooltip.append( `<li>${ item }</li>` ) );

			TVE.$( 'body' ).append( $tooltip );

			const dotOffset = 90;

			$tooltip.css( {
				top: $truncatedDot.offset().top + $truncatedDot.height() + 15 + 'px',
				left: $truncatedDot.offset().left - dotOffset + 'px',
				width: '150px',
			} );

			TVE.$( 'body' )[ 0 ].style.setProperty( '--custom-select-limit-dot-offset', `${ dotOffset + 8 }px` );
		},
		/**
		 * Close any select2s that remained open
		 */
		closeSelect2() {
			select2Utils.closeSelect2( this.$select );
		},
	};

module.exports = TagsSelect;
