export default {
	namespaced: true,
	state: {
		connected: TAPAdmin.ttw.connected,
		installed: TAPAdmin.ttw.installed,
		active: TAPAdmin.ttw.active,
	},
	getters: {
		getConnected: state => state.connected,
		getInstalled: state => state.installed,
		getActive: state => state.active,
	},
	actions: {
		setConnected: ( {commit}, payload ) => {
			commit( 'setConnected', payload );
		},
		setInstalled: ( {commit}, payload ) => {
			commit( 'setInstalled', payload );
		},
		setActive: ( {commit}, payload ) => {
			commit( 'setActive', payload );
		}
	},
	mutations: {
		setConnected: ( state, payload ) => state.connected = payload,
		setInstalled: ( state, payload ) => state.installed = payload,
		setActive: ( state, payload ) => state.active = payload,
	}
}
