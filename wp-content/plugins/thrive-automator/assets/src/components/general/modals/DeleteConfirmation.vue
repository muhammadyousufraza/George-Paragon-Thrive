<template>
	<teleport to="body">
		<div
			v-if="shouldShow"
			:style="modalSizes"
			class="tap-modal tap-delete-confirmation-modal">
			<div
				class="tap-modal-container">
				<div class="tap-modal-content">
					<div class="tap-modal-header mb-20">
						{{ modalHeader }}
					</div>
					<div class="tap-modal-description">
						{{ modalDescription }}
					</div>
				</div>
				<div class="tap-modal-footer">
					<icon-button
						:button-styles="['ghost', 'grey']"
						:button-text="'No, cancel'"
						@click="$emit('cancel')"/>
					<icon-button
						:button-styles="['ghost','delete']"
						:button-text="'Yes, delete'"
						@click="$emit('confirm')"/>
				</div>
			</div>
			<icon
				:class="'tap-modal-close'"
				icon-name="tap-cross"
				@click="$emit('cancel')"/>
		</div>
		<div
			v-if="shouldShow"
			class="tap-overlay"
			@click="$emit('cancel')"/>
	</teleport>
</template>

<script>
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import { toggleBodyOverflow } from "@/utils/ui-fn";

export default {
	name: "DeleteConfirmation",
	components: {
		IconButton,
		Icon
	},
	props: {
		shouldShow: {
			type: Boolean,
			default: () => false,
		},
		modalHeader: {
			type: String,
			default: () => ''
		},
		modalDescription: {
			type: String,
			default: () => ''
		},
		modalSizes: {
			type: Object,
			default: () => {
			}
		}
	},
	emits: [ 'cancel', 'confirm' ],
	watch: {
		//make sure that page isn't scrollable
		shouldShow() {
			toggleBodyOverflow( this.shouldShow );
		}
	},
}
</script>


