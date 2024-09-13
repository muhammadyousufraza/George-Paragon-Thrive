<template>
	<div class="tap-action-field"/>
</template>

<script>
import { generateRandomString, getComponentName, prettyPreview, ucFirst, validateValue } from "@/utils/data-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "GenericField",
	props: {
		fieldData: {
			type: Object,
			default: () => {
			}
		},
		parentData: {
			type: Object,
			default: () => {
			}
		},
		isSaved: {
			type: Boolean,
			default: () => false,
		},
		stepIndex: {
			type: Number,
			default: () => 0,
		},
		objectIdentifiers: {
			type: Array,
			default: () => []
		},
		parentType: {
			type: String,
			default: () => 'action'
		},
		parentIndex: {
			type: Number,
			default: () => 0
		},
		parentHasErrors: {
			type: Boolean,
			default: () => false,
		},
		parentShowErrors: {
			type: Boolean,
			default: () => false,
		},
		containerElement: {
			type: Node,
			default: null
		},
	},
	data() {
		return {
			loadPage: 0,
			hasChanges: false,
			syncOnChange: false,
			shouldClearEmptyFields: false,
		}
	},
	computed: {
		...mapGetters( 'fields', [ 'getActionFields', 'getTriggerFields', 'getInitialFetchedFields', 'shouldPreventTriggerFieldFetch', 'shouldPreventActionFieldFetch' ] ),
		loadLimit() {
			return this.fieldData.extra_options?.limit ?? TAPAdmin.load_limit;
		},
		hasErrors() {
			return this.hasChanges || this.parentShowErrors;
		},
		extraOptions() {
			return this.fieldData.extra_options ?? {};
		},
		parentName() {
			return ucFirst( this.parentType );
		},
		uuid() {
			return TAPAdmin._.get( this.parentData?.extra_data, [ ...this.extraDataKey, 'uuid' ] );
		},
		fieldValue() {
			return TAPAdmin._.get( this.parentData?.extra_data, [ ...this.extraDataKey, 'value' ] );
		},
		fieldValidation() {
			return TAPAdmin._.get( this.parentData?.extra_data, [ ...this.extraDataKey, 'validation' ] );
		},
		shouldFetchValues() {
			return this.fieldData?.is_ajax_field && ! this[ `get${this.parentName}Fields` ]?.[ this.parentData.id ]?.[ this.fieldData.id ]
		},
		/**
		 *  get field validators
		 */
		fieldValidators() {
			const identifiers = [ ...this.objectIdentifiers ].map( item => [ item, 'interface_fields' ] ).flat();
			// remove last subfield
			identifiers.pop();
			return TAPAdmin._.get( this.parentData.interface_fields, identifiers )?.validators || [];
		},
		ajaxValues() {
			let values = this[ `get${this.parentName}Fields` ]?.[ this.parentData.id ]?.[ this.fieldData.id ];
			if ( ! values || ! Object.keys( values ).length ) {
				values = this?.fieldData?.values
			}
			if ( ! values || ! Object.keys( values ).length ) {
				values = {}
			}
			return values;
		},
		extraDataKey() {
			const clone = [ ...this.objectIdentifiers ].map( item => [ item, 'subfield' ] ).flat();
			// remove last subfield
			clone.pop();
			return clone;
		},
		subfieldKeys() {
			const data = TAPAdmin._.get( this.parentData.fields, this.objectIdentifiers );
			let result = [];

			if ( data ) {
				if ( Array.isArray( data ) ) {
					result = data;
				} else {
					if ( typeof data === 'boolean' ) {
						result = [ 'all' ]
					} else {
						result = Object.keys( data );
					}
				}
			}
			return result;
		},
		previewValue() {
			const identifiers = [ ...this.objectIdentifiers ].map( item => [ item, 'interface_fields' ] ).flat();
			// remove last subfield
			identifiers.pop();
			return TAPAdmin._.get( this.parentData.interface_fields, identifiers )?.preview || [];
		},
		dynamicOptions() {
			const options = [];
			if ( this.fieldData.allowed_data_set_values.length ) {
				this.fieldData.allowed_data_set_values.forEach( item => {
					if ( this.getStepDataObjects( this.stepIndex ).includes( item ) ) {
						options.push( {
							value: `tap-dynamic-${item}`,
							id: `tap-dynamic-${item}`,
							label: `Dynamic ${this.getDataObjects[ item ].name || ucFirst( item.replace.replaceAll( '_', ' ' ) )}`,
							image: 'tap-database'
						} );
					}
				} );
			}

			return options;
		}
	},
	mounted() {
		if ( ! this.uuid ) {
			this.changeCustomProp( 'uuid', generateRandomString( 32 ) );
		}
		//prevent fetching again while changing steps order
		if ( ! this.getInitialFetchedFields[ this.uuid ] ) {
			if ( this.fieldData?.is_ajax_field && this.shouldFetchValues ) {
				this.fetchInitialData( true );
			} else if ( this.subfieldKeys?.length ) {
				//use timeout because this happens for deep subfields so we make sure that loader is displayed
				setTimeout( () => {
					if ( this.fieldValue ) {
						this.fetchSubfields( this.fieldValue );
					}
				}, 10 );
			}
			this.setInitialFetched( this.uuid )
		}
		if ( this.shouldClearEmptyFields ) {
			this.clearEmptyFields();
		}
	},
	methods: {
		...mapActions( 'steps', [ 'updateStep', 'setActionField', 'setActionFieldCustomProp', 'setTriggerField', 'setTriggerFieldCustomProp', 'updateStepDataField' ] ),
		...mapActions( 'fields', [ 'clearTriggerFields', 'clearActionFields', 'getActionSubfields', 'getTriggerSubfields', 'fetchActionFields', 'fetchTriggerFields', 'getActionFieldsMapping', 'getTriggerFieldsMapping', 'setInitialFetched' ] ),
		...mapActions( 'triggers', [ 'syncTriggerData' ] ),
		...mapActions( 'generic', [ 'setDataObject' ] ),
		clearEmptyFields() {
		},
		/* get subfield component */
		getFieldComponent( type ) {
			if ( type === 'input' ) {
				type = 'text';
			}
			return `${getComponentName( type )}Field`;
		},
		changeCustomProp( key, value ) {
			this[ `set${this.parentName}FieldCustomProp` ]( {
				stepIndex: this.stepIndex,
				extraDataKey: this.extraDataKey,
				parentIndex: this.parentIndex,
				key,
				value
			} );
		},
		/**
		 * Change field value
		 */
		changeProp( value ) {
			// prevent multiple fetches
			if ( this.fieldValue !== value ) {
				const shouldFetchSubfields = this.subfieldKeys?.length && value;

				this.hasChanges = true;
				this[ `set${this.parentName}Field` ]( {
					stepIndex: this.stepIndex,
					parentIndex: this.parentIndex,
					extraDataKey: this.extraDataKey,
					validation: validateValue( value, this.fieldValidators ),
					preview: this.getPrettyPreview( value ),
					value,
				} )

				if ( this.syncOnChange && typeof this[ `sync${this.parentName}Data` ] === 'function' ) {
					this.toggleLoader();
					this[ `sync${this.parentName}Data` ]( this.parentData ).then( data => {
						if ( this.parentType === 'trigger' ) {
							this.updateStepDataField( {
								stepIndex: this.stepIndex,
								dataIndex: this.parentIndex,
								content: {...TAPAdmin._.cloneDeep( this.parentData ), ...data}
							} )

							Object.keys( data.filterable_fields ).forEach( dataObjectKey => {
								this.setDataObject( {
									id: dataObjectKey,
									fields: data.filterable_fields[ dataObjectKey ],
								} );
							} )
							// fetch subfields
							if ( shouldFetchSubfields ) {
								this.fetchSubfields( value ).then( () => {
									this.toggleLoader( false );
								} );
							} else {
								this.toggleLoader( false );
							}
						}
					} );
				} else {
					// fetch subfields
					if ( shouldFetchSubfields ) {
						this.fetchSubfields( value );
					}
				}
			}
		},
		/**
		 * Fetch both values & subfields if needed
		 */
		fetchInitialData( forceFetch = false ) {
			const promises = [];
			this.toggleLoader();

			if ( this.shouldFetchValues || forceFetch ) {
				promises.push( this.fetchValues( {}, false ) );
			}

			if ( this.fieldValue && this.subfieldKeys.length && ( this.fieldData?.subfields && ! this.fieldData?.interface_fields || forceFetch ) ) {
				promises.push( this.fetchSubfields( this.fieldValue, false ) );
			}

			Promise.allSettled( promises ).then( () => {
					this.toggleLoader( false );
				}
			)
		},
		/**
		 * Fetch fields values
		 */
		fetchValues( options = {}, toggleLoader = true ) {
			if ( this[ `shouldPrevent${this.parentName}FieldFetch` ][ `${this.parentData.id}${this.fieldData.id}` ] ) {
				return;
			}

			if ( toggleLoader ) {
				this.toggleLoader();
			}
			return this[ `fetch${this.parentName}Fields` ]( {
				field: this.fieldData.id,
				[ `${this.parentType}_id` ]: this.parentData.id,
				filters: {
					[ `${this.parentType}_data` ]: JSON.stringify( this.parentData.extra_data ),
					limit: this.loadLimit,
					page: this.loadPage,
					current_value: this.fieldValue,
					...options
				},
			} ).finally( () => {
				if ( toggleLoader ) {
					this.toggleLoader( false );
				}
			} );
		},
		/**
		 * Fetch field subfield and set them in the object
		 */
		fetchSubfields( value, toggleLoader = true ) {
			if ( toggleLoader ) {
				this.toggleLoader();
			}
			//keep only the necessary data
			const extraData = TAPAdmin._.cloneDeep( this.parentData.extra_data ),
				clearData = data => {
					Object.keys( data ).forEach( key => {
						delete data[ key ].preview;
						delete data[ key ].validation;
						if ( data[ key ].subfield && Object.keys( data[ key ].subfield )?.length ) {
							clearData( data[ key ].subfield );
						} else {
							delete data[ key ].subfield;
						}
					} )

				};
			clearData( extraData );

			return new Promise( resolve => {
				this[ `get${this.parentName}FieldsMapping` ]( {
					[ `${this.parentType}_id` ]: this.parentData.id,
					action_data: JSON.stringify( extraData ),
					subfieldsData: {
						stepIndex: this.stepIndex
					}
				} ).then( () => {
					this[ `clear${this.parentName}Fields` ]( {
						[ `${this.parentType}_id` ]: this.parentData.id,
						field: this.subfieldKeys
					} );

					if ( ! TAPAdmin._.isEmpty( value ) || ! isNaN( value ) ) {
						this[ `get${this.parentName}Subfields` ]( {
							[ `${this.parentType}_id` ]: this.parentData.id,
							parent_data: JSON.stringify( extraData ),
							fields: this.subfieldKeys,
							current_value: Array.isArray( value ) ? value.join( ',' ) : value,
							subfieldsData: {
								parentIndex: this.parentIndex,
								stepIndex: this.stepIndex,
								objectIdentifiers: this.objectIdentifiers,
								extraDataKey: this.extraDataKey
							}
						} ).finally( () => {
							resolve();
							if ( toggleLoader ) {
								this.toggleLoader( false );
							}
						} )
					} else {
						resolve();
						if ( toggleLoader ) {
							this.toggleLoader( false );
						}
					}
				} );
			} )
		},
		getPrettyPreview( value ) {
			return prettyPreview( this.previewValue, value );
		},
		toggleLoader( show = true ) {
			if ( this.containerElement ) {
				this.containerElement.classList.toggle( 'tap-loader--card', show );
			}
		},
		refreshData() {
			/* eslint-disable */
			delete this.fieldData.values
			/* eslint-enable */
			this.fetchInitialData( true );
		}
	}
}
</script>
