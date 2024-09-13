<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-tags-field tap-action-field">
		<div class="tap-container-heading mb-0 tap-flex--start">
			{{ fieldData.name }}
			<div v-if="parentType==='trigger'" class="tap-field-description">
				<icon :tooltip="fieldData.description" icon-name="tap-info"/>
			</div>
		</div>
		<tags :field-value="fieldValue||[]" :has-dynamic-data="true" :field-message="fieldData.extra_options?.message" :step-index="stepIndex" @tags-change="changeProp"/>
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
import GenericField from "@/components/fields/GenericField";
import Tags from "@/components/general/Tags";

export default {
	name: "TagsField",
	components: {
		Tags
	},
	mixins: [ GenericField ]
}
</script>


