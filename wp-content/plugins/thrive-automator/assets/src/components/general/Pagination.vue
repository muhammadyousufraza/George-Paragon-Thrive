<template>
	<div
		v-if="lastPage>1"
		class="tap-pagination">
		<div
			v-tooltip="{
        content: 'First page',
        theme: 'automator',
        offset: [0, 10],
      }"
			class="tap-pag-item"
			@click="emitValue(1)">
			<icon :icon-name="'tap-angle-double-left'"/>
		</div>
		<div
			v-if="currentPage>1"
			v-tooltip="{
        content: 'Previous page',
        theme: 'automator',
        offset: [0, 10],
      }"
			class="tap-pag-item"
			@click="emitValue(currentPage-1)">
			<icon :icon-name="'tap-angle-left'"/>
		</div>
		<div
			v-for="(content,index) in uiValues"
			:key="index"
			:class="{'tap-disabled': content==='...', active: content===currentPage}"
			class="tap-pag-item"
			@click="emitValue(content)">
			{{ content }}
		</div>
		<div
			v-if="currentPage<lastPage"
			v-tooltip="{
        content: 'Next page',
        theme: 'automator',
        offset: [0, 10],
      }"
			class="tap-pag-item"
			@click="emitValue(currentPage+1)">
			<icon :icon-name="'tap-angle-right'"/>
		</div>
		<div
			v-tooltip="{
        content: 'Last page',
        theme: 'automator',
        offset: [0, 10],
      }"
			class="tap-pag-item"
			@click="emitValue(lastPage)">
			<icon :icon-name="'tap-angle-double-right'"/>
		</div>
	</div>
</template>

<script>
import Icon from "@/components/general/Icon";

export default {
	name: "Pagination",
	components: {
		Icon
	},
	props: {
		currentPage: {
			type: Number,
			default: () => 1,
		},
		rows: {
			type: Number,
			default: () => 10,
		},
		totalCount: {
			type: Number,
			default: () => 0,
		}
	},
	emits: [ 'newPage' ],
	computed: {
		lastPage() {
			let lastPage = parseInt( this.totalCount / this.rows ) || 0;

			if ( this.totalCount % this.rows ) {
				lastPage ++;
			}

			return lastPage;
		},
		uiValues() {
			const data = [];
			let renderDots = true;
			for ( let page = 1; page <= this.lastPage; page ++ ) {
				if ( page === 1 || ( ( page >= this.currentPage - 1 ) && ( page <= ( this.currentPage + 1 ) ) ) || page === this.lastPage ) {
					data.push( page );
					renderDots = true;
				} else {
					if ( ! renderDots ) {
						continue;
					}
					data.push( '...' );
					renderDots = false;
				}
			}
			return data;
		}
	},
	methods: {
		emitValue( value ) {
			if ( value && value !== this.currentPage ) {
				this.$emit( 'newPage', value );
			}
		}
	}
}
</script>


