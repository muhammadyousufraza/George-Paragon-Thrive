<template>
	<!-- eslint-disable -->
	<div class="tap-operation tap-filter-col">
		{{ filterLabel }}
	</div>
	<div class="tap-filter-col--full" :class="{'tap-error': !savedData.validation?.isValid}">
		<dropdown-toggle :stored-data="selectOptions" :wrapper-classes="[`tap-filter-dropdown`]" @select="selectValue" @dropdown-search="onSearch" @dropdown-scroll="loadMore"/>
		<div v-if="savedData.value?.length" class="tap-autocomplete-values tap-flex--start tap-flex--wrap">
			<div v-for="(item,index) in savedData.value" :key="index" class="tap-autocomplete-item tap-flex">
        <span class="tap-autocomplete-title">
          {{ getLabel( item ) }}
        </span>
				<icon icon-name="tap-cross" wrapper-classes="ml-10" @click="deleteItem(item)"
				/>
			</div>
		</div>
		<div v-if="!savedData.validation?.isValid" class="tap-filter-error">
			{{ savedData.validation?.message || 'Invalid. Please select at least one item' }}
		</div>
	</div>
	<!-- eslint-enable -->
</template>

<script>
import GenericFilter from "@/components/filters/types/GenericFilter";
import Icon from "@/components/general/Icon";
import DropdownToggle from "@/components/general/DropdownToggle";
import { prettyPreview, validateValue } from "@/utils/data-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "AutocompleteFilter",
	components: {
		DropdownToggle,
		Icon
	},
	mixins: [ GenericFilter ],
	emits: [ 'propertyChange', 'fetchMore' ],
	data() {
		return {
			loadPage: 0,
		}
	},
	computed: {
		...mapGetters( 'fields', [ 'getFieldOptions' ] ),
		loadLimit() {
			return this.fieldData.extra_options?.limit ?? TAPAdmin.load_limit;
		},
		filterLabel() {
			return this.filterData.info.operators?.checkbox?.label || 'is any of the following';
		},
		selectClasses() {
			return this.savedData.validation?.isValid ? '' : 'tap-error';
		},
		storedData() {
			return this.getFieldOptions?.[ this.savedData.data_object ]?.[ this.fieldData.id ] || {};
		},
		selectOptions() {
			const options = [];

			Object.keys( this.storedData ).forEach( key => {
				if ( ! this.savedData.value?.includes( key ) && ! this.savedData.value?.includes( this.storedData[ key ].id ) ) {
					options.push( {
						value: this.storedData[ key ].id,
						label: this.storedData[ key ].label || this.storedData[ key ].name,
					} )
				}
			} );

			return options;
		}
	},
	methods: {
		...mapActions( 'steps', [ 'updateStep' ] ),
		onSearch( searchValue ) {
			if ( searchValue.length ) {
				this.$emit( 'fetchMore', {
					search: searchValue,
					limit: - 1,
					page: 1,
					preventLoader: true,
				}, false )
			}
		},
		loadMore( event ) {
			const list = event.target;
			if ( list.scrollTop && list.childNodes.length >= this.loadLimit ) {
				const children = [ ...list.childNodes ].filter( t => t instanceof Element );
				if ( children.length ) {
					let newPage = parseInt( list.scrollTop / children[ 0 ].offsetHeight / ( this.loadLimit / 5 ) );
					if ( newPage === this.loadPage ) {
						newPage ++;
					}

					if ( newPage > this.loadPage ) {
						this.scrollTop = list.scrollTop;

						const oldPage = this.loadPage;
						this.loadPage = newPage;
						this.$emit( 'fetchMore', {
							limit: this.loadLimit * ( newPage - oldPage ),
							page: this.loadPage,
							preventLoader: true,
						}, false )
					}
				}
			}
		},
		getLabel( id ) {
			return Object.values( this.storedData ).filter( data => data.id == id )?.[ 0 ]?.label;
		},
		deleteItem( id ) {
			const savedData = this.savedData.value,
				index = savedData.indexOf( id );

			savedData.splice( index, 1 );

			this.changeProp( savedData );
		},
		selectValue( value ) {
			if ( Object.values( this.storedData ).filter( data => data.id == value )?.length && ! this.savedData.value.includes( value ) ) {
				let savedData = this.savedData.value;

				if ( ! savedData ) {
					savedData = [];
				}
				savedData.push( value );
				this.changeProp( savedData );
			}
		},
		changeProp( savedData ) {
			this.$emit( 'propertyChange', {
				value: savedData,
				validation: validateValue( savedData, this.fieldData.validators || [] )
			} );
			this.setPreview( this.getPrettyPreview( savedData ) );
		},
		getPrettyPreview( savedData ) {
			const itemsPreview = savedData.map( item => {
				const itemData = this.getItemById( item );
				return itemData ? itemData.label : item;
			} );

			return prettyPreview( `${this.fieldData.name} is any of the following $$value `, itemsPreview )
		}
	}

}
</script>


