<template>
	<div :class="{'tap-show-errors':showErrors}" class="tap-card tap-trigger-card">
		<p :class="{'tap-not-active':showSettings}" class="tap-card-name--triggers" @click="showSettings=false">
			Trigger
		</p>
		<div v-tooltip="{
           content: `Options`,
           theme: 'automator',
           offset: [0, 10],}" :class="{'tap-not-active':!showSettings}"
			 class="tap-flex tap-trigger-settings-icon pl-5 pr-5"
			 @click="showSettings=true">
			<icon icon-name="tap-cog"/>
		</div>
		<trigger-settings v-if="showSettings" @go-back="showSettings=false"/>
		<div v-if="!showSettings" class="tap-card-container tap-flex--column">
			<div v-if="!isSaved" class="tap-container-heading">
				When this happens
			</div>
			<div v-if="!isSaved" class="tap-trigger-select tap-fw">
				<dropdown-toggle :active-items="activeItems" :placeholder="'Search or explore all triggers'" :show-count="true" :stored-data="triggers" :suffixes="{singular:'trigger', plural:'triggers'}" :wrapper-classes="['tap-trigger-options']" @select="triggerSelected" @toggle="toggle"/>
				<div class="tap-trigger-info-wrapper tap-flex--between mt-10 ">
					<div v-if="showApps" class="tap-trigger-info tap-cat-info">
						or choose a trigger category
					</div>
					<div v-if="!showApps" class="tap-expand-apps " @click="showApps=true">
						Expand categories
					</div>
					<div v-if="savedData.length&&showApps" class="tap-expand-apps" @click="showApps=false">
						Collapse categories
					</div>
				</div>
			</div>
			<div v-if="!isSaved&&showApps" class="tap-trigger-apps tap-flex--start tap-fw mt-10">
				<app-card v-for="(item,index) in triggerApps" :key="index" :app="item" :no-of-items="Object.keys(item?.items||{}).length" v-on="item.has_access ? {click: ()=>toggleApp(item.id)} :{}"/>
			</div>
			<div v-if="savedData.length && !isSaved" class="tap-trigger-message">
				{{ savedTriggersLabel }}
			</div>
			<div class="tap-step-preview tap-selected-triggers tap-flex">
				<div v-if="isSaved" class="tap-card-saved-icon">
					<icon :icon-name="'tap-trigger'"/>
				</div>
				<div class=" tap-step-preview-data tap-flex--column">
					<trigger-preview
						v-for="(trigger,index) in savedData"
						:key="getTriggerKey(trigger)"
						:allow-delete="!isSaved"
						:show-errors="showErrors"
						:step-index="stepIndex"
						:trigger="trigger"
						:trigger-index="index"

						@trigger-delete="deleteTrigger"
						@change-provided-objects="syncStepData"
						@hide-errors="showErrors=false"/>
				</div>
			</div>
			<div v-if="savedData.length && !isSaved" class="tap-card-actions tap-flex--end">
				<icon-button :button-styles="['upper']" :button-text="'Done'" @click="changeStatus"/>
			</div>
			<div v-if="isSaved" class="tap-card-overlay" @click="changeStatus"/>
		</div>
	</div>
</template>

<script>
import AppCard from "@/components/apps/AppCard";
import DropdownToggle from "@/components/general/DropdownToggle";
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import TriggerPreview from "@/components/triggers/TriggerPreview";
import TriggerSettings from "@/components/triggers/TriggerSettings";
import { generateRandomString, getItemById, initFields, subfieldsAreValid } from "@/utils/data-fn";
import { triggerElementEvent } from "@/utils/ui-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "Triggers",
	components: {
		TriggerSettings,
		AppCard,
		DropdownToggle,
		TriggerPreview,
		Icon,
		IconButton
	},
	props: {
		stepIndex: {
			type: Number,
			default: () => 0,
		},
		stepsLength: {
			type: Number,
			default: () => 0,
		},
		savedData: {
			type: Array,
			default: () => [],
		},
		isSaved: {
			type: Boolean,
			default: () => false,
		}
	},
	data() {
		return {
			showSettings: false,
			showErrors: false,
			activeItems: [],
			showApps: true,
			topLevelItems: {},
		}
	},

	computed: {
		...mapGetters( 'triggers', [ 'getTriggers' ] ),
		...mapGetters( 'steps', [ 'getCurrentAutomation', 'getAutomationSteps' ] ),
		...mapGetters( 'generic', [ 'getApps' ] ),
		savedTriggersLabel() {
			return `${this.savedData.length} trigger${this.savedData.length > 1 ? 's' : ''} added`
		},
		triggers() {
			const triggers = TAPAdmin._.cloneDeep( this.getTriggers ),
				allowedTriggers = [ 'wordpress/webhook_receive' ],
				usedTriggers = this.savedData.map( trig => trig.id );

			const checkingItems = data => {
				Object.keys( data || {} ).forEach( key => {
					if ( data[ key ]?.id && usedTriggers.includes( data[ key ].id ) && ! allowedTriggers.includes( data[ key ].id ) ) {
						delete data[ key ];
					} else if ( Object.keys( data[ key ]?.items || {} )?.length ) {
						checkingItems( data[ key ].items );
					}
					if ( data[ key ]?.is_top_level ) {
						this.topLevelItems[ key ] = data[ key ];
						delete data[ key ];
					}
				} )
			};

			checkingItems( triggers );

			return {...this.topLevelItems, ...triggers};
		},
		triggerApps() {
			const data = {};

			Object.keys( this.triggers ).forEach( key => {
				if ( this.triggers[ key ].is_top_level ) {
					data[ key ] = {
						...this.topLevelItems[ key ],
						items: [ {} ],
						has_access: this.topLevelItems[ key ].has_access && this.getApps[ this.topLevelItems[ key ].app_id ].has_access,
						access_url: this.topLevelItems[ key ].access_url || this.getApps[ this.topLevelItems[ key ].app_id ].access_url,
					};
				} else {
					data[ key ] = {
						...this.getApps[ key ],
						items: this.triggers[ key ].items
					};
				}
			} );
			return data;
		},
		hasErrors() {
			let hasErrors = this.savedData.some( trigger => !! trigger.hasErrors ),
				i = 0;

			while ( i < this.savedData.length && ! hasErrors ) {
				hasErrors = this.savedData[ i ].conditions?.some( fieldData => fieldData.field && ! fieldData.validation?.isValid );
				if ( ! hasErrors && this.savedData[ i ]?.extra_data ) {
					hasErrors = ! subfieldsAreValid( this.savedData[ i ].extra_data );
				}
				i ++;
			}

			return hasErrors;
		}
	},
	watch: {
		hasErrors() {
			this.showErrors = false;
		},
	},
	methods: {
		...mapActions( 'steps', [ 'changeStepStatus', 'addStepData', 'deleteStepData', 'updateCurrentAutomation', 'syncStepData' ] ),
		getTriggerKey( trigger ) {
			return trigger.webhook_id ? `${trigger.id}-${trigger.webhook_id}` : trigger.id;
		},
		triggerSelected( value ) {
			const matchedTrigger = getItemById( this.triggers, value );

			if ( matchedTrigger ) {
				const selectedTrigger = TAPAdmin._.cloneDeep( matchedTrigger );
				selectedTrigger.conditions = [ {} ];
				selectedTrigger.extra_data = selectedTrigger.interface_fields ? initFields( selectedTrigger.interface_fields ) : {};

				if ( selectedTrigger.id === 'wordpress/webhook_receive' ) {
					selectedTrigger.webhook_id = generateRandomString( 32 );
					selectedTrigger.hook += `_${selectedTrigger.webhook_id}`;
					selectedTrigger.extra_data = {webhook_hash: {value: selectedTrigger.webhook_id}}

					const metaData = TAPAdmin._.cloneDeep( this.getCurrentAutomation.meta ?? {} );
					metaData[ `webhook-${selectedTrigger.webhook_id}` ] = {fields: {}};

					this.updateCurrentAutomation( {key: 'meta', value: metaData} )
				} else {
					selectedTrigger.hasErrors = false;
				}

				this.addStepData( {
					index: this.stepIndex,
					content: selectedTrigger
				} );

				this.syncStepData();
				this.showApps = false;
				setTimeout( () => {
					const triggerNodes = this.$el.querySelectorAll( `.tap-trigger-data` );
					triggerElementEvent( [ ...triggerNodes ].pop(), 'click' );
				}, 100 )
			}
		},
		deleteTrigger( key, value ) {
			this.deleteStepData( {
					index: this.stepIndex,
					fieldKey: key,
					fieldValue: value,
				}
			);
			this.syncStepData();
		},
		toggle( id ) {
			this.activeItems = TAPAdmin._.xor( this.activeItems, [ id ] );
		},
		toggleApp( id ) {
			if ( Object.keys( this.topLevelItems[ id ] || {} ).length ) {
				this.triggerSelected( id );
			} else {
				this.activeItems = [ id ];
				triggerElementEvent( this.$el.querySelector( `.tap-trigger-select .tap-autocomplete-toggle-trigger` ), 'click' );
			}

		},
		changeStatus() {
			this.showErrors = this.hasErrors;
			if ( this.hasErrors || this.isSaved ) {
				triggerElementEvent( this.$el.querySelectorAll( `.tap-trigger-data:not(.is-active)` ), 'click' );
				if ( this.hasErrors ) {
					setTimeout( () => {
						this.showErrors = true;
					}, 50 );
				}
			}
			if ( ! this.hasErrors || this.isSaved ) {
				this.showApps = false;
				//toggle edit status
				this.changeStepStatus( {
					index: this.stepIndex,
					status: ! this.isSaved
				} );
			}
		},
	}
}
</script>


