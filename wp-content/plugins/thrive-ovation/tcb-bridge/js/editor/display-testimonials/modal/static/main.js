const DataManager = require( '../data-manager' ),
	Utils = require( '../utils' ),
	Collection = require( '../models/testimonial-collection' ),
	Constants = require( '../constants' );

module.exports = TVE.Views.Base.base_view.extend( {
	template: 'tve-display-testimonials/modal/pages/static',
	beforeInitialize( attr ) {
		this.dataModel = attr.dataModel;
		this.setCollection = attr.setCollection;
		this.fetchedData = 0;
		this.itemsPerPage = Constants.ITEMS_PER_PAGE;

		if ( ! this.dataModel.get( 'status' ) ) {
			this.dataModel.set( 'status', 'select' );
		}

		/* If we have any preselected ids, they are available in this.dataModel.get( 'preselected_ids' ) and will be processed in initializeCollection()  */
		const selectedCollection = new Collection( [] );

		this.dataModel.set( 'selected_collection', selectedCollection );
		this.setDisplayedCollection( selectedCollection );

		this.listenTo( this.dataModel, 'change:status change:set_status', this.render )
		    .listenTo( this.dataModel, 'page-changed', page => this.fetchTestimonialPage( null, page ) )
		    .listenTo( this.dataModel, 'items-per-page-changed', itemsPerPage => {
			    this.itemsPerPage = itemsPerPage;
			    this.fetchTestimonialPage( response => this.initializePagination( response.total ) );
		    } )
		    .listenTo( this.displayedCollection, 'change:selected', ( model, isSelected ) => {
			    const maxOrderValue = this.dataModel.get( 'maximum_order' );

			    this.getSelectedCollection()[ isSelected ? 'add' : 'remove' ]( model, { silent: true } ); /* avoid activating the add/remove events with this */

			    if ( isSelected && maxOrderValue ) {
				    model.set( 'order', maxOrderValue );
			    }

			    this.toggleOrderButtonAvailability();

			    this.dataModel.trigger( 'change:selected_collection' );
		    } );
	},
	afterRender() {
		this.dataModel.set( 'type', 'static' );

		this.$listContainer = this.$( '.thrive-display-testimonials-list-container' );
		this.$list = this.$listContainer.find( '.thrive-display-testimonials-static-list' );
		this.$pagination = this.$( '.testimonials-pagination-container' );

		this.initializeNav();
		this.initializeOptions();

		if ( this.dataModel.isSelectMode() ) {
			this.initializeFilters();
		} else {
			this.initializeOrderingOptions();
		}

		this.list = new ( require( './list' ) )( {
			el: this.$list[ 0 ],
			collection: this.displayedCollection,
			dataModel: this.dataModel,
		} );

		this.initializeCollection();

		if ( this.dataModel.isSelectMode() && ! this.dataModel.get( 'set_id' ) ) {
			DataManager.getTestimonialCount( this.filters ).then( count => {
				this.initializePagination( count );

				this.$pagination.toggleClass( 'tcb-hidden', false );
			} );
		}
	},
	initializeCollection() {
		const preselectedIds = this.dataModel.get( 'preselected_ids' );

		/* we only have to fetch data once per instance, when it is first opened. After that, it is fetched through other mechanisms */
		if ( ! this.fetchedData ) {
			this.fetchedData = 1;

			this.toggleListLoader();
			this.$pagination.toggleClass( 'tcb-hidden', true );

			DataManager.getTestimonials( preselectedIds ? preselectedIds : [], this.itemsPerPage ).then( response => {
				const testimonials = response.items;

				if ( preselectedIds ) {
					testimonials.forEach( testimonial => testimonial.selected = 1 );
				}

				this.setDisplayedCollection( testimonials );

				if ( preselectedIds ) {
					this.dataModel.unset( 'preselected_ids' )
					    .set( 'selected_collection', this.displayedCollection.clone() );
				}

				this.dataModel.trigger( 'refresh-count' );

				this.toggleListLoader( false );
				this.$pagination.toggleClass( 'tcb-hidden', false );
				this.toggleOrderButtonAvailability();

				if ( testimonials.length === 0 ) {
					this.showNoTestimonialsMessage();
				}
			} );
		} else {
			if ( this.displayedCollection.length === 0 ) {
				this.showNoTestimonialsMessage();
			}
			this.toggleOrderButtonAvailability();
		}
	},

	showNoTestimonialsMessage() {
		this.$( 'button[data-fn="load"], button[data-fn="openSetPage"], button[data-fn="updateSet"]' ).tcbAddClass( 'tcb-disabled' );
		this.$( '.thrive-display-testimonials-static-list' ).hide();
		this.$( '.no-testimonials' ).show();
	},
	toggleOrderButtonAvailability() {
		const selectedCollection = this.getSelectedCollection();

		this.$( '[data-fn="switchToReorderingMode"]' ).toggleClass( 'tcb-disabled', selectedCollection && selectedCollection.length === 0 );
	},
	initializeNav() {
		if ( this.nav ) {
			this.nav.remove();
		}

		this.nav = new ( require( '../nav' ) )( {
			el: this.$( '.thrive-display-testimonials-set-selector.static' )[ 0 ],
			dataModel: this.dataModel,
			selectedValue: this.dataModel.get( 'set_id' ) ? this.dataModel.get( 'set_id' ) : 'static',
			selectCallback: () => this.clear(),
			setCollection: this.setCollection,
		} );
	},
	initializeOptions() {
		if ( this.optionsView ) {
			this.optionsView.remove();
		}

		this.optionsView = new ( require( './options' ) )( {
			el: this.$( '.thrive-display-testimonials-options' )[ 0 ],
			dataModel: this.dataModel,
		} );

		this.listenTo( this.dataModel, 'change:selected_all', newModel => {
			/* either check all the checkboxes or uncheck them all */
			const selectedValue = newModel.get( 'selected_all' );

			this.displayedCollection.each( model => model.set( 'selected', selectedValue ) );
		} );
	},
	initializePagination( totalItems ) {
		if ( this.paginationView ) {
			this.paginationView.remove();
		}

		if ( totalItems !== 0 ) {
			this.paginationView = new ( require( './pagination' ) )( {
				el: TVE.$( '<span class="testimonials-pagination"></span>' ).appendTo( this.$pagination )[ 0 ],
				dataModel: this.dataModel,
				itemsPerPage: this.itemsPerPage,
				totalItems,
			} );
		}
	},
	initializeFilters() {
		if ( this.filtersView ) {
			this.filtersView.remove();
		}

		const filterModel = new Backbone.Model();

		this.filters = {};
		this.$filterContainer = this.$( '.filter-container' );
		this.filtersView = new ( require( './filters' ) )( {
			el: this.$filterContainer[ 0 ],
			model: filterModel,
		} );

		/* whenever something changes in the filter model, query the testimonials again and return to the first page */
		this.listenTo( filterModel, 'change', newFilterModel => {
			const noResultsContainer = this.$( '.no-results' ),
				firstGroup = this.$( '.first-group' );

			this.filters = newFilterModel.toJSON();
			this.fetchTestimonialPage( response => {
				this.initializePagination( response.total );

				noResultsContainer.toggle( response.total === 0 );
				firstGroup.toggle( response.total !== 0 );
			} );
		} );
	},
	initializeOrderingOptions() {
		if ( this.orderOptionsView ) {
			this.orderOptionsView.remove();
		}

		const sortList = direction => {
			const ids = Utils.deepCopy( this.dataModel.getItemIds() );

			ids.sort( ( a, b ) => ( direction === 'ASC' ? 1 : -1 ) * ( a - b ) );
			this.list.sortItems( ids, true );
		};

		this.$orderContainer = this.$( '.static-order-container' );
		this.orderOptionsView = new ( require( '../order-options' ) )( {
			el: this.$orderContainer[ 0 ],
			dataModel: this.dataModel,
			typeList: [
				{ label: 'Manual', value: 'manual' },
				{ label: 'Date Published', value: 'date' },
			],
			onTypeSelect: value => {
				if ( value === 'date' ) {
					sortList( this.dataModel.getOrderingOptions( 'direction' ) );
				} else {
					/* nothing to do here, the current order is kept */
				}
				this.orderOptionsView.toggleVisibility( value );
			},
			onDirectionSelect: sortList,
		} );

		this.orderOptionsView.toggleVisibility( this.dataModel.getOrderingOptions( 'type' ) );
	},
	load() {
		if ( ! this.isValid() ) {
			return;
		}

		this.dataModel.load();
	},
	switchToEditMode() {
		this.dataModel.set( 'set_status', 'edit' );
	},
	switchToReorderingMode() {
		/* when reordering, the displayed collection should contain only the selected items */
		this.setDisplayedCollection( this.getSelectedCollection() );

		this.dataModel.set( 'status', 'reorder' );
	},
	switchToSelectionMode() {
		this.fetchTestimonialPage( () => this.dataModel.set( 'status', 'select' ) );
	},
	goBackToSplash() {
		this.clear();
		this.dataModel.setPage( 'splash' );
	},
	clear() {
		this.dataModel.clearData();

		this.optionsView.remove();

		if ( this.orderOptionsView ) {
			this.orderOptionsView.remove();
		}
		this.remove();
	},
	openSetPage() {
		if ( ! this.isValid() ) {
			return;
		}

		/* destroy the view because we're leaving the page */
		this.remove();

		this.dataModel.setPage( 'set', this.dataModel.getSetId() ); /* if we're currently editing a set, memorize the set ID for caching purposes */
	},
	updateSet() {
		/* when saving the set, update the displayed collection */
		this.setDisplayedCollection( this.getSelectedCollection() );

		const setInstance = this.setCollection.get( this.dataModel.get( 'set_id' ) );

		setInstance
			.setItems( this.dataModel.getItemIds() )
			.save( null, {
				wait: true,
				success: ( model, response ) => {
					setInstance.set( 'count', response.count );

					TVE.page_message( 'Testimonial set updated.' );

					this.dataModel.set( 'set_status', 'view' );
				},
				error: () => TVE.page_message( 'Error on updating the testimonial set.', true ),
			} );
	},
	isValid() {
		return this.getSelectedCollection().length > 0;
	},
	getSelectedCollection() {
		return this.dataModel.getSelectedCollection();
	},
	fetchTestimonialPage( afterFetchFn = null, page = 1 ) {
		this.toggleListLoader();

		DataManager.getTestimonials( [], this.itemsPerPage, this.filters, page ).then( response => {
			const selectedIds = this.dataModel.getItemIds(),
				testimonials = response.items;

			/* re-select what was previously selected */
			testimonials.forEach( testimonial => testimonial.selected = selectedIds.includes( testimonial.ID ) ? 1 : 0 );

			this.setDisplayedCollection( testimonials );

			this.toggleListLoader( false );

			if ( typeof afterFetchFn === 'function' ) {
				afterFetchFn( response );
			}
		} );
	},
	/**
	 * Sets the values for the collection and also re-renders the list through resetting.
	 *
	 * @param {Collection|Object} newValue
	 */
	setDisplayedCollection( newValue ) {
		if ( ! this.displayedCollection ) {
			this.displayedCollection = new Collection( [] );
		}

		this.displayedCollection.reset( newValue instanceof Collection ? newValue.clone().models : newValue );
	},
	toggleListLoader( loading = true ) {
		Utils.toggleLoadingSpinner( this.$listContainer, '.first-group, .thrive-display-testimonials-inner-container', '.no-results', loading );
	},
	toggleFilters() {
		const isVisible = ! this.$filterContainer.hasClass( 'tcb-hidden' ),
			$filtersButton = this.$( '.filter-toggler' );

		this.$filterContainer.toggleClass( 'tcb-hidden', isVisible );
		$filtersButton.toggleClass( 'filled', ! isVisible );
	},
	toggleOrderOptions() {
		const isVisible = ! this.$orderContainer.hasClass( 'tcb-hidden' ),
			$orderButton = this.$( '.order-toggler' );

		this.$orderContainer.toggleClass( 'tcb-hidden', isVisible );
		$orderButton.toggleClass( 'filled', ! isVisible );
	},
	toggleSetSelectorDropdown() {
		this.nav.$el.find( '[data-fn="selectToggle"]' ).trigger( 'click' );
	},
} );
