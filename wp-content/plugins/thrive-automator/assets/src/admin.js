import { createApp } from 'vue';
import App from './AdminApp.vue';
import router from './router';
import store from './store/index';
import '../scss/app-styles.scss'; //app css
import VTooltipPlugin from 'v-tooltip'
import 'v-tooltip/dist/v-tooltip.css'
import AutocompleteField from "@/components/fields/AutocompleteField";
import SelectField from "@/components/fields/SelectField";
import CheckboxField from "@/components/fields/CheckboxField";
import TextField from "@/components/fields/TextField";
import TextareaField from "@/components/fields/TextareaField";
import TagsField from "@/components/fields/TagsField";
import DoubleDropdownField from "@/components/fields/DoubleDropdownField";
import DateTimeField from "@/components/fields/DateTimeField";
import RadioField from "@/components/fields/RadioField";
import ActionTestField from "@/components/fields/ActionTestField";
import KeyValuePairField from "@/components/fields/KeyValuePairField";
import InputField from "@/components/general/InputField";
import Search from "@/components/general/Search";
import ToggleItem from "@/components/general/ToggleItem";
import Icon from "@/components/general/Icon";
import MappingPairField from "@/components/fields/MappingPairField";
import AutocompleteToggleField from "@/components/fields/AutocompleteToggleField";
import SelectToggleField from "@/components/fields/SelectToggleField";
import SwitchField from "@/components/fields/SwitchField";
import NumberField from "@/components/fields/NumberField";
import { setBrowserClass } from "@/utils/ui-fn";

window.TAPAdmin = window.TAPAdmin || {};

const app = createApp( App )
	.use( router )
	.use( store )
	//register tooltip plugin with our "themes"
	.use( VTooltipPlugin, {
		themes: {
			automator: {
				$extend: 'tooltip',
				hideTriggers: events => [ ...events, 'click' ],
			},
			automator_dropdown: {
				$extend: 'dropdown',
				disposeTimeout: 0
			},
			automator_dynamic_data: {
				$extend: 'automator_dropdown',
			},
			automator_toggle_data: {
				$extend: 'automator_dropdown',
			},
			automator_html_tooltip: {
				$extend: 'dropdown',
			},
			automator_menu_tooltip: {
				$extend: 'dropdown',
			},
		},
	} )
	//register action subfields as generic components so we can have custom combinations of them
	.component( 'AutocompleteField', AutocompleteField )
	.component( 'CheckboxField', CheckboxField )
	.component( 'SelectField', SelectField )
	.component( 'TextField', TextField )
	.component( 'TextareaField', TextareaField )
	.component( 'RadioField', RadioField )
	.component( 'DoubleDropdownField', DoubleDropdownField )
	.component( 'DateTimeField', DateTimeField )
	.component( 'TagsField', TagsField )
	.component( 'NumberField', NumberField )
	.component( 'ActionTestField', ActionTestField )
	.component( 'AutocompleteToggleField', AutocompleteToggleField )
	.component( 'SelectToggleField', SelectToggleField )
	.component( 'MappingPairField', MappingPairField )
	.component( 'ToggleItem', ToggleItem )
	.component( 'InputField', InputField )
	.component( 'SwitchField', SwitchField )
	.component( 'Search', Search )
	.component( 'Icon', Icon )
	.component( 'KeyValuePairField', KeyValuePairField );

app.mount( `#${TAPAdmin.app_id}` );
//export the app in case somebody would need it
TAPAdmin.app = app;
TAPAdmin.$ = window.jQuery.noConflict();
TAPAdmin._ = window.lodash.noConflict();

setBrowserClass();
