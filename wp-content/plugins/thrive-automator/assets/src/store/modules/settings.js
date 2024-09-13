import ApiRequest from "@/utils/ApiRequest";

const SettingsAPI = new ApiRequest( 'settings' );

export default {
	namespaced: true,
	state: {
		trackingConsent: !! parseInt( TAPAdmin.tracking_enabled ),
		showDisabledConsentModal: false,
		showNoConsentModal: false,
	},
	getters: {
		trackingConsent: state => state.trackingConsent,
		showDisabledConsentModal: state => state.showDisabledConsentModal,
		showNoConsentModal: state => state.showNoConsentModal,
	},
	actions: {
		async changeTrackingConsent( {commit}, consent ) {
			return SettingsAPI.customRequest( 'tracking_consent', {tracking_enabled: consent}, 'POST' ).then( () => {
				commit( 'setTrackingConsent', consent );
			} );
		},

		toggleConsentModal( {commit}, show ) {
			commit( 'setConsentModal', show );
		},

		toggleNoConsentModal( {commit}, show ) {
			commit( 'setNoConsentModal', show );
		}
	},
	mutations: {
		setTrackingConsent( state, value ) {
			state.trackingConsent = value;
		},
		setConsentModal( state, value ) {
			state.showDisabledConsentModal = value;
		},
		setNoConsentModal( state, value ) {
			state.showNoConsentModal = value;
		}
	}
}
