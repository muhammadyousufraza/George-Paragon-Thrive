<template>
	<div class="tap-form-wrapper" :class="{'tap-form-show-errors':showErrors}">
		<div v-if="showFormLoader" class="tap-form-loader tap-loader--card"/>
		<ul v-if="showErrors && errorList.length" class="tap-form-errors">
			<li v-for="(error,errorIndex) in errorList" :key="errorIndex">
				{{ error }}
			</li>
		</ul>
		<form novalidate @submit.prevent="handleSubmit">
			<div class="tap-form-inputs">
				<input-field v-for="(input,inputIndex) in inputs" :key="inputIndex" :autocomplete="input.autocomplete" :label-position="input.position" :value="input.value" :placeholder="input.placeholder" :type="input.type" :label="input.label" :has-errors="checkInputErrors(input)" @input="handleInput(input, $event)"/>
			</div>
			<div v-if="consentMessage" class="tap-form-consent tap-flex">
				<checkbox id="consent" :class="{'tap-input-error': showErrors && !hasConsent}" :value="hasConsent" @input="$emit('changeConsent')"/>
				<div class="tap-consent-text" @click="$emit('changeConsent')" v-html="consentMessage"/>
			</div>
			<button :class="{'tap-disabled': preventSend}" class="tap-form-submit mt-15" type="submit" @submit.prevent.stop="handleSubmit">
				{{ buttonText }}
			</button>
		</form>
	</div>
</template>

<script>
import InputField from "@/components/general/InputField";
import { validateValue } from "@/utils/data-fn";
import Checkbox from "@/components/general/Checkbox";

export default {
	name: "CustomForm",
	components: {
		Checkbox,
		InputField
	},
	props: {
		inputs: {
			type: Object,
			default: () => {

			},
		},
		radios: {
			type: Object,
			default: () => {

			},
		},
		checkboxes: {
			type: Object,
			default: () => {

			},
		},
		buttonText: {
			type: String,
			default: () => 'Submit',
		},
		consentMessage: {
			type: String,
			default: () => '',
		},
		hasConsent: {
			type: Boolean,
			default: () => false,
		},
		showFormLoader: {
			type: Boolean,
			default: () => false,
		}
	},
	emits: [ 'submit', 'changeConsent', 'input' ],
	data() {
		return {
			formData: {},
			showErrors: false,
		}
	},
	computed: {
		errorList() {
			const errors = [];
			Object.values( this.inputs ).map( input => {
				if ( ! input.validation.isValid && input.validation.message ) {
					errors.push( input.validation.message );
				}
			} );
			if ( this.consentMessage && ! this.hasConsent ) {
				errors.push( 'Consent must be accepted' );
			}

			return [ ...new Set( errors ) ];
		},
		preventSend() {
			return this.showErrors && this.errorList.length > 0;
		}
	},
	methods: {
		checkInputErrors( input ) {
			return ( ! input.validation?.isValid ) || false;
		},
		handleInput( input, inputValue ) {
			const validation = validateValue( inputValue, input.validators );

			if ( validation.message === 'Value can not be empty' ) {
				validation.message = input.label + ' can not be empty';
			}

			if ( input.key === 'confirm_password' && this.inputs.password?.value !== inputValue ) {
				validation.isValid = false;
				validation.message = 'Passwords do not match';
			}

			this.$emit( 'input', {
				key: input.key,
				value: inputValue,
				validation,
			} );
		},
		handleSubmit() {
			let hasErrors = false;

			if ( this.consentMessage && ! this.hasConsent ) {
				hasErrors = true;
			}
			if ( ! hasErrors ) {
				const inputs = Object.values( this.inputs );
				let inputIndex = 0;
				while ( ! hasErrors && inputIndex < inputs.length ) {
					if ( this.checkInputErrors( inputs[ inputIndex ] ) ) {
						hasErrors = true;
					}
					inputIndex ++;
				}
			}
			this.showErrors = hasErrors;
			if ( ! hasErrors ) {
				this.$emit( 'submit' );
			}
		}
	},
}
</script>
