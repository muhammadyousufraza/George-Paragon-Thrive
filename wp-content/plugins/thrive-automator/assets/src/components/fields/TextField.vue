<template>
	<div :class="{'tap-error': hasErrors && !fieldValidation?.isValid }" class="tap-input-field tap-action-field pt-5 pb-5">
		<div class="tap-container-heading">
			{{ fieldData.name }}
		</div>
		<input-field :dynamic-data-fields="dynamicDataFields(stepIndex-1)" :has-dynamic-data="true" :placeholder="fieldData?.placeholder" :value="fieldValue" @input="changeProp"/>
		<div v-if="hasErrors && !fieldValidation?.isValid" class="tap-filter-error">
			{{ fieldValidation?.message }}
		</div>
	</div>
</template>

<script>
import GenericField from "@/components/fields/GenericField";
import InputField from "@/components/general/InputField";
import { mapGetters } from "vuex";

export default {
	name: "TextField",
	components: {
		InputField
	},
	mixins: [ GenericField ],
	computed: {
		...mapGetters( 'steps', [ 'dynamicDataFields' ] ),
	}
}
</script>


