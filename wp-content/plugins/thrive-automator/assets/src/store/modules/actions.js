import ApiRequest from "@/utils/ApiRequest";

const API = new ApiRequest( 'actions' );
/**
 * Hold actions state
 */
export default {
	namespaced: true,
	/**
	 * STATE
	 */
	state: {
		actions: [],
	},
	/**
	 * ACTIONS
	 */
	actions: {
		async fetchActions( {commit} ) {
			return API.getAll().then( actions => {
				commit( 'setActions', actions );
			} )
		},

		async sendTestData( {commit}, payload ) {
			return API.sendWebhook( payload )
		},
	},
	/**
	 * GETTERS
	 */
	getters: {
		getActions: state => state.actions
	},
	/**
	 * MUTATIONS
	 */
	mutations: {
		setActions: ( state, actions ) => state.actions = actions,
	},
};
