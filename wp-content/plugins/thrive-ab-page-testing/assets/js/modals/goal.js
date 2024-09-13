module.exports = TVE_Dash.views.Modal.extend( {

	initialize: function ( args ) {

		if ( this.model.get( 'type' ) === 'monetary' && this.model.get( 'goal_pages' ) === 'sendowl' ) {
			this.template = TVE_Dash.tpl( 'modals/goal/sendowl' );
		} else {
			this.template = TVE_Dash.tpl( 'modals/goal/' + this.model.get( 'type' ) );
		}

		TVE_Dash.views.Modal.prototype.initialize.apply( this, arguments );
	},

	render: function () {
		TVE_Dash.views.Modal.prototype.render.apply( this, arguments );

		var _tpl;

		if ( this.model.get( 'type' ) === 'monetary' ) {
			_tpl = TVE_Dash.tpl( 'modals/goal/revenue-row' );
		} else if ( this.model.get( 'type' ) === 'visits' ) {
			_tpl = TVE_Dash.tpl( 'modals/goal/page-row' );
		}

		if ( _tpl ) {
			this.collection.each( function ( item ) {
				this.$( '.thrive-ap-goal-pages' ).append( _tpl( {model: item} ) );
			}, this );
		}

		return this;
	},
} );
