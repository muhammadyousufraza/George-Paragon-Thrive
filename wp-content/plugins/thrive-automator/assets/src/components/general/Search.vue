<template>
	<div :class="[...extraClasses, ...[isFullWidth?'tap-fw':'']]" class="tap-search-wrapper">
		<div v-if="label" class="tap-search-label">
			{{ label }}
		</div>
		<div class="tap-search-input">
			<input-field
				:placeholder="placeholder"
				:trigger-input-on-blur="false"
				:type="'text'"
				:value="value"
				@input="doSearch"/>
			<Icon
				v-if="!value"
				:icon-name="'tap-search'"
				:wrapper-classes="'tap-search-icon'"/>
			<Icon
				v-if="value"
				:icon-name="'tap-cross'"
				wrapper-classes="tap-search-icon tap-clear-icon"
				@click="clearValue"/>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import InputField from "@/components/general/InputField";

export default {
	name: "Search",
	components: {
		InputField,
		Icon
	},
	props: {
		label: {
			type: String,
			default: () => ''
		},
		value: {
			type: String,
			default: () => ''
		},
		placeholder: {
			type: String,
			default: () => 'Search'
		},
		debounceTime: {
			type: Number,
			default: () => 200,
		},
		isFullWidth: {
			type: Boolean,
			default: () => false,
		},
		extraClasses: {
			type: Array,
			default: () => []
		}
	},
	data() {
		return {
			debounce: false
		}
	},
	methods: {
		doSearch( value ) {
			clearTimeout( this.debounce );
			this.debounce = setTimeout( () => {
				this.$emit( 'search', value );
			}, this.debounceTime )
		},
		clearValue() {
			this.$emit( 'search', '' );
		}
	}
}
</script>


