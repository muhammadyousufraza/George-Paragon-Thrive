<div class="tvo-form-config">
	<input type="hidden" class="tags" value='<?php echo empty( $config['tags'] ) ? '[]' : json_encode( $config['tags'] ); ?>'>
	<input type="hidden" class="shortcode_id" value="<?php echo $config['id']; ?>">
	<input type="hidden" class="on_success" value="<?php echo $config['on_success_option']; ?>">
	<?php if ( $config['on_success_option'] == 'redirect' ) : ?><input type="hidden" class="on_redirect" value="<?php echo $config['on_success']; ?>"> <?php endif; ?>
</div>
<div class="tvo-form-error" style="color: red;"></div>
<?php
$captcha_api = Thrive_Dash_List_Manager::credentials( 'recaptcha' );
$site_key    = isset( $captcha_api['site_key'] ) ? $captcha_api['site_key'] : "";
?>
<script type="text/javascript">
	var $shortcode;
	document.addEventListener( 'DOMContentLoaded', function () {
		jQuery = ( typeof TVE !== 'undefined' && TVE.inner.jQuery ) || jQuery;
		var $recaptcha = jQuery( '#tvo-reCaptcha' );
		if ( $recaptcha.length ) {
			function reCaptchaLoaded() {
				$recaptcha.filter( ':not(.tvo-recaptcha-rendered)' ).each( function () {
					$this = jQuery( this );
					$this.addClass( 'tvo-recaptcha-rendered' );
				} );
			}

			function checkCaptchaLoaded() {
				if ( typeof grecaptcha === 'undefined' ) {
					setTimeout( checkCaptchaLoaded, 100 );
				} else {
					reCaptchaLoaded();
				}
			}

			var loading_script = false;
			if ( ! window.tve_gapi_loaded ) {
				jQuery.getScript( 'https://www.google.com/recaptcha/api.js', checkCaptchaLoaded );
				loading_script = true;
				window.tve_gapi_loaded = true;
			} else {

				$recaptcha.empty().css( 'height', '96px' );
				if ( ! $recaptcha.hasClass( 'tvo-recaptcha-rendered' ) ) {
					grecaptcha.render( 'tvo-reCaptcha', {
						'sitekey': '<?php echo $site_key ?>',
						'theme': 'light'
					} );
				}
			}

			if ( ! loading_script ) {
				checkCaptchaLoaded();
			}
		}

		/* apply custom color class */
		$shortcode = jQuery( '#<?php echo $unique_id; ?>' );
		<?php if ( ! empty( $config['color_class'] ) ) : ?>
		var new_class = '<?php echo $config['color_class']; ?>';
		$shortcode.attr( 'class', $shortcode.attr( 'class' ).replace( /tve_(\w+)/i, new_class ) );
		<?php endif;?>

		/* apply custom color for testimonial elements */
		<?php if ( ! empty( $config['custom_css'] ) && is_array( $config['custom_css'] ) ) : ?>
		var tve_custom_colors = <?php echo json_encode( $config['custom_css'] ); ?>;
		for ( var selector in tve_custom_colors ) {
			$shortcode.find( selector ).attr( 'data-css', tve_custom_colors[ selector ] );
		}
		<?php endif; ?>
	} )

</script>

<?php if ( ! empty( $google_client_id ) && ! $is_editor ) : ?>
	<script src="https://accounts.google.com/gsi/client" async defer></script>

	<script type="text/javascript">
		window.onGoogleLibraryLoad = () => {
			const tvoParseJwt = ( token ) => {
				const base64 = token.split( '.' )[ 1 ].replace( /-/g, '+' ).replace( /_/g, '/' );
				const jsonPayload = decodeURIComponent( window.atob( base64 ).split( '' ).map( c => '%' + ( '00' + c.charCodeAt( 0 ).toString( 16 ) ).slice( - 2 ) ).join( '' ) )

				return JSON.parse( jsonPayload );
			}

			/* initialize login functionality */
			window.google.accounts.id.initialize( {
				client_id: '<?php echo $google_client_id; ?>',
				context: 'use',
				ux_mode: 'popup',
				auto_prompt: false,
				callback: authResponse => {
					const profile = tvoParseJwt( authResponse.credential );

					var $picture = jQuery( '#<?php echo $unique_id; ?> .tvo-picture-wrapper' );
					$picture.css( 'background-image', 'url(' + profile.picture + ')' );
					$picture.siblings( '.tvo-remove-image' ).show();
				}
			} )

			window.google.accounts.id.renderButton(
				document.getElementById( 'tvo-google-login-<?php echo $unique_id; ?>' ), {
					type: 'standard',
					theme: 'outline',
					text: 'signin',
					width: 150
				}
			)
		}
	</script>
<?php endif; ?>

<?php if ( ! empty( $facebook_app_id ) && ! $is_editor ) : ?>
	<script type="text/javascript">
		document.addEventListener( 'DOMContentLoaded', function () {
			if ( typeof FB === 'undefined' ) {
				window.fbAsyncInit = function () {
					FB.init( {
						appId: '<?php echo $facebook_app_id; ?>',
						cookie: true,
						xfbml: true,
						version: 'v2.8'
					} );
				};

				// Load the SDK asynchronously
				( function ( d, s, id ) {
					var js, fjs = d.getElementsByTagName( s )[ 0 ];
					if ( d.getElementById( id ) ) {
						return;
					}
					js = d.createElement( s );
					js.id = id;
					js.src = "//connect.facebook.net/en_US/sdk.js";
					fjs.parentNode.insertBefore( js, fjs );
				}( document, 'script', 'facebook-jssdk' ) );
			}

			$shortcode.find( '.tvo-fb-button' ).click( function () {
				var $picture = jQuery( this ).parent().siblings( '.tvo-picture-wrapper' );

				if ( typeof FB !== 'undefined' ) {

					$picture.css( 'background-image', 'url("<?php echo TVO_URL; ?>templates/css/images/loading.gif")' );

					FB.getLoginStatus( function ( response ) {
						if ( response.status === 'connected' ) {
							FB.api( '/me', function ( profile ) {
								var img = '//graph.facebook.com/' + profile.id + '/picture?type=large';
								$picture.css( 'background-image', 'url(' + img + ')' );
								$picture.siblings( '.tvo-remove-image' ).show();
							} );
						} else {
							FB.login( function ( resp ) {
								if ( resp.authResponse ) {
									FB.api( '/me', function ( resp ) {
										var img = '//graph.facebook.com/' + resp.id + '/picture?type=large';
										$picture.css( 'background-image', 'url(' + img + ')' );
										$picture.siblings( '.tvo-remove-image' ).show();
									} );
								} else {
									$picture.css( 'background-image', 'url(' + $picture.data( 'default' ) + ')' );
								}
							} );
						}
					}, true );
				}
			} );
		} );
	</script>
<?php endif; ?>
