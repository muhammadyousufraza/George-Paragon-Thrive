<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-autocomplete-field tap-action-field">
		<div>
			<dropdown-toggle :placeholder="fieldData.placeholder" :stored-data="fieldOptions" :wrapper-classes="[`tap-${parentType}-dropdown`, 'tap-field-dropdown']" @select="selectValue" @dropdown-search="onSearch" @dropdown-scroll="loadMore"/>
			<div v-if="fieldValue?.length" class="tap-autocomplete-values tap-flex--start tap-flex--wrap">
				<div v-for="(item,index) in fieldValue" :key="index" class="tap-autocomplete-item tap-flex">
					<icon v-if="String( item ).includes('tap-dynamic')" :wrapper-classes="'mr-5'" icon-name="tap-database"/>
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
import GenericField from "@/components/fields/GenericField";
import Icon from "@/components/general/Icon";
import { prettyPreview } from "@/utils/data-fn";
import { mapGetters } from "vuex";
import DropdownToggle from "@/components/general/DropdownToggle";

export default {
	name: "AutocompleteField",
	components: {
		Icon,
		DropdownToggle
	},
	mixins: [ GenericField ],
	data() {
		return {
			syncOnChange: true,
			scrollTop: 0,
		}
	},
	computed: {
		...mapGetters( 'fields', [ 'getActionFields' ] ),
		...mapGetters( 'steps', [ 'getStepDataObjects' ] ),
		...mapGetters( 'generic', [ 'getDataObjects' ] ),
		fieldOptions() {
			const options = [ ...this.dynamicOptions ];

			Object.keys( this.ajaxValues ).forEach( key => {
				if ( ! this.fieldValue?.some( savedValue => savedValue == this.ajaxValues[ key ].id ) ) {
					options.push( {
						value: this.ajaxValues[ key ].id,
						label: this.ajaxValues[ key ].label || this.ajaxValues[ key ].name,
					} )
				}
			} );

			return options;
		},
	},
	methods: {
		onSearch( searchValue ) {
			if ( searchValue.length ) {
				this.fetchValues( {
					search: searchValue,
					limit: - 1,
					page: 1,
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
						this.fetchValues( {
							limit: this.loadLimit * ( newPage - oldPage ),
						}, false )
					}
				}
			}
		},
		getPrettyPreview( value ) {
			let preview = prettyPreview( this.previewValue, value )
			value.forEach( item => preview = preview.replaceAll( item, this.getLabel( item ) ) );
			return preview;
		},
		getLabel( id ) {
			const availableOptions = [ ...this.dynamicOptions, ...Object.values( this.ajaxValues || {} ) ],
				item = availableOptions?.filter( data => data.id == id )?.[ 0 ] || {};

			return item.label || item.name;
		},
		deleteItem( id ) {
			const savedData = [ ...this.fieldValue ],
				index = savedData.indexOf( id );

			savedData.splice( index, 1 );

			this.changeProp( savedData );
		},
		selectValue( value ) {

			if ( this.fieldOptions?.filter( data => data.value == value )?.length && ( ! this.fieldValue?.length || ! this.fieldValue?.some( savedValue => savedValue == value ) ) ) {
				const savedData = this.fieldValue ? [ ...this.fieldValue ] : [];
				savedData.push( value );
				this.changeProp( savedData );
			}
		},
	}
}
</script>
