var base = require( './base' ),
	traffic_model = require( '../models/traffic' );

module.exports = base.extend( {

	model: require( '../models/test-item' ),

	save_distributed_traffic: function () {

		var traffic = new traffic_model();

		this.each( function ( model ) {
			traffic.set( model.get( 'variation_id' ), model.get( 'traffic' ) );
		}, this );

		traffic.save();
	}

} );
