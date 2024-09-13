<template>
	<div class="tap-no-automations-wrapper tap-flex">
		<div class="tap-no-automations-content tap-flex--column">
			<icon :icon-name="'tap-cogs'"/>
			<h1>Thrive Automator</h1>
			<span>Build automations that pass data between a growing list of apps, plugins and services.</span>
			<a href="https://help.thrivethemes.com/en/articles/5431972-create-your-first-automation-with-thrive-automator" rel="noopener" target="_blank">See how automations work</a>
			<icon-button :button-text="'Create your first automation'" @click="createAutomation"/>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";

export default {
	name: "NoAutomation",
	components: {
		Icon,
		IconButton
	},
	methods: {
		createAutomation() {
			this.$router.push( {path: 'automation'} )
		}
	}
}
</script>
