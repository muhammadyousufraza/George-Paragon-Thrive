<template>
	<div class="tap-flex--column tap-fw tap-key-pair-container pt-5 pb-5">
		<div class="tap-flex tap-fw tap-key-pair">
			<input-field :has-errors="hasErrors&&hasKeyError" :placeholder="keyPlaceholder" :value="pairValue.key" @input="handleKeyInput"/>
			<div class="tap-key-pair-separator tap-flex">
				=
			</div>
			<input-field :dynamic-data-fields="dynamicDataFields(stepIndex-1)" :has-dynamic-data="hasDynamic" :has-errors="hasErrors&&hasValueError" :placeholder="valuePlaceholder" :value="pairValue.value" @input="handleValueInput"/>
			<div class="tap-pair-delete">
				<icon icon-name="tap-cross" @click="$emit('delete')"/>
			</div>
		</div>
		<div v-if="hasErrors && (hasKeyError||hasValueError)" :class="{'tap-flex--end': hasValueError && !hasKeyError}" class="tap-kp-errors tap-fw tap-flex--between">
			<div v-if="hasKeyError" class="tap-kp-key-error">
				{{ keyErrorMessage }}
			</div>
			<div v-if="hasValueError" class="tap-kp-field-error">
				No value
			</div>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import InputField from "@/components/general/InputField";
import { validationRegex } from "@/utils/constants";
import { validateDataKey } from "@/utils/data-fn";
import { mapGetters } from "vuex";

export default {
	name: "KeyValuePair",
	components: {
		Icon,
		InputField
	},
	props: {
		pairValue: {
			type: Object,
			default: () => {
				return {
					'key': '',
					'value': ''
				}
			},
		},
		keyPlaceholder: {
			type: String,
			default: () => 'Key',
		},
		valuePlaceholder: {
			type: String,
			default: () => 'Value',
		},
		hasDynamic: {
			type: Boolean,
			default: () => true,
		},
		hasErrors: {
			type: Boolean,
			default: () => false,
		},
		validationRegex: {
			type: String,
			default: () => validationRegex.fieldKey
		},
		stepIndex: {
			type: Number,
			default: () => 0
		},
	},
	computed: {
		...mapGetters( 'steps', [ 'dynamicDataFields' ] ),
		hasKeyError() {
			return ! validateDataKey( this.pairValue.key, this.validationRegex ).isValid;
		},
		hasValueError() {
			return ! this.pairValue.value;
		},
		keyErrorMessage() {
			return validateDataKey( this.pairValue.key, this.validationRegex ).message;
		},
	},
	methods: {
		handleKeyInput( input ) {
			this.$emit( 'input', {
				...this.pairValue,
				...{
					key: input
				}
			} );
		},
		handleValueInput( input ) {
			this.$emit( 'input', {
				...this.pairValue,
				...{
					value: input
				}
			} );
		},
	}
}
</script>


