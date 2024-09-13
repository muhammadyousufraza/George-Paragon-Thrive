/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 11:03 AM
 */

/**
 * breadcrumbs view - renders breadcrumb links
 */
(function ( $ ) {
	module.exports = Backbone.View.extend( {
		el: $( '#tab-breadcrumbs-wrapper' )[0],
		template: TVE_Dash.tpl( 'breadcrumbs' ),
		/**
		 * setup collection listeners
		 */
		initialize: function () {
			this.$title = $( 'head > title' );
			this.original_title = this.$title.html();
			this.listenTo( this.collection, 'change', this.render );
			this.listenTo( this.collection, 'add', this.render );
		},
		/**
		 * render the html
		 */
		render: function () {
			this.$el.empty().html( this.template( {links: this.collection} ) );
		}
	} );
})( jQuery );
