<template>
	<div :class="wrapperClasses" class="tap-dashboard">
		<sidebar-menu :show-info="Array.isArray(getAutomations)"/>
		<automation-sidebar v-if="Array.isArray(getAutomations)"/>
		<div :class="{'bottom-space': $route.path.includes('saved'), 'tap-overflow-auto': !isError}"
			 class="tap-main-container tap-scrollbar">
			<router-view v-if="Array.isArray(getAutomations)"/>
		</div>
		<teleport to="body">
			<div id="tap-toast-container"/>
			<div class="tap-app-loader"/>
			<notice/>
		</teleport>
	</div>
</template>
<script>
import SidebarMenu from "@/components/general/SidebarMenu";
import { mapActions, mapGetters } from "vuex";
import AutomationSidebar from "@/components/automation/AutomationSidebar";
import { foldWpMenu } from "@/utils/ui-fn";
import Notice from "@/components/general/modals/Notice";

export default {
	name: "AdminApp",
	components: {
		Notice,
		AutomationSidebar,
		SidebarMenu,
	},
	computed: {
		...mapGetters( 'automations', [ 'getAutomations' ] ),
		...mapGetters( 'errors', [ 'getErrors' ] ),
		wrapperClasses() {
			const classes = [ `tap-view-${this.currentPath}` ];
			if ( ! Array.isArray( this.getAutomations ) || ( this.isError && ! this.hasErrors ) ) {
				classes.push( 'tap-loader' );
			}
			if ( this.noSidebar ) {
				classes.push( 'tap-no-sidebar' );
			}

			return classes;
		},
		currentPath() {
			const path = this.$route.path.split( '/' ).filter( part => part );
			return path[ 0 ];
		},
		isError() {
			return this.currentPath === 'error_log';
		},
		noSidebar() {
			return this.currentPath !== 'saved';
		},
		hasErrors() {
			return Array.isArray( this.getErrors );
		},
		ttw() {
			return {
				url: TAPAdmin.ttw.url,
				token: TAPAdmin.ttw.tpm_token,
			};
		}
	},
	created() {
		foldWpMenu();
		this.fetchApps();
		this.fetchAutomations();

		window.onbeforeunload = () => {
			if ( ! TAPAdmin.debug_mode && TAPAdmin.preventLeave ) {
				return 'You have unsaved changes. Are you sure you want to leave?';
			}
		};
	},
	methods: {
		...mapActions( 'automations', [ 'fetchAutomations' ] ),
		...mapActions( 'generic', [ 'fetchApps' ] ),

	},
}
</script>
