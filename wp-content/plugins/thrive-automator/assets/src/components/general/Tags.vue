<template>
	<div :class="{'dynamic-tags':shouldShowDynamicData}" class="tap-tags-container tap-flex--column">
		<div class="tap-tags-input tap-flex">
			<input ref="inputEl" type="text" @keyup.prevent.stop="handleTag">
			<div class="tap-vert-separator"/>
			<dropdown v-if="shouldShowDynamicData" :theme="'automator_dynamic_data'" placement="bottom-end">
				<div
					v-tooltip="{
            content: 'Insert dynamic data',
            theme: 'automator',
            offset: [0, 10],
          }"
					class="tap-dynamic-data-trigger tap-flex">
					<icon icon-name="tap-database"/>
				</div>
				<template #popper>
					<div ref="closeSelect" v-close-popper class="tap-hidden tap-toggle-close"/>
					<dynamic-data :dynamic-data="dynamicDataFields(stepIndex-1)" @addShortcode="handleShortcode"/>
				</template>
			</dropdown>
		</div>
		<div class="tap-field-info tap-fw ">
			{{ fieldMessage }}
		</div>
		<div v-if="fieldValue.length" class="tap-autocomplete-values tap-flex--start tap-flex--wrap">
			<div v-for="(item,index) in fieldValue" :key="index" class="tap-autocomplete-item tap-flex">
        <span class="tap-autocomplete-title">
          {{ item }}
        </span>
				<icon icon-name="tap-cross" wrapper-classes="ml-10" @click="deleteItem(index)"/>
			</div>
		</div>
	</div>
</template>

<script>
import DynamicData from "@/components/general/DynamicData";
import Icon from "@/components/general/Icon";
import { triggerElementEvent } from "@/utils/ui-fn";
import { Dropdown } from "v-tooltip";
import { mapGetters } from "vuex";

export default {
	name: "Tags",
	components: {
		Icon,
		DynamicData,
		Dropdown
	},
	props: {
		fieldValue: {
			type: Array,
			default: () => []
		},
		hasDynamicData: {
			type: Boolean,
			default: () => false
		},
		stepIndex: {
			type: Number,
			default: () => 0
		},
		fieldMessage: {
			type: String,
			default: () => ''
		}
	},
	computed: {
		...mapGetters( 'steps', [ 'dynamicDataFields' ] ),
		shouldShowDynamicData() {
			return this.hasDynamicData && Object.keys( this.dynamicDataFields( this.stepIndex - 1 ) )?.length;
		}
	},
	methods: {
		deleteItem( index ) {
			const data = [ ...this.fieldValue ];
			data.splice( index, 1 )
			this.emitValue( data );
		},
		handleTag( event ) {
			const value = this.$refs.inputEl.value.replaceAll( ',', '' );
			if ( value.replaceAll( ' ', '' ).length && [ 13, 188 ].includes( event.keyCode ) ) {
				this.emitValue( [ ...this.fieldValue, value ] );
				this.$refs.inputEl.value = '';
			}
		},
		handleShortcode( shortcode ) {
			if ( shortcode ) {
				triggerElementEvent( this.$refs.closeSelect, 'click' );
				this.$refs.inputEl.value += shortcode;
			}
		},
		emitValue( savedData ) {
			this.$emit( 'tags-change', savedData );
		},
	},
}
</script>


