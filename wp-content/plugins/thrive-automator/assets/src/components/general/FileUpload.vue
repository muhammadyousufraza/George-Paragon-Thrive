<template>
	<div class="tap-file-upload-wrapper p-10">
		<div class="tap-file-upload-dropzone tap-flex--column" @dragenter.prevent="setActive" @dragover.prevent="setActive" @dragleave.prevent="setInactive" @drop.prevent="onDrop">
			<label v-if="!files.length" for="file-input">
				<span v-if="dropZoneActive">
					<span>Drop them here </span>
					<span class="smaller">to add them</span>
				</span>
				<span v-else>
					<a>Choose file</a>
					<span class="smaller">
						or Drag & Drop here
					</span>
				</span>
				<input v-if="multiple" id="file-input" :accept="accept" type="file" multiple @change="onInputChange">
				<input v-else id="file-input" :accept="accept" type="file" @change="onInputChange">
			</label>
			<ul v-else class="tap-file-upload-preview tap-fw m-0">
				<li v-for="(file,fileIndex) in files" :key="fileIndex" class="p-5 m-0 tap-flex--between">
					<span>{{ file.name }}</span>
					<icon icon-name="tap-cross" @click="removeFile(file)"/>
				</li>
			</ul>
			<div v-if="confirmationMessage && files.length" class="tap-upload-confirmation tap-flex">
				<icon v-if="confirmationIcon" :icon-name="confirmationIcon"/>
				<span>{{ confirmationMessage }}</span>
			</div>
			<div v-if="invalidFiles" class="tap-upload-confirmation tap-flex">
				<icon icon-name="tap-cross"/>
				<span>{{ invalidFiles }}</span>
			</div>

			<icon-button v-if="buttonText" :button-text="buttonText" :icon-name="buttonIcon" :button-styles="buttonStyles" :class="[buttonDisabled?'tap-disabled':'', 'p-5','mt-10']" @click="onUpload"/>
		</div>
	</div>
</template>

<script>
import IconButton from "@/components/general/IconButton";
import Icon from "@/components/general/Icon";
import { fileTypes } from "@/utils/constants";

const events = [ 'dragenter', 'dragover', 'dragleave', 'drop' ],
	preventDefaults = e => e.preventDefault();

class UploadableFile {
	constructor( file ) {
		this.file = file
		this.id = `${file.name}-${file.size}-${file.lastModified}-${file.type}`
		this.name = `${file.name}`
		this.url = URL.createObjectURL( file )
		this.status = null
	}
}

export default {
	name: "FileUpload",
	components: {
		Icon,
		IconButton,
	},
	props: {
		filename: {
			type: String,
			default: () => '',
		},
		buttonText: {
			type: String,
			default: () => 'Upload'
		},
		buttonIcon: {
			type: String,
			default: () => ''
		},
		buttonStyles: {
			type: Array,
			default: () => []
		},
		accept: {
			type: String,
			default: () => 'image/*'
		},
		multiple: {
			type: Boolean,
			default: () => false
		},
		preventUpload: {
			type: Boolean,
			default: () => false
		},
		confirmationMessage: {
			type: String,
			default: () => '',
		},
		confirmationIcon: {
			type: String,
			default: () => '',
		},
	},
	emits: [ 'upload', 'filesAdded' ],
	data() {
		return {
			dropZoneActive: false,
			activeTimeout: null,
			files: [],
			invalidFiles: '',
		}
	},
	computed: {
		buttonDisabled() {
			return this.preventUpload || this.files.length === 0;
		},
	},
	beforeMount() {
		events.forEach( eventName => {
			document.body.addEventListener( eventName, preventDefaults )
		} )
	},
	beforeUnmount() {
		events.forEach( eventName => {
			document.body.removeEventListener( eventName, preventDefaults )
		} )
	},
	methods: {
		addFiles( newFiles ) {
			newFiles = [ ...newFiles ];
			if ( ! this.multiple ) {
				newFiles.splice( 1 )
			}

			const initialLength = newFiles.length;

			newFiles = newFiles.filter( file => ! ( this.accept && ! fileTypes[ this.accept ].includes( file.type ) ) )
			this.invalidFiles = '';
			if ( newFiles.length !== initialLength ) {
				this.invalidFiles = `${initialLength - newFiles.length} file(s) were not added because they are not of type ${this.accept}`
			}

			this.setInactive();
			if ( newFiles.length ) {
				const newUploadableFiles = newFiles.map( file => new UploadableFile( file ) ).filter( file => ! this.fileExists( file.id ) )
				this.files = [ ...this.files, ...newUploadableFiles ];

				this.$emit( 'filesAdded', this.files );
			}
		},
		onDrop( event ) {
			if ( ! this.multiple && this.files.length ) {
				return;
			}
			this.addFiles( event.dataTransfer.files );
		},
		fileExists( otherId ) {
			return this.files.some( ( {id} ) => id === otherId )
		},
		removeFile( file ) {
			const index = this.files.indexOf( file )

			if ( index > - 1 ) {
				this.files.splice( index, 1 )
			}

			this.invalidFiles = '';
		},
		onInputChange( event ) {
			this.addFiles( event.target.files );
			event.target.value = null
		},
		setActive() {
			this.dropZoneActive = true;
			clearTimeout( this.activeTimeout );
		},
		setInactive() {
			this.activeTimeout = setTimeout( () => {
				this.dropZoneActive = false;
			}, 50 );
		},
		onUpload() {
			this.$emit( 'upload', this.files );
		}
	}
}
</script>
