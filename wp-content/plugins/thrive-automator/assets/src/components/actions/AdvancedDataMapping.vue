<template>
	<div class="tap-card tap-actions-card">
		<p :class="{'tap-card-delete':showDeleteConfirmation}" class="tap-card-name--actions">
			{{ showDeleteConfirmation ? 'Delete' : 'Do this' }}
		</p>
		<div class="tap-card-info--actions">
			<Dropdown :offset="[0,15]" :placement="'top'" :popper-hide-triggers="triggers => [...triggers, ...['click']]" :theme="'automator_html_tooltip'">
				<icon :icon-name="'tap-info'"/>
				<template #popper>
					<div class="tap-notification-popover">
						<span>If the required data for an action is unavailable, the action will be skipped. </span>
						<a href="https://help.thrivethemes.com/en/articles/5469176-thrive-automator-unavailable-actions" rel="noopener" target="_blank">Learn more</a>
					</div>
				</template>
			</Dropdown>
		</div>
		<div class="tap-card-container tap-flex--column">
			<step-actions :is-saved="isSaved" :step-index="stepIndex" :total-steps="stepsLength" @toggleDelete="showDeleteConfirmation=true"/>
			<div v-if="!isSaved" class="tap-selected-action tap-fw mb-15">
				<div class="tap-action-data tap-flex--column tap-show-errors">
					<div class="tap-action-info tap-fw tap-flex--start">
						<div class="tap-container-heading mb-5 tap-flex--start">
							{{ savedData.name }}
							<div class="tap-action-description">
								<icon :show-delay="200" :tooltip="savedData?.description" icon-name="tap-info"/>
							</div>
						</div>
					</div>
					<div class="tap-flex--column tap-advanced-mapping-fields tap-fw">
						<div class="tap-flex tap-fw tap-advanced-mapping-data">
							<div class="tap-mapping-source tap-fw mr-10">
								<div class="tap-container-heading mt-5 tap-flex--start">
									Source
								</div>
								<select2 :options="fieldOptions" :value="savedField" :wrapper-classes="showErrors && !fieldValidation?.isValid ? 'tap-error' : ''" @input="selectField"/>
								<div v-if="showErrors && !fieldValidation?.isValid" class="tap-filter-error">
									Value can not be empty
								</div>
							</div>
							<div class="tap-mapping-maps-to tap-fw">
								<div class="tap-container-heading mt-5 tap-flex--start">
									Maps to
								</div>
								<select2 :options="dataObjectsOptions" :value="savedMappedObject" :wrapper-classes="showErrors && !mappedObjectValidation?.isValid ? 'tap-error' : ''" @input="selectDataObject"/>
								<div v-if="showErrors && !mappedObjectValidation?.isValid" class="tap-filter-error">
									Value can not be empty
								</div>
							</div>
						</div>
						<div v-if="hasData" class="tap-flex--column tap-fw tap-matches">
							<div class="tap-container-heading m-0 mt-10 tap-flex--start">
								Matches
							</div>
							<field-generator v-for="(field,index) in mappingFields" :key="index" :available-fields="dataObjectValues" :has-errors="showFieldErrors" :pair-value="field" :prevent-validate-key="true" :with-toggle-select="true" type-key="value" @delete="deleteMappingField(index)" @input="insertNewField($event,index)"/>
							<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Add new'" :icon-name="'tap-plus'" @click="addMappingField"/>
						</div>
					</div>
				</div>
			</div>
			<div v-if="isSaved" class="tap-step-preview tap-flex--start">
				<div class="tap-card-saved-icon">
					<logo :value="savedData?.image || savedData?.logo"/>
				</div>
				<div class="tap-step-preview-data">
					<div class="tap-container-heading mb-5 tap-flex--start">
						{{ savedData.name }}
						<div class="tap-action-description">
							<icon :show-delay="200" :tooltip="savedData?.description" icon-name="tap-info"/>
						</div>
					</div>
					<div class="tap-container-message">
						<div v-for="(text,index) in savedContent" :key="index" class="tap-preview-field" v-html="text"/>
					</div>
				</div>
			</div>
			<div v-if="!isSaved" class="tap-card-actions tap-flex--end">
				<icon-button :button-styles="['upper']" :button-text="'Done'" @click="changeStatus"/>
			</div>
			<div v-if="isSaved" class="tap-card-overlay" @click="changeStatus"/>
		</div>
		<inline-delete v-if="showDeleteConfirmation" :message="'Are you sure you want to delete this action?'" @cancel="showDeleteConfirmation = !showDeleteConfirmation" @confirm="doActionDelete"/>
	</div>
</template>

<script>
import Actions from "@/components/actions/Actions";
import FieldGenerator from "@/components/general/FieldGenerator";
import Select2 from "@/components/general/Select2";
import { generateRandomString, validateKeyPair, validateValue, getFieldInfo } from "@/utils/data-fn";
import { select2Matcher, select2Option } from "@/utils/ui-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "AdvancedDataMapping",
	components: {
		Select2,
		FieldGenerator
	},
	mixins: [ Actions ],
	data() {
		return {
			dataObjectValues: {},
			showFieldErrors: false
		}
	},
	computed: {
		...mapGetters( 'steps', [ 'filterableFields', 'getStepDataObjects' ] ),
		...mapGetters( 'generic', [ 'getAdvancedMapDataObjects', 'getDataObjects' ] ),
		savedMappedObject() {
			return this.savedData?.extra_data.mapped_object?.value || ''
		},
		mappedObjectValidation() {
			return this.savedData?.extra_data.mapped_object?.validation || {};
		},
		savedFieldID() {
			return this.savedData?.extra_data.field_id?.value || ''
		},
		fieldValidation() {
			return this.savedData?.extra_data.field_id?.validation || {};
		},
		savedFieldObject() {
			return this.savedData?.extra_data.field_data_object?.value || ''
		},
		mappingFields() {
			return this.savedData?.extra_data.mapping_fields?.value || []
		},
		mappingFieldsValidation() {
			return this.savedData?.extra_data.mapping_fields?.validation || {};
		},
		savedField() {
			return this.savedFieldID ? `${this.savedFieldObject}/${this.savedFieldID}` : '';
		},
		hasData() {
			return this.savedMappedObject && Object.keys( this.dataObjectValues ).length;
		},
		fieldOptions() {
			const fields = this.filterableFields( this.stepIndex - 1 ),
				options = [ {id: '', text: ''} ];

			Object.keys( fields ).forEach( field => {
				if ( [ 'number', 'string' ].includes( fields[ field ]?.value_type ) ) {
					options.push( {
						id: field,
						text: fields[ field ].name
					} )
				}
			} );

			return {
				data: options,
				placeholder: 'Select field',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};

		},
		dataObjectsOptions() {
			const options = [ {id: '', text: ''} ];

			Object.keys( this.getAdvancedMapDataObjects ).forEach( field => {
				options.push( {
					id: field,
					text: this.getAdvancedMapDataObjects[ field ].name
				} )
			} );

			return {
				data: options,
				placeholder: 'Select field',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};
		},
	},
	mounted() {
		if ( ! this.mappingFields?.length ) {
			this.addMappingField();
		} else {
			this.showFieldErrors = true;
		}
		if ( this.isSaved && this.savedMappedObject ) {
			this.setStepDataObjects( this.savedMappedObject );
		}
		if ( ! this.isSaved ) {
			this.fetchData();
			this.showFieldErrors = true;
		}
	},

	methods: {
		...mapActions( 'steps', [ 'setActionField', 'updateStep', 'syncStepData' ] ),
		...mapActions( 'generic', [ 'fetchDataObjectValues' ] ),
		selectField( value ) {
			const {appID, field} = getFieldInfo( value, Object.keys( this.getDataObjects ) );

			if ( ! field || this.savedData?.field === field ) {
				return;
			}
			this.setActionField( {
				stepIndex: this.stepIndex,
				parentIndex: this.parentIndex,
				extraDataKey: [ 'field_id' ],
				validation: validateValue( value, [ 'required' ] ),
				value: field,
			} )

			this.setActionField( {
				stepIndex: this.stepIndex,
				parentIndex: this.parentIndex,
				extraDataKey: [ 'field_data_object' ],
				value: appID,
			} )
		},
		selectDataObject( value ) {
			if ( value !== this.savedMappedObject ) {
				this.setActionField( {
					stepIndex: this.stepIndex,
					parentIndex: this.parentIndex,
					extraDataKey: [ 'mapped_object' ],
					validation: validateValue( value, [ 'required' ] ),
					value: value,
				} );
				this.fetchData( value );

				this.setStepDataObjects( value );

			}
		},
		setStepDataObjects( value ) {
			const actionData = TAPAdmin._.cloneDeep( this.savedData );

			actionData.provided_params = this.getStepDataObjects( this.stepIndex - 1 );
			actionData.provided_params.push( value );
			actionData.provided_params = [ ...new Set( actionData.provided_params ) ];

			this.updateStep( {
				index: this.stepIndex,
				content: {
					type: 'actions',
					saved: this.isSaved,
					data: actionData
				}
			} );

			this.syncStepData();
		},
		fetchData( value = this.savedMappedObject ) {
			this.toggleLoader();
			this.fetchDataObjectValues( value ).then( set => {
				this.dataObjectValues = set
				this.toggleLoader( false );
			} )
		},
		changeStatus() {
			if ( ! this.isSaved && ( ! this.mappedObjectValidation?.isValid || ! this.fieldValidation?.isValid || ( this.hasData && ! this.mappingFieldsValidation?.isValid ) ) ) {
				this.showErrors = true;
				return;
			}
			this.showErrors = false;

			if ( this.isSaved && ! this.hasData ) {
				this.fetchData();
			}
			if ( ! this.isSaved ) {
				this.clearEmptyFields();
			}

			this.changeStepStatus( {
				index: this.stepIndex,
				status: ! this.isSaved
			} );
		},
		toggleLoader( show = true ) {
			this.$el?.querySelector( '.tap-card-container' )?.classList?.toggle( 'tap-loader--card', show );
		},
		addMappingField( key = '', value = '' ) {
			this.insertNewField( {
				key: TAPAdmin._.isString( key ) ? key : '',
				value: TAPAdmin._.isString( value ) ? value : '',
				uuid: generateRandomString( 32 ),
			}, this.mappingFields.length );
		},
		insertNewField( field, index = 0 ) {
			const mapped = this.mappingFields;
			mapped[ index ] = {...mapped[ index ], ...field};

			if ( ! this.showFieldErrors ) {
				this.showFieldErrors = ! mapped[ index ]?.key || ! mapped[ index ]?.value;
			}

			let isValid = validateKeyPair( mapped );
			//allow the only field to be empty
			if ( mapped.length === 1 && ! mapped[ 0 ].key && ! mapped[ 0 ].value ) {
				isValid = true;
			}

			this.setActionField( {
				stepIndex: this.stepIndex,
				parentIndex: this.parentIndex,
				extraDataKey: [ 'mapping_fields' ],
				validation: {isValid, message: isValid ? '' : 'Empty fields not allowed.'},
				value: mapped,
			} );
		},
		deleteMappingField( index ) {
			const mapped = this.mappingFields;

			mapped.splice( index, 1 );

			this.setActionField( {
				stepIndex: this.stepIndex,
				parentIndex: this.parentIndex,
				extraDataKey: [ 'mapping_fields' ],
				value: mapped,
			} );
			if ( ! mapped.length ) {
				this.addMappingField()
			}
		},
		clearEmptyFields() {
			if ( this.mappingFields?.length > 1 ) {
				const ids = [];
				let i = 0;
				while ( i < this.mappingFields.length ) {
					if ( ! this.mappingFields[ i ].key && ! this.mappingFields[ i ].value ) {
						ids.push( i );
					}
					i ++;
				}
				if ( this.mappingFields.length === ids.length ) {
					ids.pop();
				}
				ids.reverse().forEach( id => this.deleteMappingField( id ) );
			}
		},
	},

}
</script>
