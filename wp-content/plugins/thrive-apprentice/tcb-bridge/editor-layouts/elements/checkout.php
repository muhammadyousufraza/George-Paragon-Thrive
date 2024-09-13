<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/15/2018
 * Time: 2:08 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$apis = Thrive_Dash_List_Manager::get_available_apis( true, [ 'only_names' => true ] );

$payment_platform = ! empty( $apis['sendowl'] ) ? $apis['sendowl'] : '';

$login_instance              = 'login';
$create_account_instance     = 'create_account';
$recover_account_instance    = 'forgot_password';
$password_reset_confirmation = 'reset_confirmation';

$placeholders = array(
	'username_email'   => __( 'Username or Email Address', 'thrive-apprentice' ),
	'password'         => __( 'Type your password', 'thrive-apprentice' ),
	'first_name'       => __( 'John Smith', 'thrive-apprentice' ),
	'email'            => __( 'email@email.com', 'thrive-apprentice' ),
	'password_confirm' => __( 'Retype your password', 'thrive-apprentice' ),
	'lost_password'    => __( 'I have forgotten my password.', 'thrive-apprentice' ),
	'login'            => __( 'Back to Log in', 'thrive-apprentice' ),
);

?>

<div class="thrv_wrapper thrv-checkout" data-payment-platform="<?php echo $payment_platform; ?>" data-ct="checkout-30848" data-ct-name="Default">
	<div class="thrv_wrapper thrv-button-group tcb-no-clone tcb-no-delete tve_no_drag tcb-no-save">
		<div class="thrv_wrapper thrv-button-group-item tcb-no-clone tcb-no-delete tve_no_drag tcb-no-title tcb-no-save tcb-active-state tcb-with-icon"
			 data-default="true" data-instance="<?php echo $create_account_instance; ?>">
			<a href="#" class="tcb-button-link">
				<span class="tcb-button-icon">
					<div class="thrv_wrapper thrv_icon tve_no_drag tve_no_icons tcb-icon-inherit-style">
						<svg class="tcb-icon" viewBox="0 0 512 512" data-id="icon-pencil-light">
							<path
								d="M493.255 56.236l-37.49-37.49c-24.993-24.993-65.515-24.994-90.51 0L12.838 371.162.151 485.346c-1.698 15.286 11.22 28.203 26.504 26.504l114.184-12.687 352.417-352.417c24.992-24.994 24.992-65.517-.001-90.51zm-95.196 140.45L174 420.745V386h-48v-48H91.255l224.059-224.059 82.745 82.745zM126.147 468.598l-58.995 6.555-30.305-30.305 6.555-58.995L63.255 366H98v48h48v34.745l-19.853 19.853zm344.48-344.48l-49.941 49.941-82.745-82.745 49.941-49.941c12.505-12.505 32.748-12.507 45.255 0l37.49 37.49c12.506 12.506 12.507 32.747 0 45.255z"></path>
						</svg>
					</div>
				</span>
				<span class="tcb-button-texts">
					<span class="tcb-button-text thrv-inline-text"><?php echo __( 'Register', 'thrive-apprentice' ); ?></span>
				</span>
			</a>
		</div>
		<div class="thrv_wrapper thrv-button-group-item tcb-no-clone tcb-no-delete tve_no_drag tcb-no-title tcb-no-save tcb-with-icon"
			 data-instance="<?php echo $login_instance; ?>">
			<a href="#" class="tcb-button-link">
				<span class="tcb-button-icon">
					<div class="thrv_wrapper thrv_icon tve_no_drag tve_no_icons tcb-icon-inherit-style">
						<svg class="tcb-icon" viewBox="0 0 384 512" data-id="icon-unlock-alt-light">
							<path d="M336 256H96v-96c0-70.6 25.4-128 96-128s96 57.4 96 128v20c0 6.6 5.4 12 12 12h8c6.6 0 12-5.4 12-12v-18.5C320 73.1 280.9.3 192.5 0 104-.3 64 71.6 64 160v96H48c-26.5 0-48 21.5-48 48v160c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48zm16 208c0 8.8-7.2 16-16 16H48c-8.8 0-16-7.2-16-16V304c0-8.8 7.2-16 16-16h288c8.8 0 16 7.2 16 16v160zm-160-32c-8.8 0-16-7.2-16-16v-64c0-8.8 7.2-16 16-16s16 7.2 16 16v64c0 8.8-7.2 16-16 16z"></path>
						</svg>
					</div>
				</span>
				<span class="tcb-button-texts"><span class="tcb-button-text thrv-inline-text"><?php echo __( 'Log in', 'thrive-apprentice' ); ?></span></span>
			</a>
		</div>
	</div>
	<div class="tcb-tva-checkout-form-wrapper tcb-permanently-hidden tve_empty_dropzone tve_no_drag" data-instance="<?php echo $login_instance; ?>">
		<div class="thrv_wrapper tcb-checkout-form tcb-no-clone tcb-no-delete tcb-no-save">
			<form action="" method="post" novalidate data-route="login" class="tve-form tcb-tva-login-form">
				<div class="tve-cf-item-wrapper">
					<div class="tve-form-item">
						<div class="thrv-form-input-wrapper" data-type="email">
							<label class="thrv-inline-text"><?php echo __( 'Username or Email Address', 'thrive-apprentice' ); ?></label>
							<div class="tve-form-input">
								<input placeholder="<?php echo $placeholders['username_email']; ?>"
									   data-placeholder="<?php echo $placeholders['username_email']; ?>" type="text" name="username">
							</div>
						</div>
					</div>
					<div class="tve-form-item tcb-tva-password-item">
						<div class="thrv-form-input-wrapper" data-type="password">
							<label class="thrv-inline-text"><?php echo __( 'Password', 'thrive-apprentice' ); ?></label>
							<div class="tve-form-input">
								<input placeholder="<?php echo $placeholders['password']; ?>" data-placeholder="<?php echo $placeholders['password']; ?>"
									   name="password">
							</div>
						</div>
					</div>
				</div>

				<div class="tve-form-submit">
					<button class="tve_btn_txt" type="submit"><?php echo __( 'Log In', 'thrive-apprentice' ); ?></button>
				</div>

				<div class="thrv_wrapper thrv_text_element tcb-tva-lost-password-link tcb-no-clone tcb-no-delete tve_no_drag tcb-no-title tcb-no-save">
					<p><a href="javascript:void(0);" class="tva-switch-instance-link"
						  data-go_to_instance="<?php echo $recover_account_instance; ?>"><?php echo $placeholders['lost_password']; ?></a></p>
				</div>
			</form>
		</div>
	</div>
	<div class="tcb-tva-checkout-form-wrapper tve_empty_dropzone tve_no_drag" data-instance="<?php echo $create_account_instance; ?>">
		<div class="thrv_wrapper tcb-checkout-form tcb-no-clone tcb-no-delete tcb-no-save">
			<form action="" method="post" novalidate data-route="register" class="tve-form">
				<div class="tve-cf-item-wrapper">
					<div class="tve-form-item">
						<div class="thrv-form-input-wrapper" data-type="first_name">
							<label class="thrv-inline-text"><?php echo __( 'Full Name', 'thrive-apprentice' ); ?></label>
							<div class="tve-form-input">
								<input placeholder="<?php echo $placeholders['first_name']; ?>" data-placeholder="<?php echo $placeholders['first_name']; ?>"
									   type="text" name="first_name">
							</div>
						</div>
					</div>
					<div class="tve-form-item">
						<div class="thrv-form-input-wrapper" data-type="email">
							<label class="thrv-inline-text"><?php echo __( 'Email', 'thrive-apprentice' ); ?></label>
							<div class="tve-form-input">
								<input placeholder="<?php echo $placeholders['email']; ?>" data-placeholder="<?php echo $placeholders['email']; ?>" type="email"
									   name="email">
							</div>
						</div>
					</div>
					<div class="tve-form-item">
						<div class="thrv-form-input-wrapper" data-type="password">
							<label class="thrv-inline-text"><?php echo __( 'Password', 'thrive-apprentice' ); ?></label>
							<div class="tve-form-input">
								<input placeholder="<?php echo $placeholders['password']; ?>" data-placeholder="<?php echo $placeholders['password']; ?>"
									   name="password">
							</div>
						</div>
					</div>
					<div class="tve-form-item">
						<div class="thrv-form-input-wrapper" data-type="password">
							<label class="thrv-inline-text"><?php echo __( 'Confirm Password', 'thrive-apprentice' ); ?></label>
							<div class="tve-form-input">
								<input placeholder="<?php echo $placeholders['password_confirm']; ?>"
									   data-placeholder="<?php echo $placeholders['password_confirm']; ?>" name="confirm_password">
							</div>
						</div>
					</div>
				</div>
				<div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box">
					<div class="tve-content-box-background"></div>
					<div class="tve-cb">
						<div class="tve-form-submit">
							<button class="tve_btn_txt" type="submit"><?php echo __( 'Create Account', 'thrive-apprentice' ); ?></button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tcb-tva-checkout-form-wrapper tve_empty_dropzone tcb-permanently-hidden tve_no_drag" data-instance="<?php echo $recover_account_instance; ?>">
		<div class="thrv_wrapper thrv_heading" data-tag="h2">
			<h2>Password Reset</h2>
		</div>
		<div class="thrv_wrapper thrv_text_element">
			<p>Please enter your email address. You will receive a </p>
			<p> link to create a new password via email</p>
		</div>
		<div class="thrv_wrapper tcb-checkout-form tcb-no-clone tcb-no-delete tcb-no-save">
			<form action="" method="post" data-route="recover" class="tve-form" novalidate>
				<div class="tve-cf-item-wrapper">
					<div class="tve-form-item">
						<div class="thrv-form-input-wrapper" data-type="text">
							<label class="thrv-inline-text"><?php echo __( 'Username or Email Address', 'thrive-apprentice' ); ?></label>
							<div class="tve-form-input">
								<input placeholder="<?php echo $placeholders['username_email']; ?>"
									   data-placeholder="<?php echo $placeholders['username_email']; ?>" type="text" name="user_login">
							</div>
						</div>
					</div>
				</div>
				<div class="tve-form-submit">
					<button class="tve_btn_txt" type="submit"><?php echo __( 'Get New Password', 'thrive-apprentice' ); ?></button>
				</div>
			</form>
		</div>
		<div class="thrv_wrapper thrv-divider" data-style="tve_sep-1" data-thickness="3" data-color="rgb(66, 66, 66)">
			<hr class="tve_sep tve_sep-1">
		</div>
		<div class="thrv_wrapper thrv-button tcb-no-delete tcb-no-clone tcb-go-back tcb-with-icon" draggable="true">
			<a href="javascript:void(0);" class="tcb-button-link tva-switch-instance-link" data-go_to_instance="<?php echo $login_instance; ?>"
			   draggable="false">
				<span class="tcb-button-icon">
					<div class="thrv_wrapper thrv_icon">
						<svg class="tcb-icon" viewBox="0 0 448 512" data-id="icon-long-arrow-left-light" data-name="">
							<path d="M136.97 380.485l7.071-7.07c4.686-4.686 4.686-12.284 0-16.971L60.113 273H436c6.627 0 12-5.373 12-12v-10c0-6.627-5.373-12-12-12H60.113l83.928-83.444c4.686-4.686 4.686-12.284 0-16.971l-7.071-7.07c-4.686-4.686-12.284-4.686-16.97 0l-116.485 116c-4.686 4.686-4.686 12.284 0 16.971l116.485 116c4.686 4.686 12.284 4.686 16.97-.001z"></path>
						</svg>
					</div>
				</span>
				<span class="tcb-button-texts">
					<span class="thrv-inline-text"><?php echo $placeholders['login']; ?></span>
				</span>
			</a>
		</div>
	</div>
	<div class="tcb-tva-checkout-form-wrapper tve_empty_dropzone tcb-permanently-hidden tve_no_drag"
		 data-instance="<?php echo $password_reset_confirmation; ?>">
		<div class="thrv_wrapper thrv_heading" data-tag="h2">
			<h2><?php echo __( 'Password Reset', 'thrive-apprentice' ); ?></h2>
		</div>
		<div class="thrv_wrapper thrv_text_element">
			<p><?php echo __( 'The instructions to reset your password are sent to the email address you provided.', 'thrive-apprentice' ); ?></p>
			<p><?php echo __( 'If you did not receive the email, please check your spam folder as well.', 'thrive-apprentice' ); ?></p>
		</div>
		<div class="thrv_wrapper thrv-divider" data-style="tve_sep-1" data-thickness="3" data-color="rgb(66, 66, 66)">
			<hr class="tve_sep tve_sep-1">
		</div>
		<div class="thrv_wrapper thrv-button tcb-no-delete tcb-no-clone tcb-go-back tcb-with-icon" draggable="true">
			<a href="javascript:void(0);" class="tcb-button-link tva-switch-instance-link" data-go_to_instance="<?php echo $login_instance; ?>"
			   draggable="false">
				<span class="tcb-button-icon">
					<div class="thrv_wrapper thrv_icon">
						<svg class="tcb-icon" viewBox="0 0 448 512" data-id="icon-long-arrow-left-light" data-name="">
							<path d="M136.97 380.485l7.071-7.07c4.686-4.686 4.686-12.284 0-16.971L60.113 273H436c6.627 0 12-5.373 12-12v-10c0-6.627-5.373-12-12-12H60.113l83.928-83.444c4.686-4.686 4.686-12.284 0-16.971l-7.071-7.07c-4.686-4.686-12.284-4.686-16.97 0l-116.485 116c-4.686 4.686-4.686 12.284 0 16.971l116.485 116c4.686 4.686 12.284 4.686 16.97-.001z"></path>
						</svg>
					</div>
				</span>
				<span class="tcb-button-texts">
					<span class="thrv-inline-text"><?php echo $placeholders['login']; ?></span>
				</span>
			</a>
		</div>
	</div>
	<input type="hidden" name="config"
		   value="YToxOntzOjE0OiJlcnJvcl9tZXNzYWdlcyI7YTo0OntzOjU6ImVtYWlsIjtzOjIxOiJFbWFpbCBhZGRyZXNzIGludmFsaWQiO3M6MTI6ImVtcHR5X2ZpZWxkcyI7czoyMjoiU29tZSBmaWVsZHMgYXJlIGVtcHR5ISI7czoxOToicGFzc3dvcmRzX25vdF9tYXRjaCI7czoyNjoiVGhlIHBhc3N3b3JkcyBkbyBub3QgbWF0Y2giO3M6MTk6ImV4aXN0aW5nX3VzZXJfZW1haWwiO3M6MTI2OiJBbiBhY2NvdW50IHdpdGggdGhhdCBlbWFpbCBhZGRyZXNzIGFscmVhZHkgZXhpc3RzLiBJbiBvcmRlciB0byBwbGFjZSB0aGUgb3JkZXIsIHBsZWFzZSBsb2dpbiBmaXJzdC4gW2FdQ2xpY2sgaGVyZSB0byBsb2dpblsvYV0iO319">
</div>