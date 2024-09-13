<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-autocomplete-field tap-action-field">
		<div>
			<dropdown-toggle :active-items="activeItems" :placeholder="fieldData.placeholder" :stored-data="storedData" @select="selectValue" @toggle="toggle"/>
			<div v-if="fieldValue?.length" class="tap-autocomplete-values tap-flex--start tap-flex--wrap">
				<div v-for="(item,index) in fieldValue" :key="index" class="tap-autocomplete-item tap-flex">
					<icon v-if="String(item)?.includes('tap-dynamic')" :wrapper-classes="'mr-5'" icon-name="tap-database"/>
					<span class="tap-autocomplete-title">{{ getLabel( item ) }}</span>
					<icon icon-name="tap-cross" wrapper-classes="ml-10" @click="deleteItem(item)"/>
				</div>
			</div>
		</div>
		<div v-if="hasErrors && !fieldValidation?.isValid" class="tap-filter-error">
			{{ fieldValidation?.message }}
		</div>
	</div>
</template>

<script>
import AutocompleteField from "@/components/fields/AutocompleteField";
import DropdownToggle from "@/components/general/DropdownToggle";
import { ucFirst } from "@/utils/data-fn";

export default {
	name: 'AutocompleteToggleField',
	components: {
		DropdownToggle
	},
	mixins: [ AutocompleteField ],
	data() {
		return {
			activeItems: [],
		}
	},
	computed: {
		storedData() {
			const data = TAPAdmin._.cloneDeep( this.ajaxValues );

			if ( this.fieldData.allowed_data_set_values.length ) {
				this.fieldData.allowed_data_set_values.forEach( item => {
					if ( this.getStepDataObjects( this.stepIndex ).includes( item ) ) {
						data[ `tap-dynamic-${item}` ] = {
							value: `tap-dynamic-${item}`,
							label: `Dynamic ${this.getDataObjects[ item ].name || ucFirst( item.replace.replaceAll( '_', ' ' ) )}`,
							image: 'tap-database'
						}
					}
				} );
			}

			return TAPAdmin._.sortBy( data, 'value' );
		}
	},
	methods: {
		getLabel( value ) {
			let label = '';
			const checkingItems = data => {
				Object.keys( data || {} ).forEach( key => {
					if ( data[ key ]?.value == value ) {
						label = data[ key ].label;
					} else if ( Object.keys( data[ key ]?.items || {} )?.length ) {
						checkingItems( data[ key ].items );
					}
				} )
			};
			checkingItems( this.storedData );
			return label;
		},
		toggle( id ) {
			this.activeItems = TAPAdmin._.xor( this.activeItems, [ id ] );
		},
		selectValue( value ) {
			if ( ! this.fieldValue?.includes( value ) ) {
				const savedData = this.fieldValue ? [ ...this.fieldValue ] : [];
				savedData.push( value );
				this.changeProp( savedData );
			}
		},
	}
}
</script>
