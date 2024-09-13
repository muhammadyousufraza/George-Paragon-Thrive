<template>
	<div :class="{'tap-webhook-errors': trigger.hasErrors}" class="tap-receive-webhook-container">
		<div class="tap-gw-options">
			<read-only :value="webhookURL" label="Webhook URL"/>
			<div class="tap-gw-fields tap-flex--column">
				<div class="tap-webhook-listen tap-fw tap-flex--column mt-10 mb-10">
					<div class="tap-toggle-item tap-fw">
						<div :class="{'is-active':!collapsed}" class="tap-toggle-heading tap-flex--between mb-5 p-5" @click="collapsed=!collapsed">
							<div class="tap-toggle-title">
								Incoming data
							</div>
							<div class="tap-toggle-icon">
								<icon icon-name="tap-chevron-down"/>
							</div>
						</div>
						<transition name="tap-toggle">
							<div v-if="!collapsed" class="tap-toggle-content tap-flex--column tap-scrollbar" @scroll="fieldContentScroll">
								<field-generator v-for="(field,index) in triggerFields" :key="index" :available-fields="fields" :forbidden-types="forbiddenTypes(index)" :force-show-empty-errors="noFields" :has-errors="trigger.hasErrors" :pair-value="field" :with-toggle-select="true" @delete="deleteField" @input="insertNewField"/>
							</div>
						</transition>
					</div>
					<div class="tap-flex--column tap-fw mt-10">
						<div :class="{'hide-separator':collapsed}" class="tap-flex tap-fw pb-10 tap-fields-buttons pt-10">
							<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Add new'" :icon-name="'tap-plus'" @click="addField"/>
							<div class="tap-vert-separator"/>
							<icon-button :button-styles="['clean', 'ghost' ,'no-border']" :button-text="listenText" icon-name="tap-radar" @click="initListen"/>
						</div>
						<div v-if="listening" class="tap-rw-listening tap-flex--column mt-10 pt-25 pb-25">
							<icon icon-name="tap-cross" @click="stopListen"/>
							<icon-button :button-styles="['ghost','no-border', 'clean' ,'sending']" button-text="Listening" icon-name="tap-spinner"/>
							<div class="tap-rw-listening-message pr-50 pl-50">
								You can now trigger your webhook and we will auto populate
								your incoming data fields
							</div>
							<div class="tap-listening-timer mt-10">
								{{ minutes }}:{{ String( seconds ).length === 1 ? `0${seconds}` : seconds }}
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tap-gw-headers">
				<div class="tap-gw-headers-radio ">
					<div class="tap-container-heading">
						Security settings
					</div>
					<div class="tap-flex--start tap-flex--wrap mb-10">
						<radio id="none" :value="!hasCustomHeaders" text="None" @input="toggleSecurity"/>
						<radio id="custom" :value="hasCustomHeaders" text="Custom" @input="toggleSecurity"/>
					</div>
					<div v-if="hasCustomHeaders" class="tap-gw-headers-data">
						<div v-for="(field, index) in headers" :key="index" class="tap-flex--column">
							<key-value-pair :has-dynamic="false" :has-errors="trigger.hasErrors" :pair-value="field" :validation-regex="headersRegex" @delete="deleteHeader(index)" @input="editHeader($event,index)"/>
						</div>
						<div class="tap-new-field tap-flex mt-10">
							<icon-button :button-styles="['ghost','no-border', 'clean']" :button-text="'Add new'" :icon-name="'tap-plus'" @click="addHeader"/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import FieldGenerator from "@/components/general/FieldGenerator";
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import KeyValuePair from "@/components/general/KeyValuePair";
import Radio from "@/components/general/Radio";
import ReadOnly from "@/components/general/ReadOnly";
import { GenericFields, validationRegex } from "@/utils/constants";
import { generateRandomString, getKeyNicePreview } from "@/utils/data-fn";
import { triggerElementEvent } from "@/utils/ui-fn";
import { mapActions, mapGetters } from "vuex";

export default {
	name: "ReceiveWebhook",
	components: {
		Radio,
		FieldGenerator,
		IconButton,
		ReadOnly,
		KeyValuePair,
		Icon
	},
	props: {
		trigger: {
			type: Object,
			default: () => {
			}
		},
		stepIndex: {
			type: Number,
			default: 0
		},
		triggerIndex: {
			type: Number,
			default: 0
		},
		showErrors: {
			type: Boolean,
			default: false
		}
	},
	emits: [ 'generate-data' ],
	data() {
		return {
			shouldResetData: true,
			listening: false,
			collapsed: true,
			minutes: '00',
			seconds: 59,
			timer: new Date(),
			headersRegex: validationRegex.httpHeaders
		}
	},
	computed: {
		...mapGetters( 'steps', [ 'getCurrentAutomation' ] ),
		...mapGetters( 'generic', [ 'getFilters', 'getPrimaryFields' ] ),
		noFields() {
			return this.showErrors && this.triggerFields.length === 1 && ! this.triggerFields[ 0 ]?.key.length && ! this.triggerFields[ 0 ]?.type.length;
		},
		listenText() {
			return this.listening ? 'Listening' : 'Listen';
		},
		hasCustomHeaders() {
			return this.getCurrentAutomation.meta?.[ `webhook-${this.trigger.webhook_id}` ]?.use_custom_headers
		},
		headers() {
			return this.getCurrentAutomation.meta?.[ `webhook-${this.trigger.webhook_id}` ].security_headers || [ {key: '', value: ''} ];
		},
		triggerFields() {
			const dataFields = this.getCurrentAutomation.meta?.[ `webhook-${this.trigger.webhook_id}` ][ 'fields' ],
				fields = [];

			if ( !! dataFields ) {
				Object.keys( dataFields ).forEach( uuid => {
					fields.push( {
						key: dataFields[ uuid ].field_key,
						type: dataFields[ uuid ].field_type,
						uuid
					} );
				} )
			}

			return fields;
		},
		webhookURL() {
			return `${TAPAdmin.routes}/automator/webhook/${this.trigger.webhook_id}`;
		},
		fields() {
			const data = {
				generic_data: {
					label: 'Generic Data',
					items: {}
				},
				dynamic_mapping: {
					label: 'Dynamic Mapping',
					items: {}
				}
			};
			Object.keys( this.getPrimaryFields ).forEach( key => {
				data.dynamic_mapping.items[ key ] = {
					value: this.getPrimaryFields[ key ].id,
					label: this.getPrimaryFields[ key ].name,
				}
			} )
			Object.keys( GenericFields ).forEach( key => {
				data.generic_data.items[ key ] = {
					value: GenericFields[ key ].id,
					label: GenericFields[ key ].name,
				}
			} )


			return data;
		},
	},
	beforeUnmount() {
		this.stopListen();
	},
	mounted() {
		if ( ! Object.keys( this.getCurrentAutomation.meta?.[ `webhook-${this.trigger.webhook_id}` ]?.[ 'fields' ] || {} )?.length ) {
			this.addField();
		}
	},
	methods: {
		...mapActions( 'steps', [ 'updateCurrentAutomation' ] ),
		...mapActions( 'generic', [ 'listenWebhook' ] ),
		addDeepFields( levelObject, levels = [] ) {
			Object.keys( levelObject ).forEach( key => {
				const data = levelObject[ key ];
				if ( data.is_field ) {
					const keyMapping = ( previousValue, currentValue, currentIndex ) => previousValue + ( currentIndex ? `[${currentValue}]` : currentValue );
					this.addField( [ ...levels, key ].reduce( keyMapping, '' ), GenericFields[ `tap_generic_${data.type}` ] ? `tap_generic_${data.type}` : data.type, data.label )
				} else {
					this.addDeepFields( data, [ ...levels, key ] )
				}
			} )
		},
		addField( key = '', type = '', preview = '' ) {
			this.insertNewField( {
				key: TAPAdmin._.isString( key ) ? key : '',
				type: TAPAdmin._.isString( type ) ? type : '',
				uuid: generateRandomString( 32 ),
				preview: TAPAdmin._.isString( preview ) ? preview : ''
			} );
			this.collapsed = false;
		},
		clearFields() {
			const metaData = TAPAdmin._.cloneDeep( this.getCurrentAutomation?.meta ?? {} );
			TAPAdmin._.set( metaData, [ `webhook-${this.trigger.webhook_id}`, 'fields' ], {} );
			this.updateCurrentAutomation( {key: 'meta', value: metaData} );
		},
		insertNewField( data ) {
			const metaData = TAPAdmin._.cloneDeep( this.getCurrentAutomation?.meta ?? {} );
			let field;

			if ( data.type?.includes( 'tap_generic' ) ) {
				field = {...GenericFields[ data?.type ]};

				if ( data.key ) {
					field.description = field.tooltip = field.tooltip.replaceAll( '$', data.key );
					field.shortcode_tag = `%${data.key}%`;
				}

				field.name = data.preview || getKeyNicePreview( data.key, field.name );
				field.id = `${field.id}_${data.uuid}`;
			}

			TAPAdmin._.set( metaData, [ `webhook-${this.trigger.webhook_id}`, 'fields', data.uuid ], {
				...field,
				...{
					field_type: data.type,
					field_key: data.key,
					uuid: data.uuid
				}
			} );

			this.updateCurrentAutomation( {key: 'meta', value: metaData} );
			if ( this.shouldResetData ) {
				this.$emit( 'generate-data' );
			}
		},
		deleteField( identifier ) {
			const metaData = this.getCurrentAutomation.meta;

			delete metaData[ `webhook-${this.trigger.webhook_id}` ][ 'fields' ][ identifier ];

			this.updateCurrentAutomation( {key: 'meta', value: metaData} );
			this.$emit( 'generate-data' );
		},
		initListen() {
			this.listening = true;
			this.timer = new Date();

			this.listeningInterval = setInterval( () => {
				this.doListen();
			}, 3000 );

			this.listeningTimeout = setTimeout( () => {
				this.stopListen()
			}, 60 * 1000 );
			this.doListen();

			this.timerInterval = setInterval( () => {
				this.seconds --;
				if ( ! this.seconds ) {
					this.stopListen()
				}
			}, 1000 );
		},
		stopListen() {
			clearTimeout( this.listeningTimeout );

			clearInterval( this.timerInterval );

			clearInterval( this.listeningInterval );
			this.seconds = 59;
			this.listening = false;
		},
		doListen() {
			this.listenWebhook( this.trigger.webhook_id ).then( response => {
				if ( ! response.is_listener ) {
					this.stopListen();
					this.insertResponseData( response );
					this.collapsed = false;
				}
			} ).catch( () => {
				this.listening = false;
			} )
		},
		insertResponseData( response ) {
			this.clearFields();
			this.shouldResetData = false;
			Object.keys( response ).forEach( key => {
				this.addField( key, GenericFields[ `tap_generic_${response[ key ]}` ] ? `tap_generic_${response[ key ]}` : '' );
			} );
			this.$emit( 'generate-data' );
			this.shouldResetData = true;
		},
		toggleSecurity( key, checked = true ) {
			const allowCustom = checked && key === 'custom',
				metaData = this.getCurrentAutomation.meta;
			metaData[ `webhook-${this.trigger.webhook_id}` ][ 'use_custom_headers' ] = allowCustom;
			if ( allowCustom ) {
				this.addHeader()
			} else {
				metaData[ `webhook-${this.trigger.webhook_id}` ].security_headers = [];
			}
			this.updateCurrentAutomation( {key: 'meta', value: metaData} );
		},
		addHeader() {
			const metaData = this.getCurrentAutomation.meta;
			metaData[ `webhook-${this.trigger.webhook_id}` ].security_headers = metaData[ `webhook-${this.trigger.webhook_id}` ].security_headers || [];
			metaData[ `webhook-${this.trigger.webhook_id}` ].security_headers.push( {key: '', value: ''} );
			this.updateCurrentAutomation( {key: 'meta', value: metaData} );
		},
		deleteHeader( index ) {
			const metaData = this.getCurrentAutomation.meta;
			metaData[ `webhook-${this.trigger.webhook_id}` ].security_headers.splice( index, 1 );
			if ( ! metaData[ `webhook-${this.trigger.webhook_id}` ].security_headers?.length ) {
				this.toggleSecurity( 'none' );
			}
			this.updateCurrentAutomation( {key: 'meta', value: metaData} );
			this.$emit( 'generate-data' );
		},
		editHeader( value = {key: '', value: ''}, index = 0 ) {
			const metaData = this.getCurrentAutomation.meta;
			metaData[ `webhook-${this.trigger.webhook_id}` ].security_headers[ index ] = value;
			this.updateCurrentAutomation( {key: 'meta', value: metaData} );
			this.$emit( 'generate-data' );
		},
		forbiddenTypes( index ) {
			const genericKeys = Object.keys( GenericFields );
			return this.triggerFields.map( field => field.type ).filter( field => ! genericKeys.includes( field ) && field !== this.triggerFields[ index ].type );
		},
		fieldContentScroll() {
			/**
			 * Close toggle selects on scroll if they are open to prevent the content to jump around
			 *
			 * @type {NodeListOf<HTMLElementTagNameMap[string]> | NodeListOf<Element> | NodeListOf<SVGElementTagNameMap[string]>}
			 */
			const closeButtons = document.querySelectorAll( '.tap-toggle-close' );
			if ( closeButtons.length ) {
				[ ...closeButtons ].forEach( button => {
					triggerElementEvent( button, 'click' );
				} );
			}
		}
	}
}
</script>
