var base = require( '../views/base' );

module.exports = base.extend( {

	initialize: function () {
		base.prototype.initialize.apply( this, arguments );

		this.$( 'input' ).attr( 'data-value', this.model.get( 'traffic' ) );

		this.listenTo( this.model, 'change:traffic', function ( model, _value ) {
			this.$( 'input' ).val( _value );
			this.$( 'input' ).attr( 'data-value', _value );
		} );

		if ( this.model.collection.length === 1 ) {
			this.disable();
		}
	},

	on_change: function ( event ) {

		this.model.collection.save_distributed_traffic();

		return false;
	},

	on_input: function ( event ) {

		var _value = event.target.value;

		if ( _value.length <= 0 ) {
			event.target.value = 0;
		}

		if ( isNaN( parseInt( _value ) ) || parseInt( _value ) > 100 ) {
			_value = _value.substring( 0, _value.length - 1 );
		}

		if ( isNaN( parseInt( _value ) ) ) {
			_value = 0;
		}

		event.target.value = parseInt( _value );

		var _diff = parseInt( event.target.dataset.value ) - _value;
		_diff = parseInt(_diff);

		this.set_traffic( parseInt( event.target.value ), _diff );

		return false;
	},

	set_traffic: function ( value, diff ) {

		var _value = parseInt( value );

		if ( isNaN( _value ) ) {
			_value = 0;
		}

		if ( _value < 0 ) {
			_value = 0;
		}

		if ( _value > 100 ) {
			_value = 100;
		}

		this.model.set( 'traffic', _value );

		//this.model.collection.distribute_traffic( this.model );
		var traffic_alocated = this.model.collection.allocate_traffic( this.model, diff );
	},

	enable: function () {
		this.$( 'input' ).removeAttr( 'disabled' );
	},

	disable: function () {
		this.$( 'input' ).attr( 'disabled', 'disabled' );
	}
} );
