<template>
	<teleport to="body">
		<div class="tap-suite-confirm p-25 pl-50 pr-50">
			<h3>Are you sure you don’t want to enable your integrations for free?</h3>
			<div class="tap-flex">
				<img class="tap-suite-integration-examples mr-20" src="@/../images/confirmation-integrations.webp" alt="Integrations">
				<div class="tap-suite-confirm-message">
					<p>
						Thrive Automator can connect multiple plugins, tools and services in powerful automations.
					</p>
					<p>
						However, it requires those integrations to be installed and enabled. We’ve provided a library of 20+ popular integrations entirely for free.
					</p>
					<p>
						Creating your account will download those free integrations into your account.
					</p>
				</div>
			</div>
			<div class="tap-suite-confirm-actions tap-flex--between mt-10">
				<icon-button :button-styles="['ghost']" button-text="No, I want more integrations!" @click="$emit('cancel')"/>
				<icon-button :button-styles="['ghost']" button-text="Yes, skip this step" @click="$emit('confirm')"/>
			</div>
		</div>
		<div class="tap-overlay" @click="$emit('cancel')"/>
	</teleport>
</template>

<script>
import IconButton from "@/components/general/IconButton";

export default {
	name: 'SuiteConfirm',
	components: {
		IconButton
	},
	emits: [ 'cancel', 'confirm' ],
}
</script>
