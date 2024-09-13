<template>
	<!-- eslint-disable -->
	<div class="tap-operation tap-filter-col">
		{{ filterLabel }}
	</div>
	<div v-if="checkboxes?.length" :class="savedData.validation?.isValid ? '': 'tap-error'" class="tap-filter-col--full tap-checkbox-filter tap-flex--wrap tap-flex--start">
		<checkbox v-for="(item,i) in checkboxes" :id="item.id" :key="item.id" :text="item.label" :value="savedData.value?.includes(item.id)" @input="changeProp"/>
	</div>
	<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
		{{ savedData.validation?.message }}
	</div>
	<!-- eslint-enable -->
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import Checkbox from "@/components/general/Checkbox";
import { prettyPreview, validateValue } from "@/utils/data-fn";
import { mapGetters } from "vuex";

export default {
	name: "CheckboxFilter",
	components: {
		Checkbox,
	},
	mixins: [ GenericFilter ],
	emits: [ 'propertyChange' ],
	computed: {
		...mapGetters( 'fields', [ 'getFieldOptions' ] ),
		filterLabel() {
			return this.filterData.info.operators.checkbox.label || 'is any of the following';
		},
		potentialValues() {
			return this.filterData.additional?.value || this.getFieldOptions?.[ this.savedData.data_object ]?.[ this.fieldData.id ] || {}
		},
		checkboxes() {
			const data = [];
			Object.keys( this.potentialValues ).forEach( key => {
				data.push( {
					id: String( this.potentialValues[ key ].id ),
					label: this.potentialValues[ key ].label
				} )
			} );
			return data;
		}
	},
	mounted() {
		// init field value if doesnt exist
		if ( ! this.savedData.value ) {
			this.changeProp( '', false );
		}
	},
	methods: {
		changeProp( key, checked ) {
			const data = this.savedData.value || [];
			if ( checked ) {
				data.push( key )
			} else {
				const index = data.indexOf( key );
				data.splice( index, 1 );
			}

			this.$emit( 'propertyChange', {
				value: data,
				validation: validateValue( data, this.fieldData.validators || [] )
			} );
			const previewData = data.map( item => {
				const itemData = this.checkboxes.find( valueItem => item == valueItem.id );
				return itemData ? itemData.label : item;
			} );
			this.setPreview( prettyPreview( `${this.fieldData.name} is any of the following $$value `, previewData ) );
		}
	}
}
</script>


