<template>
	<div v-if="!selectValue || (selectValue&&!!fieldData)" class="tap-condition-wrapper tap-flex--column">
		<div v-if="savedData.field" :class="{'first-filter' : !filterIndex}" class="tap-filter-delete">
			<icon icon-name="tap-cross" @click="showDeleteConfirmation=true"/>
		</div>
		<div v-if="!filterIndex" class="tap-container-heading">
			Only trigger if the following conditions are met:
		</div>
		<div v-else class="tap-filter-separator">
			<div class="tap-separator"/>
			<div class="tap-filter-and">
				and
			</div>
		</div>
		<div class="tap-filter-content tap-fw tap-flex--wrap tap-flex--start">
			<div :class="[selectFieldClasses]">
				<select2 :default-value="filterIndex ? '' : 'no-condition'" :options="selectOptions" :value="selectValue" @input="fieldSelected"/>
			</div>
			<component :is="filterComponentName" v-if="filterComponentName" :field-data="fieldData" :filter-data="filterData" :filter-index="filterIndex" :saved-data="savedData" filter-type="condition" @fetch-more="fetchData" @property-change="updateFilterData"/>
		</div>
		<inline-delete v-if="showDeleteConfirmation" :message="'Are you sure you want to delete this condition?'" @cancel="showDeleteConfirmation = !showDeleteConfirmation" @confirm="deleteFilter"/>
	</div>
</template>

<script>
import FilterItem from "@/components/filters/FilterItem";
import { select2Matcher, select2Option } from "@/utils/ui-fn";
import { getFieldInfo } from "@/utils/data-fn";

export default {
	name: "ConditionItem",
	mixins: [ FilterItem ],
	computed: {
		selectValue() {
			return this.savedData.data_object && this.savedData.field ? `${this.savedData.data_object}/${this.savedData.field}` : '';
		},
		selectOptions() {
			const fields = this.filterableFields,
				options = [ {
					id: 'no-condition',
					text: 'Always trigger'
				} ];

			Object.keys( fields ).forEach( field => {
				options.push( {
					id: field,
					text: fields[ field ].name
				} )
			} );

			return {
				data: options,
				placeholder: this.filterIndex ? 'Select field' : 'Always trigger',
				width: '100%',
				theme: 'thrive-automator',
				templateResult: select2Option,
				matcher: select2Matcher,
			};

		},
	},
	methods: {
		fieldSelected( value ) {
			if ( value === 'no-condition' ) {
				this.$emit( 'updateConditions', [ {} ] );
			} else {
				const {appID, field} = getFieldInfo( value, Object.keys( this.getDataObjects ) );

				if ( ! field || this.savedData.field === field ) {
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
				this.$emit( 'updateConditions', currentData );
				if ( this.shouldFetch ) {
					this.fetchData();
				}
			}
		},
		updateFilterData( key, value ) {
			const currentData = this.savedFilters;

			if ( typeof key === 'string' ) {
				currentData[ this.filterIndex ][ key ] = value;
			} else {
				currentData[ this.filterIndex ] = {...currentData[ this.filterIndex ], ...key}
			}

			this.$emit( 'updateConditions', currentData );
		},
		deleteFilter() {
			let data = [ {} ];
			if ( this.savedFilters.length > 1 ) {
				data = this.savedFilters;
				data.splice( this.filterIndex, 1 );
			}
			this.showDeleteConfirmation = false;
			this.$emit( 'updateConditions', data );
		}
	}
}
</script>
