<template>
	<div class="tap-tracking-ribbon">
		<img alt="Logo" class="tap-tracking-icon" src="@/../images/tracking-logo.webp">
		<div class="tap-tracking-message">
			<p>
				In order to improve our products and help you get the best out of them, we would like to have your permission to gather some anonymous product data. Accepting to this means that you agree to share product usage data with us.
			</p>
			<p class="tap-bold">
				It’s important to know that we won’t collect any personal information of yours (name, email etc.) and that your data is anonymized.
			</p>
			<div class="tap-tracking-buttons">
				<div class="tap-tracking-button" @click="toggleNoConsentModal">
					No, I don’t agree to share any product data with the Thrive Themes team
				</div>
				<div class="tap-tracking-button tap-accept" @click="accept">
					Yes, I agree to share product data with the Thrive Themes team
				</div>
			</div>
		</div>
		<no-consent-modal v-if="showNoConsentModal" :notice-elem="$notice"/>
		<teleport to="body">
			<div class="tap-app-loader"/>
		</teleport>
	</div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import { toggleAppLoader } from "@/utils/ui-fn";
import NoConsentModal from "@/components/general/modals/NoConsentModal";

export default {
	name: "TrackingApp",
	components: {NoConsentModal},
	computed: {
		...mapGetters( 'settings', [ 'showNoConsentModal' ] ),
		$notice() {
			return TAPAdmin.$( `#${TAPAdmin.tracking_ribbon_id}` );
		}
	},
	mounted() {
		this.$notice.removeClass( 'tap-notice-hidden' );
	},
	methods: {
		...mapActions( 'settings', [ 'changeTrackingConsent', 'toggleNoConsentModal' ] ),
		accept() {
			toggleAppLoader()
			this.changeTrackingConsent( true ).then( () => {
				this.$notice.addClass( 'tap-notice-hidden' );
				toggleAppLoader( false )
			} );
		},
	}
}
</script>
