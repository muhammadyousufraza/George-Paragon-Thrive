<template>
	<label class="tap-radio-wrapper tap-flex">
		<input :checked="value" :name="id" type="radio" @input.prevent.stop="$emit('input',id,!value)">
		<span class="tap-checkmark"/>
		<span class="tap-checkbox-label">{{ text }}</span>
	</label>
</template>

<script>
export default {
	name: "Radio",
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
	}
}
</script>


