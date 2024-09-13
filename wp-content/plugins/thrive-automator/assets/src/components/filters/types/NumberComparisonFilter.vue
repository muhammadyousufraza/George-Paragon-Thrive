<template>
	<!-- eslint-disable -->
	<div :class="{'tap-error': !savedData.operator}" class="tap-filter-col--33">
		<select2 :options="operatorOptions" :value="savedData.operator" @input="changeOperator"/>
		<div v-if="!savedData.operator" class="tap-filter-error">
			Please select a value
		</div>
	</div>
	<div v-if="savedData.operator" :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--15">
		<input-field :placeholder="'Value'" :type="'number'" :value="savedData.value" @input="changeValue"/>
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
	name: "NumberComparisonFilter",
	components: {
		Select2,
		InputField
	},
	mixins: [ GenericFilter ],
	emits: [ 'propertyChange' ],
	methods: {
		changeOperator( value ) {
			if ( this.savedData.operator !== value ) {
				this.changeProp( {
					operator: value
				} );
			}
		},
		changeValue( value ) {
			this.changeProp( {
				value,
				validation: validateValue( value, this.fieldData.validators || [] )
			} );
		},
		changeProp( data ) {
			this.$emit( 'propertyChange', data );
			this.setPreview( prettyPreview( `${this.fieldData.name} is ${this.filterData.info.operators?.[ data.operator || this.savedData.operator ]?.label} $$value`, data.value || this.savedData.value ) );
		}
	}
}
</script>


