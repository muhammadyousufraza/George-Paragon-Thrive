import { createApp } from "vue";
import VTooltipPlugin from "v-tooltip";
import '../../../scss/generic-admin.scss';
import TrackingApp from "@/externals/tracking/TrackingApp";
import store from "@/store";
import { setBrowserClass } from "@/utils/ui-fn";

const app = createApp( TrackingApp ).use( VTooltipPlugin ).use( store );

TAPAdmin.$ = window.jQuery.noConflict();
TAPAdmin._ = window.lodash.noConflict();
app.mount( `#${TAPAdmin.tracking_ribbon_id}` );

setBrowserClass();

