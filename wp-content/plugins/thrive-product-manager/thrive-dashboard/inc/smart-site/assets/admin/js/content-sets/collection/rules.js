module.exports = Backbone.Collection.extend( {
	model: require( '../models/rule' ),
	isCompleted() {
		let isComplete = true;

		if ( this.length === 0 ) {
			isComplete = false
		} else {
			this.each( model => {
				if ( ! model.isCompleted() ) {
					isComplete = false;
				}
			} );
		}

		return isComplete
	}
} );
