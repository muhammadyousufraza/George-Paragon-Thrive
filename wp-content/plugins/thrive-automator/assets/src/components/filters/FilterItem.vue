<template>
	<div
		v-if="!selectValue || (selectValue&&!!fieldData)" class="tap-filter-wrapper tap-flex--column">
		<div :class="{'first-filter' : !filterIndex}" class="tap-filter-delete">
			<icon icon-name="tap-cross" @click="showDeleteConfirmation=true"/>
		</div>
		<div v-if="!filterIndex" class="tap-container-heading">
			Only continue
		</div>
		<div v-if="!filterIndex" class="tap-filter-separator">
			<div class="tap-separator"/>
			<div class="tap-filter-and">
				if
			</div>
		</div>
		<div v-else class="tap-filter-separator">
			<div class="tap-separator"/>
			<div class="tap-filter-and">
				and
			</div>
		</div>
		<div class="tap-filter-content tap-fw tap-flex--wrap tap-flex--start">
			<div :class="[selectFieldClasses]">
				<select2 :options="selectOptions" :value="selectValue" @input="fieldSelected"/>
				<div v-if="!savedData?.field" class="tap-filter-error">
					{{ 'Invalid. Please select a field' }}
				</div>
			</div>
			<component
				:is="filterComponentName"
				v-if="filterComponentName"
				:field-data="fieldData"
				:filter-data="filterData"
				:filter-index="filterIndex"
				:saved-data="savedData"
				@fetch-more="fetchData"
				@property-change="updateFilterData"/>
		</div>
		<inline-delete
			v-if="showDeleteConfirmation"
			:message="'Are you sure you want to delete this filter?'"
			@cancel="showDeleteConfirmation = !showDeleteConfirmation"
			@confirm="deleteFilter"/>
	</div>
</template>

<script>
import components from '@/components/filters/types';
import Icon from "@/components/general/Icon";
import InlineDelete from "@/components/general/InlineDelete";
import Select2 from "@/components/general/Select2";
import { getComponentName, getFieldInfo } from "@/utils/data-fn";
import { select2Matcher, select2Option } from "@/utils/ui-fn";
import { mapActions, mapGetters } from "vuex";


export default {
	name: "FilterItem",
	components: {
		...{
			Select2,
			Icon,
			InlineDelete
		},
		...components
	},
	props: {
		filterableFields: {
			type: Object,
			default: () => {
			}
		},
		savedFilters: {
			type: Array,
			default: () => [],
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
		saved: {
			type: Boolean,
			default: () => false
		},
		stepIndex: {
			type: Number,
			default: () => 0,
		},
	},
	data() {
		return {
			showDeleteConfirmation: false
		};
	},

	computed: {
		...mapGetters( 'fields', [ 'getFieldOptions', 'shouldPreventFieldFetch' ] ),
		...mapGetters( 'generic', [ 'getFilters', 'getDataObjects' ] ),
		filterComponentName() {
			let component = this.savedData.filter && typeof this.savedData.filter === 'string' ? this.savedData.filter : '';

			if ( component === 'autocomplete_contains' ) {
				component = 'autocomplete';
			}

			return component ? `${getComponentName( component )}Filter` : '';
		},
		selectFieldClasses() {
			let classes = '';
			if ( this.filterComponentName ) {
				switch ( this.filterComponentName ) {
					case 'DropdownFilter':
					case 'BooleanFilter':
					case 'StringEqualsFilter':
						classes = 'tap-filter-col--45';
						break;
					default:
						classes = 'tap-filter-col';
				}
			} else {
				classes = 'tap-filter-col--full';
			}
			return classes;
		},
		selectOptions() {
			const fields = this.filterableFields,
				options = [];

			Object.keys( fields ).forEach( field => {
				options.push( {
					id: field,
					text: fields[ field ].name
				} )
			} );

			return {
				data: options,
				placeholder: 'Select field',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};

		},
		selectValue() {
			return this.savedData.data_object && this.savedData.field ? `${this.savedData.data_object}/${this.savedData.field}` : '';
		},
		fieldData() {
			return this.filterableFields?.[ this.selectValue ];
		},
		filterData() {
			return this.getFilters[ this.fieldData?.filters[ 0 ] ]
		},
		shouldFetch() {
			return this.fieldData?.is_ajax_field && ! this.getFieldOptions?.[ this.savedData.data_object ]?.[ this.savedData?.field ]
		},
	},
	watch: {
		shouldFetch( newValue ) {
			if ( newValue ) {
				this.fetchData();
			}
		},
		fieldData( newValue ) {
			//delete filter if there is no matching field
			if ( this.selectValue && ! newValue ) {
				this.deleteFilter();
			}
		}
	},
	created() {
		if ( ! this.selectValue || this.fieldData && this.filterableFields[ `${this?.savedData?.data_object}/${this?.savedData?.field}` ] ) {
			if ( this.shouldFetch ) {
				this.fetchData();
			}
		} else {
			this.deleteFilter()
		}
	},
	methods: {
		...mapActions( 'steps', [ 'updateStep', 'addStepData', 'deleteStep' ] ),
		...mapActions( 'fields', [ 'fetchFieldOptions' ] ),
		fetchData( options = {} ) {
			if ( this.shouldPreventFieldFetch[ `${this.savedData.data_object}${this.savedData.field}` ] ) {
				return;
			}

			if ( ! options.preventLoader ) {
				this.toggleLoader();
			}
			const savedData = TAPAdmin._.cloneDeep( this.savedData );

			delete savedData.filter;
			delete savedData.data_object;
			delete savedData.field;

			this.fetchFieldOptions( {
				object_id: this.savedData.data_object,
				field: this.savedData.field,
				filters: {
					saved_data: savedData,
					limit: this.fieldData.extra_options?.limit ?? TAPAdmin.load_limit,
					...options
				},
			} ).finally( () => {
				this.toggleLoader( false );
			} );
		},
		deleteFilter() {
			//if we delete the only filter delete the whole step
			if ( this.savedFilters.length === 1 ) {
				this.deleteStep( this.stepIndex );
			} else {
				const currentData = this.savedFilters;
				currentData.splice( this.filterIndex, 1 );
				this.showDeleteConfirmation = false;
				this.updateStep( {
					index: this.stepIndex,
					content: {
						type: 'filters',
						saved: this.saved,
						data: currentData
					}
				} );
			}
		},
		fieldSelected( value ) {
			const {appID, field} = getFieldInfo( value, Object.keys( this.getDataObjects ) );

			if ( ! field || this.savedData?.field === field ) {
				return;
			}

			const fieldData = this.filterableFields[ value ],
				currentData = this.savedFilters;

			currentData[ this.filterIndex ] = {
				data_object: appID,
				field,
				filter: this.getFilters[ fieldData.filters[ 0 ] ].info.id,
				value: '',
				operator: '',
				unit: '',
				validation: {
					isValid: false,
					message: 'No value'
				},
			};
			this.updateStep( {
				index: this.stepIndex,
				content: {
					type: 'filters',
					saved: this.saved,
					data: currentData
				}
			} );

			if ( this.shouldFetch ) {
				this.fetchData();
			}
		},
		updateFilterData( key, value ) {
			const currentData = this.savedFilters;

			if ( typeof key === 'string' ) {
				currentData[ this.filterIndex ][ key ] = value;
			} else {
				currentData[ this.filterIndex ] = {...currentData[ this.filterIndex ], ...key}
			}

			this.updateStep( {
				index: this.stepIndex,
				content: {
					type: 'filters',
					saved: this.saved,
					data: currentData
				}
			} );
		},
		toggleLoader( show = true ) {
			this.$el?.closest( '.tap-card-container' )?.classList?.toggle( 'tap-loader--card', show );
		}
	}
}
</script>


