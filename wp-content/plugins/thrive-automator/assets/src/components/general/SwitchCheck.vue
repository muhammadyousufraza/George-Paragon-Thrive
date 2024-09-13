<template>
	<div class="tap-switch tap-fw">
		<label class="tap-flex--between">
			<span v-if="label" class="tap-switch-label">{{ label }}</span>
			<input :checked="value" class="tap-checkbox" type="checkbox" @input.prevent.stop="$emit('input',!value)">
			<span class="tap-lever"/>
		</label>
	</div>
</template>

<script>
export default {
	name: "SwitchCheck",
	props: {
		value: {
			type: Boolean,
			default: () => false,
		},
		label: {
			type: String,
			default: () => ''
		},
	},
	emits: [ 'input' ]
}
</script>


