import ApiRequest from "@/utils/ApiRequest";

const API = new ApiRequest( 'error_log' );

/**
 * Handle for trigger state
 */
export default {
	namespaced: true,
	/**
	 * STATE
	 */
	state: {
		errors: {},
		totalCount: 0
	},
	/**
	 * ACTIONS
	 */
	actions: {
		async fetchErrors( {commit}, payload = {} ) {
			return API.getOneByParams( payload ).then( response => {
				commit( 'setData', response );
			} )
		},
		async deleteError( {commit}, id ) {
			return API.deleteOne( id ).then( () => {
				commit( 'deleteError', id );
			} )
		},

		async setSettings( {commit}, payload = {} ) {
			return API.updateSettings( payload ).then( response => {
				commit( 'setData', response );
			} )
		},

		async deleteAll( {commit} ) {
			return API.deleteAll().then( () => {
				commit( 'setData', {logs: [], count: 0} );
			} )
		}
	},
	/**
	 * GETTERS
	 */
	getters: {
		getErrors: state => state.errors,
		getTotalCount: state => state.totalCount
	},
	/**
	 * MUTATIONS
	 */
	mutations: {
		setData: ( state, payload ) => {
			state.errors = payload.logs;
			state.totalCount = payload.count;
		},
		deleteError: ( state, deleteID ) => state.errors = state.errors.filter( error => error.id !== deleteID ),
	},
};
