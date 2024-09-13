<template>
	<div v-if="getNoticeShow" :class="{'tap-fade-in':fadeIn}" class="tap-dismissible-notice-wrapper tap-flex--column p-30">
		<h1 class="m-0 mb-15">
			{{ getNoticeHeader }}
		</h1>
		<p class="m-0 mb-15">
			{{ getNoticeMessage }}
		</p>
		<icon-button :button-styles="['ghost', 'clean', 'no-border']" button-text="GOT IT" @click="hideNotice"/>
	</div>
</template>

<script>
import IconButton from "@/components/general/IconButton";
import { mapGetters, mapActions } from "vuex";

export default {
	name: "Notice",
	components: {
		IconButton
	},
	data() {
		return {
			fadeIn: false,
			hideTimeout: null,
		}
	},
	computed: {
		...mapGetters( 'generic', [ 'getNoticeHeader', 'getNoticeMessage', 'getNoticeShow' ] )
	},
	watch: {
		getNoticeShow( value ) {
			if ( value ) {
				setTimeout( () => {
					this.fadeIn = true;

					this.hideTimeout = setTimeout( () => {
						this.hideNotice();
					}, 10000 );
				}, 100 );
			} else {
				this.hideNotice();
			}
		}
	},
	methods: {
		...mapActions( 'generic', [ 'setNoticeData' ] ),
		hideNotice() {
			this.fadeIn = false;
			clearTimeout( this.hideTimeout );
			this.setNoticeData( {
				show: false,
				header: '',
				message: ''
			} )
		}
	},
}
</script>
