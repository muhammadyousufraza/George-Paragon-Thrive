<template>
	<div class="tap-datepicker tap-flex">
		<datepicker v-model="pickedDate" :input-format="'yyyy/MM/dd'"/>
		<icon icon-name="tap-calendar" @click="openCalendar"/>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";
import { ref } from 'vue'
import Datepicker from 'vue3-datepicker'

export default {
	name: "DatePicker",
	components: {
		Icon,
		Datepicker
	},
	props: {
		value: {
			type: String,
			default: () => ''
		}
	},
	data() {
		return {
			pickedDate: ref( this.value ? new Date( this.value ) : new Date() )
		}
	},
	watch: {
		pickedDate() {
			this.$emit( 'input', this.pickedDate )
		},
		value() {
			if ( this.value ) {
				this.pickedDate = ref( new Date( this.value ) );
			}
		}
	},
	created() {
		this.$emit( 'input', this.pickedDate )
	},
	methods: {
		openCalendar() {
			this.$el.getElementsByTagName( 'input' )?.[ 0 ]?.click();
			return false;
		}
	}
}
</script>


