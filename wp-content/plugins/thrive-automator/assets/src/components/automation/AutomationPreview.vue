<template>
	<div :class="{ 'tap-loader--automation': showLoader,'tap-aut-published':isPublished}" class="tap-aut-preview-container tap-flex" @click.self="enterEditMode">
		<div class="tap-col--2">
			<checkbox :value="checked" @input="$emit('aut-check', aut.id)"/>
		</div>
		<div class="tap-col--28">
			<div class="tap-aut-name">
				<inline-input :placeholder="'Automation title'" :value="aut.title" @blur="updateName" @input-editing="toggleEditName"/>
			</div>
		</div>
		<div class="tap-col--20 nowrap" @click="enterEditMode">
			<div class="tap-flex--wrap tap-flex--start">
				<div v-for="(trigger,index) in autTriggers"
					 :key="index"
					 class="tap-aut-item-preview tap-flex">
					<div
						v-tooltip="{
              content: `${trigger.count} ${trigger.app_name} trigger${trigger.count >1 ? 's':''}`,
              theme: 'automator',
              offset: [0, 10],
            }"
						class="tap-aut-trigger">
						<logo :value="trigger.image"/>
						<div class="tap-aut-count">
							{{ trigger.count }}
						</div>
					</div>
					<icon icon-name="tap-plus"/>
				</div>
			</div>
			<div class="tap-fake-arrow">
				<div class="tap-line"/>
				<div class="tap-arrow-right"/>
			</div>
		</div>
		<div class="tap-col--40 tap-aut-saved-actions" @click="enterEditMode">
			<div v-for="(action,index) in autActions"
				 :key="index"
				 v-tooltip="{
             content: `${action.tooltip}`,
             theme: 'automator',
             offset: [0, 10],
           }"
				 class="tap-aut-action">
				<logo :value="action.icon"/>
				<icon :icon-name="'tap-move-right'"/>
			</div>
		</div>
		<div class="tap-col--10 tap-flex--between tap-aut-functions">
			<div class="tap-auto-status">
				<icon-button :button-styles="['status',isPublished ? 'active': 'inactive']" :button-text="isPublished ? 'Active' : 'Inactive'" @click="toggleStatus"/>
			</div>
			<div class="tap-auto-actions">
				<Dropdown :offset="[0,15]" :placement="'bottom-end'" :popper-hide-triggers="triggers => [...triggers, 'click']" :theme="'automator_dropdown'">
					<icon :icon-name="'tap-three-dots'"/>
					<template #popper>
						<div class="tap-actions-popover">
							<icon-button :button-styles="['ghost','no-border','clean']" :button-text="'Edit'" :icon-name="'tap-pen'" @click="enterEditMode"/>
							<hr>
							<icon-button :button-styles="['ghost','no-border','clean']" button-text="Duplicate" icon-name="tap-clipboard" @click="duplicateAutomation(aut.id)"/>
							<hr>
							<icon-button :button-styles="['ghost','no-border','delete','clean']" :button-text="'Delete'" :icon-name="'tap-trash'" @click="showDeleteConfirmation=true"/>
						</div>
					</template>
				</Dropdown>
			</div>
		</div>
		<inline-delete v-if="showDeleteConfirmation" :classes="'tap-row'" :message="'Are you sure you want to delete this automation?'" @cancel="showDeleteConfirmation = !showDeleteConfirmation" @confirm="deleteAutomation(aut.id)"/>
	</div>
</template>

<script>
import Checkbox from "@/components/general/Checkbox";
import Icon from "@/components/general/Icon";
import IconButton from "@/components/general/IconButton";
import InlineDelete from "@/components/general/InlineDelete";
import InlineInput from "@/components/general/InlineInput";
import Logo from "@/components/general/Logo";
import { ucFirst } from "@/utils/data-fn";
import { tapToast } from "@/utils/ui-fn";
import { Dropdown } from 'v-tooltip';
import { mapActions, mapGetters } from "vuex";

export default {
	name: "AutomationPreview",
	components: {
		Logo,
		InlineInput,
		IconButton,
		Icon,
		Dropdown,
		Checkbox,
		InlineDelete
	},
	props: {
		aut: {
			type: Object,
			default: () => {
			},
		},
		checked: {
			type: Boolean,
			default: () => false
		}
	},
	data() {
		return {
			showLoader: false,
			duringNameChange: false,
			showDeleteConfirmation: false,
		};
	},
	computed: {
		...mapGetters( 'generic', [ 'getApps' ] ),
		isPublished() {
			return this.aut.status === 'publish';
		},
		autActions() {
			const data = [];
			let i = 1;
			while ( i < this.aut.content.length ) {
				if ( this.aut.content[ i ].type === 'delay' ) {
					data.push( {
						tooltip: 'Delay',
						icon: 'tap-delay'
					} );
				} else if ( this.aut.content[ i ].type === 'filters' ) {
					data.push( {
						tooltip: 'Filter',
						icon: 'tap-filter'
					} );
				} else {
					const actionData = Object.values( this.aut.content[ i ].data )?.[ 0 ];
					data.push( {
						tooltip: actionData.name || 'Action',
						icon: actionData.image || actionData.logo || 'tap-cogs'
					} );
				}
				i ++;
			}

			return data;
		},
		autTriggers() {
			const triggers = this.aut.content[ 0 ].data,
				data = {};

			Object.keys( triggers ).forEach( key => {
				const triggerLogo = triggers[ key ].image || triggers[ key ].logo;
				let appLogo = triggerLogo,
					appName = triggers[ key ]?.app_name;
				if ( triggers[ key ]?.app_id ) {
					appName = this.getApps[ triggers[ key ]?.app_id ]?.name;
					if ( ! appName ) {
						appName = triggers[ key ]?.app_id.replaceAll( '_', ' ' ).split( ' ' ).map( part => ucFirst( part ) ).join( ' ' );
					}
					appLogo = this.getApps[ triggers[ key ].app_id ]?.logo;
				}

				let count = data[ appName ]?.count;
				if ( ! count ) {
					count = 0;
				}
				count ++;
				data[ appName ] = {
					app_name: appName,
					count,
					image: count === 1 ? triggerLogo : appLogo
				}
			} )
			return data;
		}
	},
	watch: {
		// whenever question changes, this function will run
		aut() {
			this.showLoader = false;
		}
	},
	methods: {
		...mapActions( 'steps', [ 'updateAutomation', 'deleteAutomation', 'duplicateAutomation', 'editAutomation' ] ),
		...mapActions( 'triggers', [ 'fetchLimitations' ] ),
		toggleEditName( value ) {
			if ( value ) {
				this.duringNameChange = true;
			} else {
				setTimeout( () => {
					this.duringNameChange = false;
				}, 1000 );
			}
		},
		enterEditMode() {
			if ( ! this.duringNameChange ) {
				if ( this.aut.is_valid ) {
					this.$router.push( {path: `/automation/${this.aut.id}`} )
				} else {
					tapToast( 'This automation is not valid, there are some missing triggers or actions.' );
				}
			}
		},
		toggleStatus() {
			this.showLoader = true;
			this.updateAutomation( {
				id: this.aut.id,
				status: this.isPublished ? 'draft' : 'publish'
			} );
		},
		updateName( value ) {
			this.showLoader = true;
			this.updateAutomation( {
				id: this.aut.id,
				status: this.aut.status,
				title: value,
			} );
		}
	},
}
</script>
