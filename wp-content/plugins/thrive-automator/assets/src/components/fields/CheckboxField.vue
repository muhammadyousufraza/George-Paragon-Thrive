<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-checkbox-field tap-action-field">
		<div class="tap-container-heading">
			{{ fieldData.name }}
		</div>
		<div class="tap-action-checkboxes tap-flex--start tap-flex--wrap">
			<checkbox v-for="(item,index) in ajaxValues" :id="item.id" :key="index" :text="item.label" :value="fieldValue?.includes(item.id)" @input="onCheck"/>
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
import Checkbox from "@/components/general/Checkbox";
import { prettyPreview } from "@/utils/data-fn";

export default {
	name: "CheckboxField",
	components: {
		Checkbox
	},
	mixins: [ GenericField ],
	data() {
		return {
			syncOnChange: true,
		}
	},
	computed: {
		items() {
			return this.fieldData?.values || this.getActionFields?.[ this.action.id ]?.[ this.fieldData.id ] || {}
		}
	},
	mounted() {
		if ( ! this.fieldValue && this.fieldData?.default_value ) {
			this.onCheck( this.fieldData?.default_value, true )
		}
	},
	methods: {
		getPrettyPreview( value ) {
			let preview = prettyPreview( this.previewValue, value )
			value.forEach( item => preview = preview.replaceAll( item, this.getLabel( item ) ) );
			return preview;
		},
		getLabel( id ) {
			return this.ajaxValues[ id ]?.label
		},
		onCheck( key, checked ) {
			const data = this.fieldValue ? [ ...this.fieldValue ] : [];

			if ( checked ) {
				data.push( key );
			} else {
				const index = data.indexOf( key );
				data.splice( index, 1 );
			}
			this.changeProp( data );
		}
	}
}
</script>
