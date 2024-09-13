<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-textarea-field tap-action-field pt-5 pb-5">
		<div class="tap-container-heading">
			{{ fieldData.name }}
		</div>
		<textarea :placeholder="fieldData?.placeholder" :value="fieldValue" class="tap-fw tap-textarea" @input="onInput"/>
		<div v-if="hasErrors && !fieldValidation?.isValid" class="tap-filter-error">
			{{ fieldValidation?.message }}
		</div>
	</div>
</template>

<script>
import TextField from "@/components/fields/TextField.vue";

export default {
	name: "TextareaField",
	mixins: [ TextField ],
	methods: {
		onInput( event ) {
			this.changeProp( event.target.value );
		}
	}
}
</script>
