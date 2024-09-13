<template>
	<teleport to="body">
		<div class="tap-suite-integration-overlay tap-scrollbar">
			<div class="tap-suite-integration-wrapper tap-flex--column">
				<div v-if="showSuccess" class="tap-connection-success-wrapper tap-flex--column">
					<p>Success!</p>
					<icon icon-name="tap-success-box"/>
					<h3 class="mt-25 mb-25">
						You now have connected Automator to Thrive Product Manager!
					</h3>
					<icon-button button-text="Continue" @click="skipSuite"/>
				</div>
				<div v-else class="tap-flex--column tap-fw">
					<h3>Welcome to Thrive Automator</h3>
					<icon-button :button-styles="['ghost', 'grey','no-border', 'clean']" button-text="Skip for now" icon-name="tap-cross" icon-position="right" @click="skipEnroll"/>
					<div v-if="!showConnection" class="tap-suite-comparison tap-flex--end tap-flex--column p-35">
						<suite-header :class="['tap-fw','tap-flex--between']" :is-splash="true" @button-click="showConnection=true"/>
						<suite-comparison/>
						<div class="tap-comparison-footer tap-flex mt-25 tap-fw">
							<div class="tap-separator tap-fw"/>
							<icon-button :class="['tap-suite-connect', 'ml-20 mr-20']" button-text="Connect now for FREE" @click="showConnection=true"/>
							<div class="tap-separator tap-fw"/>
						</div>
					</div>
					<div v-if="showConnection" class="tap-suite-integration-info-wrapper tap-flex--end tap-flex--column">
						<suite-header :show-connect-button="false"/>
						<suite-arrows :current-step="currentStep"/>
						<div v-if="currentStep===1" class="tap-suite-step p-15 tap-get-tpm-step tap-flex--column tap-flex--start">
							<h3>Let's start:<span> Download & Install Thrive Product Manager</span></h3>
							<div class="tap-tpm-install p-5 tap-flex--between mt-20">
								<div class="tap-tpm-get tap-flex--column p-20">
									<icon icon-name="tap-plugin-download"/>
									<h4 class="m-0 mt-5">
										Download
									</h4>
									<h4 class="m-0">
										Thrive Product Manager
									</h4>
									<a :href="tpmLink" class="tap-download-link p-10 mt-20" target="_blank">Click here to Download</a>
								</div>
								<div class="tap-tpm-upload tap-flex--column p-20">
									<icon icon-name="tap-plugin-upload"/>
									<h4 class="m-5">
										Upload it here
									</h4>
									<file-upload :button-styles="uploadButtonStyles" :button-text="uploadText" :confirmation-icon="uploadIcon" :confirmation-message="uploadMessage" :prevent-upload="preventUpload" accept="application/zip" @upload="installArchive" @files-added="verifyArchive"/>
								</div>
							</div>
						</div>
						<div v-if="currentStep===2" class="tap-suite-step p-15 tap-flex--column tap-flex--start">
							<h3>Almost there!<span> Activate Thrive Product Manager</span></h3>
							<div class="tap-tpm-activate p-5 tap-flex--column mt-20">
								<icon icon-name="tap-plugin-activate"/>
								<h4 class="m-0 mt-5">
									Activate
								</h4>
								<h4 class="m-0">
									Thrive Product Manager
								</h4>
								<a :class="{'tap-activating': duringActivation}" class="tap-activate-link p-10 m-20 tap-flex" @click="activateTpm">{{ activationMessage }}</a>
							</div>
						</div>
						<div v-if="currentStep===3" class="tap-suite-connect tap-suite-step p-15 tap-flex--column">
							<h3>Last Step, Yay!<span> Connect your account:</span></h3>
							<div class="tap-tpm-connect-form tap-flex--column">
								<h3>
									<span> Create or Connect your Thrive Account for FREE</span>
								</h3>
								<custom-form :button-text="formButtonText" :consent-message="consentMessage" :has-consent="hasConsent" :inputs="formInputs" :show-form-loader="showFormLoader" @input="handleInput" @submit="handleRegister" @change-consent="hasConsent=!hasConsent"/>
								<div class="tap-suite-go-to-login tap-flex">
									<p>Already have a Thrive Themes account?</p>
									<login-popup ref="loginTrigger" @loggedin="successLogin"/>
								</div>
							</div>
						</div>
					</div>

					<icon-button :button-styles="['ghost', 'grey','no-border', 'clean']" button-text="Skip for now" icon-name="tap-cross" icon-position="right" @click="skipEnroll"/>
					<suite-confirm v-if="showConfirmation" @cancel="cancelSkip" @confirm="goToSaved"/>
				</div>
			</div>
		</div>
	</teleport>
</template>

<script>
import CustomForm from "@/components/general/CustomForm";
import IconButton from "@/components/general/IconButton";
import Icon from "@/components/general/Icon";
import SuiteArrows from "@/components/ttw/SuiteArrows";
import FileUpload from "@/components/general/FileUpload";
import SuiteHeader from "@/components/ttw/SuiteHeader";
import SuiteConfirm from "@/components/ttw/SuiteConfirm";
import ApiRequest from "@/utils/ApiRequest";
import SuiteComparison from "@/components/ttw/SuiteComparison";
import { tapToast, toggleBodyOverflow } from "@/utils/ui-fn";
import LoginPopup from "@/components/ttw/LoginPopup";
import { mapActions } from "vuex";

export default {
	name: "SuiteIntegration",
	components: {
		LoginPopup,
		SuiteComparison,
		SuiteConfirm,
		SuiteHeader,
		FileUpload,
		SuiteArrows,
		Icon,
		CustomForm,
		IconButton
	},
	data() {
		return {
			uploadButtonStyles: [],
			showSuccess: false,
			hasConsent: false,
			currentStep: 1,
			showConnection: false,
			duringActivation: false,
			activationMessage: 'Click here to Activate',
			showConfirmation: false,
			preventUpload: false,
			showFormLoader: false,
			uploadText: 'Install',
			uploadIcon: '',
			uploadMessage: '',
			formInputs: {
				name: {
					key: 'name',
					placeholder: 'Name',
					type: 'text',
					label: 'Name',
					validators: [ 'required' ],
					position: 'top',
					autocomplete: 'name',
					validation: {
						isValid: false,
						message: 'Name can not be empty'
					}
				},
				email: {
					key: 'email',
					placeholder: 'Email',
					type: 'email',
					label: 'Email',
					validators: [ 'required', 'email' ],
					position: 'top',
					autocomplete: 'email',
					validation: {
						isValid: false,
						message: 'Email can not be empty'
					}

				},
				password:
					{
						key: 'password',
						placeholder: 'Password',
						type: 'password',
						label: 'Password',
						validators: [ 'required', 'password' ],
						position: 'top',
						autocomplete: 'new-password',
						validation: {
							isValid: false,
							message: 'Password can not be empty'
						}
					},
				confirm_password: {
					key: 'confirm_password',
					placeholder: 'Confirm password',
					type: 'password',
					label: 'Confirm password',
					validators: [ 'required', 'password' ],
					position: 'top',
					autocomplete: 'new-password',
					validation: {
						isValid: false,
						message: 'Confirm password can not be empty'
					}
				}
			}
		}
	},
	computed: {
		tpmLink() {
			return TAPAdmin.ttw.download_link;
		},
		consentMessage() {
			return `I accept the <a href="${TAPAdmin.urls.term_cond}" target="_blank">terms & conditions</a> and agree to receive news and product related emails from Thrive Themes.`
		},
		formButtonText() {
			return 'Create my Thrive Themes Account';
		},
	},
	mounted() {
		if ( ! TAPAdmin.ttw.installed ) {
			this.currentStep = 1;
		} else if ( ! TAPAdmin.ttw.active ) {
			this.currentStep = 2;
		} else if ( ! TAPAdmin.ttw.connected ) {
			this.currentStep = 3;
		} else {
			this.goToSaved();
		}
		toggleBodyOverflow();
		this.showConnection = this.currentStep > 1;
	},
	methods: {
		...mapActions( 'suite', [ 'setInstalled', 'setActive', 'setConnected' ] ),
		handleRegister() {
			this.showFormLoader = true;
			ApiRequest.wpFetchRequest( {
				path: `${TAPAdmin.routes}/register`,
				method: 'POST',
				body: {
					email: this.formInputs.email.value,
					password: this.formInputs.password.value,
					name: this.formInputs.name.value,
				}
			} ).then( () => {
				this.successLogin();
			} ).catch( errorData => {
				tapToast( errorData.error );
			} ).finally( () => {
				this.showFormLoader = false;
			} );
		},
		verifyArchive( files ) {
			const formData = this.getFormData( files );
			this.preventUpload = true;
			this.uploadMessage = '';
			this.uploadText = 'Checking archive';
			this.uploadButtonStyles = [ 'loading' ];
			ApiRequest.genericFetch( {
				path: `${TAPAdmin.routes}/verify_plugin`,
				method: 'POST',
				body: formData
			} ).then( () => {
				this.preventUpload = false;
				this.uploadMessage = 'File uploaded and checked';
				this.uploadIcon = 'tap-check';
			} ).catch( errorObj => {
				this.uploadMessage = errorObj.data.error;
				this.uploadIcon = 'tap-cross';
			} ).finally( () => {
				this.uploadText = 'Install';
				this.uploadButtonStyles = [];
			} );
		},
		installArchive( files ) {
			const formData = this.getFormData( files );
			this.preventUpload = true;
			this.uploadText = 'Installing...';
			this.uploadButtonStyles = [ 'loading' ];
			ApiRequest.genericFetch( {
				path: `${TAPAdmin.routes}/upload_plugin`,
				method: 'POST',
				body: formData
			} ).then( () => {
				this.setInstalled( true )
				TAPAdmin.ttw.installed = true;
				this.currentStep = 2;
			} ).catch( errorObj => {
				tapToast( errorObj.data.error );
			} ).finally( () => {
				this.preventUpload = false;
				this.uploadMessage = '';
				this.uploadButtonStyles = [];
			} );
		},
		activateTpm() {
			this.duringActivation = true;
			this.activationMessage = 'Activating...';
			ApiRequest.genericFetch( {
				path: `${TAPAdmin.routes}/activate_plugin?zip_upload_nonce=${TAPAdmin.file_nonce}`,
			} ).then( data => {
				this.setActive( true );
				TAPAdmin.ttw = {
					...TAPAdmin.ttw,
					...data.ttw,
				}
				if ( TAPAdmin.ttw.connected ) {
					this.successLogin();
				} else {
					this.currentStep = 3;
				}
			} ).catch( errorObj => {
				tapToast( errorObj.data.error );
			} ).finally( () => {
				this.activationMessage = 'Activate';
				this.duringActivation = false;
			} );
		},
		getFormData( files ) {
			const formData = new FormData();
			formData.append( 'file', files[ 0 ].file );
			formData.append( 'zip_upload_nonce', TAPAdmin.file_nonce );
			return formData;
		},
		goToSaved() {
			toggleBodyOverflow( false )
			this.$router.push( {path: '/saved'} ).then( () => {
				location.reload();
			} );
		},
		skipSuite() {
			this.setConnected( true );
			this.goToSaved();
		},
		skipEnroll() {
			toggleBodyOverflow( false );
			this.showConfirmation = true;
		},
		cancelSkip() {
			toggleBodyOverflow();
			this.showConfirmation = false;
		},
		handleInput( inputData ) {
			this.formInputs[ inputData.key ] = {
				...this.formInputs[ inputData.key ],
				...inputData
			};
		},
		successLogin() {
			TAPAdmin.ttw.connected = true;
			this.showSuccess = true;
		}
	},
}
</script>
