<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-select-field tap-action-field">
		<div class="tap-container-heading mb-0 tap-flex--start">
			{{ fieldData.name }}
			<div v-if="parentType==='trigger'" class="tap-field-description">
				<icon :tooltip="fieldData.description" icon-name="tap-info"/>
			</div>
		</div>
		<select-toggle :current-value="fieldValue" :placeholder="fieldData.placeholder" :stored-data="fieldOptions" :with-search="true" :wrapper-classes="[`tap-${parentType}-dropdown`, 'tap-field-dropdown']" @select="selectValue" @dropdown-search="onSearch" @dropdown-scroll="loadMore"/>
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
import AutocompleteField from "@/components/fields/AutocompleteField";
import { prettyPreview, ucFirst } from "@/utils/data-fn";
import SelectToggle from "@/components/general/SelectToggle";

export default {
	name: "SelectField",
	components: {
		SelectToggle,
	},
	mixins: [ AutocompleteField ],
	data() {
		return {
			syncOnChange: true,
		}
	},
	computed: {
		fieldOptions() {
			const options = [];

			if ( this.fieldData.allowed_data_set_values.length ) {
				this.fieldData.allowed_data_set_values.forEach( item => {
					if ( this.getStepDataObjects( this.stepIndex ).includes( item ) ) {
						options.push( {
							value: `tap-dynamic-${item}`,
							label: `Dynamic ${this.getDataObjects[ item ].name || ucFirst( item.replace.replaceAll( '_', ' ' ) )}`,
							image: 'tap-database'
						} );
					}
				} );
			}

			Object.keys( this.ajaxValues ).forEach( key => {
				options.push( {
					value: this.ajaxValues[ key ].id,
					label: this.ajaxValues[ key ].label || this.ajaxValues[ key ].name,
				} )
			} );

			return options;
		},
	},
	mounted() {
		if ( ! this.fieldValue && this.fieldData?.default_value ) {
			this.changeProp( this.fieldData?.default_value );
		}
	},
	methods: {
		getPrettyPreview( value ) {
			let options = [ ...this.dynamicOptions ];
			if ( Array.isArray( this.ajaxValues ) ) {
				options = [ ...options, ...this.ajaxValues ];
			} else {
				options = [ ...options, ...Object.values( this.ajaxValues ) ];
			}

			const selectObj = options?.filter( data => data.id == value ),
				preview = prettyPreview( this.previewValue, value );

			return this.previewValue.length && preview ? preview.replaceAll( value, selectObj?.[ 0 ]?.name || selectObj?.[ 0 ]?.label || value ) : '';
		},
		selectValue( value ) {
			if ( this.fieldOptions?.filter( data => data.value == value )?.length && this.fieldValue != value ) {
				this.changeProp( value );
			}
		},
	}
}
</script>


