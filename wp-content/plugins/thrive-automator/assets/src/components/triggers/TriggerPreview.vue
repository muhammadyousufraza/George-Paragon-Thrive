<template>
	<div ref="containerElement" :class="{'is-preview': !allowDelete, 'mb-15':allowDelete, 'is-active':!collapsed}" class="tap-trigger-item tap-flex--column">
		<div :class="{'is-active':!collapsed, 'pb-10': !collapsed}" class="tap-trigger-data tap-fw tap-flex p-10" @click="collapsed=!collapsed">
			<div v-if="allowDelete" class="tap-toggle-icon mr-20">
				<icon icon-name="tap-chevron-down"/>
			</div>
			<div v-if="trigger.image" :class="{'mr-10':allowDelete}" class="tap-trigger-image tap-flex--start">
				<logo :value="trigger.image"/>
			</div>
			<div class="tap-trigger-name tap-flex--column">
				<div class="tap-flex--start">
					{{ trigger.name }}
					<div class="tap-trigger-description">
						<icon :tooltip="trigger.description" icon-name="tap-info"/>
					</div>
				</div>
				<div v-if="triggerFieldsPreviews.length &&!allowDelete" class="tap-container-message">
					<div v-for="(text,index) in triggerFieldsPreviews" :key="index" class="tap-preview-field" v-html="text"/>
				</div>
				<div v-if="triggerConditionInfo.length && !allowDelete" class="tap-trigger-condition-info mt-5">
					<div v-for="(text,index) in triggerConditionInfo" :key="index" class="tap-preview-field" v-html="text"/>
				</div>
			</div>
			<div v-if="allowDelete" class="tap-trigger-action tap-flex">
				<div v-if="triggerLimitation&&limitationsPreview" class="tap-trigger-limitations tap-flex p-5 mr-10">
					<icon v-if="showReset" :tooltip="'Reset counter'" icon-name="tap-sync" @click.stop.prevent="resetTriggerLimit"/>
					<span v-if="limitationsPreview" class="ml-5">{{ limitationsPreview }}</span>
				</div>
				<icon icon-name="tap-trash" @click.stop="showDeleteConfirmation=true"/>
			</div>
		</div>
		<transition name="tap-toggle">
			<div v-if="allowDelete && !collapsed" class="tap-toggle-content tap-flex--column tap-scrollbar tap-fw">
				<div class="tap-trigger-dynamic tap-fw">
					<receive-webhook v-if="trigger.id==='wordpress/webhook_receive'" :show-errors="showErrors" :trigger="trigger" :trigger-index="triggerIndex" @generate-data="setWebhookDataObject"/>
				</div>
				<div v-if="interfaceFields.length" class="tap-trigger-fields pb-15 pr-5 pl-5">
					<div v-for="(field,index) in interfaceFields" :key="index" class="tap-trigger-field">
						<component :is="getFieldComponent(field.type)" :container-element="$refs.containerElement" :field-data="field" :is-saved="!allowDelete" :object-identifiers="[field.id]" :parent-data="trigger" :parent-index="triggerIndex" :parent-show-errors="showErrors" :step-index="stepIndex" parent-type="trigger"/>
					</div>
				</div>
				<div v-if="hasData" class="tap-trigger-conditions tap-fw">
					<condition-item v-for="(filter,index) in trigger.conditions" :key="index" :filter-index="index" :filterable-fields="filterableFields" :saved="!allowDelete" :saved-data="filter" :saved-filters="trigger.conditions" :step-index="stepIndex" @update-conditions="updateConditions"/>
					<div v-if="trigger.conditions?.[0]?.field" class="tap-new-condition tap-flex mt-10">
						<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Add new condition'" :icon-name="'tap-plus'" @click="addCondition"/>
					</div>
				</div>
			</div>
		</transition>
		<inline-delete v-if="showDeleteConfirmation && allowDelete" :classes="collapsed ? 'tap-row' :''" :message="'Are you sure you want to delete this trigger?'" @cancel="showDeleteConfirmation = !showDeleteConfirmation" @confirm="deleteTrigger"/>
	</div>
</template>

<script>
import ConditionItem from "@/components/filters/ConditionItem";
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import InlineDelete from "@/components/general/InlineDelete";
import Logo from "@/components/general/Logo";
import ReceiveWebhook from "@/components/general/ReceiveWebhook";
import { validationRegex } from "@/utils/constants";
import { getComponentName, getItemById, initFields, validateDataKey } from "@/utils/data-fn";
import { getFieldsPreview } from "@/utils/ui-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "TriggerPreview",
	components: {
		ReceiveWebhook,
		ConditionItem,
		Logo,
		Icon,
		IconButton,
		InlineDelete
	},
	props: {
		trigger: {
			type: Object,
			default: () => {
			},
		},
		allowDelete: {
			type: Boolean,
			default: () => true,
		},
		triggerIndex: {
			type: Number,
			default: () => 0
		},
		stepIndex: {
			type: Number,
			default: () => 0
		},
		showErrors: {
			type: Boolean,
			default: () => false,
		}
	},
	data() {
		return {
			collapsed: true,
			showDeleteConfirmation: false
		};
	},
	computed: {
		...mapGetters( 'generic', [ 'getFilters', 'getDataObjects', 'getPrimaryFields' ] ),
		...mapGetters( 'steps', [ 'getCurrentAutomation', 'getAutomationMeta' ] ),
		...mapGetters( 'triggers', [ 'getTriggers', 'getLimitations' ] ),
		...mapGetters( 'actions', [ 'getActions' ] ),
		triggerLimitation() {
			return this.getLimitations?.filter( t => {
				let hasLimitation = t.trigger_id === this.trigger.id;
				if ( this.trigger.webhook_id && t.webhook_id ) {
					hasLimitation = t.webhook_id.replaceAll( '"', '' ) === this.trigger.webhook_id;
				}
				return hasLimitation
			} )[ 0 ]
		},
		showReset() {
			return this.getCurrentAutomation.id && this.triggerLimitation;
		},
		limitationsPreview() {
			const constraints = this.getAutomationMeta.limitations,
				limitation = this.triggerLimitation;
			let preview = '',
				runs = Number( limitation?.trigger_runs || 0 );
			const total = constraints?.execution_count === 'number' ? constraints.execution_count_limit : 1;

			runs = Math.max( 0, runs );

			preview = constraints?.execution_count === 'unlimited' || constraints?.rule === 'each_logged_in' ? `${runs}` : `${Math.min( runs, total )}/${total}`;

			return preview;
		},
		hasData() {
			return Object.keys( this.trigger.filterable_fields ).some( data => data !== 'global_data' && Object.keys( this.trigger.filterable_fields[ data ] ).length );
		},
		interfaceFields() {
			return Object.values( this.trigger.interface_fields );
		},
		filterableFields() {
			const fields = {};

			Object.keys( this.trigger.filterable_fields ).forEach( appID => {
				Object.values( this.trigger.filterable_fields[ appID ] ).forEach( field => {
					if ( Object.keys( field?.filters || {} ).length ) {
						fields[ `${appID}/${field.id}` ] = {...field, ...{data_object: appID}};
					}
				} )
			} )

			return fields;
		},
		triggerConditionInfo() {
			let info = [];
			if ( this.trigger.conditions.length && this.trigger.conditions?.[ 0 ].field ) {
				this.trigger.conditions.forEach( cond => cond.preview && info.push( cond.preview ) );
				if ( ! info.length ) {
					info = [ `${this.trigger.conditions.length} condition${this.trigger.conditions.length > 1 ? 's' : ''}` ];
				}
			}
			return info;
		},
		triggerFieldsPreviews() {
			return getFieldsPreview( Object.values( this.trigger?.extra_data || {} ) );
		},
	},
	created() {
		/**
		 * backwards compat
		 */
		const shouldPrevent = TAPAdmin.preventLeave;
		if ( ! this.trigger.conditions || Array.isArray( this.trigger.conditions?.[ 0 ] ) ) {
			this.updateConditions();
			if ( ! shouldPrevent ) {
				TAPAdmin.preventLeave = false;
			}
		}
	},
	mounted() {
		/**
		 * In case now the trigger has interface fields
		 */
		const shouldPrevent = TAPAdmin.preventLeave;
		if ( this.trigger.interface_fields && ! Object.keys( this.trigger?.extra_data || {} ).length ) {
			const triggerData = TAPAdmin._.cloneDeep( this.trigger );
			triggerData.extra_data = initFields( triggerData.interface_fields );
			this.updateStepDataField( {
				stepIndex: this.stepIndex,
				dataIndex: this.triggerIndex,
				content: triggerData
			} );
			if ( ! shouldPrevent ) {
				TAPAdmin.preventLeave = false;
			}
		}

		if ( ! this.getAutomationMeta.limitations?.execution_count ) {
			this.setAutomationMeta( {
				key: 'limitations',
				value: {
					execution_count: 'unlimited',
					rule: 'all',
					moment: 'when',
					subject: 'any',
					execution_count_limit: 1,
					execution_count_minimum: 1
				}
			} )
		}

		if ( this.trigger.webhook_id ) {
			if ( this.allowDelete ) {
				this.toggleLoader();
			}
			Promise.allSettled( [
				this.fetchPrimaryFields(),
			] ).then( () => {
				this.setWebhookDataObject();
				this.toggleLoader( false );
			} )
		}

		Object.keys( this.trigger.filterable_fields ).forEach( dataObjectKey => {
			this.setDataObject( {
				id: dataObjectKey,
				fields: this.trigger.filterable_fields[ dataObjectKey ],
			} );
		} )
	},
	methods: {
		...mapActions( 'generic', [ 'fetchFilters', 'fetchPrimaryFields', 'fetchDataObjects', 'setDataObject' ] ),
		...mapActions( 'steps', [ 'updateStepDataField', 'updateCurrentAutomation', 'setTriggerField', 'setAutomationMeta' ] ),
		...mapActions( 'triggers', [ 'deleteTriggerLimitation' ] ),
		resetTriggerLimit() {
			const additional = {};
			if ( this.trigger.webhook_id ) {
				additional[ 'webhook_id' ] = this.trigger.webhook_id;
			}
			this.deleteTriggerLimitation( {
				id: this.getCurrentAutomation.id,
				trigger_id: this.trigger.id,
				additional
			} );
		},
		addCondition() {
			const triggerData = this.trigger;
			triggerData.conditions = triggerData.conditions || [];
			triggerData.conditions.push( {} );

			this.updateStepDataField( {
				stepIndex: this.stepIndex,
				dataIndex: this.triggerIndex,
				content: triggerData
			} )
			this.$emit( 'hide-errors' );
		},
		updateConditions( data = [ {} ] ) {
			const triggerData = this.trigger;
			triggerData.conditions = data;
			this.updateStepDataField( {
				stepIndex: this.stepIndex,
				dataIndex: this.triggerIndex,
				content: triggerData
			} );
			this.$emit( 'hide-errors' );
		},
		getFieldComponent( type ) {
			if ( type === 'input' ) {
				type = 'text';
			}

			return `${getComponentName( type )}Field`;
		},
		deleteTrigger() {
			if ( this.trigger.webhook_id ) {
				const metaData = TAPAdmin._.cloneDeep( this.getCurrentAutomation.meta ?? {} );
				if ( metaData[ `webhook-${this.trigger.webhook_id}` ] ) {
					delete metaData[ `webhook-${this.trigger.webhook_id}` ];
					this.updateCurrentAutomation( {key: 'meta', value: metaData} )
				}
			}
			this.$emit( 'trigger-delete', `${this.trigger.webhook_id ? 'webhook_' : ''}id`, this.trigger.webhook_id || this.trigger.id );
		},
		setWebhookDataObject() {
			const trigger = this.trigger,
				genericTrigger = getItemById( this.getTriggers, trigger.id ),
				metaData = this.getCurrentAutomation.meta,
				dataObjectKey = `webhook_data_${trigger.webhook_id}`,
				webhookFields = metaData[ `webhook-${trigger.webhook_id}` ][ 'fields' ],
				webhookFieldsKeys = Object.keys( webhookFields ),
				oldParams = [ ...trigger.provided_params ],
				filterableFields = {};

			trigger.filterable_fields = {global_data: this.getDataObjects.global_data.fields}
			trigger.provided_params = [ ...genericTrigger?.provided_params || [] ];
			trigger.hasErrors = webhookFieldsKeys.length === 0;

			webhookFieldsKeys.forEach( fieldKey => {
				const fieldData = webhookFields[ fieldKey ];
				if ( fieldData.field_key && validateDataKey( fieldData.field_key ).isValid && fieldData.field_type ) {
					if ( fieldData.field_type?.includes( 'tap_generic' ) ) {
						filterableFields[ fieldKey ] = fieldData;
					} else {
						if ( this.getPrimaryFields?.[ fieldData?.field_type ].primary_key ) {
							trigger.filterable_fields [ this.getPrimaryFields[ fieldData?.field_type ].primary_key ] = this.getDataObjects[ this.getPrimaryFields[ fieldData?.field_type ].primary_key ][ 'fields' ];
							trigger.provided_params.push( this.getPrimaryFields[ fieldData?.field_type ].primary_key );
						}
					}
				} else if ( fieldData.field_key || fieldData.field_type ) {
					trigger.hasErrors = true;
				}
			} );

			if ( ! trigger.hasErrors && metaData[ `webhook-${trigger.webhook_id}` ].use_custom_headers ) {
				trigger.hasErrors = ! metaData[ `webhook-${trigger.webhook_id}` ].security_headers.every( header => header.value && header.key && validateDataKey( header.key, validationRegex.httpHeaders ).isValid );
			}


			trigger.filterable_fields[ dataObjectKey ] = filterableFields;
			trigger.provided_params.push( dataObjectKey );
			trigger.provided_params = [ ...new Set( trigger.provided_params ) ];
			this.setDataObject( {
				id: dataObjectKey,
				fields: filterableFields,
				name: 'Webhook data'
			} );
			/**
			 *		Validate the trigger only if the data sets were changed
			 */
			const paramsChanged = TAPAdmin._.xor( trigger.provided_params, oldParams );
			if ( ! trigger.hasErrors && paramsChanged.length ) {
				this.$emit( 'changeProvidedObjects' )
			} else {
				trigger.provided_params = oldParams;
			}
			this.$emit( 'hide-errors' );
		},
		toggleLoader( show = true ) {
			this.$el?.closest( '.tap-trigger-item' )?.classList?.toggle( 'tap-loader--card', show );
		},
	}
}
</script>


