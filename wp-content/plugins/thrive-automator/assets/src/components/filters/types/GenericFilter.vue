<template>
	<div class="tap-generic-filter"/>
</template>

<script>
import { select2Option } from "@/utils/ui-fn";

export default {
	name: "GenericFilter",
	props: {
		filterData: {
			type: Object,
			default: () => {
			}
		},
		filterIndex: {
			type: Number,
			default: () => 0,
		},
		savedData: {
			type: Object,
			default: () => {
			}
		},
		fieldData: {
			type: Object,
			default: () => {
			}
		},
		filterType: {
			type: String,
			default: () => 'filter',
		}
	},
	emits: [ 'fetchMore' ],
	computed: {
		operatorOptions() {
			const options = [ {id: '', text: ''} ];
			Object.keys( this.filterData.info.operators ).forEach( operator => {
				options.push( {
					id: operator,
					text: this.filterData.info.operators[ operator ].label
				} )
			} );

			return {
				data: options,
				minimumResultsForSearch: - 1,
				placeholder: 'Select a value',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option
			};
		},
	},
	methods: {
		getItemById( item ) {
			return Object.values( this.storedData ).find( valueItem => item == valueItem.id );
		},
		setPreview( preview ) {
			this.$emit( 'propertyChange', {preview} )
		}
	}
}
</script>
