<template>
	<div :class="[typeClass, wrapperClasses]" class="tap-select-wrapper">
		<select ref="select" :placeholder="options?.placeholder"/>
		<Icon v-if="!options?.tags" :icon-name="'tap-caret-down'" :wrapper-classes="'tap-select-arrow tap-select-icon'"/>
		<Icon v-else :icon-name="'tap-search'" :wrapper-classes="'tap-select-search tap-select-icon'"/>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import 'select2/dist/css/select2.min.css';

const Select2 = require( 'select2/dist/js/select2.min' );


export default {
	name: 'Select2',
	components: {Icon},
	props: {
		options: {
			type: Object,
			default: () => {
			},
		},
		value: {
			type: String,
			default: () => ''
		},
		defaultValue: {
			type: String,
			default: () => ''
		},
		wrapperClasses: {
			type: String,
			default: () => ''
		},
		debounceTime: {
			type: Number,
			default: () => 100,
		},
	},
	emits: [
		'input',
		'dropdownScroll',
	],
	data() {
		return {
			debounce: false
		}
	},
	computed: {
		typeClass() {
			return this.options?.tags ? 'tap-select-autocomplete' : 'tap-select-default';
		}
	},
	watch: {
		value( newValue, prevValue ) {
			// update value only if it changes
			if ( newValue !== prevValue ) {
				TAPAdmin.$( this.$refs.select )
						.val( newValue )
						.trigger( 'change' );
			}
		},
		options( newOptions, oldOptions ) {
			if ( ! TAPAdmin._.isEqual( newOptions, oldOptions ) ) {
				TAPAdmin.$( this.$refs.select )
						.empty()
						.select2( newOptions )
						.val( this.value || this.defaultValue )
						.trigger( 'change' );


			}
		},
	},
	mounted() {
		const vm = this;

		TAPAdmin.$( this.$refs.select )
			// init select2
				.select2( this.options )
				.val( this.value || this.defaultValue )
				.trigger( 'change' )
			// emit event on change.
				.on( 'change', function () {
					if ( this.value ) {
						vm.$emit( 'input', this.value );
						/**
						 * Force select clear
						 */
						if ( vm.options.clearOnSelect ) {
							TAPAdmin.$( vm.$refs.select )
									.val( null ).trigger( 'change' );
						}
					}
				} )
				.on( 'select2:close', event => {
					clearTimeout( this.debounce );
					TAPAdmin.$( event.target ).parents().off( 'scroll.select2' ); //make sure that we dont prevent parents scroll once the dropdown is hidden
				} );
	},
	unmounted() {
		TAPAdmin.$( this.$refs.select )
				.off()
				.select2( 'destroy' );
	},
}
</script>


