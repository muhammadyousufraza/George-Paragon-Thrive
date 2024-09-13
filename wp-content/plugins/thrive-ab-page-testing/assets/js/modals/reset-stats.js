var _instance = null;

module.exports = TVE.modal.base.extend( {
	events: function () {
		return _.extend( {}, TVE.modal.base.prototype.events(), {
			'click .tcb-modal-save': 'reset_stats'
		} );
	},
	reset_stats: function () {
		this.running_test = false;

		if ( this.$( '#thrive-ab-reset-stats' ).is( ':checked' ) ) {
			this.running_test = TVE.CONST.ajax.thrive_ab.running_test;
		}

		this.trigger('reset_stats', this.running_test );
		TVE.main.overlay();
		this.close();
	},
	after_initialize: function () {
		this.$el.addClass( 'medium' );
	}
}, {
	/**
	 * "Singleton" implementation for modal instance
	 *
	 * @param el
	 */
	get_instance: function ( el ) {
		if ( ! _instance ) {
			_instance = new TVE.ResetStatsModal( {
				el: el
			} );
		}

		return _instance;
	}
} );
