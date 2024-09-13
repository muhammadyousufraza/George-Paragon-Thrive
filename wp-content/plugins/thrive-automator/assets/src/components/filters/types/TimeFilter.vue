<template>
	<!-- eslint-disable -->
	<div :class="{'tap-error': !savedData.operator}" class="tap-filter-col">
		<select2 :options="operatorOptions" :value="savedData.operator" @input="changeOperator"/>
		<div v-if="!savedData.operator" class="tap-filter-error">
			Please select a value
		</div>
	</div>
	<div v-if="showDatePicker" class="tap-filter-col tap-time-col tap-flex--start ml-0">
		<input-field :max="24" :min="0" :placeholder="'Hour'" :type="'number'" :value="dateHour" @input="changeHour"/>
		<input-field :max="60" :placeholder="'Mins'" :type="'number'" :value="dateMinutes" @input="changeMinutes"/>
		<a
			v-tooltip="{
        content: timezoneTooltip,
        theme: 'automator',
        offset: [0, 10],
      }"
			:href="settingsUrl" target="_blank">UTC {{ timezone }}</a>
	</div>
	<div v-if="!showDatePicker && savedData.operator" :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--20 tap-date-input-col">
		<input-field :min="1" :placeholder="'Value'" :type="'number'" :value="savedData.value" @input="changeValue"/>
		<div class="tap-filter-error">
			{{ savedData.validation?.message || 'Invalid date' }}
		</div>
	</div>
	<div
		v-if="!showDatePicker && savedData.operator" :class="{'tap-error': !savedData.unit}" class="tap-filter-col--33">
		<select2 :options="umSelectOptions" :value="savedData.unit" @input="changeUm"/>
		<div v-if="!savedData.unit" class="tap-filter-error">
			Please select a value
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import TimeDateFilter from "@/components/filters/types/TimeDateFilter";
import { prettyPreview, ucFirst } from "@/utils/data-fn";
import { select2Option } from "@/utils/ui-fn";

export default {
	name: "TimeFilter",
	mixins: [ TimeDateFilter ],
	data() {
		return {
			defaultUnit: 'hours'
		}
	},
	computed: {
		umSelectOptions() {
			const options = [];
			[ 'hours', 'minutes' ].forEach( operator => {
				options.push( {
					id: operator,
					text: `${ucFirst( operator )} ago`
				} )
			} );

			return {
				data: options,
				minimumResultsForSearch: - 1,
				placeholder: 'Select a value',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option
			}
		},
	},
	methods: {
		buildPreview( data ) {
			const operator = data.operator || this.savedData.operator,
				value = data.value || this.savedData.value,
				um = data.unit || this.savedData.unit,
				valueDate = new Date( value ),
				previewValue = [ 'before', 'after', 'equals' ].includes( operator ) ? `${valueDate.getHours()}:${valueDate.getMinutes()}` : `${value} ${um} ago`,
				operation = operator === 'equals' ? `${this.filterData.info.operators?.[ operator ]?.label}` : `is ${this.filterData.info.operators?.[ operator ]?.label}`;

			this.setPreview( prettyPreview( `${this.fieldData.name} ${operation} $$value`, previewValue ) );

		}
	}
}
</script>


