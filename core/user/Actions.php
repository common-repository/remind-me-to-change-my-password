<?php

namespace RMTCMP\User;

class Actions extends User {
	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'user_register', array( $this, 'user_register' ) );

		/**
		 * On gère également les datas si on modifie via le profil utilisateur
		 */
		add_action( 'profile_update', array( $this, 'profile_update' ) );


		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	public function admin_notices() {
		$msg   = '';
		$class = '';

		$notice_unlock_user = get_transient( 'rmtcmp_notice_unlock_user' );

		if ( false !== $notice_unlock_user ) {
			delete_transient( 'rmtcmp_notice_unlock_user' );
			$class = 'error';
			$msg   = esc_html__( 'An error occurred, please try again', 'remind-me-to-change-my-password' );
			if ( $notice_unlock_user ) {
				$class = 'success';
				$msg   = esc_html__( 'A link has been sent to the user so that he can reactivate the account.', 'remind-me-to-change-my-password' );
			}
		}

		$notice_not_allowed = get_transient( 'rmtcmp_notice_not_allowed' );
		if ( false !== $notice_not_allowed ) {
			delete_transient( 'rmtcmp_notice_not_allowed' );

			if ( $notice_not_allowed ) {
				$class = 'warning';
				$msg   = esc_html__( 'You cannot edit this user, you have to unblock him/her first', 'remind-me-to-change-my-password' );
			}
		}

		if ( '' !== $msg ) :
			?>
			<div class="notice notice-<?php echo esc_attr( $class ); ?> is-dismissible">
				<p><?php echo esc_html( $msg ); ?></p>
			</div>
			<?php
		endif;
	}

	public function init() {
		if ( isset( $_GET['rmtcmp-reset'] ) && isset( $_GET['action'] ) && 'rp' === $_GET['action'] ) {
			list( $rp_path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );

			$value = sanitize_text_field( $_GET['rmtcmp-reset'] );

			setcookie( $this->rmtcmpreset_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

			wp_safe_redirect( remove_query_arg( array( 'rmtcmp-reset' ) ) );
			exit;
		}
	}

	public function profile_update( $user_id ) {
		if ( ! isset( $_POST['pass1'] ) || '' === $_POST['pass1'] ) {
			return;
		}

		if ( ! $_POST['pass1'] === $_POST['pass2'] ) {
			return;
		}

		$this->set_password_reset_time( $user_id );
	}

	public function user_register( $user_id ) {
		$this->set_password_reset_time( $user_id );
	}

	/**
	 * Initialisation du compteur
	 */
	public function admin_init() {
		if ( isset( $_GET['action'] ) && 'init_counter_reset_pwd' === $_GET['action'] && is_numeric( $_GET['user'] ) ) {
			$user_id_init = (int) $_GET['user'];
			check_admin_referer( 'init_counter_reset_pwd-user_' . get_current_user_id() );

			$this->set_password_reset_time( $user_id_init );

			wp_redirect( admin_url() . 'users.php' );
			exit;
		}

		/**
		 * Débloquer un utilisateur
		 * Entraine un mail à l'utilisateur
		 */
		if ( isset( $_REQUEST['action'] ) && 'unlock_user' === $_REQUEST['action'] && is_numeric( $_REQUEST['user'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'unlock_user_' . get_current_user_id() ) ) {
			add_filter(
				'retrieve_password_message',
				function ( $message, $key, $user_login, $user_data ) {
					$hash = hash( 'sha256', wp_generate_password( 20, true, true ) );
					update_user_meta( $user_data->ID, 'rmtcmp_user_unlock_key', time() . ':' . $hash );

					$url         = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ) . "&rmtcmp-reset=$hash", 'login' );
					$new_message = esc_html__( 'Welcome', 'remind-me-to-change-my-password' ) . '<br><br>';
					$new_message .= sprintf(
						                esc_html__(
							                'Your account (%s) has been suspended. To reactivate it, please use the following link:',
							                'remind-me-to-change-my-password'
						                ),
						                $user_login
					                ) . '<br><br>';
					$new_message .= '<a target="_blank" href="' . $url . '">' . $url . '</a>';

					return $new_message;
				},
				60,
				4
			);

			add_filter(
				'wp_mail_content_type',
				function () {
					return 'text/html';
				}
			);

			$user_id = (int) $_GET['user'];

			$data = get_user_by( 'id', $user_id );

			$msg = 0;

			if ( isset( $data, $data->user_email ) ) {
				retrieve_password( $data->user_email );
				$msg = 1;
			}

			set_transient( 'rmtcmp_notice_unlock_user', $msg );

			wp_redirect( admin_url() . 'users.php?page=blocked-users' );
			exit;
		}


		if ( isset( $_REQUEST['action'] ) && 'erase_users' === $_REQUEST['action'] && is_array( $_GET['users_id'] ) ) {

			$userids = array_map( 'intval', (array) $_REQUEST['users_id'] );

			$url = self_admin_url( 'users.php?action=delete&users[]=' . implode( '&users[]=', $userids ) );
			$url = str_replace( '&amp;', '&', wp_nonce_url( $url, 'bulk-users' ) );

			wp_redirect( $url );
			exit;
		}
	}
}
