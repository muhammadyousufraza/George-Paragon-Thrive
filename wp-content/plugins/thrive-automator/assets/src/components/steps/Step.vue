<template>
	<div class="tap-step tap-flex--column">
		<div v-if="type" class="tap-step-content tap-fw ">
			<component :is="stepComponent" :is-saved="getAutomationSteps[index]?.saved" :saved-data="getAutomationSteps[index]?.data" :step-index="index" :steps-length="getAutomationSteps.length"/>
		</div>
		<div v-else class="tap-step-content tap-fw ">
			<div class="tap-card">
				<div class="tap-card-container">
					<step-actions :is-saved="false" :step-index="index" :steps-length="getAutomationSteps.length" @toggleDelete="deleteStep(index)"/>
					<div class="tap-choose-step-title">
						Choose next step
					</div>
					<div class="tap-step-autocomplete tap-fw">
						<dropdown-toggle :active-items="activeItems" :placeholder="'Search for actions'" :show-count="true" :stored-data="dropdownActions" :suffixes="{singular:'action', plural:'actions'}" :wrapper-classes="['tap-action-options']" @select="typeSelected" @toggle="toggle"/>
					</div>
					<div class="tap-flex--start tap-step-options mt-10">
						<div class="tap-actions-info tap-cat-info mt-5 mb-10">
							or choose an action category
						</div>
						<div class="tap-action-apps tap-flex--start tap-fw">
							<app-card v-for="(item,actionIndex) in actionApps" :key="actionIndex" :app="item" :no-of-items="Object.keys(item?.items||{}).length" v-on="item.has_access ? {click: ()=>toggleApp(item.id)} :{}"/>
						</div>
					</div>
					<div class="tap-no-action tap-fw mt-20 mb-20">
						Can't find the action you need?
						<a href="https://help.thrivethemes.com/en/articles/6208201-why-aren-t-all-actions-available-in-thrive-automator" target="_blank">Click here to learn more</a>
					</div>
				</div>
			</div>
		</div>
		<div v-if="showAddStep" class="tap-new-step tap-flex--column">
			<div class="tap-add-step-line"/>
			<div :class="{'last-step': index+1===getAutomationSteps.length}" class="tap-add-step tap-flex" @click="$emit('generate-step')">
				<icon icon-name="tap-plus"/>
				<span>Add another action</span>
			</div>
			<div v-if="index+1!==getAutomationSteps.length" class="tap-add-step-line--arrow"/>
			<div v-if="index+1!==getAutomationSteps.length" class="tap-add-step-arrow">
				<icon :icon-name="'tap-arrow-down'"/>
			</div>
		</div>
	</div>
</template>

<script>
import Actions from "@/components/actions/Actions";
import AdvancedDataMapping from "@/components/actions/AdvancedDataMapping";
import AppCard from "@/components/apps/AppCard";
import Delay from "@/components/delays/Delay";
import FiltersCard from "@/components/filters/FiltersCard";
import DropdownToggle from "@/components/general/DropdownToggle";
import Icon from "@/components/general/Icon";
import Logo from "@/components/general/Logo";
import Select2 from "@/components/general/Select2";
import StepActions from "@/components/steps/StepActions";
import Triggers from "@/components/triggers/Triggers";
import { getItemById, initFields } from "@/utils/data-fn";
import { triggerElementEvent } from "@/utils/ui-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "Step",
	components: {
		AdvancedDataMapping,
		Logo,
		FiltersCard,
		Actions,
		Triggers,
		Icon,
		StepActions,
		Delay,
		Select2,
		DropdownToggle,
		AppCard
	},
	props: {
		type: {
			type: String,
			default: () => '',
		},
		index: {
			type: Number,
			default: () => 0,
		}
	},
	data() {
		return {
			showActions: false,
			showFilters: false,
			activeItems: [],
			topLevelItems: {}
		}
	},
	computed: {
		...mapGetters( 'steps', [ 'getAutomationSteps', 'getCurrentAutomation' ] ),
		...mapGetters( 'actions', [ 'getActions' ] ),
		...mapGetters( 'generic', [ 'getApps' ] ),
		showAddStep() {
			return this.getAutomationSteps[ this.index ]?.saved || this.index + 1 !== this.getAutomationSteps.length
		},
		actionApps() {
			const actions =
				{
					filters: {
						id: 'filters',
						app_id: 'filters',
						logo: 'tap-filter',
						name: 'Filters',
						items: [ {} ],
						has_access: true,
					},
					delay: {
						id: 'delay',
						app_id: 'delay',
						logo: 'tap-delay',
						name: 'Delay',
						items: [ {} ],
						has_access: true,
					}
				};

			Object.keys( this.dropdownActions ).forEach( key => {
				if ( this.dropdownActions[ key ].is_top_level ) {
					actions[ key ] = {
						...this.topLevelItems[ key ],
						items: [ {} ],
						has_access: this.dropdownActions[ key ].has_access && this.getApps[ this.topLevelItems[ key ].app_id ].has_access,
						access_url: this.topLevelItems[ key ].access_url || this.getApps[ this.topLevelItems[ key ].app_id ].access_url,
					};
				} else {
					actions[ key ] = {
						...this.getApps[ key ],
						items: this.dropdownActions[ key ].items
					};
				}
			} );


			return actions;
		},
		dropdownActions() {
			const apps = {},
				matchingActions = this.getMatchingActions(),
				allActions = TAPAdmin._.cloneDeep( this.getActions );

			Object.keys( allActions ).forEach( appId => {
				apps[ appId ] = {...allActions[ appId ], ...this.getApps[ appId ]};
				delete apps[ appId ].id;
			} );

			Object.keys( apps ).forEach( appId => {
				Object.keys( apps[ appId ].items ).forEach( actionId => {
					if ( ! matchingActions.includes( actionId ) ) {
						delete apps[ appId ].items[ actionId ];
					} else {
						if ( apps[ appId ].items[ actionId ].is_top_level ) {
							this.topLevelItems[ actionId ] = apps[ appId ].items[ actionId ];
							delete apps[ appId ].items[ actionId ];
						}
					}
				} );
			} );

			return {...this.topLevelItems, ...apps};
		},
		//allows dynamic render of subcomponent
		stepComponent() {
			let component = '';
			switch ( this.type ) {
				case 'triggers':
					component = 'Triggers';
					break;
				case 'actions':
					component = 'Actions';
					if ( this.getAutomationSteps?.[ this.index ]?.data?.id === 'thrive/advanced_mapping' ) {
						component = 'AdvancedDataMapping'
					}
					break;
				case 'filters':
					component = 'FiltersCard';
					break;
				case 'delay':
					component = 'Delay';
					break;
				default:
			}

			return component
		}
	},
	methods: {
		...mapActions( 'steps', [ 'updateStep', 'deleteStep', 'syncStepData' ] ),
		toggle( id ) {
			this.activeItems = TAPAdmin._.xor( this.activeItems, [ id ] );
		},
		toggleApp( id ) {
			if ( [ ...[ 'filters', 'delay' ], ...Object.keys( this.topLevelItems || {} ) ].includes( id ) ) {
				this.typeSelected( id )
			} else {
				this.activeItems = [ id ];
				triggerElementEvent( this.$el.querySelector( `.tap-step-autocomplete .tap-autocomplete-toggle-trigger` ), 'click' );
			}
		},
		//set type and also trigger sub-component type
		setType( type ) {
			this.updateStep( {
				index: this.index,
				content: {
					type,
					saved: false,
					data: type === 'filters' ? [ {} ] : {}
				}
			} )
		},
		//update step data with action data & initial fields value
		typeSelected( value ) {
			if ( [ 'filters', 'delay' ].includes( value ) ) {
				this.setType( value )
			} else {
				const selected = getItemById( this.getActions, value );
				if ( selected ) {
					this.updateStep( {
						index: this.index,
						content: {
							type: 'actions',
							saved: Object.keys( selected.interface_fields ).length === 0,
							data: TAPAdmin._.cloneDeep( {
								...selected, ...{
									extra_data: initFields( selected.interface_fields )
								}
							} )
						}
					} );
					this.syncStepData();
				}
			}
		},
		getMatchingActions() {
			let actions = [];
			let stepIndex = 0;
			while ( stepIndex < this.index ) {
				if ( Array.isArray( this.getAutomationSteps[ stepIndex ]?.matching_actions ) ) {
					actions = [ ...actions, ...this.getAutomationSteps[ stepIndex ].matching_actions ]
				}
				stepIndex ++;
			}

			return [ ...new Set( actions ) ];
		}
	},
}
</script>


