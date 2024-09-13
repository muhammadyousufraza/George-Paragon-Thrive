<template>
	<div class="tap-field-generator tap-flex--column mt-10 tap-fw">
		<div class="tap-fg-inputs tap-fw tap-flex mt-15">
			<input-field :has-errors="hasErrors && hasKeyError && hasData" :placeholder="keyPlaceholder" :value="pairValue.key" @input="handleKeyInput"/>
			<select-toggle v-if="withToggleSelect" :active-items="activeItems" :current-value="pairValue[typeKey]" :stored-data="toggleOptions" :with-search="true" :wrapper-classes="hasData&&hasErrors && !pairValue[typeKey] ? ['tap-error'] : []" select-width="230px" @select="typeSelected" @toggle="toggle"/>
			<select2 v-if="!withToggleSelect" :options="fieldOptions" :value="pairValue[typeKey]" :wrapper-classes="hasData&&hasErrors && !pairValue[typeKey] ? 'tap-error' : ''" @input="typeSelected"/>
			<div class="tap-pair-delete">
				<icon icon-name="tap-cross" @click="$emit('delete', pairValue.uuid)"/>
			</div>
		</div>
		<div v-if="hasData &&hasErrors && (hasKeyError||hasTypeError)" :class="{'tap-flex--end': hasTypeError && !hasKeyError}" class="tap-fg-errors pt-5 tap-fw tap-flex--between">
			<div v-if="hasKeyError">
				{{ keyErrorMessage }}
			</div>
			<div v-if="hasTypeError">
				No type
			</div>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import InputField from "@/components/general/InputField";
import Select2 from "@/components/general/Select2";
import SelectToggle from "@/components/general/SelectToggle";
import { validateDataKey } from "@/utils/data-fn";
import { select2Option } from "@/utils/ui-fn";

export default {
	name: "FieldGenerator",
	components: {
		InputField,
		Icon,
		Select2,
		SelectToggle
	},
	props: {
		pairValue: {
			type: Object,
			default: () => {
				return {
					key: '',
					[ this.typeKey ]: '',
					uuid: ''
				}
			},
		},
		availableFields: {
			type: Object,
			default: () => {
			},
		},
		keyPlaceholder: {
			type: String,
			default: () => 'Key',
		},
		valuePlaceholder: {
			type: String,
			default: () => 'Value',
		},
		hasErrors: {
			type: Boolean,
			default: () => false,
		},
		withToggleSelect: {
			type: Boolean,
			default: () => false,
		},
		typeKey: {
			type: String,
			default: () => 'type',
		},
		forbiddenTypes: {
			type: Array,
			default: () => [],
		},
		selectWidth: {
			type: String,
			default: () => '100%'
		},
		preventValidateKey: {
			type: Boolean,
			default: () => false,
		},
		forceShowEmptyErrors: {
			type: Boolean,
			default: () => false,
		},
	},
	emits: [ 'input', 'delete' ],
	data() {
		return {
			activeItems: [],
		}
	},
	computed: {
		hasData() {
			return this.forceShowEmptyErrors || this.pairValue.key?.length > 0 || this.pairValue[ this.typeKey ]?.length > 0;
		},
		hasKeyError() {
			return this.preventValidateKey ? ! this.pairValue.key?.length : ! validateDataKey( this.pairValue.key ).isValid;
		},
		hasTypeError() {
			return ! this.pairValue[ this.typeKey ];
		},
		keyErrorMessage() {
			return this.preventValidateKey && ! this.pairValue.key?.length ? 'Data is required' : validateDataKey( this.pairValue.key ).message;
		},
		fieldOptions() {
			const options = [ {id: '', text: ''} ];
			Object.keys( this.availableFields ).forEach( field => {
				if ( ! this.forbiddenTypes.includes( this.availableFields[ field ].id ) ) {
					options.push( {
						id: this.availableFields[ field ].id,
						text: this.availableFields[ field ].label || this.availableFields[ field ].name
					} )
				}
			} );

			return {
				data: options,
				placeholder: 'Select a type',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option
			};
		},
		toggleOptions() {
			const fields = TAPAdmin._.cloneDeep( this.availableFields ),
				/**
				 * Remove used fields from available fields
				 *
				 * @param data
				 */
				checkingItems = data => {
					Object.keys( data ).forEach( key => {
						if ( data[ key ].value ) {

							if ( this.forbiddenTypes.includes( data[ key ].value ) ) {
								delete data[ key ];
							}
						} else if ( Object.keys( data[ key ]?.items || {} )?.length ) {
							checkingItems( data[ key ].items );
						}
					} )
				};
			checkingItems( fields );

			return fields
		}
	},
	methods: {
		toggle( id ) {
			this.activeItems = TAPAdmin._.xor( this.activeItems, [ id ] );
		},
		handleKeyInput( input ) {
			this.$emit( 'input', {
				...this.pairValue,
				...{
					key: input.trim()
				}
			} );
		},
		typeSelected( value ) {
			if ( this.pairValue[ this.typeKey ] !== value ) {
				this.$emit( 'input', {
					...this.pairValue,
					...{
						[ this.typeKey ]: value
					}
				} );
			}
		}
	}
}
</script>
