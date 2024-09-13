/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/16/2017
 * Time: 4:27 PM
 */
(function ( $ ) {
	//DOM ready
	$( function () {
		var $traffic_input = $( '.thrive-ab-traffic-input' ),
			variations = {};

		$.each( $traffic_input, function ( index, input ) {
			variations[input.dataset.tab_variation_id] = parseInt( input.dataset.tab_variation_value );
		} );

		$traffic_input.on( 'change', function () {
			tab_save_traffic();
		} );

		$traffic_input.on( 'input', function ( event ) {
			var _value = event.target.value,
				_variation_id = event.target.dataset.tab_variation_id;

			if ( _value.length <= 0 ) {
				event.target.value = 0;
			}

			if ( isNaN( parseInt( _value ) ) || parseInt( _value ) > 100 ) {
				_value = _value.substring( 0, _value.length - 1 );
			}

			if ( isNaN( parseInt( _value ) ) ) {
				_value = 0;
			}

			_value = parseInt( _value );

			if ( _value < 0 ) {
				_value = 0;
			}

			if ( _value > 100 ) {
				_value = 100;
			}

			var _diff = parseInt( event.target.dataset.tab_variation_value ) - _value;
			variations[_variation_id] = _value;

			$( '#thrive-ab-traffic-range-' + _variation_id ).val( _value ).attr( 'data-tab_variation_value', _value );
			$( '#thrive-ab-traffic-input-' + _variation_id ).val( _value ).attr( 'data-tab_variation_value', _value );

			tab_allocate_traffic( _variation_id, _diff );
		} );


		function tab_allocate_traffic( selected_variation_id, diff ) {

			$.each( variations, function ( variation_id, traffic ) {

				if ( variation_id === selected_variation_id ) {
					//Ignores the selected variation
					return true;
				}

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

				variations[variation_id] = _new_traffic;

				$( '#thrive-ab-traffic-range-' + variation_id ).val( _new_traffic ).attr( 'data-tab_variation_value', _new_traffic );
				$( '#thrive-ab-traffic-input-' + variation_id ).val( _new_traffic ).attr( 'data-tab_variation_value', _new_traffic );
			} );
		}

		function tab_save_traffic() {

			jQuery.ajax( {
				cache: false,
				url: ThriveAbEditPost.ajax.url,
				method: 'POST',
				dataType: 'json',
				data: {
					tab_edit_post_traffic: variations,
					route: 'traffic',
					action: ThriveAbEditPost.ajax.action,
					custom: ThriveAbEditPost.ajax.controller_action,
					nonce: ThriveAbEditPost.ajax.nonce
				}
			} ).done( function ( response ) {
			} );
		}

	} );
})( jQuery );
