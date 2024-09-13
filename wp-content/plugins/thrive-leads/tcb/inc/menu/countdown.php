<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/12/2017
 * Time: 9:33 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

$is_ult_active = is_plugin_active( 'thrive-ultimatum/thrive-ultimatum.php' );

$time_settings = tve_get_time_settings();
?>

<div id="tve-countdown-component" class="tve-component" data-view="Countdown">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-cd-active">
			<div class="tve-cd-default">
				<div class="tcb-text-center mb-10 mr-5 ml-5">
					<button class="tve-button orange click" data-fn="editElement">
						<?php echo esc_html__( 'Edit design', 'thrive-cb' ); ?>
					</button>
				</div>
				<div class="tve-control" data-view="CountdownPalette"></div>
				<div class="tve-control" data-view="Size"></div>
				<div class="tve-control" data-view="ExternalFields"></div>
				<div class="custom-fields-state" data-state="static">
					<div class="tve-control" data-view="EndDate"></div>
					<div class="control-grid">
						<div class="label self-baseline"><?php echo esc_html__( 'Time', 'thrive-cb' ); ?></div>
						<div class="input flex space-between wrap">
							<div class="tve-control tve-evergreen mb-10" data-view="Day"></div>
							<div class="tve-control  mb-10" data-view="Hour"></div>
							<div class="tve-control  mb-10" data-view="Minute"></div>
							<div class="tve-control tve-evergreen  mb-10" data-view="Second"></div>
						</div>
					</div>
				</div>
				<div class="tve-evergreen mb-10">
					<div class="tve-control no-space" data-view="StartAgain"></div>
					<div class="control-grid no-space tcb-hidden mt-5 tcb-start-again-control">
						<div class="label">&nbsp;</div>
						<div class="input flex space-between">
							<span class="tve-control mb-10" data-view="ExpDay"></span>
							<span class="tve-control mb-10" data-view="ExpHour"></span>
						</div>
					</div>
				</div>
				<div class="control-grid timezone-notice" data-timezone="<?php echo esc_attr( $time_settings['tzd'] ); ?>">
					<span class="info-text grey-text fill mt-0"><?php echo esc_html__( 'Timezone', 'thrive-cb' ); ?> UTC <?php echo esc_html( $time_settings['tzd'] ); ?></span>
				</div>
			</div>
			<div class="tve-control full-width" data-name="<?php esc_attr_e( 'Visible countdown tiles', 'thrive-cb' ); ?>" data-view="VisibleTiles" data-extends="ButtonGroup" data-required="1" data-checkbox="true"></div>
			<div class="tve-control mt-10" data-view="ShowSep"></div>

			<?php if ( ! $is_ult_active ) { ?>
				<div class="tve-cd-default">
					<div class="tve-ult-upsell flex p-5">
						<div class="mr-5">
							<svg width="44" height="48" viewBox="0 0 44 48">
								<defs>
									<path id="9wh4h0lmya" d="M0 0.377L39.616 0.377 39.616 43.297 0 43.297z"/>
									<path id="6s1zsy832c" d="M0.495 0.201L21.267 0.201 21.267 14.154 0.495 14.154z"/>
								</defs>
								<g fill="none" fill-rule="evenodd">
									<g>
										<g>
											<g transform="translate(-710 -536) translate(710 536) translate(0 .239)">
												<mask id="809zkkcgfb" fill="#fff">
													<use xlink:href="#9wh4h0lmya"/>
												</mask>
												<path fill="#424242"
													  d="M21.812 3.132L18.048.376v3.66C13.283 4.447 9 6.549 5.794 9.723 2.216 13.275 0 18.192 0 23.623c0 5.432 2.216 10.348 5.794 13.911.255.252.52.496.784.739l2.265-2.618c-.206-.185-.402-.37-.589-.555-2.96-2.94-4.784-6.99-4.784-11.477 0-4.479 1.825-8.528 4.784-11.468 2.569-2.55 5.99-4.254 9.794-4.653v3.884l3.764-2.755 3.765-2.745-3.765-2.754zm6.118 16.11c.342-.321.363-.876.03-1.226l-.677-.702c-.334-.35-.882-.36-1.235-.04l-5.598 5.258c-.088 0-.166.01-.245.01-.206.03-.393.088-.569.166l-5.773-5.977-1.07 1.012 5.824 6.016c-.088.205-.137.409-.148.622l-.42.4c-.343.321-.364.876-.03 1.227l.676.7c.334.35.883.36 1.235.04l.285-.273c.166.02.333.02.5 0 .147-.02.284-.058.411-.107l.392.409 1.07-1.012-.353-.36c.156-.312.234-.663.215-1.024l5.48-5.139zm.196-13.472l-1.382 3.163c.637.302 1.255.642 1.843 1.013l2.097-2.766c-.813-.525-1.665-1.002-2.558-1.41zm5.685 3.952c-.547-.546-1.116-1.052-1.724-1.529l-2.088 2.755c.47.38.93.779 1.362 1.207.176.175.343.36.51.545l2.755-2.132c-.265-.292-.54-.574-.815-.846zm1.904 2.19l-2.736 2.122c.823 1.12 1.51 2.346 2.04 3.66l3.322-1.012c-.647-1.714-1.54-3.32-2.626-4.77zm3.165 6.405l-3.323 1.003c.352 1.275.549 2.609.578 3.981l3.48.205c-.01-1.802-.264-3.534-.735-5.189zm-2.804 6.707c-.234 2.785-1.186 5.375-2.666 7.583l.599.468c1.028-.341 2.165-.584 3.254-.798.078-.02.206-.039.362-.069h.01c1.04-2.131 1.706-4.487 1.911-6.98l-3.47-.204zM21.93 39.714c-.696.088-1.401.137-2.127.137-3.598 0-6.92-1.16-9.617-3.116l-2.265 2.63c3.314 2.472 7.422 3.932 11.882 3.932.51 0 1.01-.02 1.5-.05.255-.807.558-1.498.863-2.082l-.236-1.451z"
													  mask="url(#809zkkcgfb)"/>
											</g>
											<path fill="#424242" d="M22.47 43.302c-.01.02-.01.039-.03.058h.04l-.01-.058z" transform="translate(-710 -536) translate(710 536)"/>
											<g transform="translate(-710 -536) translate(710 536) translate(22.31 33.47)">
												<mask id="wqxh1t79kd" fill="#fff">
													<use xlink:href="#6s1zsy832c"/>
												</mask>
												<path fill="#59A31D"
													  d="M18.919.201c-1.367 0-4.227.512-5.352.737-1.07.211-2.47.505-3.51.908-.802.311-1.562.617-2.332.998-.715.357-1.414.77-2.065 1.24l-.64.468c-.113.083-.194.16-.305.247-.109.083-.202.18-.301.255-.226.166-.412.414-.624.58-.214.164-.94.984-1.127 1.234l-.682.957C1.322 8.815.81 10 .617 11.175c-.07.43-.065.655-.114.99-.004.017-.006.034-.008.05v.52c.046.63.218 1.358.219 1.419h.048c.037-.445.72-2.031.886-2.363l.422-.783c.254-.42.525-.834.82-1.225.197-.262.403-.527.636-.763.052-.053.052-.065.097-.119.242-.286.556-.55.82-.817l.885-.753c.036-.029.07-.052.11-.082l1.56-1.093c.471-.276.92-.582 1.396-.846.43-.238 1.08-.578 1.497-.747l1.124-.496c.582-.228 1.17-.497 1.767-.692l1.217-.425.614-.204c.183-.061.44-.175.633-.189l-1.34.614c-.459.185-1.29.618-1.773.859l-1.289.688c-.407.237-.84.474-1.237.737l-1.186.815c-.073.055-.106.086-.177.138-.039.03-.058.042-.095.07l-.26.224c-.046.038-.051.034-.097.073-.033.03-.053.052-.085.085L6.584 8.026 5.337 9.738l-.499.828c-.044.074-.072.131-.111.201-.248.426-.473.87-.677 1.322l-.097.238c.334.079 2.185.059 2.63-.004l.829-.09c1.356-.144 2.918-.518 4.11-1.107.057-.026.11-.045.169-.07.046-.02.1-.05.146-.073.06-.03.086-.051.152-.087l.299-.16c.201-.108.382-.232.564-.353.215-.141.442-.302.635-.474.279-.249.578-.511.813-.802.036-.046.067-.067.102-.112.38-.47.691-.98 1.028-1.479.494-.734.955-1.483 1.354-2.278.866-1.719 1.105-1.934 2.189-3.279.044-.054.045-.065.1-.118l.578-.553c.238-.206.546-.42.845-.53.268-.098.405-.147.771-.147-.256-.378-1.35-.407-2.056-.41H18.92zm-3.6 2.33c-.028.026.04.026-.073.026.026-.025-.043-.025.073-.025z"
													  mask="url(#wqxh1t79kd)"/>
											</g>
										</g>
									</g>
								</g>
							</svg>
						</div>
						<div class="upsell-message">
							<span><?php echo esc_html__( 'For more countdown timers and advanced scarcity marketing, get ', 'thrive-cb' ) ?></span>
							<a href="//thrivethemes.com/ultimatum/upgrade/" target="_blank" class="blue-text">Thrive Ultimatum</a>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<div class="tve-control tcb-hidden" data-view="ShowElement"></div>
	</div>
</div>

