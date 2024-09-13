<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-select-field tap-action-field">
		<div class="tap-container-heading mb-0 tap-flex--start">
			{{ fieldData.name }}
			<div v-if="parentType==='trigger'" class="tap-field-description">
				<icon :tooltip="fieldData.description" icon-name="tap-info"/>
			</div>
		</div>
		<select-toggle :active-items="activeItems" :current-value="fieldValue" :placeholder="fieldData.placeholder" :select-width="'470px'" :stored-data="storedData" :with-search="true" @select="changeProp" @toggle="toggle"/>
		<div v-if="hasErrors && !fieldValidation?.isValid" class="tap-filter-error">
			{{ fieldValidation?.message }}
		</div>
		<div v-for="(subfield, index) in fieldData?.interface_fields" :key="index" class="tap-action-field-subfield">
			<component
				:is="getFieldComponent(subfield.type)"
				:field-data="subfield"
				:is-saved="isSaved"
				:object-identifiers="[...objectIdentifiers,subfield.id]"
				:parent-data="parentData"
				:parent-has-errors="parentHasErrors"
				:parent-show-errors="parentShowErrors"
				:parent-type="parentType"
				:container-element="containerElement"
				:step-index="stepIndex"/>
		</div>
	</div>
</template>
<script>
import AutocompleteToggleField from "@/components/fields/AutocompleteToggleField";
import Icon from "@/components/general/Icon";
import SelectToggle from "@/components/general/SelectToggle";

export default {
	name: 'SelectToggleField',
	components: {
		SelectToggle,
		Icon
	},
	mixins: [ AutocompleteToggleField ],
	data() {
		return {
			activeItems: [],
		}
	},
	methods: {
		toggle( id ) {
			this.activeItems = TAPAdmin._.xor( this.activeItems, [ id ] );
		},
	}
}
</script>
