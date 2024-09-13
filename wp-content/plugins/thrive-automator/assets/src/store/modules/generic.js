import ApiRequest from "@/utils/ApiRequest";
import apiFetch from "@wordpress/api-fetch";

/**
 * Set wp-rest nonce
 */
apiFetch.use( apiFetch.createNonceMiddleware( TAPAdmin.nonce ) );

const PrimaryFields = new ApiRequest( 'primary_fields' );
const DataObjects = new ApiRequest( 'data_objects' );
const AdvancedMapDataObjects = new ApiRequest( 'advanced_mapping_data_objects' );
const Filters = new ApiRequest( 'filters' );
const WebhookListener = new ApiRequest( 'webhook_listener' );
const Apps = new ApiRequest( 'apps' );
/**
 * Hold actions state
 */
export default {
	namespaced: true,
	/**
	 * STATE
	 */
	state: {
		noticeHeader: '',
		noticeShow: false,
		noticeMessage: '',
		primaryFields: [],
		filters: [],
		dataObjects: [],
		advancedMapDataObjects: [],
		apps: []
	},
	/**
	 * ACTIONS
	 */
	actions: {
		async listenWebhook( {commit}, id ) {
			return WebhookListener.getOneByParams( {webhook_id: id} );
		},

		async fetchDataObjects( {commit} ) {
			return DataObjects.getAll().then( dataObjects => {
				commit( 'setDataObjects', dataObjects );
			} )
		},

		setDataObject( {commit}, dataObject ) {
			commit( 'setDataObject', dataObject );
		},

		async fetchDataObjectValues( {commit}, id ) {
			return DataObjects.getOne( id )
		},

		async fetchPrimaryFields( {commit} ) {
			return PrimaryFields.getAll().then( fields => {
				commit( 'setPrimaryFields', fields );
			} )
		},

		async fetchAdvancedMapDataObjects( {commit} ) {
			return AdvancedMapDataObjects.getAll().then( fields => {
				commit( 'setAdvancedMapDataObjects', fields );
			} )
		},

		async fetchFilters( {commit} ) {
			return Filters.getAll().then( data => {
				const filters = {};
				Object.keys( data ).forEach( key => {
					filters[ key ] = {info: data[ key ]}
				} )
				commit( 'setFilters', filters );
			} )
		},

		async fetchApps( {commit} ) {
			return Apps.getAll().then( data => {
				commit( 'setApps', data );
			} )
		},

		setNoticeData( {commit}, data ) {
			commit( 'setNoticeData', data );
		}
	},
	/**
	 * GETTERS
	 */
	getters: {
		getPrimaryFields: state => state.primaryFields,
		getFilters: state => state.filters,
		getDataObjects: state => state.dataObjects,
		getAdvancedMapDataObjects: state => state.advancedMapDataObjects,
		getApps: state => state.apps,
		getNoticeHeader: state => state.noticeHeader,
		getNoticeMessage: state => state.noticeMessage,
		getNoticeShow: state => state.noticeShow
	},
	/**
	 * MUTATIONS
	 */
	mutations: {
		setNoticeData( state, data ) {
			state.noticeHeader = data.header;
			state.noticeMessage = data.message;
			state.noticeShow = data.show;
		},

		setPrimaryFields: ( state, fields ) => state.primaryFields = fields,
		setFilters: ( state, filters ) => state.filters = filters,
		setDataObjects: ( state, dataObjects ) => state.dataObjects = dataObjects,
		setAdvancedMapDataObjects: ( state, advancedMapDataObjects ) => state.advancedMapDataObjects = advancedMapDataObjects,

		setDataObject: ( state, dataObject ) => {
			state.dataObjects[ dataObject.id ] = {
				fields: dataObject.fields,
				name: dataObject.name || state.dataObjects?.[ dataObject.id ]?.name || '',
			}
		},
		setApps: ( state, apps ) => state.apps = apps
	},
};
