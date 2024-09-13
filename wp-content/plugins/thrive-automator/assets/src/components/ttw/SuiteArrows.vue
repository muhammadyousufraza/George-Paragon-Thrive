<template>
	<div class="tap-suite-arrows tap-fw">
		<div class="tap-suite-arrow" :class="{'active': currentStep===1, 'completed': currentStep>1, 'all-completed': currentStep===2}" data-type="download" @click="$emit('goToStep',1)">
			<arrow :icon-name="getIcon(1)" :step="1" :completed="currentStep>1" step-title="Download & Install" step-sub-title="Thrive Product Manager"/>
		</div>
		<div class="tap-suite-arrow" :class="{'active': currentStep===2, 'all-completed': currentStep>2}" data-type="activate" @click="$emit('goToStep',2)">
			<arrow :icon-name="getIcon(2)" :step="2" :completed="currentStep>2" step-title="Activate" step-sub-title="Thrive Product Manager"/>
		</div>
		<div class="tap-suite-arrow" :class="{'active': currentStep===3}" data-type="connect" @click="$emit('goToStep',3)">
			<arrow :icon-name="getIcon(3)" :step="3" step-title="Connect / Create" step-sub-title="Your Thrive account"/>
		</div>
	</div>
</template>

<script>
import Arrow from "@/components/general/Arrow";

export default {
	name: "SuiteArrows",
	components: {
		Arrow,
	},
	props: {
		currentStep: {
			type: Number,
			default: 1
		}
	},
	emits: [ 'goToStep' ],
	methods: {
		getIcon( step ) {
			let icon = 'tap-oval';

			if ( step === this.currentStep ) {
				icon = 'tap-oval-filled'
			} else if ( step < this.currentStep ) {
				icon = 'tap-filled-check'
			}

			return icon;
		}
	},
}
</script>
