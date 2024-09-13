<template>
	<div class="tap-error-item">
		<div class="tap-col--15">
			{{ automationName }}
		</div>
		<div class="tap-col--10">
			{{ content.date_started }}
		</div>
		<div class="tap-col--70">
			<div v-for="(data,index) in errorMessages" :key="index" class="tap-error-sub-item">
				<div class="tap-error-tags tap-flex--start">
					<div class="tap-error-app">
						{{ getPrettyLabel( data.label ) }}
					</div>
					<div :class="{'tap-error-success': data.is_success}" class="tap-error-type">
						{{ getLabelByType( data.type ) }}
					</div>
				</div>
				<div class="tap-error-message">
					{{ data.message }}
				</div>
			</div>
		</div>
		<div class="tap-col--3 tap-flex--between">
			<Icon :icon-name="'tap-external-link'" :tooltip="'See raw data'" @click="$emit('displayError',content.id)"/>
			<Icon :icon-name="'tap-trash'" :tooltip="'Delete item'" @click="showDeleteConfirmation=true"/>
		</div>
		<inline-delete v-if="showDeleteConfirmation" :classes="'tap-row'" :message="'Are you sure you want to delete this error log?'" @cancel="showDeleteConfirmation = !showDeleteConfirmation" @confirm="triggerDelete(content.id)"/>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import InlineDelete from "@/components/general/InlineDelete";
import { ucFirst } from "@/utils/data-fn";
import { mapActions, mapGetters } from "vuex";

const typeLabels = {
	'data-not-provided-to-action': 'No data provided to action',
	'data-not-provided-to-filter': 'No data provided to filter',
	'no-param-on-data-create': 'No params provided on create',
	'no-data-object-created': 'Object not created',
	'action-success': 'Action executed',
	'data-filter-not-valid': 'No valid value',
	'data-webhook': 'Webhook executed',
	'limitation-reject': 'Trigger did not pass execution limitations',
	'data-webhook-fail': 'Webhook failed to execute'
};

export default {
	name: "Error",
	components: {
		Icon,
		InlineDelete
	},
	props: {
		content: {
			type: Object,
			default: () => {
			}
		}
	},
	emits: [ 'afterDelete', 'displayError' ],
	data() {
		return {
			showDeleteConfirmation: false,
		};
	},
	computed: {
		...mapGetters( 'automations', [ 'getAutomations' ] ),
		automationName() {
			let name = '';

			if ( this.errorMessages.length === 1 && this.errorMessages[ 0 ].type.includes( 'data-webhook' ) ) {
				name = 'Webhook request';
			} else {
				name = this.getAutomations.filter( aut => Number( aut.id ) === Number( this.content.automation_id ) )?.[ 0 ]?.title;
			}

			return name || 'Automation deleted';
		},
		errorMessages() {
			let errors = [];
			Object.keys( this.content.error ).forEach( key => {
				errors = [ ...errors, ...Object.keys( this.content.error[ key ] ).map( errorKey => {
					return {
						type: errorKey,
						...this.content.error[ key ][ errorKey ]
					}
				} ) ]
			} )
			return errors;
		}

	},
	methods: {
		...mapActions( 'errors', [ 'deleteError' ] ),
		getLabelByType( type ) {
			return typeLabels[ type ];
		},
		getPrettyLabel( label ) {
			return label?.replaceAll( '_', ' ' ).split( ' ' ).map( part => ucFirst( part ) ).join( ' ' );
		},
		triggerDelete( id ) {
			this.deleteError( id );
			this.showDeleteConfirmation = false;
			this.$emit( 'afterDelete' )
		}
	}
}
</script>


