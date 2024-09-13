<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-switch-field tap-action-field">
		<switch-check :value="fieldValue === 'enable'" :label="fieldData.name" @input="onInput"/>
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
import SwitchCheck from "@/components/general/SwitchCheck";
import GenericField from "@/components/fields/GenericField";

export default {
	name: "SwitchField",
	components: {
		SwitchCheck
	},
	mixins: [ GenericField ],
	methods: {
		onInput( value ) {
			this.changeProp( value ? 'enable' : 'disable' )
		}
	}
}
</script>
