<template>
	<!-- eslint-disable -->
	<div class="tap-operation tap-filter-col">
		{{ filterLabel }}
	</div>
	<div v-if="radios?.length" :class="savedData.validation?.isValid ? '': 'tap-error'" class="tap-filter-col--full tap-radio-filter tap-flex--wrap tap-flex--start">
		<radio v-for="(item,i) in radios" :id="item.id" :key="i" :text="item.text" :value="savedData.value == item.id" @input="changeProp">
		</radio>
	</div>
	<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
		{{ savedData.validation?.message }}
	</div>
	<!-- eslint-enable -->
</template>

<script>
import Radio from "@/components/general/Radio";
import { prettyPreview, validateValue } from "@/utils/data-fn";
import { mapGetters } from "vuex";
import CheckboxFilter from "@/components/filters/types/CheckboxFilter";

export default {
	name: "RadioFilter",
	components: {
		Radio
	},
	mixins: [ CheckboxFilter ],
	emits: [ 'propertyChange' ],
	computed: {
		...mapGetters( 'fields', [ 'getFieldOptions' ] ),
		filterLabel() {
			return this.filterData.info.operators.radio?.label || 'is one of the following';
		},
		radios() {
			const data = [];
			Object.keys( this.potentialValues ).forEach( key => {
				data.push( {
					id: key,
					text: this.potentialValues[ key ].label
				} )
			} );
			return data;
		}
	},
	methods: {
		changeProp( key, checked ) {
			const newValue = checked ? key : '';

			this.$emit( 'propertyChange', {
				value: newValue,
				validation: validateValue( newValue, this.fieldData.validators || [] )
			} );
			this.setPreview( prettyPreview( `${this.fieldData.name} is the following $$value `, newValue ? this.potentialValues[ newValue ].label : '' ) );
		}
	}
}
</script>
