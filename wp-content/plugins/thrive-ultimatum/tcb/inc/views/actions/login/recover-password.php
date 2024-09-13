<?php $reset_url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ); ?>
<p><?php echo esc_html__( 'Someone has requested a password reset for the following account:', 'thrive-cb' ); ?></p>
<p><?php echo sprintf( esc_html__( 'Site Name: %s', 'thrive-cb' ), esc_html( $site_name ) ); ?></p>
<p><?php echo sprintf( esc_html__( 'Username: %s', 'thrive-cb' ), esc_html( $user->user_login ) ); ?></p>
<p><?php echo esc_html__( 'If this was a mistake, just ignore this email and nothing will happen.', 'thrive-cb' ); ?></p>
<p><?php echo esc_html__( 'To reset your password, visit the following address:', 'thrive-cb' ); ?> <a href="<?php echo esc_url( $reset_url ) ?>"><?php echo esc_html( $reset_url ) ?></a></p>
