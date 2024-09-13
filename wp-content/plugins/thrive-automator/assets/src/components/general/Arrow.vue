<template>
	<div class="tap-arrow-wrapper tap-flex--start p-10 pl-35">
		<icon v-if="iconName" :icon-name="iconName"/>
		<div class="tap-arrow-content ml-15">
			<p v-if="step" class="m-0 mb-5">
				Step {{ step }} <span v-if="completed"> - Completed</span>
			</p>
			<h4 class="m-0">
				{{ stepTitle }}
			</h4>
			<h4 v-if="stepSubTitle" class="m-0">
				{{ stepSubTitle }}
			</h4>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";

export default {
	name: "Arrow",
	components: {
		Icon
	},
	props: {
		iconName: {
			type: String,
			default: ''
		},
		completed: {
			type: Boolean,
			default: false
		},
		step: {
			type: Number,
			default: 0
		},
		stepTitle: {
			type: String,
			default: ''
		},
		stepSubTitle: {
			type: String,
			default: ''
		}
	}
}
</script>
