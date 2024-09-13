<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-datetime-field tap-action-field">
		<div class="tap-flex--start">
			<date-picker :value="fieldValue" @input="changeDate"/>
			<input-field :max="24" :min="0" :placeholder="'Hour'" :type="'number'" :value="dateHour" @input="changeHour"/>
			<input-field :max="60" :placeholder="'Mins'" :type="'number'" :value="dateMinutes" @input="changeMinutes"/>
			<a
				v-tooltip="{
          content: timezoneTooltip,
          theme: 'automator',
          offset: [0, 10],
        }"
				:href="settingsUrl"
				target="_blank">UTC {{ timezone }}</a>
		</div>
		<div v-if="hasErrors && !fieldValidation?.isValid" class="tap-filter-error">
			{{ fieldValidation?.message }}
		</div>
	</div>
</template>

<script>
import GenericField from "@/components/fields/GenericField";
import DatePicker from "@/components/general/DatePicker";
import InputField from "@/components/general/InputField";
import { prettyPreview } from "@/utils/data-fn";

export default {
	name: "DateTimeField",
	components: {
		InputField,
		DatePicker
	},
	mixins: [ GenericField ],
	computed: {
		settingsUrl() {
			return TAPAdmin.urls.settings_url;
		},
		timezone() {
			return TAPAdmin.timezone_offset.includes( '-' ) ? TAPAdmin.timezone_offset.replace( '-', '- ' ) : `+ ${TAPAdmin.timezone_offset || 0}`;
		},
		dateMinutes() {
			const date = new Date( this.fieldValue );
			let minutes = 0;

			if ( date.getTime() ) {
				minutes = date.getMinutes();
			}

			return minutes;
		},
		dateHour() {
			const date = new Date( this.fieldValue );
			let hours = 0;

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
			const dateObj = new Date( this.fieldValue );
			dateObj.setMinutes( value );
			this.changeDataObject( dateObj );
		},
		changeHour( value ) {
			const dateObj = new Date( this.fieldValue );
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
			this.changeProp( `${dateObj.getFullYear()}/${dateObj.getMonth() + 1}/${dateObj.getDate()} ${dateObj.getHours()}:${dateObj.getMinutes()}` );
		},
		getPrettyPreview( value ) {
			return prettyPreview( this.previewValue, `${value} ${this.timezone} UTC` );
		},
	}
}
</script>

