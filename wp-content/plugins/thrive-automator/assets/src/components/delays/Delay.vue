<template>
	<div class="tap-card tap-delay-card">
		<p :class="{'tap-card-delete':showDeleteConfirmation}" class="tap-card-name--delay">
			{{ showDeleteConfirmation ? 'Delete' : 'Delay' }}
		</p>
		<div class="tap-card-container tap-flex--column">
			<step-actions :is-saved="isSaved" :step-index="stepIndex" :total-steps="stepsLength" @toggleDelete="showDeleteConfirmation=true"/>
			<div v-if="!isSaved" class="tap-container-heading">
				Wait for
			</div>
			<div
				v-if="!isSaved" class="tap-delay tap-flex--start">
				<div class="tap-filter-col--20">
					<input-field :min="1" :placeholder="'Value'" :type="'number'" :value="savedData.value" @input="changeValue"/>
				</div>
				<div class="tap-filter-col--33">
					<select2 :options="umSelectOptions" :value="savedData.unit" @input="changeUm"/>
				</div>
			</div>
			<div v-if="isSaved" class="tap-step-preview tap-flex--start">
				<div class="tap-card-saved-icon">
					<icon :icon-name="'tap-clock'"/>
				</div>
				<div class="tap-step-preview-data">
					<div class="tap-container-heading">
						Delay
					</div>
					<div class="tap-preview-field">
						Wait for
						<div class="tap-preview-value">
							{{ savedData.value }} {{ savedData.value == 1 ? savedData.unit.slice( 0, - 1 ) : savedData.unit }}
						</div>
					</div>
				</div>
			</div>
			<div v-if="!isSaved && savedData.value && savedData.unit" class="tap-card-actions tap-flex--end">
				<icon-button :button-styles="['upper']" :button-text="'Done'" @click="changeStatus"/>
			</div>
			<div v-if="isSaved" class="tap-card-overlay" @click="changeStatus"/>
		</div>
		<inline-delete v-if="showDeleteConfirmation" :message="'Are you sure you want to delete this delay?'" @cancel="showDeleteConfirmation = !showDeleteConfirmation" @confirm="()=>{showDeleteConfirmation = !showDeleteConfirmation;deleteStep(stepIndex)}"/>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import InlineDelete from "@/components/general/InlineDelete";
import InputField from "@/components/general/InputField";
import Select2 from "@/components/general/Select2";
import StepActions from "@/components/steps/StepActions";
import { validateValue } from "@/utils/data-fn";
import { select2Option } from "@/utils/ui-fn";
import { mapActions } from "vuex";

export default {
	name: "Delay",
	components: {
		Select2,
		IconButton,
		InputField,
		StepActions,
		Icon,
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
			type: Object,
			default: () => {
			},
		},
		stepsLength: {
			type: Number,
			default: () => 0,
		}
	},
	data() {
		return {
			showDeleteConfirmation: false,
		};
	},
	computed: {
		umSelectOptions() {
			const options = [];
			Object.keys( TAPAdmin.delay_units ).forEach( unit => {
				options.push( {
					id: unit,
					text: TAPAdmin.delay_units[ unit ].label
				} )
			} );

			return {
				data: options,
				minimumResultsForSearch: - 1,
				placeholder: 'Select a value',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option
			};
		},
	},
	methods: {
		...mapActions( 'steps', [ 'updateStep', 'changeStepStatus', 'deleteStep' ] ),
		changeValue( value ) {
			if ( isNaN( Number( value ) ) ) {
				value = 1;
			} else {
				value = Math.max( value, 1 ).toString();
			}
			this.changeProp( {
				value,
				isValid: validateValue( value ) && validateValue( this.savedData.unit )
			} );
		},
		changeUm( value ) {
			this.changeProp( {
				unit: value,
				isValid: validateValue( value ) && validateValue( this.savedData.value )
			} );
		},
		changeProp( data ) {
			this.updateStep( {
				index: this.stepIndex,
				content: {
					type: 'delay',
					saved: this.saved,
					data: {...this.savedData, ...data}
				}
			} );
		},
		changeStatus() {
			this.changeStepStatus( {
				index: this.stepIndex,
				status: ! this.isSaved
			} );
		}
	}
}
</script>
