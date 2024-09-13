<template>
	<div class="tap-sidebar-container tap-scrollbar">
		<div class="tap-sidebar-title">
			Automations
		</div>
		<div class="tap-separator"/>
		<search :value="getAutomationFilters.title" @search="toggleFilter({type:'title',filter:$event})"/>
		<div :class="{'tap-filter-active': !getAutomationFilters.status.length && !getAutomationFilters.triggers.length}" class="tap-filter-item  all-filter" @click.prevent.stop="resetFilters">
			<div class="tap-filter-item-name">
				All automations
			</div>
			<div class="tap-filter-item-number">
				{{ getAutomations.length }}
			</div>
		</div>
		<div class="tap-separator"/>
		<div class="tap-filter-apps">
			<p v-if="Object.keys(triggerData).length" class="tap-filter-heading">
				Triggers
			</p>
			<div v-for="(appData,key) in triggerData" :key="key + Math.random()" :class="{'tap-filter-active': getAutomationFilters.triggers?.includes(key)}" class="tap-filter-item" @click="toggleFilter({type:'triggers',filter:key})">
				<div class="tap-filter-item-name">
					{{ key }}
				</div>
				<div class="tap-filter-item-number">
					{{ appData.count }}
				</div>
			</div>
		</div>
		<div class="tap-filter-status">
			<p class="tap-filter-heading">
				Status
			</p>
			<div :class="{'tap-filter-active': getAutomationFilters.status.includes('publish')}" class="tap-filter-item tap-filter-publish" @click="toggleFilter({type: 'status',filter:'publish'})">
				<div class="tap-filter-item-name">
					Active
				</div>
				<div class="tap-filter-item-number">
					{{ getAutomations.filter( aut => aut.status === 'publish' ).length }}
				</div>
			</div>
			<div :class="{'tap-filter-active': getAutomationFilters.status.includes('draft')}" class="tap-filter-item tap-filter-draft" @click="toggleFilter({type: 'status',filter:'draft'})">
				<div class="tap-filter-item-name tap-filter-draft">
					Inactive
				</div>
				<div class="tap-filter-item-number">
					{{ getAutomations.filter( aut => aut.status !== 'publish' ).length }}
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import Search from "@/components/general/Search";
import { ucFirst } from "@/utils/data-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "AutomationSidebar",
	components: {
		Search
	},
	computed: {
		...mapGetters( 'automations', [ 'getFilteredAutomations', 'getAutomationFilters', 'getAutomations' ] ),
		...mapGetters( 'generic', [ 'getApps' ] ),
		triggerData() {
			const data = {};
			this.getAutomations?.forEach( aut => {
				const triggers = aut.content?.[ 0 ].data || {};

				Object.keys( triggers ).forEach( key => {
					let appName = triggers[ key ]?.app_name;

					if ( triggers[ key ]?.app_id ) {
						appName = this.getApps[ triggers[ key ]?.app_id ]?.name;
						if ( ! appName ) {
							appName = triggers[ key ]?.app_id.replaceAll( '_', ' ' ).split( ' ' ).map( part => ucFirst( part ) ).join( ' ' )
						}
					}

					let count = data[ appName ]?.count;

					if ( ! count ) {
						count = 0;
					}
					count ++;
					data[ appName ] = {
						count,
					}
				} )
			} );

			return data;
		}
	},
	methods: {
		...mapActions( 'automations', [ 'toggleFilter', 'resetFilters' ] )
	}
}
</script>
