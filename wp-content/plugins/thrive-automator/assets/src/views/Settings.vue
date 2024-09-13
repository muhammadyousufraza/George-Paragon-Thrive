<template>
	<div class="tap-settings-wrapper tap-fw p-25">
		<div class="tap-screen-header mb-20">
			<span>Settings</span>
		</div>
		<div class="tap-settings-item tap-flex--start p-20">
			<checkbox id="tracking-consent" :value="trackingConsent" text="Help us improve Thrive Themes products by sharing anonymized data with us." @input="consentChange"/>
			<p class="tap-settings-message">
				<a :href="consentLearnMore" target="_blank">Learn more</a>
			</p>
			<disable-consent-modal v-if="showDisabledConsentModal"/>
		</div>
	</div>
</template>

<script>
import Checkbox from "@/components/general/Checkbox";
import { mapGetters, mapActions } from "vuex";
import DisableConsentModal from "@/components/general/modals/DisableConsentModal";
import { toggleAppLoader } from "@/utils/ui-fn";

export default {
	name: "Settings",
	components: {
		DisableConsentModal,
		Checkbox,
	},
	computed: {
		...mapGetters( 'settings', [ 'trackingConsent', 'showDisabledConsentModal' ] ),
		consentLearnMore() {
			return TAPAdmin.urls.consent;
		}
	},
	mounted() {
		if ( TAPAdmin.ttw.connected ) {
			this.$router.push( {path: '/saved'} );
		}
	},
	methods: {
		...mapActions( 'settings', [ 'changeTrackingConsent', 'toggleConsentModal' ] ),
		consentChange( id, value, event ) {
			event.target.checked = true;
			if ( value ) {
				toggleAppLoader();
				this.changeTrackingConsent( true ).then( () => {
					toggleAppLoader( false );
				} );
			} else {
				this.toggleConsentModal( true );
			}
		},
	}
}
</script>
