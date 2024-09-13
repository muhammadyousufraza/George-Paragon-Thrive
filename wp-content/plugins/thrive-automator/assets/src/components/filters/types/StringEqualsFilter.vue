<template>
	<!-- eslint-disable -->
	<div class="tap-operation--center tap-filter-col--square">
		=
	</div>
	<div :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--45">
		<input-field :value="savedData.value" @input="changeProp"/>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message || 'No value set' }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import InputField from "@/components/general/InputField";
import { prettyPreview, validateValue } from "@/utils/data-fn";

export default {
	name: "StringEqualsFilter",
	components: {
		InputField
	},
	mixins: [ GenericFilter ],
	emits: [ 'propertyChange' ],
	methods: {
		changeProp( value ) {
			this.$emit( 'propertyChange', {
				value,
				validation: validateValue( value, this.fieldData.validators || [] )
			} );
			this.setPreview( prettyPreview( `${this.fieldData.name} equals $$value `, value ) );
		}
	}
}
</script>


