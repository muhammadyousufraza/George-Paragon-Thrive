<template>
	<div class="tap-step-actions tap-flex">
		<icon v-if="stepIndex-1 >0 && isSaved" :icon-name="'tap-move-up'" :tooltip="'Move up'" @click="moveUp"/>
		<icon v-if="stepIndex+2 <=totalSteps && isSaved" :icon-name="'tap-move-down'" :tooltip="'Move down'" @click="moveDown"/>
		<div v-if="isSaved && (stepIndex-1 >0||stepIndex+2 <=totalSteps)" class="tap-vert-separator"/>
		<icon :tooltip="'Delete'" icon-name="tap-trash" @click="$emit('toggleDelete')"/>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import { mapActions } from "vuex";

export default {
	name: "StepActions",
	components: {
		Icon
	},
	props: {
		totalSteps: {
			type: Number,
			default: () => 0
		},
		isSaved: {
			type: Boolean,
			default: () => false,
		},
		stepIndex: {
			type: Number,
			default: () => 0
		},
	},
	methods: {
		...mapActions( 'steps', [ 'swapSteps', 'syncStepData' ] ),
		// change step position in UI & structure
		moveUp() {
			this.swapSteps( {
				currentIndex: this.stepIndex,
				newIndex: this.stepIndex - 1,
			} )
			this.syncStepData();
		},
		moveDown() {
			this.swapSteps( {
				currentIndex: this.stepIndex,
				newIndex: this.stepIndex + 1,
			} )
			this.syncStepData();
		},
	}
}
</script>


