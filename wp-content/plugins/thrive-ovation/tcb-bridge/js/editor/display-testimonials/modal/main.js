const DataModel = require( './models/data-model' ), /* this holds the current data state throughout all the modal pages */
	DataManager = require( './data-manager' ),
	Utils = require( './utils' ),
	ElementUtils = require( '../utils' ),
	viewMap = {
		splash: require( './splash' ),
		static: require( './static/main' ),
		dynamic: require( './dynamic/main' ),
		set: require( './set/main' ),
	},
	SetCollection = Backbone.Collection.extend( {
		model: require( './models/set' ),
	} );

module.exports = TVE.modal.base.extend( {
	beforeInitialize( attr ) {
		this.cloudModalInstance = attr.cloudModalInstance;
		this.canSwitchToTemplates = attr.canSwitchToTemplates;

		/* this persists through the modal pages and we rely on it to get the state and data */
		this.dataModel = new DataModel();

		this.setCollection = new SetCollection();

		this.listenTo( this.dataModel, 'change:page', model => this.renderPage( model.get( 'page' ) ) )
			/* update the set cache after any changes */
			.listenTo( this.setCollection, 'add remove change update', () => DataManager.updateSetCache( this.setCollection.toArray() ) )
			/* various loaders */
			.listenTo( this.dataModel, 'start-loading', () => Utils.toggleLoading( this.$( '.thrive-display-testimonials-inner' ) ) )
			.listenTo( this.dataModel, 'finish-loading', () => {
				Utils.toggleLoading( this.$( '.thrive-display-testimonials-inner' ), false );
				this.close();
			} );

		this.setCollection.reset( DataManager.getSets() );
	},

	backToTemplates() {
		this.close();

		TVE.ActiveElement.attr( 'data-ct', `display_testimonials-${TVE.ActiveElement.attr( 'data-cloud-template' )}` );

		this.cloudModalInstance.open( {
			element: TVE.ActiveElement,
		},
		);
	},
	afterInitialize() {
		this.$el.on( 'click', event => {
			/* Notify the inner components that something was clicked inside the modal */
			TVE.do_action( 'tvo.display_testimonial.modal_clicked', event );
		} );
	},
	before_open() {
		let query = TVE.PostList.utils.readQueryFromElement();
		const setId = query.set_id,
			isSet = typeof setId !== 'undefined';

		/* first we set the page to splash, and if preloading something, we change it afterwards */
		this.dataModel.setPage( 'splash' );

		/* pre-select data if something is already set on the element */
		if ( isSet || typeof query.post__in !== 'undefined' || typeof query.tax_query !== 'undefined' ) {
			/* always initialize the sets before pre-loading existing data - we either need them for accessing the set data, or for populating the Navigation select */
			if ( isSet ) {
				const set = this.setCollection.get( setId );

				/* the set might have been deleted in the meantime */
				if ( typeof set !== 'undefined' ) {
					this.initializeExistingData( set.getType(), set.getItems(), set.getOrdering(), setId );
				}
			} else {
				query = ElementUtils.parseQuery( query );

				this.initializeExistingData( query.type, query.items, query.ordering );
			}
		}

		const template = TVE.ActiveElement.attr( 'data-cloud-template' );

		if ( template ) {
			this.dataModel.set( 'template', template );
		}
	},
	renderPage() {
		const page = this.dataModel.getPage();

		this.$( '#thrive-display-testimonials' )
		    .removeClass( [ 'splash', 'static', 'dynamic', 'set' ] )
		    .addClass( page )
		    .html( ( new viewMap[ page ]( {
			    dataModel: this.dataModel,
			    setCollection: this.setCollection,
			    canSwitchToTemplates: this.canSwitchToTemplates,
		    } ) ).$el.addClass( 'thrive-display-testimonials-inner' ) );
	},
	initializeExistingData( type, items, ordering, setId ) {
		this.dataModel.initializeData( type, ordering, items )
		    .set( 'status', 'reorder', { silent: true } );

		if ( typeof setId !== 'undefined' ) {
			this.dataModel.set( 'set_status', 'view', { silent: true } );
		}

		this.dataModel.setPage( type, setId );
	},
	destroy() {
		this.stopListening();
		this.undelegateEvents();
		this.$el.off();
	},
	handle_keyup() {
		/* overwrite the default functionality, which is to close the modal on 'enter' */
	},
} );
