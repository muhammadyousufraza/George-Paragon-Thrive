<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid}" class="tap-pair-field tap-action-field p-5 pt-10 mt-10">
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
				<div v-if="!collapsed" class="tap-toggle-content tap-flex--column tap-scrollbar pt-10">
					<div v-for="(field, index) in fieldValue" :key="index" class="tap-flex--column">
						<key-value-pair :has-errors="hasErrors && !fieldValidation?.isValid" :pair-value="field" :step-index="stepIndex" :validation-regex="regex" @delete="deleteField(index)" @input="handleInput($event,index)"/>
						<div class="tap-separator"/>
					</div>
					<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Add new'" :icon-name="'tap-plus'" @click="addData()"/>
				</div>
			</transition>
		</div>
	</div>
</template>

<script>
import GenericField from '@/components/fields/GenericField';
import IconButton from '@/components/general/IconButton';
import KeyValuePair from '@/components/general/KeyValuePair';
import { validationRegex } from "@/utils/constants";

export default {
	name: "KeyValuePairField",
	components: {
		IconButton,
		KeyValuePair
	},
	mixins: [ GenericField ],
	props: {
		filterData: {
			type: Object,
			default: () => {
			}
		},
		filterIndex: {
			type: Number,
			default: () => 0,
		},
		savedData: {
			type: Object,
			default: () => {
			}
		},
		fieldData: {
			type: Object,
			default: () => {
			}
		},
	},
	emits: [ 'propertyChange' ],
	data() {
		return {
			shouldClearEmptyFields: true,
			collapsed: true,
		}
	},
	computed: {
		regex() {
			return this.fieldValidators.includes( 'http_headers' ) ? validationRegex.httpHeaders : validationRegex.fieldKey;
		}
	},
	mounted() {
		this.ensureFields();
	},
	updated() {
		this.ensureFields();
	},
	methods: {
		clearEmptyFields() {
			if ( this.fieldValue?.length > 1 ) {
				const ids = [];
				let i = 0;
				while ( i < this.fieldValue.length ) {
					if ( ! this.fieldValue[ i ].key && ! this.fieldValue[ i ].value ) {
						ids.push( i );
					}
					i ++;
				}
				ids.reverse().forEach( id => this.deleteField( id ) );
				this.ensureFields();
			}
		},
		ensureFields() {
			if ( ! this.fieldValue?.length ) {
				this.addData();
			}
		},
		handleInput( value = {key: '', value: ''}, index = 0 ) {
			const data = this.fieldValue ? [ ...this.fieldValue ] : [];
			data[ index ] = value;
			this.changeProp( data );
		},
		deleteField( index ) {
			const data = this.fieldValue ? [ ...this.fieldValue ] : [];
			data.splice( index, 1 );
			this.changeProp( data );
			if ( ! data.length ) {
				this.addData();
			}
		},
		addData() {
			const data = this.fieldValue ? [ ...this.fieldValue ] : [];
			data.push( {key: '', value: ''} );
			this.changeProp( data );
		}
	}
}
</script>
