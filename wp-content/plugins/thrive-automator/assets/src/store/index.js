import { createStore } from 'vuex';
import actions from "@/store/modules/actions";
import automations from "@/store/modules/automations";
import fields from '@/store/modules/fields';
import triggers from '@/store/modules/triggers';
import errors from "@/store/modules/errors";
import generic from "@/store/modules/generic";
import settings from "@/store/modules/settings";
import steps from "@/store/modules/steps";
import suite from "@/store/modules/suite";

const store = createStore( {
	modules: {
		actions,
		automations,
		errors,
		fields,
		triggers,
		generic,
		settings,
		suite,
		steps
	},
} );

export default store;
