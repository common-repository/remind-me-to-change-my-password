<?php

namespace RMTCMP\User;

class Login extends User {
	public function __construct() {

		/**
		 * Après un login réussi
		 * teste si le compte est bloqué ou non
		 * en fonction on le block et on le déconnecte de partout
		 */
		add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 );
		/**
		 * Gestion des messages
		 */
		add_filter( 'login_message', array( $this, 'login_message' ) );


		add_action( 'login_form_rmtcmp', array( $this, 'login_form_rmtcmp' ) );
	}

	public function login_form_rmtcmp() {
		$error = '';
		if ( isset( $_GET['error'] ) ) {
			$error = sanitize_text_field( $_GET['error'] );
		}
		$msg = '';
		switch ( $error ) {
			case 'expiredkey':
				$msg = esc_html__( 'Your password reset link has expired. Please request a new link:', 'remind-me-to-change-my-password' );
				break;
			case 'invalidkey':
				$msg = esc_html__( 'Your password reset link appears to be invalid. Please request a new link:', 'remind-me-to-change-my-password' );
				break;
		}
		$email = apply_filters( 'rmtcmp_email_contact', get_option( 'admin_email' ) );
		wp_die( $msg . ' ' . $email, '', [
			'link_url'  => get_home_url(),
			'link_text' => esc_html__( 'Back to home', 'remind-me-to-change-my-password' ),
		] );
	}

	public function login_message( $message ) {

		if ( isset( $_GET['action'], $_GET['validpass'] ) && 'lostpassword' === $_GET['action'] && 'expired' === $_GET['validpass'] ) {
			return '<p id="login_error">' . esc_html__( 'Your password has expired. Please reset it', 'remind-me-to-change-my-password' ) . '</p>';
		}


		if ( isset( $_COOKIE[ $this->rmtcmpreset_cookie ] ) && 'rp' === $_GET['action'] && '' !== $_COOKIE[ $this->rmtcmpreset_cookie ] ) {

			$maybe_invalid_key = $this->maybe_invalid_key( $_COOKIE[ $this->rmtcmpreset_cookie ] );

			if ( 1 === $maybe_invalid_key ) {
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
			}

			// valide 1h
			if ( 2 === $maybe_invalid_key ) {
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

			return '<p class="message reset-pass">' . esc_html__( 'To reactivate your account, please reset your password.', 'remind-me-to-change-my-password' ) . '</p>';
		}

		return $message;
	}

	public function wp_login( $user_login, $user ) {

		/**
		 * Si on active l'accès principal Et qu'il correspond à l'id de l'utilisateur
		 */
		if ( true === apply_filters( 'rmtcmp_activate_full_access', false ) ) {
			$options     = get_option( 'rmtcmp_form', array() );
			$full_access = isset( $options['full_access'] ) ? (int) $options['full_access'] : \RMTCMP\Utility\Helpers::get_admin_full_access();
			if ( $user->ID === $full_access ) {
				return;
			}
		}

		$user_meta = get_userdata( $user->ID );

		/**
		 * User déjà bloqué
		 */
		if ( $this->maybe_lock( $user->ID ) ) {

			do_action( 'rmtcmp_block_user_redirect' );

			$email = apply_filters( 'rmtcmp_email_contact', get_option( 'admin_email' ) );

			wp_die( esc_html__( 'Your account has been suspended. To reset your password and reactivate your account, please contact the site administrator:', 'remind-me-to-change-my-password' ) . ' ' . $email, '', [
				'link_url'  => get_home_url(),
				'link_text' => esc_html__( 'Back to home', 'remind-me-to-change-my-password' ),
			] );
		}

		$check = $this->maybe_control( $user_meta );

		/**
		 * l'utilisateur passe
		 * il n'a pas le rôle qu'on doit contrôler
		 */
		if ( false === $check ) {
			return;
		}
		/**
		 * l'utilisateur passe
		 * il est dans les temps
		 */
		if ( false === $check['password_expired'] ) {
			return;
		}


		if ( true === $check['user_lock'] ) {
			do_action( 'rmtcmp_block_user_redirect' );

			$email = apply_filters( 'rmtcmp_email_contact', get_option( 'admin_email' ) );

			wp_die( esc_html__( 'Your account has been suspended. If you wish to reactivate it, please contact the website administrator:', 'remind-me-to-change-my-password' ) . ' ' . $email, '', [
				'link_url'  => get_home_url(),
				'link_text' => esc_html__( 'Back to home', 'remind-me-to-change-my-password' ),
			] );
		}


		/**
		 * L'utilisateur doit modifier son mot de passe
		 */
		wp_safe_redirect(
			add_query_arg(
				array(
					'action'    => 'lostpassword',
					'validpass' => 'expired',
				),
				wp_login_url()
			),
			302
		);
		exit;

	}
}

