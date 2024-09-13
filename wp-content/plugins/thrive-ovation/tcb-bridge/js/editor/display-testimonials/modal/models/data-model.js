const Utils = require( '../utils' ),
	ElementUtils = require( './../../utils' );

module.exports = Backbone.Model.extend( {
	setPage( page, id ) {
		if ( page === 'static' && typeof id !== 'undefined' ) {
			this.set( 'status', 'reorder', { silent: true } );
		}

		this.set( {
			set_id: id,
			page,
		}, { silent: true },
		).trigger( 'change:page', this );
	},
	getPage() {
		return this.get( 'page' );
	},
	viewSet( set ) {
		this.initializeSetData( set )
		    .set( 'set_status', 'view', { silent: true } )
		    .setPage( set.getType(), set.get( 'id' ) );
	},
	editSet( set ) {
		this.initializeSetData( set )
		    .set( 'set_status', 'edit', { silent: true } )
		    .setPage( set.getType(), set.get( 'id' ) );
	},
	/**
	 * Initialize the data model with the set data (usually in preparation for viewing or editing the set)
	 *
	 * @param {Backbone.Model} set
	 */
	initializeSetData( set ) {
		this.initializeData( set.getType(), set.getOrdering(), set.getItems() );
		return this;
	},
	/**
	 *
	 * @param  type
	 * @param  ordering
	 * @param  items
	 * @return {exports}
	 */
	initializeData( type, ordering, items ) {
		this.set( { type, ordering }, { silent: true } ).preselectItems( items );

		return this;
	},
	getSetId() {
		return this.get( 'set_id' );
	},
	getStatus() {
		return this.get( 'status' );
	},
	getSetStatus() {
		return this.get( 'set_status' );
	},
	isSelectMode() {
		return this.getStatus() === 'select';
	},
	isReorderMode() {
		return this.getStatus() === 'reorder';
	},
	isViewMode() {
		return this.getSetStatus() === 'view';
	},
	isEditMode() {
		return this.getSetStatus() === 'edit';
	},
	getOrderingOptions( key ) {
		let orderingOptions = this.get( 'ordering' );

		if ( ! orderingOptions ) {
			orderingOptions = Utils.getDefaultOrderingOptions( this.isStatic() );

			this.set( 'ordering', orderingOptions, { silent: true } );
		}

		return key ? orderingOptions[ key ] : orderingOptions;
	},
	setOrderingOption( key, value ) {
		const orderingOptions = this.getOrderingOptions();

		orderingOptions[ key ] = value;

		this.set( 'ordering', orderingOptions, { silent: true } )
		    .trigger( 'change:ordering', this );
	},
	getItemIds() {
		let items;

		if ( this.isStatic() ) {
			const preselectedIds = this.get( 'preselected_ids' );

			if ( typeof preselectedIds === 'undefined' ) {
				const selectedCollection = this.getSelectedCollection();

				items = selectedCollection ? selectedCollection.map( model => model.get( 'ID' ) ) : [];
			} else {
				items = preselectedIds;
			}
		} else {
			items = this.getSelectedTags();
		}

		return items;
	},
	preselectItems( ids ) {
		this.set( this.isStatic() ? 'preselected_ids' : 'tags', ids );
	},
	isStatic() {
		return this.get( 'type' ) === 'static';
	},
	getSelectedCollection() {
		return this.get( 'selected_collection' );
	},
	getSelectedTags() {
		return this.get( 'tags' ) ? this.get( 'tags' ).map( tag => parseInt( tag ) ) : [];
	},
	getCount() {
		return this.isStatic() ? this.getItemIds().length : this.get( 'dynamic-set-item-count' );
	},
	clearData() {
		const templateValue = this.get( 'template' );

		this.clear( { silent: true } )
			/* keep the value of the template */
			.set( 'template', templateValue );
	},
	/**
	 * This is the main exit point of the modal.
	 * We can arrive here from the static or dynamic page, where the items and ordering data are already set on the model
	 * We can also arrive here from the splash page, by clicking 'load' on a set.
	 */
	load() {
		this.trigger( 'start-loading' );

		const query = ElementUtils.computeQuery( this.getItemIds(), this.isStatic(), this.getOrderingOptions(), this.getSetId() ),
			template = this.get( 'template' );

		if ( template ) {
			ElementUtils.fetchCloudTemplate( template, query ).then( () => {
				this.trigger( 'finish-loading' );
				this.set( 'template', null );
			} );
		} else {
			ElementUtils.applyQuery( query ).then( response => {
				ElementUtils.applyQueryCallback( response );

				this.trigger( 'finish-loading' );
			} );
		}
	},
} );
