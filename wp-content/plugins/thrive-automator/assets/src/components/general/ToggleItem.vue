<template>
	<div :data-item-index="contentIndex" class="tap-toggle-item">
		<div v-if="noOfSubItems" :class="{'is-active':activeItems.includes(contentIndex) || fieldSearch?.trim()?.length}" class="tap-toggle-heading tap-flex--between" @click="$emit('toggleItem',contentIndex)">
			<div :class="{[`pl-${itemLevel*10}`]: itemLevel}" class="tap-toggle-title tap-flex--start tap-fw">
				<icon v-if="itemLevel" :icon-name="'tap-angle-right'" :wrapper-classes="`mr-5 ${contentHasAccess ? '' : 'tap-disabled'}`"/>
				<logo v-if="content.logo || content.image" :value="content.logo || content.image" :wrapper-classes="`ml-5 mr-10 ${contentHasAccess ? '' : 'tap-disabled'}`"/>
				<icon v-if="!contentHasAccess" icon-name="tap-lock" wrapper-classes="mr-10"/>
				<span :class="`${contentHasAccess ? '' : 'tap-disabled'}`">{{ content.label }}</span>
				<span v-if="showCount && contentHasAccess" class="tap-toggle-item-count ml-5">{{ ` (${noOfSubItems} ${noOfSubItems === 1 ? suffixes.singular : suffixes.plural})` }}</span>
				<div v-if="!contentHasAccess && content.access_url" class="tap-get-access mr-10" @click.stop.prevent="openUrl(content.access_url)">
					Get Access
				</div>
			</div>
			<div class="tap-toggle-icon">
				<icon icon-name="tap-caret-down"/>
			</div>
		</div>
		<div v-else class="tap-fw tap-dynamic-item" v-on="contentHasAccess ? {click: ()=>triggerItem(getFieldValue(content), contentIndex)} :{}">
			<div v-if="getFieldValue(content)" class="tap-flex--start tap-toggle-content-item">
				<icon v-if="!contentHasAccess" icon-name="tap-lock" wrapper-classes="ml-5 mr-10"/>
				<logo v-if="contentHasAccess&& (content.logo || content.image)" :value="content.logo || content.image" :wrapper-classes="'ml-5 mr-10'"/>
				<span>{{ getFieldLabel( content ) }}</span>
				<div v-if="!contentHasAccess && content.access_url" class="tap-get-access mr-10" @click.stop.prevent="openUrl(content.access_url)">
					Get Access
				</div>
			</div>
		</div>
		<transition name="tap-toggle">
			<div v-if="activeItems.includes(contentIndex)|| fieldSearch?.trim()?.length" :class="{'tap-disabled': !contentHasAccess}" class="tap-toggle-content tap-flex--column">
				<div v-for="(subfield, subIndex) in content.items"
					 :key="subIndex" :class="{'tap-dynamic-item': !hasItems(subfield)}"
					 class="tap-fw"
					 v-on="getFieldValue(subfield) && subItemHasAccess(subfield) ? {click: ()=>triggerItem(getFieldValue(subfield), subIndex)} :{}">
					<div v-if="getFieldValue(subfield)" class="tap-flex--start tap-toggle-content-item">
						<logo v-if="subItemHasAccess(subfield) && (subfield.logo || subfield.image)" :value="subfield.logo || subfield.image" :wrapper-classes="'ml-5 mr-10'"/>
						<icon v-if="! subItemHasAccess(subfield)" icon-name="tap-lock" wrapper-classes="ml-5 mr-10"/>
						<span>{{ getFieldLabel( subfield ) }}</span>
						<div v-if="!subItemHasAccess(subfield) && subfield.access_url" class="tap-get-access mr-10" @click.stop.prevent="openUrl(subfield.access_url)">
							Get Access
						</div>
					</div>
					<component
						:is="'ToggleItem'"
						v-if="hasItems(subfield)"
						:active-items="activeItems"
						:content="subfield"
						:content-index="subIndex"
						:content-levels="[...contentLevels,subIndex]"
						:field-search="fieldSearch"
						:item-level="itemLevel+1"
						@select-item="triggerItem"
						@toggle-item="$emit('toggleItem',$event)"/>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import Logo from "@/components/general/Logo";

export default {
	name: "ToggleItem",
	components: {
		Icon,
		Logo
	},
	props: {
		contentIndex: {
			type: [ String, Number ],
			default: '',
		},
		content: {
			type: Object,
			default: () => {
			}
		},
		activeItems: {
			type: Array,
			default: () => []
		},
		contentLevels: {
			type: Array,
			default: () => []
		},
		fieldSearch: {
			type: String,
			default: ''
		},
		itemLevel: {
			type: Number,
			default: 0
		},
		showCount: {
			type: Boolean,
			default: false
		},
		suffixes: {
			type: Object,
			default: () => {
			}
		}
	},
	computed: {
		contentHasAccess() {
			return this.content.has_access ?? true;
		},
		noOfSubItems() {
			return Object.keys( this.content.items || {} ).length;
		},
	},
	methods: {
		subItemHasAccess( subItem ) {
			return subItem.has_access ?? true;
		},
		openUrl( url ) {
			window.open( url, '_blank' );
		},
		hasItems( data ) {
			return Object.keys( data?.items || {} ).length > 0;
		},
		triggerItem( value, subIndex ) {
			if ( ! Array.isArray( subIndex ) ) {
				subIndex = [ subIndex ];
			}
			this.$emit( 'selectItem', value, [ ...new Set( [ ...this.contentLevels, ...subIndex ] ) ] )
		},
		getFieldValue( data ) {
			return data?.value || data?.id || '';
		},
		getFieldLabel( data ) {
			return data?.label || data?.name || '';
		},
	}
}
</script>
