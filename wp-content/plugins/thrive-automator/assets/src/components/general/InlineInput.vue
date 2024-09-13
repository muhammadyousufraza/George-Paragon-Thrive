<template>
	<div
		:class="{'hover-icon': hoverIcon}"
		class="tap-inline-input-container">
		<input
			v-if="editing"
			ref="inputEl"
			:placeholder="placeholder"
			:value="value"
			type="text"
			@keyup.enter="handleEnter"
			@input.stop.prevent="handleInput"
			@blur.stop.prevent="handleBlur">
		<span
			v-else
			@click="toggle">
      {{ label }}
    </span>
		<icon
			v-if="!editing"
			icon-name="tap-pen"
			@click="toggle"/>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";

export default {
	name: "InlineInput",
	components: {
		Icon
	},
	props: {
		value: {
			type: String,
			default: () => '',
		},
		placeholder: {
			type: String,
			default: () => '',
		},
		hoverIcon: {
			type: Boolean,
			default: () => false,
		}
	},
	data() {
		return {
			editing: false,
		}
	},
	computed: {
		label() {
			return this.value || this.placeholder;
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
			this.emitValue();
			this.$emit( 'blur', this.$refs.inputEl.value );
			this.toggle();
		},
		toggle() {
			this.editing = ! this.editing;

			if ( this.editing ) {
				this.$nextTick( () => {
					this.$refs.inputEl.focus();
				} );
			}
			this.$emit( 'inputEditing', this.editing );
		},
		emitValue() {
			this.$emit( 'input', this.$refs.inputEl.value );
		},
	}
}
</script>


