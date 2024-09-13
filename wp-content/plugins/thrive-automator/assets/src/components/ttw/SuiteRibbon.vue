<template>
	<div class="tap-suite-ribbon tap-fw tap-flex">
		<img class="tap-suite-integration-examples" src="@/../images/integrations.webp" alt="Integrations">
		<h3><span>Connect to Thrive Product Manager for FREE</span> and get 20+ Email integrations, WooCommerce triggers, actions and more.</h3>
		<icon-button button-text="Get more integrations" :button-styles="['ghost','rounded']" @click="goToSuite"/>
	</div>
</template>

<script>
import IconButton from "@/components/general/IconButton";

export default {
	name: "SuiteRibbon",
	components: {IconButton},
	methods: {
		goToSuite() {
			this.$router.push( {path: '/suite'} );
		}
	},
}
</script>
