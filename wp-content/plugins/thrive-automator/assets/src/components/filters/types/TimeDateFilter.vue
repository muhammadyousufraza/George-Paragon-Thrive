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
	<div v-if="showDatePicker" class="tap-filter-col tap-time-col tap-flex">
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
import DateFilter from "@/components/filters/types/DateFilter";
import { validateValue } from "@/utils/data-fn";

export default {
	name: "TimeDateFilter",
	mixins: [ DateFilter ],
	computed: {
		settingsUrl() {
			return TAPAdmin.urls.settings_url;
		},
		timezone() {
			return TAPAdmin.timezone_offset.includes( '-' ) ? TAPAdmin.timezone_offset.replace( '-', '- ' ) : `+ ${TAPAdmin.timezone_offset || 0}`;
		},
		dateMinutes() {
			let minutes = 0;
			const date = new Date( this.savedData.value );

			if ( date.getTime() ) {
				minutes = date.getMinutes();
			}

			return minutes;
		},
		dateHour() {
			let hours = 0;
			const date = new Date( this.savedData.value );

			if ( date.getTime() ) {
				hours = date.getHours();
			}

			return hours;
		},
		timezoneTooltip() {
			const date = new Date();
			return `Your website timezone is set to UTC ${this.timezone} and current local time is ${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}. Change this here`;
		}
	},
	methods: {
		changeMinutes( value ) {
			const dateObj = new Date( this.savedData.value );
			dateObj.setMinutes( value );
			this.changeDataObject( dateObj );
		},
		changeHour( value ) {
			const dateObj = new Date( this.savedData.value );
			dateObj.setHours( value );
			this.changeDataObject( dateObj );
		},
		changeDate( date ) {
			const dateObj = new Date( date );
			if ( this.dateHour || this.dateMinutes ) {
				dateObj.setHours( this.dateHour, this.dateMinutes );
			}
			this.changeDataObject( dateObj );
		},
		changeDataObject( dateObj ) {
			const date = `${dateObj.getFullYear()}/${dateObj.getMonth() + 1}/${dateObj.getDate()} ${dateObj.getHours()}:${dateObj.getMinutes()}`;
			this.changeProp( {
				value: date,
				validation: validateValue( dateObj, this.fieldData.validators || [] )
			} );
		}
	}
}
</script>


