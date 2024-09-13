<template>
	<!-- eslint-disable -->
	<div class="tap-operation--center tap-filter-col--square">
		=
	</div>
	<div :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--45">
		<select2 :options="operatorOptions" :value="savedData.value" @input="changeProp"
		/>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import ExistsFilter from "@/components/filters/types/ExistsFilter";
import Select2 from "@/components/general/Select2";
import { prettyPreview, validateValue } from "@/utils/data-fn";

export default {
	name: "BooleanFilter",
	components: {
		Select2
	},
	mixins: [ ExistsFilter ],
	methods: {
		changeProp( value ) {
			if ( value !== this.savedData.value ) {
				this.$emit( 'propertyChange', {
					value,
					validation: validateValue( value, this.fieldData.validators || [] )
				} );

				this.setPreview( prettyPreview( `${this.fieldData.name} is $$value`, value ) );
			}
		}
	}
}
</script>


