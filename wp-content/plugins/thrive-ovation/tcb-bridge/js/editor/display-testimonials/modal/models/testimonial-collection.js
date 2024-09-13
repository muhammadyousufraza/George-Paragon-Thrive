module.exports = Backbone.Collection.extend( {
	comparator: 'order',
	model: require( './testimonial' ),
} );
