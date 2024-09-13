<template>
	<div class="tap-read-only-container tap-flex--column--start mb-5">
		<div v-if="label" class="tap-label mb-5">
			{{ label }}
		</div>
		<div class="tap-read-only tap-fw tap-flex--between">
			<div class="tap-ro-input tap-fw p-10">
				<input :value="value" class="tap-fw m-0 p-0" readonly>
			</div>
			<div v-if="withCopy" :class="{'is-success':isSuccess}" class="tap-ro-icon p-10" @click="copy">
				<icon icon-name="tap-clipboard" tooltip="Copy"/>
				<icon icon-name="tap-check"/>
			</div>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";

export default {
	name: "ReadOnly",
	components: {
		Icon
	},
	props: {
		value: {
			type: String,
			default: ''
		},
		label: {
			type: String,
			default: ''
		},
		withCopy: {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			isSuccess: false,
		}
	},
	methods: {
		copy() {
			navigator.clipboard.writeText( this.value ).then( () => {
				this.isSuccess = true;
				setTimeout( () => {
					this.isSuccess = false;
				}, 2000 );
			}, err => {
				console.error( 'Async: Could not copy text: ', err );
			} )
		}
	}
}
</script>
