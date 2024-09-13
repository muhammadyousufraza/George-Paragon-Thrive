<template>
	<dropdown :offset="[0,5]" :theme="'automator_toggle_data'" placement="bottom-start">
		<div :class="[wrapperClasses]" class="tap-select-toggle-trigger tap-flex tap-fw tap-input-container">
			<input :placeholder="placeholder" :value="valueLabel" readonly>
			<icon icon-name="tap-caret-down"/>
		</div>
		<template #popper>
			<div class="tap-select-toggle-popper">
				<div ref="closeSelect" v-close-popper class="tap-hidden tap-toggle-close"/>
				<search v-if="withSearch" :is-full-width="true" :value="fieldSearch" :extra-classes="searchClasses" @search="onSearch"/>
				<div :style="{width:selectWidth}" :class="wrapperClasses" class="tap-dynamic-data tap-dynamic-autocomplete">
					<div class="tap-dynamic-data-items tap-scrollbar" @scroll="$emit('dropdownScroll',$event)">
						<toggle-item
							v-for="(wrapper,wrapperIndex) in toggleData"
							:key="wrapperIndex"
							:active-items="activeItems"
							:content="wrapper"
							:content-index="wrapperIndex"
							:content-levels="[wrapperIndex]"
							:field-search="fieldSearch"
							@select-item="selectValue"
							@toggle-item="$emit('toggle',$event)"/>
					</div>
					<div v-if="noData" class="tap-no-data tap-fw ">
						No results found
					</div>
				</div>
			</div>
		</template>
	</dropdown>
</template>

<script>
import DropdownToggle from "@/components/general/DropdownToggle";
import Icon from "@/components/general/Icon";
import Search from "@/components/general/Search";
import ToggleItem from "@/components/general/ToggleItem";
import { triggerElementEvent } from "@/utils/ui-fn";
import { Dropdown } from "v-tooltip";

export default {
	name: "SelectToggle",
	components: {
		Icon,
		ToggleItem,
		Dropdown,
		Search,
	},
	mixins: [ DropdownToggle ],
	props: {
		currentValue: {
			type: [ String, Number ],
			default: '',
		},
		storedData: {
			type: Object,
			default: () => {
			}
		},
		activeItems: {
			type: Array,
			default: () => []
		},
		withSearch: {
			type: Boolean,
			default: false
		},
		wrapperClasses: {
			type: Array,
			default: () => []
		},
		searchClasses: {
			type: Array,
			default: () => []
		},
		selectWidth: {
			type: String,
			default: () => '100%'
		},
		placeholder: {
			type: String,
			default: () => 'Select a value'
		},
	},
	emits: [ 'select', 'toggle', 'dropdownScroll', 'dropdownSearch' ],
	methods: {
		onSearch( searchValue ) {
			this.fieldSearch = searchValue;
			this.$emit( 'dropdownSearch', searchValue );
		},
		selectValue( value, subIndexes ) {
			triggerElementEvent( this.$refs.closeSelect, 'click' );
			this.$emit( 'select', value, subIndexes );
			setTimeout( () => {
				this.fieldSearch = '';
			}, 100 );
		}
	}
}
</script>
