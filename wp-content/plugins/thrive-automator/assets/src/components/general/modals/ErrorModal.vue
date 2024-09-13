<template>
	<teleport to="body">
		<div
			v-if="showModal"
			class="tap-modal tap-error-modal">
			<div class="tap-modal-container">
				<div class="tap-modal-content">
					<div class="tap-modal-header">
						Error preview
					</div>
					<div class="tap-modal-description">
            <textarea
				:value="modalContent"
				class="tap-error-content tap-scrollbar"
				disabled/>
					</div>
				</div>
			</div>
			<icon
				:class="'tap-modal-close'"
				icon-name="tap-cross"
				@click="$emit('cancel')"/>
		</div>
		<div
			v-if="showModal"
			class="tap-overlay"
			@click="$emit('cancel')"/>
	</teleport>
</template>

<script>
import Icon from "@/components/general/Icon";

export default {
	name: "ErrorModal",
	components: {
		Icon
	},
	props: {
		showModal: {
			type: Boolean,
			default: () => false,
		},
		modalContent: {
			type: String,
			default: () => ''
		}
	},
	emits: [ 'cancel' ],
}
</script>


