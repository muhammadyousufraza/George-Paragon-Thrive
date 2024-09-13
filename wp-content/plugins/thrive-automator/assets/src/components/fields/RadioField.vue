<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid, 'tap-no-subfields': !Object.keys(fieldData?.interface_fields || {} )?.length }" class="tap-radio-field tap-action-field">
		<div class="tap-container-heading mt-10 mb-0">
			{{ fieldData.name }}
		</div>
		<div class="tap-action-radios tap-flex--start tap-flex--wrap mb-20">
			<radio v-for="(item,index) in ajaxValues" :id="item.id" :key="index" :text="item.label" :value="fieldValue==item.id" @input="onCheck"/>
		</div>
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
import Radio from "@/components/general/Radio";

export default {
	name: "CheckboxField",
	components: {
		Radio,
	},
	mixins: [ GenericField ],
	data() {
		return {
			syncOnChange: true,
		}
	},
	mounted() {
		if ( ! this.fieldValue && this.fieldData?.default_value ) {
			this.onCheck( this.fieldData?.default_value )
		}
	},
	methods: {
		getLabel( id ) {
			return this.ajaxValues[ id ]?.label
		},
		onCheck( key, checked = true ) {
			this.changeProp( checked ? key : '' );
		}
	}
}
</script>


