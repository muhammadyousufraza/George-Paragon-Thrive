import { createRouter, createWebHashHistory } from 'vue-router';
import SavedAutomations from "@/views/SavedAutomations";
import CreateAutomation from "@/views/Automation";
import ErrorLog from "@/views/ErrorLog";
import SuiteIntegration from "@/views/SuiteIntegration";
import Apps from "@/views/Apps";
import store from "@/store";
import Settings from "@/views/Settings";

const routes = [
	{
		path: '', // default state on app should be the dash
		redirect: '/saved'
	},
	{
		path: '/automation/:id?', // ? means that id is optional here (while adding a new automation)
		name: 'CreateAutomation',
		component: CreateAutomation,
	},
	{
		path: '/saved',
		name: 'SavedAutomations',
		component: SavedAutomations,
	},
	{
		path: '/error_log',
		name: 'ErrorLog',
		component: ErrorLog
	},
	{
		path: '/suite',
		name: 'SuiteIntegration',
		component: SuiteIntegration
	},
	{
		path: '/apps',
		name: 'Apps',
		component: Apps
	},
	{
		path: '/settings',
		name: 'Settings',
		component: Settings
	}
];

const router = createRouter( {
	history: createWebHashHistory(),
	routes,
} );

router.beforeEach( ( _to, _from, next ) => {
	store.dispatch( 'generic/setNoticeData', {show: false} );
	if ( ! TAPAdmin.debug_mode && TAPAdmin.preventLeave ) {
		if ( confirm( 'You have unsaved changes. Are you sure you want to leave?' ) ) {
			TAPAdmin.preventLeave = false;
			next();
		} else {
			next( false );
		}
	} else {
		next();
	}
} );

export default router;
