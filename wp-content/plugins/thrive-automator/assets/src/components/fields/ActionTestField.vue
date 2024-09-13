<template>
	<div :class="{'mb-0' : requestStatus!=='before', 'mb-20': requestStatus==='before'}" class="tap-action-test-field tap-action-field tap-flex--column mt-20">
		<div class="tap-container-heading">
			{{ fieldData.name }}
		</div>
		<icon-button v-if="parentHasErrors" v-tooltip="{
      content: 'Fix action fields errors first' ,
      theme: 'automator',
      offset: [0, 15],
    }" :button-styles="['clean', 'ghost']" :button-text="'Send test'" class="tap-prevent"/>
		<icon-button v-else :button-styles="['clean', 'ghost']" :button-text="'Send test'" @click="sendTest"/>
		<div v-if="requestStatus!=='before'" class="tap-action-response tap-flex mt-10 p-5 tap-fw">
			<icon-button v-if="requestStatus === 'sending'" :button-styles="['ghost','no-border', 'clean' ,'sending']" button-text="Sending" icon-name="tap-spinner"/>
			<icon-button v-if="requestStatus === 'done' && responseStatus === 200" :button-styles="['ghost','no-border', 'clean', 'success']" :button-text="successMessage" icon-name="tap-check"/>
			<icon-button v-if="requestStatus === 'done' && responseStatus !== 200" :button-styles="['ghost','no-border', 'clean', 'fail']" :button-text="failMessage" icon-name="tap-info"/>
		</div>
	</div>
</template>

<script>
import GenericField from "@/components/fields/GenericField";
import IconButton from '@/components/general/IconButton';
import { mapActions, mapGetters } from "vuex";

const errorLabels = {
	400: 'Bad Request',
	401: 'Unauthorized',
	403: 'Forbidden',
	404: 'URL not found',
	408: 'Request timeout',
	500: 'Internal server error',
};
export default {
	name: "ActionTestField",
	components: {IconButton},
	mixins: [ GenericField ],
	data() {
		return {
			responseMessage: '',
			requestStatus: 'before',
			responseStatus: 404,
		};
	},
	computed: {
		...mapGetters( 'steps', [ 'getAutomationDummyData' ] ),
		failMessage() {
			let failMessage = this.responseMessage;
			if ( ! failMessage ) {
				if ( this.extraOptions.fail_message ) {
					failMessage = this.fieldData.extra_options?.fail_message;
				} else {
					failMessage = 'Failed';
				}
			}
			if ( this.extraOptions.append_error_code ) {
				failMessage = `${failMessage} ${this.responseStatus} - ${errorLabels[ this.responseStatus ]}`;
			}

			return failMessage;
		},
		successMessage() {
			let successMessage = this.responseMessage;
			if ( ! successMessage ) {
				if ( this.extraOptions.success_message ) {
					successMessage = this.fieldData.extra_options?.success_message;
				} else {
					successMessage = 'Success';
				}
			}

			return successMessage;
		},
	},
	methods: {
		...mapActions( 'actions', [ 'sendTestData' ] ),
		sendTest() {
			this.requestStatus = 'sending';

			this.sendTestData( {
				action_id: this.parentData?.id,
				action_data: this.parentData?.extra_data,
				automation_data: this.getAutomationDummyData( this.stepIndex )
			} ).then( response => {
				this.responseStatus = response?.status_code;
				this.responseMessage = response?.message || '';
			} ).finally( () => {
				this.requestStatus = 'done';
				setTimeout( () => {
					this.requestStatus = 'before';
				}, 5000 );
			} );
		}
	}
}
</script>
