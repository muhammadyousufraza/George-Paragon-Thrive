<template>
	<div class="tap-create-aut-container tap-fw  tap-flex--column tap-flex--start">
		<div class="tap-automation-header tap-flex--between">
			<div class="tap-automation-name">
				<inline-input :placeholder="'Automation title'" :value="getCurrentAutomation.title" @blur="updateCurrentAutomation({key:'title', value:$event})" @input="updateCurrentAutomation({key:'title', value:$event})"/>
			</div>
			<div
				v-tooltip="{
          content: canSave ? '' : 'Automations require at least one trigger and one action before they can be activated',
          theme: 'automator',
          placement:'bottom-end',
          offset: [0, 10],
        }">
				<div :class="{'tap-disabled': !canSave}" class="tap-aut-status tap-flex">
					<icon-button :button-styles="['status',isPublished ? '' : 'ghost' , isPublished ? '' : 'grey']" :button-text=" 'Active'" :icon-name="isPublished ?'tap-check':'' " @click="updateCurrentAutomation({key:'status',value:true})"/>
					<icon-button :button-styles="['status',isPublished ? 'ghost' : '', 'grey']" :button-text="'Inactive'" :icon-name="isPublished ?'':'tap-check'" @click="updateCurrentAutomation({key:'status',value:false})"/>
				</div>
			</div>
		</div>
		<div :class="{'no-triggers':!hasTriggers}" class="tap-automations-steps tap-fw  tap-flex--column tap-flex--start tap-scrollbar">
			<step v-for="(step,index) in getAutomationSteps" :key="index" :index="index" :type="step.type" @generate-step="generateStep(index+1)"/>
			<div class="tap-more-integrations mt-50 tap-flex--column">
				<span>Expand Thrive Automator's reach by connecting it to other tools</span>
				<div class="tap-flex tap-api-link mt-10" @click="goToApps">
					Connect more integrations
					<icon icon-name="tap-arrow-right"/>
				</div>
			</div>
		</div>
		<div class="tap-automation-footer tap-flex--between">
			<icon-button :button-styles="['ghost','grey']" :button-text="'Cancel'" class="tap-button-cancel" @click="cancel"/>
			<icon-button :button-styles="['save']" :button-text="'Save and finish'" :class="{'tap-disabled':!canSave}" @click="save"/>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import InlineInput from "@/components/general/InlineInput";
import Step from "@/components/steps/Step";
import { getAutomationMappedData } from "@/utils/data-fn";
import { tapToast, toggleAppLoader } from "@/utils/ui-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "Automation",
	components: {
		Icon,
		Step,
		InlineInput,
		IconButton
	},
	computed: {
		...mapGetters( 'triggers', [ 'getTriggers' ] ),
		...mapGetters( 'actions', [ 'getActions' ] ),
		...mapGetters( 'steps', [ 'getAutomationSteps', 'getCurrentAutomation' ] ),
		hasTriggers() {
			return Object.keys( this.getTriggers || {} ).length > 0;
		},
		isPublished() {
			return this.getCurrentAutomation.status;
		},
		canSave() {
			return this.getAutomationSteps.length > 1 && this.getAutomationSteps.some( step => step.type === 'actions' ) && this.getAutomationSteps.every( step => step.saved );
		},
	},
	beforeMount() {
		this.resetFetchedFields();
		this.resetLimitations();
		if ( ! this.hasTriggers ) {
			toggleAppLoader();
			Promise.allSettled( [ this.fetchDataObjects(), this.fetchActions(), this.fetchTriggers(), this.fetchFilters(), this.fetchAdvancedMapDataObjects() ] ).then( () => {
				toggleAppLoader( false );
				this.checkEditingAutomation();
			} )
		} else {
			this.checkEditingAutomation();
		}
		this.resetFilters();
		this.$root.$el.classList.add( 'tap-no-sidebar' );
	},
	methods: {
		...mapActions( 'triggers', [ 'fetchTriggers', 'fetchLimitations', 'resetLimitations' ] ),
		...mapActions( 'actions', [ 'fetchActions' ] ),
		...mapActions( 'generic', [ 'fetchDataObjects', 'fetchAdvancedMapDataObjects', 'fetchFilters' ] ),
		...mapActions( 'steps', [ 'saveAutomation', 'addStep', 'resetSteps', 'setCurrentAutomation', 'editAutomation', 'updateCurrentAutomation' ] ),
		...mapActions( 'automations', [ 'resetFilters' ] ),
		...mapActions( 'fields', [ 'resetFetchedFields' ] ),
		generateStep( index ) {
			this.addStep( {
				index,
				saved: false,
				content: {
					type: '',
					data: []
				}
			} )
		},
		checkEditingAutomation() {
			/* in case of using browser history fetch the automation to be edited*/
			if ( this.$route.params.id && this.getAutomationSteps?.length === 1 ) {
				toggleAppLoader();
				this.editAutomation( this.$route.params.id ).then( () => {
					this.fetchLimitations( this.getCurrentAutomation.id );
					toggleAppLoader( false );
				} )
			}
		},
		save() {
			if ( this.canSave ) {
				TAPAdmin.preventLeave = false;
				toggleAppLoader();
				const currentAut = {...this.getCurrentAutomation};
				currentAut.meta.matching_actions = [];
				this.saveAutomation( {
					...currentAut,
					...{
						status: this.getCurrentAutomation.status ? 'publish' : 'draft',
						content: getAutomationMappedData( this.getAutomationSteps ),
					}
				} ).then( () => {
					/* reset steps too*/
					this.resetSteps();
					this.$router.push( {path: '/saved'} )
				} ).finally( () => {
					toggleAppLoader( false );
				} )
			} else {
				if ( this.getAutomationSteps.length === 1 ) {
					if ( this.getAutomationSteps[ 0 ].data.length ) {
						tapToast( 'To complete automation you need to add an action' )
					} else {
						tapToast( 'Automations require at least one trigger and one action before they can be activated' );
					}
				} else if ( ! this.getAutomationSteps.every( step => step.saved ) ) {
					tapToast( 'To save your automation, please confirm all open steps first' )
				}
			}
		},
		cancel() {
			this.resetSteps();
			this.$router.push( {path: '/saved'} )
		},
		goToApps() {
			this.$router.push( {path: '/apps'} )
		}
	},
}
</script>


