<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-double-dropdown-field tap-action-field">
		<double-dropdown :dropdown-data="ajaxValues" :main-value="fieldWrapperValue" :second-value="fieldValue" @changeMain="changeCustomProp('wrapper_value',$event)" @changeSec="changeProp"/>
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
import DoubleDropdown from "@/components/general/DoubleDropdown";

export default {
	name: "DoubleDropdownField",
	components: {
		DoubleDropdown
	},
	mixins: [ GenericField ],
	data() {
		return {
			syncOnChange: true,
		}
	},
	computed: {
		fieldWrapperValue() {
			return TAPAdmin._.get( this.action?.extra_data, [ ...this.extraDataKey, 'wrapper_value' ] );
		},
	},
}
</script>
