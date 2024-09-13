import ApiRequest from '../../utils/ApiRequest';
import { ucFirst } from "@/utils/data-fn";

const Automations = new ApiRequest( 'automations' );
/**
 * Hold automation state
 * Also handles automations steps and filters
 */
export default {
	namespaced: true, /**
	 * STATE
	 */
	state: {
		automationsFilters: {
			title: '',
			status: [],
			triggers: [],
			pagination: 1,
		},
		automations: '',
	}, /**
	 * ACTIONS
	 */
	actions: {

		async fetchAutomations( {commit} ) {
			Automations.getAll().then( automations => {
				commit( 'setAutomations', automations );
			} )
		},

		toggleFilter( {commit}, payload ) {
			commit( 'toggleFilter', payload );
		},
		resetFilters( {commit} ) {
			commit( 'resetFilters' );
		}
	}, /**
	 * GETTERS
	 */
	getters: {
		getAutomations: state => state.automations,
		getAutomationFilters: state => state.automationsFilters,
		getFilteredAutomations: ( state, getters, rootState, rootGetters ) => {
			let automations = [ ...state.automations ];
			Object.keys( state.automationsFilters ).forEach( type => {
				if ( state.automationsFilters[ type ].length ) {
					switch ( type ) {
						case 'status':
							automations = automations.filter( aut => state.automationsFilters[ type ].includes( aut.status ) )
							break;
						case 'triggers':
							automations = automations.filter( aut => {
								const triggers = aut.content[ 0 ].data;
								return Object.keys( triggers ).some( key => {
									let appName = triggers[ key ]?.app_name;
									if ( triggers[ key ]?.app_id ) {
										if ( rootGetters[ 'generic/getApps' ][ triggers[ key ].app_id ] ) {
											appName = rootGetters[ 'generic/getApps' ][ triggers[ key ].app_id ].name;
										} else {
											appName = ucFirst( triggers[ key ].app_id );
										}
									}
									return state.automationsFilters[ type ].includes( appName )
								} )
							} );
							break;
						default:
							break;
					}
				}

			} );
			if ( state.automationsFilters.title ) {
				automations = automations.filter( aut => aut.title.toLowerCase().includes( state.automationsFilters.title.toLowerCase() ) )
			}
			return automations;
		},
		getPaginationAutomations: ( state, getters ) => {
			const page = getters.getAutomationFilters.pagination - 1, startIndex = page * 10;

			return getters.getFilteredAutomations.slice( startIndex, startIndex + 10 );
		},
	}, /**
	 * MUTATIONS
	 */
	mutations: {

		addAutomation: ( state, automation ) => state.automations.unshift( automation ),

		updateAutomation: ( state, updatedAut ) => {
			const index = state.automations.findIndex( automation => automation.id === updatedAut.id );

			if ( index !== - 1 ) {
				state.automations.splice( index, 1, {...state.automations[ index ], ...updatedAut} );
			}
		},

		deleteAutomation: ( state, deleteID ) => state.automations = state.automations.filter( aut => aut.id !== deleteID ),

		setAutomations: ( state, automations ) => {
			//sort them so the published are first
			state.automations = automations.sort( ( aut1, aut2 ) => aut2.status.localeCompare( aut1.status ) );
		},

		toggleFilter: ( state, payload ) => {

			if ( [ 'title', 'pagination' ].includes( payload.type ) ) {
				state.automationsFilters[ payload.type ] = payload.filter;
			} else {
				state.automationsFilters[ payload.type ] = state.automationsFilters[ payload.type ].includes( payload.filter ) ? [] : [ payload.filter ];
			}
			//have to reset pagination while other types are changed
			if ( payload.type !== 'pagination' ) {
				state.automationsFilters.pagination = 1
			}
		},

		resetFilters: state => state.automationsFilters = {
			title: '', status: [], triggers: [], pagination: 1,
		},
	}
};
