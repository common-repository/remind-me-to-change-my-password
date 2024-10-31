<?php

namespace RMTCMP\User;

abstract class User {

	protected $rmtcmpreset_cookie = 'wp-rmtcmpreset-' . COOKIEHASH;

	/**
	 * @param $user_data
	 *
	 * @return bool
	 */
	protected function has_role_lock( $user_data ) {
		if ( in_array( 'locked_rmtcmp', $user_data->roles, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $user_id
	 */
	protected function set_password_reset_time( $user_id ) {
		$meta_name = 'user_reset_password';
		$meta_name = apply_filters( 'rmtcmp_meta_reset_password', $meta_name );
		update_user_meta( $user_id, $meta_name, time() );
	}

	/**
	 * @param $user_meta
	 *
	 * @return false|false[]
	 */
	protected function maybe_control( $user_meta ) {
		$options    = get_option( 'rmtcmp_form', array() );
		$roles_test = explode( '|', $options['lock_roles'] );

		$test = false;
		foreach ( $user_meta->roles as $r ) {
			if ( in_array( $r, $roles_test, true ) ) {
				$test = true;
				break;
			}
		}

		if ( false === $test ) {
			$key = array_search( 'without-role', $roles_test, true );
			if ( false !== $key && count( $user_meta->roles ) === 0 ) {
				$test = true;
			}
		}

		/**
		 * Si l'utilisateur n'a pas de rôle qu'on doit tester
		 *
		 */
		if ( false === $test ) {
			return false;
		}


		/**
		 * On va tester notre utilisateur et voir si on le laisse passer ou non
		 */
		$result     = $this->get_days_whitout_password_reset( $user_meta->ID );
		$days_reset = $options['max_days'];

		$lock = array(
			'user_lock'        => false,
			'password_expired' => false,
		);
		if ( $result > $days_reset ) {
			$lock['password_expired'] = true;
			if ( ( $result - $days_reset ) > $options['lock_days'] ) {
				$this->lock_user( $user_meta->ID );

				$lock['user_lock'] = true;
			}
			$this->destroy_all_sessions_with_cookies();
		}

		return $lock;
	}


	/**
	 * @param $user_id
	 *
	 * @return false|float|int
	 */
	protected function get_days_whitout_password_reset( $user_id ) {
		$meta_name = 'user_reset_password';
		$meta_name = apply_filters( 'rmtcmp_meta_reset_password', $meta_name );

		$timestamp = get_user_meta( $user_id, $meta_name, true );
		if ( '' === $timestamp ) {
			return - 1;
		}
		$timestamp = ( $timestamp > '' ) ? time() - $timestamp : 0;
		$time      = ceil( $timestamp / 86400 );
		return (int) $time > 1 ? $time - 1 : 0;
	}


	/**
	 * @param $key
	 *
	 * @return int 0|1|2 // valid / invalid / expired
	 */
	protected function maybe_invalid_key( $key ) {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'rmtcmp_user_unlock_key' && meta_value LIKE '%s'", '%' . $key ) );

		if ( ! isset( $result, $result->meta_value ) || '' === $result->meta_value ) {
			return 1;
		}
		$reset = explode( ':', $result->meta_value );

		// valide 1h
		if ( ! isset( $reset[0] ) || time() - $reset[0] > 3600 ) {
			return 2;
		}


		return 0;
	}


	/**
	 * @param int $user_id correspond à l'id de l'utilisateur
	 *
	 * @return  boolean
	 * Permet de savoir si un utilisateur est bloqué ou non / s'il est bloqué toutes ses sessions de connexion seront supprimées
	 */
	protected function maybe_lock( $user_id ) {

		$user_meta = \get_userdata( $user_id );

		if ( in_array( 'locked_rmtcmp', $user_meta->roles, true ) ) {
			$this->destroy_all_sessions_with_cookies();

			return 1;
		}

		return 0;
	}

	protected function destroy_all_sessions_with_cookies() {
		wp_clear_auth_cookie();
		wp_destroy_all_sessions();
		wp_destroy_other_sessions();
	}

	protected function copy_capabilities( $user_id ) {
		global $wpdb;
		$capabilities = $wpdb->prefix . 'capabilities';
		$level        = $wpdb->prefix . 'user_level';

		$copy_capabilities = get_user_meta( $user_id, $capabilities, true );
		$copy_level        = get_user_meta( $user_id, $level, true );

		update_user_meta( $user_id, 'rmtcmp_copy_capabilities', $copy_capabilities );
		update_user_meta( $user_id, 'rmtcmp_copy_level', $copy_level );
	}

	protected function restore_capabilities( $user ) {
		global $wpdb;

		$user->remove_all_caps();

		$user_id = $user->ID;

		$capabilities = $wpdb->prefix . 'capabilities';
		$level        = $wpdb->prefix . 'user_level';

		$copy_capabilities = get_user_meta( $user_id, 'rmtcmp_copy_capabilities', true );
		$copy_level        = get_user_meta( $user_id, 'rmtcmp_copy_level', true );

		update_user_meta( $user_id, $capabilities, $copy_capabilities );
		update_user_meta( $user_id, $level, $copy_level );

		delete_user_meta( $user_id, 'rmtcmp_lock_date' );
		delete_user_meta( $user_id, 'rmtcmp_user_unlock_key' );

		list( $rp_path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
		setcookie( $this->rmtcmpreset_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
	}

	/**
	 * @param int $user_id correspond à l'id de l'utilisateur
	 */
	public function lock_user( $user_id, $destroy_sessions = true ) {
		$u = new \WP_User( $user_id );

		$this->copy_capabilities( $user_id );

		// on supprime tous les rôles/droits
		$u->remove_all_caps();

		$u->add_role( 'locked_rmtcmp' );

		update_user_meta( $user_id, 'rmtcmp_lock_date', time() );

		if ( $destroy_sessions ) {
			$this->destroy_all_sessions_with_cookies();
		}
	}

}









