<template>
	<div class="tap-suite-header tap-flex p-25">
		<div v-if="isSplash" class="tap-suite-header-title">
			<h3 class="m-0">
				<span>Thrive Automator is 100% free for life...</span>
			</h3>
			<h3 class="m-0">
				But you can add 20+ integrations by connecting a
			</h3>
			<h3 class="m-0">
				<span>free Thrive Themes account</span>
			</h3>
			<p class="mt-15">
				Get more out of Thrive Automator by adding 20+ Email integrations, WooCommerce events, and more triggers and actions.
			</p>
			<icon-button v-if="showConnectButton" :class="['tap-suite-connect', 'mt-25']" button-text="Connect now for FREE" @click="$emit('buttonClick')"/>
			<icon v-if="showConnectButton" icon-name="tap-3-steps"/>
		</div>
		<div v-else class="tap-suite-header-title">
			<h3>Add 20+ integrations by connecting a <span>free Thrive Themes account!</span></h3>
			<p class="mt-15">
				Install and activate the Thrive Product Manager plugin to enable your new integrations.
			</p>
		</div>
		<img class="tap-suite-integration-examples" src="@/../images/integrations.webp" alt="Integrations">
	</div>
</template>

<script>
import IconButton from "@/components/general/IconButton";
import Icon from "@/components/general/Icon";

export default {
	name: "SuiteHeader",
	components: {
		Icon,
		IconButton
	},
	props: {
		isSplash: {
			type: Boolean,
			default: false
		},
		showConnectButton: {
			type: Boolean,
			default: true
		}
	},
	emits: [ 'buttonClick' ]
}
</script>
