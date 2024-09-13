<template>
	<generic-modal :has-footer="true" :allow-close="false" extra-class="tap-disable-consent-modal" @cancel="toggleConsentModal( false )">
		<template #content>
			<img alt="Logo" class="tap-tracking-icon" src="@/../images/tracking-logo-pink.png" width="210">
			<p class="tap-content-title">
				Are you sure you want to stop sharing product data with the Thrive Themes team?
			</p>
			<p>
				Remember that sharing this data will simply help us understand how you are using Thrive Themes products. The information we gather will serve the purpose of helping us improve our work and prioritize the next development steps.
			</p>
			<div class="tap-info blue mb-20 tap-bold">
				It’s important to know that we won’t collect any personal information of yours (name, email etc.)
				and that your data is anonymized.
			</div>
		</template>
		<template #footer>
			<div class="tap-modal-actions">
				<icon-button :button-styles="['save']" button-text="Ok, I will continue to share product data with Thrive Themes team" @click="toggleConsentModal( false )"/>
				<icon-button :button-styles="['ghost', 'no-border', 'clean']" button-text="No, I don`t want to share product data with Thrive Themes team" @click="disableTracking"/>
			</div>
		</template>
	</generic-modal>
</template>

<script>

import GenericModal from "@/components/general/modals/GenericModal";
import { mapActions } from "vuex";
import IconButton from "@/components/general/IconButton";
import { toggleAppLoader } from "@/utils/ui-fn";

export default {
	name: "DisableConsentModal",
	components: {
		IconButton,
		GenericModal,
	},
	methods: {
		...mapActions( 'settings', [ 'toggleConsentModal', 'changeTrackingConsent' ] ),
		disableTracking() {
			toggleAppLoader();
			this.changeTrackingConsent( false ).then( () => {
				toggleAppLoader( false );
				this.toggleConsentModal( false );
			} );

		}
	}
}
</script>
