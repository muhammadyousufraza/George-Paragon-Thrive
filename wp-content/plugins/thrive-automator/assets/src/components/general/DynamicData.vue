<template>
	<div class="tap-dynamic-data tap-shortcodes">
		<div class="tap-dynamic-search">
			<search :value="fieldSearch" @search="fieldSearch=$event"/>
		</div>
		<div class="tap-dynamic-data-items tap-scrollbar">
			<div v-for="(field, index) in filteredFields" :key="index" class="tap-toggle-item">
				<div :class="{'is-active':activeItems.includes(index) || fieldSearch?.trim()?.length}" class="tap-toggle-heading tap-flex--between" @click="toggle(index)">
					<div class="tap-toggle-title">
						{{ field.label }}
					</div>
					<div class="tap-toggle-icon">
						<icon icon-name="tap-caret-down"/>
					</div>
				</div>
				<transition name="tap-toggle">
					<div v-if="activeItems.includes(index)|| fieldSearch?.trim()?.length" class="tap-toggle-content tap-flex--column">
						<div v-for="(subfield, subIndex) in field.subfields" :key="subIndex" class="tap-dynamic-item" @click="$emit('addShortcode', subfield.shortcode_tag)">
							{{ subfield.name }}
						</div>
					</div>
				</transition>
			</div>
			<div v-if="noData" class="tap-no-data tap-fw ">
				No results found
			</div>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import Search from "@/components/general/Search";

export default {
	name: "DynamicData",
	components: {
		Icon,
		Search
	},
	props: {
		/**
		 * for data format
		 * @see dynamicDataFields
		 */
		dynamicData: {
			type: Object,
			default: () => {
			}
		}
	},
	emits: [ 'addShortcode' ],
	data() {
		return {
			activeItems: [],
			fieldSearch: ''
		}
	},
	computed: {
		noData() {
			return ! Object.keys( this.filteredFields )?.length;
		},
		filteredFields() {
			const fields = TAPAdmin._.cloneDeep( this.dynamicData || '' );
			if ( this.fieldSearch ) {
				Object.keys( fields ).forEach( key => {
					fields[ key ].subfields = fields[ key ].subfields.filter( field => {
						const searchText = this.fieldSearch.toLowerCase(),
							splitWords = searchText.replace( /\s\s+/g, ' ' ).replaceAll( ' ', '|' ),
							regex = new RegExp( splitWords, 'g' ),
							fieldText = field.name.toLowerCase();

						return fieldText.includes( searchText ) || fieldText.match( regex )?.length;
					} )
					if ( ! fields[ key ].subfields?.length ) {
						delete fields[ key ];
					}
				} );

			}

			return fields;
		},
	},
	methods: {
		toggle( id ) {
			this.activeItems = TAPAdmin._.xor( this.activeItems, [ id ] );
		},
	}
}
</script>
