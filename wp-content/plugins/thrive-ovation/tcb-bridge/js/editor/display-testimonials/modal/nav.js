const Select = require( './select' );

module.exports = TVE.Views.Base.base_view.extend( {
	afterInitialize( attr ) {
		this.selectedValue = attr.selectedValue;
		this.dataModel = attr.dataModel;
		this.selectCallback = attr.selectCallback;
		this.setCollection = attr.setCollection;

		( new Select( {
			el: this.$el[ 0 ],
			model: new Backbone.Model( {
				list: [
					{ label: 'Specific testimonials', value: 'static' },
					{ label: 'Dynamic list', value: 'dynamic' },
					{ label: 'Existing display set', value: 'set', disabled: 1 },
					...this.parseSetOptions(),
				],
				selected: this.selectedValue,
			} ),
			onSelect: value => {
				this.selectCallback();

				if ( isNaN( value ) ) {
					this.dataModel.setPage( value );
				} else {
					this.dataModel.viewSet( this.setCollection.get( value ) );
				}
			},
		} ) );
	},
	parseSetOptions() {
		return this.setCollection.map( set => ( {
			value: set.get( 'id' ),
			label: set.get( 'label' ),
			type: set.getType(),
			beforeContent: `<span class="icon ${ set.getType() }"></span>`,
			isSubOption: true,
		} ) );
	},
} );
