<template>
	<a class="tap-ttw-login" href="#" @click.prevent="openPopup">Login here</a>
</template>

<script>
export default {
	name: 'LoginPopup',

	data() {
		return {
			loginUrl: TAPAdmin.ttw.login_url,
			windowRef: null
		};
	},

	mounted() {
		window.addEventListener( 'message', event => {
			if ( event.origin === window.origin && event.source === this.windowRef && event.data?.tpm_success ) {
				this.$emit( 'loggedin' );
				this.windowRef.close();
			}
		} );
	},

	methods: {
		openPopup() {
			const left = ( screen.width / 2 ) - 220,
				top = ( screen.height / 2 ) - 300;
			this.windowRef = window.open( this.loginUrl, 'ttw_login', `width=440,height=600,top=${top},left=${left}` );
		}
	}
}
</script>
