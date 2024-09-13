<template>
	<!-- eslint-disable -->
	<div class="tap-operation tap-filter-col">
		{{ filterLabel }}
	</div>
	<div class="tap-filter-col--full">
		<dropdown-toggle :active-items="activeItems" :stored-data="storedData" :wrapper-classes="['tap-filter-dropdown']" @select="selectValue" @toggle="toggle"/>
		<div v-if="savedData.savedLevels?.length" class="tap-autocomplete-values tap-flex--start tap-flex--wrap">
			<div v-for="(item,index) in savedData.savedLevels" :key="index" class="tap-autocomplete-item tap-flex">
        <span class="tap-autocomplete-title">
          {{ getItemAtLevel( item )?.value }}
        </span>
				<icon icon-name="tap-cross" wrapper-classes="ml-10" @click="deleteItem(item)"/>
			</div>
		</div>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message || 'Invalid. Please select at least one item' }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import AutocompleteFilter from "@/components/filters/types/AutocompleteFilter";
import DropdownToggle from "@/components/general/DropdownToggle";
import Icon from "@/components/general/Icon";
import { prettyPreview, validateValue } from "@/utils/data-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "AutocompleteFilter",
	components: {
		DropdownToggle,
		Icon
	},
	mixins: [ AutocompleteFilter ],
	emits: [ 'propertyChange' ],
	data() {
		return {
			activeItems: [],
			fieldSearch: ''
		}
	},
	computed: {
		...mapGetters( 'fields', [ 'getFieldOptions' ] ),
		filterLabel() {
			return this.filterData.info.operators?.checkbox?.label || 'is any of the following';
		},
		selectClasses() {
			return this.savedData.validation?.isValid ? '' : 'tap-error';
		},
		storedData() {
			return this.getFieldOptions?.[ this.savedData.data_object ]?.[ this.fieldData.id ] || {};
		},

	},
	watch: {
		'fieldData.id': {
			handler() {
				this.activeItems = [];
				this.fieldSearch = '';
			},
			deep: true
		},
	},
	mounted() {
		if ( ! this.savedData.savedLevels ) {
			this.changeProp( {savedLevels: [], value: []} )
		}
	},
	methods: {
		...mapActions( 'steps', [ 'updateStep' ] ),
		deleteItem( id ) {
			const savedData = this.savedData,
				index = savedData.value.indexOf( id );

			savedData.value.splice( index, 1 );
			savedData.savedLevels.splice( index, 1 );
			savedData.validation = validateValue( savedData.value, this.fieldData.validators || [] )

			this.changeProp( savedData );
		},
		getItemAtLevel( levels ) {
			levels = levels.reduce( ( r, a ) => r.concat( a, 'items' ), [] );
			levels.pop();
			return TAPAdmin._.get( this.storedData, levels );
		},
		selectValue( value, subIndexes ) {
			if ( ! this.savedData?.savedLevels || ! JSON.stringify( this.savedData.savedLevels ).includes( JSON.stringify( subIndexes ) ) ) {
				const savedData = {
					...{
						savedLevels: [],
						value: []
					},
					...this.savedData
				};
				if ( ! savedData.value ) {
					savedData.value = [];
				}

				savedData.savedLevels.push( subIndexes );
				savedData.value.push( value );
				savedData.validation = validateValue( savedData.value, this.fieldData.validators || [] )
				this.changeProp( savedData );
			}
		},
		changeProp( savedData ) {
			this.$emit( 'propertyChange', savedData );
			if ( savedData.value ) {
				this.setPreview( prettyPreview( `${this.fieldData.name} is any of the following $$value `, savedData.value ) );
			}
		},
		toggle( id ) {
			this.activeItems = TAPAdmin._.xor( this.activeItems, [ id ] );
		},
	}

}
</script>


