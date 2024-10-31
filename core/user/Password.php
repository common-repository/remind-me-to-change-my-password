<?php
// $errors = new WP_Error();
namespace RMTCMP\User;

class Password extends User {
	public function __construct() {
		/***
		 * Check password reset
		 * After != Before
		 */
		add_action( 'validate_password_reset', array( $this, 'validate_password_reset' ), 10, 2 );

		/**
		 * On bloque le reset mot de passe pour les comptes bloqués
		 */
		add_filter( 'lostpassword_errors', array( $this, 'lostpassword_errors' ), 10, 2 );

		add_action( 'resetpass_form', array( $this, 'resetpass_form' ), 10, 1 );

		add_action( 'password_reset', array( $this, 'password_reset' ) );
	}

	public function password_reset( $user ) {
		if ( isset( $_REQUEST['rmtcmp-reset'] ) && '' !== $_REQUEST['rmtcmp-reset'] ) {
			$maybe_invalid_key = $this->maybe_invalid_key( $_REQUEST['rmtcmp-reset'] );

			switch ( $maybe_invalid_key ) {
				case 0:
					$this->restore_capabilities( $user );
					$this->set_password_reset_time( $user->ID );
					break;
				case 1:
					wp_safe_redirect(
						add_query_arg(
							array(
								'action' => 'rmtcmp',
								'error'  => 'invalidkey',
							),
							wp_login_url()
						),
						302
					);
					exit;
				case 2:
					wp_safe_redirect(
						add_query_arg(
							array(
								'action' => 'rmtcmp',
								'error'  => 'expiredkey',
							),
							wp_login_url()
						),
						302
					);
					exit;
			}
		} else {
			$this->set_password_reset_time( $user->ID );
		}
	}


	public function resetpass_form( $user ) {
		$reset_cookie = ( isset( $_COOKIE[ $this->rmtcmpreset_cookie ] ) && '' !== $_COOKIE[ $this->rmtcmpreset_cookie ] ) ? sanitize_text_field( $_COOKIE[ $this->rmtcmpreset_cookie ] ) : '';

		if ( '' !== $reset_cookie ) {
			echo '<input type="hidden" name="rmtcmp-reset" value="' . esc_attr( $reset_cookie ) . '" />';
		}
	}


	/**
	 * @param $errors
	 * @param $user_data
	 *
	 *  le hook lostpassword_errors fonctionne pour un reset demandé par un utilisateur ou  un force reset via l'admin
	 *
	 * @return object $errors
	 */
	public function lostpassword_errors( $errors, $user_data ) {

		/**
		 * Si on souhaite débloquer l'utilisateur via l'admin, on ne va pas plus loin
		 */
		if ( isset( $_GET['_wpnonce'] ) && is_admin() && wp_verify_nonce( $_GET['_wpnonce'], 'unlock_user_' . get_current_user_id() ) ) {
			return $errors;
		}

		$check = $this->maybe_control( $user_data );

		if ( ( false !== $check && true === $check['user_lock'] ) || $this->has_role_lock( $user_data ) ) {
			$email = apply_filters( 'rmtcmp_email_contact', get_option( 'admin_email' ) );
			$errors->add( 'password_previously', esc_html__( 'Your account has been suspended. To reset your password and reactivate your account, please contact the site administrator:', 'remind-me-to-change-my-password' ) . ' ' . $email );
		}

		return $errors;
	}

	public function validate_password_reset( $errors, $user ) {
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : 'rp';

		if ( 'resetpass' !== $action ) {
			return;
		}

		if ( wp_check_password( $_POST['pass1'], $user->data->user_pass, $user->ID ) ) {
			$errors->add( 'password_previously', esc_html__( 'Your password needs to be different than your previous one.', 'remind-me-to-change-my-password' ) );

			return;
		}
	}
}


