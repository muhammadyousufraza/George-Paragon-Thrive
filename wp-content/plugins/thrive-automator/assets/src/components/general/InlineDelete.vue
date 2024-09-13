<template>
	<div :class="classes" class="tap-inline-delete-confirmation">
		<div class="tap-delete-message">
			{{ message }}
		</div>
		<div class="tap-delete-buttons">
			<icon-button :button-styles="['ghost', 'clean', 'no-border', 'white']" :button-text="'No, cancel'" @click="$emit('cancel')"/>
			<span>|</span>
			<icon-button :button-styles="['ghost', 'clean', 'no-border', 'white']" :button-text="'Yes, delete'" @click="$emit('confirm')"/>
		</div>
	</div>
</template>

<script>
import IconButton from "@/components/general/IconButton";

export default {
	name: "InlineDelete",
	components: {
		IconButton
	},
	props: {
		message: {
			type: String,
			default: () => ''
		},
		classes: {
			type: String,
			default: () => ''
		}
	}
}
</script>


