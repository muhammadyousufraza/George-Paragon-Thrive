<template>
	<!-- eslint-disable -->
	<div class="tap-operation--center tap-filter-col--square">
		=
	</div>
	<div :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--45">
		<select-toggle :stored-data="selectOptions" :current-value="savedData.value" :with-search="true" :search-classes="['tap-select-filter-search']" :wrapper-classes="[`tap-select-filter-dropdown`, 'tap-field-dropdown']" @select="changeProp" @dropdown-search="onSearch" @dropdown-scroll="loadMore"/>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import selectToggle from "@/components/general/SelectToggle";
import { prettyPreview, validateValue } from "@/utils/data-fn";
import { mapGetters } from "vuex";
import AutocompleteFilter from "@/components/filters/types/AutocompleteFilter";

export default {
	name: "BooleanFilter",
	components: {
		selectToggle
	},
	mixins: [ AutocompleteFilter ],
	emits: [ 'propertyChange' ],
	computed: {
		...mapGetters( 'fields', [ 'getFieldOptions' ] ),
		selectOptions() {
			const options = [];

			Object.keys( this.storedData ).forEach( key => {
				options.push( {
					value: this.storedData[ key ].id,
					label: this.storedData[ key ].label || this.storedData[ key ].name,
				} )
			} );

			return options;
		}
	},
	methods: {
		changeProp( value ) {
			if ( value !== this.savedData.value ) {
				this.$emit( 'propertyChange', {
					value,
					validation: validateValue( value, this.fieldData.validators || [] )
				} );

				const previewItem = this.getItemById( value );

				this.setPreview( prettyPreview( `${this.fieldData.name} is the following $$value `, previewItem ? previewItem.label : value ) );
			}
		}
	}
}
</script>


