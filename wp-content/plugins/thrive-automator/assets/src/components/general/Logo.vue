<template>
	<div :class="wrapperClasses" class="tap-data-logo">
		<div v-if="/(https?:\/\/[^\s]+)/.test(value)" class="tap-logo-img">
			<img :alt="alt" :src="value">
		</div>
		<div v-else-if="value.includes('<svg')" class="tap-logo-svg">
			{{ value }}
		</div>
		<icon v-else :icon-name="value"/>
	</div>
</template>
<script>
import Icon from "@/components/general/Icon";

export default {
	name: "Logo",
	components: {
		Icon
	},
	props: {
		value: {
			type: String,
			default: () => ''
		},
		alt: {
			type: String,
			default: () => ''
		},
		wrapperClasses: {
			type: String,
			default: () => ''
		}
	}
}
</script>


