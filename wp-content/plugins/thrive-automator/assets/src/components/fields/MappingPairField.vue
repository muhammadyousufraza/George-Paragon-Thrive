<template>
	<div class="tap-mapping-pair-field tap-action-field p-5 pt-10 mt-10 mb-10 tap-fw-no-paddings">
		<div class="tap-toggle-item tap-fw">
			<div :class="{'is-active':!collapsed, 'pb-10': !collapsed}" class="tap-toggle-heading tap-flex--between mb-5 p-5" @click="collapsed=!collapsed">
				<div class="tap-toggle-title tap-flex--start">
					{{ fieldData.name }}
					<div v-if="fieldData.description" class="tap-field-description">
						<icon :tooltip="fieldData.description" icon-name="tap-info"/>
					</div>
				</div>
				<div class="tap-toggle-icon">
					<icon icon-name="tap-chevron-down"/>
				</div>
			</div>
			<transition name="tap-toggle">
				<div v-if="!collapsed" class="tap-toggle-content tap-flex--column tap-scrollbar">
					<div v-for="(field, index) in fieldValue" :key="index" class="tap-flex--column tap-mapping-pair-list tap-fw ">
						<mapping-pair :has-errors="hasErrors&&!fieldValidation?.isValid" :mapping-keys="ajaxValues" :pair-value="field" :step-index="stepIndex" @delete="deleteField(index)" @input="handleInput($event,index)"/>
						<div class="tap-separator mt-5 mb-5"/>
					</div>
					<div class="tap-mapping-buttons tap-flex tap-fw">
						<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Add new'" :icon-name="'tap-plus'" @click="addData()"/>
						<div class="tap-vert-separator"/>
						<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Refresh'" :icon-name="'tap-sync'" @click="refreshData"/>
					</div>
				</div>
			</transition>
		</div>
	</div>
</template>

<script>
import KeyValuePairField from "@/components/fields/KeyValuePairField";
import Icon from "@/components/general/Icon";
import IconButton from '@/components/general/IconButton';
import MappingPair from "@/components/general/MappingPair";

export default {
	name: "MappingPairField",
	components: {
		IconButton,
		MappingPair,
		Icon
	},
	mixins: [ KeyValuePairField ],
	emits: [ 'propertyChange' ],
	data() {
		return {
			collapsed: true,
		}
	},
}
</script>
