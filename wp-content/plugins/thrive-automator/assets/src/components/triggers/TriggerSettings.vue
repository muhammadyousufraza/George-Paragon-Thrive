<template>
	<div class="tap-card-container tap-flex--column-start tap-trigger-settings">
		<div class="tap-container-heading tap-flex--start">
			Limit automation executions
			<div class="tap-trigger-settings-info ml-5">
				<Dropdown :popper-hide-triggers="triggers => [...triggers, ...['click']]" :show-triggers="triggers => [...triggers, ...['hover']]" :theme="'automator_html_tooltip'">
					<icon :icon-name="'tap-info'"/>
					<template #popper>
						<div class="tap-notification-popover">
							<span>Configure your automations to run for or after a set number of times, and for specific user types. Known users are those that are either logged in or a provided email address matches an existing WordPress account. </span>
							<a href="https://help.thrivethemes.com/en/articles/6211195-setting-up-the-automation-trigger-options" rel="noopener" target="_blank">Learn more</a>
						</div>
					</template>
				</Dropdown>
			</div>
		</div>
		<div class="tap-ts-options tap-flex--wrap tap-flex--start">
			<div :class="{'with-number':limitedExecution}" class="tap-ts-execution mb-10 mr-10">
				<select2 :options="executionOptions" :value="execution" @input="changeExecution"/>
			</div>
			<div v-if="limitedExecution" class="tap-ts-execution-number mb-10 mr-10">
				<input-field :min="1" :value="executionCountLimit" type="number" @input="changeExecutionLimit"/>
			</div>
			<div class="tap-ts-rule mb-10">
				<select2 :options="ruleOptions" :value="rule" @input="changeRule"/>
			</div>
			<div class="tap-ts-moment mb-10 mr-10">
				<select2 :options="momentOptions" :value="moment" @input="changeMoment"/>
			</div>
			<div v-if="minExecution" class="tap-ts-moment-number mb-10 mr-10">
				<input-field :min="1" :value="momentNumber" type="number" @input="changeMomentNumber"/>
			</div>
			<div v-if="!minExecution && execution!=='unlimited'" class="tap-ts-subject mb-10 mr-10">
				<select2 :options="subjectOptions" :value="subject" @input="changeSubject"/>
			</div>
			<div class="tap-ts-message tap-flex mb-10">
				{{ triggerLabel }}
			</div>
		</div>
		<div class="tap-card-actions tap-flex--end">
			<icon-button :button-styles="['upper']" :button-text="'Back'" @click="$emit('goBack')"/>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import InputField from "@/components/general/InputField";
import Select2 from "@/components/general/Select2";
import { select2Option } from "@/utils/ui-fn";
import { Dropdown } from "v-tooltip";
import { mapActions, mapGetters } from "vuex";

export default {
	name: 'TriggerSettings',
	components: {
		InputField,
		Select2,
		Icon,
		Dropdown,
		IconButton
	},
	computed: {
		...mapGetters( 'steps', [ 'getAutomationMeta' ] ),
		limitations() {
			return this.getAutomationMeta?.limitations || {};
		},
		execution() {
			return this.limitations?.execution_count || '';
		},
		rule() {
			return this.limitations?.rule || '';
		},
		moment() {
			return this.limitations?.moment || '';
		},
		subject() {
			return this.limitations?.subject || '';
		},
		executionCountLimit() {
			return this.limitations?.execution_count_limit || 0;
		},
		momentNumber() {
			return this.limitations?.execution_count_minimum || 0;
		},
		limitedExecution() {
			return this.execution === 'number';
		},
		minExecution() {
			return this.moment === 'after';
		},
		triggerLabel() {
			let label = '';
			if ( this.minExecution ) {
				const isMultiple = this.momentNumber > 1;
				label = `trigger${isMultiple ? 's' : ''} ${isMultiple ? 'have' : 'has'} fired`;
			} else {
				if ( this.execution === 'unlimited' ) {
					label = 'any trigger fires';
				} else {
					label = 'trigger is	fired';
				}
			}
			return label;
		},
		defaultSelectOptions() {
			return {
				minimumResultsForSearch: - 1,
				placeholder: 'Select a value',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option
			}
		},
		executionOptions() {
			const data = [
				{
					id: 'unlimited',
					text: 'Unlimited times'
				},
				{
					id: 'once',
					text: 'One time'
				},
				{
					id: 'number',
					text: 'Number of times'
				}
			];

			return {...{data}, ...this.defaultSelectOptions};
		},
		ruleOptions() {
			const data = [
				{
					id: 'all',
					text: 'For all users and events'
				},
				{
					id: 'each_logged_in',
					text: 'For each known user'
				},
				{
					id: 'any_logged_in',
					text: 'For any known user'
				},
				{
					id: 'logged_out',
					text: 'For any anonymous user'
				}
			];

			return {...{data}, ...this.defaultSelectOptions};
		},
		momentOptions() {
			const data = [
				{
					id: 'when',
					text: 'when'
				},
				{
					id: 'after',
					text: 'after'
				} ];
			return {...{data}, ...this.defaultSelectOptions};
		},
		subjectOptions() {
			const data = [
				{
					id: 'each',
					text: 'each'
				},
				{
					id: 'any',
					text: 'any'
				} ];
			return {...{data}, ...this.defaultSelectOptions};
		}
	},
	mounted() {
		if ( ! this.execution ) {
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
	},
	methods: {
		...mapActions( 'steps', [ 'setAutomationMeta' ] ),
		changeExecution( value ) {
			this.changeValue( 'execution_count', value );
		},
		changeRule( value ) {
			this.changeValue( 'rule', value );
		},
		changeMoment( value ) {
			this.changeValue( 'moment', value );
		},
		changeSubject( value ) {
			this.changeValue( 'subject', value );
		},
		changeExecutionLimit( value ) {
			this.changeValue( 'execution_count_limit', Math.max( 1, value ) );
		},
		changeMomentNumber( value ) {
			this.changeValue( 'execution_count_minimum', Math.max( 1, value ) );
		},
		changeValue( key, value ) {
			const data = this.limitations;
			data[ key ] = value;
			this.setAutomationMeta( {key: 'limitations', value: data} );
		}
	}
}
</script>
