( function ( $ ) {
	/**
	 * We need this because this needs to be implemented in other plugins as well
	 *
	 * Ex: Thrive Apprentice
	 */
	$.extend( TD_SETS, {
		Models: {
			Set: require( './models/set' ),
			Rule: require( './models/rule' ),
		},
		Collections: {
			Sets: require( './collection/sets' ),
			Rules: require( './collection/rules' ),
		},
		Views: {
			List: require( './views/list' ),
			Item: require( './views/item' ),
			Rule: require( './views/rule' ),
			Form: require( './views/form' ),
			Pagination: require( './views/pagination' ),
			Confirm: require( './views/confirm-action' ),
			Modals: {
				Edit: require( './views/modals/edit' ),
			},
			Controls: {
				Base: require('./views/controls/base')
			}
		},
	} );

	/**
	 * Allow other functionality to modify stuff on TD_SETS constant
	 *
	 * Used in TA Plugin
	 */
	$( window ).trigger( 'td_sets_ready', TD_SETS );

	TD_SETS.sets = new TD_SETS.Collections.Sets( TD_SETS.sets );
} )( jQuery );
