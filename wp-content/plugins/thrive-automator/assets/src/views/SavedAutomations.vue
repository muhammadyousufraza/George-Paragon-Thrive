<template>
	<!-- eslint-disable -->
	<suite-ribbon v-if="showRibbon"/>
	<no-automation v-if="!getAutomations.length"/>
	<div v-else class="tap-saved-automations">
		<div class="tap-screen-header tap-flex--between">
			<span>Your automations</span>
			<icon-button :button-text="'Add new'" :icon-name="'tap-plus'" @click="createAutomation"/>
		</div>
		<div v-if="getPaginationAutomations.length" class="tap-saved-actions tap-flex--between">
			<div class="tap-col--2">
				<checkbox
					:id="'toggleAll'"
					v-tooltip="{
            content: 'Check all',
            theme: 'automator',
            offset: [0, 10],
          }"
					:value="allSelected"
					@input="toggleAll"/>
			</div>
			<div v-if="selectedAut.length" class="tap-col--2">
				<icon :tooltip="'Publish selected'" icon-name="tap-rocket" @click="toggleStatus"/>
			</div>
			<div v-if="selectedAut.length" class="tap-col--2">
				<icon :tooltip="'Unpublish selected'" icon-name="tap-pause" @click="toggleStatus(false)"/>
			</div>
			<div v-if="selectedAut.length" class="tap-col--2">
				<icon :tooltip="'Delete selected'" icon-name="tap-trash" @click="showDeleteModal = ! showDeleteModal"/>
			</div>
			<div class="tap-col--2"/>
			<div class="tap-col--90"/>
		</div>
		<div v-if="getPaginationAutomations.length" class="tap-aut-header tap-flex">
			<div class="tap-col--2"/>
			<div class="tap-col--28">
				Automation name
			</div>
			<div class="tap-col--20">
				Triggers
			</div>
			<div class="tap-col--40">
				Actions
			</div>
			<div class="tap-col--10">
				Status
			</div>
		</div>
		<automation-preview v-for="automation in getPaginationAutomations" :key="automation.id" :aut="automation" :checked="selectedAut.includes(automation.id)" @aut-check="selectAutomation"/>
		<pagination :current-page="getAutomationFilters.pagination" :total-count="getFilteredAutomations.length" @new-page="changePagination"/>
		<delete-confirmation :modal-description="'Are you sure you want to delete these automations?'" :modal-header="'Delete automations'" :modal-sizes="{'--modal-width':'30%','--modal-height':'13%'}" :should-show="showDeleteModal" @cancel="showDeleteModal = !showDeleteModal" @confirm="deleteAut"/>
	</div>
	<!-- eslint-enable -->
</template>
<script>
import AutomationPreview from "@/components/automation/AutomationPreview";
import NoAutomation from "@/components/automation/NoAutomation";
import Checkbox from "@/components/general/Checkbox";
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import DeleteConfirmation from "@/components/general/modals/DeleteConfirmation";
import Pagination from "@/components/general/Pagination";
import { toggleAppLoader } from "@/utils/ui-fn";
import { mapActions, mapGetters } from 'vuex';
import SuiteRibbon from "@/components/ttw/SuiteRibbon";

export default {
	name: 'SavedAutomations',
	components: {
		SuiteRibbon,
		Pagination,
		Checkbox,
		AutomationPreview,
		IconButton,
		Icon,
		NoAutomation,
		DeleteConfirmation
	},
	data() {
		return {
			selectedAut: [],
			allSelected: false,
			showDeleteModal: false,
		}
	},
	computed: {
		...mapGetters( 'automations', [ 'getAutomations', 'getFilteredAutomations', 'getAutomationFilters', 'getPaginationAutomations' ] ),
		...mapGetters( 'suite', [ 'getConnected', 'getInstalled', 'getActive' ] ),
		showRibbon() {
			return ! this.getActive || ! this.getInstalled || ! this.getConnected;
		},
		metricsNotice() {
			return TAPAdmin.$( '.tve-metrics-consent-notice' );
		}
	},
	watch: {
		getAutomations() {
			this.$root.$el.classList.toggle( 'tap-no-sidebar', this.getAutomations.length < 2 );
		}
	},
	mounted() {
		/* reset steps too*/
		this.resetSteps();
		this.resetFilters()
		this.$root.$el.classList.toggle( 'tap-no-sidebar', this.getAutomations.length < 2 );
		this.$el.parentNode.classList.toggle( 'tap-columns', this.showRibbon || this.metricsNotice.length );
		//move the notice so its displayed well in dashboard
		if ( this.metricsNotice.length ) {
			if ( this.showRibbon ) {
				this.metricsNotice.remove();
			} else {
				TAPAdmin.$( this.$el.parentNode ).prepend( this.metricsNotice );
			}
		}
	},
	methods: {
		...mapActions( 'steps', [ 'setCurrentAutomation', 'resetSteps', 'updateAutomation', 'deleteAutomation' ] ),
		...mapActions( 'automations', [ 'resetFilters', 'toggleFilter' ] ),
		//set-up default data for new automation
		createAutomation() {
			this.setCurrentAutomation( {
				title: 'New automation',
				status: false,
			} );
			this.$router.push( {path: 'automation'} );
		},
		//handle selected automations for the list
		selectAutomation( id ) {
			this.selectedAut = TAPAdmin._.xor( this.selectedAut, [ id ] );
			this.allSelected = this.selectedAut.length === this.getPaginationAutomations.length;
		},
		toggleStatus( publish = true ) {
			const promises = [];
			toggleAppLoader();
			this.selectedAut.forEach( id => {
				promises.push( this.updateAutomation( {
					id,
					status: publish ? 'publish' : 'draft'
				} ) );
			} )

			Promise.allSettled( promises ).then( () => {
				toggleAppLoader( false );
				this.allSelected = false;
				this.selectedAut = [];
			} );
		},
		deleteAut() {
			const promises = [],
				wasAll = this.selectedAut.length === this.getPaginationAutomations.length;

			this.showDeleteModal = false;

			toggleAppLoader();

			this.selectedAut.forEach( id => promises.push( this.deleteAutomation( id ) ) );

			Promise.allSettled( promises ).then( () => {
				toggleAppLoader( false );
				this.allSelected = false;
				this.selectedAut = [];
				//move one page back if all automations were deleted
				if ( wasAll ) {
					this.changePagination( this.getAutomationFilters.pagination - 1 );
				}
			} );
		},
		//handle all selected event
		toggleAll( id, value ) {
			this.allSelected = value;

			if ( this.allSelected ) {
				this.getPaginationAutomations.forEach( aut => {
					this.selectedAut.push( aut.id )
				} )
				this.selectedAut = [ ...new Set( this.selectedAut ) ]
			} else {
				this.selectedAut = [];
			}
		},
		changePagination( value ) {
			this.allSelected = false;
			this.selectedAut = [];
			this.toggleFilter( {type: 'pagination', filter: value} )
		}
	}
}
</script>
