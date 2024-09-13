<template>
	<generic-modal :allow-close="false" :has-footer="true" extra-class="tap-no-consent-modal">
		<template #content>
			<img alt="Logo" class="tap-tracking-icon" src="@/../images/tracking-logo.webp" width="210">
			<p class="tap-content-title">
				Collect product data consent
			</p>

			<div class="tap-info blue mb-20">
				<p class="m-0">
					Remember that sharing this data will simply help us understand how you are using Thrive Themes products.
					The information we gather will serve the purpose of helping us improve our work and prioritize the next development steps.
				</p>
				<icon icon-name="tap-caret-down"/>
				<p class="m-0 tap-bold">
					It’s important to know that we won’t collect any personal information of yours (name, email etc.)
				</p>
			</div>
			<div class="tap-modal-consent-info">
				<div class="tap-icon-holder lock"/>
				<p class="td-bold green">
					The data will not expose your website visitors or users in any way.
				</p>
			</div>
			<div class="tap-modal-consent-info">
				<div class="tap-icon-holder incognito"/>
				<p class="m-0">
					This means that, if you have a student that accesses an online course, we will never collect any identifiable information about who those students are.
				</p>
			</div>
			<div class="tap-modal-consent-info">
				<div class="tap-icon-holder switch"/>
				<p class="m-0">
					More than that, if at any point you want to go back and change this, you can always access your Settings and disable this consent checkbox.
				</p>
			</div>
		</template>
		<template #footer>
			<div class="tap-modal-actions">
				<icon-button :button-styles="['save']" button-text="Ok, I will share product data with Thrive Themes team right now" @click="applyConsent(true)"/>
				<icon-button :button-styles="['ghost', 'no-border', 'clean']" button-text="No, I won`t share product data with the Thrive Themes team" @click="applyConsent(false)"/>
			</div>
		</template>
	</generic-modal>
</template>

<script>
import { mapActions } from "vuex";
import GenericModal from "@/components/general/modals/GenericModal";
import IconButton from "@/components/general/IconButton";
import { toggleAppLoader } from "@/utils/ui-fn";
import Icon from "@/components/general/Icon";

export default {
	name: "NoConsentModal",
	components: {
		GenericModal,
		IconButton,
		Icon
	},
	props: {
		noticeElem: {
			type: Object,
			required: true,
		}
	},
	methods: {
		...mapActions( 'settings', [ 'toggleNoConsentModal', 'changeTrackingConsent' ] ),
		applyConsent( consent ) {
			toggleAppLoader()
			this.changeTrackingConsent( consent ).then( () => {
				this.toggleNoConsentModal( false );
				this.noticeElem.remove();
				toggleAppLoader( false );
			} );
		}
	}
}
</script>
