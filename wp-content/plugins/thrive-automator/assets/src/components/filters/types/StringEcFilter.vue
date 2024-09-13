<template>
	<!-- eslint-disable -->
	<div class="tap-filter-col">
		<select2 :class="{'tap-error': !savedData.operator}" :options="operatorOptions" :value="savedData.operator" @input="changeOperator"/>
		<div v-if="!savedData.operator" class="tap-filter-error">
			Please select a value
		</div>
	</div>
	<div v-if="showInput" :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--full">
		<input-field :placeholder=" savedData.operator ==='contains' ? `Enter strings separated by commas` : ''" :value="savedData.value" @input="changeValue"/>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import InputField from "@/components/general/InputField";
import Select2 from "@/components/general/Select2";
import { prettyPreview, validateValue } from "@/utils/data-fn";

export default {
	name: "StringEcFilter",
	components: {
		Select2,
		InputField,
	},
	mixins: [ GenericFilter ],
	emits: [ 'propertyChange' ],
	computed: {
		showInput() {
			return this.savedData.operator && [ 'contains', 'equals' ].includes( this.savedData.operator )
		},
	},
	methods: {
		changeOperator( value ) {
			if ( this.savedData.operator !== value ) {
				this.changeProp( {
					operator: value,
					validation: value.includes( 'empty' ) ? {isValid: true, message: ''} : validateValue( '', this.fieldData.validators || [] )
				} );
			}
		},
		changeValue( value ) {

			this.changeProp( {
				value,
				validation: validateValue( value, this.fieldData.validators || [] )
			} );
		},
		changeProp( key, value ) {
			this.$emit( 'propertyChange', key, value );
			const operator = key?.operator || this.savedData.operator,
				isBool = [ 'empty', 'not_empty' ].includes( operator );
			let preview = `${this.fieldData.name} ${this.filterData.info.operators?.[ operator ]?.label}`,
				previewValue = key?.value || this.savedData.value;
			if ( operator === 'contains' ) {
				previewValue = previewValue.split( ',' );
			}
			if ( ! isBool ) {
				preview = prettyPreview( `${preview} $$value`, previewValue );
			}
			this.setPreview( preview );
		}
	}
}
</script>


