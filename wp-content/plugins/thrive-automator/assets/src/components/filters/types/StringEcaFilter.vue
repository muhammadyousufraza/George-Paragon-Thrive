<template>
	<!-- eslint-disable -->
	<div class="tap-filter-col">
		<select2 :class="{'tap-error': !savedData.operator}" :options="operatorOptions" :value="savedData.operator" @input="changeOperator"/>
		<div v-if="!savedData.operator" class="tap-filter-error">
			Please select a value
		</div>
	</div>
	<div v-if="showInput||showAutocomplete" :class="{'tap-error': !savedData.validation?.isValid}" class="tap-filter-col--full">
		<input-field v-if="showInput" :value="savedData.value" placeholder="Enter strings separated by commas" @input="changeValue"/>
		<select2 v-if="showAutocomplete" :options="autocompleteOptions" :wrapper-classes="autocompleteClasses" @input="autocompleteValue"/>
		<div v-if="showAutocomplete&&savedData.value?.length" class="tap-autocomplete-values tap-flex--start tap-flex--wrap">
			<div v-for="(item,index) in savedData.value" :key="index" class="tap-autocomplete-item tap-flex">
        <span class="tap-autocomplete-title">
          {{ getLabel( item ) }}
        </span>
				<icon icon-name="tap-cross" wrapper-classes="ml-10" @click="deleteItem(item)"/>
			</div>
		</div>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import Icon from "@/components/general/Icon";
import InputField from "@/components/general/InputField";
import Select2 from "@/components/general/Select2";
import { prettyPreview, validateValue } from "@/utils/data-fn";
import { select2Option } from "@/utils/ui-fn";
import { mapGetters } from "vuex";

export default {
	name: "StringEcaFilter",
	components: {
		Select2,
		Icon,
		InputField,
	},
	mixins: [ GenericFilter ],
	emits: [ 'propertyChange' ],
	computed: {
		...mapGetters( 'fields', [ 'getFieldOptions' ] ),
		showInput() {
			return 'contains' === this.savedData.operator
		},
		showAutocomplete() {
			return 'autocomplete' === this.savedData.operator
		},
		autocompleteClasses() {
			return this.savedData.validation?.isValid ? '' : 'tap-error';
		},
		storedData() {
			return this.getFieldOptions?.[ this.savedData.data_object ]?.[ this.fieldData.id ] || [];
		},
		autocompleteOptions() {
			const options = [];

			Object.keys( this.storedData ).forEach( key => {
				if ( ! this.savedData.value?.includes( key ) ) {
					options.push( {
						id: this.storedData[ key ].id,
						text: this.storedData[ key ].label
					} )
				}
			} );

			return {
				data: options,
				tags: true,
				multiple: true,
				clearOnSelect: true,
				createTag: () => '',
				placeholder: this.fieldData?.description || 'Choose',
				theme: 'thrive-automator',
				templateResult: select2Option
			};
		},
	},
	methods: {
		changeOperator( value ) {
			if ( this.savedData.operator !== value ) {
				let validator = this.fieldData.validators;
				const isBool = [ 'empty', 'not_empty' ].includes( value );

				if ( isBool ) {
					validator = [];
				}
				this.changeProp( {
					operator: value,
					value: '',
					validation: isBool ? {isValid: true, message: ''} : validateValue( '', validator )
				} );
			}

		},
		getLabel( id ) {
			return Object.values( this.storedData )?.filter( data => data.id == id )?.[ 0 ]?.label
		},
		changeValue( value ) {
			this.changeProp( {
				value,
				validation: validateValue( value, this.fieldData.validators || [] )
			} );
		},
		changeProp( key, value ) {
			this.$emit( 'propertyChange', key, value );
			if ( key?.operator || key?.value ) {
				const operator = key?.operator || this.savedData.operator,
					isBool = [ 'empty', 'not_empty' ].includes( key?.operator || this.savedData.operator );
				let preview = `${this.fieldData.name} ${this.filterData.info.operators?.[ operator ]?.label}`;

				if ( ! isBool ) {
					let previewValue = key?.value || this.savedData.value;
					if ( operator === 'contains' ) {
						previewValue = previewValue.split( ',' );
					}
					preview = prettyPreview( `${preview} $$value`, previewValue );
				}

				this.setPreview( preview );
			}
		},
		deleteItem( id ) {
			const savedData = this.savedData.value,
				index = savedData.indexOf( id );

			savedData.splice( index, 1 );

			this.changeProp( {value: savedData} );
		},
		autocompleteValue( value ) {

			if ( Object.values( this.storedData )?.filter( data => data.id == value )?.length && ! this.savedData.value?.includes( value ) ) {
				let savedData = this.savedData.value;

				if ( ! savedData ) {
					savedData = [];
				}
				savedData.push( value );
				this.changeProp( {
					value: savedData,
					validation: validateValue( savedData, this.fieldData.validators || [] )
				} );
			}
		},
	}
}
</script>


