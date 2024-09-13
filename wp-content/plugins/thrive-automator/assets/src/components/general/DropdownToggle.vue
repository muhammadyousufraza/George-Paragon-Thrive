<template>
	<dropdown :offset="[0,0]" :theme="'automator_toggle_data'" placement="bottom-start" @apply-show="onShow">
		<div class="tap-autocomplete-toggle-trigger tap-flex tap-fw">
			<search ref="searchElem" :is-full-width="true" :placeholder="placeholder" :value="fieldSearch" @search="onSearch" @click="onSearchClick"/>
		</div>
		<template #popper>
			<div ref="closeSelect" v-close-popper class="tap-hidden tap-toggle-close"/>
			<div :class="wrapperClasses" class="tap-dynamic-data tap-dynamic-autocomplete">
				<div ref="scrollableArea" class="tap-dynamic-data-items tap-scrollbar" @scroll="$emit('dropdownScroll',$event)">
					<toggle-item
						v-for="(wrapper,wrapperIndex) in toggleData"
						:key="wrapperIndex"
						:active-items="activeItems"
						:content="wrapper"
						:content-index="wrapperIndex"
						:content-levels="[wrapperIndex]"
						:field-search="fieldSearch"
						:show-count="showCount"
						:suffixes="suffixes"
						@select-item="selectValue"
						@toggle-item="$emit('toggle',$event)"/>
				</div>
				<div v-if="noData" class="tap-no-data tap-fw ">
					No results found
				</div>
			</div>
		</template>
	</dropdown>
</template>

<script>
import Search from "@/components/general/Search";
import ToggleItem from "@/components/general/ToggleItem";
import { triggerElementEvent } from "@/utils/ui-fn";
import { Dropdown } from "v-tooltip";

export default {
	name: "DropdownToggle",
	components: {
		ToggleItem,
		Dropdown,
		Search,
	},
	props: {
		storedData: {
			type: Object,
			default: () => {
			}
		},
		activeItems: {
			type: Array,
			default: () => []
		},
		dropdownClasses: {
			type: Array,
			default: () => []
		},
		wrapperClasses: {
			type: Array,
			default: () => []
		},
		placeholder: {
			type: String,
			default: () => 'Search for a value'
		},
		showCount: {
			type: Boolean,
			default: false
		},
		suffixes: {
			type: Object,
			default: () => {
			}
		}
	},
	emits: [ 'select', 'toggle', 'dropdownScroll', 'dropdownSearch' ],
	data() {
		return {
			fieldSearch: '',
		}
	},
	computed: {
		noData() {
			return ! Object.keys( this.toggleData )?.length;
		},
		toggleData() {
			const fields = TAPAdmin._.cloneDeep( this.storedData ),
				searchValue = this.fieldSearch.toLowerCase(),
				checkingItems = data => {
					const isArray = Array.isArray( data );
					Object.keys( data || {} ).reverse().forEach( key => {
						const fieldValue = this.getFieldValue( data[ key ] )
						if ( fieldValue ) {
							if ( ! String( fieldValue ).toLowerCase().includes( searchValue ) && ! String( this.getFieldLabel( data[ key ] ) ).toLowerCase().includes( searchValue ) ) {
								if ( isArray ) {
									data.splice( key, 1 );
								} else {
									delete data[ key ];
								}
							}
						} else if ( Object.keys( data[ key ]?.items )?.length ) {
							checkingItems( data[ key ].items );
						}
					} )
				},
				checkEmptyFields = data => {
					const isArray = Array.isArray( data );

					Object.keys( data ).reverse().forEach( key => {

						if ( Object.keys( data[ key ]?.items || {} )?.length ) {
							checkEmptyFields( data[ key ].items );
							if ( ! Object.keys( data[ key ].items || {} )?.length ) {
								delete data[ key ];
							}
						} else if ( ! this.getFieldValue( data[ key ] ) ) {
							if ( isArray ) {
								data.splice( key, 1 );
							} else {
								delete data[ key ];
							}
						}
					} )
					return data;
				};
			if ( this.fieldSearch ) {
				checkingItems( fields );
			}

			checkEmptyFields( fields );
			return fields;
		},
		valueLabel() {
			let label = '';
			const checkingItems = data => {
				Object.keys( data || {} ).forEach( key => {
					if ( this.getFieldValue( data[ key ] ) == this.currentValue ) {
						label = data[ key ].label;
					} else if ( Object.keys( data[ key ]?.items || {} )?.length ) {
						checkingItems( data[ key ].items );
					}
				} )
			};
			if ( this.currentValue ) {
				checkingItems( this.storedData );
			}
			return label;
		}
	},
	methods: {
		onSearch( searchValue ) {
			this.fieldSearch = searchValue;
			this.$emit( 'dropdownSearch', searchValue );
		},
		onSearchClick() {
			triggerElementEvent( this.$refs.searchElem.$el.parentNode, 'click' );
		},
		onShow() {
			if ( this.activeItems.length === 1 ) {
				this.$refs.scrollableArea.querySelector( `.tap-toggle-item[data-item-index="${this.activeItems[ 0 ]}"]` )?.scrollIntoView()
			}
		},
		selectValue( value, subIndexes ) {
			triggerElementEvent( this.$refs.closeSelect, 'click' );
			this.$emit( 'select', value, subIndexes );
		},
		getFieldValue( data ) {
			return data?.value || data?.id || '';
		},
		getFieldLabel( data ) {
			return data?.label || data?.name || '';
		},
	}
}
</script>
