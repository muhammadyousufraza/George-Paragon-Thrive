import { createApp } from "vue";
import DeactivateApp from "@/externals/deactivate-feedback/DeactivateApp";
import VTooltipPlugin from "v-tooltip";
import '../../../scss/generic-admin.scss';
import { setBrowserClass } from "@/utils/ui-fn";

const app = createApp( DeactivateApp ).use( VTooltipPlugin );


app.mount( `#tap-deactivate-feedback` );
TAPAdmin.$ = window.jQuery.noConflict();
TAPAdmin._ = window.lodash.noConflict();

setBrowserClass();
