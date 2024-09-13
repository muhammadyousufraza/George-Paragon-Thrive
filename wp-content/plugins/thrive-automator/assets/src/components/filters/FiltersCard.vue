<template>
	<div :class="{'tap-show-errors':showErrors}" class="tap-card tap-filter-card">
		<p :class="{'tap-card-delete':showDeleteConfirmation}" class="tap-card-name--filters">
			{{ showDeleteConfirmation ? 'Delete' : 'Only if' }}
		</p>
		<div class="tap-card-container tap-flex--column">
			<step-actions :is-saved="isSaved" :step-index="stepIndex" :total-steps="stepsLength" @toggleDelete="showDeleteConfirmation=true"/>
			<div
				v-if="!isSaved" class="tap-filter-items tap-fw mb-15">
				<filter-item
					v-for="(filter,index) in getAutomationSteps[stepIndex].data"
					:key="index"
					:filter-index="index"
					:filterable-fields="filterableFields(stepIndex,true,true)"
					:saved="isSaved"
					:saved-data="filter"
					:saved-filters="getAutomationSteps[stepIndex].data"
					:step-index="stepIndex"/>
			</div>
			<div v-if="isSaved" class="tap-step-preview tap-flex--start">
				<div class="tap-card-saved-icon pt-15">
					<icon :icon-name="'tap-filter'"/>
				</div>
				<div class="tap-step-preview-data">
					<div class="tap-trigger-condition-info mt-5">
						<div v-for="(text,index) in savedMessage" :key="index" class="tap-preview-field" v-html="text"/>
					</div>
				</div>
			</div>
			<div v-if="!isSaved" class="tap-card-actions tap-flex--between">
				<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Add new filter'" :icon-name="'tap-plus'" @click="addFilter"/>
				<icon-button :button-styles="['upper']" :button-text="'Done'" @click="changeStatus"/>
			</div>
			<div v-if="isSaved" class="tap-card-overlay" @click="changeStatus"/>
		</div>
		<inline-delete
			v-if="showDeleteConfirmation"
			:message="'Are you sure you want to delete this filter?'"
			@cancel="showDeleteConfirmation = !showDeleteConfirmation"
			@confirm="()=>{showDeleteConfirmation = !showDeleteConfirmation;deleteStep(stepIndex)}"/>
	</div>
</template>


<script>
import FilterItem from "@/components/filters/FilterItem";
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import InlineDelete from "@/components/general/InlineDelete";
import StepActions from "@/components/steps/StepActions";
import { mapActions, mapGetters } from "vuex";

export default {
	name: 'FiltersCard',
	components: {
		FilterItem,
		Icon,
		IconButton,
		StepActions,
		InlineDelete
	},
	props: {
		isSaved: {
			type: Boolean,
			default: () => false,
		},
		stepIndex: {
			type: Number,
			default: () => 0,
		},
		savedData: {
			type: Array,
			default: () => [],
		},
		stepsLength: {
			type: Number,
			default: () => 0,
		}
	},
	data() {
		return {
			showErrors: false,
			showDeleteConfirmation: false,
		}
	},
	computed: {
		...mapGetters( 'steps', [ 'getAutomationSteps', 'filterableFields' ] ),
		hasErrors() {
			return this.getAutomationSteps?.[ this.stepIndex ]?.type === 'filters' ? this.getAutomationSteps[ this.stepIndex ].data.some( field => ! field.validation?.isValid ) : false;
		},
		savedMessage() {
			const filters = this.getAutomationSteps[ this.stepIndex ].data,
				info = [];
			if ( Array.isArray( filters ) ) {
				filters.forEach( cond => info.push( cond.preview ) );

				if ( ! info.length ) {
					let fieldsLabel = '';
					const suffix = filters.length > 1 ? 's' : '';
					fieldsLabel = filters.map( field => this.filterableFields( this.stepIndex )[ `${field.data_object}/${field.field}` ]?.name ).join( ', ' ) || '';
					fieldsLabel = filters.length ? `${filters.length} condition${suffix}: ${fieldsLabel}` : ``;
					info.push( fieldsLabel );
				}
			}
			return info;
		}
	},
	watch: {
		hasErrors() {
			this.showErrors = false;
		},
		filterableFields() {
			//prevent checks while saving the automation
			if ( this.getAutomationSteps.length > 1 && this.getAutomationSteps[ this.stepIndex ] ) {
				const data = this.getAutomationSteps[ this.stepIndex ].data;
				if ( Array.isArray( data ) && ! data.every( field => this.filterableFields[ `${field.data_object}/${field.field}` ] ) ) {
					setTimeout( () => {
						if ( ! TAPAdmin.fetchingActions ) {
							this.deleteStep( this.stepIndex );
						}
					}, 100 );
				}
			}
		},
	},
	methods: {
		...mapActions( 'steps', [ 'updateStep', 'addStepData', 'changeStepStatus', 'deleteStep' ] ),
		addFilter() {
			this.showErrors = false;
			this.addStepData( {
				index: this.stepIndex,
				content: {}
			} );
		},
		changeStatus() {
			this.showErrors = this.hasErrors;
			if ( ! this.hasErrors ) {
				this.changeStepStatus( {
					index: this.stepIndex,
					status: ! this.isSaved
				} );
			}
		}
	}
}
</script>


