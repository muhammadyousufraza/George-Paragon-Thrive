<template>
	<div class="tap-sidebar-menu">
		<div :class="{active: currentPath === 'saved'}" class="tap-menu-header-item" @click="goToDash">
			<icon :icon-name="'tap-logo'"/>
			<span class="tap-menu-text">Automator</span>
		</div>
		<div class="tap-menu-items tap-flex--column">
			<div :class="{active: currentPath === 'error_log'}" class="tap-menu-item" @click="goToLogs">
				<icon :icon-name="'tap-error-log-active'" class="active-icon"/>
				<icon :icon-name="'tap-error-log-default'" class="default-icon"/>
				<span class="tap-menu-text">Logs</span>
			</div>
			<Dropdown :offset="[-25,15]" :shown="showAppsTooltip" :disabled="!showAppPopup" :placement="'right-start'" :hide-triggers="['click']" :theme="'automator_menu_tooltip'">
				<div :class="{active: $route.path.includes('apps')}" class="tap-menu-item" @click="goToApps()">
					<icon :icon-name="'tap-apps-active'" class="active-icon"/>
					<icon :icon-name="'tap-apps-default'" class="default-icon"/>
					<span class="tap-menu-text">Apps</span>
				</div>
				<template v-if="showAppsTooltip" #popper>
					<teleport to=".tap-dashboard">
						<div class="tap-popup-overlay" @click.prevent="hideAppPopup"/>
					</teleport>
					<div class="tap-apps-popup p-25">
						<p class="tap-new-notification m-0">
							NEW
						</p>
						<h3 class="m-0 mt-20 mb-15">
							Expand Automator's reach
						</h3>
						<p>Quickly enable integrations with Automator for more actions and triggers.</p>
						<div class="tap-apps-popup-actions tap-flex--between">
							<icon-button button-text="Got it" :button-styles="['ghost']" @click="hideAppPopup"/>
							<a :href="appsLearnMore" target="_blank" class="tap-apps-popup-link" @click="hideAppPopup">
								Learn more
							</a>
						</div>
						<div class="tap-apps-popup-image">
							<img class="tap-suite-integration-examples" src="@/../images/integrations.webp" alt="Integrations">
						</div>
					</div>
				</template>
			</Dropdown>
			<div v-if="!hasTTW" :class="{active: currentPath === 'settings'}" class="tap-menu-item tap-menu-item-settings" @click="goToSettings">
				<icon icon-name="tap-cog"/>
				<span class="tap-menu-text">Settings</span>
			</div>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import { Dropdown } from "v-tooltip";
import IconButton from "@/components/general/IconButton";
import ApiRequest from "@/utils/ApiRequest";
import { mapGetters } from "vuex";

export default {
	name: 'SidebarMenu',
	components: {
		Icon,
		Dropdown,
		IconButton
	},
	props: {
		showInfo: {
			type: Boolean,
			default: false
		},

	},
	data() {
		return {
			showAppPopup: ! TAPAdmin.tooltips.apps_tooltip,
		}
	},
	computed: {
		...mapGetters( 'suite', [ 'getConnected' ] ),
		appsLearnMore() {
			return '//help.thrivethemes.com/en/articles/6517989-using-the-apps-section-in-thrive-automator';
		},
		showAppsTooltip() {
			return this.showInfo && this.showAppPopup && this.getConnected
		},
		currentPath() {
			const path = this.$route.path.split( '/' ).filter( part => part );
			return path[ 0 ];
		},
		hasTTW() {
			return TAPAdmin.ttw.connected;
		}
	},
	methods: {
		goToDash() {
			this.$router.push( {path: '/saved'} );
		},
		goToSettings() {
			this.$router.push( {path: '/settings'} );
		},
		goToLogs() {
			this.$router.push( {path: '/error_log'} );
		},
		goToApps() {
			this.$router.push( {path: `/${this.getConnected ? 'apps' : 'suite'}`} );
		},
		hideAppPopup() {
			this.showAppPopup = false;
			ApiRequest.wpFetchRequest( {
				path: `${TAPAdmin.routes}/hide_tooltip`,
				body: {
					tooltip: 'apps_tooltip'
				},
			} )
		}
	}
}
</script>


