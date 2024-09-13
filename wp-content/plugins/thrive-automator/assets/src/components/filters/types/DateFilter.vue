<template>
	<!-- eslint-disable -->
	<div :class="{'tap-error': !savedData.operator}" class="tap-filter-col">
		<select2 :options="operatorOptions" :value="savedData.operator" @input="changeOperator"/>
		<div v-if="!savedData.operator" class="tap-filter-error">
			Please select a value
		</div>
	</div>
	<div v-if="showDatePicker" :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col tap-date-col">
		<date-picker :value="savedData.value" @input="changeDate"/>
		<div class="tap-filter-error">
			{{ savedData.validation?.message || 'Invalid date' }}
		</div>
	</div>
	<div v-if="!showDatePicker && savedData.operator" :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--20 tap-date-input-col">
		<input-field :min="1" :placeholder="'Value'" :type="'number'" :value="savedData.value" @input="changeValue"/>
		<div class="tap-filter-error">
			{{ savedData.validation?.message || 'Invalid date' }}
		</div>
	</div>
	<div v-if="!showDatePicker && savedData.operator" :class="{'tap-error': !savedData.unit}" class="tap-filter-col--33">
		<select2 :options="umSelectOptions" :value="savedData.unit" @input="changeUm"/>
		<div v-if="!savedData.unit" class="tap-filter-error">
			Please select a value
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import DatePicker from "@/components/general/DatePicker";
import InputField from "@/components/general/InputField";
import Select2 from "@/components/general/Select2";
import { prettyPreview, ucFirst, validateValue } from "@/utils/data-fn";
import { select2Option } from "@/utils/ui-fn";

export default {
	name: "DateFilter",
	components: {
		Select2,
		InputField,
		DatePicker
	},
	mixins: [ GenericFilter ],
	emits: [ 'propertyChange' ],
	data() {
		return {
			defaultUnit: 'days'
		}
	},
	computed: {
		umSelectOptions() {
			const options = [];
			[ 'days', 'hours', 'minutes', 'months', 'years' ].forEach( operator => {
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
			};
		},
		showDatePicker() {
			return [ 'before', 'after', 'equals' ].includes( this.savedData.operator ?? '' );
		}
	},
	methods: {
		isWithDatePicker( value ) {
			return [ 'before', 'after', 'equals' ].includes( value );
		},
		changeOperator( operatorValue ) {
			if ( this.savedData.operator !== operatorValue ) {
				const dateObj = new Date();
				let fieldValue = this.savedData.value || '';

				/**
				 * Properly reset field value based on type of the operator
				 */
				if ( this.isWithDatePicker( operatorValue ) && ! this.isWithDatePicker( this.savedData.operator ) ) {
					fieldValue = `${dateObj.getFullYear()}/${dateObj.getMonth() + 1}/${dateObj.getDate()}`;
				} else if ( ! this.isWithDatePicker( operatorValue ) && ( ! this.savedData.operator || this.isWithDatePicker( this.savedData.operator ) ) ) {
					fieldValue = 1;
				}

				this.changeProp( {
					operator: operatorValue,
					value: fieldValue,
					unit: this.savedData.unit || this.defaultUnit,
					validation: validateValue( fieldValue, this.fieldData.validators || [] )
				} );
			}
		},
		changeDate( date ) {
			const dateObj = new Date( date )
			date = `${dateObj.getFullYear()}/${dateObj.getMonth() + 1}/${dateObj.getDate()}`;
			if ( this.savedData.value !== date ) {
				this.changeProp( {
					value: date,
					validation: validateValue( date, this.fieldData.validators || [] )
				} );
			}
		},
		changeValue( value ) {
			value = Math.max( value, 1 );
			this.changeProp( {
				value,
				validation: validateValue( value, this.fieldData.validators || [] )
			} );
		},
		changeUm( unit ) {
			if ( unit !== this.savedData.unit ) {
				this.changeProp( {
					unit,
				} );
			}
		},
		changeProp( data ) {
			this.$emit( 'propertyChange', data );
			this.buildPreview( data );
		},
		buildPreview( data ) {
			const operator = data.operator || this.savedData.operator,
				value = data.value || this.savedData.value,
				um = data.unit || this.savedData.unit,
				previewValue = [ 'before', 'after', 'equals' ].includes( operator ) ? value : `${value} ${um} ago`,
				operation = operator === 'equals' ? `${this.filterData.info.operators?.[ operator ]?.label}` : `is ${this.filterData.info.operators?.[ operator ]?.label}`;

			this.setPreview( prettyPreview( `${this.fieldData.name} ${operation} $$value`, previewValue ) );

		}
	}
}
</script>


