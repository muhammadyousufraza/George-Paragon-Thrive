<template>
	<div :class="[styleClass,{'icon-right':iconPosition==='right'}]" class="tap-button">
		<icon v-if="iconName" :icon-name="iconName"/>
		<span class="tap-button-text">{{ buttonText }}</span>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";

export default {
	name: 'IconButton',
	components: {Icon},
	props: {
		iconName: {
			type: String,
			default: () => '',
		},
		iconPosition: {
			type: String,
			default: () => 'left',
		},
		buttonText: {
			type: String,
			default: () => 'Text',
		},
		buttonStyles: {
			type: Array,
			default: () => [],
		}
	},
	computed: {
		styleClass() {
			return this.buttonStyles.map( style => style ? `tap-button--${style}` : '' ).join( ' ' ).trim();
		}
	}
}
</script>


