<template>
	<div v-if="Array.isArray(getErrors)" class="tap-error-log tap-scrollbar">
		<div class="tap-screen-header">
			<span>Logs</span>
		</div>
		<div class="tap-error-filters">
			<div class="tap-col--15">
				<select2 :options="automationOptions" :value="automation" @input="changeAut"/>
			</div>
			<div class="tap-col--15">
				<select2
					:options="intervalOptions" :value="interval" @input="changeInterval"/>
			</div>
			<div class="tap-col--15 tap-flex--column tap-error-count">
				{{ getTotalCount }} result{{ getTotalCount === 1 ? '' : 's' }}
			</div>
			<div class="tap-error-settings tap-flex--end">
				<input-field
					:value="storeNumber" label="Store up to" type="number" @input="updateStore"/>
				<div>log entries</div>
				<div v-if="getErrors.length" class="tap-vert-separator"/>
				<icon-button
					v-if="getErrors.length" :button-styles="['ghost','delete', 'no-border', 'clean']" button-text="Delete all" icon-name="tap-trash" @click="showDeleteModal=true"/>
			</div>
		</div>
		<div v-if="getErrors.length" class="tap-error-header">
			<div class="tap-col--15">
				Automation name
			</div>
			<div class="tap-col--10">
				Date
			</div>
			<div class="tap-col--70">
				Message
			</div>
			<div class="tap-col--3">
				Actions
			</div>
		</div>
		<error
			v-for="(error,index) in getErrors"
			:key="index"
			:content="error"
			@afterDelete="fetchData(false)"
			@displayError="showContent"/>
		<div v-if="!getErrors.length" class="tap-no-logs tap-flex--column">
			<icon icon-name="tap-file-search"/>
			<p>No logs found</p>
		</div>
		<div v-if="getErrors.length" class="tap-error-footer tap-flex--between">
			<input-field :label="'Rows per page'" :placeholder="'Rows'" :type="'number'" :value="rows" @input="changeRows"/>
			<pagination :current-page="currentPage" :rows="rows" :total-count="getTotalCount" @new-page="changePagination"/>
		</div>
		<error-modal :modal-content="modalContent" :show-modal="showModal" @cancel="showModal = !showModal"/>
		<delete-confirmation :modal-description="'Are you sure you want to delete these logs?'" :modal-header="'Delete logs'" :modal-sizes="{'--modal-width':'30%','--modal-height':'13%'}" :should-show="showDeleteModal" @cancel="showDeleteModal = !showDeleteModal" @confirm="deleteAllLogs"/>
	</div>
</template>

<script>
import Error from "@/components/general/Error";
import Select2 from "@/components/general/Select2";
import { mapActions, mapGetters } from "vuex";
import { select2Matcher, select2Option, toggleAppLoader } from "@/utils/ui-fn";
import ErrorModal from "@/components/general/modals/ErrorModal";
import InputField from "@/components/general/InputField";
import Pagination from "@/components/general/Pagination";
import IconButton from "@/components/general/IconButton";
import Icon from "@/components/general/Icon";

import DeleteConfirmation from "@/components/general/modals/DeleteConfirmation";

export default {
	name: "ErrorLog",
	components: {
		Icon,
		IconButton,
		Pagination,
		InputField,
		ErrorModal,
		Error,
		Select2,
		DeleteConfirmation
	},
	data() {
		return {
			automation: 'all',
			interval: 'all',
			showModal: false,
			modalContent: '0',
			rows: TAPAdmin.log_settings.rows,
			currentPage: 1,
			debounce: false,
			storeNumber: TAPAdmin.log_settings.max_entries,
			showDeleteModal: false,
		}
	},

	computed: {
		...mapGetters( 'errors', [ 'getErrors', 'getTotalCount' ] ),
		...mapGetters( 'automations', [ 'getAutomations' ] ),
		automationOptions() {
			return {
				data: [ {
					id: 'all',
					text: 'All automations'
				},
					...this.getAutomations.map( aut => {
						return {
							id: aut.id,
							text: aut.title
						}
					} ) ],
				placeholder: 'Select a value',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};
		},
		intervalOptions() {
			return {
				data: [ {
					id: 'all',
					text: 'All entries'
				}, {
					id: '7',
					text: 'Last 7 days'
				}, {
					id: '14',
					text: 'Last 14 days'
				}, {
					id: '30',
					text: 'Last 30 days'
				}, ],
				minimumResultsForSearch: - 1,
				placeholder: 'Select a value',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option
			}
		}
	},
	watch: {
		getErrors() {
			if ( ! this.getErrors.length && this.currentPage !== 1 ) {
				this.changePagination( 1 );
			}
		}
	},
	beforeMount() {
		this.fetchErrors();
	},
	methods: {
		...mapActions( 'errors', [ 'fetchErrors', 'setSettings', 'deleteAll' ] ),
		deleteAllLogs() {
			this.showDeleteModal = false;
			this.deleteAll()
		},
		updateStore( value ) {
			value = parseInt( value );
			clearTimeout( this.debounce );
			this.debounce = setTimeout( () => {
				if ( ! isNaN( value ) && this.rows !== value ) {
					TAPAdmin.log_settings.max_entries = this.storeNumber = value;
					this.updateSettings();
				}
			}, 1000 )

		},
		changeRows( value ) {
			value = parseInt( value );
			clearTimeout( this.debounce );
			this.debounce = setTimeout( () => {
				if ( ! isNaN( value ) && this.rows !== value ) {
					TAPAdmin.log_settings.rows = this.rows = value;
					this.updateSettings();
				}
			}, 1000 )
		},
		updateSettings() {
			this.currentPage = 1;//pagination
			toggleAppLoader();
			this.setSettings( {
				settings: {
					rows: this.rows,
					max_entries: this.storeNumber
				}
			} ).then( () => {
				toggleAppLoader( false )
			} );
		},
		changeInterval( value ) {
			if ( this.interval !== value ) {
				this.interval = value;
				this.currentPage = 1;//pagination
				this.fetchData();
			}
		},
		changeAut( value ) {
			if ( this.automation !== value ) {
				this.automation = value;
				this.currentPage = 1;//pagination
				this.fetchData();
			}
		},

		changePagination( value ) {
			this.currentPage = value;
			this.fetchData();
		},
		//Fetch error logs based on props
		fetchData( showLoader = true ) {
			showLoader && toggleAppLoader();
			this.fetchErrors( {id: this.automation, interval: this.interval, page: this.currentPage - 1} ).finally( () => {
				showLoader && toggleAppLoader( false )
			} );
		},
		showContent( id ) {
			this.modalContent = this.getErrors.filter( error => Number( error.id ) === Number( id ) )?.[ 0 ].raw_data;
			this.showModal = this.modalContent.length > 1;
		}
	}
}
</script>


