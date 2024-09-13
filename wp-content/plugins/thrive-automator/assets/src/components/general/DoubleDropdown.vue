<template>
	<div class="tap-dd-wrapper">
		<select2 :options="mainSelectOptions" :value="mainValue" @input="$emit('changeMain',$event)"/>
		<select2 v-if="mainValue" :options="secondSelectOptions" :value="secondValue" @input="$emit('changeSec',$event)"/>
	</div>
</template>

<script>
import Select2 from "@/components/general/Select2";
import { select2Matcher, select2Option } from "@/utils/ui-fn";

export default {
	name: "DoubleDropdown",
	components: {
		Select2
	},
	props: {
		dropdownData: {
			type: Object,
			default: () => {
			}
		},
		mainValue: {
			type: String,
			default: () => ''
		},
		secondValue: {
			type: String,
			default: () => ''
		},
		mainDesc: {
			type: String,
			default: () => ''
		},
		secondDesc: {
			type: String,
			default: () => ''
		},

	},
	emits: [ 'changeMain', 'changeSec' ],
	computed: {
		mainSelectOptions() {
			const options = [ {id: '', text: ''} ];
			Object.keys( this.dropdownData ).forEach( key => {
				options.push( {
					id: this.dropdownData[ key ].id,
					text: this.dropdownData[ key ].name || this.dropdownData[ key ].label
				} )
			} );
			return {
				data: options,
				placeholder: this.mainDesc || 'Choose',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};
		},
		secondSelectOptions() {
			const options = [],
				matchedItem = Object.values( this.dropdownData )?.filter( item => item.id == this.mainValue )?.[ 0 ];
			if ( matchedItem ) {
				Object.keys( matchedItem.values ).forEach( key => {
					options.push( {
						id: matchedItem.values[ key ].id,
						text: matchedItem.values[ key ].name || matchedItem.values[ key ].label
					} )
				} );
			}

			return {
				data: options,
				placeholder: this.secondDesc || 'Choose',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};
		}
	}
}
</script>


