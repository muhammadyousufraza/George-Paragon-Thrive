<template>
	<div class="tap-filter-col">
		<select2 :options="operatorOptions" :value="savedData.value" :wrapper-classes="savedData.validation?.isValid ? '' :'tap-error'" @input="changeProp"/>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message || 'No value set' }}
		</div>
	</div>
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import Select2 from "@/components/general/Select2";
import { prettyPreview, validateValue } from "@/utils/data-fn";

export default {
	name: "ExistsFilter",
	components: {
		Select2
	},
	mixins: [ GenericFilter ],
	props: {
		filterData: {
			type: Object,
			default: () => {
			}
		},
		filterIndex: {
			type: Number,
			default: () => 0,
		},
		savedData: {
			type: Object,
			default: () => {
			}
		},
		fieldData: {
			type: Object,
			default: () => {
			}
		},
	},
	emits: [ 'propertyChange' ],
	methods: {
		changeProp( value ) {
			if ( value !== this.savedData.value ) {
				this.$emit( 'propertyChange', {
					value,
					validation: validateValue( value, this.fieldData.validators || [] )
				} );

				this.setPreview( prettyPreview( `${this.fieldData.name} $$value `, this.filterData.info.operators?.[ value ]?.label ) );
			}
		}
	}
}
</script>


