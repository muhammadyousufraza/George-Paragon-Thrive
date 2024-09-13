<template>
	<teleport to="body">
		<div :class="extraClass" :style="extraStyle" class="tap-modal tap-modal-md p-0">
			<icon v-if="allowClose" icon-name="tap-cross" wrapper-classes="tap-modal-close" @click="$emit('cancel')"/>
			<div class="tap-modal-header">
				<div class="tap-modal-title">
					<slot name="title"/>
				</div>
			</div>
			<div class="tap-modal-content">
				<slot name="content"/>
			</div>
			<div v-if="hasFooter" class="tap-modal-footer">
				<slot name="footer"/>
			</div>
		</div>
		<div class="tap-overlay" v-on="allowClose ? {click: ()=>$emit('cancel')} :{}"/>
	</teleport>
</template>

<script>
import Icon from "@/components/general/Icon";

export default {
	name: "GenericModal",
	components: {
		Icon
	},
	props: {
		extraClass: {
			type: String,
			default: ''
		},
		hasFooter: {
			type: Boolean,
			default: false
		},
		extraStyle: {
			type: Object,
			default: () => {
			}
		},
		allowClose: {
			type: Boolean,
			default: true
		}
	},
	emits: [ 'cancel' ]
}
</script>

<style lang="scss" scoped>
.tap-modal-header {
	display: flex;
	justify-content: space-between;
	color: #575b65;
}

.tap-modal {
	background-color: #f1f1f1;

	.tap-cancel {
		position: absolute;
		top: 10px;
		right: 10px;
		cursor: pointer;
		z-index: 9;
	}

	&.tap-modal-sm {
		--modal-height: 240px;
		--modal-width: 420px;
		max-width: var(--modal-width);
		max-height: var(--modal-height);
	}

	&.tap-modal-md {
		--modal-width: 640px;
		max-width: var(--modal-width);
	}

	&.tap-modal-lg {
		--modal-width: 900px;
		max-width: var(--modal-width);
	}

	&.auto-columns {
		display: flex;
		flex-direction: column;
	}

	.tap-modal-content {
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
		padding: 20px 50px;
		height: calc(100% - 130px);
		position: absolute;
		box-sizing: border-box;
	}

	.tap-modal-footer {
		height: 130px;
		display: flex;
	}
}
</style>
