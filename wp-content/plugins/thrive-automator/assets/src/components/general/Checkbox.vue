<template>
	<label class="tap-checkbox-wrapper tap-flex">
		<input :checked="value" :name="id" type="checkbox" @input.prevent.stop="onInput">
		<span class="tap-checkmark"/>
		<span v-if="text" class="tap-checkbox-label">{{ text }}</span>
	</label>
</template>

<script>
export default {
	name: "Checkbox",
	props: {
		text: {
			type: String,
			default: () => ''
		},
		id: {
			type: [ String, Number ],
			default: () => ''
		},
		value: {
			type: Boolean,
			default: () => false,
		}
	},
	methods: {
		onInput( event ) {
			this.$emit( 'input', this.id, ! this.value, event );
		}
	}
}
</script>


