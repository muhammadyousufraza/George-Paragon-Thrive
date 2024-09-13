<template>
	<div :class="moreClasses" class="tap-input-container tap-flex">
		<span v-if="label?.trim()?.length" class="tap-input-label">{{ label }}</span>
		<input
			ref="inputEl"
			:max="max"
			:min="min"
			:placeholder="placeholder"
			:type="type"
			:value="value"
			:accept="accept"
			:autocomplete="autocomplete"
			@keyup.enter="handleEnter"
			@input.stop.prevent="handleInput"
			@blur.stop.prevent="handleBlur">
		<div v-if="hasDynamicData" class="tap-vert-separator"/>
		<dropdown
			v-if="hasDynamicData"
			:theme="'automator_dynamic_data'"
			placement="bottom-end">
			<div
				v-tooltip="{
          content: 'Insert dynamic data',
          theme: 'automator',
          offset: [0, 10],
        }"
				class="tap-dynamic-data-trigger tap-flex">
				<icon icon-name="tap-database"/>
			</div>
			<template #popper>
				<div ref="closeSelect" v-close-popper class="tap-hidden tap-toggle-close"/>
				<dynamic-data :dynamic-data="dynamicDataFields" @addShortcode="handleShortcode"/>
			</template>
		</dropdown>
	</div>
</template>

<script>
import DynamicData from "@/components/general/DynamicData";
import Icon from "@/components/general/Icon";
import { triggerElementEvent } from "@/utils/ui-fn";
import { Dropdown } from "v-tooltip";

export default {
	name: "InputField",
	components: {
		Dropdown,
		Icon,
		DynamicData
	},
	props: {
		value: {
			type: [ String, Number ],
			default: () => '',
		},
		label: {
			type: String,
			default: () => '',
		},
		placeholder: {
			type: String,
			default: () => '',
		},
		min: {
			type: Number,
			default: () => 0,
		},
		max: {
			type: Number,
			default: () => 10000,
		},
		type: {
			type: String,
			default: () => 'text',
		},
		triggerInputOnBlur: {
			type: Boolean,
			default: () => true,
		},
		hasDynamicData: {
			type: Boolean,
			default: () => false
		},
		hasErrors: {
			type: Boolean,
			default: () => false
		},
		dynamicDataFields: {
			type: Object,
			default: () => {
			}
		},
		extraClasses: {
			type: Array,
			default: () => []
		},
		accept: {
			type: String,
			default: () => ''
		},
		autocomplete: {
			type: String,
			default: () => 'off'
		},
		labelPosition: {
			type: String,
			default: () => 'left'
		},
	},
	computed: {
		moreClasses() {
			const classes = [ ...this.extraClasses, `tap-label-${this.labelPosition}` ];

			if ( this.label.length ) {
				classes.push( 'no-border' );
			}
			if ( ! this.value?.length ) {
				classes.push( 'no-value' );
			}
			if ( this.hasDynamicData ) {
				classes.push( 'with-dynamic' );
			}
			if ( this.hasErrors ) {
				classes.push( 'tap-input-error' );
			}
			return classes;
		}
	},
	methods: {
		handleEnter() {
			this.$refs.inputEl.blur();
		},
		handleInput() {
			this.emitValue();
		},
		handleBlur() {
			if ( this.triggerInputOnBlur ) {
				this.emitValue();
			}
			this.$emit( 'blur', this.$refs.inputEl.value );
		},
		emitValue() {
			this.$emit( 'input', this.$refs.inputEl.value );
		},
		handleShortcode( shortcode ) {
			if ( shortcode ) {
				triggerElementEvent( this.$refs.closeSelect, 'click' );
				this.$refs.inputEl.value += shortcode;
				this.emitValue();
			}
		},
	}
}
</script>


