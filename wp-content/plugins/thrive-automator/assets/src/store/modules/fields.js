import ApiRequest from "@/utils/ApiRequest";
import { replaceStringSpecialChars } from "@/utils/ui-fn";

const FieldOptions = new ApiRequest( 'field_options' );

const ActionFieldsOptions = new ApiRequest( 'action_field_options' );
const ActionSubfields = new ApiRequest( 'action_field_steps' );
const ActionFieldsMapping = new ApiRequest( 'action_fields_mapping' );

const TriggerFields = new ApiRequest( 'trigger_field_options' );
const TriggerSubfields = new ApiRequest( 'trigger_field_steps' );


const fixNames = data => {
	if ( Array.isArray( data ) ) {
		data = data.map( item => {
			if ( item.label ) {
				item.label = replaceStringSpecialChars( item.label );
			}
			if ( item.name ) {
				item.name = replaceStringSpecialChars( item.name );
			}
			return item;
		} );
	} else if ( TAPAdmin._.isObject( data ) ) {
		Object.keys( data ).forEach( key => {
			if ( data[ key ].label ) {
				data[ key ].label = replaceStringSpecialChars( data[ key ].label );
			}
			if ( data[ key ].name ) {
				data[ key ].name = replaceStringSpecialChars( data[ key ].name );
			}
		} );
	}

	return data;
}

/**
 * Handles filters & actions fields data
 */
export default {
	namespaced: true, /**
	 * STATE
	 */
	state: {
		preventFieldFetch: {},
		preventActionFieldFetch: {},
		preventTriggerFieldFetch: {},
		fieldOptions: {},
		actionFields: {},
		triggerFields: {},
		initialFetchedFields: {}
	}, /**
	 * ACTIONS
	 */
	actions: {
		async fetchFieldOptions( {commit}, payload ) {
			return FieldOptions.syncObject( payload ).then( response => {
				commit( 'setFieldOptions', {...payload, ...{data: response}} );
			} )
		},

		clearActionFields( {commit}, payload ) {
			commit( 'clearActionFields', payload );
		},

		clearTriggerFields( {commit}, payload ) {
			commit( 'clearTriggerFields', payload );
		},

		async fetchActionFields( {commit}, payload ) {
			return ActionFieldsOptions.syncObject( payload ).then( response => {
				commit( 'setActionFields', {...payload, ...{data: response}} );
			} )
		},

		async fetchTriggerFields( {commit}, payload ) {
			return TriggerFields.syncObject( payload ).then( response => {
				commit( 'setTriggerFields', {...payload, ...{data: response}} );
			} )
		},

		async getActionSubfields( {commit}, payload ) {
			const subfieldsData = payload.subfieldsData;
			delete payload.subfieldsData;
			return ActionSubfields.getOneByParams( payload ).then( response => {
				commit( 'steps/initActionSubfields', {...subfieldsData, ...{responseData: response}}, {root: true} );

				Object.values( response || {} ).forEach( field => {
					if ( Object.keys( field.values || {} ).length ) {
						commit( 'setActionFields', {
							data: field.values, action_id: payload.action_id, field: field.id
						} )
					}
				} )
			} );
		},

		async getTriggerSubfields( {commit}, payload ) {
			const subfieldsData = payload.subfieldsData;
			delete payload.subfieldsData;
			return TriggerSubfields.getOneByParams( payload ).then( response => {
				commit( 'steps/initTriggerSubfields', {...subfieldsData, ...{responseData: response}}, {root: true} );

				Object.values( response || {} ).forEach( field => {
					if ( Object.keys( field.values || {} ).length ) {
						commit( 'setTriggerFields', {
							data: field.values, trigger_id: payload.trigger_id, field: field.id
						} )
					}
				} )
			} );
		},

		async getActionFieldsMapping( {commit}, payload ) {
			const subfieldsData = payload.subfieldsData;
			delete payload.subfieldsData;
			return ActionFieldsMapping.getOneByParams( payload ).then( response => {
				commit( 'steps/setActionFieldsMapping', {...subfieldsData, ...{responseData: response}}, {root: true} );
			} );
		},

		async getTriggerFieldsMapping( {commit}, payload ) {
		},

		resetFieldOptions( {commit} ) {
			commit( 'resetFieldOptions' );
		},

		resetActionFields( {commit} ) {
			commit( 'resetActionFields' );
		},

		setInitialFetched( {commit}, payload ) {
			commit( 'setInitialFetched', payload );
		},

		resetInitialFetched( {commit}, ) {
			commit( 'resetInitialFetched' );
		},

		resetFetchedFields( {commit} ) {
			commit( 'resetFetchedFields' );
		}
	}, /**
	 * GETTERS
	 */
	getters: {
		getFieldOptions: state => state.fieldOptions,
		getActionFields: state => state.actionFields,
		getTriggerFields: state => state.triggerFields,
		getInitialFetchedFields: state => state.initialFetchedFields,
		shouldPreventFieldFetch: state => state.preventFieldFetch,
		shouldPreventActionFieldFetch: state => state.preventActionFieldFetch,
		shouldPreventTriggerFieldFetch: state => state.preventTriggerFieldFetch
	}, /**
	 * MUTATIONS
	 */
	mutations: {
		resetFetchedFields( state ) {
			state.preventActionFieldFetch = {};
			state.preventTriggerFieldFetch = {};
			state.preventFieldFetch = {};
		},

		setInitialFetched: ( state, payload ) => {
			state.initialFetchedFields[ payload ] = true;
		},

		resetInitialFetched: ( state ) => {
			state.initialFetchedFields = {};
		},

		setFieldOptions: ( state, payload ) => {
			if ( ! state.fieldOptions[ payload.object_id ] ) {
				state.fieldOptions[ payload.object_id ] = {}
			}
			const oldData = TAPAdmin._.cloneDeep( state.fieldOptions[ payload.object_id ][ payload.field ] || {} );

			state.fieldOptions[ payload.object_id ][ payload.field ] = fixNames( {...state.fieldOptions[ payload.object_id ][ payload.field ], ...payload.data} );

			// If the data it's the same, we don't need to fetch anymore
			state.preventFieldFetch[ `${payload.object_id}${payload.field}` ] = TAPAdmin._.isEqual( oldData, state.fieldOptions[ payload.object_id ][ payload.field ] );
		},

		clearActionFields: ( state, payload ) => {
			if ( state.actionFields[ payload.action_id ] ) {
				if ( Array.isArray( payload.field ) ) {
					payload.field.forEach( key => {
						delete state.actionFields[ payload.action_id ][ key ]
						delete state.preventActionFieldFetch[ `${payload.action_id}${key}` ]
					} )
				} else {
					delete state.actionFields[ payload.action_id ][ payload.field ];
					delete state.preventActionFieldFetch[ `${payload.action_id}${payload.field}` ];
				}
			}
		},

		clearTriggerFields: ( state, payload ) => {
			if ( state.triggerFields[ payload.trigger_id ] ) {
				if ( Array.isArray( payload.field ) ) {
					payload.field.forEach( key => {
						delete state.triggerFields[ payload.trigger_id ][ key ]
						delete state.preventTriggerFieldFetch[ `${payload.trigger_id}${key}` ]
					} )
				} else {
					delete state.triggerFields[ payload.trigger_id ][ payload.field ];
					delete state.preventTriggerFieldFetch[ `${payload.trigger_id}${payload.field}` ];
				}
			}
		},

		setActionFields: ( state, payload ) => {
			if ( ! state.actionFields[ payload.action_id ] ) {
				state.actionFields[ payload.action_id ] = {}
			}
			if ( Object.values( payload.data ).length ) {
				const oldData = TAPAdmin._.cloneDeep( state.actionFields[ payload.action_id ][ payload.field ] || {} );

				state.actionFields[ payload.action_id ][ payload.field ] = {...state.actionFields[ payload.action_id ][ payload.field ], ...payload.data};

				state.preventActionFieldFetch[ `${payload.action_id}${payload.field}` ] = TAPAdmin._.isEqual( oldData, state.actionFields[ payload.action_id ][ payload.field ] );
			} else {
				state.preventActionFieldFetch[ `${payload.action_id}${payload.field}` ] = true;
			}
		},

		setTriggerFields: ( state, payload ) => {
			if ( ! state.triggerFields[ payload.trigger_id ] ) {
				state.triggerFields[ payload.trigger_id ] = {}
			}
			if ( Object.values( payload.data ).length ) {
				const oldData = state.triggerFields[ payload.trigger_id ][ payload.field ];

				state.triggerFields[ payload.trigger_id ][ payload.field ] = {...state.triggerFields[ payload.trigger_id ][ payload.field ], ...payload.data};

				state.preventTriggerFieldFetch[ `${payload.trigger_id}${payload.field}` ] = TAPAdmin._.isEqual( oldData, state.triggerFields[ payload.trigger_id ][ payload.field ] );
			} else {
				state.preventActionFieldFetch[ `${payload.action_id}${payload.field}` ] = true;
			}
		},

		resetFieldOptions: state => state.fieldOptions = {},

		resetActionFields: state => state.actionFields = {},
	}
};
