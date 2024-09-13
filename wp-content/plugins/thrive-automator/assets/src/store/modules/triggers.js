import ApiRequest from "@/utils/ApiRequest";

const API = new ApiRequest( 'triggers' );
const Limitations = new ApiRequest( 'limitations' );
/**
 * Handle for trigger state
 */
export default {
	namespaced: true, /**
	 * STATE
	 */
	state: {
		triggers: {},
		limitations: [],
	}, /**
	 * ACTIONS
	 */
	actions: {
		async fetchTriggers( {commit} ) {
			return API.getAll().then( triggers => {
				commit( 'setTriggers', triggers );
			} )
		},

		async syncTriggerData( {commit}, payload ) {
			return API.updateOne( payload.id, {trigger_data: payload} )
		},

		async fetchLimitations( {commit}, id ) {
			return Limitations.getOne( id ).then( limitations => {
				commit( 'setLimitations', limitations );
			} )
		},

		async deleteTriggerLimitation( {commit}, payload ) {
			const {id, ...props} = payload;
			return Limitations.deleteByProps( id, props ).then( limitations => {
				commit( 'setLimitations', limitations );
			} )
		},

		resetLimitations( {commit} ) {
			commit( 'setLimitations', [] );
		}
	}, /**
	 * GETTERS
	 */
	getters: {
		getTriggers: state => state.triggers,
		getLimitations: state => state.limitations,
	}, /**
	 * MUTATIONS
	 */
	mutations: {
		setTriggers: ( state, triggers ) => state.triggers = triggers,

		setLimitations: ( state, limitations ) => state.limitations = limitations,
	},
};
