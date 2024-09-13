/**
 * Required in frontend only, when a variation is displayed
 * - to allow TCB trigger ajax request for custom html forms to register conversion on TOP
 * - to register impressions for a variation by TD Lazy Loading
 */
var ThriveGlobal = ThriveGlobal || {$j: jQuery.noConflict()};

ThriveAB = ThriveAB || {};

(function ( $ ) {

	/**
	 * DOM Ready
	 */
	$( function () {
		//hook into dashboard ajax request
		ThriveAB.dashboard_hook();
	} );

	/**
	 * In case on current variation exists a LG Element with custom html
	 * we need to set some data on submit() event to allow conversions to be registered
	 */
	if ( typeof ThriveAB.test_type !== 'undefined' && ThriveAB.test_type === 'optins' ) {
		$( 'body' ).off( 'should_submit_form.tcb' ).on( 'should_submit_form.tcb', '.thrv_lead_generation', function ( event ) {
			event.flag_need_data = true;
			return true;
		} );
	}

	/**
	 * Try to hook into dashboard ajax lazy load request
	 * and inject some data to pe processed by TOP on server, usually register impression
	 */
	ThriveAB.dashboard_hook = function () {

		if ( typeof TVE_Dash === 'undefined' || TVE_Dash.ajax_sent === true ) {
			return;
		}

		$( document ).on( 'tve-dash.load', function () {
			/**
			 * assign some data on dash request to be caught on server
			 * @see Thrive_AB_Ajax:dashboard_lazy_load()
			 */
			TVE_Dash.add_load_item( 'top_lazy_load', ThriveAB.impression_data );
		} );
	};

})( ThriveGlobal.$j );
