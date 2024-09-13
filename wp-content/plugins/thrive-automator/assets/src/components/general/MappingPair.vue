<template>
	<div class="tap-flex--column tap-fw tap-mapping-pair-container">
		<div class="tap-flex--between tap-fw tap-mapping-pair p-5">
			<select2 :options="mappingOptions" :value="pairValue.key" :wrapper-classes="hasErrors && hasKeyError ? 'tap-error' : ''" @input="handleKeyInput"/>
			<input-field :dynamic-data-fields="dynamicDataFields(stepIndex-1)" :has-dynamic-data="hasDynamic" :has-errors="hasErrors&&hasValueError" :placeholder="valuePlaceholder" :value="pairValue.value" @input="handleValueInput"/>
			<div class="tap-pair-delete">
				<icon icon-name="tap-cross" @click="$emit('delete')"/>
			</div>
		</div>
		<div v-if="hasErrors && (hasKeyError||hasValueError)" :class="{'tap-flex--end': hasValueError && !hasKeyError}" class="tap-kp-errors tap-fw tap-flex--between p-5">
			<div v-if="hasKeyError" class="tap-kp-key-error">
				{{ keyErrorMessage }}
			</div>
			<div v-if="hasValueError" class="tap-kp-field-error">
				No value set
			</div>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import InputField from "@/components/general/InputField";
import Select2 from "@/components/general/Select2";
import { select2Matcher, select2Option } from "@/utils/ui-fn";
import { mapGetters } from "vuex";
import { validateDataKey } from "@/utils/data-fn";

export default {
	name: "MappingPair",
	components: {
		Select2,
		Icon,
		InputField
	},
	props: {
		mappingKeys: {
			type: Object,
			default: () => {
			}
		},
		pairValue: {
			type: Object,
			default: () => {
				return {
					'key': '',
					'value': ''
				}
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
		hasDynamic: {
			type: Boolean,
			default: () => true,
		},
		hasErrors: {
			type: Boolean,
			default: () => false,
		},
		stepIndex: {
			type: Number,
			default: () => 0
		},
	},
	computed: {
		...mapGetters( 'steps', [ 'dynamicDataFields' ] ),
		hasKeyError() {
			return ! validateDataKey( this.pairValue.key ).isValid;
		},
		hasValueError() {
			return ! this.pairValue.value;
		},
		keyErrorMessage() {
			return validateDataKey( this.pairValue.key ).message;
		},
		mappingOptions() {
			const options = [ {id: '', text: ''} ];
			Object.keys( this.mappingKeys ).forEach( key => {
				options.push( {
					id: this.mappingKeys[ key ].id,
					text: this.mappingKeys[ key ].label
				} )
			} );
			return {
				data: options,
				placeholder: 'Select a key',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};
		}
	},
	watch: {
		mappingKeys( newValue ) {
			const data = Object.values( newValue );
			if ( this.pairValue?.key && data.length && ! data.some( pair => pair.id == this.pairValue.key ) ) {
				this.handleKeyInput( '', true );
			}
		}
	},
	methods: {
		handleKeyInput( input, forceEmit = false ) {
			if ( input || forceEmit ) {
				this.$emit( 'input', {
					...this.pairValue,
					...{
						key: input
					}
				} );
			}
		},
		handleValueInput( input ) {
			this.$emit( 'input', {
				...this.pairValue,
				...{
					value: input
				}
			} );
		},
	}
}
</script>
