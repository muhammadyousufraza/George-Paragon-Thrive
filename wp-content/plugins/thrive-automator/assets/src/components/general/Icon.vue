<template>
	<div v-tooltip="{
         content: tooltip || '' ,
         theme: tooltip? 'automator':'tooltip',
         offset: [0, 15],
         delay:{
           show:showDelay,
         }
       }"
		 :class="classes">
		<svg class="tap-icon">
			<use :xlink:href="'#'+iconName"/>
		</svg>
	</div>
</template>
<script>
export default {
	name: 'Icon',
	props: {
		iconName: {
			type: String,
			default: () => '',
		},
		wrapperClasses: {
			type: String,
			default: () => '',
		},
		tooltip: {
			type: String,
			default: () => '',
		},
		showDelay: {
			type: Number,
			default: () => 0,
		}
	},
	computed: {
		classes() {
			return `tap-icon-wrapper tap-icon-${this.iconName.replace( 'tap-', '' )} ${this.wrapperClasses}`.trim()
		}
	}
}
</script>


