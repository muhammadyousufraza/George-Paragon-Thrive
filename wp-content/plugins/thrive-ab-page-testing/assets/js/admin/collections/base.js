/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 10:55 AM
 */

/**
 * Base Collection
 */
module.exports = Backbone.Collection.extend( {
	/**
	 * helper function to get the last item of a collection
	 *
	 * @return Backbone.Model
	 */
	last: function () {
		return this.at( this.size() - 1 );
	}
} );
