<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

?>
<div class="wrap rmtcmp">

	<h2><?php esc_html_e( 'Password Reset Manager', 'remind-me-to-change-my-password' ); ?></h2>
	<p><?php esc_html_e( 'With this extension, define the validity period for the passwords, set the roles that would be submitted to this rule, remind the users to reset their passwords in due time and suspend the account after a set period of time without password-reset.', 'remind-me-to-change-my-password' ); ?></p>

	<form method="post" action="options.php">
		<?php

		settings_fields( 'rmtcmp_options' );

		do_settings_sections( 'rmtcmp_options' );

		submit_button();

		?>
	</form>
</div>
