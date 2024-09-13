export const GenericFields = {
	'tap_generic_text': {
		description: 'Filter by $ value',
		dummy_value: 'text',
		filters: [ 'string_ec' ],
		id: 'tap_generic_text',
		field_type: 'text',
		is_ajax_field: false,
		name: 'Text',
		placeholder: '',
		shortcode_tag: '',
		tooltip: 'Filter by $ value',
		validators: [ 'required' ],
		value_type: 'string'
	},
	'tap_generic_number': {
		description: 'Filter by $ value',
		dummy_value: 25,
		filters: [ 'number_comparison' ],
		id: 'tap_generic_number',
		field_type: 'number',
		is_ajax_field: false,
		name: 'Number',
		placeholder: '',
		shortcode_tag: '',
		tooltip: 'Filter by $ value',
		validators: [ 'required' ],
		value_type: 'number'
	},
	'tap_generic_date': {
		description: 'Filter by $ value',
		dummy_value: '2021-09-06',
		filters: [ 'date' ],
		id: 'tap_generic_date',
		is_ajax_field: false,
		name: 'Date',
		placeholder: '',
		shortcode_tag: '',
		tooltip: 'Filter by $ value',
		validators: [ 'required' ],
		value_type: 'date',
		field_type: 'date',
	},
	'tap_generic_timedate': {
		description: 'Filter by $ value',
		dummy_value: '2021-09-06 17:18:57',
		filters: [ 'time_date' ],
		id: 'tap_generic_timedate',
		is_ajax_field: false,
		name: 'Time & date',
		placeholder: '',
		shortcode_tag: '',
		tooltip: 'Filter by $ value',
		validators: [ 'required' ],
		value_type: 'timedate',
		field_type: 'timedate'
	},
	'tap_generic_boolean': {
		description: 'Filter by $ value',
		dummy_value: true,
		filters: [ 'boolean' ],
		id: 'tap_generic_boolean',
		is_ajax_field: false,
		name: 'Boolean',
		placeholder: '',
		shortcode_tag: '',
		tooltip: 'Filter by $ value',
		validators: [ 'required' ],
		value_type: 'boolean',
		field_type: 'boolean',
	}
}
export const fileTypes = {
	'application/zip': [ 'application/x-zip-compressed', 'application/zip', 'application/zip-compressed' ],
};

export const dbIcon = '<div class="tap-icon-wrapper tap-icon-database"><svg class="tap-icon"> <use xlink:href="#tap-database" /></svg></div>';

export const regexPatterns = {
	shortcode: /%(.*?)%/
}

export const validationRegex = {
	httpHeaders: '[a-zA-Z0-9$-]+',
	fieldKey: '[a-zA-Z0-9-_]+(\\[(.*?)\\])*$',
}
