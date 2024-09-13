<template>
	<div v-if="savedData" class="tap-card tap-actions-card">
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
		<div ref="containerElement" class="tap-card-container tap-flex--column">
			<step-actions :is-saved="isSaved" :step-index="stepIndex" :total-steps="stepsLength" @toggleDelete="showDeleteConfirmation=true"/>
			<div v-if="!isSaved" class="tap-selected-action tap-fw mb-20">
				<div class="tap-action-data tap-flex--column tap-show-errors">
					<div class="tap-action-info tap-fw tap-flex--start">
						<div class="tap-container-heading mb-5 tap-flex--start">
							{{ savedData.name }}
							<div v-if="savedData.description" class="tap-action-description">
								<icon :show-delay="200" :tooltip="savedData.description" icon-name="tap-info"/>
							</div>
						</div>
					</div>
					<component
						:is="getFieldComponent(field.type)" v-for="(field,index) in interfaceFields"
						:key="index"
						:field-data="field"
						:is-saved="isSaved"
						:object-identifiers="[field.id]"
						:parent-data="savedData"
						:parent-has-errors="hasErrors"
						:parent-show-errors="showErrors"
						:container-element="$refs.containerElement"
						:step-index="stepIndex"/>
				</div>
			</div>
			<div v-if="isSaved" class="tap-step-preview tap-flex--start">
				<div class="tap-card-saved-icon">
					<logo :value="savedData?.image || savedData?.logo"/>
				</div>
				<div class="tap-step-preview-data">
					<div class="tap-container-heading mt-5 mb-15 tap-flex--start">
						{{ savedData.name }}
						<div v-if="savedData.description" class="tap-action-description">
							<icon :show-delay="200" :tooltip="savedData.description" icon-name="tap-info"/>
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
			<div v-if="isSaved" class="tap-card-overlay" :class="{'no-pointer': ! interfaceFields.length}" @click="changeStatus"/>
		</div>
		<inline-delete v-if="showDeleteConfirmation" :message="'Are you sure you want to delete this action?'" @cancel="showDeleteConfirmation = !showDeleteConfirmation" @confirm="doActionDelete"/>
	</div>
</template>
<script>
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import InlineDelete from "@/components/general/InlineDelete";
import Logo from "@/components/general/Logo";
import Select2 from "@/components/general/Select2";
import StepActions from "@/components/steps/StepActions";
import { getComponentName, subfieldsAreValid } from "@/utils/data-fn";
import { getFieldsPreview, triggerElementEvent } from "@/utils/ui-fn";
import { Dropdown } from "v-tooltip";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "Actions",
	components: {
		Logo,
		StepActions,
		Select2,
		IconButton,
		Icon,
		Dropdown,
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
			showErrors: false,
		}
	},
	computed: {
		...mapGetters( 'steps', [ 'getAutomationSteps', 'getCurrentAutomation' ] ),
		...mapGetters( 'generic', [ 'getDataObjects' ] ),
		interfaceFields() {
			return Object.values( this.savedData?.interface_fields );
		},
		savedContent() {
			return getFieldsPreview( Object.values( this.savedData?.extra_data || {} ), true );
		},
		hasErrors() {
			return ! subfieldsAreValid( this.savedData?.extra_data );
		}
	},
	watch: {
		'getAutomationSteps': {
			handler( newValue ) {
				if ( newValue[ this.stepIndex ]?.data?.id && newValue[ this.stepIndex - 1 ]?.matching_actions?.length && ! newValue[ this.stepIndex - 1 ].matching_actions.includes( newValue[ this.stepIndex ].data.id ) ) {
					this.deleteStep( this.stepIndex );
				}
			},
			deep: true,
		},
		'savedData.extra_data': {
			handler() {
				this.showErrors = false;
			},
			deep: true
		},
	},
	methods: {
		...mapActions( 'steps', [ 'changeStepStatus', 'updateStep', 'deleteStep', 'syncStepData' ] ),
		changeStatus() {
			if ( ! this.interfaceFields.length ) {
				return;
			}
			/**
			 * check for unset tags and try to set and then resave
			 * @type {NodeListOf<HTMLElementTagNameMap[string]> | NodeListOf<Element> | NodeListOf<SVGElementTagNameMap[string]>}
			 */
			const tags = this.$el.querySelectorAll( '.tap-tags-container input' );
			if ( tags.length ) {
				[ ...tags ].forEach( tag => {
					if ( tag.value ) {
						triggerElementEvent( tag )
					}
				} );
			}
			this.showErrors = this.hasErrors;
			if ( ! this.hasErrors ) {
				this.changeStepStatus( {
					index: this.stepIndex,
					status: ! this.isSaved
				} );
			}

		},
		doActionDelete() {
			this.deleteStep( this.stepIndex );
			this.showDeleteConfirmation = ! this.showDeleteConfirmation;
			this.syncStepData();
		},
		getFieldComponent( type ) {
			if ( type === 'input' ) {
				type = 'text';
			}

			return `${getComponentName( type )}Field`;
		},
	}
}
</script>
