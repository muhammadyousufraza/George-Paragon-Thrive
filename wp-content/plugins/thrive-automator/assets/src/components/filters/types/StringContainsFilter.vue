<template>
	<!-- eslint-disable -->
	<div class="tap-operation tap-filter-col">
		{{ filterLabel }}
	</div>
	<div :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--full">
		<input-field :placeholder="'Enter strings separated by commas'" :value="savedValue" @input="changeProp"/>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import InputField from "@/components/general/InputField";
import { prettyPreview, validateValue } from "@/utils/data-fn";

export default {
	name: "StringContains",
	components: {
		InputField
	},
	mixins: [ GenericFilter ],
	computed: {
		filterLabel() {
			return this.filterData?.info.operators?.contains?.label || 'contains';
		},
		savedValue() {
			return Array.isArray( this.savedData.value ) ? this.savedData.value.join( ',' ) : this.savedData.value;
		}
	},
	methods: {
		changeProp( value ) {
			value = value.split( ',' );
			this.$emit( 'propertyChange', {
				value,
				validation: validateValue( value, this.fieldData.validators || [] )
			} );
			this.setPreview( prettyPreview( `${this.fieldData.name} contains any of the following $$value `, value ) );
		}
	}
}
</script>


