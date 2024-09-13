<template>
	<div v-if="noOfItems || !app?.has_access" :class="{'tap-locked':!app?.has_access,'no-access':!app?.has_access, [`tap-app-${app.app_id ||app.id}`]:noOfItems}" class="tap-app-card tap-flex--column mb-10">
		<div class="tap-app-logo mt-5">
			<logo :value="app.logo || app.image"/>
		</div>
		<div class="tap-app-name mt-10 mb-5">
			{{ app.name }}
		</div>
		<div v-if="!app?.has_access" class="tap-get-access ml-0" @click.stop.prevent="openUrl">
			Get Access
		</div>
		<div v-if="!app?.has_access" class="tap-app-lock tap-flex">
			<icon icon-name="tap-lock"/>
			<span class="tap-app-lock-text">Not activated</span>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import Logo from "@/components/general/Logo";

export default {
	name: "AppCard",
	components: {
		Icon,
		Logo
	},
	props: {
		app: {
			type: Object,
			default: () => {
			}
		},
		noOfItems: {
			type: Number,
			default: 0
		}
	},
	methods: {
		openUrl() {
			window.open( this.app.access_url, '_blank' );
		}
	}
}
</script>

