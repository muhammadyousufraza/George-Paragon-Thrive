import ApiRequest from "@/utils/ApiRequest";
import { generateRandomString, initFields, setUrlParam, ucFirst } from "@/utils/data-fn";
import apiFetch from "@wordpress/api-fetch";
import { toggleAppLoader } from "@/utils/ui-fn";

const Automation = new ApiRequest( 'automation' );
/**
 * Hold automation states
 * Also handles automations steps and filters
 */
export default {
	namespaced: true,
	/**
	 * STATE
	 */
	state: {
		currentAutomation: {
			title: 'New automation',
			status: false,
			meta: {}
		},
		steps: [
			{
				type: 'triggers',
				saved: false,
				data: []
			}
		]
	},
	/**
	 * ACTIONS
	 */
	actions: {

		async syncStepData( {dispatch} ) {
			dispatch( 'setProvidedObjects' )
			TAPAdmin.fetchingActions = true;
			dispatch( 'syncMatchingActions' ).then( () => {
				TAPAdmin.fetchingActions = false;
			} );
		},

		async syncMatchingActions( {commit, state} ) {
			const initialPath = `${TAPAdmin.routes}/matching_actions`,
				promises = [];
			let path;

			state.steps.forEach( ( step, stepIndex ) => {
				path = initialPath;

				if ( step?.data_objects?.length ) {
					path = setUrlParam( path, 'provided_objects', step.data_objects )

					promises.push( apiFetch( {path} ).then( actions => {
						commit( 'setMatchingActions', {stepIndex, actions} );
					} ) );

				} else {
					commit( 'setMatchingActions', {stepIndex, actions: []} );
				}
			} )

			return Promise.allSettled( promises );
		},

		async saveAutomation( {commit}, payload ) {
			return Automation.create( {model: payload}, payload.id || 0 ).then( automationID => {
				if ( payload.id ) {
					commit( 'automations/updateAutomation', payload, {root: true} );
				} else {
					commit( 'automations/addAutomation', {...payload, ...{id: automationID, is_valid: true}}, {root: true} );
				}
			} )
		},

		async updateAutomation( {commit}, payload ) {
			return Automation.updateOne( payload.id, {model: payload} ).then( () => {
				commit( 'automations/updateAutomation', payload, {root: true} );
			} )
		},

		async deleteAutomation( {commit}, id ) {
			return Automation.deleteOne( id ).then( () => {
				commit( 'automations/deleteAutomation', id, {root: true} );
			} )
		},
		async duplicateAutomation( {commit}, id ) {
			toggleAppLoader();
			return Automation.duplicateOne( id ).then( payload => {
				toggleAppLoader( false );
				commit( 'automations/addAutomation', payload, {root: true} );
				commit( 'generic/setNoticeData', {
					header: 'Automation duplicated successfully',
					message: 'Your automation was successfully duplicated. Some details may have been updated, so please open the automation and check the details.',
					show: true,
				}, {root: true} )
			} )
		},
		async editAutomation( {commit}, id ) {
			return Automation.getOne( id ).then( aut => {
				commit( 'setSteps', aut.steps );
				commit( 'setCurrentAutomation', aut );
			} )
		},

		setCurrentAutomation( {commit}, automation ) {
			commit( 'setCurrentAutomation', automation );
		},

		updateCurrentAutomation( {commit}, data ) {
			commit( 'updateCurrentAutomation', data );
		},

		addStep( {commit}, payload ) {
			commit( 'addStep', payload );
		},

		setSteps( {commit}, steps ) {
			commit( 'setSteps', steps )
		},

		setActionField( {commit}, steps ) {
			commit( 'setActionField', steps )
		},

		setTriggerField( {commit}, steps ) {
			commit( 'setTriggerField', steps )
		},

		setActionFieldCustomProp( {commit}, steps ) {
			commit( 'setActionFieldCustomProp', steps )
		},

		setTriggerFieldCustomProp( {commit}, steps ) {
			commit( 'setTriggerFieldCustomProp', steps )
		},

		updateStep( {commit}, payload ) {
			commit( 'updateStep', payload );
		},

		deleteStep( {commit}, payload ) {
			commit( 'deleteStep', payload );
		},

		swapSteps( {commit}, payload ) {
			commit( 'swapSteps', payload );
		},

		resetSteps( {commit} ) {
			commit( 'resetSteps' );
			commit( 'setCurrentAutomation', {
				title: 'New automation',
				status: false,
				meta: {
					matching_actions: []
				}
			} );
			commit( 'fields/resetActionFields', {}, {root: true} );
			commit( 'fields/resetFieldOptions', {}, {root: true} );
			commit( 'fields/resetInitialFetched', {}, {root: true} );
		},

		addStepData( {commit}, payload ) {
			commit( 'addStepData', payload );
		},

		updateStepDataField( {commit}, payload ) {
			commit( 'updateStepDataField', payload );
		},
		deleteStepData( {commit}, payload ) {
			commit( 'deleteStepData', payload );
		},

		changeStepStatus( {commit}, payload ) {
			commit( 'changeStepStatus', payload );
		},

		setProvidedObjects( {commit} ) {
			commit( 'setProvidedObjects' );
		},

		setAutomationMeta( {commit}, data ) {
			commit( 'setAutomationMeta', data );
		},
	},
	/**
	 * GETTERS
	 */
	getters: {
		getStepDataObjects: state => stepIndex => {
			return state.steps[ stepIndex ]?.data_objects || [];
		},
		getAutomationSteps: state => state.steps,
		getCurrentAutomation: state => state.currentAutomation,
		getAutomationMeta: state => state.currentAutomation.meta || {},

		/**
		 * stepIndex - used to take all the field until a specified step
		 * withFilters - whether or not to include fields that can't be filtered
		 * commonTriggerSets - whether or not to get the fields that are common to all the triggers
		 * @param state
		 * @param getters
		 * @param rootState
		 * @param rootGetters
		 * @returns {function(*=, *=, *=): {}}
		 */
		filterableFields: ( state, getters, rootState, rootGetters ) => ( stepIndex = 1, withFilters = true, commonTriggerSets = false ) => {
			const setFields = dataKey => {
				const dataSet = rootGetters[ 'generic/getDataObjects' ]?.[ dataKey ]?.[ 'fields' ];
				Object.values( dataSet || {} ).forEach( field => {
					if ( ! withFilters || ( withFilters && Object.keys( field.filters ).length ) ) {
						fields[ `${dataKey}/${field.id}` ] = {...field, ...{data_object: dataKey}};
					}
				} )
			};

			let fields = {};
			let triggerSets = [];
			Object.values( getters.getAutomationSteps[ 0 ].data ).forEach( trigger => {
				const sets = Object.keys( trigger?.filterable_fields || {} );
				if ( commonTriggerSets ) {
					if ( ! triggerSets.length ) {
						triggerSets = sets;
					} else {
						triggerSets = [ ...triggerSets, ...sets.filter( set => triggerSets.includes( set ) ) ];
						triggerSets = triggerSets.filter( set => sets.includes( set ) );
					}
				} else {
					sets.forEach( appID => setFields( appID ) )
				}
			} );

			triggerSets = [ ...new Set( triggerSets ) ];
			if ( triggerSets.length ) {
				triggerSets.forEach( dataObject => setFields( dataObject ) )
			}

			let index = 1;
			while ( index <= stepIndex ) {
				if ( getters.getAutomationSteps[ index ].type === 'actions' ) {
					getters.getAutomationSteps[ index ]?.data_objects?.forEach( dataObject => setFields( dataObject ) )
				}
				index ++;
			}

			return fields;
		},
		getAutomationDummyData: ( state, getters ) => ( stepIndex = 1 ) => {
			const dummyData = {};

			Object.values( getters.filterableFields( stepIndex, false ) ).forEach( field => TAPAdmin._.set( dummyData, [ field.data_object, field.id ], field.dummy_value ) );

			return dummyData;
		},
		/**
		 * Dynamic data field
		 * Each field must contain a shortcode_tag which will be used in UI
		 * @param _state
		 * @param getters
		 * @param _rootState
		 * @param rootGetters
		 * @returns {{}}
		 */
		dynamicDataFields: ( _state, getters, _rootState, rootGetters ) => ( stepIndex = 1 ) => {
			const items = {};

			Object.values( getters.filterableFields( stepIndex, false ) ).forEach( field => {
				if ( field.shortcode_tag ) {
					if ( ! items[ field.data_object ] ) {
						let label = rootGetters[ 'generic/getDataObjects' ]?.[ field.data_object ].name;

						if ( ! label ) {
							label = field.data_object.replaceAll( '_', ' ' ).split( ' ' ).map( part => part.toLowerCase() ).join( ' ' )
						}

						items[ field.data_object ] = {
							label: ucFirst( label ),
							subfields: []
						}
					}
					items[ field.data_object ].subfields.push( field );
				}
			} )

			return items;
		}
	},
	/**
	 * MUTATIONS
	 */
	mutations: {
		setMatchingActions: ( state, data ) => {
			state.steps[ data.stepIndex ].matching_actions = data.actions;
		},

		setProvidedObjects: state => {
			let providedObjects = [];

			state.steps.forEach( step => {
				let setDataObjects = false;
				switch ( step.type ) {
					case 'actions':
						setDataObjects = true;
						providedObjects = [ ...providedObjects, ...step.data.provided_params ];
						break;
					case 'triggers':
						setDataObjects = true;
						step.data.forEach( trigger => {
							providedObjects = [ ...providedObjects, ...trigger.provided_params ];
						} )
						break;
					default:
						break;
				}
				if ( setDataObjects ) {
					step.data_objects = [ ...new Set( providedObjects ) ];
				}
			} );
		},

		setCurrentAutomation: ( state, aut ) => {
			delete aut.steps;
			if ( ! aut.meta || ( Array.isArray( aut.meta ) && aut.meta.length ) ) {
				aut.meta = {};
			}
			state.currentAutomation = aut
		},

		updateCurrentAutomation( state, data ) {
			TAPAdmin.preventLeave = true;
			state.currentAutomation[ data.key ] = data.value;
		},

		setAutomationMeta( state, {key, value} ) {
			state.currentAutomation.meta[ key ] = value;
		},

		addStep: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			state.steps.splice( payload.index, 0, payload.content );
		},

		setSteps: ( state, steps ) => state.steps = steps,

		updateStep: ( state, payload ) => {
			state.steps[ payload.index ] = payload.content;
		},

		deleteStep: ( state, payload ) => {
			state.steps.splice( payload, 1 );
		},

		resetSteps: state => state.steps = [
			{
				type: 'triggers',
				saved: false,
				data: []
			}
		],

		swapSteps: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			[ state.steps[ payload.currentIndex ], state.steps[ payload.newIndex ] ] = [ state.steps[ payload.newIndex ], state.steps[ payload.currentIndex ] ];
		},

		changeStepStatus: ( state, payload ) => {
			state.steps[ payload.index ].saved = payload.status;
			state.steps[ payload.index ].id = generateRandomString( 32 );
		},

		updateStepDataField: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			state.steps[ payload.stepIndex ].data[ payload.dataIndex ] = payload.content;
		},

		addStepData: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			state.steps[ payload.index ].data.push( payload.content );
		},

		deleteStepData: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			state.steps[ payload.index ].data = state.steps[ payload.index ].data.filter( obj => obj[ payload.fieldKey || 'id' ] !== payload.fieldValue )
		},

		/**
		 * Change action field value
		 * @param state
		 * @param payload
		 */
		setActionField: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data.extra_data, payload.extraDataKey, {
					...TAPAdmin._.get( state.steps[ payload.stepIndex ].data.extra_data, payload.extraDataKey ),
					...{
						value: payload.value,
						validation: payload.validation,
						preview: payload.preview,
						subfield: {}
					}
				}
			);
		},
		/**
		 * Change trigger field value
		 * @param state
		 * @param payload
		 */
		setTriggerField: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data[ payload.parentIndex ].extra_data, payload.extraDataKey, {
					...TAPAdmin._.get( state.steps[ payload.stepIndex ].data[ payload.parentIndex ].extra_data, payload.extraDataKey ),
					...{
						value: payload.value,
						validation: payload.validation,
						preview: payload.preview,
						subfield: {}
					}
				}
			);
		},
		setTriggerFieldCustomProp: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data[ payload.parentIndex ].extra_data, payload.extraDataKey, {
					...TAPAdmin._.get( state.steps[ payload.stepIndex ].data[ payload.parentIndex ].extra_data, payload.extraDataKey ),
					...{
						[ payload.key ]: payload.value,
					}
				}
			);
		},

		setActionFieldCustomProp: ( state, payload ) => {
			TAPAdmin.preventLeave = true;
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data.extra_data, payload.extraDataKey, {
					...TAPAdmin._.get( state.steps[ payload.stepIndex ].data.extra_data, payload.extraDataKey ),
					...{
						[ payload.key ]: payload.value,
					}
				}
			);
		},

		setActionFieldsMapping: ( state, payload ) => {
			state.steps[ payload.stepIndex ].data.fields = payload.responseData;
		},

		/**
		 * Init action subfields for interface & validation default values
		 * @param state
		 * @param payload
		 */
		initActionSubfields: ( state, payload ) => {
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data.interface_fields, payload.objectIdentifiers.map( item => [ item, 'interface_fields' ] ).flat(), payload.responseData );
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data.extra_data, [ ...payload.extraDataKey, 'subfield' ],
				{
					...initFields( payload.responseData ),
					...TAPAdmin._.get( state.steps[ payload.stepIndex ].data.extra_data, [ ...payload.extraDataKey, 'subfield' ] )
				} );

			let shouldCheckKeys = true,
				allowedKeys = TAPAdmin._.get( state.steps[ payload.stepIndex ].data.fields, payload.extraDataKey.filter( item => item !== 'subfield' ) );


			if ( typeof allowedKeys === 'boolean' ) {
				shouldCheckKeys = false;
			} else if ( ! Array.isArray( allowedKeys ) ) {
				allowedKeys = Object.keys( allowedKeys );
			}

			if ( shouldCheckKeys ) {
				/**
				 * Clean up unused fields
				 */
				Object.keys( TAPAdmin._.get( state.steps[ payload.stepIndex ].data.extra_data, [ ...payload.extraDataKey, 'subfield' ] ) ).forEach( key => {
					if ( ! allowedKeys.includes( key ) ) {
						TAPAdmin._.unset( state.steps[ payload.stepIndex ].data.extra_data, [ ...payload.extraDataKey, 'subfield', key ] );
					}
				} );
			}
		},
		initTriggerSubfields: ( state, payload ) => {
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data[ payload.parentIndex ].interface_fields, payload.objectIdentifiers.map( item => [ item, 'interface_fields' ] ).flat(), payload.responseData );
			TAPAdmin._.set( state.steps[ payload.stepIndex ].data[ payload.parentIndex ].extra_data, [ ...payload.extraDataKey, 'subfield' ],
				{
					...initFields( payload.responseData ),
					...TAPAdmin._.get( state.steps[ payload.stepIndex ].data[ payload.parentIndex ].extra_data, [ ...payload.extraDataKey, 'subfield' ] )
				} );
		}
	},
};
