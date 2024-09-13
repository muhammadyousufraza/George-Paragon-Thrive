var traffic_model = require( '../models/traffic' );

module.exports = Backbone.Collection.extend( {


	/**
	 * Based on the excluded_model traffic
	 * Distributes the remaining traffic equally to the other models in collection
	 *
	 * @param excluded_model
	 */
	distribute_traffic: function ( excluded_model ) {

		if ( this.length <= 1 ) {
			return;
		}

		var _new_traffic = parseInt( (100 - excluded_model.get( 'traffic' )) / (this.length - 1) );

		this.each( function ( model ) {
			if ( model.get( model.idAttribute ) !== excluded_model.get( excluded_model.idAttribute ) ) {
				model.set( 'traffic', _new_traffic );
			}
		}, this );

		var _rest = (100 - excluded_model.get( 'traffic' )) % (this.length - 1);

		/**
		 * add rest to 1st or last element from collection
		 */
		if ( _rest ) {
			var _last_model = this.last();
			if ( _last_model.get( _last_model.idAttribute ) === excluded_model.get( excluded_model.idAttribute ) ) {
				this.first().set( 'traffic', _new_traffic + _rest );
			} else {
				_last_model.set( 'traffic', _new_traffic + _rest );
			}
		}
	},

	/**
	 * split the removed traffic equally to remained items
	 *
	 * @param excluded_model
	 * @param traffic
	 */
	split_traffic: function ( excluded_model, traffic ) {

		var split_traffic = parseInt( traffic / (this.length - 1) );

		this.each( function ( model ) {
			if ( model.get( model.idAttribute ) === excluded_model.get( excluded_model.idAttribute ) ) {
				return;
			}

			model.set( 'traffic', model.get( 'traffic' ) + split_traffic );

		}, this );

		var _rest = parseInt( traffic % (this.length - 1) );

		/**
		 * add rest to 1st or last element from collection
		 */
		if ( _rest ) {
			var _last_model = this.last();
			if ( _last_model.get( _last_model.idAttribute ) === excluded_model.get( excluded_model.idAttribute ) ) {
				var _first = this.first();
				_first.set( 'traffic', _first.get( 'traffic' ) + _rest );
			} else {
				_last_model.set( 'traffic', _last_model.get( 'traffic' ) + _rest );
			}
		}
	},

	/**
	 * Create a new Traffic Model with traffic prop from the collection
	 * and send it to server to be saved
	 */
	save_distributed_traffic: function () {

		var traffic = new traffic_model();

		this.each( function ( model ) {
			traffic.set( model.get( model.idAttribute ), model.get( 'traffic' ) );
		}, this );

		traffic.save();
	},
	equalize_traffic: function () {

		var new_traffic = parseInt( 100 / this.length ),
			mod = 100 % this.length;

		this.each( function ( model ) {
			model.set( 'traffic', new_traffic );
		} );

		this.findWhere( {is_control: true} ).set( 'traffic', new_traffic + mod );
	},
	allocate_traffic: function ( excluded_model, diff ) {

		this.each( function ( model ) {

			if ( model.get( model.idAttribute ) === excluded_model.get( excluded_model.idAttribute ) ) {
				return;
			}

			var traffic = parseInt( model.get( 'traffic' ) );

			var _new_traffic = traffic + diff;

			if ( _new_traffic < 0 ) {
				diff = _new_traffic;
				_new_traffic = 0;
			} else if ( _new_traffic > 100 ) {
				diff = _new_traffic - 100;
				_new_traffic = 100;
			} else {
				diff = 0;
			}

			model.set( 'traffic', _new_traffic );

		}, this );
	}
} );
